<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("location: login.php");
    exit;
}

require 'config.php';

// Obtener el user_id sesionado
$user_id = $_SESSION['user_id'];

// Obtener la cantidad de los dispositivos del usuario
$sql = "SELECT COUNT(*) FROM devices WHERE user_id = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$result = $stmt->get_result();
$num_devices = $result->fetch_row()[0];

// Consulta SQL para obtener los últimos 10 datos de cada device_id
$sql = "SELECT g.*, d.animal_type, d.animal_name
    FROM (
        SELECT *, ROW_NUMBER() OVER (PARTITION BY device_id ORDER BY id DESC) AS row_num
        FROM gps_data
        WHERE device_id IN (
            SELECT device_id FROM devices WHERE user_id = $user_id
        )
    ) AS g
    JOIN devices AS d ON g.device_id = d.device_id
    WHERE g.row_num <= 10";
$result = $db->query($sql);

if (!$result) {
    echo "Error: " . $sql . "<br>" . $db->error;
}

$rows = $result->fetch_all(MYSQLI_ASSOC);
$nogpsData = false;

if (!$rows) {
    $nogpsData = true;
    return;
    //exit("<h1>No hay datos de localización</h1>");
}

// Crear una matriz vacía de 5 x 10
$gps_data = array_fill(0, 5, array_fill(0, 10, null));
// Crear un array vacío de 5
$devices_data = array_fill(0, 5, null);

$backup_did = "a";
$column_index = 0;
$row_index = -1;

// Recorrer los resultados y agregarlos a la matriz
foreach ($result as $row) {
    $lat = $row['lat'];
    $lng = $row['lng'];
    $created_at = $row['created_at'];
    $_did = $row['device_id'];
    if($backup_did != $_did) {
        $row_index++;
        $column_index = 0;
        $backup_did = $_did;
    }
    // Agregar los datos a la matriz
    $gps_data[$row_index][$column_index] = array('lat' => $lat, 'lng' => $lng, 'created_at' => $created_at, 'device_id' => $_did);

    $column_index++;
}

$backup_did = "a";
$index = 0;

// Recorrer los resultados y agregarlos al array
foreach ($result as $row) {
    $animal_type = $row['animal_type'];
    $animal_name = $row['animal_name'];
    $_did = $row['device_id'];
    if($backup_did != $_did) {
        // Agregar los datos al array
        $devices_data[$index] = array('device_id' => $_did, 'animal_name' => $animal_name, 'animal_type' => $animal_type);

        $backup_did = $_did;
        $index++;
    }
}

$row = $gps_data[0][0];
$centerLat = $row['lat'];
$centerLng = $row['lng'];

/*//Mostrar los datos de devices_data
for ($i = 0; $i < 5; $i++) {
    $row = $devices_data[$i];
    if ($row) {
        $animal_type = $row['animal_type'];
        $animal_name = $row['animal_name'];
        $_did = $row['device_id'];
        // Mostrar los datos en la página
        echo "Index: $i, Tipo de animal: $animal_type, Nombre del animal: $animal_name, Device ID: $_did <br>";
    }
}

// Mostrar los datos de la matriz
for ($i = 0; $i < 5; $i++) {
    for ($j = 0; $j < 10; $j++) {
        $row = $gps_data[$i][$j];
        if ($row) {
            $lat = $row['lat'];
            $lng = $row['lng'];
            $created_at = $row['created_at'];
            $_did = $row['device_id'];
            // Mostrar los datos en la página
            echo "Row Index: $i, Column Index: $j, Latitud: $lat, Longitud: $lng, Created At: $created_at Device ID: $_did <br>";
        }
    }
}*/
?>