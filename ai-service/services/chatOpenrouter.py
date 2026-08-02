import time
import numpy as np
from typing import List, Optional
from langchain_ollama import OllamaEmbeddings, ChatOllama
from langchain_core.prompts import ChatPromptTemplate
from config import settings
from models import ChatHistoryItem
from services.db_settings import get_chatbot_settings
from services.db import get_db_connection

# Batas maksimal Cosine Distance (0.0 = persis identik, >0.65 = kurang relevan)
DISTANCE_THRESHOLD = 0.65

# 1. Definisikan ChatPromptTemplate untuk Kondensasi Pertanyaan
condense_prompt_template = ChatPromptTemplate.from_messages([
    (
        "system",
        """
Tugasmu HANYA menulis ulang pertanyaan menjadi pertanyaan mandiri.

Aturan:
- Jangan menjawab pertanyaan.
- Jangan memberi penjelasan.
- Jangan memberi informasi tambahan.
- Output HARUS berupa 1 kalimat pertanyaan.
- Jika pertanyaan sudah mandiri, kembalikan persis apa adanya.
"""
    ),
    ("human", "Riwayat:\n{history}\n\nPertanyaan:\n{newMessage}")
])

def condense_pmb_question(newMessage: str, history: List[ChatHistoryItem], llm: ChatOllama) -> str:
    history_str = ""
    for item in history:
        history_str += f"Calon Mahasiswa: {item.question}\nAsisten PMB: {item.answer}\n"
        
    formatted_messages = condense_prompt_template.format_messages(
        history=history_str,
        newMessage=newMessage
    )
    
    response = llm.invoke(formatted_messages)
    return response.content.strip()

def maximal_marginal_relevance(query_embedding: list[float], candidate_embeddings: list[np.ndarray], k: int = 5, lambda_mult: float = 0.5) -> list[int]:
    """
    Menghitung MMR (Maximal Marginal Relevance) re-ranking untuk memilih k kandidat paling relevan & beragam.
    """
    if not candidate_embeddings:
        return []
    
    query_vec = np.array(query_embedding, dtype=float)
    cand_vecs = np.array(candidate_embeddings, dtype=float)
    
    query_norm = np.linalg.norm(query_vec)
    cand_norms = np.linalg.norm(cand_vecs, axis=1)
    
    query_norm = query_norm if query_norm != 0 else 1.0
    cand_norms = np.where(cand_norms == 0, 1.0, cand_norms)
    
    sim_to_query = np.dot(cand_vecs, query_vec) / (cand_norms * query_norm)
    similarity_matrix = np.dot(cand_vecs, cand_vecs.T) / np.outer(cand_norms, cand_norms)
    
    selected = []
    unselected = list(range(len(candidate_embeddings)))
    
    for _ in range(min(k, len(candidate_embeddings))):
        if not unselected:
            break
            
        if not selected:
            best_idx = unselected[np.argmax(sim_to_query[unselected])]
        else:
            mmr_scores = []
            for idx in unselected:
                max_sim_to_selected = max(similarity_matrix[idx, s] for s in selected)
                score = lambda_mult * sim_to_query[idx] - (1 - lambda_mult) * max_sim_to_selected
                mmr_scores.append((score, idx))
            best_idx = max(mmr_scores, key=lambda x: x[0])[1]
            
        selected.append(best_idx)
        unselected.remove(best_idx)
        
    return selected

def chat_rag(newMessage: str, history: List[ChatHistoryItem] = None, k: Optional[int] = None, fetch_k: Optional[int] = None) -> tuple[str, str]:
    if history is None:
        history = []
        
    start_time = time.time()
    print("\n--- [START] PMB RAG Chat (PostgreSQL pgvector Adapter + HNSW Search) ---")
    try:
        # 0. Load chatbot settings dari PostgreSQL
        bot_settings = get_chatbot_settings()
        actual_k = k if k is not None else bot_settings["top_k"]
        actual_fetch_k = fetch_k if fetch_k is not None else bot_settings["fetch_k"]
        temperature = bot_settings["temperature"]
        system_prompt = bot_settings["system_prompt"]

        print(f"[Settings DB] top_k={actual_k}, fetch_k={actual_fetch_k}, temperature={temperature}")

        # Dynamic Chat Prompt Template
        chat_prompt_template = ChatPromptTemplate.from_messages([
            ("system", system_prompt),
            ("human", """Konteks Informasi:
{context}

Pertanyaan Calon Mahasiswa: {search_query}""")
        ])

        # 1. Inisialisasi Model LLM & Embeddings PMB
        t0 = time.time()
        print("[Step 1] Inisialisasi Model LLM & Embeddings...")
        embeddings = OllamaEmbeddings(
            base_url=settings.OLLAMA_BASE_URL,
            model=settings.OLLAMA_EMBEDDING_MODEL
        )
        llm = ChatOllama(
            base_url=settings.OLLAMA_BASE_URL,
            model=settings.OLLAMA_LLM_MODEL,
            temperature=temperature,
            timeout=getattr(settings, "OLLAMA_TIMEOUT", 180)
        )
        t1 = time.time()
        print(f" -> Selesai dalam: {t1 - t0:.4f} detik")
        
        # 2. Kondensasi pertanyaan jika ada history percakapan
        if history:
            t_condense_start = time.time()
            print("[Step 1.5] Kondensasi Pertanyaan (Meringkas Riwayat)...")
            search_query = condense_pmb_question(newMessage, history, llm)
            t_condense_end = time.time()
            print(f" -> Hasil Standalone Query: '{search_query}'")
            print(f" -> Selesai dalam: {t_condense_end - t_condense_start:.4f} detik")
        else:
            search_query = newMessage
            
        # 3. Ambil Dokumen PMB dari PostgreSQL (knowledge_chunks) menggunakan pgvector Adapter + HNSW Index
        t2 = time.time()
        calc_fetch_k = max(actual_fetch_k, actual_k)
        print(f"[Step 2] Mencari dokumen di PostgreSQL (pgvector adapter + HNSW, k={actual_k}, fetch_k={calc_fetch_k}, threshold={DISTANCE_THRESHOLD}) untuk: '{search_query}'...")

        raw_vector = embeddings.embed_query(search_query)
        query_vector = np.array(raw_vector)

        # Gunakan helper db terpusat dengan register_pgvector=True
        conn = get_db_connection(register_pgvector=True)
        cur = conn.cursor()

        # Filtering Cosine Distance dilakukan langsung di level database SQL
        cur.execute(
            """
            SELECT kc.chunk_text, kb.metadata_name, kc.embedding, (kc.embedding <=> %s::vector) AS distance
            FROM knowledge_chunks kc
            JOIN knowledge_bases kb ON kc.knowledge_base_id = kb.id
            WHERE (kc.embedding <=> %s::vector) <= %s
            ORDER BY kc.embedding <=> %s::vector
            LIMIT %s
            """,
            (query_vector, query_vector, DISTANCE_THRESHOLD, query_vector, calc_fetch_k)
        )
        rows = cur.fetchall()
        cur.close()
        conn.close()

        candidates = []
        for chunk_text, meta_name, emb_val, dist in rows:
            emb_array = emb_val.to_numpy() if hasattr(emb_val, 'to_numpy') else np.array(emb_val)
            candidates.append({
                "page_content": chunk_text,
                "source": meta_name,
                "embedding": emb_array,
                "distance": dist
            })

        # Re-rank menggunakan MMR jika kandidat > actual_k
        if len(candidates) > actual_k:
            cand_embeddings = [c["embedding"] for c in candidates]
            selected_indices = maximal_marginal_relevance(query_vector, cand_embeddings, k=actual_k, lambda_mult=0.5)
            docs = [candidates[i] for i in selected_indices]
        else:
            docs = candidates

        print(f"\n=================== [HASIL RETRIEVAL POSTGRESQL PGVECTOR (k={len(docs)}, fetch_k={len(candidates)})] ===================")
        for i, doc in enumerate(docs, 1):
            source_info = doc.get("source", "N/A")
            dist_info = doc.get("distance", 0.0)
            print(f"Doc #{i} | Source: {source_info} | Cosine Distance: {dist_info:.4f}")
            print(f"Teks Content:\n{doc['page_content']}")
            print("-" * 70)
        print("=========================================================================\n")
        
        if docs:
            context = "\n\n".join([doc["page_content"] for doc in docs])
        else:
            context = "Informasi tidak ditemukan pada basis pengetahuan PMB STMIK Bandung."

        t3 = time.time()
        print(f" -> Selesai dalam: {t3 - t2:.4f} detik (Dokumen ditemukan: {len(docs)})")
        
        # 4. Format prompt RAG Utama menggunakan ChatPromptTemplate
        t4 = time.time()
        print("[Step 3] Memformat prompt utama...")
        formatted_messages = chat_prompt_template.format_messages(
            context=context,
            search_query=search_query
        )
        t5 = time.time()
        print(f" -> Selesai dalam: {t5 - t4:.4f} detik")
        
        # 5. Panggil LLM PMB dengan pesan terformat
        t6 = time.time()
        print("[Step 4] Memanggil LLM PMB untuk mendapatkan jawaban...")
        response = llm.invoke(formatted_messages)
        t7 = time.time()
        print(f" -> Selesai dalam: {t7 - t6:.4f} detik")
        
        total_time = t7 - start_time
        print(f"--- [END] Total Waktu Proses RAG pgvector: {total_time:.4f} detik ---\n")
        return response.content, search_query
    except Exception as e:
        print(f" -> ERROR: Terjadi kesalahan pada RAG pgvector: {str(e)}")
        raise Exception(f"Terjadi kesalahan pada RAG pgvector: {str(e)}")
