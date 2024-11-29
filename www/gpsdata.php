<?php 

require 'config.php';

$lat = $_GET['lat'];
$lng = $_GET['lng'];
$_did = $_GET['did'];

if (isset($lat) && isset($lng) && isset($_did)) {
	// Verificar si el dispositivo existe en la tabla devices
	$device_query = "SELECT * FROM devices WHERE device_id = '".$_did."'";
	$device_result = $db->query($device_query);

	if ($device_result->num_rows > 0) {
		// El dispositivo existe en la tabla devices
		$sql = "INSERT INTO gps_data (lat,lng,device_id) VALUES ('".$lat."','".$lng."','".$_did."')";

		if($db->query($sql) === FALSE) { 
			echo "Error: " . $sql . "<br>" . $db->error;
		} else {
			exit("Dato registrado."); 
		}
	} else {
		// El dispositivo no existe en la tabla devices
		exit("Error, dispositivo no registrado."); 
	}
} else {
    // Las variables no se han llenado
	exit("Error, no se han enviado datos.");
}

?>