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
    <title>Login - Sistem POS Tokoroti</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        body {
            background-color: #89C4F4; /* Background biru muda luar */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-card {
            background-color: white;
            width: 800px;
            display: flex;
            border-radius: 0px; /* Sesuai gambar yang terlihat tajam di sudut */
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        /* Bagian Kiri (Biru) */
        .left-panel {
            background-color: #3498DB;
            width: 45%;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
        }

        .left-panel img {
            width: 180px;
            height: 180px;
            background-color: white;
            border-radius: 50%;
            padding: 20px;
            margin-bottom: 30px;
            object-fit: contain;
        }

        .left-panel h1 {
            font-size: 28px;
            line-height: 1.2;
            text-align: left;
            width: 100%;
        }

        /* Bagian Kanan (Form) */
        .right-panel {
            width: 55%;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .top-links {
            text-align: right;
            font-size: 13px;
            margin-bottom: 40px;
            color: #666;
        }

        .top-links a {
            color: #3498DB;
            text-decoration: none;
            font-weight: bold;
        }

        h2 {
            font-size: 32px;
            color: #3498DB;
            margin-bottom: 30px;
        }

        .input-group {
            margin-bottom: 25px;
            position: relative;
        }

        .input-group input {
            width: 100%;
            padding: 10px 0;
            border: none;
            border-bottom: 1px solid #333;
            outline: none;
            font-size: 16px;
            color: #333;
        }

        .input-group label {
            position: absolute;
            top: 10px;
            left: 0;
            color: #999;
            transition: 0.3s;
            pointer-events: none;
        }

        /* Efek label melayang saat input diisi/fokus */
        .input-group input:focus ~ label,
        .input-group input:valid ~ label {
            top: -15px;
            font-size: 12px;
            color: #3498DB;
        }

        .btn-login {
            background-color: #3498DB;
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
            transition: 0.3s;
        }

        .btn-login:hover {
            background-color: #2980B9;
        }

        .footer-links {
            text-align: center;
            margin-top: 30px;
            font-size: 13px;
        }

        .footer-links a {
            color: #3498DB;
            text-decoration: none;
        }

        /* Pesan Error */
        .error-msg {
            color: #e74c3c;
            font-size: 13px;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="left-panel">
            <img src="public/assets/kasir.png" alt="POS Icon">
            <h1>Selamat datang<br>di Sistem POS<br>Tokoroti!</h1>
        </div>

        <div class="right-panel">


            <h2>Login</h2>

            <?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['login_error'])): ?>
    <div class="error-msg"><?= $_SESSION['login_error']; unset($_SESSION['login_error']); ?></div>
<?php endif; ?>

            <form action="process/login_process.php" method="POST">
                <div class="input-group">
                    <input type="text" name="username" required>
                    <label>Username</label>
                </div>

                <div class="input-group">
                    <input type="password" name="password" required>
                    <label>Password</label>
                </div>

                <button type="submit" class="btn-login">Login</button>
            </form>

        </div>
    </div>

</body>
</html>
