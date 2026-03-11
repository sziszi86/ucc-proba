# UCC Event Management & Helpdesk System - Prezentáció

## Tartalomjegyzék
1. [Megközelítések](#megközelítések)
2. [Javasolt megoldás](#javasolt-megoldás)
3. [Alkalmazott eszközök](#alkalmazott-eszközök)
4. [Felhasznált források](#felhasznált-források)
5. [Tervezés és architektúra](#tervezés-és-architektúra)

---

## 1. Megközelítések

### 1.1 Architektúra választás
A projekt során három fő architektúrát mérlegeltünk:

**Monolitikus architektúra:**
- Előnyök: Egyszerűbb fejlesztés kezdeti fázisban, könnyebb debuggolás
- Hátrányok: Nehezebben skálázható, korlátozott technológiai választás

**Microservices architektúra:**
- Előnyök: Független sk álázhatóság, technológiai függetlenség
- Hátrányok: Komplexebb infrastruktúra, nehezebb tesztelés és debugging

**Választott: Szeparált Backend-Frontend architektúra (Hybrid):**
- A backend és frontend teljesen különválasztott
- RESTful API kommunikáció HTTP protokollon keresztül
- Modern, skálázható, de nem túlkomplexített megoldás
- Egyszerű deployment és karbantartás

### 1.2 Adatbázis választás
**Relációs vs NoSQL:**
- A projekt adatstruktúrája jól definiált relációkkal rendelkezik (user-event, user-chat, chat-message)
- Választott: **SQLite** (fejlesztéshez), könnyen migrálható MySQL/PostgreSQL-re production környezetben
- Relációs integritás és ACID tulajdonságok fontosak az események és chat üzenetek tárolásához

### 1.3 Authentikáció módszer
**Összehasonlítás:**
- **Session-based auth**: Szerveroldali session tárolás, több szerver esetén komplikált
- **OAuth 2.0**: Külső provider-ek esetén ideális, túlzottan komplex belső rendszerhez
- **JWT (JSON Web Tokens)**: Választott megoldás
  - Stateless, skálázható
  - Mobil applikációkhoz is ideális
  - Lejárati idő és refresh token támogatás
  - Modern biztonsági standard

### 1.4 MFA (Multi-Factor Authentication) implementáció
**Google Authenticator vs SMS-based:**
- **SMS-based**: Költséges, lassabb, kevésbé biztonságos
- **Google Authenticator (TOTP)**: Választott megoldás
  - Ingyenes
  - Offline működés
  - Iparági standard
  - Felhasználóbarát QR kód beállítás

### 1.5 AI Chat megközelítés (Smart Hybrid)
A projekt egy egyedi hibrid megoldást használ a megbízhatóság és a privát szféra védelme érdekében:

**Működési elv:**
1.  **Helyi intelligencia (Offline/Default):** Egy kiterjesztett kulcsszó- és környezetfüggő algoritmus, amely azonnal válaszol a leggyakoribb kérdésekre (eseménykezelés, MFA, login) API hívás nélkül. Ez garantálja, hogy a rendszer mindig működőképes és "okos" marad, külső kulcsok nélkül is.
2.  **LLM Integráció (Opcionális):** Ha a `.env` fájlban be van állítva a `GEMINI_API_KEY`, a rendszer automatikusan a Google Gemini 1.5 Flash modellt használja a komplexebb kérdések megválaszolására.
3.  **Human Escalation:** Mindkét módban a rendszer figyeli az ügynöki kéréseket, és szükség esetén automatikusan átadja a chatet egy emberi operátornak.

**Előnyök:**
- **Adatbiztonság:** Nem kötelező külső félnek adatot küldeni.
- **Költséghatékonyság:** Az alapműködés ingyenes.
- **Reliability:** Nincs külső függőség az alapfunkciókhoz.

---

## 2. Javasolt megoldás

### 2.1 Rendszer áttekintés
A rendszer egy full-stack webalkalmazás, amely eseménykezelést és AI-támogatott helpdesk funkciót biztosít.

**Főbb komponensek:**
1. **Backend (Laravel 10)**
   - RESTful API
   - JWT authentikáció
   - Service layer architektúra
   - OWASP Top 10 biztonsági védelme

2. **Frontend (Vue.js 3)**
   - TypeScript támogatás
   - Feature-based struktúra
   - Pinia state management
   - Vue Router navigáció

3. **Adatbázis (SQLite/MySQL)**
   - Users, Events, Chats, Chat Messages táblák
   - Relációs integritás és indexelés

### 2.2 Funkcionális követelmények megvalósítása

#### 2.2.1 Event Management
**CRUD műveletek:**
- **Create**: Új esemény létrehozása címmel, dátummal és opcio nális leírással
- **Read**: Felhasználó saját eseményeinek listázása időrendi sorrendben
- **Update**: Esemény leírásának módosítása
- **Delete**: Esemény törlése

**Validáció:**
- Title: Kötelező, maximum 255 karakter
- Occurrence: Kötelező, érvényes dátum formátum
- Description: Opcionális, text típus

**Biztonság:**
- Csak saját események elérése és módosítása
- JWT token alapú authentikáció
- Input sanitization XSS ellen

#### 2.2.2 Authentikáció és biztonság
**Login folyamat:**
1. Email + jelszó validáció
2. MFA ellenőrzés (ha engedélyezve)
3. JWT token generálás
4. Token tárolás localStorage-ban (frontend)
5. Automatikus token refresh

**Password Reset:**
1. Email megadása
2. Reset link küldése emailben
3. Token validáció
4. Új jelszó beállítása (minimum 8 karakter, confirmation)

**MFA Setup:**
1. QR kód generálás (Google Authenticator)
2. Secret key tárolás titkosítva
3. Teszt kód verifikáció
4. MFA aktiválás

**OWASP Top 10 védelem:**
- **Injection**: Laravel ORM Eloquent (parameterized queries)
- **Broken Authentication**: JWT + MFA, secure password hashing (bcrypt)
- **Sensitive Data Exposure**: HTTPS, titkosított adatbázis mezők
- **XML External Entities**: N/A (JSON API)
- **Broken Access Control**: Role-based access (is_helpdesk_agent)
- **Security Misconfiguration**: Security headers (X-Frame-Options, CSP, XSS-Protection)
- **Cross-Site Scripting**: Input sanitization, output encoding
- **Insecure Deserialization**: N/A (JSON)
- **Using Components with Known Vulnerabilities**: Composer/NPM audit
- **Insufficient Logging**: Laravel logging, error tracking

#### 2.2.3 Helpdesk System
**User Interface:**
- Chat létrehozása
- Üzenetek küldése
- AI válaszok valós időben
- Emberi agent kérés

**AI Funkcionalitás:**
- Kulcsszó alapú válaszgenerálás
- Gyakori kérdések (FAQ) válaszok
- Automatikus emberi átvétel detektálás
- Kontextus-érzékeny válaszok

**Agent Interface:**
- Összes nyitott chat megtekintése
- Chat átvétele (assign)
- Válaszok küldése
- Chat lezárása

**Chat státuszok:**
- **open**: Új chat, AI válaszol
- **in_progress**: Agent átvette
- **closed**: Megoldva és lezárva

### 2.3 Adatmodell

**Users tábla:**
```
- id (PK)
- name
- email (unique)
- password (hashed)
- is_helpdesk_agent (boolean)
- mfa_enabled (boolean)
- mfa_secret (encrypted)
- timestamps
```

**Events tábla:**
```
- id (PK)
- user_id (FK -> users.id, cascade delete)
- title
- occurrence (datetime)
- description (nullable)
- timestamps
```

**Chats tábla:**
```
- id (PK)
- user_id (FK -> users.id, cascade delete)
- agent_id (FK -> users.id, nullable, set null on delete)
- status (enum: open, in_progress, closed)
- needs_human (boolean)
- timestamps
```

**Chat_Messages tábla:**
```
- id (PK)
- chat_id (FK -> chats.id, cascade delete)
- sender_id (FK -> users.id, nullable, set null on delete)
- message (text)
- sender_type (enum: user, agent, ai)
- timestamps
```

### 2.4 API Endpoints

**Authentication:**
- POST `/api/auth/login` - Login
- POST `/api/auth/verify-mfa` - MFA verification
- POST `/api/auth/forgot-password` - Request password reset
- POST `/api/auth/reset-password` - Reset password
- POST `/api/auth/logout` - Logout (auth required)
- GET `/api/auth/me` - Get current user (auth required)
- POST `/api/auth/enable-mfa` - Enable MFA (auth required)
- POST `/api/auth/confirm-mfa` - Confirm MFA setup (auth required)
- POST `/api/auth/disable-mfa` - Disable MFA (auth required)

**Events:**
- GET `/api/events` - List user events (auth required)
- POST `/api/events` - Create event (auth required)
- GET `/api/events/{id}` - Get event (auth required)
- PUT `/api/events/{id}` - Update event (auth required)
- DELETE `/api/events/{id}` - Delete event (auth required)

**Chat:**
- GET `/api/chats` - List user chats (auth required)
- POST `/api/chats` - Create chat (auth required)
- GET `/api/chats/{id}` - Get chat with messages (auth required)
- POST `/api/chats/{id}/messages` - Send message (auth required)
- DELETE `/api/chats/{id}` - Delete chat (auth required)

**Helpdesk (Agent only):**
- GET `/api/helpdesk/chats` - List all chats (auth + agent required)
- GET `/api/helpdesk/chats/{id}` - Get chat (auth + agent required)
- POST `/api/helpdesk/chats/{id}/assign` - Assign chat (auth + agent required)
- POST `/api/helpdesk/chats/{id}/messages` - Send agent message (auth + agent required)
- POST `/api/helpdesk/chats/{id}/close` - Close chat (auth + agent required)

---

## 3. Alkalmazott eszközök

### 3.1 Backend Technologies
| Eszköz | Verzió | Funkció |
|--------|--------|---------|
| **Laravel** | 10.50.2 | PHP framework, MVC architektúra, ORM |
| **PHP** | 8.1.33 | Programozási nyelv |
| **Composer** | 2.9.5 | Dependency management |
| **JWT Auth** | 2.3.0 | JSON Web Token authentikáció |
| **Google2FA Laravel** | 2.3.0 | Multi-factor authentication |
| **L5-Swagger** | 8.6.5 | OpenAPI/Swagger dokumentáció |
| **SQLite** | 3.x | Adatbázis (fejlesztés) |
| **Guzzle HTTP** | 7.10.0 | HTTP client (external API calls) |

### 3.2 Frontend Technologies
| Eszköz | Verzió | Funkció |
|--------|--------|---------|
| **Vue.js** | 3.22.0 | Progressive JavaScript framework |
| **TypeScript** | Latest | Type-safe JavaScript |
| **Vite** | Latest | Build tool, development server |
| **Pinia** | Latest | State management |
| **Vue Router** | Latest | Client-side routing |
| **Axios** | Latest | HTTP client |
| **ESLint** | Latest | Code linting |

### 3.3 Development Tools
| Eszköz | Funkció |
|--------|---------|
| **Git** | Version control |
| **GitHub** | Code repository, collaboration |
| **Postman** | API testing |
| **VS Code** | IDE |
| **Chrome DevTools** | Frontend debugging |
| **Laravel Artisan** | CLI tooling |
| **npm** | Frontend package management |

### 3.4 Security Tools & Practices
- **CORS Middleware** - Cross-Origin Resource Sharing
- **Rate Limiting** - API request throttling
- **Security Headers** - XSS, Clickjacking protection
- **CSRF Protection** - Laravel built-in
- **bcrypt** - Password hashing
- **Input Validation** - Laravel Validator
- **SQL Injection Prevention** - Laravel Eloquent ORM
- **HTTPS** - Secure communication (production)

---

## 4. Felhasznált források

### 4.1 Hivatalos dokumentációk
1. **Laravel Documentation** - https://laravel.com/docs/10.x
   - Authentication, Authorization, Database, Eloquent ORM

2. **Vue.js Documentation** - https://vuejs.org/guide/
   - Composition API, Components, Reactivity

3. **TypeScript Documentation** - https://www.typescriptlang.org/docs/
   - Type system, Interfaces, Generics

4. **Pinia Documentation** - https://pinia.vuejs.org/
   - State management patterns

5. **JWT.io** - https://jwt.io/introduction
   - JWT token structure, best practices

6. **OpenAPI Specification** - https://swagger.io/specification/
   - API documentation standards

### 4.2 Security Resources
1. **OWASP Top 10** - https://owasp.org/www-project-top-ten/
   - Web application security risks

2. **OWASP Cheat Sheet Series** - https://cheatsheetseries.owasp.org/
   - Authentication, Session Management, Input Validation

3. **Google Authenticator TOTP** - https://github.com/google/google-authenticator
   - Time-based One-Time Password algorithm

4. **Laravel Security Best Practices** - Laravel official guides
   - CSRF, XSS, SQL Injection prevention

### 4.3 Package Documentation
1. **tymon/jwt-auth** - https://github.com/tymondesigns/jwt-auth
   - JWT implementation in Laravel

2. **pragmarx/google2fa-laravel** - https://github.com/antonioribeiro/google2fa
   - Google2FA integration

3. **darkaonline/l5-swagger** - https://github.com/DarkaOnLine/L5-Swagger
   - Swagger/OpenAPI in Laravel

4. **axios** - https://axios-http.com/docs/intro
   - HTTP client for Vue.js

### 4.4 Tutorials & Learning Resources
1. **Laravel Daily** - YouTube channel
   - Best practices, tips & tricks

2. **Vue Mastery** - https://www.vuemastery.com/
   - Vue.js 3 Composition API, TypeScript

3. **Laracasts** - https://laracasts.com/
   - Laravel advanced topics

4. **MDN Web Docs** - https://developer.mozilla.org/
   - Web standards, APIs, security

### 4.5 Design & UX Resources
1. **Material Design** - https://material.io/design
   - UI/UX principles

2. **Vue.js Community Style Guide** - https://vuejs.org/style-guide/
   - Component structure, naming conventions

---

## 5. Tervezés és architektúra

### 5.1 Rendszer architektúra diagram

```
┌─────────────────────────────────────────────────────────────┐
│                         FRONTEND                             │
│  ┌───────────┐  ┌───────────┐  ┌───────────┐  ┌──────────┐ │
│  │   Login   │  │  Events   │  │ Helpdesk  │  │  Agent   │ │
│  │   View    │  │   View    │  │   View    │  │   View   │ │
│  └─────┬─────┘  └─────┬─────┘  └─────┬─────┘  └────┬─────┘ │
│        │              │              │             │        │
│        └──────────────┴──────────────┴─────────────┘        │
│                       │                                      │
│              ┌────────▼────────┐                             │
│              │  Vue Router     │                             │
│              └────────┬────────┘                             │
│                       │                                      │
│              ┌────────▼────────┐                             │
│              │   Pinia Store   │                             │
│              └────────┬────────┘                             │
│                       │                                      │
│              ┌────────▼────────┐                             │
│              │  API Services   │                             │
│              │   (Axios)       │                             │
│              └────────┬────────┘                             │
└───────────────────────┼──────────────────────────────────────┘
                        │
                        │ HTTP/HTTPS + JWT
                        │
┌───────────────────────▼──────────────────────────────────────┐
│                      BACKEND API                             │
│                                                              │
│  ┌────────────────────────────────────────────────────────┐ │
│  │              Middleware Layer                          │ │
│  │  ┌──────┐  ┌─────────┐  ┌────────┐  ┌──────────────┐ │ │
│  │  │ CORS │  │  Rate   │  │ JWT    │  │   Security   │ │ │
│  │  │      │  │ Limit   │  │ Auth   │  │   Headers    │ │ │
│  │  └──────┘  └─────────┘  └────────┘  └──────────────┘ │ │
│  └────────────────────────────────────────────────────────┘ │
│                         │                                    │
│  ┌─────────────────────▼───────────────────────────────────┐│
│  │                  Router (routes/api.php)                ││
│  └─────────────────────┬───────────────────────────────────┘│
│                        │                                     │
│  ┌─────────────────────▼───────────────────────────────────┐│
│  │              Controllers Layer                          ││
│  │  ┌──────────┐  ┌────────┐  ┌────────┐  ┌────────────┐ ││
│  │  │   Auth   │  │ Event  │  │  Chat  │  │  Helpdesk  │ ││
│  │  │Controller│  │Controller│ │Controller│ │ Controller │ ││
│  │  └─────┬────┘  └────┬───┘  └────┬───┘  └─────┬──────┘ ││
│  └────────┼────────────┼───────────┼─────────────┼────────┘│
│           │            │           │             │          │
│  ┌────────▼────────────▼───────────▼─────────────▼────────┐│
│  │                 Service Layer                           ││
│  │         ┌───────────────────────────┐                   ││
│  │         │    ChatAIService          │                   ││
│  │         └───────────────────────────┘                   ││
│  └─────────────────────┬───────────────────────────────────┘│
│                        │                                     │
│  ┌─────────────────────▼───────────────────────────────────┐│
│  │                 Models Layer                            ││
│  │  ┌──────┐  ┌───────┐  ┌──────┐  ┌─────────────────┐   ││
│  │  │ User │  │ Event │  │ Chat │  │  ChatMessage    │   ││
│  │  │Model │  │ Model │  │Model │  │     Model       │   ││
│  │  └──┬───┘  └───┬───┘  └──┬───┘  └────────┬────────┘   ││
│  └─────┼──────────┼─────────┼───────────────┼─────────────┘│
└────────┼──────────┼─────────┼───────────────┼──────────────┘
         │          │         │               │
         │ Eloquent ORM       │               │
         │          │         │               │
┌────────▼──────────▼─────────▼───────────────▼──────────────┐
│                      DATABASE                               │
│  ┌───────┐  ┌────────┐  ┌───────┐  ┌──────────────────┐   │
│  │ users │  │ events │  │ chats │  │  chat_messages   │   │
│  └───────┘  └────────┘  └───────┘  └──────────────────┘   │
│                     SQLite / MySQL                          │
└─────────────────────────────────────────────────────────────┘
```

### 5.2 Database Entity Relationship Diagram (ERD)

```
┌─────────────────────────┐
│         users           │
├─────────────────────────┤
│ PK  id                  │
│     name                │
│ UK  email               │
│     password            │
│     is_helpdesk_agent   │
│     mfa_enabled         │
│     mfa_secret          │
│     timestamps          │
└───────┬─────────────────┘
        │ 1
        │
        │ owns many
        │
        ├──────────────────────────────┐
        │                              │
        │ N                            │ N
┌───────▼─────────────┐       ┌────────▼────────────┐
│      events         │       │       chats         │
├─────────────────────┤       ├─────────────────────┤
│ PK  id              │       │ PK  id              │
│ FK  user_id         │       │ FK  user_id         │
│     title           │       │ FK  agent_id        │
│     occurrence      │       │     status          │
│     description     │       │     needs_human     │
│     timestamps      │       │     timestamps      │
└─────────────────────┘       └────────┬────────────┘
                                       │ 1
                                       │
                                       │ has many
                                       │
                                       │ N
                              ┌────────▼────────────┐
                              │  chat_messages      │
                              ├─────────────────────┤
                              │ PK  id              │
                              │ FK  chat_id         │
                              │ FK  sender_id       │
                              │     message         │
                              │     sender_type     │
                              │     timestamps      │
                              └─────────────────────┘
```

**Relationships:**
- User 1:N Event (One user has many events)
- User 1:N Chat (One user has many chats)
- User 1:N Chat as Agent (One agent handles many chats)
- Chat 1:N ChatMessage (One chat has many messages)
- User 1:N ChatMessage (One user sends many messages)

### 5.3 Application Flow - User Login

```
┌──────┐                                                    ┌────────┐
│ User │                                                    │Backend │
└──┬───┘                                                    └───┬────┘
   │                                                            │
   │  1. Enter email & password                                │
   ├──────────────────────────────────────────────────────────►│
   │                                                            │
   │                     2. Validate credentials                │
   │                        Hash password                       │
   │                        Check database                      │
   │◄───────────────────────────────────────────────────────────┤
   │                                                            │
   │  3. MFA Required?                                          │
   │◄───────────────────────────────────────────────────────────┤
   │   (if mfa_enabled = true)                                  │
   │                                                            │
   │  4. Enter MFA Code                                         │
   ├──────────────────────────────────────────────────────────►│
   │                                                            │
   │                     5. Verify TOTP code                    │
   │                        Compare with mfa_secret             │
   │◄───────────────────────────────────────────────────────────┤
   │                                                            │
   │  6. Return JWT Token + User Data                           │
   │◄───────────────────────────────────────────────────────────┤
   │                                                            │
   │  7. Store token in localStorage                            │
   │     Navigate to Dashboard                                  │
   │                                                            │
```

### 5.4 Application Flow - Helpdesk Chat

```
┌──────┐                                                    ┌────────┐
│ User │                                                    │Backend │
└──┬───┘                                                    └───┬────┘
   │                                                            │
   │  1. Create New Chat                                        │
   ├──────────────────────────────────────────────────────────►│
   │                                                            │
   │                     2. Create chat record                  │
   │                        status = 'open'                     │
   │◄───────────────────────────────────────────────────────────┤
   │                                                            │
   │  3. Send Message: "How do I create an event?"              │
   ├──────────────────────────────────────────────────────────►│
   │                                                            │
   │                     4. Save user message                   │
   │                     5. ChatAIService processes message     │
   │                        - Detect keywords                   │
   │                        - Generate response                 │
   │                     6. Save AI message                     │
   │◄───────────────────────────────────────────────────────────┤
   │                                                            │
   │  7. Display AI Response                                    │
   │                                                            │
   │  8. Send: "I need to speak to a human"                     │
   ├──────────────────────────────────────────────────────────►│
   │                                                            │
   │                     9. Detect "human" keyword              │
   │                        Set needs_human = true              │
   │                        Save AI message                     │
   │◄───────────────────────────────────────────────────────────┤
   │                                                            │
   │  10. Display: "Connecting you to an agent..."              │
   │                                                            │
   │  [Agent sees chat in dashboard]                            │
   │                                                            │
   │  11. Agent assigns chat                                    │
   │      status = 'in_progress'                                │
   │                                                            │
   │  12. Agent sends message                                   │
   │◄───────────────────────────────────────────────────────────┤
   │                                                            │
   │  13. User receives agent message                           │
   │                                                            │
   │  14. Chat continues...                                     │
   │                                                            │
   │  15. Agent closes chat                                     │
   │      status = 'closed'                                     │
   │                                                            │
```

### 5.6 User Journey Maps

#### 5.6.1 Regular User Journey
```
1. Login → 2. Dashboard → 3. Events List → 4. Create Event
                    ↓
              5. Helpdesk → 6. Chat with AI → 7. Resolve or Escalate
```

#### 5.6.2 Helpdesk Agent Journey
```
1. Login → 2. Agent Dashboard → 3. View Open Chats → 4. Assign Chat
                                                          ↓
                                               5. Reply to User ←─┐
                                                          ↓       │
                                               6. Continue Chat ──┘
                                                          ↓
                                               7. Close Chat
```

### 5.7 Security Flow

```
┌─────────────────────────────────────────────────────────┐
│              Client Request (Frontend)                  │
└───────────────────────┬─────────────────────────────────┘
                        │
                        │ HTTPS
                        ↓
┌─────────────────────────────────────────────────────────┐
│                  CORS Middleware                        │
│  - Check Origin                                         │
│  - Validate Headers                                     │
└───────────────────────┬─────────────────────────────────┘
                        │
                        ↓
┌─────────────────────────────────────────────────────────┐
│              Security Headers Middleware                │
│  - X-Frame-Options: DENY                                │
│  - X-XSS-Protection: 1                                  │
│  - Content-Security-Policy                              │
│  - Strict-Transport-Security                            │
└───────────────────────┬─────────────────────────────────┘
                        │
                        ↓
┌─────────────────────────────────────────────────────────┐
│              Rate Limiting Middleware                   │
│  - Throttle requests (60/minute)                        │
└───────────────────────┬─────────────────────────────────┘
                        │
                        ↓
┌─────────────────────────────────────────────────────────┐
│               JWT Authentication                        │
│  - Validate Token                                       │
│  - Check Expiry                                         │
│  - Load User                                            │
└───────────────────────┬─────────────────────────────────┘
                        │
                        ↓
┌─────────────────────────────────────────────────────────┐
│            Input Validation & Sanitization              │
│  - Laravel Validator                                    │
│  - XSS Prevention                                       │
└───────────────────────┬─────────────────────────────────┘
                        │
                        ↓
┌─────────────────────────────────────────────────────────┐
│             Controller Logic                            │
│  - Authorization Check                                  │
│  - Business Logic                                       │
└───────────────────────┬─────────────────────────────────┘
                        │
                        ↓
┌─────────────────────────────────────────────────────────┐
│               Database Query (ORM)                      │
│  - Parameterized Queries (SQL Injection Protection)    │
└───────────────────────┬─────────────────────────────────┘
                        │
                        ↓
┌─────────────────────────────────────────────────────────┐
│              Response + Security Headers                │
└─────────────────────────────────────────────────────────┘
```

---

## 6. Deployment és Környezet

### 6.1 Fejlesztői Környezet
- **OS**: macOS / Linux / Windows
- **PHP**: 8.1+
- **Composer**: 2.x
- **Node.js**: 18.x+
- **npm**: 9.x+
- **Database**: SQLite (dev), MySQL (production)

### 6.2 Production Környezet (javasolt)
- **Webserver**: Nginx + PHP-FPM
- **Database**: MySQL 8.0+ / PostgreSQL 14+
- **Cache**: Redis
- **Queue**: Laravel Queue with Redis
- **SSL**: Let's Encrypt (HTTPS)
- **Monitoring**: Laravel Telescope, Sentry

### 6.3 Environment Variables
```env
# Application
APP_NAME="UCC Event Management"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ucc_events
DB_USERNAME=db_user
DB_PASSWORD=secure_password

# JWT
JWT_SECRET=your-generated-secret-key
JWT_TTL=60

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_FROM_ADDRESS=noreply@yourdomain.com
```

---

## 7. Tesztelés

### 7.1 Backend Tesztelés
- **Unit Tests**: Laravel PHPUnit
  - Model relationships
  - Service layer logic
  - ChatAIService responses

- **Feature Tests**:
  - API endpoints
  - Authentication flow
  - Event CRUD operations
  - Chat functionality

- **Security Tests**:
  - OWASP Top 10 vulnerabilities
  - Input validation
  - Authorization checks

### 7.2 Frontend Tesztelés
- **Unit Tests**: Vitest
  - Vue components
  - Pinia stores
  - Service functions

- **E2E Tests**: Cypress (javasolt)
  - Login flow
  - Event management
  - Helpdesk chat

### 7.3 Manual Testing
- **Postman Collection**: API endpoints tesztelése
- **Browser Testing**: Chrome, Firefox, Safari, Edge
- **Mobile Responsive**: Various screen sizes

---

## 8. Jövőbeli Fejlesztések (Roadmap)

### 8.1 Fázis 2 (Short-term)
- [ ] Email notifications (event reminders)
- [ ] Real-time chat updates (WebSockets)
- [ ] File upload support in chat
- [ ] Event categories and tags
- [ ] Advanced search and filtering

### 8.2 Fázis 3 (Medium-term)
- [ ] Mobile application (React Native / Flutter)
- [ ] Calendar integration (Google Calendar, Outlook)
- [ ] Analytics dashboard for agents
- [ ] Multi-language support (i18n)

### 8.3 Fázis 4 (Long-term)
- [ ] Advanced AI integration (OpenAI GPT)
- [ ] Video call support for helpdesk
- [ ] Recurring events
- [ ] Team collaboration features
- [ ] Third-party integrations (Slack, Teams)

---

## 9. Belépési adatok teszteléshez 🔑

**Ügyfél (Regular User):**
- Email: `test@example.com`
- Jelszó: `password`

**Ügyfélszolgálati munkatárs (Agent):**
- Email: `agent@example.com`
- Jelszó: `password`

---

## 10. Konklúzió

A megvalósított rendszer teljesíti az összes funkcionális és nem-funkcionális követelményt:

✅ **Alapfunkciók:**
- Event Management (CRUD)
- JWT Authentikáció
- Password Reset
- MFA (Google Authenticator)

✅ **Haladó és Bónusz funkciók:**
- **Web Voice Support (Bónusz)**: Beszédfelismerés és felolvasás
- AI-powered Helpdesk
- Agent Interface
- OWASP Top 10 védelem
- RESTful API
- Feature-based Frontend Architecture

✅ **Dokumentáció:**
- OpenAPI/Swagger API docs
- Részletes prezentáció
- Architektúra diagramok
- User journeys

✅ **Modern Tech Stack:**
- Laravel 10 + Vue.js 3
- TypeScript
- JWT + MFA
- Security best practices

A rendszer készen áll további fejlesztésekre, skálázásra és production környezetbe történő telepítésre.

---

**Projekt GitHub Repository:** [GitHub Link itt]
**API Dokumentáció:** `backend/storage/api-docs/api-docs.yaml`
**Frontend:** `frontend/src/`
**Backend:** `backend/app/`

---

## Demo Video

A projekt bemutatása és működése videón:
- **YouTube**: https://youtu.be/Ml0goCglM7k?si=_pyQeL8mUqd1ouSS

---

**Készítette:** [Név]
**Dátum:** 2026-03-06
**Projekt:** UCC Event Management & Helpdesk System
