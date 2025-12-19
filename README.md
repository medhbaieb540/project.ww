# 🎮 GAMEBRIDGE – Connected & Intelligent Gaming Platform

**Connecting indie developers with players through community-driven engagement**

## 🚀 Overview

GameBridge is a comprehensive gaming platform designed to bridge the gap between indie game developers and passionate players. Our platform creates a vibrant ecosystem where developers can showcase their creations, collect valuable feedback, and build visibility, while players discover unique indie experiences, compete in tournaments, earn rewards, and actively shape the games they love.

### ✨ Key Vision

- **For Developers**: A launchpad for indie games with real-time feedback and community engagement
- **For Players**: A discovery platform for unique gaming experiences with interactive participation
- **For the Community**: A connected space where gaming enthusiasts can interact, compete, and collaborate

## 📋 Features

### 🎮 Core Modules

#### **Game Management**

- Upload and showcase indie games
- Advanced search and filtering (category, rating, date)
- Grid/List view toggle
- Bulk operations (export, delete)
- Soft delete with trash management
- File upload support (images, game files)

#### **Tournament System**

- Create and join gaming tournaments
- Tournament management and participation tracking
- Real-time tournament updates
- Reward distribution for winners

#### **Community & Events**

- Event creation and management
- Community-driven event participation
- Event analytics and reporting
- Social interaction features

#### **Feedback System**

- Structured feedback collection
- Developer response system
- Feedback analytics dashboard
- Community voting on features

#### **Rewards & Engagement**

- Gamified reward system
- Achievement tracking
- Point-based rewards
- Player progression

#### **User Management**

- Complete user profiles
- Account creation and authentication
- Password reset functionality
- User activity tracking
- Ban/Unban system for moderation

#### **Admin Dashboard**

- Comprehensive analytics
- User management
- Content moderation
- System configuration
- Feedback management

## 🏗️ Architecture

### **Technology Stack**

- **Backend**: PHP 7.4+ (MVC Architecture)
- **Frontend**: HTML5, CSS3, JavaScript
- **Database**: MySQL
- **Email**: PHPMailer with multi-language support
- **Dependencies**: Composer for package management

### **Project Structure**

```
gamebridge/
├── Controller/ # Application Controllers
│ ├── GameController.php
│ ├── TournamentController.php
│ ├── UserController.php
│ ├── EventController.php
│ ├── FeedbackController.php
│ ├── AdminController.php
│ └── ... (other controllers)
│
├── Model/ # Data Models
│ ├── GameModel.php
│ ├── User.php
│ ├── Tournament.php
│ ├── Event.php
│ ├── Feedback.php
│ ├── Reward.php
│ └── ... (other models)
│
├── View/ # Presentation Layer
│ ├── BackOffice/ # Admin Interface
│ │ ├── dashboard.php
│ │ ├── admin.php
│ │ ├── games_dashboard.php
│ │ ├── trash.php
│ │ ├── admin_feedback.php
│ │ ├── adminrewards.php
│ │ ├── admintour.php
│ │ └── event/
│ │
│ ├── FrontOffice/ # Public Interface
│ │ ├── game/
│ │ ├── tournaments.php
│ │ ├── events.php
│ │ ├── feedback.php
│ │ ├── profile.php
│ │ └── ... (other views)
│ │
│ └── partials/ # Reusable components
│
├── public/ # Publicly accessible assets
│ ├── css/ # Stylesheets
│ │ ├── style.css
│ │ ├── styleAdmin.css
│ │ ├── tournaments.css
│ │ └── ... (other styles)
│ │
│ ├── js/ # JavaScript files
│ │ ├── admin.js
│ │ ├── tournaments.js
│ │ ├── login.js
│ │ └── ... (other scripts)
│ │
│ ├── images/ # Images and assets
│ │ ├── games/
│ │ ├── logo.png
│ │ └── ... (other images)
│ │
│ └── index.php # Main entry point
│
├── config/ # Configuration files
│ ├── db.php
│ └── ... (other configs)
│
├── uploads/ # User uploads directory
│ ├── images/
│ └── games/
│
├── vendor/ # Composer dependencies
│ └── phpmailer/ # Email functionality
│
├── assets/ # Additional assets
├── sql/ # Database scripts
│ ├── gamebridge.sql
│ └── gamebridgefinal2.sql
│
├── composer.json # PHP dependencies
├── .htaccess # URL rewriting
└── README.md # This file
```

## 🚀 Installation

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Composer (for dependency management)
- XAMPP/WAMP/MAMP (for local development)

### Step-by-Step Setup

1. **Clone/Download the Project**
   ```bash
   cd /path/to/your/webroot
   git clone [repository-url] gamebridge
   ```

2.Install Dependencies

cd gamebridge
composer install

3.Database Setup

CREATE DATABASE gamebridge;
USE gamebridge;
-- Import the SQL file
SOURCE /path/to/gamebridgefinal.sql;

4.Configuration

config/db.php.example to config/db.php

Update database credentials:

$host = 'localhost';
$dbname = 'gamebridge';
$username = 'your_username';
$password = 'your_password';

5.File Permissions

chmod -R 755 uploads/
chmod -R 755 public/images/

6.Configure Base URL
Update .htaccess or server configuration to match your project path.

7.Start Development Server

php -S localhost:8000 -t public/
Access: http://localhost:8000

### 📖 Usage Guide

For Players
1 Create an Account: Register with email/password

2 Browse Games: Discover indie games with advanced filtering

3 Join Tournaments: Participate in community tournaments

4 Provide Feedback: Share your thoughts on games

5 Earn Rewards: Complete challenges and earn points

6 Participate in Events: Join community events and competitions

For Developers
1 Register as Developer: Create a developer account

2 Upload Games: Showcase your indie creations

3 Manage Games: Update, delete, or feature your games

4 Collect Feedback: Receive and respond to player feedback

5 Analyze Performance: View game statistics and engagement metrics

For Administrators
1 Access Admin Dashboard: /admin or admin login

2 Manage Users: View, ban, or unban users

3 Moderate Content: Approve/reject games and content

4 Manage Tournaments: Create and oversee tournaments

5 View Analytics: Platform statistics and user engagement

### 🔧 Configuration

Email Setup (PHPMailer)
Configure email settings in the relevant controller:

$mail = new PHPMailer\PHPMailer\PHPMailer();
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'your-email@gmail.com';
$mail->Password = 'your-password';
$mail->SMTPSecure = 'tls';
$mail->Port = 587;

File Upload Limits
Update php.ini:

upload_max_filesize = 500M
post_max_size = 500M
max_execution_time = 300

🔌 API Endpoints

Game Management :
GET /game/list # List all games
POST /game/create # Create new game
GET /game/show/{id} # View game details
POST /game/update/{id} # Update game
POST /game/delete/{id} # Soft delete game
GET /game/trash # View deleted games
POST /game/restore/{id} # Restore from trash
POST /game/permanent-delete/{id} # Permanent delete

Tournament System :
GET /tournaments # List tournaments
POST /tournaments/create # Create tournament
GET /tournaments/join/{id} # Join tournament
POST /tournaments/update/{id} # Update tournament

User Management:
POST /auth/login # User login
POST /auth/register # User registration
POST /auth/forgot-password # Password reset request
POST /auth/reset-password # Password reset
GET /profile/{id} # View profile
POST /profile/update/{id} # Update profile

### 🛠️ Development

Coding Standards

Follow PSR-12 coding standards

Use meaningful variable and function names

Add comments for complex logic

Keep controllers thin, models fat

### Git Workflow

# Create feature branch

git checkout -b feature/feature-name

# Commit changes

git add .
git commit -m "feat: description of changes"

# Push to repository

git push origin feature/feature-name

### Testing

# Run basic tests

php public/index.php test

### 🚢 Deployment

## Production Checklist

Update database credentials

Configure email settings

Set proper file permissions

Enable HTTPS

Configure backup strategy

Set up monitoring

Configure error logging

Optimize database indexes

## Performance Optimization

Enable PHP opcache

Configure MySQL query cache

Use CDN for static assets

Implement image optimization

Enable Gzip compression

### 🔒 Security

## Best Practices Implemented

SQL injection prevention (PDO prepared statements)

XSS protection (htmlspecialchars)

CSRF tokens for forms

Secure password hashing (password_hash)

File upload validation

Session security

Input sanitization

## Regular Security Tasks

Update dependencies regularly

Review error logs

Monitor suspicious activities

Regular backup verification

SSL certificate renewal

### 📊 Database Schema

## Key tables include:

users - User accounts and profiles

games - Game information and metadata

tournaments - Tournament details

events - Community events

feedback - User feedback and ratings

rewards - Reward system data

participations - Event/tournament participation

categories - Game categorization

### 🤝 Contributing

Fork the repository

Create a feature branch

Make your changes

Write/update tests

Submit a pull request

###Development Setup

# Clone your fork

git clone https://github.com/your-username/gamebridge.git

# Install dependencies

composer install

# Set up development database

mysql -u root -p < sql/development.sql

### 🐛 Troubleshooting

## Common Issues

Database Connection Failed

Check credentials in config/db.php

Verify MySQL service is running

Check database permissions

File Upload Not Working

Verify uploads/ directory permissions

Check PHP upload_max_filesize

Ensure proper file type validation

Email Not Sending

Verify SMTP configuration

Check email credentials

Review server firewall settings

Page Not Found (404)

Check .htaccess configuration

Verify mod_rewrite is enabled

Check base URL configuration

### 📞 Support

For support and inquiries:

Documentation: Check this README and code comments

Issues: GitHub Issues page

Contact: [Your contact email/forum]

### 📄 License

This project is licensed under the [Your License] - see the LICENSE file for details.

### 👥 Team

user_managment: [Ayman Mohamed]

games_managment : [Ragheb Ghada ]

tournaments : [Saif allah amami ]

rewards : [Mohamed hbaieb]

community : [Rihem bouzayen ]

events : [ Rayen chihi ]

### 🎯 Roadmap

## Planned Features

Mobile application

Live streaming integration

Advanced matchmaking

Social media integration

Advanced analytics dashboard

API for third-party integration

Cloud save functionality

Multi-language support

Dark mode theme

## Version History

v1.0 (Current): Core platform with basic features

v2.0 (Planned): Advanced community features

v3.0 (Planned): Mobile app and API expansion

Built with passion for the indie gaming community 🎮❤️

GameBridge - Where indie developers and players connect, create, and compete.
