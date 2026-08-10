<?php
declare(strict_types=1);

namespace RepuestosDelLitoral\Controllers;

use RepuestosDelLitoral\Services\PdfImportService;
use RepuestosDelLitoral\Services\ImportStagingService;
use RepuestosDelLitoral\Models\Product;
use RepuestosDelLitoral\Models\ImportLog;
use Exception;

class AdminImportController {
    private PdfImportService $pdfService;
    private ImportStagingService $stagingService;
    private array $patternConfig;

    public function __construct() {
        $this->pdfService = new PdfImportService();
        $this->stagingService = new ImportStagingService();
        
        $configPath = __DIR__ . '/../config/import-pattern.php';
        if (file_exists($configPath)) {
            $this->patternConfig = require $configPath;
        } else {
            $this->patternConfig = [];
        }
    }

    public function parseUpload(array $file, array $options): array {
        // Limpieza de JSON viejos
        $this->stagingService->cleanupOldImports();

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Error al subir el archivo.");
        }
        
        if ($file['size'] > 20 * 1024 * 1024) { // 20 MB max
            throw new Exception("El archivo es demasiado grande (máximo 20 MB).");
        }

        $rawText = $this->pdfService->extractRawText($file['tmp_name']);
        
        $lines = explode("\n", $rawText);
        $rows = [];
        $pattern = $this->patternConfig['line_pattern'] ?? '';
        $skipLines = $this->patternConfig['skip_lines_containing'] ?? [];
        $decSep = $this->patternConfig['decimal_separator'] ?? ',';
        $thoSep = $this->patternConfig['thousands_separator'] ?? '.';
        
        $retailMargin = (float)($options['retail_margin'] ?? 0);
        $wholesaleMargin = isset($options['wholesale_margin']) && $options['wholesale_margin'] !== '' 
            ? (float)$options['wholesale_margin'] 
            : null;
        $categoryId = !empty($options['category_id']) ? (int)$options['category_id'] : null;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Skip headers/footers
            $skip = false;
            foreach ($skipLines as $skipWord) {
                if (stripos($line, $skipWord) !== false) {
                    $skip = true;
                    break;
                }
            }
            if ($skip) continue;

            if ($pattern && preg_match($pattern, $line, $matches)) {
                $code = trim($matches['code'] ?? '');
                $description = trim($matches['description'] ?? '');
                $rawPriceStr = trim($matches['price'] ?? '0');
                
                // Limpiar string de precio (sacar separador de miles, cambiar decimal por punto)
                if ($thoSep !== '') {
                    $rawPriceStr = str_replace($thoSep, '', $rawPriceStr);
                }
                $rawPriceStr = str_replace($decSep, '.', $rawPriceStr);
                $rawPrice = (float)$rawPriceStr;

                $retailPrice = round($rawPrice * (1 + $retailMargin / 100));
                $wholesalePrice = $wholesaleMargin !== null 
                    ? round($rawPrice * (1 + $wholesaleMargin / 100))
                    : null;

                // Ver si existe
                $existing = Product::findByCode($code);
                
                $rows[] = [
                    'code' => $code,
                    'name' => $description,
                    'description' => '',
                    'raw_price' => $rawPrice,
                    'retail_price' => $retailPrice,
                    'wholesale_price' => $wholesalePrice,
                    'category_id' => $categoryId,
                    'action' => $existing ? 'update' : 'create',
                    'existing_id' => $existing ? (int)$existing['id'] : null,
                    'excluded' => false
                ];
            }
        }

        $importId = uniqid('imp_', true);
        $this->stagingService->save($importId, $rows);

        // Previsualización cruda para debugging del patrón (primeras 50 líneas)
        $rawPreview = implode("\n", array_slice($lines, 0, 50));

        return [
            'importId' => $importId,
            'totalRows' => count($rows),
            'rawPreview' => $rawPreview
        ];
    }

    public function getPreview(string $importId, int $page, int $perPage = 50): array {
        return $this->stagingService->getPage($importId, $page, $perPage);
    }

    public function updateRow(string $importId, int $rowIndex, array $changes): void {
        $this->stagingService->updateRow($importId, $rowIndex, $changes);
    }

    public function confirm(string $importId, string $filename): array {
        $rows = $this->stagingService->getAll($importId);
        
        $importedCount = 0;
        $failedCount = 0;
        $updatedCount = 0;
        $createdCount = 0;

        foreach ($rows as $row) {
            if (!empty($row['excluded'])) {
                continue;
            }

            $data = [
                'category_id' => $row['category_id'],
                'code' => $row['code'],
                'name' => $row['name'],
                'description' => $row['description'] ?? '',
                'retail_price' => $row['retail_price'],
                'wholesale_price' => $row['wholesale_price'],
                'stock' => 0, // se cargaría a mano
                'active' => 1
            ];

            try {
                if ($row['action'] === 'update' && !empty($row['existing_id'])) {
                    // Update existente
                    Product::update((int)$row['existing_id'], $data);
                    $updatedCount++;
                } else {
                    // Create nuevo
                    Product::create($data);
                    $createdCount++;
                }
                $importedCount++;
            } catch (Exception $e) {
                $failedCount++;
            }
        }
        
        ImportLog::create([
            'filename' => $filename,
            'products_imported' => $importedCount,
            'products_failed' => $failedCount
        ]);

        $this->stagingService->delete($importId);

        return [
            'total_processed' => $importedCount,
            'created' => $createdCount,
            'updated' => $updatedCount,
            'failed' => $failedCount
        ];
    }
}
