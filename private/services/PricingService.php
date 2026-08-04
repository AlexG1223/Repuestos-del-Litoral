<?php
declare(strict_types=1);

namespace RepuestosDelLitoral\Services;

class PricingService {

    /**
     * Resuelve el precio y nivel aplicable para un producto según la sesión activa del usuario.
     * 
     * @param array $product
     * @param array|null $sessionUser
     * @return array ['price' => float, 'tier' => 'retail'|'wholesale', 'original_retail' => float|null]
     */
    public static function resolvePrice(array $product, ?array $sessionUser): array {
        $retailPrice = (float)($product['retail_price'] ?? 0);
        $wholesalePrice = isset($product['wholesale_price']) && $product['wholesale_price'] !== null 
            ? (float)$product['wholesale_price'] 
            : null;

        $isWholesaleApproved = $sessionUser !== null 
            && isset($sessionUser['role']) && $sessionUser['role'] === 'wholesale' 
            && isset($sessionUser['approved']) && (int)$sessionUser['approved'] === 1;

        if ($isWholesaleApproved && $wholesalePrice !== null && $wholesalePrice > 0) {
            return [
                'price'           => $wholesalePrice,
                'tier'            => 'wholesale',
                'original_retail' => $retailPrice
            ];
        }

        return [
            'price'           => $retailPrice,
            'tier'            => 'retail',
            'original_retail' => null
        ];
    }

    /**
     * Aplica la resolución de precio a una estructura de producto o lista de productos.
     */
    public static function applyToProduct(array $product, ?array $sessionUser): array {
        $priceInfo = self::resolvePrice($product, $sessionUser);

        $product['retail_price']    = (float)$product['retail_price'];
        $product['wholesale_price'] = isset($product['wholesale_price']) && $product['wholesale_price'] !== null 
            ? (float)$product['wholesale_price'] 
            : null;
        
        $product['display_price']   = $priceInfo['price'];
        $product['price']           = $priceInfo['price']; // Compatibilidad
        $product['price_tier']      = $priceInfo['tier'];
        $product['is_wholesale']    = $priceInfo['tier'] === 'wholesale';

        return $product;
    }
}
