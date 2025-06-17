<?php
// Permite que cualquier origen (tu app Flutter) acceda a este script
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST"); // Permitir solo peticiones POST
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

// *** Credenciales de la Base de Datos Railway (¡Ahora desde Variables de Entorno!) ***
$servername = getenv('DB_SERVER') ?: ''; // Valor por defecto para desarrollo local si no está seteada
$username_db = getenv('DB_USERNAME') ?: '';
$password_db = getenv('DB_PASSWORD') ?: '';
$dbname = getenv('DB_NAME') ?: '';
$port = getenv('DB_PORT') ?: 0; // El puerto se lee como string, luego se usará como int

// Convertir el puerto a entero, ya que getenv() devuelve un string
$port = (int)$port;

// Conectar a la base de datos
$conn = new mysqli($servername, $username_db, $password_db, $dbname, $port);

// Verificar si la conexión falló
if ($conn->connect_error) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["success" => false, "message" => "Error de conexión a la base de datos", "error" => $conn->connect_error]);
    exit;
}

// Obtener los datos del cuerpo de la petición POST (JSON)
$data = json_decode(file_get_contents("php://input"));

// Validar que se recibió el campo 'username_or_email'
if (!isset($data->username_or_email) || empty(trim($data->username_or_email))) {
    http_response_code(400); // Bad Request
    echo json_encode(["success" => false, "message" => "Debe proporcionar un nombre de usuario o correo electrónico."]);
    exit;
}

$usernameOrEmail = $conn->real_escape_string(trim($data->username_or_email));

// Buscar el usuario por nombre de usuario o correo electrónico en la tabla `usuarios_app`
$sql = "SELECT id, nombre, apellidos, correo FROM usuarios_app WHERE correo = ? OR documento = ?";

// Usar prepared statements para mayor seguridad (previene inyección SQL)
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error al preparar la consulta SQL", "error" => $conn->error]);
    exit;
}

// Intentamos buscar por correo y documento (si usas documento como identificador de login)
// Si solo usas correo para login, puedes quitar la segunda parte del OR y el segundo bind_param
$stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail); // "ss" porque son dos cadenas
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $userEmail = $user['correo'];
    $userName = $user['nombre'] . ' ' . $user['apellidos'];

    // --- LÓGICA DE RECUPERACIÓN (SIMULACIÓN DE ENVÍO DE EMAIL) ---
    // EN UNA APLICACIÓN REAL:
    // 1. Generarías un token único y seguro (ej. usando bin2hex(random_bytes(32)))
    // 2. Guardarías este token en una nueva tabla de la base de datos junto con el ID del usuario y una fecha de expiración.
    // 3. Enviarías un correo electrónico al $userEmail (usando un servicio SMTP externo como SendGrid, Mailgun, etc.)
    //    con un enlace para restablecer la contraseña que incluiría este token.
    //    Ejemplo de enlace: https://tuapp.com/reset-password?token=GENERATED_SECURE_TOKEN
    // 4. El usuario haría clic en ese enlace, lo que lo llevaría a una página donde podría establecer una nueva contraseña
    //    después de validar el token.

    $subject = "Recuperación de Contraseña para Discípulos App";
    $message = "Hola {$userName},\n\n";
    $message .= "Hemos recibido una solicitud para recuperar tus credenciales.\n";
    $message .= "En una aplicación REAL, aquí iría un enlace ÚNICO para restablecer tu contraseña.\n";
    $message .= "Por ejemplo: https://tuapp.com/reset-password?token=GENERATED_SECURE_TOKEN\n\n";
    $message .= "Si no solicitaste esto, ignora este correo.\n\n";
    $message .= "Saludos,\nEl Equipo de Discípulos App";

    // Simulación de envío de correo.
    // Para envío real, la función mail() de PHP a menudo requiere configuración SMTP en el servidor de hosting (Render).
    // O mejor aún, integrar con una API de un servicio de correos como SendGrid.
    // Esto es solo para fines de demostración en el backend que el proceso "inició".
    /*
    $headers = 'From: no-reply@tudominio.com' . "\r\n" .
               'Reply-To: no-reply@tudominio.com' . "\r\n" .
               'X-Mailer: PHP/' . phpversion();
    if (mail($userEmail, $subject, $message, $headers)) {
        // Correo enviado con éxito (simulado)
    } else {
        // Falló el envío de correo (pero el proceso de recuperación aún puede "iniciar" si quieres mostrar el mensaje al usuario)
        error_log("Fallo el envio de correo a $userEmail para recuperacion de credenciales.");
    }
    */

    // Por seguridad, siempre enviamos un mensaje genérico para no revelar si el correo/usuario existe o no
    http_response_code(200); // OK
    echo json_encode(["success" => true, "message" => "Si el usuario existe, se ha iniciado el proceso de recuperación de credenciales. Revisa tu correo."]);

} else {
    // Si el usuario no fue encontrado, por seguridad, damos el mismo mensaje genérico.
    http_response_code(200); // OK
    echo json_encode(["success" => true, "message" => "Si el usuario existe, se ha iniciado el proceso de recuperación de credenciales. Revisa tu correo."]);
}

$stmt->close();
$conn->close();
?>
