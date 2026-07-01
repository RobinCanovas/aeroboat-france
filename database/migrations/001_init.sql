-- Schéma initial (contrat figé). Portable SQLite / MySQL : voir docs/CONTRACTS.md §3.

CREATE TABLE {{PREFIX}}users (
  id {{PK}},
  username VARCHAR(60) NOT NULL,
  display_name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NULL,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(20) NOT NULL,
  active INTEGER NOT NULL,
  last_login_at DATETIME NULL,
  created_at DATETIME NOT NULL
) {{OPTS}};

CREATE UNIQUE INDEX {{PREFIX}}users_username ON {{PREFIX}}users (username);

CREATE TABLE {{PREFIX}}throttle (
  id {{PK}},
  bucket VARCHAR(60) NOT NULL,
  ip_hash VARCHAR(64) NOT NULL,
  created_at INTEGER NOT NULL
) {{OPTS}};

CREATE INDEX {{PREFIX}}throttle_lookup ON {{PREFIX}}throttle (bucket, ip_hash, created_at);

CREATE TABLE {{PREFIX}}posts (
  id {{PK}},
  slug_fr VARCHAR(160) NOT NULL,
  slug_en VARCHAR(160) NOT NULL,
  title_fr VARCHAR(200) NOT NULL,
  title_en VARCHAR(200) NOT NULL,
  excerpt_fr VARCHAR(400) NULL,
  excerpt_en VARCHAR(400) NULL,
  body_fr TEXT NOT NULL,
  body_en TEXT NOT NULL,
  cover VARCHAR(190) NULL,
  cover_alt_fr VARCHAR(200) NULL,
  cover_alt_en VARCHAR(200) NULL,
  status VARCHAR(12) NOT NULL,
  publish_at DATETIME NULL,
  author_id INTEGER NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL
) {{OPTS}};

CREATE UNIQUE INDEX {{PREFIX}}posts_slug_fr ON {{PREFIX}}posts (slug_fr);

CREATE UNIQUE INDEX {{PREFIX}}posts_slug_en ON {{PREFIX}}posts (slug_en);

CREATE INDEX {{PREFIX}}posts_status ON {{PREFIX}}posts (status, publish_at);

CREATE TABLE {{PREFIX}}team_members (
  id {{PK}},
  name VARCHAR(120) NOT NULL,
  role_fr VARCHAR(160) NULL,
  role_en VARCHAR(160) NULL,
  bio_fr TEXT NULL,
  bio_en TEXT NULL,
  photo VARCHAR(190) NULL,
  photo_consent INTEGER NOT NULL,
  linkedin VARCHAR(250) NULL,
  sort_order INTEGER NOT NULL,
  published INTEGER NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL
) {{OPTS}};

CREATE TABLE {{PREFIX}}partners (
  id {{PK}},
  name VARCHAR(160) NOT NULL,
  category VARCHAR(30) NOT NULL,
  description_fr TEXT NULL,
  description_en TEXT NULL,
  logo VARCHAR(190) NULL,
  logo_consent INTEGER NOT NULL,
  url VARCHAR(250) NULL,
  sort_order INTEGER NOT NULL,
  published INTEGER NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL
) {{OPTS}};

CREATE TABLE {{PREFIX}}applications (
  id {{PK}},
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL,
  school VARCHAR(160) NULL,
  level VARCHAR(100) NULL,
  skills TEXT NULL,
  motivation TEXT NOT NULL,
  availability VARCHAR(250) NULL,
  cv_file VARCHAR(100) NULL,
  consent_at DATETIME NOT NULL,
  status VARCHAR(20) NOT NULL,
  notes TEXT NULL,
  locale VARCHAR(2) NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL
) {{OPTS}};

CREATE INDEX {{PREFIX}}applications_status ON {{PREFIX}}applications (status, created_at);

CREATE TABLE {{PREFIX}}messages (
  id {{PK}},
  kind VARCHAR(12) NOT NULL,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  subject VARCHAR(200) NULL,
  request_type VARCHAR(20) NULL,
  body TEXT NOT NULL,
  locale VARCHAR(2) NOT NULL,
  handled INTEGER NOT NULL,
  consent_at DATETIME NOT NULL,
  created_at DATETIME NOT NULL
) {{OPTS}};

CREATE INDEX {{PREFIX}}messages_handled ON {{PREFIX}}messages (handled, created_at);
