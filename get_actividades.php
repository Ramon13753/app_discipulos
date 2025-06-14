<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

set_exception_handler(function($e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Excepción", "error" => $e->getMessage()]);
    exit;
});

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error interno", "error" => "$errstr en $errfile:$errline"]);
    exit;
});

// *** ASEGÚRATE DE USAR LAS MISMAS CREDENCIALES DE BD QUE TE FUNCIONARON ***
$servername = "mysql-5v2t-production.up.railway.app";
$username = "root"; // Usuario de tu base de datos
$password = "NXcdHmwfHhmucKqdmxPCYMLrRFDMiyNu"; // Contraseña de tu base de datos
$dbname = "railway"; // Nombre de tu base de datos
$port = 3306; // Puerto de tu base de datos

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Conexión a la base de datos fallida", "error" => $conn->connect_error]);
    exit;
}

// Consulta para obtener todas las actividades, ordenadas por fecha y hora
$sql = "SELECT id, titulo, descripcion, fecha_actividad, hora_actividad, lugar FROM actividades ORDER BY fecha_actividad ASC, hora_actividad ASC";
$result = $conn->query($sql);

$actividades = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $actividades[] = $row;
    }
    echo json_encode(["success" => true, "message" => "Actividades obtenidas correctamente", "data" => $actividades]);
} else {
    echo json_encode(["success" => true, "message" => "No hay actividades registradas", "data" => []]);
}

$conn->close();
?>
