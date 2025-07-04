<?php
// Permite que cualquier origen (tu app Flutter) acceda a este script
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST"); // Permitir solo peticiones POST
header("Access-Control-Max-Age: 3600"); // Cachear la pre-respuesta por 1 hora
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// --- TEMPORAL PARA DEPURACIÓN: Mostrar todos los errores PHP ---
// ¡MANTEN ESTAS LÍNEAS ACTIVAS DURANTE ESTA PRUEBA!
// Esto ayuda a que los errores de PHP se impriman directamente, lo que puede corromper el JSON,
// pero a veces revela la causa subyacente.
error_reporting(E_ALL);
ini_set('display_errors', 1);
// -------------------------------------------------------------

// Manejador de excepciones para capturar cualquier error PHP no manejado
set_exception_handler(function($e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Excepción en el servidor", "error" => $e->getMessage()]);
    exit;
});

// Manejador de errores para capturar advertencias y errores de PHP
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    // Solo manejar errores que estén dentro del error_reporting actual
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

// *** Credenciales de la Base de Datos Railway (¡AHORA CON FALLBACKS DIRECTOS!) ***
// Si getenv() no funciona, usará los valores hardcodeados para la conexión.
// ESTO ES TEMPORAL PARA DEBUGGING DE LAS VARIABLES DE ENTORNO.
// LUEGO DEBES CAMBIAR LOS VALORES DE FALLBACK A Cadenas Vacías ('') o 0.
$servername = getenv('DB_SERVER') ?: '';
$username_db = getenv('DB_USERNAME') ?: '';
$password_db = getenv('DB_PASSWORD') ?: '';
$dbname = getenv('DB_NAME') ?: '';
$port = getenv('DB_PORT') ?: 0;

// Convertir el puerto a entero, ya que getenv() devuelve un string
$port = (int)$port;

// Conectar a la base de datos
// Asegúrate de usar $username_db y $password_db aquí
$conn = new mysqli($servername, $username_db, $password_db, $dbname, $port);

// Verificar si la conexión falló
if ($conn->connect_error) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["success" => false, "message" => "Error de conexión a la base de datos", "error" => $conn->connect_error]);
    exit;
}

// Obtener los datos del cuerpo de la petición POST (JSON)
$data = json_decode(file_get_contents("php://input"));

// Validar que se recibieron los campos necesarios
if (!isset($data->correo) || empty(trim($data->correo)) ||
    !isset($data->contrasena) || empty(trim($data->contrasena))) {
    http_response_code(400); // Bad Request
    echo json_encode(["success" => false, "message" => "Por favor, proporciona el correo y la contraseña."]);
    exit;
}

// Limpiar y escapar los datos de entrada para prevenir inyecciones SQL
$correo = $conn->real_escape_string(trim($data->correo));
$contrasena_ingresada = trim($data->contrasena); // Contraseña ingresada por el usuario, sin hashear aún

// Prepara la consulta para buscar al usuario por correo electrónico en tu tabla `usuarios_app`
$sql = "SELECT id, nombre, apellidos, documento, contrasena FROM usuarios_app WHERE correo = ?";

// Usar prepared statements para mayor seguridad (previene inyección SQL)
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error al preparar la consulta SQL", "error" => $conn->error]);
    exit;
}

$stmt->bind_param("s", $correo); // "s" indica que el parámetro es una cadena (string)
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    // Verificar la contraseña hasheada
    // ¡IMPORTANTE!: `contrasena` en la DB DEBE almacenar el hash seguro
    if (password_verify($contrasena_ingresada, $user['contrasena'])) {
        // Credenciales correctas, inicio de sesión exitoso
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Inicio de sesión exitoso.",
            "userName" => $user['nombre'] . ' ' . $user['apellidos'], // Combinar nombre y apellidos
            "documento" => $user['documento']
        ]);
    } else {
        // Contraseña incorrecta
        http_response_code(401); // Unauthorized
        echo json_encode(["success" => false, "message" => "Correo o contraseña incorrectos."]);
    }
} else {
    // Usuario no encontrado (correo incorrecto)
    http_response_code(401); // Unauthorized
    echo json_encode(["success" => false, "message" => "Correo o contraseña incorrectos."]);
}

$stmt->close();
$conn->close();
?>
