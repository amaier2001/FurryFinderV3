<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("location: login.php");
    exit;
}

require_once "config.php";

// Obtener los dispositivos del usuario
$sql = "SELECT id, device_id, animal_name, animal_type FROM devices WHERE user_id = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$result = $stmt->get_result();
$devices = $result->fetch_all(MYSQLI_ASSOC);

$sql = "SELECT COUNT(*) FROM devices WHERE user_id = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$result = $stmt->get_result();
$num_devices = $result->fetch_row()[0];

$form_active = true;

// Si el usuario ya tiene 5 dispositivos, mostrar un mensaje de error
if ($num_devices >= 5) {
    $form_active = false;
} else {
    // Si se ha enviado un formulario para agregar un dispositivo, insertar el nuevo dispositivo en la base de datos
    if (isset($_POST["device_id"]) && isset($_POST["animal_name"]) && isset($_POST["animal_type"])) {
        $device_id = $_POST["device_id"];
        $animal_name = $_POST["animal_name"];
        $animal_type = $_POST["animal_type"];

        // Check if the device_id already exists
        $sql = "SELECT COUNT(*) FROM devices WHERE device_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("s", $device_id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            echo "El dispositivo ya esta registrado.";
        } else {
            $sql = "INSERT INTO devices (user_id, device_id, animal_name, animal_type) VALUES (?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("iiss", $_SESSION["user_id"], $device_id, $animal_name, $animal_type);
            $stmt->execute();
            header("location: settings.php");
            exit;
        }
    }
}

// Si se ha enviado un formulario para eliminar un dispositivo, eliminar el dispositivo de la base de datos
if (isset($_POST["delete_device_id"])) {
    $delete_device_id = $_POST["delete_device_id"];

    // Delete all records from gps_data that have the device_id
    $sql = "DELETE FROM gps_data WHERE device_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("s", $delete_device_id);
    $stmt->execute();
    $stmt->close();

    // Delete the device from the devices table
    $sql = "DELETE FROM devices WHERE user_id = ? AND device_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("is", $_SESSION["user_id"], $delete_device_id);
    $stmt->execute();
    header("location: settings.php");
    exit;
}

?>

<h1>Configuración</h1>

<h2>Dispositivos</h2>

<table>
    <thead>
        <tr>
            <th>ID del dispositivo</th>
            <th>Nombre del animal</th>
            <th>Tipo de animal</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($devices as $device): ?>
            <tr>
                <td><?php echo $device["device_id"]; ?></td>
                <td><?php echo $device["animal_name"]; ?></td>
                <?php 
                    if ($device["animal_type"] == "dog"): 
                        $animal_type = "Perro";
                    elseif ($device["animal_type"] == "cat"):
                        $animal_type = "Gato";
                    endif;   
                ?>
                <td><?php echo $animal_type; ?></td>
                <td>
                    <form method="post" style="display: inline-block;">
                        <input type="hidden" name="delete_device_id" value="<?php echo $device["device_id"]; ?>">
                        <button type="submit">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php if ($form_active): ?>
<h3>Agregar dispositivo</h3>
<form method="post">
    <label for="device_id">ID del dispositivo:</label>
    <input type="number" id="device_id" name="device_id" required min="0">
    <br>
    <label for="animal_name">Nombre del animal:</label>
    <input type="text" id="animal_name" name="animal_name" maxlength="255" required>
    <br>
    <label for="animal_type">Tipo de animal:</label>
    <select id="animal_type" name="animal_type" required>
        <option value="dog">Perro</option>
        <option value="cat">Gato</option>
    </select>
    <br>
    <button type="submit">Agregar dispositivo</button>
</form>
<?php 
else:
    echo "<p>Ya tienes 5 dispositivos. Elimina uno para agregar otro.</p>";
endif; 
?>

<a style="position: absolute; top: 10; right: 10;" href="logout.php">Cerrar sesión</a>
<a style="position: absolute; top: 10; right: 105;" href="map.php">Mapa</a>
