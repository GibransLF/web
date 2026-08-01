import time
from typing import List
from langchain_ollama import OllamaEmbeddings, ChatOllama
from langchain_chroma import Chroma
from langchain_core.prompts import ChatPromptTemplate
from config import settings
from models import ChatHistoryItem

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

# 2. Definisikan ChatPromptTemplate untuk Chat RAG Utama (Tanpa History Placeholder)
chat_prompt_template = ChatPromptTemplate.from_messages([
    ("system", """Anda adalah asisten akademik PMB (Penerimaan Mahasiswa Baru). 
Aturan:
- Jawab hanya berdasarkan informasi pada konteks.
- Fokus hanya pada pertanyaan pengguna.
- Jangan menambahkan informasi yang tidak ditanyakan.
- Jika jawaban dapat disampaikan dalam 3-5 kalimat,
- Jika informasi tidak ada pada konteks, katakan bahwa informasi tersebut belum tersedia"""),
    ("human", """Konteks Informasi:
{context}

Pertanyaan Calon Mahasiswa: {search_query}""")
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

def chat_rag_mmr(newMessage: str, history: List[ChatHistoryItem] = None, k: int = 5, fetch_k: int = 20) -> tuple[str, str]:
    if history is None:
        history = []
        
    start_time = time.time()
    print("\n--- [START] PMB RAG Chat (MMR Search) ---")
    try:
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
            temperature=settings.LLM_TEMPERATURE
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
            
        # 3. Ambil Dokumen PMB dari ChromaDB menggunakan pencarian MMR
        t2 = time.time()
        actual_fetch_k = max(fetch_k, k)  # Memastikan fetch_k minimal sama dengan k
        print(f"[Step 2] Mencari dokumen relevan di ChromaDB menggunakan MMR (k={k}, fetch_k={actual_fetch_k}) untuk: '{search_query}'...")
        vectorstore = Chroma(
            persist_directory=settings.CHROMA_PERSIST_DIR,
            embedding_function=embeddings,
            collection_name=settings.CHROMA_COLLECTION_NAME
        )
        
        docs = vectorstore.max_marginal_relevance_search(
            search_query,
            k=k,
            fetch_k=actual_fetch_k,
            lambda_mult=0.5
        )
        
        print(f"\n=================== [HASIL RETRIEVAL CHROMADB MMR (k={k}, fetch_k={actual_fetch_k})] ===================")
        for i, doc in enumerate(docs, 1):
            source_info = doc.metadata.get("source", "N/A")
            print(f"Doc #{i} | Source: {source_info}")
            print(f"Teks Content:\n{doc.page_content}")
            print("-" * 70)
        print("=========================================================================\n")
        
        context = "\n\n".join([doc.page_content for doc in docs])
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
        print(f"--- [END] Total Waktu Proses RAG MMR: {total_time:.4f} detik ---\n")
        return response.content, search_query
    except Exception as e:
        print(f" -> ERROR: Terjadi kesalahan pada RAG MMR: {str(e)}")
        raise Exception(f"Terjadi kesalahan pada RAG MMR: {str(e)}")
