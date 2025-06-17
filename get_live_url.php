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
$servername = getenv('DB_SERVER') ?: ''; // Valor por defecto para desarrollo local si no está seteada
$username_db = getenv('DB_USERNAME') ?: '';
$password_db = getenv('DB_PASSWORD') ?: '';
$dbname = getenv('DB_NAME') ?: '';
$port = getenv('DB_PORT') ?: 0; // El puerto se lee como string, luego se usará como int

// Convertir el puerto a entero, ya que getenv() devuelve un string
$port = (int)$port;

$conn = new mysqli($servername, $username_db, $password_db, $dbname, $port);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Conexión a la base de datos fallida", "error" => $conn->connect_error]);
    exit;
}

// Consulta para obtener el valor de 'culto_en_vivo_url'
$sql = "SELECT valor FROM configuracion_app WHERE clave = 'culto_en_vivo_url'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode(["success" => true, "url" => $row['valor']]);
} else {
    // Si la clave no existe (aunque ya la insertamos), devuelve URL vacía
    echo json_encode(["success" => true, "url" => "", "message" => "Clave 'culto_en_vivo_url' no encontrada."]);
}

$conn->close();
?>
