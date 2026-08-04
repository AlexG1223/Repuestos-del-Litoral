<?php
declare(strict_types=1);

namespace RepuestosDelLitoral\Controllers;

use RepuestosDelLitoral\Models\Product;
use RepuestosDelLitoral\Models\Category;

class CatalogController {
    /**
     * Devuelve la lista paginada de productos según parámetros de búsqueda.
     */
    public function listProducts(array $queryParams): array {
        $page = isset($queryParams['page']) ? (int)$queryParams['page'] : 1;
        $perPage = isset($queryParams['per_page']) ? (int)$queryParams['per_page'] : 12;

        $filters = [];
        if (!empty($queryParams['category'])) {
            if (is_numeric($queryParams['category'])) {
                $filters['category_id'] = (int)$queryParams['category'];
            } else {
                $filters['category_slug'] = trim((string)$queryParams['category']);
            }
        }

        if (!empty($queryParams['search'])) {
            $filters['search'] = trim((string)$queryParams['search']);
        }

        return Product::paginate($page, $perPage, $filters);
    }

    /**
     * Devuelve el detalle de un producto por su slug.
     */
    public function getProductDetail(string $slug): ?array {
        $cleanSlug = trim($slug);
        if ($cleanSlug === '') {
            return null;
        }
        return Product::findBySlug($cleanSlug);
    }

    /**
     * Devuelve todas las categorías registradas.
     */
    public function listCategories(): array {
        return Category::all();
    }
}
