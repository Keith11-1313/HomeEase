<<<<<<< HEAD
-- ============================================================
-- HomeEase — Apartment Management System
-- Complete Database Schema
-- ============================================================
-- This file creates the entire database and all the tables
-- needed by HomeEase.  Run it once inside phpMyAdmin or the
-- MySQL CLI to set everything up.
-- ============================================================


-- --------------------------------------------------------
-- 1.  CREATE THE DATABASE
-- --------------------------------------------------------
-- "CREATE DATABASE" tells MySQL to make a brand-new empty
-- database called `homeease`.
-- "IF NOT EXISTS" prevents an error if the database was
-- already created before.
-- "CHARACTER SET utf8mb4" lets us store any language's
-- characters, including emojis.
-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `homeease`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

-- "USE" tells MySQL: "from now on, every table I create
-- should go inside the `homeease` database."
USE `homeease`;


-- ============================================================
-- 2.  USERS TABLE
-- ============================================================
-- Stores every person who can log in: admins (owners),
-- tenants, and guests.
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
  -- "id" is the unique number that identifies each user.
  -- AUTO_INCREMENT means MySQL picks the next number for us.
  `id`            INT            AUTO_INCREMENT PRIMARY KEY,

  -- "first_name" and "last_name" hold the person's real name.
  `first_name`    VARCHAR(100)   NOT NULL,
  `last_name`     VARCHAR(100)   NOT NULL,

  -- "email" is used to log in.  UNIQUE makes sure no two
  -- users can register the same email address.
  `email`         VARCHAR(255)   NOT NULL UNIQUE,

  -- "phone" is optional contact info.
  `phone`         VARCHAR(20)    DEFAULT NULL,

  -- "password" stores the HASHED version of the password
  -- (never plain text). 255 chars is enough for bcrypt.
  `password`      VARCHAR(255)   NOT NULL,

  -- "role" limits what the person can do.
  --   admin  = the building owner / manager
  --   tenant = a renter living in a unit
  --   guest  = a visitor browsing vacant units
  `role`          ENUM('admin', 'tenant', 'guest')
                  NOT NULL DEFAULT 'guest',

  -- "avatar" stores the filename of the profile picture
  -- (optional, can be NULL if user hasn't uploaded one).
  `avatar`        VARCHAR(255)   DEFAULT NULL,

  -- "is_active" lets an admin disable an account without
  -- deleting it.  1 = active, 0 = disabled.
  `is_active`     TINYINT(1)     NOT NULL DEFAULT 1,

  -- "created_at" records the exact moment the account was
  -- made.  CURRENT_TIMESTAMP fills this in automatically.
  `created_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,

  -- "updated_at" changes every time we edit the user's row.
  `updated_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                 ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- ENGINE=InnoDB gives us foreign-key support and safe
-- transactions.


-- ============================================================
-- 3.  UNITS TABLE
-- ============================================================
-- Each row is one apartment unit in the building.
-- ============================================================
CREATE TABLE IF NOT EXISTS `units` (
  `id`            INT            AUTO_INCREMENT PRIMARY KEY,

  -- "unit_number" is the label on the door, e.g. "101" or "3A".
  `unit_number`   VARCHAR(20)    NOT NULL UNIQUE,

  -- "type" describes the apartment layout.
  `type`          ENUM('Studio', 'One Bedroom', 'Two Bedroom', 'Loft')
                  NOT NULL DEFAULT 'Studio',

  -- "description" is a short blurb shown to guests when they
  -- browse available units.
  `description`   TEXT           DEFAULT NULL,

  -- "rent_price" is the monthly rent in your local currency.
  -- DECIMAL(10,2) allows up to 99,999,999.99.
  `rent_price`    DECIMAL(10,2)  NOT NULL DEFAULT 0.00,

  -- "status" tells us if anyone is living there right now.
  `status`        ENUM('Vacant', 'Occupied')
                  NOT NULL DEFAULT 'Vacant',

  -- "tenant_id" links to the user who currently rents this
  -- unit.  NULL means the unit is empty.
  `tenant_id`     INT            DEFAULT NULL,

  -- "floor_number" helps with physical location.
  `floor_number`  INT            DEFAULT NULL,

  `created_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                 ON UPDATE CURRENT_TIMESTAMP,

  -- FOREIGN KEY: if we refer to a user as the tenant, that
  -- user MUST exist in the `users` table.
  -- ON DELETE SET NULL: if the tenant account is deleted,
  -- the unit becomes vacant instead of breaking.
  FOREIGN KEY (`tenant_id`) REFERENCES `users`(`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 4.  PAYMENTS TABLE
-- ============================================================
-- Every rent payment, whether pending, verified, or rejected.
-- ============================================================
CREATE TABLE IF NOT EXISTS `payments` (
  `id`            INT            AUTO_INCREMENT PRIMARY KEY,

  -- "tenant_id" identifies who made the payment.
  `tenant_id`     INT            NOT NULL,

  -- "unit_id" identifies which unit the payment is for.
  `unit_id`       INT            NOT NULL,

  -- "amount" is how much money was paid.
  `amount`        DECIMAL(10,2)  NOT NULL,

  -- "payment_date" is when the tenant says they paid.
  `payment_date`  DATE           NOT NULL,

  -- "method" tells us HOW the tenant paid.
  `method`        ENUM('Cash', 'Bank Transfer', 'GCash', 'Other')
                  NOT NULL DEFAULT 'Cash',

  -- "reference_number" is a receipt or transaction ID the
  -- tenant can provide as proof.
  `reference_number` VARCHAR(100) DEFAULT NULL,

  -- "status" tracks whether the admin has confirmed the
  -- payment yet.
  --   Pending  = waiting for admin verification
  --   Verified = admin confirmed the payment
  --   Rejected = admin rejected (e.g., wrong amount)
  `status`        ENUM('Pending', 'Verified', 'Rejected')
                  NOT NULL DEFAULT 'Pending',

  -- "verified_by" links to the admin user who approved or
  -- rejected the payment.  NULL while still pending.
  `verified_by`   INT            DEFAULT NULL,

  -- "verified_at" records the timestamp when the admin acted.
  `verified_at`   DATETIME       DEFAULT NULL,

  -- "notes" lets the admin add a short comment, like
  -- "partial payment — balance due next month".
  `notes`         TEXT           DEFAULT NULL,

  -- "period_covered" labels which month the payment covers,
  -- e.g. "February 2026".
  `period_covered` VARCHAR(50)   DEFAULT NULL,

  `created_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                 ON UPDATE CURRENT_TIMESTAMP,

  -- Foreign keys link payments to existing users and units.
  FOREIGN KEY (`tenant_id`)   REFERENCES `users`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`unit_id`)     REFERENCES `units`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`verified_by`) REFERENCES `users`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 5.  TICKETS TABLE  (Maintenance Requests)
-- ============================================================
-- Tenants create tickets when something in their unit needs
-- fixing.  Admins update the status as work progresses.
-- ============================================================
CREATE TABLE IF NOT EXISTS `tickets` (
  `id`            INT            AUTO_INCREMENT PRIMARY KEY,

  -- "tenant_id" is the user who reported the problem.
  `tenant_id`     INT            NOT NULL,

  -- "unit_id" is the apartment where the issue is.
  `unit_id`       INT            NOT NULL,

  -- "subject" is a short title like "Leaky faucet in kitchen".
  `subject`       VARCHAR(255)   NOT NULL,

  -- "description" provides the full details of the problem.
  `description`   TEXT           NOT NULL,

  -- "priority" helps the admin decide what to fix first.
  `priority`      ENUM('Low', 'Medium', 'High', 'Urgent')
                  NOT NULL DEFAULT 'Medium',

  -- "status" tracks the maintenance workflow.
  --   Pending     = just submitted, nobody started yet
  --   In Progress = a worker is on the job
  --   Resolved    = problem fixed
  `status`        ENUM('Pending', 'In Progress', 'Resolved')
                  NOT NULL DEFAULT 'Pending',

  -- "assigned_to" can optionally point to the admin / staff
  -- member handling this ticket.
  `assigned_to`   INT            DEFAULT NULL,

  -- "resolved_at" records when the issue was finally fixed.
  `resolved_at`   DATETIME       DEFAULT NULL,

  `created_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                 ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY (`tenant_id`)  REFERENCES `users`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`unit_id`)    REFERENCES `units`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 6.  TICKET_UPDATES TABLE
-- ============================================================
-- Every time someone changes a ticket's status, a row is
-- added here so we have a full history.
-- ============================================================
CREATE TABLE IF NOT EXISTS `ticket_updates` (
  `id`            INT            AUTO_INCREMENT PRIMARY KEY,

  -- "ticket_id" links back to the ticket being updated.
  `ticket_id`     INT            NOT NULL,

  -- "updated_by" is the user (usually admin) who changed
  -- the status or left a comment.
  `updated_by`    INT            NOT NULL,

  -- "old_status" and "new_status" show what changed.
  `old_status`    ENUM('Pending', 'In Progress', 'Resolved')
                  NOT NULL,
  `new_status`    ENUM('Pending', 'In Progress', 'Resolved')
                  NOT NULL,

  -- "comment" lets the updater explain what was done.
  `comment`       TEXT           DEFAULT NULL,

  `created_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (`ticket_id`)  REFERENCES `tickets`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`updated_by`) REFERENCES `users`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 7.  AUDIT_LOGS TABLE
-- ============================================================
-- Automatically records every UPDATE or DELETE action so the
-- admin can review changes later (and export as PDF).
-- ============================================================
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id`            INT            AUTO_INCREMENT PRIMARY KEY,

  -- "user_id" is who performed the action.
  `user_id`       INT            DEFAULT NULL,

  -- "action" is the type of operation: Update or Delete.
  `action`        ENUM('Update', 'Delete')
                  NOT NULL,

  -- "table_name" tells us WHICH table was affected
  -- (e.g., "users", "units", "payments").
  `table_name`    VARCHAR(100)   NOT NULL,

  -- "record_id" is the primary-key value of the row that
  -- was changed or removed.
  `record_id`     INT            NOT NULL,

  -- "old_values" stores a JSON snapshot of the data BEFORE
  -- the change, so we can see what it used to look like.
  `old_values`    JSON           DEFAULT NULL,

  -- "new_values" stores a JSON snapshot AFTER the change.
  -- For deletes, this can be NULL because the row is gone.
  `new_values`    JSON           DEFAULT NULL,

  -- "ip_address" records where the action came from (useful
  -- for security reviews).
  `ip_address`    VARCHAR(45)    DEFAULT NULL,

  `created_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 8.  NOTIFICATIONS TABLE
-- ============================================================
-- In-app alerts shown to individual users (e.g., "Your rent
-- for February is overdue").
-- ============================================================
CREATE TABLE IF NOT EXISTS `notifications` (
  `id`            INT            AUTO_INCREMENT PRIMARY KEY,

  -- "user_id" is the person who should see this notification.
  `user_id`       INT            NOT NULL,

  -- "title" is the bold headline (e.g., "Payment Overdue").
  `title`         VARCHAR(255)   NOT NULL,

  -- "message" is the full notification body.
  `message`       TEXT           NOT NULL,

  -- "type" categorises the notification so the frontend can
  -- show different icons / colours.
  `type`          ENUM('payment', 'ticket', 'system', 'reminder')
                  NOT NULL DEFAULT 'system',

  -- "is_read" tracks whether the user has opened it.
  -- 0 = unread, 1 = read.
  `is_read`       TINYINT(1)     NOT NULL DEFAULT 0,

  -- "link" is an optional URL inside the app where the user
  -- can take action (e.g., "/payments.php?id=5").
  `link`          VARCHAR(255)   DEFAULT NULL,

  `created_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 9.  FAQS TABLE
-- ============================================================
-- Frequently Asked Questions visible to guests (and everyone
-- else) on the public page.
-- ============================================================
CREATE TABLE IF NOT EXISTS `faqs` (
  `id`            INT            AUTO_INCREMENT PRIMARY KEY,

  -- "question" is what the visitor is asking.
  `question`      VARCHAR(500)   NOT NULL,

  -- "answer" is the response shown when they expand the FAQ.
  `answer`        TEXT           NOT NULL,

  -- "display_order" controls the order FAQs appear on the
  -- page.  Lower numbers are shown first.
  `display_order` INT            NOT NULL DEFAULT 0,

  -- "is_active" lets the admin hide a FAQ without deleting
  -- it.  1 = visible, 0 = hidden.
  `is_active`     TINYINT(1)     NOT NULL DEFAULT 1,

  `created_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                 ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- DONE!  The schema is ready.
-- Now run seed_data.php in a browser to fill these tables
-- with sample data.
-- ============================================================
=======
-- ============================================================
-- HomeEase — Apartment Management System
-- Complete Database Schema
-- ============================================================
-- This file creates the entire database and all the tables
-- needed by HomeEase.  Run it once inside phpMyAdmin or the
-- MySQL CLI to set everything up.
-- ============================================================


-- --------------------------------------------------------
-- 1.  CREATE THE DATABASE
-- --------------------------------------------------------
-- "CREATE DATABASE" tells MySQL to make a brand-new empty
-- database called `homeease`.
-- "IF NOT EXISTS" prevents an error if the database was
-- already created before.
-- "CHARACTER SET utf8mb4" lets us store any language's
-- characters, including emojis.
-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `homeease`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

-- "USE" tells MySQL: "from now on, every table I create
-- should go inside the `homeease` database."
USE `homeease`;


-- ============================================================
-- 2.  USERS TABLE
-- ============================================================
-- Stores every person who can log in: admins (owners),
-- tenants, and guests.
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
  -- "id" is the unique number that identifies each user.
  -- AUTO_INCREMENT means MySQL picks the next number for us.
  `id`            INT            AUTO_INCREMENT PRIMARY KEY,

  -- "first_name" and "last_name" hold the person's real name.
  `first_name`    VARCHAR(100)   NOT NULL,
  `last_name`     VARCHAR(100)   NOT NULL,

  -- "email" is used to log in.  UNIQUE makes sure no two
  -- users can register the same email address.
  `email`         VARCHAR(255)   NOT NULL UNIQUE,

  -- "phone" is optional contact info.
  `phone`         VARCHAR(20)    DEFAULT NULL,

  -- "password" stores the HASHED version of the password
  -- (never plain text). 255 chars is enough for bcrypt.
  `password`      VARCHAR(255)   NOT NULL,

  -- "role" limits what the person can do.
  --   admin  = the building owner / manager
  --   tenant = a renter living in a unit
  --   guest  = a visitor browsing vacant units
  `role`          ENUM('admin', 'tenant', 'guest')
                  NOT NULL DEFAULT 'guest',

  -- "avatar" stores the filename of the profile picture
  -- (optional, can be NULL if user hasn't uploaded one).
  `avatar`        VARCHAR(255)   DEFAULT NULL,

  -- "is_active" lets an admin disable an account without
  -- deleting it.  1 = active, 0 = disabled.
  `is_active`     TINYINT(1)     NOT NULL DEFAULT 1,

  -- "created_at" records the exact moment the account was
  -- made.  CURRENT_TIMESTAMP fills this in automatically.
  `created_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,

  -- "updated_at" changes every time we edit the user's row.
  `updated_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                 ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- ENGINE=InnoDB gives us foreign-key support and safe
-- transactions.


-- ============================================================
-- 3.  UNITS TABLE
-- ============================================================
-- Each row is one apartment unit in the building.
-- ============================================================
CREATE TABLE IF NOT EXISTS `units` (
  `id`            INT            AUTO_INCREMENT PRIMARY KEY,

  -- "unit_number" is the label on the door, e.g. "101" or "3A".
  `unit_number`   VARCHAR(20)    NOT NULL UNIQUE,

  -- "type" describes the apartment layout.
  `type`          ENUM('Studio', 'One Bedroom', 'Two Bedroom', 'Loft')
                  NOT NULL DEFAULT 'Studio',

  -- "description" is a short blurb shown to guests when they
  -- browse available units.
  `description`   TEXT           DEFAULT NULL,

  -- "rent_price" is the monthly rent in your local currency.
  -- DECIMAL(10,2) allows up to 99,999,999.99.
  `rent_price`    DECIMAL(10,2)  NOT NULL DEFAULT 0.00,

  -- "status" tells us if anyone is living there right now.
  `status`        ENUM('Vacant', 'Occupied')
                  NOT NULL DEFAULT 'Vacant',

  -- "tenant_id" links to the user who currently rents this
  -- unit.  NULL means the unit is empty.
  `tenant_id`     INT            DEFAULT NULL,

  -- "floor_number" helps with physical location.
  `floor_number`  INT            DEFAULT NULL,

  `created_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                 ON UPDATE CURRENT_TIMESTAMP,

  -- FOREIGN KEY: if we refer to a user as the tenant, that
  -- user MUST exist in the `users` table.
  -- ON DELETE SET NULL: if the tenant account is deleted,
  -- the unit becomes vacant instead of breaking.
  FOREIGN KEY (`tenant_id`) REFERENCES `users`(`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 4.  PAYMENTS TABLE
-- ============================================================
-- Every rent payment, whether pending, verified, or rejected.
-- ============================================================
CREATE TABLE IF NOT EXISTS `payments` (
  `id`            INT            AUTO_INCREMENT PRIMARY KEY,

  -- "tenant_id" identifies who made the payment.
  `tenant_id`     INT            NOT NULL,

  -- "unit_id" identifies which unit the payment is for.
  `unit_id`       INT            NOT NULL,

  -- "amount" is how much money was paid.
  `amount`        DECIMAL(10,2)  NOT NULL,

  -- "payment_date" is when the tenant says they paid.
  `payment_date`  DATE           NOT NULL,

  -- "method" tells us HOW the tenant paid.
  `method`        ENUM('Cash', 'Bank Transfer', 'GCash', 'Other')
                  NOT NULL DEFAULT 'Cash',

  -- "reference_number" is a receipt or transaction ID the
  -- tenant can provide as proof.
  `reference_number` VARCHAR(100) DEFAULT NULL,

  -- "status" tracks whether the admin has confirmed the
  -- payment yet.
  --   Pending  = waiting for admin verification
  --   Verified = admin confirmed the payment
  --   Rejected = admin rejected (e.g., wrong amount)
  `status`        ENUM('Pending', 'Verified', 'Rejected')
                  NOT NULL DEFAULT 'Pending',

  -- "verified_by" links to the admin user who approved or
  -- rejected the payment.  NULL while still pending.
  `verified_by`   INT            DEFAULT NULL,

  -- "verified_at" records the timestamp when the admin acted.
  `verified_at`   DATETIME       DEFAULT NULL,

  -- "notes" lets the admin add a short comment, like
  -- "partial payment — balance due next month".
  `notes`         TEXT           DEFAULT NULL,

  -- "period_covered" labels which month the payment covers,
  -- e.g. "February 2026".
  `period_covered` VARCHAR(50)   DEFAULT NULL,

  `created_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                 ON UPDATE CURRENT_TIMESTAMP,

  -- Foreign keys link payments to existing users and units.
  FOREIGN KEY (`tenant_id`)   REFERENCES `users`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`unit_id`)     REFERENCES `units`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`verified_by`) REFERENCES `users`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 5.  TICKETS TABLE  (Maintenance Requests)
-- ============================================================
-- Tenants create tickets when something in their unit needs
-- fixing.  Admins update the status as work progresses.
-- ============================================================
CREATE TABLE IF NOT EXISTS `tickets` (
  `id`            INT            AUTO_INCREMENT PRIMARY KEY,

  -- "tenant_id" is the user who reported the problem.
  `tenant_id`     INT            NOT NULL,

  -- "unit_id" is the apartment where the issue is.
  `unit_id`       INT            NOT NULL,

  -- "subject" is a short title like "Leaky faucet in kitchen".
  `subject`       VARCHAR(255)   NOT NULL,

  -- "description" provides the full details of the problem.
  `description`   TEXT           NOT NULL,

  -- "priority" helps the admin decide what to fix first.
  `priority`      ENUM('Low', 'Medium', 'High', 'Urgent')
                  NOT NULL DEFAULT 'Medium',

  -- "status" tracks the maintenance workflow.
  --   Pending     = just submitted, nobody started yet
  --   In Progress = a worker is on the job
  --   Resolved    = problem fixed
  `status`        ENUM('Pending', 'In Progress', 'Resolved')
                  NOT NULL DEFAULT 'Pending',

  -- "assigned_to" can optionally point to the admin / staff
  -- member handling this ticket.
  `assigned_to`   INT            DEFAULT NULL,

  -- "resolved_at" records when the issue was finally fixed.
  `resolved_at`   DATETIME       DEFAULT NULL,

  `created_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                 ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY (`tenant_id`)  REFERENCES `users`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`unit_id`)    REFERENCES `units`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 6.  TICKET_UPDATES TABLE
-- ============================================================
-- Every time someone changes a ticket's status, a row is
-- added here so we have a full history.
-- ============================================================
CREATE TABLE IF NOT EXISTS `ticket_updates` (
  `id`            INT            AUTO_INCREMENT PRIMARY KEY,

  -- "ticket_id" links back to the ticket being updated.
  `ticket_id`     INT            NOT NULL,

  -- "updated_by" is the user (usually admin) who changed
  -- the status or left a comment.
  `updated_by`    INT            NOT NULL,

  -- "old_status" and "new_status" show what changed.
  `old_status`    ENUM('Pending', 'In Progress', 'Resolved')
                  NOT NULL,
  `new_status`    ENUM('Pending', 'In Progress', 'Resolved')
                  NOT NULL,

  -- "comment" lets the updater explain what was done.
  `comment`       TEXT           DEFAULT NULL,

  `created_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (`ticket_id`)  REFERENCES `tickets`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`updated_by`) REFERENCES `users`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 7.  AUDIT_LOGS TABLE
-- ============================================================
-- Automatically records every UPDATE or DELETE action so the
-- admin can review changes later (and export as PDF).
-- ============================================================
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id`            INT            AUTO_INCREMENT PRIMARY KEY,

  -- "user_id" is who performed the action.
  `user_id`       INT            DEFAULT NULL,

  -- "action" is the type of operation: Update or Delete.
  `action`        ENUM('Update', 'Delete')
                  NOT NULL,

  -- "table_name" tells us WHICH table was affected
  -- (e.g., "users", "units", "payments").
  `table_name`    VARCHAR(100)   NOT NULL,

  -- "record_id" is the primary-key value of the row that
  -- was changed or removed.
  `record_id`     INT            NOT NULL,

  -- "old_values" stores a JSON snapshot of the data BEFORE
  -- the change, so we can see what it used to look like.
  `old_values`    JSON           DEFAULT NULL,

  -- "new_values" stores a JSON snapshot AFTER the change.
  -- For deletes, this can be NULL because the row is gone.
  `new_values`    JSON           DEFAULT NULL,

  -- "ip_address" records where the action came from (useful
  -- for security reviews).
  `ip_address`    VARCHAR(45)    DEFAULT NULL,

  `created_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 8.  NOTIFICATIONS TABLE
-- ============================================================
-- In-app alerts shown to individual users (e.g., "Your rent
-- for February is overdue").
-- ============================================================
CREATE TABLE IF NOT EXISTS `notifications` (
  `id`            INT            AUTO_INCREMENT PRIMARY KEY,

  -- "user_id" is the person who should see this notification.
  `user_id`       INT            NOT NULL,

  -- "title" is the bold headline (e.g., "Payment Overdue").
  `title`         VARCHAR(255)   NOT NULL,

  -- "message" is the full notification body.
  `message`       TEXT           NOT NULL,

  -- "type" categorises the notification so the frontend can
  -- show different icons / colours.
  `type`          ENUM('payment', 'ticket', 'system', 'reminder')
                  NOT NULL DEFAULT 'system',

  -- "is_read" tracks whether the user has opened it.
  -- 0 = unread, 1 = read.
  `is_read`       TINYINT(1)     NOT NULL DEFAULT 0,

  -- "link" is an optional URL inside the app where the user
  -- can take action (e.g., "/payments.php?id=5").
  `link`          VARCHAR(255)   DEFAULT NULL,

  `created_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 9.  FAQS TABLE
-- ============================================================
-- Frequently Asked Questions visible to guests (and everyone
-- else) on the public page.
-- ============================================================
CREATE TABLE IF NOT EXISTS `faqs` (
  `id`            INT            AUTO_INCREMENT PRIMARY KEY,

  -- "question" is what the visitor is asking.
  `question`      VARCHAR(500)   NOT NULL,

  -- "answer" is the response shown when they expand the FAQ.
  `answer`        TEXT           NOT NULL,

  -- "display_order" controls the order FAQs appear on the
  -- page.  Lower numbers are shown first.
  `display_order` INT            NOT NULL DEFAULT 0,

  -- "is_active" lets the admin hide a FAQ without deleting
  -- it.  1 = visible, 0 = hidden.
  `is_active`     TINYINT(1)     NOT NULL DEFAULT 1,

  `created_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                 ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- DONE!  The schema is ready.
-- Now run seed_data.php in a browser to fill these tables
-- with sample data.
-- ============================================================
>>>>>>> 047237f57cd0fd2e115602c620d70bf3bd6e29a0
