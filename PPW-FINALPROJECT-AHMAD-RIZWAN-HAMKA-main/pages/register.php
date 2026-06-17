<?php
include 'koneksi.php'; 

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$registerError = '';
$successMessage = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone_number = trim($_POST['phone_number']);
    $passwordInput = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($passwordInput !== $confirmPassword) {
        $registerError = "Password tidak cocok!";
    } else {
        $hashedPassword = password_hash($passwordInput, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone_number) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $hashedPassword, $phone_number);

        if ($stmt->execute()) {
            $successMessage = "Registrasi berhasil! Silakan <a href='?page=login'>login</a>.";
        } else {
            $registerError = "Gagal mendaftar: " . $stmt->error;
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Zwnzs Store</title>
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

        .register-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
            background: linear-gradient(135deg, #84a1ff 0%, #f0f0f0 100%);
        }

        .register-card {
            background-color: #ffffff;
            border: 2px solid #1b1b1b;
            border-radius: 8px;
            padding: 40px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .register-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
        }

        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .register-header h1 {
            font-size: 32px;
            font-weight: 900;
            color: #1b1b1b;
            margin-bottom: 8px;
            line-height: 1.2;
        }

        .register-header p {
            font-size: 16px;
            color: #666;
            font-weight: 400;
        }

        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
            flex: 1;
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

        .register-button {
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
            margin-top: 10px;
        }

        .register-button:hover {
            background-color: #333333;
            transform: translateY(-2px);
        }

        .register-button:active {
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

        .success-message {
            background-color: #efe;
            color: #363;
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            border: 1px solid #cfc;
        }

        .success-message a {
            color: #1b1b1b;
            font-weight: 600;
            text-decoration: none;
        }

        .success-message a:hover {
            text-decoration: underline;
        }

        .register-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #f0f0f0;
        }

        .register-footer p {
            font-size: 14px;
            color: #666;
        }

        .register-footer a {
            color: #1b1b1b;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .register-footer a:hover {
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

        .password-requirements {
            font-size: 12px;
            color: #666;
            margin-top: 4px;
            line-height: 1.4;
        }

        /* Responsive */
        @media (max-width: 768px) {
            header {
                padding: 15px 20px;
            }

            .register-card {
                padding: 30px 25px;
                margin: 20px;
            }

            .register-header h1 {
                font-size: 28px;
            }

            .form-row {
                flex-direction: column;
                gap: 0;
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
            .register-card {
                padding: 25px 20px;
            }

            .register-header h1 {
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
            <a href="?page=login">Login</a>
        </nav>
    </header>

    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <h1>Join Us Today</h1>
                <p>Create your account to start shopping</p>
            </div>

            <?php if ($registerError): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($registerError); ?>
                </div>
            <?php endif; ?>

            <?php if ($successMessage): ?>
                <div class="success-message">
                    <?php echo $successMessage; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="?page=register">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" class="form-control" required 
                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                           placeholder="Enter your full name">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" required 
                                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                placeholder="your@email.com">
                    </div>

                    <div class="form-group">
                        <label for="phone_number">Phone Number</label>
                        <input type="text" id="phone_number" name="phone_number" class="form-control" required 
                                value="<?php echo isset($_POST['phone_number']) ? htmlspecialchars($_POST['phone_number']) : ''; ?>"
                                placeholder="08123456789">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required
                                placeholder="Create a strong password">
                        <div class="password-requirements">
                            Min. 8 characters with letters and numbers
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required
                                placeholder="Confirm your password">
                    </div>
                </div>

                <button type="submit" class="register-button">Create Account</button>
            </form>

            <div class="register-footer">
                <p>Already have an account? <a href="?page=login">Sign in here</a></p>
            </div>
        </div>
    </div>

    <footer>
        <div>© 2025 zwnzs Store. All rights reserved.</div>
        <div>Made with ❤️</div>
    </footer>

    <script>
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword && confirmPassword.length > 0) {
                this.style.borderColor = '#dc3545';
            } else {
                this.style.borderColor = '#f0f0f0';
            }
        });

        document.getElementById('phone_number').addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.length > 0 && !value.startsWith('0')) {
                value = '0' + value;
            }
            this.value = value;
        });
    </script>
</body>
</html>