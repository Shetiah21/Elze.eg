-- Elze.eg E-Commerce Database Seed Data
-- Generated: 2026-07-02

USE elze_db;

-- 1. Insert Categories
INSERT INTO categories (id, name, slug) VALUES
(1, 'T-shirts', 't-shirts'),
(2, 'Ringer T-shirts', 'ringer-t-shirts'),
(3, 'Knitted Polos', 'knitted-polos'),
(4, 'Tops', 'tops')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- 2. Insert Admin User (Password is 'admin123')
INSERT INTO users (id, name, email, password, role, status, email_verified_at) VALUES
(1, 'Elze Admin', 'admin@elze.eg', '$2y$10$O0n7B1eRk2e.sMvIeE0kOeGZ71y8u1l.kOaJ.Gz90h.o/O1H3mOaC', 'admin', 'active', NOW())
ON DUPLICATE KEY UPDATE email=VALUES(email);

-- 3. Insert Products
INSERT INTO products (id, category_id, name, slug, description, base_price, size_chart_details) VALUES
-- T-shirts (Category 1)
(1, 1, 'Classic Crewneck T-Shirt', 'classic-crewneck-t-shirt', 'Premium local Egyptian cotton t-shirt with classic crewneck fit. Made from 100% fine cotton for extra breathability and soft feel in hot weather.', 350.00, '{"S":{"chest":50,"length":70,"shoulder":42},"M":{"chest":52,"length":72,"shoulder":44},"L":{"chest":54,"length":74,"shoulder":46},"XL":{"chest":56,"length":76,"shoulder":48}}'),
-- Ringer T-shirts (Category 2)
(2, 2, 'Retro Contrast Ringer T-Shirt', 'retro-contrast-ringer-t-shirt', 'Vintage-inspired ringer tee featuring contrast ribbed neck and sleeve bands. Perfect casual wear matching the modern Egyptian brand aesthetic.', 390.00, '{"S":{"chest":48,"length":68,"shoulder":41},"M":{"chest":50,"length":70,"shoulder":43},"L":{"chest":52,"length":72,"shoulder":45}}'),
-- Knitted Polos (Category 3)
(3, 3, 'Premium Knitted Ringer Polo', 'premium-knitted-ringer-polo', 'Elegant lightweight knit polo with contrast detailing on collar and cuffs. Designed for a tailored fit using premium local cotton blends.', 490.00, '{"S":{"chest":49,"length":69,"shoulder":42},"M":{"chest":51,"length":71,"shoulder":44},"L":{"chest":53,"length":73,"shoulder":46}}'),
-- Tops (Category 4)
(4, 4, 'Ribbed Knit Top', 'ribbed-knit-top', 'Sleek ribbed knit top crafted for ultimate everyday comfort. Tailored fit silhouette that matches a clean and professional daily wear.', 420.00, '{"S":{"chest":46,"length":64,"shoulder":38},"M":{"chest":48,"length":66,"shoulder":40},"L":{"chest":50,"length":68,"shoulder":42}}')
ON DUPLICATE KEY UPDATE name=VALUES(name), description=VALUES(description), base_price=VALUES(base_price), size_chart_details=VALUES(size_chart_details);

-- 4. Insert Product Variants
-- Variants for Product 1 (Classic Crewneck T-Shirt): S, M, L, XL in 10 Colors
-- Colors: White, Black, Blue, Navy, Burgundy, Olive, Grey, Brown, Pink, Yellow
-- Loop simulated by individual inserts
INSERT INTO product_variants (product_id, size, color, stock, price_modifier) VALUES
-- White
(1, 'S', 'White', 50, 0.00), (1, 'M', 'White', 50, 0.00), (1, 'L', 'White', 50, 0.00), (1, 'XL', 'White', 50, 0.00),
-- Black
(1, 'S', 'Black', 50, 0.00), (1, 'M', 'Black', 50, 0.00), (1, 'L', 'Black', 50, 0.00), (1, 'XL', 'Black', 50, 0.00),
-- Blue
(1, 'S', 'Blue', 50, 0.00), (1, 'M', 'Blue', 50, 0.00), (1, 'L', 'Blue', 50, 0.00), (1, 'XL', 'Blue', 50, 0.00),
-- Navy
(1, 'S', 'Navy', 50, 0.00), (1, 'M', 'Navy', 50, 0.00), (1, 'L', 'Navy', 50, 0.00), (1, 'XL', 'Navy', 50, 0.00),
-- Burgundy
(1, 'S', 'Burgundy', 50, 0.00), (1, 'M', 'Burgundy', 50, 0.00), (1, 'L', 'Burgundy', 50, 0.00), (1, 'XL', 'Burgundy', 50, 0.00),
-- Olive
(1, 'S', 'Olive', 50, 0.00), (1, 'M', 'Olive', 50, 0.00), (1, 'L', 'Olive', 50, 0.00), (1, 'XL', 'Olive', 50, 0.00),
-- Grey
(1, 'S', 'Grey', 50, 0.00), (1, 'M', 'Grey', 50, 0.00), (1, 'L', 'Grey', 50, 0.00), (1, 'XL', 'Grey', 50, 0.00),
-- Brown
(1, 'S', 'Brown', 50, 0.00), (1, 'M', 'Brown', 50, 0.00), (1, 'L', 'Brown', 50, 0.00), (1, 'XL', 'Brown', 50, 0.00),
-- Pink
(1, 'S', 'Pink', 50, 0.00), (1, 'M', 'Pink', 50, 0.00), (1, 'L', 'Pink', 50, 0.00), (1, 'XL', 'Pink', 50, 0.00),
-- Yellow
(1, 'S', 'Yellow', 50, 0.00), (1, 'M', 'Yellow', 50, 0.00), (1, 'L', 'Yellow', 50, 0.00), (1, 'XL', 'Yellow', 50, 0.00);

-- Variants for Product 2 (Retro Contrast Ringer T-Shirt): S, M, L in 4 Colors
-- Colors: White rib Black, Black rib White, Navy rib White, Burgundy rib White
INSERT INTO product_variants (product_id, size, color, stock, price_modifier) VALUES
-- White ra2aba black
(2, 'S', 'White ra2aba black', 30, 0.00), (2, 'M', 'White ra2aba black', 30, 0.00), (2, 'L', 'White ra2aba black', 30, 0.00),
-- Black ra2aba white
(2, 'S', 'Black ra2aba white', 30, 0.00), (2, 'M', 'Black ra2aba white', 30, 0.00), (2, 'L', 'Black ra2aba white', 30, 0.00),
-- Navy ra2aba white
(2, 'S', 'Navy ra2aba white', 30, 0.00), (2, 'M', 'Navy ra2aba white', 30, 0.00), (2, 'L', 'Navy ra2aba white', 30, 0.00),
-- Burgundy ra2aba white
(2, 'S', 'Burgundy ra2aba white', 30, 0.00), (2, 'M', 'Burgundy ra2aba white', 30, 0.00), (2, 'L', 'Burgundy ra2aba white', 30, 0.00);

-- Variants for Product 3 (Premium Knitted Ringer Polo): S, M, L in 2 Colors
-- Colors: White rib Black, Navy rib White
INSERT INTO product_variants (product_id, size, color, stock, price_modifier) VALUES
-- White ra2aba black
(3, 'S', 'White ra2aba black', 25, 0.00), (3, 'M', 'White ra2aba black', 25, 0.00), (3, 'L', 'White ra2aba black', 25, 0.00),
-- Navy ra2aba white
(3, 'S', 'Navy ra2aba white', 25, 0.00), (3, 'M', 'Navy ra2aba white', 25, 0.00), (3, 'L', 'Navy ra2aba white', 25, 0.00);

-- Variants for Product 4 (Ribbed Knit Top): S, M, L in 4 Colors
-- Colors: White, Black, Navy, Burgundy
INSERT INTO product_variants (product_id, size, color, stock, price_modifier) VALUES
-- White
(4, 'S', 'White', 40, 0.00), (4, 'M', 'White', 40, 0.00), (4, 'L', 'White', 40, 0.00),
-- Black
(4, 'S', 'Black', 40, 0.00), (4, 'M', 'Black', 40, 0.00), (4, 'L', 'Black', 40, 0.00),
-- Navy
(4, 'S', 'Navy', 40, 0.00), (4, 'M', 'Navy', 40, 0.00), (4, 'L', 'Navy', 40, 0.00),
-- Burgundy
(4, 'S', 'Burgundy', 40, 0.00), (4, 'M', 'Burgundy', 40, 0.00), (4, 'L', 'Burgundy', 40, 0.00);

-- 5. Insert Mock Product Images
-- Image records mapping, pointing to /public/uploads/products/ default locations
INSERT INTO product_images (product_id, image_path, is_primary) VALUES
(1, '/uploads/products/tshirt_classic.jpg', 1),
(2, '/uploads/products/ringer_classic.jpg', 1),
(3, '/uploads/products/polo_classic.jpg', 1),
(4, '/uploads/products/top_classic.jpg', 1);

-- 6. Insert default address for testing
INSERT INTO addresses (id, user_id, recipient_name, phone_number, governorate, city, street_address, building_details, is_default) VALUES
(1, 1, 'Admin Test Recipient', '01000000000', 'Cairo', 'New Cairo', '90th Street', 'Building 12, Floor 3', 1);
