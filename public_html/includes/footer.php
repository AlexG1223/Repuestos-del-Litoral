<footer class="site-footer">
  <div class="footer-container">
    <div class="footer-col">
      <h3>Repuestos del Litoral</h3>
      <p>Venta minorista y mayorista de repuestos, motosierras, desmalezadoras, aceites y accesorios. Calidad y servicio
        técnico garantizado.</p>
    </div>

    <div class="footer-col">
      <h3>Contacto & Local</h3>
      <ul>
        <li>📍 Dirección: Asencio 1930, Dolores, Soriano</li>
        <li>📞 Teléfono / WhatsApp: <a href="https://wa.me/59892492756" target="_blank" rel="noopener">092 492 756</a>
        </li>
        <li>💬 Atencion inmediata vía WhatsApp</li>
      </ul>
    </div>
  </div>

</footer><?php
require_once __DIR__ . '/../../private/config/database.php';
require_once __DIR__ . '/../../private/models/Setting.php';
$minOrderAmount = \RepuestosDelLitoral\Models\Setting::get('min_order_amount', '2000');
?>
<script>
  window.APP_CONFIG = {
    minOrderAmount: <?= json_encode((int)$minOrderAmount) ?>
  };
</script>
