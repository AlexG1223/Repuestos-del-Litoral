-- Migration script for categories and subcategories hierarchy
-- Ensure foreign key constraint exists and insert sample subcategories

-- Verify parent_id column exists (it's already in schema.sql, but this ensures safe execution on existing databases)
SET FOREIGN_KEY_CHECKS=0;

-- Sample subcategories for demonstration:
-- Under Motosierras (id: 1)
INSERT IGNORE INTO categories (id, parent_id, name, slug) VALUES
(5, 1, 'Cadenas y Espadas', 'cadenas-y-espadas'),
(6, 1, 'Pistones y Cilindros', 'pistones-y-cilindros');

-- Under Desmalezadoras (id: 2)
INSERT IGNORE INTO categories (id, parent_id, name, slug) VALUES
(7, 2, 'Cabezales e Hilos', 'cabezales-e-hilos'),
(8, 2, 'Carburadores y Motores', 'carburadores-y-motores');

-- Under Repuestos y Motor (id: 3)
INSERT IGNORE INTO categories (id, parent_id, name, slug) VALUES
(9, 3, 'Bujías y Filtros', 'bujias-y-filtros');

-- Under Accesorios y Lubricantes (id: 4)
INSERT IGNORE INTO categories (id, parent_id, name, slug) VALUES
(10, 4, 'Aceites 2T y Mezc', 'aceites-2t-y-mezcla');

-- Optional: re-assign existing sample products to subcategories if applicable
UPDATE products SET category_id = 5 WHERE id IN (5, 6);
UPDATE products SET category_id = 8 WHERE id = 7;
UPDATE products SET category_id = 6 WHERE id = 8;
UPDATE products SET category_id = 9 WHERE id = 9;
UPDATE products SET category_id = 10 WHERE id = 10;

SET FOREIGN_KEY_CHECKS=1;
