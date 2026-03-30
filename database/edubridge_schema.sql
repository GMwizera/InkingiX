-- InkingiX Rwanda Database Schema
-- Version 1.0 - MVP
-- UTF-8 encoding for Kinyarwanda support


SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- Create database
CREATE DATABASE IF NOT EXISTS edubridge_rwanda
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE edubridge_rwanda;

-- =====================================================
-- USERS TABLE - All user types (students, school_admin, system_admin)
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    role ENUM('student', 'school_admin', 'system_admin') DEFAULT 'student',
    school_name VARCHAR(255),
    grade_level ENUM('S1', 'S2', 'S3', 'S4', 'S5', 'S6') NULL,
    date_of_birth DATE NULL,
    gender ENUM('male', 'female', 'other') NULL,
    phone VARCHAR(20) NULL,
    preferred_language ENUM('en', 'rw') DEFAULT 'en',
    profile_completed TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_school (school_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- SCHOOLS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS schools (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    name_rw VARCHAR(255),
    district VARCHAR(100),
    province VARCHAR(100),
    school_type ENUM('public', 'private', 'government_aided') DEFAULT 'public',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CAREER CATEGORIES (Holland Codes: RIASEC)
-- =====================================================
CREATE TABLE IF NOT EXISTS career_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) NOT NULL UNIQUE,
    name_en VARCHAR(100) NOT NULL,
    name_rw VARCHAR(100),
    description_en TEXT,
    description_rw TEXT,
    icon VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CAREERS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS careers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title_en VARCHAR(255) NOT NULL,
    title_rw VARCHAR(255),
    description_en TEXT,
    description_rw TEXT,
    required_skills_en TEXT,
    required_skills_rw TEXT,
    education_path_en TEXT,
    education_path_rw TEXT,
    salary_range_min INT,
    salary_range_max INT,
    demand_level ENUM('low', 'growing', 'high') DEFAULT 'growing',
    job_outlook_en TEXT,
    job_outlook_rw TEXT,
    work_environment_en TEXT,
    work_environment_rw TEXT,
    primary_category_id INT,
    secondary_category_id INT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (primary_category_id) REFERENCES career_categories(id),
    FOREIGN KEY (secondary_category_id) REFERENCES career_categories(id),
    INDEX idx_category (primary_category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- INSTITUTIONS (Universities and TVET)
-- =====================================================
CREATE TABLE IF NOT EXISTS institutions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_en VARCHAR(255) NOT NULL,
    name_rw VARCHAR(255),
    type ENUM('university', 'tvet', 'college') NOT NULL,
    location VARCHAR(255),
    website VARCHAR(255),
    description_en TEXT,
    description_rw TEXT,
    is_public TINYINT(1) DEFAULT 1,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CAREER-INSTITUTION RELATIONSHIP
-- =====================================================
CREATE TABLE IF NOT EXISTS career_institutions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    career_id INT NOT NULL,
    institution_id INT NOT NULL,
    program_name_en VARCHAR(255),
    program_name_rw VARCHAR(255),
    duration VARCHAR(50),
    FOREIGN KEY (career_id) REFERENCES careers(id) ON DELETE CASCADE,
    FOREIGN KEY (institution_id) REFERENCES institutions(id) ON DELETE CASCADE,
    UNIQUE KEY unique_career_institution (career_id, institution_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- ASSESSMENT QUESTIONS
-- =====================================================
CREATE TABLE IF NOT EXISTS assessment_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_en TEXT NOT NULL,
    question_rw TEXT,
    question_type ENUM('likert', 'multiple_choice', 'yes_no') DEFAULT 'likert',
    category_id INT NOT NULL,
    weight INT DEFAULT 1,
    order_number INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES career_categories(id),
    INDEX idx_category (category_id),
    INDEX idx_order (order_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- USER ASSESSMENTS (Assessment Sessions)
-- =====================================================
CREATE TABLE IF NOT EXISTS user_assessments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    is_completed TINYINT(1) DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_completed (is_completed)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- ASSESSMENT RESPONSES
-- =====================================================
CREATE TABLE IF NOT EXISTS assessment_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assessment_id INT NOT NULL,
    question_id INT NOT NULL,
    response_value INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assessment_id) REFERENCES user_assessments(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES assessment_questions(id),
    UNIQUE KEY unique_assessment_question (assessment_id, question_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- ASSESSMENT RESULTS (Category Scores)
-- =====================================================
CREATE TABLE IF NOT EXISTS assessment_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assessment_id INT NOT NULL,
    category_id INT NOT NULL,
    score DECIMAL(5,2) NOT NULL,
    percentage DECIMAL(5,2) NOT NULL,
    FOREIGN KEY (assessment_id) REFERENCES user_assessments(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES career_categories(id),
    UNIQUE KEY unique_assessment_category (assessment_id, category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CAREER MATCHES (Top careers for each assessment)
-- =====================================================
CREATE TABLE IF NOT EXISTS career_matches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assessment_id INT NOT NULL,
    career_id INT NOT NULL,
    match_percentage DECIMAL(5,2) NOT NULL,
    rank_order INT NOT NULL,
    FOREIGN KEY (assessment_id) REFERENCES user_assessments(id) ON DELETE CASCADE,
    FOREIGN KEY (career_id) REFERENCES careers(id),
    INDEX idx_assessment (assessment_id),
    INDEX idx_rank (rank_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- BOOKMARKS (Saved careers)
-- =====================================================
CREATE TABLE IF NOT EXISTS bookmarks (
    user_id INT NOT NULL,
    career_id INT NOT NULL,
    saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, career_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (career_id) REFERENCES careers(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- INSERT DEFAULT DATA
-- =====================================================

-- Career Categories (RIASEC Model)
INSERT INTO career_categories (code, name_en, name_rw, description_en, description_rw, icon) VALUES
('R', 'Realistic', 'Ibikorwa by''intoki', 'People who enjoy working with things, tools, machines, plants, or animals. They prefer practical, hands-on activities.', 'Abantu bakunda gukora ibintu bikoreshwa intoki, imashini, ibimera, cyangwa inyamaswa.', 'fa-tools'),
('I', 'Investigative', 'Ubushakashatsi', 'People who enjoy researching, analyzing, and solving problems. They prefer working with ideas and data.', 'Abantu bakunda gushakisha, gusesengura, no gukemura ibibazo. Bakunda gukora n''ibitekerezo n''amakuru.', 'fa-microscope'),
('A', 'Artistic', 'Ubuhanzi', 'People who enjoy creative activities like art, music, writing, or design. They prefer self-expression and imagination.', 'Abantu bakunda ibikorwa by''ubuhanzi nk''ishusho, umuziki, kwandika, cyangwa gushushanya.', 'fa-palette'),
('S', 'Social', 'Imibereho', 'People who enjoy helping, teaching, or serving others. They prefer working with people rather than things.', 'Abantu bakunda gufasha, kwigisha, cyangwa gukorera abandi. Bakunda gukora n''abantu.', 'fa-users'),
('E', 'Enterprising', 'Ubucuruzi', 'People who enjoy leading, persuading, and managing others. They prefer business and entrepreneurship.', 'Abantu bakunda kuyobora, kwemeza, no gucunga abandi. Bakunda ubucuruzi.', 'fa-briefcase'),
('C', 'Conventional', 'Amategeko', 'People who enjoy organizing, following procedures, and working with data. They prefer structured environments.', 'Abantu bakunda gutunganya, gukurikiza amabwiriza, no gukora n''amakuru.', 'fa-clipboard-list');

-- Sample Schools
INSERT INTO schools (name, name_rw, district, province, school_type) VALUES
('Lycee de Kigali', 'Lisee ya Kigali', 'Nyarugenge', 'Kigali City', 'public'),
('College Saint Andre', 'Koleji Mutagatifu Andre', 'Gasabo', 'Kigali City', 'private'),
('Groupe Scolaire Officiel de Butare', 'GSOB', 'Huye', 'Southern Province', 'public'),
('FAWE Girls School', 'Ishuri ry''Abakobwa rya FAWE', 'Gasabo', 'Kigali City', 'private'),
('Ecole des Sciences de Musanze', 'Ishuri ry''Ubumenyi rya Musanze', 'Musanze', 'Northern Province', 'public'),
('Riviera High School', 'Ishuri Rikuru rya Riviera', 'Kicukiro', 'Kigali City', 'private'),
('Nyamata Secondary School', 'Ishuri Ryisumbuye rya Nyamata', 'Bugesera', 'Eastern Province', 'public'),
('King David Academy', 'Akademiya ya King David', 'Gasabo', 'Kigali City', 'private');

-- Rwandan Institutions
INSERT INTO institutions (name_en, name_rw, type, location, website, description_en, is_public) VALUES
('University of Rwanda', 'Kaminuza y''u Rwanda', 'university', 'Kigali', 'https://ur.ac.rw', 'The largest public university in Rwanda offering various programs.', 1),
('African Leadership University', 'Kaminuza y''Ubuyobozi bwa Afrika', 'university', 'Kigali', 'https://www.alueducation.com', 'Pan-African university focused on developing future African leaders.', 0),
('Carnegie Mellon University Africa', 'Carnegie Mellon Africa', 'university', 'Kigali', 'https://www.cmu.edu/africa', 'American university offering graduate programs in technology.', 0),
('Rwanda Polytechnic', 'Ishuri ry''Imyuga ryo mu Rwanda', 'tvet', 'Multiple Locations', 'https://www.rp.ac.rw', 'Technical and vocational education institution.', 1),
('IPRC Kigali', 'IPRC Kigali', 'tvet', 'Kigali', 'https://www.iprckigali.rp.ac.rw', 'Integrated Polytechnic Regional College offering technical programs.', 1),
('IPRC South', 'IPRC Amajyepfo', 'tvet', 'Huye', 'https://www.iprcsouth.rp.ac.rw', 'Technical college in the Southern Province.', 1),
('Akilah Institute', 'Ikigo cy''Akilah', 'college', 'Kigali', 'https://www.akilahinstitute.org', 'Women-focused college offering hospitality and entrepreneurship.', 0),
('University of Kigali', 'Kaminuza ya Kigali', 'university', 'Kigali', 'https://www.uok.ac.rw', 'Private university offering various undergraduate programs.', 0),
('Mount Kenya University Rwanda', 'Kaminuza ya Mount Kenya mu Rwanda', 'university', 'Kigali', 'https://www.mku.ac.ke', 'Kenyan university with a campus in Rwanda.', 0),
('University of Lay Adventists of Kigali', 'UNILAK', 'university', 'Kigali', 'https://www.unilak.ac.rw', 'Private Christian university.', 0);

-- Sample Careers
INSERT INTO careers (title_en, title_rw, description_en, description_rw, required_skills_en, required_skills_rw, education_path_en, education_path_rw, salary_range_min, salary_range_max, job_outlook_en, primary_category_id) VALUES
('Software Developer', 'Umuhanga mu ikoranabuhanga', 'Design, develop, and maintain software applications and systems.', 'Gushushanya, guteza imbere, no kubungabunga porogaramu z''ikoranabuhanga.', 'Programming, Problem-solving, Logical thinking, Teamwork', 'Gukoresha porogaramu, Gukemura ibibazo, Gutekereza neza, Gukorana n''abandi', 'Bachelor''s degree in Computer Science or Software Engineering, or TVET diploma in IT', 'Impamyabumenyi ya kaminuza mu bumenyi bw''ikoranabuhanga cyangwa impamyabumenyi y''imyuga', 300000, 1500000, 'High demand due to digital transformation in Rwanda', 2),

('Medical Doctor', 'Umuganga', 'Diagnose and treat illnesses, provide medical care to patients.', 'Gusuzuma no kuvura indwara, gutanga ubuvuzi ku barwayi.', 'Medical knowledge, Communication, Empathy, Decision-making', 'Ubumenyi bw''ubuvuzi, Gutumaana, Impuhwe, Gufata ibyemezo', 'MBBS degree (6 years) followed by internship and specialization', 'Impamyabumenyi ya dogitora (imyaka 6) no gukora mu bitaro', 500000, 2500000, 'Always in demand, especially in rural areas', 2),

('Teacher', 'Umwarimu', 'Educate and guide students in various subjects at different levels.', 'Kwigisha no kuyobora abanyeshuri mu masomo atandukanye.', 'Communication, Patience, Subject knowledge, Creativity', 'Gutumaana, Kwihangana, Ubumenyi bw''isomo, Ubwenge', 'Bachelor''s degree in Education or subject area with teaching certification', 'Impamyabumenyi mu burezi cyangwa mu isomo runaka', 150000, 500000, 'Stable demand with government investment in education', 4),

('Accountant', 'Umubarisiya', 'Manage financial records, prepare reports, and ensure compliance.', 'Gucunga inyandiko z''imari, gutegura raporo, no kubahiriza amategeko.', 'Mathematics, Attention to detail, Analytical skills, Integrity', 'Imibare, Kwitonda, Ubushobozi bwo gusesengura, Ubudahemuka', 'Bachelor''s degree in Accounting or Finance, CPA certification recommended', 'Impamyabumenyi mu kubara cyangwa imari', 250000, 1000000, 'Growing demand as businesses expand', 6),

('Civil Engineer', 'Umuhanga mu bubaki', 'Design and oversee construction of infrastructure projects.', 'Gushushanya no kugenzura imishinga y''ibikorwa remezo.', 'Mathematics, Technical drawing, Problem-solving, Project management', 'Imibare, Gushushanya, Gukemura ibibazo, Kuyobora imishinga', 'Bachelor''s degree in Civil Engineering', 'Impamyabumenyi mu bwubatsi', 400000, 1500000, 'High demand due to infrastructure development', 1),

('Nurse', 'Umuforomo', 'Provide patient care, administer medications, and support doctors.', 'Gutanga ubuvuzi ku barwayi, gutanga imiti, no gufasha abaganga.', 'Patient care, Communication, Medical knowledge, Compassion', 'Kwita ku barwayi, Gutumaana, Ubumenyi bw''ubuvuzi, Impuhwe', 'Diploma or Bachelor''s degree in Nursing', 'Impamyabumenyi y''ubuforomo', 200000, 600000, 'High demand in healthcare sector', 4),

('Business Manager', 'Umuyobozi w''ubucuruzi', 'Oversee business operations, manage teams, and drive growth.', 'Kuyobora ibikorwa by''ubucuruzi, gucunga itsinda, no guteza imbere.', 'Leadership, Communication, Strategic thinking, Financial literacy', 'Ubuyobozi, Gutumaana, Gutekereza intego, Ubumenyi bw''imari', 'Bachelor''s degree in Business Administration or MBA', 'Impamyabumenyi mu bucuruzi cyangwa MBA', 400000, 2000000, 'Growing with expanding business sector', 5),

('Graphic Designer', 'Umushushanyabyuma', 'Create visual content for marketing, branding, and communication.', 'Gukora ibigaragara ku bw''ubucuruzi, gutanga umwirondoro, no gutumaana.', 'Creativity, Design software, Visual communication, Attention to detail', 'Ubwenge, Porogaramu zo gushushanya, Gutumaana biboneka, Kwitonda', 'Diploma or Bachelor''s degree in Graphic Design or Fine Arts', 'Impamyabumenyi mu gushushanya', 200000, 800000, 'Growing with digital marketing demand', 3),

('Agricultural Officer', 'Umuhanga mu buhinzi', 'Advise farmers on modern farming techniques and agricultural practices.', 'Kugira inama abahinzi ku buryo bushya bwo guhinga.', 'Agricultural knowledge, Communication, Problem-solving, Field work', 'Ubumenyi bw''ubuhinzi, Gutumaana, Gukemura ibibazo, Gukora mu murima', 'Bachelor''s degree in Agriculture or related field', 'Impamyabumenyi mu buhinzi', 200000, 700000, 'Important for Rwanda''s agricultural development', 1),

('Journalist', 'Umunyamakuru', 'Research, write, and report news stories across various media.', 'Gushakisha, kwandika, no gutangaza inkuru mu itangazamakuru.', 'Writing, Research, Communication, Critical thinking', 'Kwandika, Gushakisha, Gutumaana, Gutekereza neza', 'Bachelor''s degree in Journalism or Mass Communication', 'Impamyabumenyi mu itangazamakuru', 180000, 600000, 'Evolving with digital media landscape', 3),

('Electrician', 'Umuyoboro w''amashanyarazi', 'Install, maintain, and repair electrical systems and equipment.', 'Gushyiraho, kubungabunga, no gukosora sisitemu z''amashanyarazi.', 'Technical skills, Problem-solving, Safety awareness, Physical fitness', 'Ubumenyi bw''imyuga, Gukemura ibibazo, Kwirinda, Imbaraga', 'TVET diploma or certificate in Electrical Installation', 'Impamyabumenyi y''imyuga y''amashanyarazi', 150000, 500000, 'Essential skill with growing demand', 1),

('Hotel Manager', 'Umuyobozi w''ihoteli', 'Oversee hotel operations, guest services, and staff management.', 'Kuyobora ibikorwa by''ihoteli, serivisi z''abashyitsi, no kuyobora abakozi.', 'Hospitality, Leadership, Communication, Problem-solving', 'Kwakira abantu, Ubuyobozi, Gutumaana, Gukemura ibibazo', 'Bachelor''s degree in Hospitality Management or related field', 'Impamyabumenyi mu kwakira abantu', 300000, 1200000, 'Growing with tourism sector expansion', 5),

('Data Analyst', 'Umusesengura w''amakuru', 'Analyze data to help organizations make informed decisions.', 'Gusesengura amakuru kugira ngo ibigo bifate ibyemezo bifite ishingiro.', 'Statistics, Data tools, Critical thinking, Communication', 'Imibare, Ibikoresho by''amakuru, Gutekereza neza, Gutumaana', 'Bachelor''s degree in Statistics, Computer Science, or related field', 'Impamyabumenyi mu mibare cyangwa ikoranabuhanga', 350000, 1200000, 'High demand in data-driven economy', 2),

('Lawyer', 'Avoka', 'Provide legal advice, represent clients, and uphold justice.', 'Gutanga inama z''amategeko, guhagararira abakiriya, no kurengera ubutabera.', 'Legal knowledge, Communication, Critical thinking, Ethics', 'Ubumenyi bw''amategeko, Gutumaana, Gutekereza neza, Imyitwarire', 'Bachelor''s degree in Law (LLB) and bar examination', 'Impamyabumenyi y''amategeko no kwinjira muri barreau', 400000, 2000000, 'Stable demand with growing legal sector', 5),

('Pharmacist', 'Umufaramasiye', 'Dispense medications and provide pharmaceutical care to patients.', 'Gutanga imiti no gutanga ubuvuzi bw''ubushakashatsi ku barwayi.', 'Pharmaceutical knowledge, Attention to detail, Communication, Ethics', 'Ubumenyi bw''imiti, Kwitonda, Gutumaana, Imyitwarire', 'Bachelor''s degree in Pharmacy', 'Impamyabumenyi mu bufaramasiye', 350000, 1000000, 'Growing with healthcare sector', 2);

-- Link careers to institutions
INSERT INTO career_institutions (career_id, institution_id, program_name_en, duration) VALUES
(1, 1, 'Bachelor of Science in Computer Science', '4 years'),
(1, 2, 'BSc Computer Science', '3 years'),
(1, 3, 'Master of Science in Information Technology', '2 years'),
(1, 4, 'Diploma in Software Development', '2 years'),
(2, 1, 'Bachelor of Medicine and Surgery (MBChB)', '6 years'),
(3, 1, 'Bachelor of Education', '4 years'),
(4, 1, 'Bachelor of Business Administration - Accounting', '4 years'),
(4, 8, 'Bachelor of Commerce - Accounting', '4 years'),
(5, 1, 'Bachelor of Science in Civil Engineering', '5 years'),
(6, 1, 'Bachelor of Science in Nursing', '4 years'),
(6, 4, 'Diploma in Nursing', '3 years'),
(7, 2, 'Bachelor of Business Administration', '3 years'),
(7, 8, 'Bachelor of Business Administration', '4 years'),
(8, 4, 'Diploma in Graphic Design', '2 years'),
(9, 1, 'Bachelor of Science in Agriculture', '4 years'),
(10, 1, 'Bachelor of Arts in Journalism', '4 years'),
(11, 4, 'Diploma in Electrical Installation', '2 years'),
(11, 5, 'Advanced Diploma in Electrical Engineering', '3 years'),
(12, 7, 'Diploma in Hospitality Management', '2 years'),
(13, 1, 'Bachelor of Statistics', '4 years'),
(13, 3, 'Master of Science in Data Science', '2 years'),
(14, 1, 'Bachelor of Laws (LLB)', '4 years'),
(15, 1, 'Bachelor of Pharmacy', '5 years');

-- Assessment Questions (30 questions covering RIASEC categories)
INSERT INTO assessment_questions (question_en, question_rw, category_id, weight, order_number) VALUES
-- Realistic (R) - Category 1
('I enjoy fixing or repairing things with my hands.', 'Nkunda gukosora ibintu nkoresheje intoki zanjye.', 1, 1, 1),
('I like working with tools, machines, or equipment.', 'Nkunda gukora n''ibikoresho, imashini, cyangwa ibikoresho.', 1, 1, 2),
('I prefer practical activities over theoretical discussions.', 'Nkunda ibikorwa bigaragara kuruta ibiganiro by''ibitekerezo.', 1, 1, 3),
('I enjoy building or constructing things.', 'Nkunda kubaka cyangwa gukora ibintu.', 1, 1, 4),
('I like working outdoors or in nature.', 'Nkunda gukora hanze cyangwa mu ibidukikije.', 1, 1, 5),

-- Investigative (I) - Category 2
('I enjoy solving complex problems and puzzles.', 'Nkunda gukemura ibibazo bigoye n''imikino yo gutekereza.', 2, 1, 6),
('I like conducting research to discover new information.', 'Nkunda gukora ubushakashatsi kugira ngo menye ibintu bishya.', 2, 1, 7),
('I am curious about how things work.', 'Ndashishikajwe no kumenya uko ibintu bikora.', 2, 1, 8),
('I prefer analyzing data to make decisions.', 'Nkunda gusesengura amakuru mbere yo gufata ibyemezo.', 2, 1, 9),
('I enjoy studying science, mathematics, or technology.', 'Nkunda kwiga ubumenyi, imibare, cyangwa ikoranabuhanga.', 2, 1, 10),

-- Artistic (A) - Category 3
('I enjoy expressing myself through art, music, or writing.', 'Nkunda kwiyerekana binyuze mu buhanzi, umuziki, cyangwa kwandika.', 3, 1, 11),
('I like creating new and original ideas or designs.', 'Nkunda gukora ibitekerezo bishya n''amashusho.', 3, 1, 12),
('I appreciate beauty and aesthetics in my environment.', 'Nkunda ubwiza mu bidukikije.', 3, 1, 13),
('I prefer activities that allow creativity and imagination.', 'Nkunda ibikorwa binshoboza kuba umuhanzi no gutekereza.', 3, 1, 14),
('I enjoy performing or presenting to others.', 'Nkunda gukinira cyangwa kwerekana imbere y''abandi.', 3, 1, 15),

-- Social (S) - Category 4
('I enjoy helping and supporting other people.', 'Nkunda gufasha no gushyigikira abandi bantu.', 4, 1, 16),
('I like teaching or explaining things to others.', 'Nkunda kwigisha cyangwa gusobanurira abandi ibintu.', 4, 1, 17),
('I am interested in understanding people''s feelings and problems.', 'Nshishikajwe no kumva uko abandi bantu bumva n''ibibazo byabo.', 4, 1, 18),
('I prefer working in teams rather than alone.', 'Nkunda gukora mu itsinda kuruta gukora wenyine.', 4, 1, 19),
('I enjoy volunteering or community service activities.', 'Nkunda gukora ku bushake cyangwa ibikorwa byo gufasha abaturage.', 4, 1, 20),

-- Enterprising (E) - Category 5
('I enjoy leading and directing other people.', 'Nkunda kuyobora no gutanga amabwiriza abandi bantu.', 5, 1, 21),
('I like persuading or convincing others of my ideas.', 'Nkunda kwemeza cyangwa guhamya abandi ibitekerezo byanjye.', 5, 1, 22),
('I am interested in starting my own business someday.', 'Nshishikajwe no gutangira ubucuruzi bwanjye umunsi umwe.', 5, 1, 23),
('I enjoy taking risks to achieve goals.', 'Nkunda gufata ibyago kugira ngo ngere ku ntego.', 5, 1, 24),
('I prefer competitive activities and challenges.', 'Nkunda ibikorwa by''amarushanwa n''imbogamizi.', 5, 1, 25),

-- Conventional (C) - Category 6
('I like organizing and keeping things in order.', 'Nkunda gutunganya no kubika ibintu mu buryo.', 6, 1, 26),
('I enjoy working with numbers, data, and records.', 'Nkunda gukora n''imibare, amakuru, n''inyandiko.', 6, 1, 27),
('I prefer following clear instructions and procedures.', 'Nkunda gukurikiza amabwiriza n''inzira zigaragara.', 6, 1, 28),
('I pay attention to details and accuracy in my work.', 'Ndita ku bisobanuro n''ukuri mu kazi kanjye.', 6, 1, 29),
('I like working in a structured and predictable environment.', 'Nkunda gukora ahantu hafite amategeko n''ahashobora kumenyekana.', 6, 1, 30);

-- Create default admin user (password: admin123 - should be changed immediately)
INSERT INTO users (email, password, first_name, last_name, role, is_active) VALUES
('admin@inkingiX.rw', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator', 'system_admin', 1);

-- Create indexes for better performance
CREATE INDEX idx_careers_primary_cat ON careers(primary_category_id);
CREATE INDEX idx_questions_category ON assessment_questions(category_id);
CREATE INDEX idx_responses_assessment ON assessment_responses(assessment_id);
CREATE INDEX idx_results_assessment ON assessment_results(assessment_id);
