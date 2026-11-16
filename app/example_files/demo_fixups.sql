-- Demo data fix-ups (also appended to marketplace_backup.sql).
-- The original dump only priced one product: give every product a price offer,
-- every physical product a shipping option, and enable the simulated coin.
-- Safe to run more than once.
INSERT INTO offers (id, product_id, min_quantity, price, created_at, updated_at, deleted)
SELECT UUID(), p.id, 1, IF(CAST(p.coins AS UNSIGNED) > 0, CAST(p.coins AS UNSIGNED), 500), NOW(), NOW(), 0
FROM products p
WHERE p.id NOT IN (SELECT product_id FROM offers);

INSERT INTO shippings (id, product_id, name, price, duration, from_quantity, to_quantity, created_at, updated_at, deleted)
SELECT UUID(), p.id, 'Standard Shipping', 10.00, '5-7 business days', 1, 100, NOW(), NOW(), 0
FROM products p
WHERE p.id IN (SELECT id FROM physical_products)
  AND p.id NOT IN (SELECT product_id FROM shippings);

UPDATE products SET coins = 'stb';

-- Pictures for the two "Targeted Extraction Services" listings (they had none and showed the grey placeholder).
INSERT INTO images (id, product_id, image, `first`, created_at, updated_at)
SELECT UUID(), p.id, 'nyc.jpg', 1, NOW(), NOW() FROM products p
WHERE p.id = 'a1d57b9a-ecff-11ef-9872-00163e5e6c20' AND p.id NOT IN (SELECT product_id FROM images);
INSERT INTO images (id, product_id, image, `first`, created_at, updated_at)
SELECT UUID(), p.id, 'hollywood.jpg', 1, NOW(), NOW() FROM products p
WHERE p.id = 'b2e67c9a-ecff-11ef-9872-00163e5e6c21' AND p.id NOT IN (SELECT product_id FROM images);
