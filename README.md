# 🎓 Club Event Manager

A comprehensive web-based platform for managing college clubs and events. Built with PHP, MySQL, and Bootstrap, this system streamlines club administration, event management, and student participation.

![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-7952B3?style=flat&logo=bootstrap&logoColor=white)

## ✨ Features

### 👨‍💼 Admin Panel

- **User Management**: Approve/reject student registrations
- **Club Management**: Create, edit, and delete clubs with images
- **Event Management**: Organize events across all clubs
- **Registration Oversight**: Monitor and approve club memberships
- **Dashboard Analytics**: View system-wide statistics

### 🎯 Club Admin Panel

- **Event Creation**: Schedule and manage club-specific events
- **Member Management**: Approve/reject club membership requests
- **Registration Tracking**: View event registrations in real-time
- **Analytics Dashboard**: Track club events and member count

### 🎓 Student Dashboard

- **Club Discovery**: Browse available clubs with descriptions and images
- **Club Registration**: Join clubs (max 2 clubs per student)
- **Event Registration**: Register for events from joined clubs
- **Personal Dashboard**: Track club memberships and event registrations

## 🚀 Quick Start

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- XAMPP/WAMP (for local development)

### Installation

1. **Clone the repository**

   ```bash
   git clone https://github.com/AMSearth/ClubManager.git
   cd ClubEventManager
   ```

2. **Configure database**

   Edit `config.php` with your database credentials:

   ```php
   $host = 'localhost';
   $dbname = 'club_manager';
   $username = 'your_username';
   $password = 'your_password';
   ```

3. **Setup database**

   Choose one method:

   **Option A: PHP Setup Script**

   ```bash
   php setup.php
   ```

   **Option B: MySQL Import**

   ```bash
   mysql -u username -p < database.sql
   ```

4. **Configure uploads directory**

   ```bash
   chmod 755 uploads/
   ```

5. **Access the application**
   ```
   http://localhost/ClubManager/
   ```

### Default Credentials

**Admin Account:**

- Username: `admin`
- Password: `password`

⚠️ **Important:** Change the default admin password after first login!

## 📁 Project Structure

```
ClubEventManager/
├── config.php                    # Database configuration
├── index.php                     # Landing page & club listing
├── login.php                     # User authentication
├── register.php                  # Student registration
├── dashboard.php                 # Student dashboard
├── admin.php                     # Admin panel
├── club_admin.php               # Club admin panel
├── register_club.php            # Club registration handler
├── register_event.php           # Event registration handler
├── get_event_registrations.php  # Fetch event registrations
├── logout.php                   # Session termination
├── setup.php                    # Database setup script
├── database.sql                 # Database schema
└── uploads/                     # Club & event images
```

## 🗄️ Database Schema

```sql
clubs
├── id (PK)
├── name
├── description
└── image

users
├── id (PK)
├── username (UNIQUE)
├── password
├── role (student/admin/club_admin)
├── status (pending/approved/rejected)
└── club_id (FK → clubs.id)

events
├── id (PK)
├── club_id (FK → clubs.id)
├── title
├── description
└── event_date

registrations
├── id (PK)
├── user_id (FK → users.id)
├── club_id (FK → clubs.id)
└── status (pending/approved/rejected)

event_registrations
├── id (PK)
├── event_id (FK → events.id)
├── user_id (FK → users.id)
└── registered_at
```

## 🔐 Security Features

- **SQL Injection Protection**: Prepared statements with parameter binding
- **XSS Prevention**: Output sanitization with `htmlspecialchars()`
- **Password Security**: Bcrypt hashing with `password_hash()`
- **Session Management**: Secure session-based authentication
- **Role-Based Access Control**: Three-tier permission system
- **Foreign Key Constraints**: Data integrity enforcement

## 🎨 User Roles

| Role           | Permissions                                                                            |
| -------------- | -------------------------------------------------------------------------------------- |
| **Student**    | Browse clubs, register for clubs (max 2), register for events, view personal dashboard |
| **Club Admin** | Manage club events, approve/reject club registrations, view registration analytics     |
| **Admin**      | Full system access, manage users/clubs/events, approve student registrations           |

## 📸 Screenshots

### Landing Page

Browse all available clubs with descriptions and images

### Student Dashboard

Track club memberships and event registrations

### Admin Panel

Comprehensive system management interface

### Club Admin Panel

Event management and member approval system

## 🛠️ Technologies Used

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework**: Bootstrap 5.1.3
- **Icons**: Font Awesome 6.0
- **Architecture**: MVC-inspired structure

## 📋 Features Breakdown

### Club Management

- Create clubs with name, description, and image
- Edit existing club information
- Delete clubs (cascades to related data)
- Image upload support (JPEG, PNG, GIF, WebP)

### Event Management

- Schedule events with date and description
- Club-specific event creation
- Event registration tracking
- Real-time registration counts

### User Management

- Student registration with approval workflow
- Role assignment (student/club_admin/admin)
- Status tracking (pending/approved/rejected)
- Club membership limits (2 clubs per student)

### Registration System

- Club membership requests
- Event registration with duplicate prevention
- Approval/rejection workflow
- Registration history tracking

## 🔄 Workflow

1. **Student Registration**

   - Student creates account → Admin approves → Student can login

2. **Club Membership**

   - Student browses clubs → Registers for club → Club admin approves → Student becomes member

3. **Event Registration**
   - Club admin creates event → Students see event → Students register → Tracked in dashboard

## 🚧 Future Enhancements

- [ ] Email notifications for approvals
- [ ] CSRF token protection
- [ ] File upload validation (MIME type, size limits)
- [ ] Event calendar view
- [ ] Search and filter functionality
- [ ] Export registration data (CSV/PDF)
- [ ] Event attendance tracking
- [ ] Club announcements system
- [ ] User profile management
- [ ] Password reset functionality

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is open source and available under the [MIT License](LICENSE).

## 👨‍💻 Author

**Your Name**

- GitHub: [@AMSearth](https://github.com/AMSearth)

## 🙏 Acknowledgments

- Bootstrap for the responsive UI framework
- Font Awesome for icons
- PHP community for excellent documentation

## 📞 Support

For issues, questions, or suggestions:

- Open an issue on GitHub
- Contact: ajinky.manoj.shinde@example.com

---

⭐ **Star this repository if you find it helpful!**

Made with ❤️ for college club management
