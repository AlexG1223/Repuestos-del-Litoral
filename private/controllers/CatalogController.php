<?php
declare(strict_types=1);

namespace RepuestosDelLitoral\Controllers;

use RepuestosDelLitoral\Models\Product;
use RepuestosDelLitoral\Models\Category;
use RepuestosDelLitoral\Services\SessionService;
use RepuestosDelLitoral\Services\PricingService;

class CatalogController {
    /**
     * Devuelve la lista paginada de productos adaptada al tipo de usuario en sesión.
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

        $result = Product::paginate($page, $perPage, $filters);
        $currentUser = SessionService::currentUser();

        // Aplicar la lógica de precios dinámicos minorista/mayorista
        foreach ($result['items'] as &$item) {
            $item = PricingService::applyToProduct($item, $currentUser);
        }
        unset($item);

        return $result;
    }

    /**
     * Devuelve el detalle de un producto por su slug adaptado al tipo de usuario en sesión.
     */
    public function getProductDetail(string $slug): ?array {
        $cleanSlug = trim($slug);
        if ($cleanSlug === '') {
            return null;
        }

        $product = Product::findBySlug($cleanSlug);
        if (!$product) {
            return null;
        }

        $currentUser = SessionService::currentUser();
        return PricingService::applyToProduct($product, $currentUser);
    }

    /**
     * Devuelve todas las categorías registradas.
     */
    public function listCategories(): array {
        return Category::all();
    }
}
