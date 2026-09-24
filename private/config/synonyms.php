<?php
declare(strict_types=1);

/**
 * Diccionario de Sinónimos, Equivalencias y Correcciones Tipográficas.
 * 
 * Permite definir variaciones de búsqueda de clientes para relacionar
 * términos mal escritos, marcas, sinónimos y plurales con sus términos canónicos en el catálogo.
 */

return [
    // Marcas y variaciones de escritura comunes
    'stihl' => ['stil', 'sthil', 'shtil', 'stlih', 'stihll'],
    'husqvarna' => ['husvarna', 'husquarna', 'hsq', 'huswarna', 'husvarma'],
    
    // Categorías y máquinas principales
    'motosierra' => ['moto sierra', 'motocierra', 'motosiera', 'motoserra', 'moto-sierra', 'motosierras'],
    'desmalezadora' => ['desmalesadora', 'desmalezadoras', 'desbrozadora', 'desbrozadoras', 'bordeadora', 'bordeadoras', 'orilladora', 'orilladoras', 'motoguadaña', 'motoguadañas'],
    'cortacesped' => ['corta cesped', 'cortacespedes', 'cortadora de cesped', 'podadora'],
    'cortasetos' => ['corta setos', 'cortaseto', 'cerustico'],
    'sopladora' => ['sopla dora', 'soplador', 'sopladoras'],
    'fumigadora' => ['pulverizadora', 'fumigadoras'],

    // Componentes, repuestos y consumibles
    'carburador' => ['carburadores', 'carbúrador', 'carburado', 'carburadoes'],
    'cadena' => ['cadenas', 'catenas'],
    'espada' => ['espadas', 'barra', 'barras', 'guia', 'guias'],
    'carretel' => ['carreteles', 'cabezal', 'cabezales', 'porta tanza', 'portatanza', 'carretel tanza'],
    'tanza' => ['tanzas', 'hilo', 'hilos', 'nylon', 'hilo nylon'],
    'buja' => ['bujia', 'bujias', 'bujía', 'bujías'],
    'filtro' => ['filtros', 'filtro aire', 'filtro nafta', 'filtro combustible'],
    'aceite' => ['aceites', 'lubricante', 'lubricantes', '2t', 'mezcla'],
    'piston' => ['pistones', 'piston completo', 'pistón'],
    'cilindro' => ['cilindros', 'conjunto cilindro', 'kit cilindro'],
    'embrague' => ['embragues', 'clutch'],
    'arranque' => ['arrancador', 'polea arranque', 'tirador', 'retráctil', 'retractil'],
    'resorte' => ['resortes', 'muelle'],

    // Medidas y especificaciones habituales
    '3/8' => ['38', '3/8p', 'paso 38', '38p'],
    '1/4' => ['14', 'paso 14', '1/4p'],
    '325' => ['.325', 'paso 325', '0.325'],
    '404' => ['.404', 'paso 404', '0.404']
];
