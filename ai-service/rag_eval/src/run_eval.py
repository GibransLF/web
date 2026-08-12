import os
import sys
import json
import time
import pandas as pd
from dotenv import load_dotenv
from datasets import Dataset

from langchain_openai import ChatOpenAI, OpenAIEmbeddings
from ragas import evaluate
from ragas.llms import LangchainLLMWrapper
from ragas.embeddings import LangchainEmbeddingsWrapper
from ragas.metrics import (
    context_precision,
    context_recall,
    faithfulness,
    answer_relevancy
)

# Setup path agar dapat mengimpor services dari ai-service
ai_service_dir = os.path.abspath(os.path.join(os.path.dirname(__file__), "../.."))
sys.path.append(ai_service_dir)

# Load environment variables dari ai-service/.env terlebih dahulu, lalu rag_eval/.env tanpa menimpa DB embedding
env_path_rag_eval = os.path.join(os.path.dirname(__file__), "../.env")
env_path_ai_service = os.path.join(ai_service_dir, ".env")

load_dotenv(dotenv_path=env_path_ai_service)
load_dotenv(dotenv_path=env_path_rag_eval, override=False)

# Import fungsi chat_rag dari services.chatOpenrouter
from services.chatOpenrouter import chat_rag

def run_evaluation():
    print("\n--- [START] RAGAS Evaluation Loop (OpenRouter Evaluator) ---")
    
    openrouter_key = os.getenv("OPENROUTER_API_KEY")
    if not openrouter_key or openrouter_key == "your_openrouter_api_key_here":
        print("[WARNING] OPENROUTER_API_KEY belum dikonfigurasi di rag_eval/.env!")
        print("Harap isikan OPENROUTER_API_KEY Anda pada file rag_eval/.env untuk mengeksekusi evaluasi.")
        return

    openrouter_base_url = os.getenv("OPENROUTER_BASE_URL", "https://openrouter.ai/api/v1")
    eval_model_name = os.getenv("OPENROUTER_EVAL_MODEL", "qwen/qwen3.7-flash")
    embed_model_name = os.getenv("RAGAS_EVAL_EMBEDDING_MODEL", "openai/text-embedding-3-small")

    # 1. Inisialisasi OpenRouter Evaluator LLM
    print(f"[Step 1] Inisialisasi OpenRouter Evaluator LLM ({eval_model_name})...")
    openrouter_llm = ChatOpenAI(
        model=eval_model_name,
        openai_api_key=openrouter_key,
        openai_api_base=openrouter_base_url,
        default_headers={
            "HTTP-Referer": "http://localhost:8080",
            "X-Title": "PMB RAG Evaluation"
        }
    )
    evaluator_llm = LangchainLLMWrapper(openrouter_llm)

    # 2. Inisialisasi OpenRouter Evaluator Embeddings (untuk metrik answer_relevancy)
    print(f"[Step 2] Inisialisasi OpenRouter Evaluator Embeddings ({embed_model_name})...")
    openrouter_embeddings = OpenAIEmbeddings(
        model=embed_model_name,
        openai_api_key=openrouter_key,
        openai_api_base=openrouter_base_url
    )
    evaluator_embeddings = LangchainEmbeddingsWrapper(openrouter_embeddings)

    # 3. Load dataset pengujian
    dataset_path = os.path.join(os.path.dirname(__file__), "../datasets/pmb_ground_truth.json")
    print(f"[Step 3] Memuat benchmark dataset dari {dataset_path}...")
    with open(dataset_path, "r", encoding="utf-8") as f:
        ground_truth_samples = json.load(f)

    questions = []
    responses = []
    contexts_list = []
    ground_truths = []

    # 4. Eksekusi RAG pipeline pada ai-service
    print("[Step 4] Eksekusi RAG pipeline pada ai-service...")
    for idx, sample in enumerate(ground_truth_samples, 1):
        q = sample["question"]
        gt = sample["ground_truth"]
        print(f" -> Testing Soal #{idx}: '{q}'")
        
        ans, search_q, retrieved_ctxs = chat_rag(newMessage=q)
        
        questions.append(q)
        responses.append(ans)
        contexts_list.append(retrieved_ctxs if retrieved_ctxs else ["Tidak ada konteks."])
        ground_truths.append(gt)

    # 5. Konversi ke RAGAS Dataset Format
    eval_dict = {
        "question": questions,
        "response": responses,
        "contexts": contexts_list,
        "ground_truth": ground_truths
    }
    dataset = Dataset.from_dict(eval_dict)

    # 6. Menjalankan Evaluasi RAGAS (OpenRouter LLM + OpenRouter Embeddings)
    print("[Step 6] Menjalankan evaluasi RAGAS (OpenRouter LLM + OpenRouter Embeddings)...")
    metrics = [
        context_precision,
        context_recall,
        faithfulness,
        answer_relevancy
    ]

    results = evaluate(
        dataset=dataset,
        metrics=metrics,
        llm=evaluator_llm,
        embeddings=evaluator_embeddings
    )

    print("\n=================== [HASIL EVALUASI RAGAS] ===================")
    print(results)
    print("=============================================================\n")

    # 7. Simpan laporan ke CSV (dengan penanganan aman jika file sedang dibuka)
    results_dir = os.path.join(os.path.dirname(__file__), "../results")
    os.makedirs(results_dir, exist_ok=True)
    
    df_results = results.to_pandas()
    csv_path = os.path.join(results_dir, "eval_report.csv")
    try:
        df_results.to_csv(csv_path, index=False)
        print(f"[SUCCESS] Laporan evaluasi berhasil disimpan di: {csv_path}")
    except PermissionError:
        alt_csv_path = os.path.join(results_dir, f"eval_report_{int(time.time())}.csv")
        df_results.to_csv(alt_csv_path, index=False)
        print(f"[SUCCESS] File utama sedang terbuka. Laporan evaluasi berhasil disimpan di file alternatif: {alt_csv_path}")

if __name__ == "__main__":
    run_evaluation()
