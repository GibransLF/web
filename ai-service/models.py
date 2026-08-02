from pydantic import BaseModel
from typing import Optional

class ChatHistoryItem(BaseModel):
    question: str
    answer: str

class ChatRequest(BaseModel):
    newMessage: str
    history: Optional[list[ChatHistoryItem]] = None

class ChatResponse(BaseModel):
    success: bool
    response: str
    question: str
