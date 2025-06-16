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
        return false; // Permite que otros manejadores o el sistema manejen el error si no es para nosotros
    }
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error interno en el servidor", "error" => "$errstr en $errfile:$errline"]);
    exit;
});

// *** Credenciales de la Base de Datos Railway (¡Asegúrate de que sean las correctas!) ***
// Usaremos las que te funcionaron para 'discipulos_de_cristo'
$servername = "shuttle.proxy.rlwy.net";
$username = "root"; // Tu usuario de base de datos en Railway
$password = "NXcdHmwfHhmucKqdmxPCYMLrRFDMiyNu"; // Tu contraseña de base de datos en Railway
$dbname = "discipulos_app"; // El nombre de tu base de datos en Railway
$port = 40395; // El puerto público que Railway te proporciona

// Intentar establecer la conexión a la base de datos
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Verificar si la conexión falló
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Error de conexión a la base de datos", "error" => $conn->connect_error]);
    exit;
}

// Consulta SQL para obtener todas las canciones, ordenadas por título
$sql = "SELECT id, title, artist, audio_url, thumbnail_url, duration_seconds FROM music_tracks ORDER BY title ASC";
$result = $conn->query($sql);

$musicTracks = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $musicTracks[] = $row;
    }
    echo json_encode(["success" => true, "message" => "Canciones obtenidas correctamente", "data" => $musicTracks]);
} else {
    echo json_encode(["success" => true, "message" => "No hay canciones disponibles.", "data" => []]);
}

$conn->close(); // Cerrar la conexión a la base de datos
?>
