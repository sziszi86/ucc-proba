# UCC Event Management & Helpdesk System

Event management system with AI-powered helpdesk functionality.

## Features

- Event management (Create, Read, Update, Delete)
- JWT Authentication with MFA support
- Password reset functionality
- **Smart Hybrid Helpdesk Chatbot** (Works offline with smart keyword matching + optional Gemini AI integration)
- Helpdesk agent interface
- Web Voice Support (Speech-to-Text & Text-to-Speech)
- OWASP Top 10 security implementation
- RESTful API
- Modern Vue.js 3 frontend (Tailwind CSS v4)

## Tech Stack

### Backend
- Laravel 10.50.2
- PHP 8.1.33
- JWT Authentication
- Google 2FA (MFA)
- SQLite (development) / MySQL (production)

### Frontend
- Vue.js 3
- TypeScript
- Pinia (State Management)
- Vue Router
- Axios

## Installation

### Backend Setup

```bash
cd backend

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Generate JWT secret
php artisan jwt:secret

# Create database file (SQLite)
touch database/database.sqlite

# Run migrations
php artisan migrate

# Start development server
php artisan serve
```

The backend will be available at `http://localhost:8000`

### Frontend Setup

```bash
cd frontend

# Install dependencies
npm install

# Start development server
npm run dev
```

The frontend will be available at `http://localhost:5173`

## API Documentation

OpenAPI/Swagger documentation is available at:
- File: `backend/storage/api-docs/api-docs.yaml`
- Format: OpenAPI 3.0.0

Import this file to Postman or Swagger UI to explore the API endpoints.

## Authentication

### Default Credentials
For testing purposes, you can use the following accounts:

**Regular User:**
- Email: `test@example.com`
- Password: `password`

**Helpdesk Agent:**
- Email: `agent@example.com`
- Password: `password`

### Login API
```bash
POST /api/auth/login
{
  "email": "test@example.com",
  "password": "password"
}
```

### MFA (if enabled)
```bash
POST /api/auth/verify-mfa
{
  "user_id": 1,
  "email": "test@example.com",
  "password": "password",
  "code": "123456"
}
```

## Bonus Features Implemented

- ✅ **MFA (Multi-Factor Authentication):** Google Authenticator support with QR code setup.
- ✅ **Web Voice Support:** Speech-to-Text (Microphone) and Text-to-Speech (Voice Mode) in the Helpdesk.
- ✅ **OWASP Top 10 Protections:** Security headers and secure coding practices.


## Environment Variables

### Backend (.env)
```
APP_NAME="UCC Event Management"
APP_URL=http://localhost:8000
DB_CONNECTION=sqlite
JWT_SECRET=<generated-secret>
```

### Frontend (.env)
```
VITE_API_URL=http://localhost:8000/api
```

## Security Features

- JWT token-based authentication
- Multi-Factor Authentication (Google Authenticator)
- Password reset with email verification
- CORS protection
- Rate limiting
- Security headers (XSS, Clickjacking protection)
- Input validation and sanitization
- SQL injection prevention (Eloquent ORM)
- HTTPS support (production)

## Project Structure

### Backend (Service Layer Architecture)
```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       ├── AuthController.php
│   │   │       ├── EventController.php
│   │   │       ├── ChatController.php
│   │   │       └── HelpdeskController.php
│   │   └── Middleware/
│   │       └── SecurityHeaders.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Event.php
│   │   ├── Chat.php
│   │   └── ChatMessage.php
│   └── Services/
│       └── ChatAIService.php
├── database/
│   └── migrations/
├── routes/
│   └── api.php
└── storage/
    └── api-docs/
        └── api-docs.yaml
```

### Frontend (Feature-Based Architecture)
```
frontend/
├── src/
│   ├── features/
│   │   ├── auth/
│   │   ├── events/
│   │   ├── helpdesk/
│   │   └── helpdesk-agent/
│   ├── services/
│   │   ├── api.ts
│   │   ├── authService.ts
│   │   ├── eventService.ts
│   │   └── chatService.ts
│   ├── stores/
│   │   └── auth.ts
│   ├── router/
│   │   └── index.ts
│   └── App.vue
└── package.json
```

## API Endpoints

### Authentication
- `POST /api/auth/login` - Login
- `POST /api/auth/verify-mfa` - Verify MFA
- `POST /api/auth/logout` - Logout
- `GET /api/auth/me` - Get current user
- `POST /api/auth/forgot-password` - Request password reset
- `POST /api/auth/reset-password` - Reset password
- `POST /api/auth/enable-mfa` - Enable MFA
- `POST /api/auth/confirm-mfa` - Confirm MFA setup
- `POST /api/auth/disable-mfa` - Disable MFA

### Events
- `GET /api/events` - List all user events
- `POST /api/events` - Create event
- `GET /api/events/{id}` - Get event details
- `PUT /api/events/{id}` - Update event
- `DELETE /api/events/{id}` - Delete event

### Chat (Helpdesk)
- `GET /api/chats` - List all user chats
- `POST /api/chats` - Create new chat
- `GET /api/chats/{id}` - Get chat with messages
- `POST /api/chats/{id}/messages` - Send message
- `DELETE /api/chats/{id}` - Delete chat

### Helpdesk (Agents Only)
- `GET /api/helpdesk/chats` - List all chats
- `GET /api/helpdesk/chats/{id}` - Get chat details
- `POST /api/helpdesk/chats/{id}/assign` - Assign chat to agent
- `POST /api/helpdesk/chats/{id}/messages` - Send agent message
- `POST /api/helpdesk/chats/{id}/close` - Close chat

## Database Schema

### Users
- id, name, email, password
- is_helpdesk_agent (boolean)
- mfa_enabled (boolean)
- mfa_secret (encrypted)

### Events
- id, user_id (FK), title, occurrence (datetime), description

### Chats
- id, user_id (FK), agent_id (FK), status, needs_human

### Chat Messages
- id, chat_id (FK), sender_id (FK), message, sender_type

## Testing

### Backend Tests
```bash
cd backend
php artisan test
```

### Frontend Tests
```bash
cd frontend
npm run test
```

## Deployment

### Production Environment
- Webserver: Nginx + PHP-FPM
- Database: MySQL 8.0+ or PostgreSQL
- SSL: Let's Encrypt (HTTPS)
- Cache: Redis
- Queue: Laravel Queue with Redis

### Environment Setup
1. Update `.env` with production values
2. Set `APP_DEBUG=false`
3. Set `APP_ENV=production`
4. Configure database credentials
5. Run `php artisan config:cache`
6. Run `php artisan route:cache`
7. Build frontend: `npm run build`

## Documentation

- **Presentation**: `PRESENTATION.md` - Detailed project documentation
- **API Docs**: `backend/storage/api-docs/api-docs.yaml` - OpenAPI specification

## Demo Video

Watch the project demonstration and overview:
- **YouTube**: https://youtu.be/Ml0goCglM7k?si=_pyQeL8mUqd1ouSS

