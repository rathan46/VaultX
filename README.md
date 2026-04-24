# VaultX 🔐

**A Secure and Scalable Encrypted Cloud Storage Platform**

VaultX is a privacy-first cloud storage platform designed to ensure that sensitive data remains protected at every stage. It combines **end-to-end encryption**, **searchable encrypted storage**, **tamper-proof logging**, and **role-based access control** into a single full-stack solution.

Built for academic projects, enterprise prototypes, and modern secure storage systems, VaultX demonstrates how usability and security can coexist in real-world applications.

---

## 🚀 Features

### 🔒 Secure File Storage

* Upload and manage files securely through a modern web interface.
* Files are encrypted before storage using strong encryption standards.
* Large files are handled efficiently using chunk-based encryption.

### 🔍 Searchable Encrypted Data

* Search files without exposing raw keywords.
* Keywords are hashed before storage for privacy-preserving lookups.

### 📝 Tamper-Proof Audit Logs

* Every major activity is logged securely.
* Logs are chained with hashes to detect modification or deletion attempts.

### 👥 Role-Based Access Control

* Supports multiple users with different access permissions.
* Designed for collaborative and enterprise environments.

### ⚡ Full-Stack Architecture

* **Frontend:** Next.js + TypeScript
* **Backend:** FastAPI (Python)
* **Database:** PostgreSQL

---

## 🏗️ Project Architecture

```text
VaultX/
│── frontend/        # Next.js user interface
│── backend/         # FastAPI backend services
│── database/        # PostgreSQL schemas & data
│── docker-compose.yml
│── README.md
```

### Data Flow

1. User interacts with the frontend dashboard.
2. Requests are sent securely to the backend API.
3. Files and metadata are encrypted before being stored.
4. PostgreSQL stores only encrypted data.
5. Logs are recorded in a tamper-evident chain.

---

## 🛡️ Security Model

### File Encryption

* AES-256-GCM encryption for confidentiality and integrity.
* Unique nonce for every encrypted chunk.
* Secure streaming for large files.

### Key Isolation

Different categories of data use separate derived keys, reducing risk of cross-compromise.

### Encrypted Search

* Keywords are hashed before storage.
* Search works by comparing hashes, not plaintext values.

### Audit Integrity

* Hash-linked logs ensure tampering is detectable.
* Useful for compliance and forensic review.

---

## ⚙️ Installation & Setup

### Prerequisites

* Node.js (latest LTS)
* Python 3.10+
* PostgreSQL
* Docker (optional)

### Clone Repository

```bash
git clone https://github.com/rathan46/VaultX.git
cd VaultX
```

### Backend Setup

```bash
cd backend
pip install -r requirements.txt
uvicorn main:app --reload
```

### Frontend Setup

```bash
cd frontend
npm install
npm run dev
```

### Using Docker

```bash
docker-compose up --build
```

---

## 📌 Usage

1. Register or log in to your account.
2. Upload files securely.
3. Search encrypted content using keywords.
4. Manage permissions and users.
5. Review secure audit logs.

---

## 🎯 Use Cases

* Secure personal cloud storage
* Enterprise document management
* Privacy-first SaaS platforms
* Academic cybersecurity projects
* Internal company file systems

---

## 🛠️ Future Improvements

* Two-Factor Authentication (2FA)
* File sharing with expiring links
* Zero-knowledge encryption model
* Mobile application support
* AI-powered threat monitoring

---

## 🤝 Contributing

Contributions are welcome! Fork the repository, create a new branch, and submit a pull request.

```bash
git checkout -b feature-name
git commit -m "Added new feature"
git push origin feature-name
```

---

## 👨‍💻 Author

Developed by Rathan R Prabhu

---

⭐ If you like this project, consider giving it a star on GitHub!
