<?php
declare(strict_types=1);

namespace RepuestosDelLitoral\Services;

use Exception;

class ImportStagingService {
    private string $storageDir;

    public function __construct() {
        $this->storageDir = __DIR__ . '/../../private/logs/imports';
        if (!is_dir($this->storageDir)) {
            if (!mkdir($this->storageDir, 0777, true)) {
                throw new Exception("No se pudo crear el directorio de logs de importación.");
            }
        }
    }

    private function getFilePath(string $importId): string {
        // Validación básica del ID para evitar directory traversal
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $importId)) {
            throw new Exception("ID de importación inválido.");
        }
        return $this->storageDir . '/' . $importId . '.json';
    }

    public function save(string $importId, array $rows): void {
        $filePath = $this->getFilePath($importId);
        $result = file_put_contents($filePath, json_encode($rows, JSON_UNESCAPED_UNICODE));
        if ($result === false) {
            throw new Exception("No se pudo guardar el archivo temporal de importación.");
        }
    }

    public function getPage(string $importId, int $page, int $perPage = 50): array {
        $all = $this->getAll($importId);
        $total = count($all);
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        
        $items = array_slice($all, $offset, $perPage);
        
        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int)$totalPages
        ];
    }

    public function updateRow(string $importId, int $rowIndex, array $changes): void {
        $all = $this->getAll($importId);
        
        if (!isset($all[$rowIndex])) {
            throw new Exception("Fila no encontrada.");
        }
        
        $all[$rowIndex] = array_merge($all[$rowIndex], $changes);
        $this->save($importId, $all);
    }

    public function getAll(string $importId): array {
        $filePath = $this->getFilePath($importId);
        if (!file_exists($filePath)) {
            throw new Exception("No se encontró el archivo temporal de importación.");
        }
        
        $content = file_get_contents($filePath);
        $data = json_decode($content, true);
        
        if (!is_array($data)) {
            throw new Exception("Formato JSON inválido en el archivo temporal.");
        }
        
        return $data;
    }

    public function delete(string $importId): void {
        $filePath = $this->getFilePath($importId);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    
    /**
     * Limpia archivos JSON temporales de más de 24 horas para no acumular basura
     */
    public function cleanupOldImports(): void {
        $files = glob($this->storageDir . '/*.json');
        $now = time();
        foreach ($files as $file) {
            if (is_file($file)) {
                if ($now - filemtime($file) >= 86400) { // 24 horas
                    unlink($file);
                }
            }
        }
    }
}
