-- ============================================================
-- Flynn's Cozy Staycation — seed data
-- ============================================================
-- Everything here is editable from the admin CMS. This file
-- just gives the owner a working starting point taken from the
-- published rate page.
-- ============================================================

INSERT INTO buildings (name, address, landmarks, map_url) VALUES
  ('Trees Residences', 'Fairview, Quezon City',
   'SM Fairview, Ayala Fairview Terraces, Robinson Novaliches', NULL),
  ('Milan Residenze', 'Fairview, Quezon City',
   'SM Fairview, Ayala Fairview Terraces, Robinson Novaliches', NULL)
ON CONFLICT (name) DO NOTHING;


-- ============================================================
-- UNITS
-- ============================================================
INSERT INTO units
  (slug, name, tagline, building_id, description, size_sqm, floor_no,
   bed_type, capacity_max, view_note, sort_order)
VALUES
  ('unit-2', 'Unit 2', 'Simple Modern',
   (SELECT id FROM buildings WHERE name = 'Trees Residences'),
   'A sleek, high-energy studio that pairs minimalist functionality with a vibrant, neon-lit atmosphere.',
   20.00, 6, 'Queen', 4, 'Simple modern studio', 2),

  ('unit-3', 'Unit 3', 'With Balcony',
   (SELECT id FROM buildings WHERE name = 'Milan Residenze'),
   'A comfortable studio elevated by a private balcony and an open city outlook.',
   22.00, 6, 'Double', 4, 'Balcony', 3),

  ('unit-4', 'Unit 4', 'City View w/ Balcony',
   (SELECT id FROM buildings WHERE name = 'Milan Residenze'),
   'A 14th-floor studio built around the view, with a private balcony to take it in.',
   22.00, 14, 'Queen', 4, 'City view w/ balcony', 4),

  ('unit-5', 'Unit 5', 'Pool View w/ Balcony',
   (SELECT id FROM buildings WHERE name = 'Milan Residenze'),
   'A 9th-floor studio overlooking the pool, with a private balcony and blackout curtains.',
   23.95, 9, 'Double', 4, 'Pool view w/ balcony', 5)
ON CONFLICT (slug) DO NOTHING;


-- ============================================================
-- INCLUSIONS
-- ============================================================
INSERT INTO amenities (name, category, sort_order) VALUES
  ('Air conditioning',        'Living',   10),
  ('Living area',             'Living',   20),
  ('Sofa',                    'Living',   30),
  ('42" TV',                  'Media',    40),
  ('Netflix',                 'Media',    50),
  ('Prime Video',             'Media',    60),
  ('YouTube',                 'Media',    70),
  ('LokTV',                   'Media',    80),
  ('Private comfort room',    'Bathroom', 90),
  ('Towels',                  'Bathroom', 100),
  ('Kitchen',                 'Kitchen',  110),
  ('Induction stove w/ pans', 'Kitchen',  120),
  ('Fire extinguisher',       'Safety',   130),
  ('Balcony',                 'Outdoor',  140),
  ('Blackout curtains',       'Bedroom',  150)
ON CONFLICT (name) DO NOTHING;

-- Included in every unit.
INSERT INTO unit_amenities (unit_id, amenity_id)
SELECT u.id, a.id
FROM units u
CROSS JOIN amenities a
WHERE a.name NOT IN ('Balcony', 'Blackout curtains')
ON CONFLICT DO NOTHING;

-- Balcony: Units 3, 4, 5.
INSERT INTO unit_amenities (unit_id, amenity_id)
SELECT u.id, a.id
FROM units u
JOIN amenities a ON a.name = 'Balcony'
WHERE u.slug IN ('unit-3', 'unit-4', 'unit-5')
ON CONFLICT DO NOTHING;

-- Blackout curtains: Unit 5.
INSERT INTO unit_amenities (unit_id, amenity_id)
SELECT u.id, a.id
FROM units u
JOIN amenities a ON a.name = 'Blackout curtains'
WHERE u.slug = 'unit-5'
ON CONFLICT DO NOTHING;


-- ============================================================
-- RATES  (12-hour published card)
-- ============================================================
INSERT INTO rates (unit_id, day_type, slot_type, hours, amount)
SELECT u.id, d.day_type, s.slot_type, 12, d.amount
FROM (VALUES
  ('unit-2', 1550.00, 1650.00),
  ('unit-3', 1650.00, 1700.00),
  ('unit-4', 1650.00, 1700.00),
  ('unit-5', 1650.00, 1700.00)
) AS r(slug, weekday, weekend)
JOIN units u ON u.slug = r.slug
CROSS JOIN LATERAL (VALUES ('weekday', r.weekday), ('weekend_holiday', r.weekend)) AS d(day_type, amount)
CROSS JOIN (VALUES ('overnight'), ('daycation')) AS s(slot_type)
ON CONFLICT (unit_id, day_type, slot_type, hours) DO NOTHING;

-- The ₱1,350 "overnight promo for 2" overlaps the normal 12-hour
-- overnight rate, so it is not seeded as a price row. It lives as
-- a notice until the owner confirms whether it replaces or stacks.
-- ============================================================
-- ADD-ONS
-- ============================================================
INSERT INTO addons (code, label, amount, unit_label, category, description, sort_order) VALUES
  ('extra_hour', 'Additional hour',        180.00, 'per hour', 'extra',
   'Charged for every hour beyond the booked 12-hour slot.', 10),
  ('extra_pax',  'Additional pax',         300.00, 'per pax',  'extra',
   'Adults and teens. Kids 11 and below stay free if no extra bedding is needed.', 20),
  ('parking_reserved_motor', 'Reserved parking — motor', 250.00, 'per stay', 'parking',
   'Full payment with the downpayment. A slot is guaranteed.', 30),
  ('parking_reserved_car',   'Reserved parking — car',   350.00, 'per stay', 'parking',
   'Full payment with the downpayment. A slot is guaranteed.', 40),
  ('parking_pay_motor',      'Pay parking — motor',       40.00, 'per stay', 'parking',
   'Outside the complex, first-come first-served.', 50),
  ('parking_pay_car',        'Pay parking — car',         60.00, 'per stay', 'parking',
   'Outside the complex, first-come first-served.', 60)
ON CONFLICT (code) DO NOTHING;


-- ============================================================
-- POOL  (Units 1-2: ₱250 / ₱350 · Units 3-5: ₱170 / ₱350)
-- ============================================================
INSERT INTO pool_rates (unit_id, day_type, amount)
SELECT u.id, d.day_type, d.amount
FROM (VALUES
  ('unit-2', 250.00, 350.00),
  ('unit-3', 170.00, 350.00),
  ('unit-4', 170.00, 350.00),
  ('unit-5', 170.00, 350.00)
) AS p(slug, weekday, holiday)
JOIN units u ON u.slug = p.slug
CROSS JOIN LATERAL (VALUES ('weekday', p.weekday), ('weekend_holiday', p.holiday)) AS d(day_type, amount)
ON CONFLICT (unit_id, day_type) DO NOTHING;


-- ============================================================
-- DEPOSIT TIERS
-- ============================================================
INSERT INTO deposit_rules (min_hours, amount, label) VALUES
  (1,  500.00,  'Stay under 50 hours'),
  (51, 1000.00, 'Stay of 51 hours or more')
ON CONFLICT (min_hours) DO NOTHING;


-- ============================================================
-- HOLIDAYS  (drives weekend/holiday pricing)
-- ============================================================
INSERT INTO holidays (holiday_date, name) VALUES
  ('2026-11-30', 'Bonifacio Day'),
  ('2026-12-08', 'Feast of the Immaculate Conception'),
  ('2026-12-24', 'Christmas Eve'),
  ('2026-12-25', 'Christmas Day'),
  ('2026-12-30', 'Rizal Day'),
  ('2026-12-31', 'New Year''s Eve'),
  ('2027-01-01', 'New Year''s Day')
ON CONFLICT (holiday_date) DO NOTHING;


-- ============================================================
-- FAQs
-- ============================================================
INSERT INTO faqs (question, answer, sort_order) VALUES
  ('How do I make a reservation?',
   'Message us on Facebook Messenger. A ₱500 downpayment secures your slot. It serves as the security deposit and is refundable within the day after check-out if there are no damages, missing items, or violations. It cannot be transferred to a reschedule or another guest. Cancellations or reschedules made within 7 days of check-in are non-refundable, and the full booking amount is charged.', 10),
  ('What time is check-in?',
   'Our check-in window is 6:00 PM to 9:00 PM. The unit is self check-in: you receive a passcode once the balance has been settled.', 20),
  ('What are the requirements?',
   'One valid ID per guest, with a visible full name, photo, and date of birth.', 30),
  ('How do I pay the balance?',
   'The downpayment is paid online. The remaining balance is settled on check-in through our bank accounts. Please send the transfer screenshot manually rather than using the page''s automatic screenshot option. No balance, no passcode.', 40),
  ('Is the security deposit refundable?',
   'Yes. It is returned within the day after check-out, provided there are no damages, missing items, or violations.', 50),
  ('Do children pay?',
   'Kids 11 years old and below stay free as long as no additional bedding is required. Additional adults and teens are ₱300 per pax.', 60),
  ('Is parking available?',
   'Building parking is currently unavailable. You can book reserved parking near the building (motor ₱250, car ₱350, paid in full with the downpayment) or use pay parking outside the complex (motor ₱40, car ₱60, first-come first-served).', 70),
  ('Is the pool included?',
   'The pool is a separate fee. It is closed every Monday. Appropriate swimwear such as rashguards, wetsuits, cycling shorts, or board shorts is mandatory.', 80),
  ('Can I extend my stay?',
   'Yes, subject to availability. Additional hours are ₱180 each.', 90)
ON CONFLICT (question) DO NOTHING;


-- ============================================================
-- SETTINGS
-- ============================================================
INSERT INTO settings (key, value) VALUES
  ('brand_name',     'Flynn''s Cozy Staycation'),
  ('tagline',        'Fully furnished studio stays in Fairview, Quezon City'),
  ('hero_title',     E'Flynn''s Cozy\nStaycation.'),
  ('hero_support',   'Furnished studio stays in Fairview, Quezon City. Choose a 12-hour overnight or daycation stay for two.'),
  ('rooms_intro',    'Self check-in studios across two residences in Fairview. Each is fully air-conditioned and set up for a short stay rather than a night.'),
  ('calendar_intro', 'Availability changes quickly. Message us to confirm your dates.'),
  ('included_pax',   '2'),
  ('messenger_url',  'https://m.me/flynnscozystaycation'),
  ('contact_note',   'All reservations and payments are completed through Facebook Messenger.'),
  ('address',        'Fairview, Quezon City, Philippines, 1118'),
  ('landmarks',      'SM Fairview, Ayala Fairview Terraces, Robinson Novaliches'),
  ('checkin_window', '6:00 PM – 9:00 PM'),
  ('booking_steps',  'Message us on Messenger|Say the date, room and hours;Send the downpayment|₱500, refundable after check-out;Settle the balance on check-in|Passcode sent after payment'),
  ('booking_result', 'You hold the slot|₱500 only'),
  ('pricing_note',   'Weekday overnight, two pax included. Weekends and holidays cost a little more; extra hours, pax, parking and pool are quoted in the calculator. The refundable deposit comes back after check-out.'),
  ('cta_title',      E'Make it a cozy\nstaycation.'),
  ('cta_note',       'Message us with your dates; we reply with availability and the exact total.'),
  ('promo_note',     'Overnight promo: ₱1,350 for 2 pax (12 hours). Ask us on Messenger.')
ON CONFLICT (key) DO NOTHING;

UPDATE settings SET value = 'Flynn''s Cozy Staycation'
WHERE key = 'brand_name' AND value = 'Flynn''s Rate';
UPDATE settings SET value = 'https://m.me/flynnscozystaycation'
WHERE key = 'messenger_url' AND value = 'https://m.me/flynnsrate';
UPDATE settings SET value = E'Flynn''s Cozy\nStaycation.'
WHERE key = 'hero_title' AND value = E'Studios by\nthe half day.';
UPDATE settings SET value = 'Furnished studio stays in Fairview, Quezon City. Choose a 12-hour overnight or daycation stay for two.'
WHERE key = 'hero_support' AND value = 'Furnished studios in Fairview, Quezon City. Twelve hours from 7 to 7, overnight or daycation, two pax included.';
UPDATE settings SET value = E'Make it a cozy\nstaycation.'
WHERE key = 'cta_title' AND value = E'Take the night,\nor take the day.';
