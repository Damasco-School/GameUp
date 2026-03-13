# 🎮 GameUp: La Tua Piattaforma di Didattica Interattiva

**GameUp** è una web-app ispirata a Wordwall, progettata per trasformare l'apprendimento in un'esperienza coinvolgente. La piattaforma permette ai docenti di creare giochi didattici personalizzati partendo da template dinamici e agli studenti di sfidarsi in tempo reale.

Sviluppato come progetto per **Sistemi e Reti**, GameUp mette al centro la modularità dei servizi e l'efficienza del deployment tramite containerizzazione.

---

## 🚀 Architettura del Sistema (Docker Based)

Il cuore di **GameUp** risiede nella sua struttura a microservizi, orchestrata tramite **Docker Compose**. Questa scelta tecnica garantisce che l'app sia pronta all'uso su qualsiasi server locale senza conflitti di dipendenze.

### I Componenti:
1.  **GameUp-Web (Frontend):** Interfaccia utente costruita per essere rapida e intuitiva (HTML5/CSS3/JS).
2.  **GameUp-API (Backend):** Il motore logico che gestisce i template, i quiz e i salvataggi.
3.  **GameUp-DB (Database):** Un database relazionale per mantenere la persistenza di domande, utenti e classifiche.

---

## 🛠️ Stack Tecnologico

* **Virtualizzazione:** Docker & Docker Compose
* **Backend:** [Inserisci qui, es: Node.js / Express]
* **Frontend:** [Inserisci qui, es: Vanilla JS / Bootstrap]
* **Database:** [Inserisci qui, es: MySQL]
* **Networking:** Rete interna bridge per isolare il traffico dati dal traffico web.

---

## 📦 Installazione e Avvio Rapido

Per caricare **GameUp** sul server locale del laboratorio:

1.  **Clona la repository:**
    ```bash
    git clone [https://github.com/tuo-username/gameup.git](https://github.com/tuo-username/gameup.git)
    cd gameup
    ```

2.  **Configura le variabili d'ambiente:**
    Modifica il file `.env` inserendo le credenziali necessarie:
    ```env
    DB_USER=admin
    DB_PASSWORD=secret_password
    ```

3.  **Lancia il sistema:**
    ```bash
    docker-compose up -d --build
    ```

4.  **Gioca!**
    Apri il browser e digita l'IP del server (es. `http://192.168.x.x`) per accedere alla dashboard.

---

## 🎮 Funzionalità di GameUp

* **Template Engine:** Scegli tra Quiz, Vero/Falso o Abbinamenti.
* **Creator Mode:** Interfaccia drag-and-drop per i docenti.
* **Classifiche Live:** Visualizzazione immediata dei risultati migliori per ogni gioco.
* **Mobile Ready:** Progettato per funzionare perfettamente sugli smartphone degli studenti.

---

## 🛡️ Note Tecniche (Sistemi e Reti)

* **Docker Volumes:** Implementati per garantire che i quiz creati non vengano persi al riavvio dei container.
* **Port Forwarding:** Esposizione della sola porta HTTP standard per motivi di sicurezza di rete.
* **Scalabilità:** Grazie alla separazione tra logica e dati, è possibile scalare orizzontalmente il servizio API in caso di alto traffico in classe.

---

**Sviluppato da:** [Il Tuo Nome / Gruppo]  
**Classe:** [Tua Classe]  
**Anno Scolastico:** 2025/2026
