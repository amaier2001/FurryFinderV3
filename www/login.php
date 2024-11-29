<?php
require_once "config.php";

if (isset($_GET["registro"]) && $_GET["registro"] == "exitoso") {
    echo "¡Registro exitoso! Por favor inicie sesión.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Validar los datos ingresados
    if (empty($username) || empty($password)) {
        echo "Por favor complete todos los campos.";
    } else {
        // Verificar si el usuario existe en la base de datos
        $sql = "SELECT user_id, password FROM users WHERE username = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $hashed_password = $row["password"];

            // Verificar si la contraseña es correcta
            if (password_verify($password, $hashed_password)) {
                // Iniciar sesión y redirigir al usuario a la página de bienvenida
                session_start();
                $_SESSION["user_id"] = $row["user_id"];
                header("location: map.php");
            } else {
                echo "Contraseña incorrecta.";
            }
        } else {
            echo "El nombre de usuario no existe.";
        }
    }
}
?>

<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" style="display: flex; flex-direction: column; width: 200px; margin: 200px auto;">
    <label for="username">Usuario:</label>
    <input type="text" name="username" id="username" style="display: block;">

    <label for="password">Contraseña:</label>
    <input type="password" name="password" id="password" style="display: block;">

    <input type="submit" value="Iniciar sesión" style="display: block;">
</form>

<a style="position: absolute; top: 10; right: 10;" href="register.php">Regístrate</a>