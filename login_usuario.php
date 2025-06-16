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

if (isset($_POST['correo'], $_POST['contrasena'])) {
    $correo = $_POST['correo'];
    $contrasena = $_POST['contrasena'];

    // *** ASEGÚRATE DE USAR LAS MISMAS CREDENCIALES DE BD QUE TE FUNCIONARON ***
    $servername = "shuttle.proxy.rlwy.net";
    $username = "root"; // Usuario de tu base de datos
    $password = "NXcdHmwfHhmucKqdmxPCYMLrRFDMiyNu"; // Contraseña de tu base de datos
    $dbname = "discipulos_app"; // Nombre de tu base de datos
    $port = 40395; // Puerto de tu base de datos

    $conn = new mysqli($servername, $username, $password, $dbname, $port);

    if ($conn->connect_error) {
        echo json_encode(["success" => false, "message" => "Conexión a la base de datos fallida", "error" => $conn->connect_error]);
        exit;
    }

    // Preparar la consulta para buscar el usuario por correo
    $stmt = $conn->prepare("SELECT id, nombre, apellidos, documento, contrasena FROM usuarios_app WHERE correo = ?");
    if ($stmt === false) {
        echo json_encode(["success" => false, "message" => "Error al preparar la consulta", "error" => $conn->error]);
        exit;
    }

    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Verificar la contraseña
        if (password_verify($contrasena, $user['contrasena'])) {
            echo json_encode([
                "success" => true,
                "message" => "Inicio de sesión exitoso",
                "userName" => $user['nombre'] . ' ' . $user['apellidos'], // Envía el nombre completo
                "documento" => $user['documento'] // Envía el documento si lo necesitas
            ]);
        } else {
            echo json_encode(["success" => false, "message" => "Contraseña incorrecta"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Correo no encontrado"]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Faltan datos para el inicio de sesión"]);
}
?>
