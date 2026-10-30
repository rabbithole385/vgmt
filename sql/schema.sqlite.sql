-- VMGT BOOKINGS - SQLite schema (used automatically on Render by bin/bootstrap.php)
-- Generated alongside schema.sql (MySQL). Timestamps default to Lagos time (UTC+1, no DST).

CREATE TABLE IF NOT EXISTS admins (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  username TEXT NOT NULL UNIQUE,
  email TEXT NOT NULL,
  password_hash TEXT NOT NULL,
  role TEXT NOT NULL DEFAULT 'editor' CHECK (role IN ('super_admin','editor')),
  status TEXT NOT NULL DEFAULT 'active' CHECK (status IN ('active','disabled')),
  last_login_at TEXT NULL,
  created_at TEXT NOT NULL DEFAULT (datetime('now','+1 hour'))
);

CREATE TABLE IF NOT EXISTS celebrities (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  slug TEXT NOT NULL UNIQUE,
  category TEXT NOT NULL DEFAULT '',
  short_bio TEXT NULL,
  bio TEXT NULL,
  image TEXT NULL,
  base_fee_note TEXT NULL,
  is_featured INTEGER NOT NULL DEFAULT 0,
  status TEXT NOT NULL DEFAULT 'published' CHECK (status IN ('published','draft')),
  sort_order INTEGER NOT NULL DEFAULT 0,
  created_at TEXT NOT NULL DEFAULT (datetime('now','+1 hour')),
  updated_at TEXT NOT NULL DEFAULT (datetime('now','+1 hour'))
);

CREATE TABLE IF NOT EXISTS bookings (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  celebrity_id INTEGER NULL REFERENCES celebrities(id) ON DELETE SET NULL,
  celebrity_name_snapshot TEXT NULL,
  full_name TEXT NOT NULL,
  email TEXT NOT NULL,
  phone TEXT NULL,
  organization TEXT NULL,
  event_type TEXT NULL,
  event_date TEXT NULL,
  event_location TEXT NULL,
  budget_range TEXT NULL,
  message TEXT NULL,
  status TEXT NOT NULL DEFAULT 'pending' CHECK (status IN ('pending','contacted','confirmed','declined')),
  admin_notes TEXT NULL,
  created_at TEXT NOT NULL DEFAULT (datetime('now','+1 hour')),
  updated_at TEXT NOT NULL DEFAULT (datetime('now','+1 hour'))
);

CREATE TABLE IF NOT EXISTS inquiries (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  email TEXT NOT NULL,
  subject TEXT NULL,
  message TEXT NOT NULL,
  status TEXT NOT NULL DEFAULT 'new' CHECK (status IN ('new','read','replied')),
  created_at TEXT NOT NULL DEFAULT (datetime('now','+1 hour'))
);

CREATE TABLE IF NOT EXISTS blog_posts (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  title TEXT NOT NULL,
  slug TEXT NOT NULL UNIQUE,
  excerpt TEXT NULL,
  content TEXT NULL,
  featured_image TEXT NULL,
  status TEXT NOT NULL DEFAULT 'draft' CHECK (status IN ('draft','published')),
  published_at TEXT NULL,
  created_at TEXT NOT NULL DEFAULT (datetime('now','+1 hour')),
  updated_at TEXT NOT NULL DEFAULT (datetime('now','+1 hour'))
);

CREATE TABLE IF NOT EXISTS subscribers (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  email TEXT NOT NULL UNIQUE,
  created_at TEXT NOT NULL DEFAULT (datetime('now','+1 hour'))
);

CREATE TABLE IF NOT EXISTS site_settings (
  setting_key TEXT PRIMARY KEY,
  setting_value TEXT NULL
);

INSERT OR IGNORE INTO site_settings (setting_key, setting_value) VALUES
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
('youtube_url', '#');

INSERT OR IGNORE INTO celebrities (name, slug, category, image, is_featured, sort_order) VALUES
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
('Taron Egerton', 'taron-egerton', 'Actor', 'Taron-Egerton.png', 1, 11);

