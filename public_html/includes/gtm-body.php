<?php
/**
 * Inclusión compartida para la apertura del <body>.
 * Inyecta el snippet <noscript> del contenedor de Google Tag Manager (GTM).
 */
if (!defined('GTM_CONTAINER_ID')) {
    if (file_exists(__DIR__ . '/../../private/config/settings.php')) {
        require_once __DIR__ . '/../../private/config/settings.php';
    }
}
$gtmContainerId = defined('GTM_CONTAINER_ID') ? GTM_CONTAINER_ID : 'GTM-XXXXXXX';
?>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo htmlspecialchars($gtmContainerId, ENT_QUOTES, 'UTF-8'); ?>"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
