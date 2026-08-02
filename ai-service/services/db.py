import psycopg2
from pgvector.psycopg2 import register_vector
from config import settings

def get_db_connection(register_pgvector: bool = False):
    """
    Fungsi terpusat untuk membuat koneksi ke database PostgreSQL.
    Jika register_pgvector=True, mendaftarkan adapter pgvector secara otomatis.
    """
    conn = psycopg2.connect(
        host=settings.POSTGRES_HOST,
        port=settings.POSTGRES_PORT,
        dbname=settings.POSTGRES_DB,
        user=settings.POSTGRES_USER,
        password=settings.POSTGRES_PASSWORD
    )
    if register_pgvector:
        register_vector(conn)
    return conn
