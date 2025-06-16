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

if (
    isset($_POST['nombre'], $_POST['apellidos'], $_POST['documento'], $_POST['direccion'], 
          $_POST['telefono'], $_POST['genero'], $_POST['correo'], $_POST['contrasena'])
) {
    $nombre = $_POST['nombre'];
    $apellidos = $_POST['apellidos'];
    $documento = $_POST['documento'];
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];
    $genero = strtoupper(trim($_POST['genero']));
    $correo = $_POST['correo'];
    $contrasena = password_hash($_POST['contrasena'], PASSWORD_BCRYPT);

    if ($genero !== 'M' && $genero !== 'F') {
        echo json_encode(["success" => false, "message" => "Género inválido"]);
        exit;
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["success" => false, "message" => "Correo inválido"]);
        exit;
    }

    $conn = new mysqli("shuttle.proxy.rlwy.net", "root", "NXcdHmwfHhmucKqdmxPCYMLrRFDMiyNu", "discipulos_app", 40395);

    if ($conn->connect_error) {
        echo json_encode(["success" => false, "message" => "Conexión fallida: " . $conn->connect_error]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO usuarios_app (nombre, apellidos, documento, direccion, telefono, genero, correo, contrasena) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $nombre, $apellidos, $documento, $direccion, $telefono, $genero, $correo, $contrasena);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Usuario registrado correctamente"]);
    } else {
        if ($stmt->errno === 1062) {
            echo json_encode(["success" => false, "message" => "Documento o correo ya existe"]);
        } else {
            echo json_encode(["success" => false, "message" => "Error: " . $stmt->error]);
        }
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Faltan datos en la solicitud"]);
}
?>
