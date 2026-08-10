<?php
declare(strict_types=1);

namespace RepuestosDelLitoral\Controllers;

use RepuestosDelLitoral\Models\Product;
use RepuestosDelLitoral\Models\ProductImage;

class AdminProductController {

    public function list(array $filters): array {
        return Product::allForAdmin($filters);
    }

    public function get(int $id): ?array {
        return Product::findByIdForAdmin($id);
    }

    public function save(array $payload): int {
        $id = isset($payload['id']) ? (int)$payload['id'] : 0;
        
        $data = [
            'name'            => $payload['name'] ?? '',
            'slug'            => $payload['slug'] ?? '',
            'code'            => $payload['code'] ?? '',
            'description'     => $payload['description'] ?? '',
            'category_id'     => !empty($payload['category_id']) ? (int)$payload['category_id'] : null,
            'retail_price'    => (float)($payload['retail_price'] ?? 0),
            'wholesale_price' => isset($payload['wholesale_price']) && $payload['wholesale_price'] !== '' ? (float)$payload['wholesale_price'] : null,
            'stock'           => (int)($payload['stock'] ?? 0),
            'active'          => isset($payload['active']) ? (int)$payload['active'] : 1
        ];

        if ($data['name'] === '') {
            throw new \InvalidArgumentException('El nombre del producto es obligatorio.');
        }

        if ($id > 0) {
            Product::update($id, $data);
            return $id;
        } else {
            return Product::create($data);
        }
    }

    public function delete(int $id): void {
        Product::deletePermanently($id);
    }

    public function toggleActive(int $id, bool $active): void {
        Product::setActive($id, $active);
    }

    public function uploadImage(int $productId, array $file): array {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Error al subir el archivo.');
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            throw new \InvalidArgumentException('El archivo excede el límite de 5 MB.');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!array_key_exists($mime, $allowedMimes)) {
            throw new \InvalidArgumentException('Formato de imagen no permitido. Solo JPG, PNG y WEBP.');
        }

        $extension = $allowedMimes[$mime];
        $newFilename = uniqid('img_', true) . '.' . $extension;

        $baseUploadDir = dirname(__DIR__, 2) . '/public_html/assets/uploads/products/';
        $productDir = $baseUploadDir . $productId . '/';

        if (!is_dir($productDir)) {
            mkdir($productDir, 0755, true);
        }

        $destinationPath = $productDir . $newFilename;
        if (!move_uploaded_file($file['tmp_name'], $destinationPath)) {
            throw new \RuntimeException('Error al guardar el archivo en el servidor.');
        }

        $url = '/assets/uploads/products/' . $productId . '/' . $newFilename;
        
        $images = ProductImage::allByProduct($productId);
        $isPrimary = count($images) === 0;

        $imageId = ProductImage::create($productId, $url, $isPrimary);

        return [
            'id' => $imageId,
            'url' => $url,
            'is_primary' => $isPrimary
        ];
    }

    public function removeImage(int $imageId): void {
        ProductImage::delete($imageId);
    }

    public function setPrimaryImage(int $productId, int $imageId): void {
        ProductImage::setPrimary($productId, $imageId);
    }
    
    public function reorderImages(int $productId, array $orderedIds): void {
        ProductImage::reorder($productId, $orderedIds);
    }
}
