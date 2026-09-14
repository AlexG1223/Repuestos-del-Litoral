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
$ga4MeasurementId = defined('GA4_MEASUREMENT_ID') ? GA4_MEASUREMENT_ID : 'G-SKQP4XXET0';
$searchConsoleVerification = defined('SEARCH_CONSOLE_VERIFICATION') ? SEARCH_CONSOLE_VERIFICATION : '';
?>
<!-- Google Search Console Verification Tag -->
<?php if (!empty($searchConsoleVerification)): ?>
<meta name="google-site-verification" content="<?php echo htmlspecialchars($searchConsoleVerification, ENT_QUOTES, 'UTF-8'); ?>">
<?php endif; ?>

<!-- Google tag (gtag.js) - Google Analytics 4 -->
<?php if (!empty($ga4MeasurementId) && $ga4MeasurementId !== 'G-XXXXXXX'): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo htmlspecialchars($ga4MeasurementId, ENT_QUOTES, 'UTF-8'); ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', '<?php echo htmlspecialchars($ga4MeasurementId, ENT_QUOTES, 'UTF-8'); ?>');
</script>
<?php endif; ?>

<!-- Google Tag Manager -->
<?php if (!empty($gtmContainerId) && $gtmContainerId !== 'GTM-XXXXXXX'): ?>
<script>
window.dataLayer = window.dataLayer || [];
(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','<?php echo htmlspecialchars($gtmContainerId, ENT_QUOTES, 'UTF-8'); ?>');
</script>
<?php endif; ?>
<!-- End Google Tag Manager -->
