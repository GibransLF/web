# Product Requirements Document (PRD)
## AI Assistant PMB STMIK Bandung

**Versi:** 1.0
**Status:** Draft
**Platform:** Web (Responsive)

---

## 1. Latar Belakang

Website Penerimaan Mahasiswa Baru (PMB) STMIK Bandung saat ini menyediakan informasi melalui halaman website dan FAQ. Namun, informasi masih bersifat statis sehingga calon mahasiswa harus mencari jawaban secara manual.

AI Assistant PMB STMIK Bandung dikembangkan sebagai aplikasi pendamping berbasis Artificial Intelligence (AI) yang mampu memberikan informasi PMB melalui percakapan alami menggunakan teknologi Retrieval-Augmented Generation (RAG).

Aplikasi ini bukan pengganti website PMB, melainkan media pendamping yang memudahkan calon mahasiswa memperoleh informasi secara cepat, akurat, dan dapat diakses selama 24 jam.

---

## 2. Tujuan Produk

- Memberikan layanan informasi PMB yang cepat dan mudah diakses.
- Mengurangi waktu pencarian informasi oleh calon mahasiswa.
- Mempermudah admin PMB dalam memperbarui knowledge tanpa mengubah kode program.
- Menyediakan layanan chatbot berbasis AI yang mampu memberikan jawaban berdasarkan knowledge resmi PMB.

---

## 3. Target Pengguna

### Calon Mahasiswa

Kebutuhan:
- Bertanya mengenai informasi PMB.
- Melihat riwayat percakapan.
- Mendapatkan jawaban dengan cepat.

### Admin PMB

Kebutuhan:
- Mengelola Knowledge Base.
- Mengatur konfigurasi chatbot.
- Melihat riwayat percakapan.
- Melihat statistik penggunaan chatbot.

---

## 4. Ruang Lingkup

### Termasuk

- Landing Page PMB
- AI Assistant
- History Chat
- Dashboard Admin
- Knowledge Base
- Chatbot Settings
- Analytics
- Retrieval-Augmented Generation (RAG)

### Tidak Termasuk

- Pendaftaran PMB
- Pembayaran
- Login Mahasiswa
- Edit Knowledge
- Rating Jawaban
- Streaming Response

---

## 5. User Role

### Guest

- Menggunakan chatbot tanpa login.
- Riwayat percakapan disimpan berdasarkan session.

### Admin

- Login ke dashboard.
- Mengelola seluruh fitur administrasi.

---

## 6. Fitur

### 6.1 Landing Page

Menampilkan informasi singkat mengenai PMB.

Konten:
- Hero Section
- Tentang PMB
- Program Studi
- Informasi PMB
- Tombol menuju Website PMB Resmi

**Desktop**
AI Assistant tampil sebagai sidebar di sisi kanan.

**Mobile**
Landing Page tetap ditampilkan. Terdapat Floating Chat Button yang akan membuka halaman chatbot penuh.

---

### 6.2 AI Assistant

Fitur:
- Welcome Message
- Mengirim pertanyaan
- Menampilkan jawaban AI
- Loading Animation (•••)
- Mendukung Markdown
- Menampilkan disclaimer AI
- Menampilkan history chat berdasarkan session

Apabila informasi tidak ditemukan, chatbot menampilkan pesan:

> "Maaf, saya tidak menemukan informasi tersebut pada knowledge base PMB STMIK Bandung."

---

### 6.3 History Chat

Seluruh percakapan disimpan berdasarkan session.

Data yang disimpan:
- Guest ID
- Pertanyaan
- Jawaban
- Waktu

Untuk kebutuhan AI, sistem hanya menggunakan 1 percakapan terakhir sebagai memory.

---

### 6.4 Knowledge Base

Admin dapat:
- Upload dokumen DOCX
- Download dokumen
- Hapus dokumen

**Ketentuan Upload**

- Hanya menerima file DOCX.
- Nama file tidak boleh sama.
- Saat upload, admin wajib mengisi metadata dokumen melalui form:
  - **Kategori** (contoh: Biaya, Jadwal, Program Studi, Persyaratan, Umum) — dipilih dari daftar kategori yang sudah ditentukan (dropdown), agar konsisten dan bisa dipakai untuk filtered retrieval.
  - **Deskripsi singkat** (opsional) — membantu admin mengenali isi dokumen dari daftar Knowledge Base.
- Metadata ini disimpan di level dokumen dan diturunkan ke seluruh chunk yang dihasilkan dari dokumen tersebut, sehingga setiap chunk punya kategori yang jelas tanpa perlu admin menandai per-chunk secara manual.

**Alur Otomatis Setelah Upload Berhasil**

1. Membaca dokumen DOCX.
2. Mengubah dokumen menjadi Markdown.
3. Chunking:
   - Split awal berdasarkan heading Markdown (`MarkdownHeaderTextSplitter`) agar potongan konten mengikuti struktur asli dokumen (per sub-bagian/topik).
   - Section yang masih terlalu panjang dipecah lebih lanjut menggunakan `RecursiveCharacterTextSplitter` dengan chunk size dan overlap yang ditentukan di konfigurasi sistem.
4. Membuat embedding untuk setiap chunk.
5. Menyimpan setiap chunk beserta embedding dan metadata kategori (warisan dari dokumen) ke tabel `knowledge_chunks` di PostgreSQL + pgvector.

**Hapus Dokumen**

- Saat admin menghapus dokumen dari Knowledge Base, sistem juga menghapus seluruh chunk yang terasosiasi dengan `knowledge_base_id` dokumen tersebut di tabel `knowledge_chunks`, agar tidak ada chunk yatim (orphan) yang masih bisa ter-retrieve.

---

### 6.5 Chatbot Settings

Admin dapat mengubah konfigurasi chatbot melalui dashboard.

Parameter yang dapat diatur:
- Maksimal karakter input pengguna.
- Maksimal memory chat.
- Nilai Top-K.
- Nilai Fetch-K (MMR).
- Temperature LLM.
- System Prompt AI.

Seluruh konfigurasi disimpan di database sehingga dapat diubah tanpa melakukan perubahan kode program.

---

### 6.6 Analytics

Dashboard menampilkan:
- Total chat.
- Total pertanyaan.
- Total dokumen knowledge.
- Pertanyaan yang paling sering diajukan.

---

## 7. Non Functional Requirement

**Performance**
- Waktu respons chatbot maksimal ±10 detik (bergantung pada OpenRouter).

**Security**
- CSRF Protection.
- Validasi upload file.
- Session untuk Guest.
- Authentication untuk Admin.

**Compatibility**
- Desktop.
- Tablet.
- Mobile.

---

## 8. Teknologi

**Frontend**
- Laravel 13
- Livewire
- Tailwind CSS

**Backend AI**
- FastAPI
- LangChain

**Embedding Model**
- Ollama
- Qwen3 Embedding 0.6B

**Large Language Model**
- OpenRouter
- Qwen3 8B

**Vector Database**
- PostgreSQL
- pgvector (index HNSW)

**Komunikasi Antar Sistem**
- REST API (Laravel ↔ FastAPI)

---

## 9. Struktur Proyek (Repository)

Proyek dipisah menjadi dua repository/folder utama sesuai tanggung jawab masing-masing layer:

| Folder | Isi | Teknologi |
|---|---|---|
| `chatbot-PMB` | Aplikasi web utama: Landing Page, AI Assistant (UI), History Chat, Dashboard Admin, Knowledge Base (UI), Chatbot Settings (UI), Analytics | Laravel 13, Livewire, Tailwind CSS |
| `AI-service` | Layanan backend AI: pemrosesan dokumen, chunking, embedding, RAG pipeline, komunikasi dengan LLM | FastAPI, LangChain |

Kedua layer berkomunikasi melalui REST API, dengan `chatbot-PMB` (Laravel) bertindak sebagai konsumen yang mengirim request ke `AI-service` (FastAPI) untuk seluruh proses yang melibatkan LLM, retrieval, dan pengelolaan Knowledge Base.

---

## 10. Arsitektur Sistem

```
User
        │
        ▼
Laravel 13 (chatbot-PMB)
        │
     REST API
        │
        ▼
FastAPI (AI-service)
        │
     LangChain
        │
   MMR Retriever
        │
PostgreSQL + pgvector (index HNSW)
        │
OpenRouter (Qwen3 8B)
        │
        ▼
Jawaban AI
```

---

## 11. Alur Knowledge Base

```
Admin Upload DOCX + Isi Metadata (Kategori)
        │
        ▼
Membaca Dokumen
        │
        ▼
Konversi ke Markdown
        │
        ▼
Chunking (Header-based + Recursive Split)
        │
        ▼
Embedding per Chunk
        │
        ▼
Simpan ke knowledge_chunks
(embedding + metadata kategori)
        │
        ▼
PostgreSQL + pgvector (index HNSW)
```

*Catatan: Saat dokumen dihapus, seluruh chunk terkait pada `knowledge_chunks` turut dihapus.*

---

## 12. Alur Chatbot

```
Pengguna Bertanya
        │
        ▼
Ambil 1 Percakapan Terakhir
        │
        ▼
Standalone Question
        │
        ▼
MMR Retrieval
(fetch_k = 15, k = 5)
[pencarian pada index HNSW]
        │
        ▼
OpenRouter (Qwen3 8B)
        │
        ▼
Jawaban AI + Sumber Dokumen
(diambil dari original_name pada
knowledge_base_id tiap chunk yang dipakai)
        │
        ▼
Simpan History Chat
```

Jawaban AI menyertakan referensi dokumen sumber (nama file) yang menjadi dasar jawaban, untuk transparansi dan memudahkan verifikasi oleh admin maupun calon mahasiswa.

---

## 13. Struktur Database (Konseptual)

### users
Digunakan untuk akun Admin.

### knowledge_bases
- id
- filename
- original_name
- kategori
- deskripsi
- created_at
- updated_at

### knowledge_chunks
- id
- knowledge_base_id *(foreign key ke knowledge_bases)*
- chunk_text
- embedding *(vector, pgvector)*
- kategori *(warisan dari knowledge_bases, disimpan ulang di level chunk untuk mempercepat filtered search)*
- chunk_order
- created_at

> Index: `CREATE INDEX ... USING hnsw (embedding vector_cosine_ops)` pada kolom `embedding` di tabel `knowledge_chunks`.

### chatbot_settings
- id
- max_input_character
- max_chat_memory
- top_k
- fetch_k
- temperature
- system_prompt
- updated_at

### chat_history
- id
- guest_id
- question
- answer
- source_documents *(referensi original_name dokumen yang dipakai sebagai sumber jawaban, disimpan sebagai JSON/array)*
- created_at

---

## 14. Kriteria Keberhasilan

Sistem dinyatakan berhasil apabila:

- Admin dapat mengunggah dokumen DOCX beserta metadata kategorinya.
- Dokumen berhasil diproses menjadi chunk dan embedding.
- Chunk dan embedding tersimpan di tabel `knowledge_chunks` pada PostgreSQL + pgvector dengan index HNSW.
- Chatbot mampu menjawab pertanyaan berdasarkan knowledge base beserta sumber dokumennya.
- Riwayat percakapan tersimpan berdasarkan session.
- Dashboard analytics menampilkan statistik penggunaan chatbot.
- Konfigurasi chatbot dapat diubah melalui dashboard tanpa mengubah kode program.
- Sistem dapat digunakan dengan baik pada perangkat desktop maupun mobile.

---

## Catatan Implementasi

- **Metadata kategori** diinput manual oleh admin saat upload (bukan diekstrak otomatis dari heading dokumen), sehingga kategorisasi lebih terkontrol dan konsisten sesuai kebutuhan bisnis PMB.
- **Index HNSW** dipilih karena mudah diimplementasikan langsung melalui ekstensi pgvector tanpa komponen tambahan, dan memberikan akurasi pencarian similarity yang baik untuk skala data PMB (jumlah dokumen dan chunk yang tidak terlalu besar).
- **Struktur repository** dipisah antara `chatbot-PMB` (Laravel, layer aplikasi/UI) dan `AI-service` (FastAPI + LangChain, layer AI/RAG), agar kedua layer bisa dikembangkan dan di-deploy secara independen.
