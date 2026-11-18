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
   'Paid in advance with the booking fee. A slot is guaranteed.', 30),
  ('parking_reserved_car',   'Reserved parking — car',   350.00, 'per stay', 'parking',
   'Paid in advance with the booking fee. A slot is guaranteed.', 40),
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
  ('Can we pay in cash?',
   'We are self check-in, online payment only.', 10),
  ('How can we get the key or passcode?',
   'If you have paid in advance, we will send the passcode to you 5 minutes before check-in time.', 20),
  ('Is it okay if we arrive late?',
   'Yes, just let us know your estimated arrival time.', 30),
  ('Is it okay if we don''t arrive at the same time?',
   'No problem at all! Just follow the same check-in process.', 40),
  ('Can I pay onsite for pool tickets?',
   'Yes, but extra charges will apply.', 50),
  ('Can we check out late?',
   'Late check-out is not allowed unless you inform us in advance. Extensions are subject to availability. Please note that a ₱500 fee applies if check-out is delayed by more than 3 minutes.', 60),
  ('When and how is the security deposit refunded?',
   'Please wait for us to check the unit before the security deposit is released. We manage 5 units and inspect them in order; deposit release usually takes less than 2–3 hours. Refunds are sent via GCash, Maya, or BPI, provided there are no damages, missing items, or rule violations.', 70),
  ('Can I cook in the unit?',
   'Yes, light cooking is allowed. Please avoid strong-smelling foods (e.g., shrimp, tuyo, other seafood, curry) and oils (₱500 strong odor penalty).', 80),
  ('Do you provide towels and toiletries?',
   'Yes, basic towels and toiletries are provided.', 90),
  ('Is the Wi-Fi free?',
   'Yes, complimentary Wi-Fi is available in all units.', 100),
  ('Can I have my food delivered via Grab or Foodpanda?',
   'Yes! Please meet the delivery rider at the ground floor lobby.', 110),
  ('Is there a curfew?',
   'For noise: Loud noises, videoke, and karaoke should stop by 8:00 PM – 9:00 PM as courtesy to the neighbors. For going in and out of the building: No curfew.', 120),
  ('Are pets and visitors allowed?',
   'No pets allowed. Only pre-registered guests are permitted inside the unit; no unregistered visitors are allowed (₱1,000 penalty per unregistered guest).', 130),
  ('Is smoking or vaping allowed?',
   'Strictly no smoking or vaping inside the unit. This includes the use of tobacco, cannabis, vape, e-cigarettes, etc. Penalty: ₱3,500.', 140),
  ('What are the cleanliness (CLAYGO) rules?',
   'Maintain cleanliness at all times (clean as you go). Do not leave unwashed and oily dishes in the sink (Penalty: ₱500 for an untidy unit). Please dispose of trash properly in the building garbage room (Penalty: ₱250).', 150),
  ('Can we have an in-room massage session?',
   'Massage sessions are generally not allowed inside the unit as oils or liniments (such as Efficascent Oil) may stain bedsheets and mattresses, and leave a strong odor in the room (Penalty: ₱1,500).', 160),
  ('How do I report a problem during my stay?',
   'You can contact us via Facebook Messenger, and we will assist you promptly.', 170)
ON CONFLICT (question) DO UPDATE SET answer = EXCLUDED.answer, sort_order = EXCLUDED.sort_order;


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
  ('checkin_window', 'Overnight: 6:00 PM – 9:00 PM · Daycation: [CONFIRM_09]'),
  ('checkin_overnight', '6:00 PM – 9:00 PM'),
  ('checkin_daycation', '[CONFIRM_09: 7:00 AM – 9:00 AM]'),
  ('checkout_overnight', '7:00 AM'),
  ('checkout_daycation', '7:00 PM'),
  ('booking_steps',  'Message us on Messenger|Say the date, room and slot;Send the booking fee|[CONFIRM_01] to hold the slot;Settle balance on arrival|Passcode sent after check-in payment'),
  ('booking_result', 'You hold the slot|[CONFIRM_01] booking fee'),
  ('pricing_note',   'Weekday overnight, two pax included. Weekends and holidays cost a little more; extra hours, pax, parking and pool are quoted in the calculator. The security deposit ([CONFIRM_02]) is refunded after check-out.'),
  ('cta_title',      E'Make it a cozy\nstaycation.'),
  ('cta_note',       'Message us with your dates; we reply with availability and the exact total.'),
  ('promo_note',     'Overnight promo: ₱1,350 for 2 pax (12 hours). Ask us on Messenger.')
ON CONFLICT (key) DO UPDATE SET value = EXCLUDED.value;

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
