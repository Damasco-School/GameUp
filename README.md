# 🎮 GameUp

> Piattaforma didattica interattiva con mondo 2D pixel art, giochi educativi multiplayer e forum di classe.

Progetto sviluppato per la materia **Sistemi e Reti** — Polo Tecnologico Imperiese.

---

## 🌍 Cos'è GameUp

GameUp è una web-app ispirata a Wordwall in cui gli studenti esplorano un **villaggio 2D stile Stardew Valley**, entrano negli edifici per materia, creano e giocano a quiz didattici, e discutono sul forum. Il tutto in tempo reale con i compagni di classe grazie al multiplayer WebSocket.

### Funzionalità principali

- **Mondo 2D pixel art** — villaggio esplorabile con 6 edifici per materia, NPC con dialoghi, fontana animata, lago, fiori e alberi con depth sorting
- **Via segreta** — percorso nascosto nel bordo sud del villaggio che porta al Giardino Segreto con effetti magici e lucciole
- **Multiplayer in tempo reale** — vedi gli altri studenti muoversi nel mondo, chat integrata
- **5 tipi di gioco** — Quiz a scelta multipla, Flashcard, Matching, Snake didattico, Tetris Quiz
- **Crea giochi** — editor in 4 step per creare giochi per qualsiasi materia
- **Forum** — discussioni divise per categoria/materia, con login e registrazione
- **Pannello Admin** — gestione completa di giochi, post e utenti con un'interfaccia dedicata
- **Offline-first** — tutto funziona anche senza server grazie al localStorage come fallback

---

## 🏗️ Architettura

```
GameUp/
├── docker-compose.yml        ← orchestra i 4 container
│
├── frontend/                 ← Nginx (porta 80)
│   ├── index.html            ← Mondo 2D pixel art + multiplayer
│   ├── create.html           ← Editor giochi (4 step)
│   ├── play.html             ← 5 tipi di gioco giocabili
│   ├── forum.html            ← Forum con login/registrazione
│   └── admin.html            ← Pannello amministratore
│
├── backend/                  ← PHP 8.2 + Apache (porta 8080)
│   ├── index.php             ← API REST completa
│   ├── .htaccess             ← URL rewriting
│   └── Dockerfile
│
├── db/                       ← MySQL 8.0
│   └── init.sql              ← Schema tabelle + dati di esempio
│
└── ws-server/                ← Node.js WebSocket (porta 3000)
    ├── server.js             ← Gestione presenze, posizioni, chat
    ├── package.json
    └── Dockerfile
```

### Stack tecnologico

| Layer | Tecnologia |
|-------|-----------|
| Frontend | HTML5 Canvas, CSS3, JavaScript vanilla |
| Backend API | PHP 8.2 + Apache |
| Database | MySQL 8.0 |
| Multiplayer | Node.js + WebSocket (libreria `ws`) |
| Web server | Nginx (frontend), Apache (backend) |
| Containerizzazione | Docker + Docker Compose |

### API REST (backend PHP)

| Metodo | Endpoint | Descrizione |
|--------|----------|-------------|
| GET | `/games?subject=X` | Lista giochi per materia |
| GET | `/games/:id` | Singolo gioco con dati |
| POST | `/games` | Crea nuovo gioco |
| DELETE | `/games/:id` | Elimina gioco (admin) |
| POST | `/games/:id/play` | Incrementa contatore |
| GET | `/games/:id/scores` | Top 10 classifica |
| POST | `/games/:id/scores` | Salva punteggio |
| GET | `/forum/categories` | Categorie forum |
| GET | `/forum/categories/:id/posts` | Post di una categoria |
| POST | `/forum/categories/:id/posts` | Nuovo post |
| GET | `/forum/posts/:id` | Post con risposte |
| POST | `/forum/posts/:id/replies` | Nuova risposta |
| DELETE | `/forum/posts/:id` | Elimina post (admin) |
| DELETE | `/forum/replies/:id` | Elimina risposta (admin) |
| POST | `/auth/register` | Registrazione utente |
| POST | `/auth/login` | Login utente |
| GET | `/users` | Lista utenti (admin) |
| DELETE | `/users/:id` | Elimina utente (admin) |

### Schema Database

```
users              games               forum_categories
─────────          ──────────          ────────────────
id                 id                  id
email              title               name
password_hash      description         icon
name               subject             desc
created_at         type (ENUM)
                   author              forum_posts
scores             data (JSON)         ───────────
──────             plays               id
id                 created_at          category_id → forum_categories
game_id → games                        author
player_name        forum_replies       title
score              ─────────────       body
created_at         id                  reply_count
                   post_id → posts     views
                   author              created_at
                   body
                   created_at
```

---

## 🚀 Avvio

### Requisiti
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) installato

### Prima volta
```bash
git clone https://github.com/TUO-USERNAME/GameUp.git
cd GameUp
docker-compose up --build
```

Il primo avvio scarica le immagini Docker e inizializza il database — richiede qualche minuto.

### Volte successive
```bash
docker-compose up -d        # avvia in background
docker-compose down         # ferma (dati conservati)
```

### Accesso

| Cosa | URL locale | URL in LAN |
|------|-----------|------------|
| Frontend (mondo) | http://localhost | http://\<IP\>:80 |
| Backend API | http://localhost:8080 | http://\<IP\>:8080 |
| WebSocket | ws://localhost:3000 | ws://\<IP\>:3000 |

Per trovare l'IP del server in LAN:
```bash
hostname -I        # Linux/Mac
ipconfig           # Windows (cerca "Indirizzo IPv4")
```

---

## 🔧 Pannello Admin

Accessibile da `http://localhost/admin.html`

**Credenziali predefinite:**
```
Email:    damasco.simon@polotecnologicoimperiese.it
Password: admin2024
```

> ⚠️ Cambia la password modificando la variabile `ADMIN_PASSWORD` nel file `frontend/admin.html`

Il pannello permette di:
- Visualizzare statistiche (giochi, post, risposte, utenti)
- Eliminare giochi non idonei
- Eliminare post e risposte del forum
- Gestire gli account utenti

---

## 🗄️ Comandi utili

```bash
# Log in tempo reale
docker-compose logs -f backend
docker-compose logs -f ws-server

# Accedere al database MySQL
docker exec -it gameup-db-1 mysql -ugameup_user -pgameup_pass gameup

# Query utili dentro MySQL
SHOW TABLES;
SELECT id, title, subject, type FROM games;
SELECT id, email, name FROM users;
SELECT id, title, reply_count FROM forum_posts ORDER BY created_at DESC;

# Rebuild di un singolo container (es. dopo modifica frontend)
docker-compose up -d --build frontend

# ⚠️ Reset completo (ELIMINA TUTTI I DATI)
docker-compose down -v
```

---

## 📁 Persistenza dati

I dati sono persistenti in due livelli:

1. **MySQL** (quando Docker è attivo) — dati sul server, condivisi tra tutti gli utenti
2. **localStorage del browser** (fallback offline) — dati locali, visibili solo su quel browser

Quando il server non è raggiungibile, il frontend salva automaticamente in localStorage e sincronizza al prossimo avvio di Docker.

---

## 👤 Autore

Progetto sviluppato da **Damasco Simon** — classe [CLASSE]
Polo Tecnologico Imperiese — A.S. 2024/2025
Materia: Sistemi e Reti
