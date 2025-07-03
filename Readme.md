# 📦 Symfony Data Importer

This project is a Symfony-based web application that supports:

- ✅ Importing large Excel datasets (100,000+ rows) asynchronously via background jobs
- ✅ RabbitMQ integration for messaging between Laravel and Symfony
- ✅ Audit logging of RabbitMQ messages
- ✅ Docker-based local development setup

---

## 🚀 Features

- Asynchronous Excel import using Symfony Messenger
- Native SQL batch inserts with `Doctrine\DBAL`
- Beautiful upload UI with Bootstrap styling
- Message consumer for RabbitMQ storing logs in MySQL
- Cleanly organized service structure and message handlers

---

## ⚙️ Prerequisites

- Docker & Docker Compose
- PHP 8.3 (inside Docker)
- Composer
- Node.js & NPM (optional for assets)

---

## 🐳 Run with Docker

Clone the repo and run the following:

```bash
cp .env .env.local
make up
```

Then install PHP & JS dependencies:

```bash
make install
```
The app will be available at: http://localhost:8001

RabbitMQ UI at: http://localhost:15673
Login: guest / guest

## Sample excel to upload: https://limewire.com/d/ORg9P#g7HvZTyiHT
