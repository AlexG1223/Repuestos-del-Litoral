<?php
declare(strict_types=1);

namespace RepuestosDelLitoral\Models;

use RepuestosDelLitoral\Config\Database;
use PDO;
use Exception;

class ImportLog {
    /**
     * Inserta un nuevo registro en import_logs
     */
    public static function create(array $data): int {
        $db = Database::getConnection();
        
        $sql = "INSERT INTO import_logs (filename, products_imported, products_failed) 
                VALUES (:filename, :products_imported, :products_failed)";
                
        $stmt = $db->prepare($sql);
        
        $stmt->bindValue(':filename', $data['filename'], PDO::PARAM_STR);
        $stmt->bindValue(':products_imported', $data['products_imported'] ?? 0, PDO::PARAM_INT);
        $stmt->bindValue(':products_failed', $data['products_failed'] ?? 0, PDO::PARAM_INT);
        
        $stmt->execute();
        
        return (int)$db->lastInsertId();
    }
}
