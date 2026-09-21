<?php
declare(strict_types=1);

header('Content-Type: application/xml; charset=utf-8');

require_once __DIR__ . '/../private/config/database.php';
require_once __DIR__ . '/../private/config/settings.php';
require_once __DIR__ . '/../private/models/Category.php';
require_once __DIR__ . '/../private/models/Product.php';
require_once __DIR__ . '/../private/services/SeoService.php';

use RepuestosDelLitoral\Services\SeoService;
use RepuestosDelLitoral\Models\Product;

$baseUrl = rtrim(SeoService::getBaseUrl(), '/');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">
  <channel>
    <title>Repuestos del Litoral - Catálogo de Productos</title>
    <link><?= htmlspecialchars($baseUrl) ?></link>
    <description>Repuestos de maquinaria agrícola e industrial, motosierras, desmalezadoras, motores 4T, generadores y herramientas de jardín en Dolores, Soriano, Uruguay.</description>
<?php
try {
    $productsData = Product::paginate(1, 2000, []);
    $products = $productsData['items'] ?? [];
    foreach ($products as $prod) {
        if (!empty($prod['slug']) && !empty($prod['name'])) {
            $img = '';
            if (!empty($prod['primary_image']) && !str_contains($prod['primary_image'], 'placeholder.jpg')) {
                $img = $prod['primary_image'];
            } elseif (!empty($prod['images']) && is_array($prod['images'])) {
                $firstImg = $prod['images'][0]['url'] ?? '';
                if (!empty($firstImg) && !str_contains($firstImg, 'placeholder.jpg')) {
                    $img = $firstImg;
                }
            }

            // Omitir productos sin imagen real (Google Merchant Center rechaza imágenes placeholder o vacías)
            if (empty($img)) {
                continue;
            }

            $imageUrl = str_starts_with($img, 'http') ? $img : $baseUrl . $img;
            $prodUrl = $baseUrl . '/producto/' . rawurlencode($prod['slug']);
            $price = number_format((float)($prod['display_price'] ?? $prod['retail_price'] ?? 0), 2, '.', '');
            $stock = (int)($prod['stock'] ?? 0);
            $availability = $stock > 0 ? 'in_stock' : 'out_of_stock';
            $rawDesc = !empty($prod['description']) ? strip_tags($prod['description']) : ($prod['name'] . ' disponible en Repuestos del Litoral, Dolores, Soriano, Uruguay.');

            echo "    <item>\n";
            echo "      <g:id>" . htmlspecialchars((string)($prod['id'] ?? $prod['code'] ?? $prod['slug'])) . "</g:id>\n";
            echo "      <g:title>" . htmlspecialchars($prod['name']) . "</g:title>\n";
            echo "      <g:description>" . htmlspecialchars(mb_substr($rawDesc, 0, 500)) . "</g:description>\n";
            echo "      <g:link>" . htmlspecialchars($prodUrl) . "</g:link>\n";
            echo "      <g:image_link>" . htmlspecialchars($imageUrl) . "</g:image_link>\n";
            echo "      <g:condition>new</g:condition>\n";
            echo "      <g:availability>" . $availability . "</g:availability>\n";
            echo "      <g:price>" . $price . " UYU</g:price>\n";
            if (!empty($prod['category_name'])) {
                echo "      <g:product_type>" . htmlspecialchars($prod['category_name']) . "</g:product_type>\n";
            }
            echo "      <g:brand>Repuestos del Litoral</g:brand>\n";
            echo "    </item>\n";
        }
    }
} catch (\Throwable $e) {
    // Ignorar error de BD
}
?>
  </channel>
</rss>
