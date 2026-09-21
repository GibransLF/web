# Web PMB Chatbot & AI Service (RAG)

Sistem Chatbot Penerimaan Mahasiswa Baru (PMB) interaktif berbasis **Retrieval-Augmented Generation (RAG)**. Proyek ini memadukan portal web pengguna dan manajemen PMB dengan microservice kecerdasan buatan (AI) untuk menjawab pertanyaan calon mahasiswa seputar informasi pendaftaran, rincian biaya, program studi, dan FAQ kampus secara otomatis dan akurat.

---

## 🏛️ Arsitektur & Struktur Direktori

Repositori ini menggunakan struktur monorepo yang terdiri dari komponen-komponen berikut:

- **`chatbot-PMB/`**: Aplikasi web portal utama yang dibangun menggunakan **Laravel 13**, **Livewire 4**, **Flux UI**, **Tailwind CSS**, dan database queue. Berfungsi sebagai antarmuka chat publik, panel admin untuk kelola knowledge base, dan panel supervisor untuk monitoring percakapan.
- **`ai-service/`**: Microservice backend AI berbasis **Python FastAPI**, **LangChain**, dan **PostgreSQL (`pgvector`)**. Berfungsi memproses chunking dokumen, pembentukan vector embedding, pencarian semantik (similarity search), serta integrasi LLM melalui **OpenRouter API**.
- **`data/`**: Direktori berisi dokumen rujukan resmi kampus format `.docx` (Panduan Pendaftaran, Biaya & Info Kampus, FAQ) yang siap diunggah ke knowledge base.
- **`setup dev.bat`**: Script batch untuk menjalankan seluruh service sekaligus menggunakan Windows Terminal (`wt`).

```
Pengguna / Mahasiswa
        │
        ▼
┌──────────────────┐       Queue Worker       ┌──────────────────────┐
│  Laravel 13 Web  │ ───────────────────────> │  AI Service (FastAPI)│
│  (chatbot-PMB)   │ <─────────────────────── │     (ai-service)     │
└──────────────────┘         HTTP/REST        └──────────┬───────────┘
                                                         │
                                        ┌────────────────┴────────────────┐
                                        ▼                                 ▼
                             PostgreSQL (pgvector)              OpenRouter Cloud API
                             [Vector Knowledge Base]            [LLM & Embedding]
```

---

## 📋 Prasyarat Sistem (Prerequisites)

Pastikan perangkat lunak berikut telah terpasang pada komputer Anda sebelum memulai instalasi:

- **Git**
- **PHP >= 8.3** dengan ekstensi aktif:
  - `pdo_sqlite` (atau `pdo_mysql` jika menggunakan MySQL)
  - `mbstring`
  - `curl`
  - `fileinfo`
  - `openssl`
- **Composer** (PHP Dependency Manager)
- **Node.js (LTS v18+ atau v20+) & npm**
- **Python >= 3.10** (disarankan 3.11 atau 3.12) & **pip**
- **PostgreSQL (v15+)** dengan ekstensi **`pgvector`** terpasang
- **API Key OpenRouter** (diperlukan untuk model LLM & text embedding)

---

## 🚀 Panduan Setup & Instalasi

### Langkah 1: Kloning Repositori

Buka terminal (Git Bash, Command Prompt, atau PowerShell) dan clone repositori ini:

```bash
git clone https://github.com/GibransLF/web.git
cd web
```

---

### Langkah 2: Persiapan Database Vector (PostgreSQL)

Microservice AI memerlukan database PostgreSQL dengan ekstensi `pgvector` untuk menyimpan embedding dokumen.

1. Buka console PostgreSQL (`psql`) atau gunakan database client (pgAdmin / DBeaver).
2. Buat database baru bernama `pmb-rag`:
   ```sql
   CREATE DATABASE "pmb-rag";
   ```
3. Hubungkan ke database tersebut dan aktifkan ekstensi `vector`:
   ```sql
   \c pmb-rag
   CREATE EXTENSION IF NOT EXISTS vector;
   ```

---

### Langkah 3: Setup AI Microservice (`ai-service`)

1. Masuk ke direktori `ai-service`:
   ```bash
   cd ai-service
   ```

2. Buat dan aktifkan Virtual Environment Python:
   - **Windows (PowerShell / Command Prompt)**:
     ```bash
     python -m venv .venv
     .venv\Scripts\activate
     ```
   - **Linux / macOS**:
     ```bash
     python3 -m venv .venv
     source .venv/bin/activate
     ```

3. Pasang seluruh dependensi pustaka Python:
   ```bash
   pip install -r requirements.txt
   ```

4. Salin template environment ke file `.env`:
   - **Windows**:
     ```bash
     copy .env.example .env
     ```
   - **Linux / macOS**:
     ```bash
     cp .env.example .env
     ```

5. **Konfigurasi File `.env`**:
   Buka file `.env` di dalam folder `ai-service/`, kemudian masukkan **API Key OpenRouter** Anda dan sesuaikan kredensial database PostgreSQL:
   ```env
   PORT=8080
   HOST=127.0.0.1

   # OpenRouter Configuration
   OPENROUTER_API_KEY=isi_api_key_openrouter_anda_di_sini
   OPENROUTER_BASE_URL=https://openrouter.ai/api/v1
   OPENROUTER_MODEL=qwen/qwen3.7-flash
   OPENROUTER_SECOND_MODEL=~deepseek/deepseek-v4-flash-latest
   OPENROUTER_EMBEDDING_MODEL=qwen/qwen3-embedding-8b

   # PostgreSQL Database
   POSTGRES_HOST=127.0.0.1
   POSTGRES_PORT=5432
   POSTGRES_DB=pmb-rag
   POSTGRES_USER=postgres
   POSTGRES_PASSWORD=password_postgres_anda
   ```

6. Uji coba menjalankan server FastAPI:
   ```bash
   uvicorn main:app --reload --host 127.0.0.1 --port 8080
   ```
   Buka browser pada `http://127.0.0.1:8080/docs` untuk memastikan API Swagger aktif dan berjalan normal.

---

### Langkah 4: Setup Web Portal PMB (`chatbot-PMB`)

Buka terminal baru atau pindah direktori ke folder `chatbot-PMB`:

1. Masuk ke direktori `chatbot-PMB`:
   ```bash
   cd ../chatbot-PMB
   ```

2. Pasang dependensi PHP melalui Composer:
   ```bash
   composer install
   ```

3. Salin file environment:
   - **Windows**:
     ```bash
     copy .env.example .env
     ```
   - **Linux / macOS**:
     ```bash
     cp .env.example .env
     ```

4. Generate Application Key Laravel:
   ```bash
   php artisan key:generate
   ```

5. **Konfigurasi Database & Service**:
   Buka file `.env` di dalam folder `chatbot-PMB/`. Secara default aplikasi menggunakan **SQLite**:
   - Jika menggunakan SQLite, pastikan file database tersedia (Laravel biasanya akan menawarkan membuat file ini secara otomatis saat migrasi, atau Anda dapat membuatnya manual di `database/database.sqlite`).
   - Pastikan variabel koneksi AI Service dan Queue sesuai:
     ```env
     QUEUE_CONNECTION=database
     AI_SERVICE_URL=http://127.0.0.1:8080
     AI_SERVICE_TIMEOUT=180
     ```

6. Jalankan migrasi database beserta seeder data awal:
   ```bash
   php artisan migrate --seed
   ```

7. Pasang dependensi JavaScript dan build aset frontend:
   ```bash
   npm install
   npm run build
   ```

---

## 👤 Akun Bawaan (Default Accounts)

Proses `db:seed` telah membuatkan akun pengguna default untuk pengujian:

| Role / Peran | Email | Password | Hak Akses |
|---|---|---|---|
| **Administrator PMB** | `admin@test.com` | `wasdwasd` | Kelola Dokumen Knowledge Base, Pengaturan Bot, Manajemen User |
| **Supervisor PMB** | `supervisior1@test.com` | `plokijuh.` | Monitoring Riwayat Chat, Validasi & Evaluasi Respon AI |

---

## 🏃 Menjalankan Aplikasi

Agar sistem dapat berfungsi secara menyeluruh, terdapat 4 layanan yang perlu dijalankan:

### Opsi 1: Menjalankan Secara Manual (4 Terminal)

Buka 4 tab/jendela terminal terpisah:

1. **Terminal 1 - AI Microservice (FastAPI)**:
   ```bash
   cd ai-service
   .venv\Scripts\activate      # (Linux/macOS: source .venv/bin/activate)
   uvicorn main:app --reload --host 127.0.0.1 --port 8080
   ```

2. **Terminal 2 - Web Server Laravel**:
   ```bash
   cd chatbot-PMB
   php artisan serve --host=0.0.0.0 --port=8000
   ```
   *Aplikasi web dapat diakses melalui browser di `http://127.0.0.1:8000`.*

3. **Terminal 3 - Queue Worker Laravel (Wajib)**:
   ```bash
   cd chatbot-PMB
   php artisan queue:work
   ```
   > **Catatan Penting**: Antrean job ini wajib berjalan agar pesan chat dari pengguna dapat diteruskan ke microservice AI dan proses embedding file knowledge base dapat diproses di latar belakang.

4. **Terminal 4 - Frontend Vite (Development)**:
   ```bash
   cd chatbot-PMB
   npm run dev
   ```
   *(Opsional jika Anda sudah menjalankan `npm run build` sebelumnya).*

---

### Opsi 2: Menjalankan Cepat di Windows (`setup dev.bat`)

Tersedia file shortcut `setup dev.bat` untuk membuka ke-4 tab tersebut sekaligus menggunakan Windows Terminal (`wt`).

> ⚠️ **PENTING: Penyesuaian Path Manual untuk Hasil Git Clone**
> File `setup dev.bat` berisi path direktori absolut. Jika Anda meng-clone proyek ini di folder selain `C:\workplace\kuliah\skripsi\web`, buka file `setup dev.bat` menggunakan teks editor (misal: Notepad atau VS Code), lalu sesuaikan path direktori pada perintah `cd` dengan lokasi folder proyek Anda saat ini:
> ```cmd
> new-tab --title "Laravel" cmd /k "cd <LOKASI_FOLDER_ANDA>\chatbot-PMB && php artisan serve --host=0.0.0.0 --port=8000" ^
> ; new-tab --title "Queue" cmd /k "cd <LOKASI_FOLDER_ANDA>\chatbot-PMB && php artisan queue:work" ^
> ; new-tab --title "Vite" cmd /k "cd <LOKASI_FOLDER_ANDA>\chatbot-PMB && npm run dev" ^
> ; new-tab --title "FastAPI" cmd /k "cd <LOKASI_FOLDER_ANDA>\ai-service && .venv\Scripts\activate && uvicorn main:app --reload --host 127.0.0.1 --port 8080"
> ```
> Setelah path disesuaikan, Anda cukup klik ganda (double-click) file `setup dev.bat` untuk menjalankan seluruh service.

---

## 📚 Inisialisasi Dokumen Knowledge Base (Folder `data/`)

Agar chatbot dapat menjawab pertanyaan spesifik mengenai PMB:

1. Buka browser dan akses web di `http://127.0.0.1:8000/login`.
2. Masuk menggunakan akun Administrator (`admin@test.com` / `wasdwasd`).
3. Masuk ke menu **Knowledge Base** pada panel navigasi admin.
4. Klik tombol **Tambah Knowledge Base** atau **Upload Dokumen**.
5. Unggah file dokumen rujukan yang tersedia pada folder `data/` di repositori ini:
   - `PANDUAN PENDAFTARAN.docx`
   - `biaya dan tentang kami.docx`
   - `faq.docx`
6. Pastikan **Queue Worker** (`php artisan queue:work`) sedang aktif agar proses ekstraksi teks, chunking, dan pembentukan vector embedding ke PostgreSQL berjalan sukses.
7. Setelah selesai, buka halaman utama chat PMB dan mulai bertanya seputar informasi pendaftaran mahasiswa baru!
