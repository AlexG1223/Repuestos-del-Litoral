<?php
declare(strict_types=1);

// Patrón de línea esperado, un producto por línea de texto extraído.
// AJUSTAR esta expresión regular después de ver el texto real del PDF
// (sección "Vista previa de texto crudo" en /admin/importar.php).
//
// Ejemplo de línea típica a cubrir: "COD1234  Cadena 3/8 72 eslabones  1250.00"
return [
    'line_pattern' => '/^(?<code>[A-Z0-9\-]+)\s{2,}(?<description>.+?)\s{2,}(?<price>[\d.,]+)\s*$/m',
    'decimal_separator' => ',',       // AJUSTAR según el PDF (',' o '.')
    'thousands_separator' => '.',     // AJUSTAR según el PDF
    'skip_lines_containing' => ['CATÁLOGO', 'Página', 'Total', 'Repuestos del Litoral', 'PAGINA'], // encabezados/pies a ignorar
];
