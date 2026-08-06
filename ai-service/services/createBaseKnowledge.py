import os
import tempfile
import json
import re
import time
import psycopg2
from datetime import datetime
from fastapi import UploadFile
from markitdown import MarkItDown
from langchain_text_splitters import MarkdownHeaderTextSplitter, RecursiveCharacterTextSplitter
from langchain_ollama import OllamaEmbeddings
from config import settings
from services.db import get_db_connection

def create_base_knowledge(file: UploadFile, filename: str, kb_id: int) -> int:
    """
    Logika pemrosesan upload Word (DOCX):
    1. Konversi DOCX ke Markdown via MarkItDown (temp file).
    2. Split Markdown berbasis Header (MarkdownHeaderTextSplitter) & RecursiveCharacterTextSplitter.
    3. Simpan heading di metadata dan di awal chunk.
    4. Simpan chunks & vector embedding ke database PostgreSQL (tabel knowledge_chunks).
    """
    t_start = time.time()
    print(f"\n--- [START] Memproses KB (ID: {kb_id}): '{filename}' ---")
    temp_path = None
    try:
        # 1. Baca upload file dan simpan ke file temporary
        suffix = os.path.splitext(file.filename)[1]
        with tempfile.NamedTemporaryFile(delete=False, suffix=suffix) as temp_file:
            content = file.file.read()
            temp_file.write(content)
            temp_path = temp_file.name

        # 2. Konversi DOCX ke Markdown menggunakan MarkItDown
        print(f"[1/4] Konversi DOCX -> Markdown (MarkItDown)...")
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

        # 3. Potong teks markdown berbasis Header (MarkdownHeaderTextSplitter) & RecursiveCharacterTextSplitter
        headers_to_split_on = [
            ("#", "Header 1"),
            ("##", "Header 2"),
            ("###", "Header 3"),
            ("####", "Header 4")
        ]
        header_splitter = MarkdownHeaderTextSplitter(
            headers_to_split_on=headers_to_split_on,
            strip_headers=True
        )
        header_splits = header_splitter.split_text(markdown_content)

        text_splitter = RecursiveCharacterTextSplitter(
            chunk_size=settings.CHUNK_SIZE,
            chunk_overlap=settings.CHUNK_OVERLAP,
            separators=["\n\n", "\n", " ", ""]
        )
        raw_docs = text_splitter.split_documents(header_splits)

        # 4. Tambahkan prefix heading ke page_content & filter chunk kosong/pendek
        docs = []
        for doc in raw_docs:
            clean_body = doc.page_content.strip()
            if not clean_body:
                continue

            # Buat prefix heading dari metadata
            header_prefix_parts = []
            for h_level, h_key in [("#", "Header 1"), ("##", "Header 2"), ("###", "Header 3"), ("####", "Header 4")]:
                if h_key in doc.metadata:
                    header_prefix_parts.append(f"{h_level} {doc.metadata[h_key]}")

            header_prefix = "\n".join(header_prefix_parts)
            if header_prefix:
                doc.page_content = f"{header_prefix}\n\n{clean_body}"
            else:
                doc.page_content = clean_body

            if len(doc.page_content.strip()) >= 50:
                docs.append(doc)

        print(f"[2/4] Chunking berbasis Header & Filtering (Hasil: {len(docs)} chunk)...")

        if not docs:
            print(f"[WARNING] Tidak ada chunk valid dari '{filename}'.")
            return 0

        # 5. Inisialisasi Embedding Provider (Mode 1: Ollama Lokal | Mode 2: OpenRouter Cloud)
        # ------------------------------------------------------------------------------
        # MODE 1 (NONAKTIF): Ollama Embeddings Lokal
        # from langchain_ollama import OllamaEmbeddings
        # embeddings = OllamaEmbeddings(
        #     base_url=settings.OLLAMA_BASE_URL,
        #     model=settings.OLLAMA_EMBEDDING_MODEL
        # )

        # MODE 2 (AKTIF): OpenRouter Cloud Embeddings (via langchain_openai OpenAIEmbeddings)
        print(f"[3/4] Inisialisasi Embedding Model ({settings.OPENROUTER_EMBEDDING_MODEL})...")
        from langchain_openai import OpenAIEmbeddings
        embeddings = OpenAIEmbeddings(
            openai_api_key=settings.OPENROUTER_API_KEY,
            openai_api_base=settings.OPENROUTER_BASE_URL,
            model=settings.OPENROUTER_EMBEDDING_MODEL,
            dimensions=1024,
            check_embedding_ctx_length=False,
            tiktoken_enabled=False
        )
        # ------------------------------------------------------------------------------

        # 6. Koneksi ke PostgreSQL database pmb-rag via helper terpusat
        conn = get_db_connection(register_pgvector=True)
        cur = conn.cursor()

        # Verifikasi ID knowledge_bases resmi yang dikirim dari Laravel
        cur.execute("SELECT id FROM knowledge_bases WHERE id = %s", (kb_id,))
        row = cur.fetchone()
        if not row:
            raise Exception(f"Dokumen Knowledge Base dengan ID {kb_id} tidak ditemukan di database PostgreSQL.")

        kb_id = row[0]

        # Hapus chunk lama jika ada
        cur.execute("DELETE FROM knowledge_chunks WHERE knowledge_base_id = %s", (kb_id,))

        print(f"[4/4] Vektorisasi & Simpan {len(docs)} chunk ke PostgreSQL (KB ID: {kb_id})...")

        # Hasilkan embedding dan simpan ke knowledge_chunks beserta metadata
        now = datetime.now()
        for doc in docs:
            vector = embeddings.embed_query(doc.page_content)
            vector_str = json.dumps(vector)
            meta_dict = {"source": filename}
            meta_dict.update(doc.metadata)
            meta_json = json.dumps(meta_dict)
            cur.execute(
                """
                INSERT INTO knowledge_chunks (knowledge_base_id, chunk_text, embedding, metadata, created_at)
                VALUES (%s, %s, %s, %s, %s)
                """,
                (kb_id, doc.page_content, vector_str, meta_json, now)
            )

        conn.commit()
        cur.close()
        conn.close()

        t_elapsed = time.time() - t_start
        print(f"--- [SUCCESS] '{filename}' berhasil diproses ({len(docs)} chunk tersimpan) dalam {t_elapsed:.2f}s ---\n")
        return len(docs)

    except Exception as e:
        print(f"[ERROR] Gagal memproses '{filename}': {str(e)}")
        raise Exception(f"Gagal memproses file: {str(e)}")
    finally:
        if temp_path and os.path.exists(temp_path):
            os.remove(temp_path)
