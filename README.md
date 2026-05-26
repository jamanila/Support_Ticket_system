# 🛠 Support Ticket System (PHP OOP)

A simple Support Ticket Management System built with plain PHP, MySQL, and session-based authentication. The application supports three roles: **Admin**, **Agent**, and **User**, with role-based access to dashboards, ticket creation, assignment, and conversation tracking.

---

## 🚀 Features

- Session-based login and registration
- Role-based access control:
  - Admin
  - Agent
  - User
- Ticket creation and update workflow
- Agent assignment from admin dashboard
- Ticket status updates: Open, In Progress, Closed
- Ticket conversation / reply page for users and agents
- Flash success/error messaging via session toasts
- Centralized header/footer partials for layout consistency

---

## 📁 Project Structure

SupportSystem/
├── app/
│   ├── middleware/
│   │   ├── Auth.php
│   │   └── login.php
│   └── models/
│       ├── Ticket.php
│       ├── Users.php
│       ├── Comment.php
│       └── Notification.php
├── config/
│   └── db.php
├── public/
│   └── css/
│       └── app.css
├── views/
│   ├── admin/
│   │   ├── index.php
│   │   └── delete.php
│   ├── agent/
│   │   └── dashboard.php
│   ├── auth/
│   │   ├── login.php
│   │   └── register.php
│   ├── errors/
│   │   ├── 401.php
│   │   ├── 403.php
│   │   ├── 404.php
│   │   └── 500.php
│   ├── partials/
│   │   ├── footer.php
│   │   └── header.php
│   ├── tickets/
│   │   ├── agent.php
│   │   ├── create.php
│   │   ├── ticket-details.php
│   │   ├── update.php
│   │   └── user.php
│   └── user/
│       └── dashboard.php
├── index.php
└── README.md

---

## ⚙️ Installation

1. Place the project folder inside your local server document root, for example:

   `C:\xampp\htdocs\SupportSystem`

2. Create a MySQL database and import the application schema.
3. Update the database connection settings in `config/db.php`.
4. Open the application in your browser:

   `http://localhost/SupportSystem`

---

## 🧭 Usage

- `views/auth/login.php` — login page for all users
- `views/auth/register.php` — registration page
- `views/user/dashboard.php` — user dashboard with create ticket access
- `views/agent/dashboard.php` — agent dashboard with assigned tickets
- `views/admin/index.php` — admin dashboard for ticket assignment and management
- `views/tickets/create.php` — ticket creation form
- `views/tickets/ticket-details.php` — ticket conversation page
- `views/tickets/update.php` — status update page
- `views/admin/delete.php` — ticket deletion page

---

## 🔧 Notes

- Shared layout components are located in `views/partials/header.php` and `views/partials/footer.php`.
- Global styles are in `public/css/app.css`.
- Authentication checks and route access control are handled by `app/middleware/Auth.php` and `app/middleware/login.php`.

---

## 🔮 Future Improvements

- Real-time chat with AJAX or WebSockets
- Notification system
- Pagination and advanced filtering
- File attachments for tickets
- Improved MVC structure and routing
- REST API support
