import requests
import psycopg2
from config import settings

url = "http://127.0.0.1:8000/service/createnewknowledge"
file_path = "faq.docx"
metadata_name = "faq_test_pmb.docx"

print(f"1. Mengirim POST request upload {file_path} ke {url}...")

with open(file_path, "rb") as f:
    files = {"file": (file_path, f, "application/vnd.openxmlformats-officedocument.wordprocessingml.document")}
    data = {"filename": metadata_name}
    response = requests.post(url, files=files, data=data)

print(f"HTTP Status Code: {response.status_code}")
print(f"Response JSON: {response.json()}")

if response.status_code == 200 and response.json().get("success"):
    print("\n2. Menginspeksi database PostgreSQL (pmb-rag)...")
    conn = psycopg2.connect(
        host=settings.POSTGRES_HOST,
        port=settings.POSTGRES_PORT,
        dbname=settings.POSTGRES_DB,
        user=settings.POSTGRES_USER,
        password=settings.POSTGRES_PASSWORD
    )
    cur = conn.cursor()
    
    cur.execute("SELECT id, filename, metadata_name FROM knowledge_bases WHERE metadata_name = %s", (metadata_name,))
    kb_row = cur.fetchone()
    print(f"KnowledgeBase Record: {kb_row}")
    
    if kb_row:
        kb_id = kb_row[0]
        cur.execute("SELECT COUNT(*), MIN(LENGTH(chunk_text)), MAX(LENGTH(chunk_text)) FROM knowledge_chunks WHERE knowledge_base_id = %s", (kb_id,))
        count, min_len, max_len = cur.fetchone()
        print(f"KnowledgeChunks: Total = {count} chunk, Min Length = {min_len}, Max Length = {max_len}")
        
        cur.execute("SELECT chunk_order, SUBSTRING(chunk_text FROM 1 FOR 60), SUBSTRING(embedding::text FROM 1 FOR 40) FROM knowledge_chunks WHERE knowledge_base_id = %s ORDER BY chunk_order LIMIT 2", (kb_id,))
        sample_chunks = cur.fetchall()
        for idx, text, emb in sample_chunks:
            print(f"  Chunk #{idx}: '{text}...' | Embedding prefix: {emb}...")

    cur.close()
    conn.close()
    print("\nVERIFIKASI BERHASIL!")
else:
    print("VERIFIKASI GAGAL!")
