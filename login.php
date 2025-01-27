<?php
session_start();
require_once 'config.php';

// Función para registrar errores
function logError($message, $curl_error = '', $curl_errno = '')
{
    error_log("Login Error: $message | cURL Error: $curl_error | cURL Errno: $curl_errno");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['nombre'] ?? '';
    $password = $_POST['password'] ?? '';

    // Preparar los datos para la API
    $data = array(
        'username' => $username,
        'password' => $password
    );

    // Configurar la petición cURL
    $ch = curl_init();

    // Opciones básicas de cURL
    curl_setopt_array($ch, [
        CURLOPT_URL => $LOGIN_URL,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],

        // Opciones adicionales para resolver problemas comunes
        CURLOPT_SSL_VERIFYPEER => false, // Solo para desarrollo, no usar en producción
        CURLOPT_SSL_VERIFYHOST => false, // Solo para desarrollo, no usar en producción
        CURLOPT_TIMEOUT => 30,           // Timeout en segundos
        CURLOPT_CONNECTTIMEOUT => 10,    // Timeout de conexión
        CURLOPT_VERBOSE => true,         // Habilitar modo verbose para debugging
        CURLOPT_HEADER => true,          // Incluir headers en la respuesta
    ]);

    // Capturar output verbose
    $verbose = fopen('php://temp', 'w+');
    curl_setopt($ch, CURLOPT_STDERR, $verbose);

    // Ejecutar la petición
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_errno = curl_errno($ch);
    $curl_error = curl_error($ch);

    // Obtener información detallada de la petición
    $info = curl_getinfo($ch);

    if ($response === false) {
        // Error en la petición cURL
        $error = "Error de conexión: " . $curl_error;
        logError("cURL execution failed", $curl_error, $curl_errno);

        // Obtener información de debug
        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);
        error_log("Verbose information: " . $verboseLog);

        // Información de diagnóstico
        $debug_info = "
            Error Número: $curl_errno\n
            Error Mensaje: $curl_error\n
            URL: {$info['url']}\n
            Total Time: {$info['total_time']}\n
            Connect Time: {$info['connect_time']}\n
            DNS Time: {$info['namelookup_time']}\n
        ";
        error_log("Debug information: " . $debug_info);
    } else {
        // Separar headers y body
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
                
        if ($http_code == 200) {
            $data = json_decode($body, true);
            $_SESSION['bearer'] = $data[0];            
            $_SESSION['usuario'] = $data[1];
            $_SESSION['sucursal'] = $data[2];
            $_SESSION['nombre'] = $data[3];
            $_SESSION['nivel'] = $data[4];
            $_SESSION['semana'] = $data[5];
            
            header("Location: ofertas.php");
            exit();
           
        } else {
            $error = "Error de autenticación (Código: $http_code)";
            logError("HTTP error", "HTTP Code: $http_code");
        }
    }

    curl_close($ch);
    fclose($verbose);
    
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="icon" href="img/favicon.ico" />
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Login</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label>Usuario:</label>
                                <input type="text" name="nombre" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Contraseña:</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Ingresar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>