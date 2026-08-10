-- ============================================================
-- Repuestos del Litoral — Seed data inicial
-- ============================================================

-- Categorías
INSERT INTO categories (id, parent_id, name, slug) VALUES
(1, NULL, 'Motosierras', 'motosierras'),
(2, NULL, 'Desmalezadoras', 'desmalezadoras'),
(3, NULL, 'Repuestos y Motor', 'repuestos-y-motor'),
(4, NULL, 'Accesorios y Lubricantes', 'accesorios-y-lubricantes');

-- Productos
INSERT INTO products (id, category_id, code, name, slug, description, retail_price, wholesale_price, stock, active) VALUES
(1, 1, 'MS-250', 'Motosierra Stihl MS 250 - 45.4 cc', 'motosierra-stihl-ms-250', 'Motosierra compacta de alta potencia para trabajos de leña y mantenimiento en fincas o chacras. Motor de 2.3 kW, paso de cadena .325".', 18900.00, 16500.00, 5, 1),
(2, 1, 'HUSQ-120', 'Motosierra Husqvarna 120 Mark II', 'motosierra-husqvarna-120-mark-ii', 'Motosierra fácil de usar, ideal para tareas cotidianas de corte. Motor X-Torq con bajas emisiones y reducido consumo de combustible.', 15400.00, 13800.00, 8, 1),
(3, 2, 'DES-FS55', 'Desmalezadora Stihl FS 55', 'desmalezadora-stihl-fs-55', 'Desmalezadora liviana y maniobrable con manillar abierto. Ideal para bordear césped y desmalezar césped alto.', 13900.00, 12200.00, 6, 1),
(4, 2, 'DES-HUSQ143R', 'Desmalezadora Husqvarna 143R-II', 'desmalezadora-husqvarna-143r-ii', 'Desmalezadora robusta diseñada para trabajo pesado en condiciones exigentes. Incluye arnés doble profesional.', 24500.00, NULL, 4, 1),
(5, 3, 'REP-CAD36', 'Cadena Motosierra Stihl 36 Dientes .325"', 'cadena-motosierra-stihl-36-dientes-325', 'Cadena original Stihl de 36 dientes paso .325" y 1.3mm de espesor. Alta durabilidad y filo duradero.', 1250.00, 1050.00, 30, 1),
(6, 3, 'REP-ESP45', 'Espada Motosierra 18" Rollomatic E', 'espada-motosierra-18-rollomatic-e', 'Espada / guía de 45cm (18 pulgadas) para motosierras Stihl MS 250 y similares. Cuerpo compuesto de tres placas soldadas.', 2800.00, 2400.00, 15, 1),
(7, 3, 'REP-CARB-FS55', 'Carburador Completo para Stihl FS 55 / FS 45', 'carburador-completo-stihl-fs-55', 'Carburador de repuesto tipo Zama compatible con desmalezadoras Stihl FS 55, FS 45 y FS 38.', 1450.00, 1200.00, 12, 1),
(8, 3, 'REP-PIST-MS250', 'Kit Pistón y Aros para Motosierra MS 250 (42.5mm)', 'kit-piston-aros-motosierra-ms-250', 'Kit de cilindro y pistón completo incluye aros, perno y seguros para motosierra MS 250.', 1980.00, NULL, 10, 1),
(9, 3, 'REP-BUJ-BMR7A', 'Bujía NGK BMR7A para Motores 2 Tiempos', 'bujia-ngk-bmr7a-2-tiempos', 'Bujía universal NGK BMR7A especial para motosierras, desmalezadoras y cortadoras de césped 2T.', 220.00, 170.00, 50, 1),
(10, 4, 'ACC-ACE2T-1L', 'Aceite Castrol 2T Sintético 1 Litro', 'aceite-castrol-2t-sintetico-1-litro', 'Aceite de mezcla para motores de dos tiempos. Minimiza residuos carbonosos y extiende la vida útil del motor.', 480.00, 390.00, 40, 1),
(11, 4, 'ACC-TANZ-3MM', 'Tanaza / Tanque Hilo Nylon 3.0mm x 15m Perfil Cuadrado', 'tanaza-hilo-nylon-3mm-15m', 'Hilo de nylon reforzado de 3mm de diámetro, perfil cuadrado para alto rendimiento en desmalezadoras.', 350.00, 280.00, 25, 1),
(12, 4, 'ACC-LIMA-48', 'Lima Redonda Stihl 4.8mm (3/16") para Cadena', 'lima-redonda-stihl-48mm', 'Lima especial para afilado de cadenas de motosierra paso .325". Venta por unidad.', 180.00, 140.00, 60, 1);

-- Imágenes de productos (rutas relativas a uploads)
INSERT INTO product_images (product_id, url, sort_order, is_primary) VALUES
(1, '/assets/uploads/products/placeholder.jpg', 1, 1),
(2, '/assets/uploads/products/placeholder.jpg', 1, 1),
(3, '/assets/uploads/products/placeholder.jpg', 1, 1),
(4, '/assets/uploads/products/placeholder.jpg', 1, 1),
(5, '/assets/uploads/products/placeholder.jpg', 1, 1),
(6, '/assets/uploads/products/placeholder.jpg', 1, 1),
(7, '/assets/uploads/products/placeholder.jpg', 1, 1),
(8, '/assets/uploads/products/placeholder.jpg', 1, 1),
(9, '/assets/uploads/products/placeholder.jpg', 1, 1),
(10, '/assets/uploads/products/placeholder.jpg', 1, 1),
(11, '/assets/uploads/products/placeholder.jpg', 1, 1),
(12, '/assets/uploads/products/placeholder.jpg', 1, 1);
