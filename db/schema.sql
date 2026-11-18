-- ============================================================
-- Flynn's Cozy Staycation — PostgreSQL / Supabase schema
-- ============================================================
-- Showcase + quote calculator. No bookings, no checkout.
-- Every public page reads from these tables, so the owner can
-- change content without touching code.
-- ============================================================

-- Admin accounts (owner only). Single role by design.
CREATE TABLE IF NOT EXISTS admin_users (
  id            SERIAL       PRIMARY KEY,
  email         TEXT         NOT NULL UNIQUE,
  password_hash TEXT         NOT NULL,
  display_name  TEXT         NOT NULL DEFAULT 'Owner',
  is_active     BOOLEAN      NOT NULL DEFAULT TRUE,
  last_login_at TIMESTAMPTZ,
  created_at    TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);


-- ============================================================
-- LOCATION
-- ============================================================
CREATE TABLE IF NOT EXISTS buildings (
  id        SERIAL PRIMARY KEY,
  name      TEXT   NOT NULL UNIQUE,
  address   TEXT,
  landmarks TEXT,          -- "SM Fairview, Ayala Terraces, ..."
  map_url   TEXT
);


-- ============================================================
-- ROOMS
-- ============================================================
CREATE TABLE IF NOT EXISTS units (
  id           SERIAL      PRIMARY KEY,
  slug         TEXT        NOT NULL UNIQUE,
  name         TEXT        NOT NULL,          -- "Unit 2"
  tagline      TEXT,                          -- "Simple Modern"
  building_id  INT         REFERENCES buildings(id) ON DELETE SET NULL,
  description  TEXT,
  size_sqm     NUMERIC(5,2),
  floor_no     INT,
  bed_type     TEXT,                          -- Queen / Double
  capacity_max INT         NOT NULL DEFAULT 4,
  view_note    TEXT,                          -- "City view w/ balcony"
  is_published BOOLEAN     NOT NULL DEFAULT TRUE,
  sort_order   INT         NOT NULL DEFAULT 0,
  created_at   TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at   TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS unit_photos (
  id         SERIAL PRIMARY KEY,
  unit_id    INT    NOT NULL REFERENCES units(id) ON DELETE CASCADE,
  url        TEXT   NOT NULL,               -- storage path or absolute URL
  caption    TEXT,
  media_type TEXT NOT NULL DEFAULT 'image' CHECK (media_type IN ('image', 'video')),
  poster_url TEXT,
  sort_order INT    NOT NULL DEFAULT 0,
  is_cover   BOOLEAN NOT NULL DEFAULT FALSE
);
ALTER TABLE unit_photos ADD COLUMN IF NOT EXISTS media_type TEXT NOT NULL DEFAULT 'image';
ALTER TABLE unit_photos ADD COLUMN IF NOT EXISTS poster_url TEXT;
CREATE INDEX IF NOT EXISTS unit_photos_unit_idx ON unit_photos(unit_id);


-- ============================================================
-- INCLUSIONS
-- ============================================================
CREATE TABLE IF NOT EXISTS amenities (
  id         SERIAL PRIMARY KEY,
  name       TEXT   NOT NULL UNIQUE,
  category   TEXT   NOT NULL DEFAULT 'General',  -- Living, Media, Kitchen, Safety
  icon       TEXT,
  sort_order INT    NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS unit_amenities (
  unit_id    INT  NOT NULL REFERENCES units(id) ON DELETE CASCADE,
  amenity_id INT  NOT NULL REFERENCES amenities(id) ON DELETE CASCADE,
  note       TEXT,
  PRIMARY KEY (unit_id, amenity_id)
);


-- ============================================================
-- PRICING
-- ============================================================
-- Base rate per unit, per day type, per slot, per stay length.
-- "slot_type" is the real-world distinction on the calendar:
-- overnight (7pm-7am) vs daycation (7am-7pm), both 12 hours.
CREATE TABLE IF NOT EXISTS rates (
  id        SERIAL PRIMARY KEY,
  unit_id   INT    NOT NULL REFERENCES units(id) ON DELETE CASCADE,
  day_type  TEXT   NOT NULL CHECK (day_type IN ('weekday', 'weekend_holiday')),
  slot_type TEXT   NOT NULL DEFAULT 'flexible'
                   CHECK (slot_type IN ('overnight', 'daycation', 'flexible')),
  hours     INT    NOT NULL CHECK (hours > 0),
  amount    NUMERIC(10,2) NOT NULL CHECK (amount >= 0),
  label     TEXT,
  UNIQUE (unit_id, day_type, slot_type, hours)
);

-- Flat add-ons: extra hour, extra pax, parking.
CREATE TABLE IF NOT EXISTS addons (
  id          SERIAL PRIMARY KEY,
  code        TEXT   NOT NULL UNIQUE,
  label       TEXT   NOT NULL,
  amount      NUMERIC(10,2) NOT NULL DEFAULT 0,
  unit_label  TEXT,                        -- "per hour", "per pax", "per stay"
  category    TEXT   NOT NULL DEFAULT 'extra' CHECK (category IN ('extra', 'parking', 'pool')),
  description TEXT,
  sort_order  INT    NOT NULL DEFAULT 0,
  is_active   BOOLEAN NOT NULL DEFAULT TRUE
);

-- Pool fee depends on the unit and the day type.
CREATE TABLE IF NOT EXISTS pool_rates (
  unit_id  INT  NOT NULL REFERENCES units(id) ON DELETE CASCADE,
  day_type TEXT NOT NULL CHECK (day_type IN ('weekday', 'weekend_holiday')),
  amount   NUMERIC(10,2) NOT NULL,
  PRIMARY KEY (unit_id, day_type)
);

-- Refundable deposit tiers, matched by stay length.
CREATE TABLE IF NOT EXISTS deposit_rules (
  min_hours INT NOT NULL PRIMARY KEY,
  amount    NUMERIC(10,2) NOT NULL,
  label     TEXT
);

-- Drives weekday/weekend detection from a picked date.
CREATE TABLE IF NOT EXISTS holidays (
  holiday_date DATE PRIMARY KEY,
  name         TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS availability_blocks (
  id         SERIAL PRIMARY KEY,
  block_date DATE NOT NULL,
  slot       TEXT NOT NULL DEFAULT 'all' CHECK (slot IN ('overnight','daycation','all')),
  note       TEXT NOT NULL DEFAULT '',
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  UNIQUE (block_date, slot)
);


-- ============================================================
-- CONTENT
-- ============================================================
CREATE TABLE IF NOT EXISTS faqs (
  id           SERIAL PRIMARY KEY,
  question     TEXT   NOT NULL UNIQUE,
  answer       TEXT   NOT NULL,
  sort_order   INT    NOT NULL DEFAULT 0,
  is_published BOOLEAN NOT NULL DEFAULT TRUE
);

-- Global copy: brand, Messenger link, check-in window, etc.
CREATE TABLE IF NOT EXISTS settings (
  key   TEXT PRIMARY KEY,
  value TEXT NOT NULL DEFAULT ''
);

CREATE TABLE IF NOT EXISTS announcements (
  id           SERIAL PRIMARY KEY,
  title        TEXT NOT NULL,
  body         TEXT NOT NULL DEFAULT '',
  start_date   DATE,
  end_date     DATE,
  is_published BOOLEAN NOT NULL DEFAULT FALSE,
  created_at   TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  CHECK (start_date IS NULL OR end_date IS NULL OR end_date >= start_date)
);

CREATE TABLE IF NOT EXISTS reviews (
  id           SERIAL PRIMARY KEY,
  guest_name   TEXT NOT NULL,
  review_date  DATE,
  rating       INT CHECK (rating BETWEEN 1 AND 5),
  body         TEXT NOT NULL,
  source       TEXT,
  is_published BOOLEAN NOT NULL DEFAULT TRUE,
  sort_order   INT NOT NULL DEFAULT 0,
  created_at   TIMESTAMPTZ NOT NULL DEFAULT NOW()
);


-- ============================================================
-- AUDIT
-- ============================================================
CREATE TABLE IF NOT EXISTS audit_logs (
  id         BIGSERIAL PRIMARY KEY,
  user_id    INT REFERENCES admin_users(id) ON DELETE SET NULL,
  action     TEXT NOT NULL,               -- create / update / delete
  table_name TEXT NOT NULL,
  record_id  TEXT,
  old_values JSONB,
  new_values JSONB,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);


-- ============================================================
-- HOUSEKEEPING
-- ============================================================
CREATE OR REPLACE FUNCTION set_updated_at() RETURNS TRIGGER
LANGUAGE plpgsql AS $$
BEGIN
  NEW.updated_at = NOW();
  RETURN NEW;
END;
$$;

DROP TRIGGER IF EXISTS units_touch ON units;
CREATE TRIGGER units_touch
  BEFORE UPDATE ON units
  FOR EACH ROW EXECUTE FUNCTION set_updated_at();
