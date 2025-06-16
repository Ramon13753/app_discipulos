<?php
// Permite que cualquier origen (tu app Flutter) acceda a este script
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Manejador de excepciones para capturar cualquier error PHP
set_exception_handler(function($e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Excepción en el servidor", "error" => $e->getMessage()]);
    exit;
});

// Manejador de errores para capturar advertencias y errores de PHP
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error interno en el servidor", "error" => "$errstr en $errfile:$errline"]);
    exit;
});

// *** Credenciales de la Base de Datos Railway ***
$servername = "shuttle.proxy.rlwy.net";
$username = "root";
$password = "NXcdHmwfHhmucKqdmxPCYMLrRFDMiyNu";
$dbname = "discipulos_de_cristo"; // Asegúrate que este sea el nombre correcto
$port = 40395;

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Error de conexión a la base de datos", "error" => $conn->connect_error]);
    exit;
}

// Obtener el límite de anuncios de la URL, si se proporciona
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20; // Por defecto, si no se especifica, se obtienen 20

// Asegurarse de que el límite sea un número positivo y razonable
if ($limit <= 0 || $limit > 100) { // Puedes ajustar el máximo según tus necesidades
    $limit = 20; // Si el límite es inválido, vuelve al valor por defecto
}

// Construir la consulta SQL con el límite
$sql = "SELECT id, title, description, content_url FROM announcements ORDER BY created_at DESC LIMIT $limit";
$result = $conn->query($sql);

$announcements = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
    echo json_encode(["success" => true, "message" => "Anuncios obtenidos correctamente", "data" => $announcements]);
} else {
    echo json_encode(["success" => true, "message" => "No hay anuncios disponibles.", "data" => []]);
}

$conn->close();
?>
