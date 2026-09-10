<?php
declare(strict_types=1);

header('Content-Type: application/xml; charset=utf-8');

require_once __DIR__ . '/../private/config/database.php';
require_once __DIR__ . '/../private/config/settings.php';
require_once __DIR__ . '/../private/models/Category.php';
require_once __DIR__ . '/../private/models/Product.php';
require_once __DIR__ . '/../private/services/SeoService.php';

use RepuestosDelLitoral\Services\SeoService;
use RepuestosDelLitoral\Models\Category;
use RepuestosDelLitoral\Models\Product;

$baseUrl = rtrim(SeoService::getBaseUrl(), '/');
$currentDate = date('Y-m-d');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <!-- Páginas Estáticas -->
  <url>
    <loc><?= htmlspecialchars($baseUrl . '/') ?></loc>
    <lastmod><?= $currentDate ?></lastmod>
    <changefreq>daily</changefreq>
    <priority>1.0</priority>
  </url>
  <url>
    <loc><?= htmlspecialchars($baseUrl . '/inicio.php') ?></loc>
    <lastmod><?= $currentDate ?></lastmod>
    <changefreq>daily</changefreq>
    <priority>0.9</priority>
  </url>
  <url>
    <loc><?= htmlspecialchars($baseUrl . '/index.php') ?></loc>
    <lastmod><?= $currentDate ?></lastmod>
    <changefreq>daily</changefreq>
    <priority>0.9</priority>
  </url>
  <url>
    <loc><?= htmlspecialchars($baseUrl . '/faq.php') ?></loc>
    <lastmod><?= $currentDate ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.8</priority>
  </url>
  <url>
    <loc><?= htmlspecialchars($baseUrl . '/motosierra-a-nafta.php') ?></loc>
    <lastmod><?= $currentDate ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.9</priority>
  </url>

<?php
// Categorías
try {
    $categories = Category::all();
    foreach ($categories as $cat) {
        if (!empty($cat['slug'])) {
            $catUrl = $baseUrl . '/index.php?category=' . rawurlencode($cat['slug']);
            echo "  <url>\n";
            echo "    <loc>" . htmlspecialchars($catUrl) . "</loc>\n";
            echo "    <changefreq>weekly</changefreq>\n";
            echo "    <priority>0.8</priority>\n";
            echo "  </url>\n";
        }
    }
} catch (\Throwable $e) {
    // Ignorar error si BD no accesible en generación estática
}

// Productos activos
try {
    $productsData = Product::paginate(1, 1000, []);
    $products = $productsData['items'] ?? [];
    foreach ($products as $prod) {
        if (!empty($prod['slug'])) {
            $prodUrl = $baseUrl . '/producto/' . rawurlencode($prod['slug']);
            $lastmod = !empty($prod['created_at']) ? substr($prod['created_at'], 0, 10) : $currentDate;
            echo "  <url>\n";
            echo "    <loc>" . htmlspecialchars($prodUrl) . "</loc>\n";
            echo "    <lastmod>" . htmlspecialchars($lastmod) . "</lastmod>\n";
            echo "    <changefreq>daily</changefreq>\n";
            echo "    <priority>0.8</priority>\n";
            echo "  </url>\n";
        }
    }
} catch (\Throwable $e) {
    // Ignorar
}
?>
</urlset>
