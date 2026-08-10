<?php
require_once __DIR__ . '/private/config/database.php';

try {
    // No DB connection needed

    
    $txtPath = __DIR__ . '/catalogo.txt';
    if (!file_exists($txtPath)) {
        die("No se encontro el archivo catalogo.txt. Por favor, pega el texto del PDF en este archivo.\n");
    }

    $pdfContent = file_get_contents($txtPath);

    // Extraer solo la parte del PDF
    $startStr = "==Start of PDF==";
    $endStr = "==End of PDF==";
    $start = strpos($pdfContent, $startStr);
    $end = strpos($pdfContent, $endStr);
    
    if ($start !== false && $end !== false) {
        $pdfContent = substr($pdfContent, $start + strlen($startStr), $end - $start - strlen($startStr));
    }

    // Limpiar lineas basura
    $pdfContent = preg_replace('/==Screenshot for page \d+==/', '', $pdfContent);
    $pdfContent = preg_replace('/==Start of OCR for page \d+==/', '', $pdfContent);
    $pdfContent = preg_replace('/==End of OCR for page \d+==/', '', $pdfContent);
    $pdfContent = str_replace("PIEZAS DE CALIDAD CEL: 092492756", "", $pdfContent);
    $pdfContent = str_replace("LOS PRECIOS NO INCLUYEN IVA Y PUEDEN VARIAR SIN PREVIO AVISO", "", $pdfContent);

    // Separar en lineas y limpiar vacias
    $rawLines = explode("\n", $pdfContent);
    $cleanLines = [];
    foreach($rawLines as $l) {
        $l = trim($l);
        if ($l !== '') {
            $cleanLines[] = $l;
        }
    }

    echo "Lineas limpias a procesar: " . count($cleanLines) . "\n";

    $products = [];
    $currentProduct = null;

    // Un patron general para identificar si la linea tiene el precio al final: ej. "$257" o "$ 257"
    for ($i = 0; $i < count($cleanLines); $i++) {
        $line = $cleanLines[$i];
        
        // Si la linea es un codigo corto (letras y numeros, sin espacios o pocos) y la siguiente no tiene precio inmediatamente
        // O usamos acumulacion:
        if ($currentProduct === null) {
            $currentProduct = ['code' => '', 'name' => '', 'price' => 0];
        }

        // Buscar si hay un precio al final de la linea
        if (preg_match('/\$?\s*(\d+(?:\.\d+)?)\s*$/', $line, $matches) || preg_match('/\$?\s*(\d+)\s*c\/u$/i', $line, $matches)) {
            // Tiene precio. Podria ser toda la linea "CODIGO DESCRIPCION $PRECIO"
            $priceStr = $matches[0];
            $price = floatval($matches[1]);
            
            $textWithoutPrice = trim(str_replace($priceStr, '', $line));
            
            if ($currentProduct['code'] === '') {
                // Si esta vacio, y la linea tiene precio, la linea tiene todo
                $parts = explode(' ', $textWithoutPrice, 2);
                if (count($parts) > 1) {
                    $currentProduct['code'] = $parts[0];
                    $currentProduct['name'] = $parts[1];
                } else {
                    $currentProduct['name'] = $textWithoutPrice; // fallback
                }
            } else {
                // Ya teniamos codigo, agregamos al nombre
                $currentProduct['name'] .= ' ' . $textWithoutPrice;
            }
            
            $currentProduct['price'] = $price;
            
            // Limpiar y guardar
            $currentProduct['name'] = trim($currentProduct['name']);
            if (!empty($currentProduct['name']) && $currentProduct['price'] > 0) {
                // Convertir USD a UYU o dejarlo asi? El prompt no dice, asumimos pesos uruguayos o la moneda base
                // Verificamos si code y name existen
                $products[] = $currentProduct;
            }
            
            $currentProduct = null;
        } else {
            // No tiene precio. Es parte del codigo o del nombre
            if ($currentProduct['code'] === '') {
                // Asumimos que la primera linea es el codigo si es corta y no tiene espacios
                if (strlen($line) <= 10 && strpos($line, ' ') === false) {
                    $currentProduct['code'] = $line;
                } else {
                    // Es nombre sin codigo claro
                    $parts = explode(' ', $line, 2);
                    if (count($parts) > 1 && strlen($parts[0]) <= 10 && preg_match('/^[A-Z0-9]+$/i', $parts[0])) {
                        $currentProduct['code'] = $parts[0];
                        $currentProduct['name'] = $parts[1];
                    } else {
                        $currentProduct['name'] = $line;
                    }
                }
            } else {
                $currentProduct['name'] .= ' ' . $line;
            }
        }
    }

    echo "Productos detectados: " . count($products) . "\n";

    $sqlFile = __DIR__ . '/public_html/assets/catalogo_generado.sql';
    $sqlContent = "INSERT INTO products (name, description, price, sku, category_id, stock, active) VALUES\n";

    $values = [];
    foreach ($products as $p) {
        $name = addslashes($p['name']);
        $finalPrice = $p['price'];
        $code = !empty($p['code']) ? addslashes($p['code']) : 'UNK-'.uniqid();
        $values[] = "('{$name}', '{$name}', {$finalPrice}, '{$code}', 1, 100, 1)";
    }

    $sqlContent .= implode(",\n", $values) . "\nON DUPLICATE KEY UPDATE name=VALUES(name), price=VALUES(price);";

    file_put_contents($sqlFile, $sqlContent);
    echo "¡Exito! Creado el archivo catalogo_generado.sql en assets con " . count($products) . " productos.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
