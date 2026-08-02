import os
import tempfile
import json
import re
import psycopg2
from datetime import datetime
from fastapi import UploadFile
from markitdown import MarkItDown
from langchain_text_splitters import RecursiveCharacterTextSplitter
from langchain_ollama import OllamaEmbeddings
from config import settings
from services.db import get_db_connection

def create_base_knowledge(file: UploadFile, filename: str) -> int:
    """
    Logika pemrosesan upload Word (DOCX):
    1. Konversi DOCX ke Markdown via MarkItDown (temp file).
    2. Split Markdown menjadi chunks dengan RecursiveCharacterTextSplitter & Filter Chunk Sampah.
    3. Simpan chunks & vector embedding ke database PostgreSQL (tabel knowledge_chunks).
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
        
        # 2.5 Perbaiki header tabel hasil MarkItDown
        pattern = re.compile(
            r'^\|(?:\s*\|\s*)+\s*$\r?\n'            # |  |  |
            r'^\|(?:\s*:?-+:?\s*\|)+\s*$\r?\n'      # | --- | --- |
            r'(?P<header>^\|.*\|)$',                 # | **Pertanyaan** | **Jawaban** |
            flags=re.MULTILINE
        )

        def replace(match):
            header = match.group("header")

            # Hitung jumlah kolom
            column_count = header.count("|") - 1

            # Buat separator baru sesuai jumlah kolom
            separator = "|" + "|".join([" --- "] * column_count) + "|"

            return f"{header}\n{separator}"

        markdown_content = pattern.sub(replace, markdown_content)

        # 3. Potong teks markdown menjadi chunks menggunakan RecursiveCharacterTextSplitter
        splitter = RecursiveCharacterTextSplitter(
            chunk_size=settings.CHUNK_SIZE,
            chunk_overlap=settings.CHUNK_OVERLAP,
            separators=["\n\n", "\n", "\n#", ""]
        )
        raw_docs = splitter.create_documents(
            texts=[markdown_content],
            metadatas=[{"source": filename}]
        )

        # 4. Filter chunk sampah/header kosong pendek
        docs = []
        for doc in raw_docs:
            clean_text = doc.page_content.strip()
            if len(clean_text) >= 100 and not (clean_text.startswith("#") and "\n" not in clean_text):
                docs.append(doc)

        if not docs:
            return 0

        # 5. Inisialisasi Ollama Embeddings
        embeddings = OllamaEmbeddings(
            base_url=settings.OLLAMA_BASE_URL,
            model=settings.OLLAMA_EMBEDDING_MODEL
        )

        # 6. Koneksi ke PostgreSQL database pmb-rag via helper terpusat
        conn = get_db_connection(register_pgvector=True)
        cur = conn.cursor()

        # Cari ID knowledge_bases berdasarkan metadata_name
        cur.execute("SELECT id FROM knowledge_bases WHERE metadata_name = %s", (filename,))
        row = cur.fetchone()
        if row:
            kb_id = row[0]
        else:
            now = datetime.now()
            cur.execute(
                "INSERT INTO knowledge_bases (filename, metadata_name, created_at) VALUES (%s, %s, %s) RETURNING id",
                (filename, filename, now)
            )
            kb_id = cur.fetchone()[0]

        # Hapus chunk lama jika ada
        cur.execute("DELETE FROM knowledge_chunks WHERE knowledge_base_id = %s", (kb_id,))

        # Hasilkan embedding dan simpan ke knowledge_chunks
        now = datetime.now()
        for doc in docs:
            vector = embeddings.embed_query(doc.page_content)
            vector_str = json.dumps(vector)
            cur.execute(
                """
                INSERT INTO knowledge_chunks (knowledge_base_id, chunk_text, embedding, created_at)
                VALUES (%s, %s, %s, %s)
                """,
                (kb_id, doc.page_content, vector_str, now)
            )

        conn.commit()
        cur.close()
        conn.close()

        return len(docs)

    except Exception as e:
        raise Exception(f"Gagal memproses file: {str(e)}")
    finally:
        if temp_path and os.path.exists(temp_path):
            os.remove(temp_path)
