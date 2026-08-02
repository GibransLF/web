import psycopg2
from config import settings

def delete_base_knowledge(filename: str) -> int:
    """
    Logika penghapusan dokumen dari PostgreSQL berdasarkan metadata filename.
    """
    try:
        conn = psycopg2.connect(
            host=settings.POSTGRES_HOST,
            port=settings.POSTGRES_PORT,
            dbname=settings.POSTGRES_DB,
            user=settings.POSTGRES_USER,
            password=settings.POSTGRES_PASSWORD
        )
        cur = conn.cursor()

        # 1. Cari ID knowledge_bases berdasarkan filename / metadata_name
        cur.execute(
            "SELECT id FROM knowledge_bases WHERE metadata_name = %s OR filename = %s",
            (filename, filename)
        )
        row = cur.fetchone()
        if not row:
            cur.close()
            conn.close()
            return 0

        kb_id = row[0]

        # 2. Hapus chunk terkait di knowledge_chunks
        cur.execute("DELETE FROM knowledge_chunks WHERE knowledge_base_id = %s", (kb_id,))
        chunks_deleted = cur.rowcount

        conn.commit()
        cur.close()
        conn.close()

        return chunks_deleted
    except Exception as e:
        raise Exception(f"Gagal menghapus data: {str(e)}")
