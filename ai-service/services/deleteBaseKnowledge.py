from langchain_ollama import OllamaEmbeddings
from langchain_chroma import Chroma
from config import settings

def delete_base_knowledge(filename: str) -> int:
    """
    Logika penghapusan dokumen dari ChromaDB berdasarkan metadata source.
    """
    try:
        # 1. Inisialisasi Ollama Embeddings & ChromaDB
        embeddings = OllamaEmbeddings(
            base_url=settings.OLLAMA_BASE_URL,
            model=settings.OLLAMA_EMBEDDING_MODEL
        )
        vectorstore = Chroma(
            persist_directory=settings.CHROMA_PERSIST_DIR,
            embedding_function=embeddings,
            collection_name=settings.CHROMA_COLLECTION_NAME
        )
        
        # 2. Akses low-level collection ChromaDB untuk mencari & menghapus by metadata 'source'
        collection = vectorstore._collection
        results = collection.get(where={"source": filename})
        ids = results.get("ids", [])
        
        # 3. Hapus data jika ditemukan
        if ids:
            collection.delete(ids=ids)
            return len(ids)
        return 0
        
    except Exception as e:
        raise Exception(f"Gagal menghapus data: {str(e)}")
