# Flynn's Cozy Staycation

Self-contained PHP + PostgreSQL platform for a transient studio rental in Fairview, Quezon City. No framework, no npm, no Composer. Guests browse rooms, generate quotes, and book through Facebook Messenger.

---

## Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.2+, strict types, zero framework |
| Database | PostgreSQL 15+ (local Docker or Supabase) |
| Frontend | Vanilla JS (ES6), CSS custom properties |
| Icons | Tabler SVG (vendored) |
| Media | Google Drive embeds via `drive_image_url()` |

---

## Structure

```
├── index.php                # Homepage
├── rooms.php                # Room listing
├── room.php                 # Room detail + quote calculator
├── gallery.php              # Photo & video gallery
├── availability.php         # Public availability calendar
├── faq.php / house-rules.php / privacy.php
├── 404.php
│
├── admin/
│   ├── index.php            # Dashboard
│   ├── login.php / logout.php
│   ├── rooms.php / room_edit.php
│   ├── photos.php           # Drag-to-reorder media manager
│   ├── rates.php
│   ├── amenities.php
│   ├── addons.php
│   ├── reviews.php
│   ├── faqs.php
│   ├── calendar.php         # Slot blocking + announcements
│   └── settings.php
│
├── lib/
│   ├── bootstrap.php        # Init, CSP headers, helpers
│   ├── auth.php             # Session, CSRF, rate limiting
│   ├── calc.php             # Quote engine + Messenger URL builder
│   ├── db.php               # PDO singleton
│   ├── repo.php             # All DB queries
│   └── policies.php         # Canonical policy copy
│
├── includes/
│   ├── icons.php
│   ├── public_header.php / public_footer.php
│   └── admin_header.php / admin_footer.php
│
├── assets/css/              # site.css, admin.css
├── assets/js/               # site.js, availability.js
├── assets/icons/            # Tabler SVGs
│
├── db/
│   ├── schema.sql
│   ├── seed.sql
│   └── migrations/
│
└── scripts/
    ├── db_init.php
    └── create_admin.php
```

---

## Local Setup

**Requirements:** PHP 8.2+ with `pdo_pgsql`, `mbstring`, `openssl` · PostgreSQL 15+

### 1. Start the database

```sh
docker run -d --name flynns-pg \
  -e POSTGRES_PASSWORD=devpass \
  -e POSTGRES_DB=flynns \
  -p 55432:5432 \
  postgres:17-alpine
```

### 2. Configure environment

```sh
cp .env.example .env
```

```ini
DB_DSN=pgsql:host=127.0.0.1;port=55432;dbname=flynns
DB_USER=postgres
DB_PASS=devpass
APP_DEBUG=1
BASE_URL=
APP_TZ=Asia/Manila
CURRENCY=₱
```

### 3. Apply schema and seed data

```sh
php scripts/db_init.php
```

### 4. Create the admin account

```sh
php scripts/create_admin.php owner@example.com "Your Name"
# prompts for password
```

### 5. Start the dev server

```sh
php -S 127.0.0.1:8000
```

- Site: http://127.0.0.1:8000
- Admin: http://127.0.0.1:8000/admin/login.php

---

## Production

### Supabase

```ini
DATABASE_URL=postgresql://postgres.<ref>:<password>@<region>.pooler.supabase.com:5432/postgres?sslmode=require
```

```sh
php scripts/db_init.php
php scripts/create_admin.php owner@yourdomain.com "Owner Name"
```

### Web server

- Set `APP_DEBUG=0`
- Serve from the project root; block access to `.env`, `db/`, `scripts/`, `tests/`, `.git/`

```nginx
location ~ ^/(\.env|db|scripts|tests|\.git) {
    deny all;
}
```

### Google Drive media

1. Upload photo or video to Google Drive
2. Set share to **Anyone with the link — Viewer**
3. Paste the share URL into the CMS media manager
4. The app converts it automatically via `drive_image_url()`

---

## Security

- `script-src 'self'` CSP — no inline scripts
- CSRF tokens on all admin POST handlers
- Login rate-limited to 5 attempts per 15 minutes
- PDO prepared statements throughout (`EMULATE_PREPARES => false`)
- `e()` helper for context-aware HTML escaping

---

## Pre-launch

- [ ] `APP_DEBUG=0` and `sslmode=require` on `DATABASE_URL`
- [ ] Set `messenger_url` in Admin → Settings
- [ ] Confirm room rates, deposit tiers, and weekend definitions
- [ ] Replace placeholder photos with real studio shots

---

## License

Proprietary. All rights reserved © Flynn's Cozy Staycation.
