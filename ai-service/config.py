from pydantic_settings import BaseSettings

class Settings(BaseSettings):
    PORT: int = 8080
    HOST: str = "127.0.0.1"
    
    OLLAMA_BASE_URL: str = "http://localhost:11434"
    OLLAMA_EMBEDDING_MODEL: str = "qwen3-embedding:0.6b"
    OLLAMA_LLM_MODEL: str = "qwen2.5:3b-instruct"

    OPENROUTER_API_KEY: str = ""
    OPENROUTER_BASE_URL: str = "https://openrouter.ai/api/v1"
    OPENROUTER_MODEL: str = ""
    
    POSTGRES_HOST: str = "127.0.0.1"
    POSTGRES_PORT: int = 5432
    POSTGRES_DB: str = "pmb-rag"
    POSTGRES_USER: str = "postgres"
    POSTGRES_PASSWORD: str = "plokijuh"
    
    CHUNK_SIZE: int = 600
    CHUNK_OVERLAP: int = 100
    RETRIEVAL_K: int = 3
    LLM_TEMPERATURE: float = 0.2
    OLLAMA_TIMEOUT: int = 180

    class Config:
        env_file = ".env"
        extra = "ignore"

settings = Settings()
