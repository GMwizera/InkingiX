# InkingiX Rwanda

A career discovery platform designed for Rwandan secondary school students. It helps students explore career paths through personalized assessments based on the RIASEC model and matches them with relevant careers and educational institutions in Rwanda.

## Live Demo
https://inkingix-production.up.railway.app

## GitHub Repository
https://github.com/GMwizera/InkingiX

## SRS Document
https://docs.google.com/document/d/1CZzcku1T0N1YNoOBMLiCNvYhM5-c7TSFWAKELIEFdHw/edit?usp=sharing

## Tech Stack
PHP 8.2 · MySQL 8 · Apache · Bootstrap 5 · Vanilla JS

## Local Setup (Step by Step)

### 1. Install XAMPP
- Download and install XAMPP from [apachefriends.org](https://www.apachefriends.org/)
- Make sure Apache and MySQL are running

### 2. Clone the Repository
```bash
git clone https://github.com/GMwizera/InkingiX.git
```

### 3. Move to XAMPP htdocs
```bash
mv InkingiX /Applications/XAMPP/htdocs/
```
On Windows:
```bash
move InkingiX C:\xampp\htdocs\
```

### 4. Create Environment File
```bash
cd /Applications/XAMPP/htdocs/InkingiX
cp .env.example .env
```

Edit `.env` and set your database credentials:
```
DB_HOST=127.0.0.1
DB_NAME=inkingix_rwanda
DB_USER=root
DB_PASS=
```

### 5. Create Database
- Open phpMyAdmin at `http://localhost/phpmyadmin`
- Click "New" in the left sidebar
- Create database named `inkingix_rwanda`
- Set collation to `utf8mb4_unicode_ci`

### 6. Import Database Schema
- Select the `inkingix_rwanda` database
- Go to "Import" tab
- Choose file: `database/edubridge_schema.sql`
- Click "Go" to import

### 7. Access the Application
Open your browser and visit:
```
http://localhost/InkingiX
```

## Test Accounts

| Role         | Email                  | Password   |
|--------------|------------------------|------------|
| Student      | Register on site       | -          |
| School Admin | school@inkingi.rw     | School123  |
| System Admin | admin@inkingi.rw      | Admin123   |

## Features

1. **User Authentication** - Secure registration and login with role-based access (Student, School Admin, System Admin)

2. **Career Assessment** - 30-question RIASEC-based assessment to evaluate interests and personality traits

3. **Personalized Career Matching** - AI-powered matching engine that recommends careers based on assessment results

4. **Career Exploration** - Browse 15+ careers with detailed information including salary ranges, required skills, and educational pathways

5. **Institution Directory** - Explore Rwandan universities, colleges, and TVET schools with program offerings

6. **Career Comparison** - Side-by-side comparison of up to 3 careers to help with decision making

7. **Bookmarks System** - Save favorite careers for later review and comparison

8. **My Journey Dashboard** - Track assessment progress, view results, and manage saved careers

9. **Bilingual Support** - Full support for English and Kinyarwanda languages

10. **Admin Dashboard** - School-scoped analytics for school admins, platform-wide management for system admins

## Project Structure

```
InkingiX/
├── admin/                  # Admin panel pages
│   ├── index.php          # Admin dashboard
│   ├── users.php          # User management
│   ├── careers.php        # Career management
│   ├── institutions.php   # Institution management
│   ├── reports.php        # Analytics reports
│   └── includes/          # Admin header/footer
├── assets/
│   ├── css/style.css      # Main stylesheet
│   ├── js/main.js         # JavaScript functions
│   └── images/            # Site images & favicon
├── config/
│   └── database.php       # Database connection & config
├── database/
│   └── edubridge_schema.sql  # Database schema & seed data
├── includes/
│   ├── functions.php      # Helper functions
│   ├── matching_engine.php # Career matching algorithm
│   ├── header.php         # Public header
│   ├── header-dashboard.php # Dashboard sidebar
│   └── footer.php         # Footer templates
├── lang/
│   ├── en.php             # English translations
│   └── rw.php             # Kinyarwanda translations
├── index.php              # Landing page
├── login.php              # User login
├── register.php           # User registration
├── dashboard.php          # My Journey dashboard
├── assessment.php         # Career assessment
├── results.php            # Assessment results
├── careers.php            # Career listing
├── career.php             # Single career detail
├── compare.php            # Career comparison
├── institutions.php       # Institution listing
├── bookmarks.php          # Saved careers
├── profile.php            # User profile
├── .env.example           # Environment template
└── README.md              # This file
```

## Screenshots

### Landing Page
The homepage showcases the platform's value proposition with a clean, modern design.

### Assessment
Interactive 30-question assessment with progress tracking.

### Career Matching Results
Visual breakdown of RIASEC scores with personalized career recommendations.

### Admin Dashboard
Analytics and management tools for administrators.

## License
This project was developed for educational purposes at African Leadership University.

## Author
Gisele Mwizera - ALU 2026
