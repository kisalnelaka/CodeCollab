# CodeCollab - Interactive Real-Time Collaborative Coding Platform

A full-stack web application that provides a real-time, collaborative coding environment with GitHub integration.

## Project Overview

CodeCollab allows users to:
- Engage in live coding sessions with real-time collaboration
- Link GitHub repositories for version control
- Participate in gamified coding challenges and hackathons
- Track progress with leaderboards and badges

## Technology Stack

### Backend
- PHP (Laravel)
- MySQL
- Laravel Echo with Pusher for real-time communication

### Frontend
- JavaScript (React)
- Monaco Editor for code editing
- Chart.js for data visualization

### DevOps
- Docker for containerization
- GitHub Actions for CI/CD
- SSL for secure communication

## Features

1. **Real-Time Collaborative Editor**
   - Multiple users can edit code simultaneously
   - Syntax highlighting and multi-cursor functionality

2. **User Authentication & Profiles**
   - Secure sign-up/login system
   - GitHub repository linking

3. **Gamification**
   - Leaderboards, badges, and points
   - Live coding challenges and hackathons

4. **GitHub Integration**
   - Commit and track coding sessions to GitHub repositories
   - OAuth integration for secure authentication

5. **Responsive UI/UX**
   - Modern, mobile-first design
   - Interactive dashboards with real-time data visualizations

## Setup Instructions

### Backend Setup

1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/codecollab.git
   cd codecollab/backend
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Set up environment variables:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Configure the database in `.env`:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=codecollab
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. Set up GitHub OAuth credentials in `.env`:
   ```
   GITHUB_CLIENT_ID=your_github_client_id
   GITHUB_CLIENT_SECRET=your_github_client_secret
   GITHUB_REDIRECT_URI=http://localhost:8000/api/auth/github/callback
   ```

6. Run migrations:
   ```bash
   php artisan migrate
   ```

7. Start the development server:
   ```bash
   php artisan serve
   ```

### Frontend Setup (Coming Soon)

Instructions for setting up the React frontend will be provided in future updates.

## API Documentation

### Authentication Endpoints
- `POST /api/register` - Register a new user
- `POST /api/login` - Login a user
- `POST /api/logout` - Logout a user
- `GET /api/user` - Get authenticated user
- `POST /api/github-token` - Update GitHub token

### Project Endpoints
- `GET /api/projects` - List all projects
- `POST /api/projects` - Create a new project
- `GET /api/projects/{id}` - Get a specific project
- `PUT /api/projects/{id}` - Update a project
- `DELETE /api/projects/{id}` - Delete a project

### Coding Session Endpoints
- `GET /api/projects/{projectId}/coding-sessions` - List sessions for a project
- `POST /api/projects/{projectId}/coding-sessions` - Create a new session
- `GET /api/coding-sessions/{id}` - Get a specific session
- `PUT /api/coding-sessions/{id}` - Update a session
- `DELETE /api/coding-sessions/{id}` - Delete a session
- `POST /api/coding-sessions/{id}/join` - Join a session
- `POST /api/coding-sessions/{id}/leave` - Leave a session
- `POST /api/coding-sessions/{id}/update-content` - Update session content

### Challenge Endpoints
- `GET /api/challenges` - List all challenges
- `POST /api/challenges` - Create a new challenge
- `GET /api/challenges/{id}` - Get a specific challenge
- `PUT /api/challenges/{id}` - Update a challenge
- `DELETE /api/challenges/{id}` - Delete a challenge
- `POST /api/challenges/{id}/submit` - Submit a solution
- `GET /api/challenges/{id}/leaderboard` - Get challenge leaderboard

## Development Status

- [x] Database schema design
- [x] User authentication with Laravel Sanctum
- [x] GitHub integration fields
- [x] Project model and controller
- [x] Coding Session model and controller
- [x] Challenge model and controller
- [x] API routes
- [ ] Frontend development
- [ ] Real-time collaboration with WebSockets
- [ ] GitHub OAuth integration
- [ ] Deployment configuration

## License

MIT
