from typing import Dict, Any
from config import settings
from services.db import get_db_connection

DEFAULT_SYSTEM_PROMPT = """Anda adalah asisten akademik PMB (Penerimaan Mahasiswa Baru). 
Aturan:
- Jawab hanya berdasarkan informasi pada konteks.
- Fokus hanya pada pertanyaan pengguna.
- Jangan menambahkan informasi yang tidak ditanyakan.
- Jawaban maksimal 3-5 kalimat."""

def get_chatbot_settings() -> Dict[str, Any]:
    """
    Mengambil konfigurasi chatbot terbaru dari tabel chatbot_settings di PostgreSQL.
    Jika terjadi error atau tabel kosong, mengembalikan nilai fallback default dari config.
    """
    default_config = {
        "top_k": 7,
        "fetch_k": 15,
        "temperature": 0.2,
        "system_prompt": DEFAULT_SYSTEM_PROMPT
    }
    
    try:
        conn = get_db_connection()
        cur = conn.cursor()
        
        cur.execute("""
            SELECT top_k, fetch_k, temperature, system_prompt 
            FROM chatbot_settings 
            ORDER BY id DESC 
            LIMIT 1
        """)
        row = cur.fetchone()
        
        cur.close()
        conn.close()
        
        if row:
            top_k, fetch_k, temperature, system_prompt = row
            return {
                "top_k": top_k if top_k is not None else default_config["top_k"],
                "fetch_k": fetch_k if fetch_k is not None else default_config["fetch_k"],
                "temperature": float(temperature) if temperature is not None else default_config["temperature"],
                "system_prompt": system_prompt.strip() if (system_prompt and system_prompt.strip()) else default_config["system_prompt"]
            }
    except Exception as e:
        print(f"[Warning] Gagal mengambil chatbot_settings dari PostgreSQL: {e}. Menggunakan nilai default.")
        
    return default_config
