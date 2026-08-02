import uvicorn
from fastapi import FastAPI, UploadFile, File, Form, HTTPException
from config import settings
from models import ChatRequest, ChatResponse
# Import fungsi logika dari services/
from services.createBaseKnowledge import create_base_knowledge
# from services.chat import chat_rag
from services.chatOpenrouter import chat_rag

app = FastAPI(
    title="PMB RAG Service",
    description="Microservice RAG dengan Router di main.py dan Logika Terpisah di services/",
    version="1.0.0"
)

# 1. Base Endpoint
@app.get("/")
async def root():
    return {"status": "running", "service": "PMB RAG Service"}

# 2. Endpoint Knowledge Base - POST (Upload & Ingest DOCX)
@app.post("/service/createnewknowledge")
async def add_knowledge(
    file: UploadFile = File(...),
    filename: str = Form(...)
):
    if not file.filename.endswith(".docx"):
        raise HTTPException(status_code=400, detail="Hanya file DOCX yang didukung saat ini.")
    try:
        chunks_added = create_base_knowledge(file, filename)
        return {
            "success": True,
            "message": f"Berhasil memproses file '{file.filename}' dengan nama metadata '{filename}'.",
            "chunks": chunks_added
        }
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

# 3. Endpoint Chat RAG - POST (Tanya Jawab RAG)
@app.post("/service/chat", response_model=ChatResponse)
async def chat(request: ChatRequest):
    try:
        response_text, search_query = chat_rag(
            newMessage=request.newMessage,
            history=request.history or []
        )
        return ChatResponse(
            success=True,
            response=response_text,
            question=search_query
        )
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

if __name__ == "__main__":
    uvicorn.run("main:app", host=settings.HOST, port=settings.PORT, reload=True)
