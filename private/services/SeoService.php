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
                    'closes' => '12:00'
                ],
                [
                    '@type' => 'OpeningHoursSpecification',
                    'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
                    'opens' => '14:00',
                    'closes' => '18:00'
                ],
                [
                    '@type' => 'OpeningHoursSpecification',
                    'dayOfWeek' => ['Saturday'],
                    'opens' => '08:00',
                    'closes' => '12:00'
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
     * Retorna títulos y meta descriptions optimizados para páginas de categoría.
     */
    public static function getCategorySeoMeta(?string $slug): array {
        $normalized = $slug ? strtolower(trim($slug)) : '';

        if ($normalized === 'motosierras' || str_contains($normalized, 'motosierra')) {
            return [
                'title' => 'Motosierras a Nafta y Repuestos | Repuestos del Litoral',
                'meta_description' => 'Venta de motosierras a nafta, repuestos, espadas, cadenas y bujías Stihl y Husqvarna en Dolores, Soriano. Envíos a todo Uruguay.',
                'h1' => 'Motosierras a Nafta y Repuestos Originales'
            ];
        }

        if ($normalized === 'desmalezadoras' || str_contains($normalized, 'desmalezadora')) {
            return [
                'title' => 'Desmalezadoras a Nafta y Repuestos | Repuestos del Litoral',
                'meta_description' => 'Venta de desmalezadoras a nafta, repuestos, discos, carburadores e hilo en Dolores, Soriano, Uruguay. Calidad y repuestos agrícolas.',
                'h1' => 'Desmalezadoras a Nafta y Repuestos para Jardín'
            ];
        }

        if ($normalized === 'herramientas-de-jardin' || $normalized === 'jardin' || $normalized === 'bordeadoras' || str_contains($normalized, 'jardin') || str_contains($normalized, 'bordeadora')) {
            return [
                'title' => 'Herramientas de Jardín y Bordeadoras | Repuestos del Litoral',
                'meta_description' => 'Ferretería y herramientas de jardín en Dolores, Soriano. Bordeadoras, repuestos e insumos agrícolas en Repuestos del Litoral Uruguay.',
                'h1' => 'Herramientas de Jardín, Bordeadoras y Repuestos'
            ];
        }

        return [
            'title' => 'Motosierras, Desmalezadoras y Repuestos | Repuestos del Litoral',
            'meta_description' => 'Repuestos del Litoral en Dolores, Soriano. Venta de motosierras, desmalezadoras, repuestos agrícolas, ferretería y herramientas en Uruguay.',
            'h1' => 'Catálogo de Repuestos, Motosierras y Ferretería'
        ];
    }

    /**
     * Retorna títulos y meta descriptions optimizados para fichas de producto detectando palabras clave long-tail.
     */
    public static function getProductSeoMeta(array $product): array {
        $name = $product['name'] ?? '';
        $rawDesc = !empty($product['description']) ? strip_tags($product['description']) : '';
        $lowerName = mb_strtolower($name);
        $keyword = '';
        
        if (str_contains($lowerName, 'bujia') || str_contains($lowerName, 'bujía')) {
            $keyword = 'bujías motosierra';
        } elseif (str_contains($lowerName, 'espada')) {
            $keyword = 'espada motosierra';
        } elseif (str_contains($lowerName, 'cadena')) {
            $keyword = 'cadena para motosierra';
        } elseif (str_contains($lowerName, 'carburador')) {
            $keyword = 'carburador desmalezadora';
        } elseif (str_contains($lowerName, 'disco')) {
            $keyword = 'disco para desmalezadora';
        } elseif (str_contains($lowerName, 'motosierra') && (str_contains($lowerName, 'nafta') || str_contains($lowerName, '2 tiempos'))) {
            $keyword = 'motosierra a nafta';
        } elseif (str_contains($lowerName, 'motosierra')) {
            $keyword = 'repuestos motosierra';
        } elseif (str_contains($lowerName, 'desmalezadora')) {
            $keyword = 'repuestos desmalezadora';
        }

        if ($keyword !== '') {
            $title = mb_substr($name, 0, 30) . ' — ' . ucfirst($keyword) . ' | RDL';
        } else {
            $title = mb_substr($name, 0, 38) . ' | Repuestos del Litoral';
        }

        if (mb_strlen($title) > 60) {
            $title = mb_substr($title, 0, 57) . '...';
        }

        if ($rawDesc !== '') {
            $desc = mb_substr($rawDesc, 0, 100) . '... Compra en Repuestos del Litoral, Dolores, Soriano (Uruguay). Envíos a todo el país.';
        } else {
            $desc = "Compre {$name} en Repuestos del Litoral, Dolores, Soriano (Uruguay). Venta de repuestos agrícolas, motosierras y ferretería.";
        }

        if (mb_strlen($desc) > 155) {
            $desc = mb_substr($desc, 0, 152) . '...';
        } elseif (mb_strlen($desc) < 120) {
            $desc = str_pad($desc, 125, ' Envíos a todo Uruguay.');
        }

        return [
            'title' => $title,
            'meta_description' => $desc,
            'keyword' => $keyword
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
