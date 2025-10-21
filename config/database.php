<?php
// Alias para compatibilidad: algunos archivos requieren 'config/database.php' mientras
// que el archivo real en este proyecto está nombrado 'dabase.php'.
require_once __DIR__ . '/dabase.php';

// Si la clase Database no existe después de requerir, lanzar un error claro.
if (!class_exists('Database')) {
    throw new RuntimeException('Archivo de configuración de base de datos cargado, pero no se encontró la clase Database. Verifique config/dabase.php');
}

// Reexportar la clase (no es necesario hacer nada más porque dabase.php define la clase).
?>
