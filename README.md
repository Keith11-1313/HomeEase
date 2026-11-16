# Flynn's Cozy Staycation

> **Fairview, Quezon City Transient Stays Showcase & Quote Engine**  
> A lightweight, dependency-free PHP 8.2+ & PostgreSQL platform designed for transient studio stays. Built for speed, security, and conversion via Facebook Messenger.


---


## Table of Contents


- [Overview](#overview)
- [Key Features](#key-features)
  - [Public Guest Experience](#public-guest-experience)
  - [Quote Calculation Engine](#quote-calculation-engine)
  - [Owner Content Management System (CMS)](#owner-content-management-system-cms)
  - [Security & Architecture Highlights](#security--architecture-highlights)
- [Tech Stack](#tech-stack)
- [Directory Structure](#directory-structure)
- [Getting Started](#getting-started)
  - [System Requirements](#system-requirements)
  - [Local Setup](#local-setup)
  - [Running Tests](#running-tests)
- [Database Schema & Data Models](#database-schema--data-models)
- [Production Deployment (Supabase & Web Server)](#production-deployment-supabase--web-server)
- [Media Management & Video Embeds](#media-management--video-embeds)
- [Pre-Launch Checklist](#pre-launch-checklist)
- [Attribution & Credits](#attribution--credits)


---


## Overview


**Flynn's Cozy Staycation** showcases studio apartments in Fairview, Quezon City (*Trees Residences* and *Milan Residenze*).

Unlike heavy e-commerce booking systems, this platform implements a **zero-friction, conversational booking model**:

- **No guest accounts, cart checkouts, or on-site payment processing.**
- Guests explore studios, inspect verified inclusions, review transparent slot-based rates, and generate tailored quotes.
- The system packages quotes into prefilled, one-click **Facebook Messenger** inquiry links where booking verification, downpayment, and keyless check-in instructions occur directly with the owner.


---

## Key Features

### Public Guest Experience

- **12-Hour Slot Distinction**: Tailored for transient staycation patterns:
  - 🌙 **Overnight**: 7:00 PM – 7:00 AM
  - ☀️ **Daycation**: 7:00 AM – 7:00 PM
  - 🔄 **Flexible / Multi-day**: Up to 72 hours
- **Studio Showcase**: Bento-style photo galleries, Google Drive video walkthrough tours, and categorized amenity badges (Living, Media, Kitchen, Bathroom, Safety, Outdoor).
- **Interactive Availability Calendar**: Multi-month visual calendar indicating unavailable slots (`Overnight`, `Daycation`, `Fully booked`) and owner announcement windows.
- **Neighborhood Guide**: Embedded Google Maps and transportation details for SM Fairview, Ayala Fairview Terraces, and Robinsons Novaliches.
- **Dynamic Header Ink**: Client script automatically adjusts header contrast dynamically when scrolling over light or dark background sections.

### Quote Calculation Engine

- **Slot & Calendar-Aware Rates**: Automatically resolves weekday vs. weekend/holiday pricing (powered by a dedicated `holidays` database table).
- **Itemized Extras**:
  - Extra hours beyond base stay
  - Additional guests above the base allowance (configurable in settings)
  - Motorcycle & car parking options (reserved or pay parking)
  - Complex-specific pool access rates
- **Refundable Security Deposit**: Dynamic tier calculation matching duration of stay, presented separately to avoid misleading totals.
- **Prefilled Messenger Hand-off**: Converts estimated totals, stay details, date, and room name into a URL-encoded Messenger conversation opener.

### Owner Content Management System (CMS)

- **Dashboard & Metric Overview**: Real-time counters for units, media items, inclusions, and active FAQs.
- **Complete Studio Management**: Edit room taglines, specs (square meters, floor level, bed type, max guests), and toggle published status.
- **Media Manager with Drag-to-Reorder**: Manage cover images, gallery photos, and Google Drive video previews with instant reordering via HTML5 drag-and-drop.
- **Dynamic Rates & Add-ons**: Manage slot rates, extra hour/pax surcharges, parking options, pool fees, and deposit rules without touching code.
- **Calendar & Announcement Controller**: Block off unavailable slots and publish date-ranged alert banners.
- **Full Audit Trail**: Database-level logging (`audit_logs`) tracking administrative actions with JSON snapshots of old and new values.

### Security & Architecture Highlights

- **Zero Third-Party Runtime Dependencies**: No Composer, npm, or node_modules bloat.
- **Native Prepared Statements**: Comprehensive protection against SQL injection via PDO (`PDO::ERRMODE_EXCEPTION`, `PDO::EMULATE_PREPARES => false`).
- **Strict Content Security Policy (CSP)**: Hardened headers (`nosniff`, `SAMEORIGIN`, `strict-origin-when-cross-origin`, restrictive CSP directives).
- **Brute-Force Login Throttling**: Session-backed authentication rate limiting (maximum 5 failed attempts per 15-minute window).
- **CSRF Defense**: Cryptographic CSRF tokens validated on all administrative mutations.
- **XSS Protection**: Consistent context-aware sanitization helper `e()`.


---


## Tech Stack


| Component | Technology | Description |
| :--- | :--- | :--- |
| **Backend** | PHP 8.2+ | Native vanilla PHP (strict typing, zero framework overhead) |
| **Database** | PostgreSQL 15+ | Local Docker Alpine or cloud-hosted on Supabase |
| **Database Layer** | PDO (`pdo_pgsql`) | Prepared queries with connection pooling compatibility |
| **Frontend** | Vanilla ES6+ & CSS3 | Semantic markup, CSS Custom Properties, `IntersectionObserver` |
| **Iconography** | Tabler SVG Icons | Vendored inline/external SVG asset system |
| **Storage / Media** | HTTPS & Google Drive | External CDN/image URLs & Google Drive video embeds |

---

## Directory Structure

```text
HomeEase/
├── .env.example             # Template for local and production environment variables
├── .htaccess                # Apache routing and security rewrite rules
├── config.php               # Environment parser and PostgreSQL DSN configuration
├── 404.php                  # Branded 404 error page
├── index.php                # Homepage (Hero, Stay Types, Showcase, Steps, Maps, FAQs)
├── rooms.php                # Studio catalog listing page
├── room.php                 # Individual room detail, bento gallery, rates & calculator
├── gallery.php              # Full visual photo & video gallery
├── availability.php         # Public informational availability calendar
├── calculator.php           # AJAX POST endpoint for asynchronous quote estimates
├── faq.php                  # Comprehensive guest FAQ page
├── admin/                   # Owner CMS portal
│   ├── index.php            # Dashboard with overview metrics & audit log
│   ├── login.php / logout   # Authenticated session handlers with rate limiting
│   ├── rooms.php / edit     # Room catalog and studio editor
│   ├── photos.php           # Media manager (drag-to-reorder, video & photo links)
│   ├── rates.php            # Pricing matrix management
│   ├── amenities.php        # Inclusions management
│   ├── addons.php           # Add-ons and refundable deposit tiers
│   ├── calendar.php         # Slot blocking & announcement banners
│   └── settings.php         # Global site settings (Messenger URL, copy, address)
├── assets/
│   ├── css/                 # Modern styling (site.css, admin.css)
│   ├── js/                  # Vanilla scripts (site.js, availability.js)
│   ├── icons/               # Tabler SVG icon library
│   ├── img/                 # Favicon and core brand marks
│   └── credits.json         # Image provenance & CC license attributions
├── db/
│   ├── schema.sql           # Complete PostgreSQL DDL with triggers & indices
│   ├── seed.sql             # Idempotent starter data (rooms, rates, inclusions)
│   └── migrations/          # Incremental schema evolution scripts
├── includes/
│   ├── icons.php            # SVG helper and brand logo lockup generator
│   ├── public_header.php    # Shared public layout header
│   ├── public_footer.php    # Shared public layout footer & license notices
│   ├── admin_header.php     # CMS navigation shell & flash messaging
│   └── admin_footer.php     # CMS layout footer & modal dialogs
├── lib/
│   ├── auth.php             # Authentication, session, and CSRF routines
│   ├── bootstrap.php        # Core init (error handlers, CSP, helpers, sessions)
│   ├── calc.php             # Quote arithmetic & Messenger URL formatting
│   ├── db.php               # PDO singleton connection provider
│   └── repo.php             # Data access repository (read/write queries)
├── scripts/
│   ├── db_init.php          # CLI utility to apply schema and seed data
│   └── create_admin.php     # CLI utility to securely provision owner accounts
└── tests/
    ├── admin_inputs.php     # Unit checks for media sanitization & URL parsing
    └── auth_throttle.php    # Unit checks for login rate limiting logic
```

---

## Getting Started

### System Requirements

- **PHP 8.2 or higher** with extensions: `pdo_pgsql`, `mbstring`, `openssl`, `fileinfo`
- **PostgreSQL 15+** (Local container or remote Supabase instance)
- **Docker** (Optional, for local database container)


### Local Setup

#### 1. Start Local PostgreSQL Database

Run a local PostgreSQL container using Docker:


```powershell
docker run -d --name flynns-pg -e POSTGRES_PASSWORD=devpass -e POSTGRES_DB=flynns -p 55432:5432 postgres:17-alpine
```

#### 2. Configure Environment Variables

Copy `.env.example` to `.env`:


```powershell
Copy-Item .env.example .env
```

Ensure `.env` matches your local database settings:

```ini
DB_DSN=pgsql:host=127.0.0.1;port=55432;dbname=flynns
DB_USER=postgres
DB_PASS=devpass
APP_DEBUG=1
BASE_URL=
APP_TZ=Asia/Manila
CURRENCY=₱
```


#### 3. Initialize Database Schema & Seed Data

Execute the idempotent database initialization script:

```powershell
php scripts/db_init.php
```

#### 4. Provision the Admin Owner Account

Create your primary CMS login credentials:

```powershell
$env:ADMIN_PASSWORD = Read-Host -AsSecureString "Enter owner password" | ConvertFrom-SecureString -AsPlainText
php scripts/create_admin.php owner@example.com "Studio Owner"
Remove-Item Env:ADMIN_PASSWORD
```

> [!NOTE]
> Passwords must be at least 8 characters. The script hashes passwords using PHP's native `PASSWORD_DEFAULT` (Bcrypt/Argon2).

#### 5. Launch the Development Server

Start PHP's built-in web server:

```powershell
php -S 127.0.0.1:8000
```

- Public Showcase: [http://127.0.0.1:8000](http://127.0.0.1:8000)
- Owner CMS: [http://127.0.0.1:8000/admin/login.php](http://127.0.0.1:8000/admin/login.php)

---

### Running Tests

Run the assertion test suites:

```powershell
php tests/admin_inputs.php
php tests/auth_throttle.php
```

---

## Database Schema & Data Models

The database design uses strict foreign key cascading and triggers:

```
┌──────────────┐         ┌──────────────┐         ┌──────────────────┐
│  buildings   │◄────────┤    units     │◄────────┤   unit_photos    │
└──────────────┘         └──────┬───────┘         └──────────────────┘
                                │
        ┌───────────────────────┼───────────────────────┐
        ▼                       ▼                       ▼
┌──────────────┐        ┌───────────────┐       ┌──────────────┐
│    rates     │        │unit_amenities │       │  pool_rates  │
└──────────────┘        └───────┬───────┘       └──────────────┘
                                ▼
                        ┌───────────────┐
                        │   amenities   │
                        └───────────────┘
```

- **`units` & `buildings`**: Studio metadata, floor level, dimensions, bed configurations, and condo residence details.
- **`rates`**: Structured pricing matrix indexed by `(unit_id, day_type, slot_type, hours)`.
- **`addons` & `deposit_rules`**: Flat fee extras (extra hour/pax, parking) and duration-based refundable security deposits.
- **`availability_blocks`**: Unavailability records mapped to dates and slots (`overnight`, `daycation`, or `all`).
- **`announcements`**: Broadcast messages with optional date ranges that appear on public availability views.
- **`settings`**: Key-value repository storing business phone, Messenger links, address, and copy.
- **`audit_logs`**: System audit trail capturing user IDs, affected tables, and state change diffs.

---

## Production Deployment (Supabase & Web Server)

### 1. Supabase PostgreSQL Configuration

In production, supply a connection pooler URI via the `DATABASE_URL` environment variable:

```ini
DATABASE_URL=postgresql://postgres.<project-ref>:<password>@<region>.pooler.supabase.com:5432/postgres?sslmode=require
```

Apply the initial schema remotely:

```powershell
php scripts/db_init.php
php scripts/create_admin.php owner@yourdomain.com "Owner Name"
```

### 2. Web Server Hardening (Apache / Nginx / Caddy)

- Set `APP_DEBUG=0` in your production environment.
- Enforce **HTTPS** with valid SSL certificates.
- Set the web document root directly to the project root directory.
- **Restrict sensitive paths**: Block public HTTP access to `.env`, `db/`, `scripts/`, `tests/`, and `.git/`. Only `assets/` and public PHP entry points should be accessible.

Sample Nginx rule:

```nginx
location ~ ^/(\.env|db|scripts|tests|\.git) {
    deny all;
    return 404;
}
```

---

## Media Management & Video Embeds

- **Photos**: Can be hosted on external HTTPS image providers or cloud storage. If using Google Drive image share links, the system automatically translates them to optimized thumbnail previews via `drive_image_url()`.
- **Video Walkthroughs**:
  1. Upload the video to Google Drive.
  2. Set permission to **"Anyone with the link can view"**.
  3. Paste the share link into the room media manager in CMS.
  4. The platform converts the link into an embedded preview with customizable caption and poster image fallback.

---

## Pre-Launch Checklist

Before driving public guest traffic:

- [ ] Set `APP_DEBUG=0` and ensure production `DATABASE_URL` uses SSL (`sslmode=require`).
- [ ] In **Admin > Settings**, update `messenger_url` to the active Facebook page link (`https://m.me/yourpage`).
- [ ] Confirm room pricing, weekend definitions, pool fees, and deposit tiers in the CMS.
- [ ] Verify unit photos and replace any sample links with real studio photography.
- [ ] Ensure self check-in instructions and lockbox guidelines are updated under Settings.

---

## Attribution & Credits

- **Iconography**: [Tabler Icons](https://tabler.io/icons) (MIT License).
- **Photographic Assets**: Curated open-access imagery of Fairview and Quezon City licensed under Creative Commons (CC0 and CC BY-SA 4.0). Complete provenance details, author links, and licensing specifics are preserved in [`assets/credits.json`](file:///c:/Users/Jerald/Desktop/HomeEase/assets/credits.json).

---

## License

All rights reserved &copy; Flynn's Cozy Staycation. Proprietary software developed for private commercial operation.
