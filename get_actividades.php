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

// *** Credenciales de la Base de Datos Railway (¡Ahora desde Variables de Entorno!) ***
$servername = getenv('DB_SERVER') ?: 'localhost'; // Valor por defecto para desarrollo local si no está seteada
$username_db = getenv('DB_USERNAME') ?: 'root';
$password_db = getenv('DB_PASSWORD') ?: '';
$dbname = getenv('DB_NAME') ?: 'default_db';
$port = getenv('DB_PORT') ?: 3306; // El puerto se lee como string, luego se usará como int

// Convertir el puerto a entero, ya que getenv() devuelve un string
$port = (int)$port;

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
