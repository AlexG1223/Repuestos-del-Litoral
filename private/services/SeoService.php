<?php
declare(strict_types=1);

namespace RepuestosDelLitoral\Services;

class SeoService {
    public static function getBaseUrl(): string {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'repuestosdellitoral.com';
        return $protocol . '://' . $host;
    }

    /**
     * Esquema LocalBusiness / HardwareStore para Repuestos del Litoral.
     */
    public static function getLocalBusinessSchema(): array {
        $baseUrl = self::getBaseUrl();
        return [
            '@context' => 'https://schema.org',
            '@type' => ['Store', 'HardwareStore'],
            '@id' => $baseUrl . '/#organization',
            'name' => 'Repuestos del Litoral',
            'legalName' => 'Repuestos del Litoral',
            'url' => $baseUrl . '/',
            'logo' => $baseUrl . '/assets/img/logo.png',
            'image' => $baseUrl . '/assets/img/inicio-1.jpg',
            'description' => 'Venta minorista y mayorista de repuestos para maquinaria agrícola e industrial, artículos de ferretería, calzado de trabajo, mates, artículos de pesca y productos para mascotas en Dolores, Soriano, Uruguay.',
            'telephone' => '+59845344109',
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => '+59899655283',
                'contactType' => 'customer service',
                'availableLanguage' => ['Spanish']
            ],
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'Asencio 1930',
                'addressLocality' => 'Dolores',
                'addressRegion' => 'Soriano',
                'postalCode' => '75200',
                'addressCountry' => 'UY'
            ],
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => -33.5352914,
                'longitude' => -58.2143246
            ],
            'hasMap' => 'https://www.google.com/maps/place/Semiller%C3%ADa+My.Vi.Da/@-33.5279796,-58.248913,13.57z/data=!4m10!1m2!2m1!1sAsencio+1930,+Dolores,+Soriano,+Uruguay!3m6!1s0x95a5291884f9ab0f:0x6aa8bf331873e2d1!8m2!3d-33.5352914!4d-58.2143246',
            'openingHoursSpecification' => [
                [
                    '@type' => 'OpeningHoursSpecification',
                    'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
                    'opens' => '08:00',
                    'closes' => '19:00'
                ],
                [
                    '@type' => 'OpeningHoursSpecification',
                    'dayOfWeek' => ['Saturday'],
                    'opens' => '08:00',
                    'closes' => '12:30'
                ]
            ],
            'priceRange' => '$$',
            'areaServed' => [
                [
                    '@type' => 'AdministrativeArea',
                    'name' => 'Dolores'
                ],
                [
                    '@type' => 'AdministrativeArea',
                    'name' => 'Soriano'
                ],
                [
                    '@type' => 'AdministrativeArea',
                    'name' => 'Litoral'
                ],
                [
                    '@type' => 'Country',
                    'name' => 'Uruguay'
                ]
            ]
        ];
    }

    /**
     * Esquema Product para fichas de producto.
     */
    public static function getProductSchema(array $product): array {
        $baseUrl = self::getBaseUrl();
        $productUrl = $baseUrl . '/producto/' . rawurlencode($product['slug'] ?? '');
        
        $images = [];
        if (!empty($product['images']) && is_array($product['images'])) {
            foreach ($product['images'] as $img) {
                $url = $img['url'] ?? '';
                if ($url) {
                    $images[] = str_starts_with($url, 'http') ? $url : $baseUrl . $url;
                }
            }
        }
        if (empty($images)) {
            $images[] = $baseUrl . '/assets/uploads/products/placeholder.jpg';
        }

        $stock = (int)($product['stock'] ?? 0);
        $availability = $stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock';
        $price = (float)($product['display_price'] ?? $product['retail_price'] ?? 0);

        $description = !empty($product['description']) 
            ? strip_tags($product['description']) 
            : ($product['name'] . ' disponible en Repuestos del Litoral, Dolores, Soriano.');

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            '@id' => $productUrl . '#product',
            'name' => $product['name'] ?? '',
            'url' => $productUrl,
            'image' => $images,
            'description' => $description,
            'sku' => $product['code'] ?? (string)($product['id'] ?? ''),
            'mpn' => $product['code'] ?? '',
            'brand' => [
                '@type' => 'Brand',
                'name' => self::extractBrandName($product['name'] ?? '')
            ],
            'offers' => [
                '@type' => 'Offer',
                'url' => $productUrl,
                'priceCurrency' => 'UYU',
                'price' => number_format($price, 2, '.', ''),
                'itemCondition' => 'https://schema.org/NewCondition',
                'availability' => $availability,
                'seller' => [
                    '@type' => 'Organization',
                    'name' => 'Repuestos del Litoral'
                ]
            ]
        ];

        if (!empty($product['category_name'])) {
            $schema['category'] = $product['category_name'];
        }

        return $schema;
    }

    /**
     * Esquema BreadcrumbList.
     */
    public static function getBreadcrumbSchema(array $items): array {
        $baseUrl = self::getBaseUrl();
        $itemListElement = [];
        $position = 1;

        foreach ($items as $item) {
            $itemListElement[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $item['name'],
                'item' => str_starts_with($item['url'], 'http') ? $item['url'] : $baseUrl . $item['url']
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $itemListElement
        ];
    }

    /**
     * Esquema FAQPage para la sección de preguntas frecuentes.
     */
    public static function getFaqSchema(array $qaList): array {
        $mainEntity = [];

        foreach ($qaList as $qa) {
            $mainEntity[] = [
                '@type' => 'Question',
                'name' => $qa['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $qa['answer']
                ]
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $mainEntity
        ];
    }

    /**
     * Helper para extraer la marca del nombre del producto (ej: Stihl, Husqvarna, Castrol, NGK).
     */
    private static function extractBrandName(string $name): string {
        $knownBrands = ['Stihl', 'Husqvarna', 'Castrol', 'NGK', 'Niwa', 'Echo', 'Toyama', 'Tramontina'];
        foreach ($knownBrands as $brand) {
            if (stripos($name, $brand) !== false) {
                return $brand;
            }
        }
        return 'Repuestos del Litoral';
    }
}
