<?php
declare(strict_types=1);

namespace RepuestosDelLitoral\Controllers;

use RepuestosDelLitoral\Models\Product;
use RepuestosDelLitoral\Models\Category;
use RepuestosDelLitoral\Services\SessionService;
use RepuestosDelLitoral\Services\PricingService;
use RepuestosDelLitoral\Services\SearchService;

class CatalogController {
    /**
     * Devuelve la lista paginada de productos adaptada al tipo de usuario en sesión.
     */
    public function listProducts(array $queryParams): array {
        $currentUser = SessionService::currentUser();

        if (!empty($queryParams['search'])) {
            $result = SearchService::search($queryParams);
        } else {
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

            $result = Product::paginate($page, $perPage, $filters);
        }

        // Aplicar la lógica de precios dinámicos minorista/mayorista en items
        if (!empty($result['items'])) {
            foreach ($result['items'] as &$item) {
                $item = PricingService::applyToProduct($item, $currentUser);
            }
            unset($item);
        }

        // Aplicar la lógica de precios dinámicos minorista/mayorista en recomendados (si existen)
        if (!empty($result['recommended'])) {
            foreach ($result['recommended'] as &$recItem) {
                $recItem = PricingService::applyToProduct($recItem, $currentUser);
            }
            unset($recItem);
        }

        return $result;
    }

    /**
     * Sugerencias de autocompletado en vivo para la barra de búsqueda.
     */
    public function getSearchSuggestions(string $query): array {
        $suggestions = SearchService::getSuggestions($query, 6);
        $currentUser = SessionService::currentUser();

        foreach ($suggestions as &$sug) {
            $sug = PricingService::applyToProduct($sug, $currentUser);
        }
        unset($sug);

        return $suggestions;
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
