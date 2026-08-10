<?php
declare(strict_types=1);

namespace RepuestosDelLitoral\Services;

require_once __DIR__ . '/../../vendor/autoload.php';

use Smalot\PdfParser\Parser;
use Exception;

class PdfImportService {
    
    /**
     * Extrae todo el texto de un PDF.
     */
    public function extractRawText(string $filePath): string {
        if (!file_exists($filePath)) {
            throw new Exception("El archivo PDF no existe.");
        }
        
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($filePath);
            return $pdf->getText();
        } catch (Exception $e) {
            throw new Exception("Error al parsear el PDF: " . $e->getMessage());
        }
    }
    
    /**
     * Intenta extraer imágenes del PDF (Best-effort).
     * Devuelve un array con datos de imágenes (base64 o paths).
     */
    public function attemptImageExtraction(string $filePath): array {
        // Nota: smalot/pdfparser tiene un soporte limitado para imágenes complejas.
        // Se implementa como un array vacío de fallback para no romper el flujo
        // si la extracción falla. El admin subirá las fotos después si es necesario.
        return [];
    }
}
