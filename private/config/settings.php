<?php
declare(strict_types=1);

namespace RepuestosDelLitoral\Config;

// Configurar zona horaria oficial de Uruguay (UTC-3)
date_default_timezone_set('America/Montevideo');

if (!defined('SITE_NAME')) {
    define('SITE_NAME', getenv('SITE_NAME') ?: 'Repuestos del Litoral');
}

if (!defined('WHATSAPP_NUMBER')) {
    define('WHATSAPP_NUMBER', getenv('WHATSAPP_NUMBER') ?: '59899655283');
}

if (!defined('SITE_URL')) {
    define('SITE_URL', getenv('SITE_URL') ?: '/public_html');
}

if (!defined('UPLOADS_PATH')) {
    define('UPLOADS_PATH', dirname(__DIR__, 2) . '/public_html/assets/uploads/products/');
}

if (!defined('UPLOADS_URL')) {
    define('UPLOADS_URL', SITE_URL . '/assets/uploads/products/');
}

if (!defined('ITEMS_PER_PAGE')) {
    define('ITEMS_PER_PAGE', 12);
}

// Configuración de Analítica y Tracking (GTM, GA4, Meta Pixel, Search Console)
if (!defined('GTM_CONTAINER_ID')) {
    define('GTM_CONTAINER_ID', getenv('GTM_CONTAINER_ID') ?: 'GTM-XXXXXXX');
}

if (!defined('GA4_MEASUREMENT_ID')) {
    define('GA4_MEASUREMENT_ID', getenv('GA4_MEASUREMENT_ID') ?: 'G-XXXXXXX');
}

if (!defined('META_PIXEL_ID')) {
    define('META_PIXEL_ID', getenv('META_PIXEL_ID') ?: 'XXXXXXXXXXXXXXX');
}

if (!defined('SEARCH_CONSOLE_VERIFICATION')) {
    define('SEARCH_CONSOLE_VERIFICATION', getenv('SEARCH_CONSOLE_VERIFICATION') ?: '');
}

