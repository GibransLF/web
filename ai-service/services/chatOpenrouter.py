import time
import numpy as np
from typing import List, Optional
from langchain_ollama import OllamaEmbeddings
from langchain_openrouter import ChatOpenRouter
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

def condense_pmb_question(newMessage: str, history: List[ChatHistoryItem], llm: ChatOpenRouter) -> str:
    try:
        history_str = ""
        for item in history:
            history_str += f"Calon Mahasiswa: {item.question}\nAsisten PMB: {item.answer}\n"
            
        formatted_messages = condense_prompt_template.format_messages(
            history=history_str,
            newMessage=newMessage
        )
        
        response = llm.invoke(formatted_messages)
        return response.content.strip()
    except Exception as e:
        print(f"[WARNING] Kondensasi riwayat pertanyaan gagal: {str(e)[:100]}. Menggunakan pertanyaan awal.")
        return newMessage

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
    print("\n--- [START] PMB RAG Chat (OpenRouter API + PostgreSQL pgvector Adapter) ---")
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

        # 1. Inisialisasi Embedding Provider (Mode 1: Ollama Lokal | Mode 2: OpenRouter Cloud)
        # ------------------------------------------------------------------------------
        # MODE 1 (AKTIF DEFAULT): Ollama Embeddings Lokal
        from langchain_ollama import OllamaEmbeddings
        embeddings = OllamaEmbeddings(
            base_url=settings.OLLAMA_BASE_URL,
            model=settings.OLLAMA_EMBEDDING_MODEL
        )

        # MODE 2 (OPSIONAL): OpenRouter Cloud Embeddings
        # from langchain_openrouter import OpenRouterEmbeddings
        # embeddings = OpenRouterEmbeddings(
        #     api_key=settings.OPENROUTER_API_KEY,
        #     model=settings.OPENROUTER_EMBEDDING_MODEL
        # )
        # ------------------------------------------------------------------------------

        # Inisialisasi LLM Utama (OPENROUTER_MODEL)
        primary_model_name = settings.OPENROUTER_MODEL
        print(f"[Step 1] Inisialisasi OpenRouter LLM (Model Utama: {primary_model_name})...")
        llm = ChatOpenRouter(
            api_key=settings.OPENROUTER_API_KEY,
            model=primary_model_name,
            temperature=temperature
        )
        
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
            SELECT kc.chunk_text, kc.embedding, (kc.embedding <=> %s::vector) AS distance
            FROM knowledge_chunks kc
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
        for chunk_text, emb_val, dist in rows:
            emb_array = emb_val.to_numpy() if hasattr(emb_val, 'to_numpy') else np.array(emb_val)
            candidates.append({
                "page_content": chunk_text,
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
        
        # 5. Panggil LLM PMB dengan Try-Catch 2-Tier Fallback (Model Utama -> Second Model)
        t6 = time.time()
        print(f"[Step 4] Memanggil LLM PMB ('{primary_model_name}') untuk mendapatkan jawaban...")
        
        response_content = None
        
        # Attempt 1: Model Utama (OPENROUTER_MODEL)
        try:
            response = llm.invoke(formatted_messages)
            response_content = response.content
        except Exception as err_primary:
            err_msg = str(err_primary)
            print(f"[WARNING] Model utama '{primary_model_name}' mengalami kendala: {err_msg[:120]}")
            
            # Attempt 2: Second Model Fallback (OPENROUTER_SECOND_MODEL)
            second_model_name = getattr(settings, "OPENROUTER_SECOND_MODEL", "").strip()
            if second_model_name:
                try:
                    print(f"[FALLBACK] Mengalihkan ke model cadangan (second model): '{second_model_name}'...")
                    secondary_llm = ChatOpenRouter(
                        api_key=settings.OPENROUTER_API_KEY,
                        model=second_model_name,
                        temperature=temperature
                    )
                    response = secondary_llm.invoke(formatted_messages)
                    response_content = response.content
                except Exception as err_secondary:
                    print(f"[WARNING] Model cadangan '{second_model_name}' juga mengalami kendala: {str(err_secondary)[:120]}")

        # Jika seluruh model gagal / rate-limited, kembalikan jawaban ramah publik di chat-widget
        if not response_content:
            print("[INFO] Mengembalikan respon ramah publik karena AI service sedang mengalami antrean/gangguan.")
            response_content = "Maaf, layanan AI Assistant PMB sedang mengalami antrean tinggi / gangguan sementara. Silakan coba beberapa saat lagi atau hubungi panitia PMB STMIK Bandung."

        t7 = time.time()
        print(f" -> Selesai dalam: {t7 - t6:.4f} detik")
        
        total_time = t7 - start_time
        print(f"--- [END] Total Waktu Proses RAG pgvector: {total_time:.4f} detik ---\n")
        return response_content, search_query
    except Exception as e:
        print(f"[ERROR] Kendala pada alur RAG: {str(e)[:150]}")
        fallback_msg = "Maaf, layanan AI Assistant PMB sedang mengalami antrean tinggi / gangguan sementara. Silakan coba beberapa saat lagi atau hubungi panitia PMB STMIK Bandung."
        return fallback_msg, newMessage
