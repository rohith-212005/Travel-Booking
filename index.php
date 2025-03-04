<?php
include('includes/dp.php'); // Ensure database connection
session_start();

$error_message = ""; // Store errors

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form inputs
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (!empty($username) && !empty($password)) {
        // Check if username exists in users table
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Verify password
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];

                // Store login details in 'logins' table
                $stmt = $conn->prepare("INSERT INTO logins (user_id, username, password) VALUES (?, ?, ?)");
                $stmt->execute([$user['user_id'], $username, $user['password']]);

                // Redirect to homepage
                header("Location: homepage.php");
                exit();
            } else {
                $error_message = "❌ Incorrect password. Please try again.";
            }
        } else {
            $error_message = "❌ Username not registered. Please Register first.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Travel Booking</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Navbar -->
    <nav>
        <div class="logo">Travel Booking</div>
    </nav>

    <!-- Login Form -->
    <form action="" method="post">
        <div>
            <h1>Login</h1>
            <br>
            <input type="text" placeholder="Username" id="username" name="username" required>
            <br><br>
            <input type="password" placeholder="Password" id="password" name="password" required>
            <br><br>
            <input type="checkbox" name="remember_me"> Remember me
            <br><br>
            <input type="submit" value="Login" id="submit">
            <br><br>
            <p>Don't have an account? <a href="register.php" target="_main">Register</a></p>
            <?php if (!empty($error_message)): ?>
                <p class="error-message"><?= htmlspecialchars($error_message); ?></p>
            <?php endif; ?>
        </div>
    </form>

</body>
</html>
