<footer class="site-footer">
  <div class="footer-container">
    <div class="footer-col">
      <h3>Repuestos del Litoral</h3>
      <p>Venta minorista y mayorista de repuestos agrícolas, motosierras, desmalezadoras, herramientas de jardín y ferretería en Dolores, Soriano, Uruguay.</p>
    </div>

    <div class="footer-col">
      <h3>Contacto & Local</h3>
      <ul>
        <li>📍 Dirección: <a href="https://www.google.com/maps/place/Semiller%C3%ADa+My.Vi.Da/@-33.5279796,-58.248913,13.57z/data=!4m10!1m2!2m1!1sAsencio+1930,+Dolores,+Soriano,+Uruguay!3m6!1s0x95a5291884f9ab0f:0x6aa8bf331873e2d1!8m2!3d-33.5352914!4d-58.2143246" target="_blank" rel="noopener noreferrer" style="color: inherit; text-decoration: underline;">Asencio 1930, Dolores, Soriano, Uruguay</a></li>
        <li>📞 Teléfono: 4534 4109 | WhatsApp: <a href="https://wa.me/59899655283" target="_blank" rel="noopener">099 655 283</a></li>
        <li>🕒 Horarios: Lun a Vie 08:00-12:00 y 14:00-18:00 | Sáb 08:00-12:00</li>
        <li>💬 Atención comercial y envíos a todo el país</li>
      </ul>
    </div>

    <div class="footer-col">
      <h3>Categorías y Guías SEO</h3>
      <ul>
        <li><a href="/index.php?category=motosierras" style="color: inherit;">Motosierras y Repuestos Stihl / Husqvarna</a></li>
        <li><a href="/index.php?category=desmalezadoras" style="color: inherit;">Desmalezadoras, Discos y Carburadores</a></li>
        <li><a href="/motosierra-a-nafta.php" style="color: inherit;">Guía de Motosierras a Nafta</a></li>
        <li><a href="/faq.php" style="color: inherit;">Preguntas Frecuentes & Ferretería Dolores</a></li>
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
