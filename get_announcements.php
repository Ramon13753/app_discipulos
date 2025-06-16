<?php
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");

    set_exception_handler(function($e) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Excepción en el servidor", "error" => $e->getMessage()]);
        exit;
    });

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

    // Consulta para obtener todos los anuncios, ordenados por fecha de creación descendente
    $sql = "SELECT id, title, description, content_url FROM announcements ORDER BY created_at DESC";
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
    
