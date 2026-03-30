# EduBridge Rwanda — Master Build Prompt
> **Purpose of this document:** Hand this entire file to any AI coding assistant
> (Cursor, GitHub Copilot, ChatGPT, Claude, Gemini, etc.) as the first message.
> It contains everything needed to build, run, and deploy EduBridge Rwanda from
> scratch — no prior context required.

---

## 0. WHO YOU ARE AND WHAT YOU ARE BUILDING

You are a senior full-stack web developer building **EduBridge Rwanda** — a
bilingual (English + Kinyarwanda) career discovery web application for Rwandan
secondary school students (Senior 1–6, ages 13–19).

The goal of the platform is to help students make informed career and subject
choices by matching their interests to real Rwandan careers, universities, and
TVET institutions, while also giving schools aggregate insight into their
students' interests.

**Problem being solved:**
Rwanda has only 1 career counselor per 1,500 students (REB, 2023), and 38% of
Rwandan graduates work in fields unrelated to their studies (NISR, 2022).
EduBridge Rwanda replaces the missing counselor with a free, accessible digital
tool.

---

## 1. TECH STACK

| Layer | Technology | Reason |
|---|---|---|
| Frontend | HTML5, CSS3, Bootstrap 5, Vanilla JS | Low bandwidth friendly, no build tools needed |
| Backend | PHP 8.0+ | Matches local hosting environments in Rwanda |
| Database | MySQL 8.0 | Standard, widely supported |
| Web Server | Apache (XAMPP locally, cPanel or DigitalOcean in production) | |
| PWA | Service Worker + Web App Manifest | Offline-lite support for low-bandwidth schools |
| Version Control | Git + GitHub | |
| Deployment | DigitalOcean Droplet OR Railway.app OR cPanel hosting | |
| SSL | Let's Encrypt (Certbot) | Free HTTPS |
| Languages | PHP, SQL, JavaScript, HTML, CSS | |

**DO NOT use:**
- Any JavaScript framework (no React, Vue, Angular)
- Any paid APIs or services
- Any npm build pipeline (no webpack, vite, etc.)
- Inline styles (use CSS classes only)
- jQuery (use Vanilla JS)

---

## 2. PROJECT FOLDER STRUCTURE

Create exactly this structure from day one:

```
edubridge-rwanda/
├── public/                        ← Apache document root points here
│   ├── index.php                  ← Landing page
│   ├── register.php
│   ├── login.php
│   ├── logout.php
│   ├── dashboard.php
│   ├── assessment.php
│   ├── results.php
│   ├── career.php                 ← Career detail page (?id=X)
│   ├── compare.php                ← Side-by-side career comparison
│   ├── bookmarks.php
│   ├── manifest.json              ← PWA manifest
│   ├── sw.js                      ← Service Worker
│   ├── offline.php
│   ├── admin/
│   │   ├── index.php              ← Admin dashboard (school aggregate data)
│   │   └── careers.php           ← Career data management
│   └── assets/
│       ├── css/
│       │   ├── style.css          ← Main stylesheet
│       │   └── admin.css
│       ├── js/
│       │   ├── app.js             ← Main JS (assessment flow, bookmarks, compare)
│       │   └── admin.js
│       └── images/
│           ├── logo.svg
│           └── icon-192.png
├── src/
│   ├── config/
│   │   └── database.php           ← PDO connection (reads from .env)
│   ├── models/
│   │   ├── User.php
│   │   ├── Assessment.php
│   │   ├── Career.php
│   │   └── Question.php
│   ├── controllers/
│   │   ├── AuthController.php
│   │   ├── AssessmentController.php
│   │   ├── CareerController.php
│   │   └── AdminController.php
│   └── helpers/
│       ├── auth_helper.php        ← session checks, role guards
│       ├── matching_engine.php    ← scoring + adaptive logic
│       └── lang_helper.php        ← language loading helper
├── lang/
│   ├── en.php                     ← All English UI strings
│   └── kn.php                     ← All Kinyarwanda UI strings
├── database/
│   ├── schema.sql                 ← All CREATE TABLE statements
│   └── seed.sql                   ← Real Rwandan careers, questions, institutions
├── .env.example                   ← Committed to Git (no real values)
├── .env                           ← NEVER commit this file
├── .gitignore
└── README.md
```

---

## 3. DATABASE SCHEMA

Run `database/schema.sql` first, then `database/seed.sql`.
Use UTF-8mb4 encoding on all tables for Kinyarwanda character support.

```sql
-- database/schema.sql

CREATE DATABASE IF NOT EXISTS edubridge CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE edubridge;

CREATE TABLE users (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  name            VARCHAR(100) NOT NULL,
  email           VARCHAR(150) UNIQUE NOT NULL,
  password_hash   VARCHAR(255) NOT NULL,
  school_name     VARCHAR(150),
  grade           ENUM('S1','S2','S3','S4','S5','S6'),
  language        ENUM('en','kn') DEFAULT 'en',
  role            ENUM('student','admin','superadmin') DEFAULT 'student',
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE career_categories (
  id       INT AUTO_INCREMENT PRIMARY KEY,
  name_en  VARCHAR(100) NOT NULL,
  name_kn  VARCHAR(100) NOT NULL
);

CREATE TABLE careers (
  id                  INT AUTO_INCREMENT PRIMARY KEY,
  category_id         INT NOT NULL,
  name_en             VARCHAR(150) NOT NULL,
  name_kn             VARCHAR(150) NOT NULL,
  description_en      TEXT,
  description_kn      TEXT,
  required_skills_en  TEXT,
  required_skills_kn  TEXT,
  salary_range        VARCHAR(100),
  demand_level        ENUM('low','growing','high') DEFAULT 'growing',
  FOREIGN KEY (category_id) REFERENCES career_categories(id)
);

CREATE TABLE institutions (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  name      VARCHAR(200) NOT NULL,
  type      ENUM('university','tvet') NOT NULL,
  location  VARCHAR(100),
  website   VARCHAR(200)
);

CREATE TABLE career_institutions (
  career_id       INT NOT NULL,
  institution_id  INT NOT NULL,
  program_name    VARCHAR(200),
  PRIMARY KEY (career_id, institution_id),
  FOREIGN KEY (career_id)      REFERENCES careers(id),
  FOREIGN KEY (institution_id) REFERENCES institutions(id)
);

CREATE TABLE questions (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  text_en     TEXT NOT NULL,
  text_kn     TEXT NOT NULL,
  category_id INT NOT NULL,
  weight      TINYINT DEFAULT 1,
  order_num   INT NOT NULL,
  FOREIGN KEY (category_id) REFERENCES career_categories(id)
);

CREATE TABLE assessments (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  user_id       INT NOT NULL,
  started_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  completed_at  TIMESTAMP NULL,
  status        ENUM('in_progress','completed') DEFAULT 'in_progress',
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE responses (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  assessment_id  INT NOT NULL,
  question_id    INT NOT NULL,
  answer         TINYINT NOT NULL COMMENT '1=Strongly Disagree, 5=Strongly Agree',
  FOREIGN KEY (assessment_id) REFERENCES assessments(id),
  FOREIGN KEY (question_id)   REFERENCES questions(id)
);

CREATE TABLE results (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  assessment_id  INT NOT NULL,
  career_id      INT NOT NULL,
  match_score    DECIMAL(5,2),
  rank           TINYINT,
  FOREIGN KEY (assessment_id) REFERENCES assessments(id),
  FOREIGN KEY (career_id)     REFERENCES careers(id)
);

CREATE TABLE bookmarks (
  user_id    INT NOT NULL,
  career_id  INT NOT NULL,
  saved_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, career_id),
  FOREIGN KEY (user_id)   REFERENCES users(id),
  FOREIGN KEY (career_id) REFERENCES careers(id)
);
```

### Seed Data Requirements

`database/seed.sql` MUST include the following real Rwandan data:

**Career Categories (minimum 6):**
Technology, Healthcare, Business & Finance, Agriculture & Environment,
Education, Engineering & Construction, Creative Arts & Media, Law & Governance

**Careers (minimum 15, with real Rwandan salary ranges):**
Software Developer, Nurse, Agronomist, Secondary School Teacher,
Civil Engineer, Accountant, Journalist, Lawyer, Doctor, Data Analyst,
Electrician (TVET), Graphic Designer, Hotel Manager, Social Worker,
Environmental Scientist

Each career must have:
- `name_kn`: actual Kinyarwanda name (e.g., "Injeniyeri ya Logicieli" for Software Developer)
- `description_kn`: 2–3 sentence Kinyarwanda description
- `salary_range`: in RWF/month (e.g., "300,000 – 800,000 RWF/month")
- `demand_level`: accurate for Rwanda's 2024 job market

**Institutions (minimum 8):**
University of Rwanda (UR), African Leadership University (ALU),
Carnegie Mellon University Africa (CMU-Africa), INES Ruhengeri,
Integrated Polytechnic Regional Center (IPRC) Kigali,
IPRC Musanze, IPRC Huye, Akilah Institute

**Questions (exactly 30):**
5 questions per category, written in both English and Kinyarwanda.
Use Likert scale 1–5 (Strongly Disagree → Strongly Agree).
Write questions in culturally familiar Rwandan scenarios:
- Agriculture: "I enjoy working outdoors and caring for plants or animals"
- Technology: "I like solving problems using computers or phones"
- Education: "I enjoy explaining things to others and helping them understand"

---

## 4. ENVIRONMENT CONFIGURATION

```bash
# .env.example  (commit this)
DB_HOST=localhost
DB_NAME=edubridge
DB_USER=root
DB_PASS=
APP_ENV=development
APP_URL=http://localhost/edubridge-rwanda/public
```

```bash
# .gitignore
.env
*.log
.DS_Store
/vendor/
```

Database connection in `src/config/database.php`:
```php
<?php
$host = getenv('DB_HOST') ?: 'localhost';
$db   = getenv('DB_NAME') ?: 'edubridge';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user, $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(['error' => 'Database connection failed']));
}
```

---

## 5. FEATURE SPECIFICATIONS

Build every feature below exactly as described.
Do not skip, simplify, or defer any feature marked **[MVP]**.
Features marked **[V2]** are optional enhancements.

---

### FEATURE 1 — User Registration & Authentication [MVP]

**Registration fields:** name, email, password, school_name, grade (S1–S6),
language preference (EN/KN)

**Rules:**
- Password: minimum 8 characters
- Password hashing: `password_hash($pass, PASSWORD_BCRYPT)`
- Email must be unique (show friendly error if duplicate)
- Session started on successful login: store `user_id`, `role`, `language`,
  `name`, `school_name`
- All pages except index/register/login require session check via
  `auth_helper.php`

```php
// src/helpers/auth_helper.php
function requireLogin() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['user_id'])) {
        header('Location: /login.php');
        exit;
    }
}

function requireRole(string $role) {
    requireLogin();
    if ($_SESSION['role'] !== $role) {
        header('Location: /dashboard.php');
        exit;
    }
}
```

---

### FEATURE 2 — Bilingual Interface (English + Kinyarwanda) [MVP]

- Language preference stored in `$_SESSION['language']` (default: `'en'`)
- A visible toggle button on every page switches language instantly
  (POST request that updates session + redirects back to same page)
- ALL UI text — buttons, labels, placeholders, error messages, headings —
  must come from language files, never hardcoded in PHP templates
- Career names, descriptions, and question text all have `_en` and `_kn`
  database columns — display based on session language

```php
// src/helpers/lang_helper.php
function t(string $key): string {
    static $strings = null;
    if ($strings === null) {
        $lang = $_SESSION['language'] ?? 'en';
        $strings = require __DIR__ . "/../../lang/{$lang}.php";
    }
    return $strings[$key] ?? $key;
}
```

Usage in templates: `<?= t('start_assessment') ?>`

**lang/en.php must include keys for:**
welcome, tagline, register, login, logout, start_assessment, my_results,
bookmarks, my_journey, career_match, demand_high, demand_growing, demand_low,
compare, save_career, retake, schools, programs, salary, required_skills,
next_question, previous, submit, language_toggle, your_top_matches,
assessment_progress, admin_dashboard, top_careers_school

---

### FEATURE 3 — Career Interest Assessment (Adaptive) [MVP]

**Flow:**
1. Student clicks "Start Assessment" on dashboard
2. System creates a new `assessments` row with status `in_progress`
3. Questions display one at a time with a progress bar
4. Student selects 1–5 on a Likert scale (radio buttons, large touch targets)
5. Each answer is saved immediately via AJAX POST to avoid data loss
6. After question 10, the adaptive engine identifies the top 2 scoring
   categories from answers so far
7. The remaining 20 questions are weighted 60% toward those top 2 categories
8. On final question, student clicks "See My Results"
9. Matching engine runs, results saved to `results` table, assessment marked
   `completed`

**Adaptive engine in `src/helpers/matching_engine.php`:**
```php
function getTopCategoriesSoFar(PDO $pdo, int $assessmentId): array {
    $stmt = $pdo->prepare("
        SELECT q.category_id, SUM(r.answer * q.weight) AS score
        FROM responses r
        JOIN questions q ON r.question_id = q.id
        WHERE r.assessment_id = ?
        GROUP BY q.category_id
        ORDER BY score DESC
        LIMIT 2
    ");
    $stmt->execute([$assessmentId]);
    return array_column($stmt->fetchAll(), 'category_id');
}

function calculateFinalMatches(PDO $pdo, int $assessmentId): array {
    $stmt = $pdo->prepare("
        SELECT q.category_id,
               SUM(r.answer * q.weight) AS raw_score,
               SUM(5 * q.weight) AS max_possible
        FROM responses r
        JOIN questions q ON r.question_id = q.id
        WHERE r.assessment_id = ?
        GROUP BY q.category_id
    ");
    $stmt->execute([$assessmentId]);
    $categories = $stmt->fetchAll();

    $scores = [];
    foreach ($categories as $cat) {
        $pct = round(($cat['raw_score'] / $cat['max_possible']) * 100, 2);
        $scores[$cat['category_id']] = $pct;
    }
    arsort($scores);

    // Get top 5 careers from top scoring categories
    $topCatIds = array_slice(array_keys($scores), 0, 3);
    $placeholders = implode(',', array_fill(0, count($topCatIds), '?'));
    $stmt = $pdo->prepare("
        SELECT id, category_id, name_en, name_kn, demand_level
        FROM careers
        WHERE category_id IN ($placeholders)
        LIMIT 5
    ");
    $stmt->execute($topCatIds);
    $careers = $stmt->fetchAll();

    $results = [];
    foreach ($careers as $i => $career) {
        $results[] = [
            'career_id'   => $career['id'],
            'career'      => $career,
            'match_score' => $scores[$career['category_id']] ?? 0,
            'rank'        => $i + 1
        ];
    }
    return $results;
}
```

---

### FEATURE 4 — Results Page [MVP]

Display top 3–5 career matches as cards. Each card must show:

- Career name (in current language)
- Match percentage (e.g., "87% match") with a colored progress bar
- **Demand badge:** 🟢 High Demand / 🟡 Growing / 🔴 Low Demand
  (color-coded using CSS classes: `.badge-high`, `.badge-growing`, `.badge-low`)
- "View Details" button → career.php?id=X
- "☆ Save" bookmark button (AJAX, no page reload)
- "Compare" checkbox (max 2 selectable at once)

When 2 careers are selected for compare, a sticky bottom bar appears:
"Compare Selected Careers →" button → compare.php?a=X&b=Y

---

### FEATURE 5 — Career Detail Page [MVP]

URL: `career.php?id=X`

Display:
- Career name (EN + KN toggle)
- Full description
- Required skills (as tag pills)
- Salary range in RWF/month
- Demand level badge
- Education pathways section: list of institutions with program names,
  institution type (University / TVET), location, and website link
- "← Back to Results" link
- "☆ Save / Unsave" bookmark toggle button

---

### FEATURE 6 — Career Comparison Page [MVP]

URL: `compare.php?a=X&b=Y`

Side-by-side two-column layout comparing:
- Career name
- Match score
- Demand level
- Required skills
- Salary range
- Education pathways

On mobile: stack vertically with clear section headers.
Include a "← Back to Results" button.

---

### FEATURE 7 — My Journey Dashboard [MVP]

This is the student's personal home after login. Show:

- **Welcome back, [Name]** header
- Assessment status card:
  - If never taken: "Start Your Assessment" CTA button
  - If in progress: "Continue Assessment" with progress %
  - If completed: "View My Results" + date taken
- **Top match preview:** show the #1 matched career with its demand badge
- **Bookmarked Careers** section: grid of saved career cards
- **Retake Assessment** button (only visible if assessment is completed)
- Language toggle always visible in the top navigation

---

### FEATURE 8 — School Administrator Dashboard [MVP]

URL: `admin/index.php` — protected by `requireRole('admin')`

Visible data (all anonymized — no student names):

- **Top 5 most-wanted careers** among students at this school
  (bar chart using CSS only, no JS chart library)
- **Total assessments completed** this month
- **Grade distribution** of students who have taken the assessment
  (S1 through S6, shown as a horizontal bar)
- **Interest clusters:** percentage of students per career category
  (e.g., "42% Technology, 28% Healthcare, …")

Query pattern:
```sql
SELECT c.name_en, COUNT(*) AS interest_count
FROM results r
JOIN careers c      ON r.career_id = c.id
JOIN assessments a  ON r.assessment_id = a.id
JOIN users u        ON a.user_id = u.id
WHERE u.school_name = :school AND r.rank = 1
GROUP BY c.id
ORDER BY interest_count DESC
LIMIT 5;
```

Admin users are created manually in the database (no public registration for
admin role). School admins only see data from their own school_name.

---

### FEATURE 9 — Rwanda Labour Market Demand Indicator [MVP]

Each career in the database has a `demand_level` field (low / growing / high).
This is displayed everywhere a career appears (results, detail, comparison,
bookmarks). CSS classes control color:

```css
.badge-high    { background: #16a34a; color: white; } /* green */
.badge-growing { background: #d97706; color: white; } /* amber */
.badge-low     { background: #dc2626; color: white; } /* red   */
```

In the admin dashboard, demand levels are also shown alongside each
top career so teachers know which paths are most marketable.

---

### FEATURE 10 — Progressive Web App (PWA / Offline-Lite) [MVP]

Add two files:

`public/manifest.json`:
```json
{
  "name": "EduBridge Rwanda",
  "short_name": "EduBridge",
  "description": "Career discovery for Rwandan students",
  "start_url": "/",
  "display": "standalone",
  "background_color": "#ffffff",
  "theme_color": "#0057A8",
  "icons": [
    { "src": "assets/images/icon-192.png", "sizes": "192x192", "type": "image/png" }
  ]
}
```

`public/sw.js`:
```javascript
const CACHE_NAME = 'edubridge-v1';
const OFFLINE_URLS = [
  '/',
  '/assessment.php',
  '/assets/css/style.css',
  '/assets/js/app.js',
  '/offline.php'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(OFFLINE_URLS))
  );
});

self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(cached => cached || fetch(event.request))
      .catch(() => caches.match('/offline.php'))
  );
});
```

Register in every page's `<head>`:
```html
<script>
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js');
  }
</script>
<link rel="manifest" href="/manifest.json">
```

---

## 6. SECURITY REQUIREMENTS

Every single one of these is non-negotiable:

| Requirement | Implementation |
|---|---|
| Password storage | `password_hash($pass, PASSWORD_BCRYPT)` only. Never MD5 or SHA1. |
| SQL injection | ALL database queries use PDO prepared statements. Zero string concatenation in SQL. |
| XSS prevention | ALL user-supplied output wrapped in `htmlspecialchars($val, ENT_QUOTES, 'UTF-8')` |
| Session security | `session_regenerate_id(true)` after login |
| CSRF protection | Hidden token in all POST forms, validated server-side |
| HTTPS | Enforce in production: redirect all HTTP → HTTPS in `.htaccess` |
| Data protection | Comply with Rwanda Law No. 058/2021 on Personal Data and Privacy |
| Student age | No student data sold, shared, or used for ads. Privacy policy page required. |
| Input validation | Server-side validation on all form fields. Client-side validation is UX only. |

`.htaccess` for Apache:
```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## 7. UI/UX DESIGN REQUIREMENTS

### Visual Identity

- **Primary color:** `#0057A8` (Rwanda blue — references the national flag)
- **Accent color:** `#16a34a` (green — growth, opportunity)
- **Background:** `#f8fafc` (light grey-white)
- **Font:** `Nunito` (Google Fonts) — friendly, readable, works well in
  Kinyarwanda
- **Border radius:** 12px on cards, 8px on buttons
- Logo: text-based "EduBridge" with a small bridge/arrow icon

### Responsive Breakpoints

- Mobile-first design
- Must work at 320px minimum width (low-end Android phones)
- Tested at: 320px, 375px, 768px, 1024px, 1440px
- All buttons minimum 44px height (touch target)
- Minimum font size: 16px body text

### Page-Level UX Rules

- Assessment page: one question per screen, large radio buttons, clear
  progress bar (e.g., "Question 7 of 30")
- Results page: career cards in a responsive grid (1 col mobile, 2 col tablet,
  3 col desktop)
- No modals for critical flows (registration, login, assessment)
- Loading states on all AJAX calls (spinner or button text change)
- Error messages in red, success messages in green, displayed above forms
- Language toggle: always in top-right of navbar, labeled "EN | KN"

---

## 8. NON-FUNCTIONAL REQUIREMENTS

| Requirement | Target |
|---|---|
| Page load time | Under 5 seconds on 4G (500KB max per page) |
| Concurrent users | Support 50 simultaneous users (MVP) |
| Uptime | 95% during school hours (7:00 AM – 6:00 PM EAT) |
| Browser support | Chrome 90+, Firefox 88+, Safari 14+, Edge 90+ |
| Device support | Android smartphones, iOS devices, Windows/Mac/Linux desktops |
| Encoding | UTF-8mb4 throughout (database, PHP headers, HTML meta charset) |
| Images | Compressed, max 100KB each. Use SVG for icons where possible. |
| Accessibility | Minimum WCAG 2.1 AA: alt text on images, proper label elements, keyboard navigation |

---

## 9. GIT WORKFLOW

### Repository Setup

```bash
git init
git add .
git commit -m "chore: initial project structure and schema"
git remote add origin https://github.com/USERNAME/edubridge-rwanda.git
git branch -M main
git push -u origin main
```

### Branch Strategy

```
main          ← production-ready only. Never commit directly.
develop       ← integration branch. Merge features here first.
  └── feature/auth
  └── feature/assessment-engine
  └── feature/results-ui
  └── feature/admin-dashboard
  └── feature/pwa
  └── feature/kinyarwanda-content
```

### Commit Message Format (Conventional Commits)

```
feat: add adaptive question branching after question 10
fix: correct bcrypt password verification on login
chore: add .env.example and update .gitignore
docs: update README with local setup instructions
style: improve mobile layout on results cards
refactor: extract matching logic into MatchingEngine class
```

### What NEVER goes into Git

```gitignore
.env
*.log
.DS_Store
config/local.php
/vendor/
node_modules/
```

---

## 10. DEPLOYMENT

### Option A — Railway.app (Recommended for student projects, free tier)

1. Connect GitHub repo to railway.app
2. Add MySQL plugin in Railway dashboard
3. Set environment variables: `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
4. Railway auto-deploys on every push to `main`
5. Import `schema.sql` and `seed.sql` via Railway's database shell

### Option B — DigitalOcean Droplet ($6/month, most professional)

```bash
# On fresh Ubuntu 22.04 droplet:
sudo apt update && sudo apt upgrade -y
sudo apt install apache2 mysql-server php8.2 php8.2-mysql php8.2-mbstring -y

# Clone repo
cd /var/www/html
git clone https://github.com/USERNAME/edubridge-rwanda.git
cd edubridge-rwanda

# Create .env (never copy from repo)
nano .env

# Set Apache document root to /var/www/html/edubridge-rwanda/public
sudo nano /etc/apache2/sites-available/edubridge.conf
sudo a2ensite edubridge.conf
sudo a2enmod rewrite
sudo systemctl restart apache2

# Free SSL
sudo apt install certbot python3-certbot-apache -y
sudo certbot --apache -d yourdomain.com
```

### Auto-deploy with GitHub Actions

```yaml
# .github/workflows/deploy.yml
name: Deploy to Production

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - name: SSH deploy
        uses: appleboy/ssh-action@v0.1.6
        with:
          host:     ${{ secrets.SERVER_HOST }}
          username: ${{ secrets.SERVER_USER }}
          key:      ${{ secrets.SSH_PRIVATE_KEY }}
          script: |
            cd /var/www/html/edubridge-rwanda
            git pull origin main
```

Add `SERVER_HOST`, `SERVER_USER`, `SSH_PRIVATE_KEY` in GitHub repo →
Settings → Secrets and Variables → Actions.

### Option C — cPanel Hosting (ESICIA-style, existing knowledge)

1. Zip project (excluding `.env` and dev files)
2. Upload to `/public_html/edubridge/` via cPanel File Manager
3. Create MySQL database + user in cPanel → MySQL Databases
4. Import `schema.sql` and `seed.sql` via phpMyAdmin
5. Create `.env` directly on server via cPanel File Manager
6. Set subdomain document root to `/public_html/edubridge/public/`

---

## 11. BUILD ORDER (SPRINT PLAN)

Follow this exact order. Do not build UI before the database is seeded.

### Sprint 1 — Weeks 1–2: Foundation
- [ ] Create full folder structure
- [ ] Write `schema.sql` and `seed.sql` (all 15 careers, 30 questions, 8 institutions)
- [ ] Set up `database.php` with PDO connection
- [ ] Build `register.php` with bcrypt and validation
- [ ] Build `login.php` with session management
- [ ] Build `logout.php`
- [ ] Build `auth_helper.php` and `lang_helper.php`
- [ ] Build `lang/en.php` and `lang/kn.php` with all keys
- [ ] Set up Git, push to GitHub
- **Commit:** `feat: auth system, language files, database schema`

### Sprint 2 — Weeks 3–4: Core Engine
- [ ] Build `assessment.php` with question display and AJAX answer saving
- [ ] Build adaptive question logic in `matching_engine.php`
- [ ] Build final score calculation and results saving
- [ ] Build `results.php` with career match cards and demand badges
- [ ] Build `career.php` (career detail page)
- [ ] Build bookmark AJAX endpoints
- **Commit:** `feat: assessment flow, matching engine, results display`

### Sprint 3 — Weeks 5–6: Enhanced Features
- [ ] Build `compare.php` (side-by-side comparison)
- [ ] Build `bookmarks.php`
- [ ] Build full `dashboard.php` (My Journey view)
- [ ] Build `admin/index.php` (school aggregate dashboard)
- [ ] Add CSS-only bar charts to admin dashboard
- [ ] Add career demand data to all seed careers
- **Commit:** `feat: comparison, bookmarks, journey dashboard, admin view`

### Sprint 4 — Weeks 7–8: PWA + Polish + Deploy
- [ ] Add `manifest.json` and `sw.js`
- [ ] Add `offline.php`
- [ ] Mobile responsiveness audit (test at 320px)
- [ ] CSRF token implementation on all forms
- [ ] Security audit (check all SQL uses prepared statements, all output uses htmlspecialchars)
- [ ] Write `README.md` with local setup instructions
- [ ] Deploy to chosen hosting platform
- [ ] Test on real Android Chrome (PWA install prompt)
- **Commit:** `feat: PWA support, security hardening, production deployment`

---

## 12. README.md REQUIREMENTS

The README must include:
- Project description (1 paragraph)
- Live demo URL
- Screenshots of: landing page, assessment, results page, admin dashboard
- Local setup instructions (XAMPP, Git clone, .env setup, database import)
- Tech stack list
- Folder structure overview
- How to switch between English and Kinyarwanda
- Contribution guide (branch naming, commit format)
- License (MIT)

---

## 13. FINAL LAUNCH CHECKLIST

Before calling this project done, verify every item:

```
□ All 30 questions display correctly in both EN and KN
□ Assessment adaptive logic activates after question 10
□ Results show top 3-5 careers with correct match percentages
□ All careers display demand badges in correct color
□ Career comparison works with exactly 2 careers selected
□ Bookmarks persist across sessions (stored in DB, not localStorage)
□ Admin dashboard only shows data from admin's own school
□ Language toggle works on every single page
□ Registration rejects duplicate emails with friendly message
□ All forms have CSRF protection
□ Zero SQL queries use string concatenation (all use prepared statements)
□ All output uses htmlspecialchars
□ HTTPS enforced in production
□ .env is NOT in GitHub repository
□ README explains how to run locally in under 5 minutes
□ PWA installs on Android Chrome (check for install prompt)
□ Pages load under 5 seconds on simulated 4G (Chrome DevTools)
□ All pages are usable on 320px wide screen
□ seed.sql has real Rwandan universities, TVET institutions, careers, and salaries
□ GitHub repo is public with clean commit history
□ Live deployment URL is accessible and HTTPS
```

---

*Document version: 1.0 — EduBridge Rwanda*
*Prepared for: AI-assisted development handoff*
*Author: Gisele Mwizera Amen / ESICIA Ltd*
*Stack: PHP 8 · MySQL 8 · Apache · Bootstrap 5 · Vanilla JS*
