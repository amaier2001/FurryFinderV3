<?php
require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $email = $_POST["email"];

    // Validar los datos ingresados
    if (empty($username) || empty($password) || empty($email)) {
        echo "Por favor complete todos los campos.";
    } else {
        // Verificar si el usuario ya existe en la base de datos
        $sql = "SELECT user_id FROM users WHERE username = ? OR email = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (isset($row['username']) && $row['username'] == $username) {
                echo "El nombre de usuario ya está en uso.";
            } else {
                echo "El correo electrónico ya está en uso.";
            }
        } else {
            // Insertar el nuevo usuario en la base de datos
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, password, email) VALUES (?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("sss", $username, $hashed_password, $email);
            $stmt->execute();

            // Redirigir al usuario a la página de login
            header("location: login.php?registro=exitoso");
            exit();
        }
    }
}
?>

<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" style="display: flex; flex-direction: column; width: 200px; margin: 200px auto;">
    <label for="username">Usuario:</label>
    <input type="text" name="username" id="username" style="display: block;">

    <label for="password">Contraseña:</label>
    <input type="password" name="password" id="password" style="display: block;">

    <label for="confirm_password">Confirmar contraseña:</label>
    <input type="password" name="confirm_password" id="confirm_password" style="display: block;">

    <label for="email">Correo electrónico:</label>
    <input type="email" name="email" id="email" style="display: block;">

    <input type="submit" value="Registrarse" style="display: block;">
</form>

<script>
    const passwordField = document.getElementById("password");
    const confirmPasswordField = document.getElementById("confirm_password");

    confirmPasswordField.addEventListener("input", () => {
        if (passwordField.value !== confirmPasswordField.value) {
            confirmPasswordField.setCustomValidity("Las contraseñas no coinciden.");
        } else {
            confirmPasswordField.setCustomValidity("");
        }
    });
</script>

<a style="position: absolute; top: 10; right: 10;" href="login.php">Inicia sesión</a>