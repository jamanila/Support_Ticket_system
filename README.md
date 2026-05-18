# 🛠 Support Ticket System (PHP OOP)

A simple yet powerful **Support Ticket Management System** built using PHP (OOP), MySQL, and session-based authentication.  
It supports multiple roles such as **Admin, Agent, and User**, with ticket creation, assignment, and conversation tracking.

---

## 🚀 Features

### 👤 Authentication & Roles
- Session-based login system
- Role-based access control:
  - Admin
  - Agent
  - User

---

### 🎫 Ticket Management
- Create new support tickets
- View all tickets (role-based visibility)
- Assign tickets to agents (Admin)
- Update ticket status:
  - Open
  - In Progress
  - Closed
- Delete tickets (Admin only)

---

### 💬 Ticket Conversation System
- Each ticket has a dedicated conversation page
- Users and agents can communicate inside a ticket
- Chronological message display (chat-style UI)

---

### 📊 Admin Dashboard
- Total tickets overview
- Open / In-progress / Closed counters
- Ticket management table
- Agent assignment system

---

### 🎨 UI/UX
- Modern dark-themed interface
- Inline CSS styling (no external CSS framework)
- Glassmorphism-inspired cards
- Responsive layout (basic support)

---

## 🏗 Project Structure
/SupportSystem
│
├── models/
│ ├── Ticket.php
│ ├── Users.php
│ └── Comment.php (future)
│
├── middleware/
│ ├── Auth.php
│ └── login.php
│
├── admin/
│ ├── index.php
│
├── agent/
│ ├── index.php
│
├── user/
│ ├── index.php
│
├── tickets/
│ ├── create.php
│ ├── update.php
│ ├── delete.php
│ ├── ticket_details.php
│
├── config/
│ ├── db.php
│
└── README.md
