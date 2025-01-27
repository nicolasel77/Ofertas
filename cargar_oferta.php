<?php
session_start();
require_once 'config.php';
checkLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $sql = "INSERT INTO Ofertas (fecha, id_sucursal, id_producto, descripcion, 
                costo_original, costo_oferta, kilos, usuario_creacion) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $_POST['fecha'],
            $_POST['sucursal'],
            $_POST['producto'],
            sanitize($_POST['descripcion']),
            $_POST['costo_original'],
            $_POST['costo_oferta'],
            $_POST['kilos'],
            $_SESSION['user_id']
        ]);
        
        $mensaje = "Datos guardados correctamente";
    } catch(PDOException $e) {
        $error = "Error al guardar los datos: " . $e->getMessage();
    }
}

// Obtener lista de sucursales
$sucursales = $conn->query("SELECT id_sucursal, nombre FROM Sucursales")->fetchAll();
// Obtener lista de productos
$productos = $conn->query("SELECT id_producto, nombre FROM Productos")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cargar Oferta</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2>Cargar Nueva Oferta</h2>
        
        <?php if (isset($mensaje)): ?>
            <div class="alert alert-success"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" class="mt-3">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Fecha:</label>
                    <input type="date" name="fecha" class="form-control" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label>Sucursal:</label>
                    <select name="sucursal" class="form-control" required>
                        <?php foreach($sucursales as $sucursal): ?>
                            <option value="<?php echo $sucursal['id_sucursal']; ?>">
                                <?php echo $sucursal['nombre']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label>Producto:</label>
                    <select name="producto" class="form-control" required>
                        <?php foreach($productos as $producto): ?>
                            <option value="<?php echo $producto['id_producto']; ?>">
                                <?php echo $producto['nombre']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label>Descripción:</label>
                    <input type="text" name="descripcion" class="form-control" required>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label>Costo Original:</label>
                    <input type="number" step="0.01" name="costo_original" class="form-control" required>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label>Costo Oferta:</label>
                    <input type="number" step="0.01" name="costo_oferta" class="form-control" required>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label>Kilos:</label>
                    <input type="number" step="0.01" name="kilos" class="form-control" required>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Guardar Oferta</button>
            <a href="dashboard.php" class="btn btn-secondary">Volver</a>
        </form>
    </div>
</body>
</html>
