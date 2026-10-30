-- VMGT BOOKINGS - Database Schema
-- Import this once via phpMyAdmin (cPanel) into your MySQL database.
-- Charset: utf8mb4

SET NAMES utf8mb4;

-- ---------------------------------------------------------------
-- Admin users
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admins (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  username VARCHAR(60) NOT NULL UNIQUE,
  email VARCHAR(150) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('super_admin','editor') NOT NULL DEFAULT 'editor',
  status ENUM('active','disabled') NOT NULL DEFAULT 'active',
  last_login_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- No admin account is seeded here on purpose (a hardcoded password hash in a
-- public SQL file is a bad idea). After importing this schema, open
-- /install/create_admin.php in your browser once to create your first admin
-- account with a real password of your choosing. That script locks itself
-- after it has created the first super_admin.

-- ---------------------------------------------------------------
-- Celebrities
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS celebrities (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  slug VARCHAR(180) NOT NULL UNIQUE,
  category VARCHAR(150) NOT NULL DEFAULT '',
  short_bio VARCHAR(300) NULL,
  bio TEXT NULL,
  image VARCHAR(255) NULL,
  base_fee_note VARCHAR(150) NULL,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('published','draft') NOT NULL DEFAULT 'published',
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------
-- Bookings (multi-step booking form submissions)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS bookings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  celebrity_id INT UNSIGNED NULL,
  celebrity_name_snapshot VARCHAR(150) NULL,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL,
  phone VARCHAR(40) NULL,
  organization VARCHAR(150) NULL,
  event_type VARCHAR(150) NULL,
  event_date DATE NULL,
  event_location VARCHAR(200) NULL,
  budget_range VARCHAR(60) NULL,
  message TEXT NULL,
  status ENUM('pending','contacted','confirmed','declined') NOT NULL DEFAULT 'pending',
  admin_notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (celebrity_id) REFERENCES celebrities(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------
-- General contact inquiries
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS inquiries (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL,
  subject VARCHAR(200) NULL,
  message TEXT NOT NULL,
  status ENUM('new','read','replied') NOT NULL DEFAULT 'new',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------
-- Blog / News
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS blog_posts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  slug VARCHAR(220) NOT NULL UNIQUE,
  excerpt VARCHAR(400) NULL,
  content LONGTEXT NULL,
  featured_image VARCHAR(255) NULL,
  status ENUM('draft','published') NOT NULL DEFAULT 'draft',
  published_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------
-- Newsletter subscribers
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS subscribers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(150) NOT NULL UNIQUE,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------
-- Site settings (key/value store for editable site content)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS site_settings (
  setting_key VARCHAR(100) PRIMARY KEY,
  setting_value LONGTEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO site_settings (setting_key, setting_value) VALUES
('site_name', 'VMGT BOOKINGS'),
('tagline', 'Book Your Favourite Celebrity'),
('hero_subtitle', 'We are available round the globe'),
('about_title', 'About Us'),
('about_text', 'VMGT BOOKINGS is a full-service talent booking agency, specifically focused on the needs of event professionals looking to book keynote speakers, public figures and corporate entertainment for their events. Our mission is to provide a best-in-class talent booking experience from start to finish. We connect audiences with innovative minds and powerful messages and entertainment, regardless of the industry.'),
('cta_title', 'Book A Celeb Now!'),
('cta_text', 'Are you looking to book keynote speakers, public figures and corporate entertainment for your events? Our mission is to provide a best-in-class talent booking experience from start to finish.'),
('contact_email', 'info@vmgtbookings.com'),
('contact_phone', '+234 800 000 0000'),
('contact_address', 'Abuja, Nigeria'),
('facebook_url', '#'),
('twitter_url', '#'),
('instagram_url', '#'),
('youtube_url', '#')
ON DUPLICATE KEY UPDATE setting_key = setting_key;

-- ---------------------------------------------------------------
-- Seed celebrities (carried over from the original static site)
-- ---------------------------------------------------------------
INSERT INTO celebrities (name, slug, category, image, is_featured, sort_order) VALUES
('Jane Wane', 'jane-wane', 'Singer', 'jane-wane.png', 1, 1),
('Hugh Michael Jackman', 'hugh-michael-jackman', 'Actor', 'Hugh-Michael-Jackman.png', 1, 2),
('Park Seo-joon', 'park-seo-joon', 'Actor', 'Park-Seo-joon.png', 1, 3),
('Jason Momoa', 'jason-momoa', 'Actor, Filmmaker', 'Jason-Momoa.png', 1, 4),
('Calum Scott', 'calum-scott', 'Singer, Songwriter', 'Calum-Scott.png', 1, 5),
('Keanu Reeves', 'keanu-reeves', 'Actor', 'Keanu-Reeves.png', 1, 6),
('Chen Kun', 'chen-kun', 'Actor, Singer, Producer', 'Chen-Kun.png', 1, 7),
('Jung Il-woo', 'jung-il-woo', 'Actor, TV Host', 'Jung-Il-woo.png', 1, 8),
('Michele Morrone', 'michele-morrone', 'Actor, Singer, Model, Fashion Designer', 'MICHELE-MORRONE.png', 1, 9),
('Song Joong Ki', 'song-joong-ki', 'Actor', 'SONG-JOONG-KI.png', 1, 10),
('Taron Egerton', 'taron-egerton', 'Actor', 'Taron-Egerton.png', 1, 11)
ON DUPLICATE KEY UPDATE slug = slug;
