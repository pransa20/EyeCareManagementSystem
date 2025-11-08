-- Insert additional products with diverse options and competitive pricing
INSERT INTO products (category_id, name, description, price, stock, image_path, category, created_at) VALUES

-- Premium Eyeglasses
(1, 'Flex Comfort Pro', 'Flexible titanium frame with memory technology and adjustable nose pads', 799.99, 15, '../assets/images/products/flex-comfort-pro.svg', 'Eyeglasses', CURRENT_TIMESTAMP),
(1, 'Nordic Minimalist', 'Scandinavian-inspired lightweight frame with premium acetate', 699.99, 12, '../assets/images/products/nordic-minimalist.svg', 'Eyeglasses', CURRENT_TIMESTAMP),
(1, 'Digital Work Plus', 'Advanced blue light protection with anti-fatigue lens coating', 649.99, 18, '../assets/images/products/digital-work-plus.svg', 'Eyeglasses', CURRENT_TIMESTAMP),
(1, 'Heritage Collection', 'Vintage-inspired acetate frames with modern comfort features', 599.99, 20, '../assets/images/products/heritage-collection.svg', 'Eyeglasses', CURRENT_TIMESTAMP),
(1, 'Air Lite Tech', 'Ultra-lightweight aerospace material frame with rimless design', 899.99, 10, '../assets/images/products/air-lite-tech.svg', 'Eyeglasses', CURRENT_TIMESTAMP),

-- Fashion Sunglasses
(2, 'Urban Explorer', 'Contemporary urban design with polarized gradient lenses', 599.99, 15, '../assets/images/products/urban-explorer.svg', 'Sunglasses', CURRENT_TIMESTAMP),
(2, 'Mountain View Pro', 'High-altitude sports sunglasses with wrap-around protection', 749.99, 12, '../assets/images/products/mountain-view-pro.svg', 'Sunglasses', CURRENT_TIMESTAMP),
(2, 'Retro Fusion', 'Classic round design with modern polarized technology', 549.99, 18, '../assets/images/products/retro-fusion.svg', 'Sunglasses', CURRENT_TIMESTAMP),
(2, 'Ocean Drive', 'Coastal-inspired design with salt-water resistant coating', 649.99, 14, '../assets/images/products/ocean-drive.svg', 'Sunglasses', CURRENT_TIMESTAMP),
(2, 'Night Driver Elite', 'Specialized night driving lenses with anti-glare technology', 699.99, 16, '../assets/images/products/night-driver-elite.svg', 'Sunglasses', CURRENT_TIMESTAMP),

-- Specialty Contact Lenses
(3, 'Sport Vision Pro', 'High-performance daily lenses for athletes', 549.99, 20, '../assets/images/products/sport-vision-pro.svg', 'Contact Lenses', CURRENT_TIMESTAMP),
(3, 'Crystal Clear Monthly', 'Premium monthly lenses with enhanced clarity', 599.99, 15, '../assets/images/products/crystal-clear-monthly.svg', 'Contact Lenses', CURRENT_TIMESTAMP),
(3, 'Natural Tone Elite', 'Premium natural-looking colored lenses', 579.99, 18, '../assets/images/products/natural-tone-elite.svg', 'Contact Lenses', CURRENT_TIMESTAMP),
(3, 'Moisture Lock Plus', 'Extended wear lenses with advanced hydration technology', 649.99, 12, '../assets/images/products/moisture-lock-plus.svg', 'Contact Lenses', CURRENT_TIMESTAMP),
(3, 'Ultra Thin Daily', 'Ultra-thin daily disposables for sensitive eyes', 529.99, 16, '../assets/images/products/ultra-thin-daily.svg', 'Contact Lenses', CURRENT_TIMESTAMP),

-- Premium Accessories
(4, 'Smart Clean Kit', 'Electronic cleaning system with UV sanitization', 549.99, 15, '../assets/images/products/smart-clean-kit.svg', 'Accessories', CURRENT_TIMESTAMP),
(4, 'Designer Case Collection', 'Premium leather cases with microfiber interior', 529.99, 18, '../assets/images/products/designer-case.svg', 'Accessories', CURRENT_TIMESTAMP),
(4, 'Pro Care Bundle', 'Complete lens care system with premium solutions', 509.99, 20, '../assets/images/products/pro-care-bundle.svg', 'Accessories', CURRENT_TIMESTAMP),
(4, 'Travel Essentials Pro', 'Compact travel kit with all necessary accessories', 519.99, 16, '../assets/images/products/travel-essentials.svg', 'Accessories', CURRENT_TIMESTAMP),
(4, 'Lens Guard Elite', 'Advanced lens protection and cleaning system', 539.99, 14, '../assets/images/products/lens-guard-elite.svg', 'Accessories', CURRENT_TIMESTAMP);