import os
import tempfile
from fastapi import UploadFile
from markitdown import MarkItDown
from langchain_text_splitters import RecursiveCharacterTextSplitter
from langchain_ollama import OllamaEmbeddings
from langchain_chroma import Chroma
from config import settings

def create_base_knowledge(file: UploadFile, filename: str) -> int:
    """
    Logika pemrosesan upload Word (DOCX):
    1. Konversi DOCX ke Markdown via MarkItDown (temp file).
    2. Split Markdown menjadi chunks dengan RecursiveCharacterTextSplitter & Filter Chunk Sampah.
    3. Simpan chunks ke ChromaDB dengan metadata source bernilai filename.
    """
    temp_path = None
    try:
        # 1. Baca upload file dan simpan ke file temporary
        suffix = os.path.splitext(file.filename)[1]
        with tempfile.NamedTemporaryFile(delete=False, suffix=suffix) as temp_file:
            content = file.file.read()
            temp_file.write(content)
            temp_path = temp_file.name

        # 2. Konversi DOCX ke Markdown menggunakan MarkItDown
        markitdown = MarkItDown()
        result = markitdown.convert(temp_path)
        markdown_content = result.text_content

        # 3. Potong teks markdown menjadi chunks menggunakan RecursiveCharacterTextSplitter (Opsi 3)
        splitter = RecursiveCharacterTextSplitter(
            chunk_size=settings.CHUNK_SIZE,
            chunk_overlap=settings.CHUNK_OVERLAP,
            separators=["\n\n", "\n", " ", ""]
        )
        raw_docs = splitter.create_documents(
            texts=[markdown_content],
            metadatas=[{"source": filename}] # Gunakan filename dari Laravel
        )

        # 4. Filter chunk sampah/header kosong pendek (Opsi 1)
        docs = []
        for doc in raw_docs:
            clean_text = doc.page_content.strip()
            # Minimal 30 karakter dan bukan baris header tunggal saja 
            if len(clean_text) >= 30 and not (clean_text.startswith("#") and "\n" not in clean_text):
                docs.append(doc)

        # 4. Inisialisasi Ollama Embeddings & ChromaDB
        embeddings = OllamaEmbeddings(
            base_url=settings.OLLAMA_BASE_URL,
            model=settings.OLLAMA_EMBEDDING_MODEL
        )
        vectorstore = Chroma(
            persist_directory=settings.CHROMA_PERSIST_DIR,
            embedding_function=embeddings,
            collection_name=settings.CHROMA_COLLECTION_NAME
        )

        # 5. Simpan ke ChromaDB
        vectorstore.add_documents(docs)
        return len(docs)

    except Exception as e:
        raise Exception(f"Gagal memproses file: {str(e)}")
    finally:
        # Hapus file temporary jika ada
        if temp_path and os.path.exists(temp_path):
            os.remove(temp_path)
