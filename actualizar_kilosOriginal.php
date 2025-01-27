<?php
require 'config.php';

// actualizar_kilos.php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

// Habilitar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Extraer los parámetros
$id_sucursal = $data['sucursal'];
$id_productos = $data['id_productos'];
$kilos = $data['kilos'];
$fecha = $data['fecha'];
$token = $data['token'];

// Preparar los datos para enviar a la API local
// $datos = [        
//     'id_sucursal' => $id_sucursal,
//     'id_productos' => $id_productos,
//     'kilos' => $kilos,
//     'fecha' => $fecha
// ];
$datos = [
    'fecha' => '2025-01-21',
    'id_sucursal' => 6005,
    'id_productos' => 100,
    'oferta' => 'No se che',
    'descripcion' => 'BOFE',
    'kilos' => 1
];

// Inicializar cURL para hacer la solicitud a la API local
$ch = curl_init($OFERTAS_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datos));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
]);

curl_setopt($ch, CURLOPT_VERBOSE, true);  // Habilitar modo verboso
$verboseLog = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verboseLog);

// Ejecutar la solicitud
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Verificar errores de cURL
if(curl_errno($ch)) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error de conexión',
        'detalles' => curl_error($ch)
    ]);
    curl_close($ch);
    exit;
}

// Obtener log de cURL para depuración
rewind($verboseLog);
$verboseLogContents = stream_get_contents($verboseLog);

curl_close($ch);

// Verificar el código de respuesta HTTP
if ($httpCode >= 200 && $httpCode < 300) {
    // Solicitud exitosa
    echo json_encode([
        'status' => 'success', 
        'message' => 'Kilos actualizados correctamente',
        'response' => json_decode($response, true),
        'curl_debug' => $verboseLogContents
    ]);
} else {
    // Error en la solicitud
    http_response_code($httpCode);
    echo json_encode([
        'status' => 'error', 
        'message' => 'Error al actualizar kilos',
        'response' => json_decode($response, true),
        'curl_debug' => $verboseLogContents,
        'http_code' => $httpCode
    ]);
}
?>