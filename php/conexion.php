<?php
// ============================================================
// CONEXIÓN A BASE DE DATOS - TechNova
// ============================================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // Cambia por tu usuario MySQL
define('DB_PASS', '');            // Cambia por tu contraseña MySQL
define('DB_NAME', 'technova_db');

function getConexion() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, 3307);
    $conn->set_charset("utf8mb4");

    if ($conn->connect_error) {
        die(json_encode([
            'error' => true,
            'mensaje' => 'Error de conexión: ' . $conn->connect_error
        ]));
    }
    return $conn;
}
?>
