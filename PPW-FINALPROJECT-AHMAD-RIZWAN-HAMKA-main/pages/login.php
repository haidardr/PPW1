<?php
session_start();


include 'koneksi.php'; 


$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$loginError = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $passwordInput = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($passwordInput, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            // buat cookie
            if (isset($_POST['remember'])) {
                setcookie("user_id", $user['id'], time() + (86400 * 30), "/");
                setcookie("user_name", $user['name'], time() + (86400 * 30), "/");
                setcookie("user_role", $user['role'], time() + (86400 * 30), "/");
            }

            if ($_SESSION['user_role'] == 'user'){
                header("Location: ?page=profile");
            } else{
                header("Location: ?page=dashboard");
            }
            
            exit();
        } else {
            $loginError = "Wrong Password!";
        }
    } else {
        $loginError = "Email not found";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Zwnzs Store</title>
    <link rel="icon" href="assets/images/favicon.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            background-color: white;
            color: #000000;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        header {
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #ffffff;
            border-bottom: 1px solid #f0f0f0;
        }

        nav {
            display: flex;
            gap: 30px;
        }

        nav a {
            text-decoration: none;
            color: #000;
            font-size: 16px;
            font-weight: 400;
        }

        .login-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
            background: linear-gradient(135deg, #f0f0f0 0%, #ffffff 100%);
        }

        .login-card {
            background-color: #ffffff;
            border: 2px solid #1b1b1b;
            border-radius: 8px;
            padding: 40px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            font-size: 32px;
            font-weight: 900;
            color: #1b1b1b;
            margin-bottom: 8px;
            line-height: 1.2;
        }

        .login-header p {
            font-size: 16px;
            color: #666;
            font-weight: 400;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #1b1b1b;
            margin-bottom: 6px;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            font-size: 16px;
            border: 2px solid #f0f0f0;
            border-radius: 6px;
            background-color: #fafafa;
            transition: all 0.3s ease;
            font-family: "Poppins", sans-serif;
        }

        .form-control:focus {
            outline: none;
            border-color: #1b1b1b;
            background-color: white;
            box-shadow: 0 0 0 3px rgba(27, 27, 27, 0.1);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 25px;
        }

        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #1b1b1b;
        }

        .checkbox-group label {
            font-size: 14px;
            color: #666;
            margin: 0;
            cursor: pointer;
        }

        .login-button {
            width: 100%;
            background-color: #1b1b1b;
            color: white;
            padding: 14px 20px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: "Poppins", sans-serif;
        }

        .login-button:hover {
            background-color: #333333;
            transform: translateY(-2px);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .error-message {
            background-color: #fee;
            color: #c33;
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            border: 1px solid #fcc;
        }

        .login-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #f0f0f0;
        }

        .login-footer p {
            font-size: 14px;
            color: #666;
        }

        .login-footer a {
            color: #1b1b1b;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .login-footer a:hover {
            color: #333333;
            text-decoration: underline;
        }

        footer {
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #ffffff;
            border-top: 1px solid #f0f0f0;
        }

        /* Responsive */
        @media (max-width: 768px) {
            header {
                padding: 15px 20px;
            }

            .login-card {
                padding: 30px 25px;
                margin: 20px;
            }

            .login-header h1 {
                font-size: 28px;
            }

            nav {
                gap: 20px;
            }

            footer {
                padding: 15px 20px;
                flex-direction: column;
                gap: 10px;
            }
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 25px 20px;
            }

            .login-header h1 {
                font-size: 24px;
            }

            nav {
                gap: 15px;
            }

            nav a {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div>
            <a href="?page=home" class="logo">
                <img src="assets/images/logo.png" style="height: 18px;" alt="">
            </a>
        </div>
        <nav>
            <a href="?page=home">Home</a>
            <a href="?page=shop">Shop</a>
            <a href="?page=register">Register</a>
        </nav>
    </header>

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>Welcome Back</h1>
                <p>Sign in to your account</p>
            </div>

            <?php if ($loginError): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($loginError); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" required 
                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Remember me for 30 days</label>
                </div>

                <button type="submit" class="login-button">Sign In</button>
            </form>

            <div class="login-footer">
                <p>Don't have an account? <a href="?page=register">Create one here</a></p>
            </div>
        </div>
    </div>

    <footer>
        <div>© 2025 zwnzs Store. All rights reserved.</div>
        <div>Made with ❤️</div>
    </footer>
</body>
</html>