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
        <li>📍 Dirección: <a href="https://www.google.com/maps/place/Semiller%C3%ADa+My.Vi.Da/@-33.5279796,-58.248913,13.57z/data=!4m10!1m2!2m1!1sAsencio+1930,+Dolores,+Soriano,+Uruguay!3m6!1s0x95a5291884f9ab0f:0x6aa8bf331873e2d1!8m2!3d-33.5352914!4d-58.2143246!15sCidBc2VuY2lvIDE5MzAsIERvbG9yZXMsIFNvcmlhbm8sIFVydWd1YXlaJiIkYXNlbmNpbyAxOTMwIGRvbG9yZXMgc29yaWFubyB1cnVndWF5kgEOaGFyZHdhcmVfc3RvcmXgAQA!16s%2Fg%2F11sg5q09v5?hl=en-US&entry=ttu&g_ep=EgoyMDI2MDgxMi4wIKXMDSoASAFQAw%3D%3D" target="_blank" rel="noopener noreferrer" style="color: inherit; text-decoration: underline;">Asencio 1930, Dolores, Soriano</a></li>
        <li>📞 Teléfono / WhatsApp: <a href="https://wa.me/59899655283" target="_blank" rel="noopener">099 655 283</a>
        </li>
        <li>💬 Atencion inmediata vía WhatsApp</li>
      </ul>
    </div>
  </div>

</footer><?php
require_once __DIR__ . '/../../private/config/database.php';
require_once __DIR__ . '/../../private/models/Setting.php';
$minOrderAmount = \RepuestosDelLitoral\Models\Setting::get('min_order_amount', '0');
?>
<script>
  window.APP_CONFIG = {
    minOrderAmount: <?= json_encode((int)$minOrderAmount) ?>
  };
</script>
