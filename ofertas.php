<?php
session_start();
require_once 'config.php';
checkLogin();

// Función para obtener el lunes de la semana actual
function getLunes()
{
    $fecha = new DateTime();
    $diaSemana = $fecha->format('N');
    if ($diaSemana == 7) { // Si es domingo
        $fecha->modify('-6 days');
    } else {
        $fecha->modify('-' . ($diaSemana - 1) . ' days');
    }
    return $fecha;
}

// Obtener el lunes actual
$fechaLunes = getLunes();

// Si se seleccionó una fecha específica
if (isset($_POST['fecha_seleccionada'])) {
    $fechaActual = new DateTime($_POST['fecha_seleccionada']);
} else {
    $fechaActual = clone $fechaLunes;
}

// Configurar opciones de cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $OFERTAS_URL . '?sucursal=' . $_SESSION['sucursal'] . '&dia=' . $fechaActual->format('N'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

// Ejecutar petición
$response = curl_exec($ch);

// Verificar errores
if (curl_errno($ch)) {
    echo 'Error: ' . curl_error($ch);
    exit;
}

curl_close($ch);

// Decodificar JSON
$ofertas = json_decode($response, true);

?>

<!DOCTYPE html>
<html>

<head>
    <title>Sistema de Ofertas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="icon" href="img/favicon.ico" />
    <style>
        .day-button {
            width: 100%;
            margin-bottom: 10px;
        }

        .day-button.active {
            background-color: #0056b3;
            border-color: #0056b3;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <div class="row">
            <h1><?php echo $_SESSION['nombre'] . ' ('  . $_SESSION['sucursal'] . ')'; ?></h1>
            <!-- Botones de días de la semana -->
            <div class="col-md-2">
                <h4>Días de la semana</h4>
                <?php
                $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
                $fechaDia = clone $fechaLunes;

                foreach ($diasSemana as $index => $dia) {
                    $active = $fechaActual->format('Y-m-d') === $fechaDia->format('Y-m-d') ? 'active' : '';
                ?>
                    <form method="POST">
                        <input type="hidden" name="fecha_seleccionada" value="<?php echo $fechaDia->format('Y-m-d'); ?>">
                        <button type="submit" class="btn btn-primary day-button <?php echo $active; ?>">
                            <?php echo $dia; ?><br>
                            <small><?php echo $fechaDia->format('d/m/Y'); ?></small>
                        </button>
                    </form>
                <?php
                    $fechaDia->modify('+1 day');
                }
                ?>
            </div>

            <!-- Tabla de ofertas -->
            <div class="col-md-10">
                <h3>Ofertas para <?php echo $fechaActual->format('d/m/Y'); ?></h3>

                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Descripción</th>
                            <th>Oferta</th>
                            <th>Kilos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $oscurecer = false;
                        foreach ($ofertas as $oferta) {
                            echo $oscurecer ? '<tr class="oscurecer">' : '' . "<tr>";
                            echo "<td id='{$oferta['id_productos']}' data-id='{$oferta['id_productos']}' data-kilos='{$oferta['kilos']}' >{$oferta['id_productos']}</td>";
                            echo "<td id='{$oferta['id_productos']}' data-id='{$oferta['id_productos']}' data-kilos='{$oferta['kilos']}' >{$oferta['descripcion']}</td>";
                            echo "<td id='{$oferta['id_productos']}' data-id='{$oferta['id_productos']}' data-kilos='{$oferta['kilos']}' >{$oferta['oferta']}</td>";
                            echo "<td id='{$oferta['id_productos']}' data-id='{$oferta['id_productos']}' data-kilos='{$oferta['kilos']}' class='cantidad'>{$oferta['kilos']}</td>";
                            echo "</tr>";

                            $oscurecer = !$oscurecer;
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div id="modalOfertas" class="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarModal()">&times;</span>
            <h3>Editar</h3>
            <input type="number" id="kilosInput" step="0.01">
            <input type="hidden" id="idRegistro">
            <button class="boton-guardar" id="saveChanges">Guardar</button>
        </div>
    </div>

    <script>
        const ofmodal = document.getElementById('modalOfertas');
        const kilosInput = document.getElementById('kilosInput');
        const idRegistroInput = document.getElementById('idRegistro');

        document.addEventListener('DOMContentLoaded', function() {

            // Si no hay fecha seleccionada, simular clic en el botón del lunes
            <?php if (!isset($_POST['fecha_seleccionada'])): ?>
                document.querySelector('.day-button').click();
            <?php endif; ?>


            // Agregar click listener a todas las celdas
            document.querySelectorAll('td').forEach(celda => {
                celda.addEventListener('click', function() {
                    // Obtener el ID de la celda
                    const id = celda.getAttribute('data-id');
                    const kilosCelda = document.querySelector(`td.cantidad[data-id='${id}']`);

                    if (kilosCelda) {
                        ofmodal.style.display = 'block';
                        kilosInput.value = kilosCelda.textContent;
                        idRegistroInput.value = id;
                        kilosInput.select();
                    }
                });
            });

            // Manejar guardado
            document.getElementById('saveChanges').addEventListener('click', function() {
                const id = document.getElementById('idRegistro').value;
                const kilos = document.getElementById('kilosInput').value;

                // Crear el objeto JSON con los datos
                const data = {
                    fecha: '<?php echo $fechaActual->format('Y-m-d'); ?>',
                    id_productos: id,
                    kilos: kilos
                };

                fetch('actualizar_kilos.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(data)
                    });                   
                    cerrarModal();
            });
            // Cerrar ofmodal al hacer click fuera
            window.addEventListener('click', function(event) {
                if (event.target === ofmodal) {
                    ofmodal.style.display = 'none';
                }
            });

            // Cerrar ofmodal al hacer click fuera
            window.addEventListener('click', function(event) {
                if (event.target === ofmodal) {
                    cerrarModal();
                }
            });

        });

        function cerrarModal() {
            ofmodal.style.display = 'none';
        }
    </script>
</body>

</html>