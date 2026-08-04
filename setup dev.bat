@echo off
wt ^
new-tab --title "Laravel" cmd /k "cd C:\workplace\kuliah\skripsi\web\chatbot-PMB && php artisan serve --host=0.0.0.0 --port=8000" ^
; new-tab --title "Queue" cmd /k "cd C:\workplace\kuliah\skripsi\web\chatbot-PMB && php artisan queue:work" ^
; new-tab --title "Vite" cmd /k "cd C:\workplace\kuliah\skripsi\web\chatbot-PMB && npm run dev" ^
; new-tab --title "FastAPI" cmd /k "cd C:\workplace\kuliah\skripsi\web\ai-service && .venv\Scripts\activate && uvicorn main:app --reload --host 127.0.0.1 --port 8080"