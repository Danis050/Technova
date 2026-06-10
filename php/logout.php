<?php
// SCRUM-HU24-03 | HU-24: Integración registrarAuditoria() — David Urias (U20240435)
session_start();
require_once 'auditoria_helper.php';
 
// Registrar logout antes de destruir la sesión (aún tenemos los datos)
if (isset($_SESSION['id_usuario'])) {
    registrarAuditoria('logout', 'usuario', $_SESSION['id_usuario'], 'Cierre de sesión');
}
 
session_destroy();
header('Location: ../login.html');
exit;
?>
 
