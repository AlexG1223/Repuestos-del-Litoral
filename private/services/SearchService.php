<?php
declare(strict_types=1);

namespace RepuestosDelLitoral\Services;

use RepuestosDelLitoral\Config\Database;
use RepuestosDelLitoral\Models\Product;
use PDO;

class SearchService {
    private static ?array $synonymsMap = null;
    private static ?array $productIndexCache = null;

    /**
     * Normaliza una cadena de texto (acentos, minúsculas, espacios y caracteres especiales).
     */
    public static function normalizeText(string $text): string {
        $text = mb_strtolower($text, 'UTF-8');

        // Mapa de acentos y caracteres especiales en español
        $accents = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u',
            'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
            'â' => 'a', 'ê' => 'e', 'î' => 'i', 'ô' => 'o', 'û' => 'u',
            'ñ' => 'n'
        ];
        $text = strtr($text, $accents);

        // Reemplazar caracteres no alfanuméricos excepto '/' y '-' por espacios
        $text = preg_replace('/[^\a-z0-9\/\-\s]/u', ' ', $text);

        // Normalizar múltiples espacios
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    /**
     * Carga el diccionario de sinónimos y construye el mapa de búsqueda inversa.
     */
    public static function getSynonymMap(): array {
        if (self::$synonymsMap === null) {
            $synonymsFile = dirname(__DIR__) . '/config/synonyms.php';
            $rawSynonyms = file_exists($synonymsFile) ? require $synonymsFile : [];

            $map = [];
            foreach ($rawSynonyms as $canonical => $variations) {
                $normCanonical = self::normalizeText((string)$canonical);
                $map[$normCanonical] = $normCanonical;

                foreach ($variations as $var) {
                    $normVar = self::normalizeText((string)$var);
                    if ($normVar !== '') {
                        $map[$normVar] = $normCanonical;
                    }
                }
            }

            self::$synonymsMap = $map;
        }

        return self::$synonymsMap;
    }

    /**
     * Procesa la consulta de búsqueda, aplicando reemplazos de sinónimos y tokenización.
     */
    public static function processQuery(string $rawQuery): array {
        $normalized = self::normalizeText($rawQuery);
        if ($normalized === '') {
            return [
                'normalized' => '',
                'tokens' => [],
                'expanded_terms' => []
            ];
        }

        $synMap = self::getSynonymMap();

        // 1. Reemplazos de frases complejas de sinónimos (ej: "moto sierra" -> "motosierra")
        $processedStr = $normalized;
        foreach ($synMap as $variant => $canonical) {
            $strVar = (string)$variant;
            $strCan = (string)$canonical;
            if (str_contains($strVar, ' ') && str_contains($processedStr, $strVar)) {
                $processedStr = str_replace($strVar, $strCan, $processedStr);
            }
        }

        $rawTokens = explode(' ', $processedStr);
        $tokens = [];
        $expandedTerms = [];

        foreach ($rawTokens as $token) {
            $token = trim($token);
            if ($token === '' || mb_strlen($token) < 2 && !is_numeric($token)) {
                continue;
            }

            // Normalización de singular/plural básico en español
            $cleanToken = $token;
            if (mb_strlen($cleanToken) > 3 && str_ends_with($cleanToken, 'es')) {
                $cleanToken = mb_substr($cleanToken, 0, -2);
            } elseif (mb_strlen($cleanToken) > 3 && str_ends_with($cleanToken, 's') && !str_ends_with($cleanToken, 'is')) {
                $cleanToken = mb_substr($cleanToken, 0, -1);
            }

            $tokens[] = $token;
            if ($cleanToken !== $token) {
                $tokens[] = $cleanToken;
            }

            // Buscar en mapa de sinónimos
            if (isset($synMap[$token])) {
                $expandedTerms[] = $synMap[$token];
            }
            if (isset($synMap[$cleanToken])) {
                $expandedTerms[] = $synMap[$cleanToken];
            }
            $expandedTerms[] = $token;
            $expandedTerms[] = $cleanToken;
        }

        $allTerms = array_values(array_unique(array_filter(array_merge($tokens, $expandedTerms))));

        return [
            'normalized' => $normalized,
            'tokens' => array_values(array_unique($tokens)),
            'expanded_terms' => $allTerms
        ];
    }

    /**
     * Calcula la distancia Levenshtein entre dos palabras tolerando 1 a 2 caracteres según longitud.
     */
    public static function isFuzzyMatch(string $word1, string $word2, &$outDistance = 0): bool {
        $str1 = (string)$word1;
        $str2 = (string)$word2;

        if ($str1 === $str2) {
            $outDistance = 0;
            return true;
        }

        $len1 = mb_strlen($str1);
        $len2 = mb_strlen($str2);

        if (abs($len1 - $len2) > 2) {
            return false;
        }

        // Si son palabras muy cortas (< 4 letras), requerir coincidencia exacta
        if ($len1 < 4 || $len2 < 4) {
            return false;
        }

        $maxDistance = ($len1 >= 7 || $len2 >= 7) ? 2 : 1;

        try {
            $distance = levenshtein($str1, $str2);
            $outDistance = $distance;
            return $distance <= $maxDistance;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Ejecuta la búsqueda inteligente paginada con Fuzzy Search y Relevancia Ponderada.
     */
    public static function search(array $queryParams): array {
        $rawSearch = trim((string)($queryParams['search'] ?? ''));
        $page = max(1, (int)($queryParams['page'] ?? 1));
        $perPage = max(1, (int)($queryParams['per_page'] ?? 12));
        $categoryParam = trim((string)($queryParams['category'] ?? ''));

        // Si no hay parámetro de búsqueda, delegar la consulta estándar al modelo Product
        if ($rawSearch === '') {
            return Product::paginate($page, $perPage, $queryParams);
        }

        $queryData = self::processQuery($rawSearch);
        $searchTerms = $queryData['expanded_terms'];
        $rawNormalized = $queryData['normalized'];

        if (empty($searchTerms)) {
            return Product::paginate($page, $perPage, $queryParams);
        }

        $db = Database::getConnection();

        // 1. Obtener todos los productos activos (con su categoría e imagen) para evaluar score
        $whereSql = "p.active = 1";
        $sqlParams = [];

        if ($categoryParam !== '') {
            if (is_numeric($categoryParam)) {
                $whereSql .= " AND (p.category_id = ? OR p.category_id IN (SELECT id FROM categories WHERE parent_id = ?))";
                $sqlParams[] = (int)$categoryParam;
                $sqlParams[] = (int)$categoryParam;
            } else {
                $whereSql .= " AND (c.slug = ? OR p.category_id IN (SELECT child.id FROM categories child JOIN categories parent ON child.parent_id = parent.id WHERE parent.slug = ?))";
                $sqlParams[] = $categoryParam;
                $sqlParams[] = $categoryParam;
            }
        }

        $sql = "
            SELECT 
                p.id,
                p.category_id,
                c.name AS category_name,
                c.slug AS category_slug,
                p.code,
                p.name,
                p.slug,
                p.description,
                p.retail_price,
                p.wholesale_price,
                p.stock,
                p.created_at,
                (
                    SELECT url 
                    FROM product_images pi 
                    WHERE pi.product_id = p.id 
                    ORDER BY pi.is_primary DESC, pi.sort_order ASC, pi.id ASC 
                    LIMIT 1
                ) AS primary_image,
                COALESCE((
                    SELECT SUM(oi.quantity) 
                    FROM order_items oi 
                    WHERE oi.product_id = p.id 
                       OR (p.code IS NOT NULL AND p.code != '' AND oi.product_code = p.code)
                ), 0) AS total_sold,
                COALESCE(p.views_count, 0) AS views_count
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE {$whereSql}
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute($sqlParams);
        $allProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $scoredProducts = [];
        $catalogWordsSet = [];

        // 2. Evaluador de Relevancia y Fuzzy Matching
        foreach ($allProducts as $product) {
            $score = 0;
            $nameNorm = self::normalizeText($product['name'] ?? '');
            $codeNorm = self::normalizeText($product['code'] ?? '');
            $catNorm = self::normalizeText($product['category_name'] ?? '');
            $descNorm = self::normalizeText($product['description'] ?? '');

            // Coleccionar palabras del catálogo para sugerencias "¿Quisiste decir...?"
            foreach (explode(' ', $nameNorm . ' ' . $catNorm) as $w) {
                if (mb_strlen($w) >= 4) {
                    $catalogWordsSet[$w] = true;
                }
            }

            // A) Coincidencia EXACTA o PARCIAL por SKU/Código (Máximo Peso)
            if ($codeNorm !== '') {
                if ($codeNorm === $rawNormalized) {
                    $score += 300;
                } elseif (str_contains($codeNorm, $rawNormalized) || str_contains($rawNormalized, $codeNorm)) {
                    $score += 150;
                }
            }

            // B) Coincidencia de la frase completa normalizada en el título
            if (str_contains($nameNorm, $rawNormalized)) {
                $score += 120;
            }

            // C) Evaluación por términos individuales y fuzzy matching
            $nameTokens = explode(' ', $nameNorm);
            $catTokens = explode(' ', $catNorm);

            foreach ($searchTerms as $term) {
                $termLen = mb_strlen($term);
                if ($termLen < 2) continue;

                // Match en Título
                foreach ($nameTokens as $nToken) {
                    if ($nToken === $term) {
                        $score += 50;
                    } elseif (str_contains($nToken, $term)) {
                        $score += 30;
                    } else {
                        $dist = 0;
                        if (self::isFuzzyMatch($term, $nToken, $dist)) {
                            $score += (30 - ($dist * 10)); // 20 para dist 1, 10 para dist 2
                        }
                    }
                }

                // Match en Categoría
                foreach ($catTokens as $cToken) {
                    if ($cToken === $term) {
                        $score += 40;
                    } elseif (self::isFuzzyMatch($term, $cToken)) {
                        $score += 20;
                    }
                }

                // Match en Descripción
                if ($descNorm !== '' && str_contains($descNorm, $term)) {
                    $score += 15;
                }
            }

            if ($score > 0) {
                $product['retail_price'] = (float)$product['retail_price'];
                $product['wholesale_price'] = isset($product['wholesale_price']) ? (float)$product['wholesale_price'] : null;
                $product['stock'] = (int)$product['stock'];
                if (empty($product['primary_image'])) {
                    $product['primary_image'] = '/assets/uploads/products/placeholder.jpg';
                }

                $product['_search_score'] = $score;
                $scoredProducts[] = $product;
            }
        }

        // 3. Si hay productos encontrados con Score > 0, ordenar y paginar
        if (!empty($scoredProducts)) {
            usort($scoredProducts, function ($a, $b) {
                if ($a['_search_score'] !== $b['_search_score']) {
                    return $b['_search_score'] <=> $a['_search_score'];
                }
                if ($a['total_sold'] !== $b['total_sold']) {
                    return $b['total_sold'] <=> $a['total_sold'];
                }
                return $b['views_count'] <=> $a['views_count'];
            });

            $total = count($scoredProducts);
            $totalPages = (int)ceil($total / $perPage);
            $offset = ($page - 1) * $perPage;
            $pagedItems = array_slice($scoredProducts, $offset, $perPage);

            return [
                'items'       => $pagedItems,
                'total'       => $total,
                'page'        => $page,
                'per_page'    => $perPage,
                'total_pages' => $totalPages,
                'did_you_mean' => null,
                'is_fallback' => false
            ];
        }

        // 4. FALLBACK "SIN RESULTADOS": Calcular "¿Quisiste decir...?" y obtener Productos Más Vendidos
        $didYouMean = self::calculateDidYouMean($rawNormalized, array_keys($catalogWordsSet));
        $topSellers = Product::getTopBestSellers(8);

        return [
            'items'          => [],
            'recommended'    => $topSellers,
            'total'          => 0,
            'page'           => 1,
            'per_page'       => $perPage,
            'total_pages'    => 1,
            'did_you_mean'   => $didYouMean,
            'search_query'   => $rawSearch,
            'is_fallback'    => true
        ];
    }

    /**
     * Sugerencias de Autocompletado Ultrarrápidas para el input de búsqueda.
     */
    public static function getSuggestions(string $rawQuery, int $limit = 6): array {
        $cleanQuery = trim($rawQuery);
        if ($cleanQuery === '' || mb_strlen($cleanQuery) < 2) {
            return [];
        }

        $result = self::search([
            'search' => $cleanQuery,
            'page' => 1,
            'per_page' => $limit
        ]);

        $items = $result['items'] ?? [];
        $suggestions = [];

        foreach ($items as $item) {
            $suggestions[] = [
                'id'            => $item['id'],
                'name'          => $item['name'],
                'code'          => $item['code'] ?? '',
                'slug'          => $item['slug'],
                'category_name' => $item['category_name'] ?? '',
                'retail_price'  => (float)$item['retail_price'],
                'primary_image' => $item['primary_image'] ?? '/assets/uploads/products/placeholder.jpg'
            ];
        }

        return $suggestions;
    }

    /**
     * Calcula la palabra sugerida más cercana ("¿Quisiste decir...?").
     */
    private static function calculateDidYouMean(string $rawQuery, array $catalogWords): ?string {
        if (empty($catalogWords) || mb_strlen($rawQuery) < 3) {
            return null;
        }

        $queryTokens = explode(' ', $rawQuery);
        $correctedTokens = [];
        $hasCorrection = false;

        foreach ($queryTokens as $token) {
            $strToken = (string)$token;
            if (mb_strlen($strToken) < 3) {
                $correctedTokens[] = $strToken;
                continue;
            }

            $bestMatch = $strToken;
            $minDist = 99;

            foreach ($catalogWords as $cWord) {
                $strCWord = (string)$cWord;
                if (abs(mb_strlen($strToken) - mb_strlen($strCWord)) > 2) {
                    continue;
                }

                try {
                    $dist = levenshtein($strToken, $strCWord);
                    if ($dist < $minDist && $dist <= 2) {
                        $minDist = $dist;
                        $bestMatch = $strCWord;
                    }
                } catch (\Throwable $e) {
                    continue;
                }
            }

            if ($bestMatch !== $strToken) {
                $hasCorrection = true;
                $correctedTokens[] = $bestMatch;
            } else {
                $correctedTokens[] = $strToken;
            }
        }

        return $hasCorrection ? implode(' ', $correctedTokens) : null;
    }
}
