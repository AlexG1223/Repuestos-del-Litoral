<?php
/**
 * Inclusión compartida para el <head> de la aplicación.
 * Inicializa dataLayer, inyecta el snippet asíncrono de Google Tag Manager (GTM)
 * y la meta etiqueta de verificación de Google Search Console si está configurada.
 */
if (!defined('GTM_CONTAINER_ID')) {
    if (file_exists(__DIR__ . '/../../private/config/settings.php')) {
        require_once __DIR__ . '/../../private/config/settings.php';
    }
}
$gtmContainerId = defined('GTM_CONTAINER_ID') ? GTM_CONTAINER_ID : 'GTM-XXXXXXX';
$searchConsoleVerification = defined('SEARCH_CONSOLE_VERIFICATION') ? SEARCH_CONSOLE_VERIFICATION : '';
?>
<!-- Google Search Console Verification Tag -->
<?php if (!empty($searchConsoleVerification)): ?>
<meta name="google-site-verification" content="<?php echo htmlspecialchars($searchConsoleVerification, ENT_QUOTES, 'UTF-8'); ?>">
<?php endif; ?>

<!-- Google Tag Manager -->
<script>
window.dataLayer = window.dataLayer || [];
(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','<?php echo htmlspecialchars($gtmContainerId, ENT_QUOTES, 'UTF-8'); ?>');
</script>
<!-- End Google Tag Manager -->
