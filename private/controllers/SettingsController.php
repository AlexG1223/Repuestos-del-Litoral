<?php
declare(strict_types=1);

namespace RepuestosDelLitoral\Controllers;

use RepuestosDelLitoral\Models\Setting;
use RepuestosDelLitoral\Services\SessionService;

class SettingsController {
    public function getSettings(): array {
        return Setting::all();
    }

    public function updateSettings(array $data): array {
        if (!SessionService::isAdmin()) {
            throw new \Exception('No tienes permisos para realizar esta acción.');
        }

        foreach ($data as $key => $value) {
            // Validate key
            if (!is_string($key) || empty(trim($key))) continue;
            Setting::set(trim($key), trim((string)$value));
        }

        return ['success' => true];
    }
}
