<?php
session_start();

// Kalau sudah login, redirect sesuai role
if (isset($_SESSION['id_karyawan'])) {
    if ($_SESSION['role'] === 'Manajer') {
        header("Location: public/dashboard.php");
    } else {
        header("Location: public/kasir.php");
    }
    exit;
}

$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TokoRoti</title>
</head>
<body>
    <h2>Login TokoRoti</h2>

    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form action="process/login_process.php" method="POST">
        <table>
            <tr>
                <td>Username</td>
                <td><input type="text" name="username" required autofocus></td>
            </tr>
            <tr>
                <td>Password</td>
                <td><input type="password" name="password" required></td>
            </tr>
        </table>
        <br>
        <button type="submit">Login</button>
    </form>
</body>
</html>
