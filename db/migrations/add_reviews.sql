-- Migration: add reviews table for social proof
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
