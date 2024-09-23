SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;

-- Create the database
CREATE DATABASE IF NOT EXISTS clothing_store;

-- Use the newly created database
USE clothing_store;

-- Create the discount table first
CREATE TABLE IF NOT EXISTS `promotion` (
    promotion_id INT AUTO_INCREMENT PRIMARY KEY,
    promotion_name VARCHAR(100) NOT NULL,
    promo_code VARCHAR(12) NOT NULL,
    `value` DECIMAL(10, 2) NOT NULL,
    min_subtotal DECIMAL(10, 2) NOT NULL,
    `type` ENUM('Percentage', 'Amount') DEFAULT 'Percentage',
    description TEXT,
    disabled TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `color` (
    color_id INT AUTO_INCREMENT PRIMARY KEY,
    color_name VARCHAR(20) NOT NULL,
    color_code VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `size` (
    size_id INT AUTO_INCREMENT PRIMARY KEY,
    size_name VARCHAR(20) NOT NULL,
    size_code VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `neckline` (
    neckline_id INT AUTO_INCREMENT PRIMARY KEY,
    neckline_name VARCHAR(20) NOT NULL,
    neckline_code VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `sleeve` (
    sleeve_id INT AUTO_INCREMENT PRIMARY KEY,
    sleeve_name VARCHAR(20) NOT NULL,
    sleeve_code VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `fabric` (
    fabric_id INT AUTO_INCREMENT PRIMARY KEY,
    fabric_name VARCHAR(20) NOT NULL,
    fabric_code VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `contact` (
    contact_id INT AUTO_INCREMENT PRIMARY KEY,
    contact_name VARCHAR(100) NOT NULL,
    contact_no VARCHAR(12) NOT NULL,
    address_line_one VARCHAR(100) NOT NULL,
    address_line_two VARCHAR(100),
    city VARCHAR(25) NOT NULL,
    `state` VARCHAR(25) NOT NULL,
    postal_code VARCHAR(25) NOT NULL,
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `user` (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    disabled TINYINT(1) DEFAULT 0,
    role ENUM('admin', 'customer', 'supplier') DEFAULT 'customer',
    shipping_id INT,
    billing_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (shipping_id) REFERENCES contact(contact_id) ON DELETE CASCADE,
    FOREIGN KEY (billing_id) REFERENCES contact(contact_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `gender` (
    gender_id INT AUTO_INCREMENT PRIMARY KEY,
    gender_name VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `category` (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `brand` (
    brand_id INT AUTO_INCREMENT PRIMARY KEY,
    brand_name VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `product` (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(100) NOT NULL,
    skin_tone VARCHAR(10),
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    discount DECIMAL(10, 2),
    stock INT NOT NULL,
    gender_id INT,
    category_id INT,
    brand_id INT,
    favourite_count INT DEFAULT 0,
    image MEDIUMBLOB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (gender_id) REFERENCES gender(gender_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES category(category_id) ON DELETE CASCADE,
    FOREIGN KEY (brand_id) REFERENCES brand(brand_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `order` (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total DECIMAL(10, 2) NOT NULL,
    shipping_cost DECIMAL(10, 2),
    shipping_discount DECIMAL(10, 2),
    sales_tax DECIMAL(10, 2),
    status ENUM('Pending', 'Shipped', 'Delivered', 'Completed', 'Cancelled', 'Declined', 'Refunded') DEFAULT 'Pending',
    shipping_id INT,
    billing_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE,
    FOREIGN KEY (shipping_id) REFERENCES contact(contact_id) ON DELETE CASCADE,
    FOREIGN KEY (billing_id) REFERENCES contact(contact_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `item_spec` (
    item_spec_id INT AUTO_INCREMENT PRIMARY KEY,
    color_id INT NOT NULL,
    size_id INT NOT NULL,
    neckline_id INT,
    sleeve_id INT,
    fabric_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (color_id) REFERENCES `color`(color_id) ON DELETE CASCADE,
    FOREIGN KEY (size_id) REFERENCES `size`(size_id) ON DELETE CASCADE,
    FOREIGN KEY (neckline_id) REFERENCES `neckline`(neckline_id) ON DELETE CASCADE,
    FOREIGN KEY (sleeve_id) REFERENCES `sleeve`(sleeve_id) ON DELETE CASCADE,
    FOREIGN KEY (fabric_id) REFERENCES `fabric`(fabric_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `order_item` (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    item_spec_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES `order`(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES product(product_id) ON DELETE CASCADE,
    FOREIGN KEY (item_spec_id) REFERENCES item_spec(item_spec_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `cart_item` (
    cart_item_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    product_id INT,
    item_spec_id INT,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES `user`(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES product(product_id) ON DELETE CASCADE,
    FOREIGN KEY (item_spec_id) REFERENCES item_spec(item_spec_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `favourite` (
    favourite_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    product_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES product(product_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert an admin user -> password: admin@1234
INSERT INTO user (email, first_name, last_name, role, password) VALUES
('admin@example.com', 'Admin', 'User', 'admin', '$2y$10$2oc.t7BSXdJWft/VcbW1BOEifWswH1b7QEBMZN9ulxL11XHwGCBqK');

-- Insert a normal user -> password: 'user@1234'
INSERT INTO user (email, first_name, last_name, role, password) VALUES
('user@example.com', 'Normal', 'User', 'customer', '$2y$10$VPAsmY1ukb22z7RDeQxVSu3wb3CE/.7mbiuJc/wtS8g2oHaHvXToC');

INSERT INTO gender (gender_name, description) VALUES
('Men', 'Premium collection tailored for the modern man, combining style with comfort.'),
('Women', 'Elegant and trendy fashion for women who appreciate quality and design.'),
('Kids', 'Fun, colorful, and durable clothing designed to keep up with active kids.');

-- Insert sample data into categories
INSERT INTO category (category_name, description) VALUES
('Shoes', 'Explore our range of stylish and comfortable shoes, perfect for every occasion.'),
('Bags', 'Carry your essentials in style with our collection of trendy and functional bags.'),
('Tops', 'Discover our selection of fashionable tops, designed to make you stand out.'),
('Pants', 'Find the perfect pair of pants, blending comfort with cutting-edge fashion.'),
('Dresses', 'Elegant dresses that are perfect for any event, offering a mix of style and sophistication.'),
('Outerwear', 'Stay warm and stylish with our collection of coats, jackets, and other outerwear.'),
('Accessories', 'Complete your look with our range of accessories, from belts to hats, that add the perfect finishing touch.'),
('Sportswear', 'Performance-driven sportswear designed to help you reach your peak in style and comfort.');

-- Insert sample data into brands
INSERT INTO brand (brand_name, description) VALUES
('Nike', 'Nike is a global leader in sportswear, committed to inspiring athletes with innovation and quality.'),
('Adidas', 'Adidas is known for blending performance with style, offering cutting-edge designs for athletes and fashion enthusiasts alike.'),
('Gucci', 'Gucci represents the pinnacle of Italian craftsmanship and luxury, offering timeless designs that epitomize sophistication.'),
('Levi\'s', 'Levi\'s has been synonymous with denim since 1853, creating iconic jeans that define casual style.'),
('H&M', 'H&M offers fashion and quality at the best price in a sustainable way, making trendy styles accessible to everyone.'),
('Zara', 'Zara is known for its fast fashion, bringing the latest trends from the runway to the streets at an affordable price.'),
('Puma', 'Puma fuses sport, lifestyle, and fashion, producing innovative products for the fastest athletes and trendsetters.'),
('Under Armour', 'Under Armour is dedicated to making all athletes better through passion, design, and relentless pursuit of innovation.'),
('Calvin Klein', 'Calvin Klein is a global lifestyle brand that exemplifies bold, progressive ideals and a seductive aesthetic.'),
('Louis Vuitton', 'Louis Vuitton is a world-renowned luxury brand known for its iconic bags, shoes, and accessories that define elegance and sophistication.');

-- Insert sample data into colors
INSERT INTO `color` (color_name, color_code) VALUES
('Black', '#000000'),
('White', '#FFFFFF'),
('Red', '#0000FF'),
('Blue', '#FF0000'),
('Navy Blue', '#000080'),
('Gray', '#808080'),
('Beige', '#F5F5DC'),
('Brown', '#A52A2A'),
('Olive Green', '#808000'),
('Burgundy', '#800020'),
('Mustard', '#FFDB58'),
('Coral', '#FF7F50'),
('Emerald Green', '#50C878'),
('Blush Pink', '#F5C2C2'),
('Lavender', '#E6E6FA'),
('Teal', '#008080'),
('Rose Gold', '#B76E79'),
('Champagne', '#F7E7CE'),
('Cobalt Blue', '#0047AB'),
('Rust', '#B7410E'),
('Ivory', '#FFFFF0'),
('Turquoise', '#40E0D0');

INSERT INTO `size` (size_name, size_code) VALUES
('Extra Small', 'XS'),
('Small', 'S'),
('Medium', 'M'),
('Large', 'L'),
('Extra Large', 'XL'),
('Double XL', 'XXL'),
('Triple XL', 'XXXL');

INSERT INTO `neckline` (neckline_name, neckline_code) VALUES
('V-Neck', 'VNK'),
('Round Neck', 'RND'),
('Scoop Neck', 'SCP'),
('Boat Neck', 'BNT'),
('Halter Neck', 'HLT'),
('Square Neck', 'SQN'),
('Sweetheart Neck', 'SWT'),
('Off-Shoulder', 'OFF'),
('Turtleneck', 'TRT'),
('Cowl Neck', 'CWL');

INSERT INTO `sleeve` (sleeve_name, sleeve_code) VALUES
('Short Sleeve', 'SHS'),
('Long Sleeve', 'LNS'),
('Sleeveless', 'SLS'),
('Cap Sleeve', 'CPS'),
('Bell Sleeve', 'BLS'),
('Puff Sleeve', 'PFS'),
('Raglan Sleeve', 'RGS'),
('Bishop Sleeve', 'BPS'),
('Kimono Sleeve', 'KMS'),
('Flutter Sleeve', 'FTS');

INSERT INTO `fabric` (fabric_name, fabric_code) VALUES
('Cotton', 'CTN'),
('Silk', 'SLK'),
('Wool', 'WOL'),
('Linen', 'LIN'),
('Denim', 'DNM'),
('Polyester', 'PLY'),
('Rayon', 'RYN'),
('Nylon', 'NYL'),
('Velvet', 'VLV'),
('Chiffon', 'CHF');

-- Insert sample data into products
INSERT INTO product (product_name, skin_tone,  description, price, discount, stock, gender_id, category_id, brand_id, favourite_count, image, created_at, last_modified) VALUES
('Nike Air Max 270', 'light', 'Featuring a sleek design and a large Max Air unit, these sneakers offer exceptional cushioning and a stylish look. Perfect for both casual wear and athletic performance.', 150.00, 0.00, 30, 1, 1, 1, 12, NULL, NOW(), NOW()),

('Adidas UltraBoost 21', 'dark', 'These running shoes are made with Primeknit material and feature a Boost midsole for unparalleled comfort and energy return. Ideal for long-distance running.', 180.00, 25.00, 25, 1, 1, 2, 15, NULL, NOW(), NOW()),

('Gucci GG Marmont Bag', 'fair', 'This chic Gucci bag is crafted from matelassé leather with the iconic GG logo. Its spacious interior and elegant design make it a perfect accessory for any outfit.', 2200.00, 0.00, 10, 1, 2, 3, 20, NULL, NOW(), NOW()),

('Levi\'s 501 Original Jeans', 'fair', 'Timeless and durable, these Levi\'s jeans feature a classic straight fit and sturdy denim material. Perfect for everyday wear and casual outings.', 89.99, 10.00, 40, 1, 3, 4, 18, NULL, NOW(), NOW()),

('H&M Cotton V-Neck Shirt', 'fair', 'This comfortable cotton shirt features a classic V-neck design. Breathable and versatile, it\'s ideal for both casual and semi-formal occasions.', 29.99, 5.00, 50, 2, 4, 5, 25, NULL, NOW(), NOW()),

('Zara Faux Leather Jacket', 'fair', 'Stylish and edgy, this faux leather jacket has a modern cut and multiple zippered pockets. Great for layering in cooler weather.', 99.99, 15.00, 15, 1, 5, 6, 22, NULL, NOW(), NOW()),

('Puma Suede Classic', 'medium', 'These iconic Puma sneakers are made from soft suede and feature a rubber outsole for durability. A versatile choice for any casual look.', 75.00, 10.00, 20, 1, 6, 7, 14, NULL, NOW(), NOW()),

('Under Armour Performance T-Shirt', 'fair', 'Crafted with moisture-wicking technology, this performance T-shirt keeps you dry and comfortable during workouts. Lightweight and breathable.', 35.00, 8.00, 60, 2, 7, 8, 30, NULL, NOW(), NOW()),

('Calvin Klein Slim Fit Jeans', 'fair', 'These jeans are made from a blend of cotton and elastane for a slim fit with added stretch. They offer a modern look with classic styling.', 99.00, 12.00, 35, 1, 8, 9, 20, NULL, NOW(), NOW()),

('Louis Vuitton Neverfull MM', 'medium', 'This versatile tote bag is crafted from Monogram canvas with leather handles. Its spacious interior makes it perfect for everyday use.', 1550.00, 10.00, 12, 1, 4, 10, 28, NULL, NOW(), NOW()),

('Nike Air Force 1', 'dark', 'Classic and comfortable, these Nike Air Force 1 sneakers feature a durable leather upper and cushioned sole. A staple for any sneaker collection.', 110.00, 15.00, 25, 1, 1, 1, 18, NULL, NOW(), NOW()),

('Adidas NMD_R1', 'light', 'With a Primeknit upper and Boost midsole, the Adidas NMD_R1 offers a sleek design and superior comfort. Perfect for both running and casual wear.', 140.00, 20.00, 20, 1, 2, 2, 22, NULL, NOW(), NOW()),

('Gucci Horsebit Loafer', 'fair', 'These luxury loafers feature the iconic horsebit detail and are crafted from high-quality leather. Perfect for adding a touch of sophistication to any outfit.', 850.00, 10.00, 12, 1, 3, 3, 25, NULL, NOW(), NOW()),

('Levi\'s Trucker Jacket', 'medium', 'A classic denim jacket made from durable cotton with a button-down front and multiple pockets. Great for layering over any outfit.', 110.00, 10.00, 30, 1, 4, 4, 20, NULL, NOW(), NOW()),

('H&M Linen Blend Shorts', 'medium', 'These shorts are made from a breathable linen blend, perfect for hot weather. They feature an elastic waistband with a drawstring for added comfort.', 25.00, 5.00, 50, 2, 5, 5, 15, NULL, NOW(), NOW()),

('Zara Plaid Shirt', 'dark', 'This plaid shirt is made from soft cotton flannel, offering a relaxed fit with a button-down front and chest pockets. Great for casual wear.', 49.99, 5.00, 45, 2, 6, 6, 20, NULL, NOW(), NOW()),

('Puma Training Leggings', 'dark', 'Made from stretchy, moisture-wicking fabric, these leggings are designed for comfort and flexibility during workouts. The high-rise waistband ensures a secure fit.', 45.00, 5.00, 30, 2, 7, 7, 18, NULL, NOW(), NOW()),

('Under Armour Base Layer Top', 'fair', 'This thermal top is made from soft fabric that retains heat, perfect for layering in cold weather. It also features moisture-wicking properties.', 50.00, 6.00, 40, 2, 8, 8, 25, NULL, NOW(), NOW()),

('Calvin Klein Leather Belt', 'light', 'A classic leather belt featuring the Calvin Klein logo buckle. This belt is a versatile accessory that adds a sophisticated touch to any outfit.', 60.00, 5.00, 20, 1, 3, 9, 22, NULL, NOW(), NOW()),

('Louis Vuitton Monogram Scarf', 'medium', 'Crafted from a blend of silk and wool, this scarf features the iconic LV monogram pattern. A luxurious accessory that adds elegance to any look.', 460.00, 30.00, 15, 1, 3, 10, 28, NULL, NOW(), NOW()),

('Nike Dri-FIT Shorts', 'fair', 'These athletic shorts are made from moisture-wicking fabric to keep you dry during workouts. The elastic waistband offers a comfortable and adjustable fit.', 35.00, 4.00, 50, 1, 1, 1, 20, NULL, NOW(), NOW()),

('Adidas 3-Stripes T-Shirt', 'medium', 'Featuring the classic 3-Stripes design, this T-shirt is made from soft cotton and offers a comfortable fit for casual wear or workouts.', 40.00, 3.00, 50, 2, 2, 2, 22, NULL, NOW(), NOW()),

('Gucci Silk Tie', 'medium', 'This luxurious silk tie features a subtle pattern and the Gucci logo at the back. A refined accessory for formal occasions.', 250.00, 20.00, 30, 1, 3, 3, 25, NULL, NOW(), NOW()),

('Levi\'s Graphic Hoodie', 'medium', 'Made from soft cotton, this hoodie features a bold Levi\'s logo on the chest. Perfect for casual wear and staying warm.', 60.00, 5.00, 40, 1, 4, 4, 20, NULL, NOW(), NOW()),

('H&M Wool Blend Coat', 'fair',  'This coat is made from a wool blend for warmth and style. It features a double-breasted design and a tailored fit, making it perfect for colder weather.', 120.00, 15.00, 25, 2, 5, 5, 18, NULL, NOW(), NOW()),

('Zara High-Waisted Trousers', 'medium',  'These trousers are made from lightweight fabric with a high-waisted design and a tailored fit. Perfect for both work and casual outings.', 49.99, 6.00, 40, 2, 6, 6, 25, NULL, NOW(), NOW()),

('Puma Graphic T-Shirt', 'fair', 'This graphic tee is made from comfortable cotton with a bold Puma logo on the front. Ideal for everyday wear.', 25.00, 2.00, 60, 2, 7, 7, 18, NULL, NOW(), NOW()),

('Under Armour Training Cap', 'fair', 'Made from breathable fabric, this cap keeps you cool during workouts. It features an adjustable strap for a customized fit.', 20.00, 2.00, 75, 2, 8, 8, 20, NULL, NOW(), NOW()),

('Calvin Klein Denim Skirt', 'medium', 'This skirt is made from stretch denim for a comfortable fit. It features a zippered front and a raw hem for a modern touch.', 75.00, 5.00, 40, 2, 5, 8, 22, NULL, NOW(), NOW()),

('Louis Vuitton Epi Leather Wallet', 'fair', 'Crafted from durable Epi leather, this wallet features multiple card slots and a zip-around design for convenience.', 760.00, 18.00, 25, 2, 1, 10, 28, NULL, NOW(), NOW()),

('Nike Yoga Pants', 'fair',  'These yoga pants are made from soft, stretchy fabric with a high-rise waistband. Perfect for yoga and other workouts.', 55.00, 5.00, 35, 2, 1, 1, 18, NULL, NOW(), NOW()),

('Adidas Running Jacket', 'medium', 'This lightweight jacket is made from water-resistant fabric with reflective details for safety during runs. It features a zippered front.', 80.00, 10.00, 30, 2, 2, 2, 25, NULL, NOW(), NOW()),

('Gucci Wool Sweater', 'medium', 'A luxurious wool sweater with the iconic GG logo. It offers warmth and style with a relaxed fit.', 950.00, 5.00, 15, 2, 3, 3, 30, NULL, NOW(), NOW()),

('Levi\'s Trucker Jacket', 'medium', 'Durable denim jacket with a button-down front and multiple pockets. A classic piece for layering.', 130.00, 10.00, 30, 2, 4, 4, 22, NULL, NOW(), NOW()),

('H&M V-Neck Sweater', 'fair', 'Soft knit V-neck sweater with a relaxed fit. Ideal for layering or wearing on its own.', 35.00, 3.00, 60, 2, 5, 5, 18, NULL, NOW(), NOW()),

('Zara High-Waisted Trousers', 'dark', 'Tailored high-waisted trousers with a zippered front and side pockets. A chic option for various occasions.', 49.99, 5.00, 40, 2, 6, 6, 20, NULL, NOW(), NOW()),

('Puma Graphic T-Shirt', 'medium', 'Graphic T-shirt made from soft cotton with a bold Puma logo. Perfect for casual wear.', 25.00, 2.00, 60, 2, 7, 7, 18, NULL, NOW(), NOW()),

('Under Armour Training Cap', 'fair', 'Lightweight training cap with breathable fabric and adjustable strap. Keeps you cool during workouts.', 20.00, 5.00, 75, 2, 8, 8, 20, NULL, NOW(), NOW()),

('Calvin Klein Denim Skirt', 'light', 'Stretch denim skirt with a zippered front and raw hem. Offers a comfortable and modern fit.', 75.00, 5.00, 40, 2, 8, 9, 20, NULL, NOW(), NOW()),

('Louis Vuitton Epi Leather Wallet', 'fair', 'Durable Epi leather wallet with multiple card slots and zip-around design. Stylish and functional.', 760.00, 10.00, 25, 2, 7, 10, 25, NULL, NOW(), NOW());

