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
