<?php
// Permite que cualquier origen (tu app Flutter) acceda a este script
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST"); // Solo permitir peticiones POST
header("Access-Control-Max-Age: 3600"); // Cachear la pre-respuesta por 1 hora
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Manejador de excepciones para capturar cualquier error PHP no manejado
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

// Asegurarse de que la petición sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Método no permitido
    echo json_encode(["success" => false, "message" => "Método no permitido. Solo se aceptan peticiones POST."]);
    exit;
}

// *** Credenciales de la Base de Datos Railway ***
$servername = "shuttle.proxy.rlwy.net";
$username_db = "root"; // Renombrado para evitar conflicto con variables POST/JSON
$password_db = "NXcdHmwfHhmucKqdmxPCYMLrRFDMiyNu";
$dbname = "discipulos_de_cristo"; // ¡CAMBIADO! Nombre correcto de la base de datos
$port = 40395; // El puerto público que Railway te proporciona

// Conectar a la base de datos
$conn = new mysqli($servername, $username_db, $password_db, $dbname, $port);

// Verificar si la conexión falló
if ($conn->connect_error) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["success" => false, "message" => "Error de conexión a la base de datos", "error" => $conn->connect_error]);
    exit;
}

// Obtener los datos del cuerpo de la petición POST (JSON)
// --- ¡CAMBIO CLAVE AQUÍ! Ahora lee JSON ---
$data = json_decode(file_get_contents("php://input"));
// ----------------------------------------

// Validar que se recibieron todos los campos necesarios
if (!isset($data->nombre) || empty(trim($data->nombre)) ||
    !isset($data->apellidos) || empty(trim($data->apellidos)) ||
    !isset($data->documento) || empty(trim($data->documento)) ||
    !isset($data->direccion) || empty(trim($data->direccion)) ||
    !isset($data->telefono) || empty(trim($data->telefono)) ||
    !isset($data->genero) || empty(trim($data->genero)) ||
    !isset($data->correo) || empty(trim($data->correo)) ||
    !isset($data->contrasena) || empty(trim($data->contrasena))) {
    http_response_code(400); // Bad Request
    echo json_encode(["success" => false, "message" => "Por favor, completa todos los campos requeridos."]);
    exit;
}

// Limpiar y escapar los datos de entrada para prevenir inyecciones SQL
$nombre = $conn->real_escape_string(trim($data->nombre));
$apellidos = $conn->real_escape_string(trim($data->apellidos));
$documento = $conn->real_escape_string(trim($data->documento));
$direccion = $conn->real_escape_string(trim($data->direccion));
$telefono = $conn->real_escape_string(trim($data->telefono));
$genero = strtoupper(trim($data->genero)); // Asegurarse que el género sea M o F
$correo = $conn->real_escape_string(trim($data->correo));
$contrasena_plana = trim($data->contrasena);

// Validación de género (ahora que leemos de JSON)
if ($genero !== 'M' && $genero !== 'F') {
    http_response_code(400); // Bad Request
    echo json_encode(["success" => false, "message" => "Género inválido. Debe ser 'M' o 'F'."]);
    exit;
}

// Validación de correo
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400); // Bad Request
    echo json_encode(["success" => false, "message" => "Correo inválido. Por favor, introduce un correo válido."]);
    exit;
}

// --- ¡IMPORTANTE! HASHEAR LA CONTRASEÑA ANTES DE ALMACENARLA ---
$contrasena_hasheada = password_hash($contrasena_plana, PASSWORD_DEFAULT); // Usar PASSWORD_DEFAULT es más robusto
if ($contrasena_hasheada === false) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error al hashear la contraseña."]);
    exit;
}
// ---------------------------------------------------------------

// Verificar si el correo o documento ya existen (para evitar duplicados)
$checkSql = "SELECT id FROM usuarios_app WHERE correo = ? OR documento = ?";
$stmtCheck = $conn->prepare($checkSql);
if ($stmtCheck === false) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error al preparar la verificación de usuario/correo.", "error" => $conn->error]);
    exit;
}
$stmtCheck->bind_param("ss", $correo, $documento);
$stmtCheck->execute();
$checkResult = $stmtCheck->get_result();

if ($checkResult->num_rows > 0) {
    http_response_code(409); // Conflict
    echo json_encode(["success" => false, "message" => "El correo o documento ya están registrados."]);
    $stmtCheck->close();
    $conn->close();
    exit;
}
$stmtCheck->close();


// Prepara la consulta SQL para insertar el nuevo usuario en `usuarios_app`
$sql = "INSERT INTO usuarios_app (nombre, apellidos, documento, direccion, telefono, genero, correo, contrasena) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

// Usar prepared statements para mayor seguridad (previene inyección SQL)
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error al preparar la consulta SQL de inserción", "error" => $conn->error]);
    exit;
}

// "ssssssss" indica que todos los 8 parámetros son cadenas (strings)
$stmt->bind_param("ssssssss", $nombre, $apellidos, $documento, $direccion, $telefono, $genero, $correo, $contrasena_hasheada);

if ($stmt->execute()) {
    http_response_code(201); // Created
    echo json_encode(["success" => true, "message" => "Usuario registrado exitosamente."]);
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(["success" => false, "message" => "Error al registrar el usuario.", "error" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
