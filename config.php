<?php
//$API_URL = 'https://oficina.hopto.org:7056/api';
$API_URL = 'https://localhost:7056/api';
$LOGIN_URL = $API_URL . '/Auth/login';
$OFERTAS_URL = $API_URL . '/Ofertas';


// Función para verificar si el usuario está logueado
function checkLogin() {
    if (!isset($_SESSION['usuario'])) {
        header("Location: login.php");
        exit();
    }
}

// Función para sanitizar inputs
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}
