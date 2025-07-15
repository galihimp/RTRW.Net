<?php
session_start();

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: admin/dashboard.php");
    exit();
}

require_once __DIR__ . '/config/config_database.php';

$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        try {
            $stmt = $mysqli->prepare("SELECT id_user, username, password, nama_lengkap, level, status FROM users WHERE username = ? AND status = 'aktif'");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                $password_valid = false;

                if (strlen($user['password']) === 32 && ctype_xdigit($user['password'])) {
                    $password_valid = (md5($password) === $user['password']);
                } else {
                    $password_valid = password_verify($password, $user['password']);
                    if (!$password_valid) {
                        $password_valid = ($password === $user['password']);
                    }
                }

                if ($password_valid) {
                    $_SESSION['logged_in'] = true;
                    $_SESSION['user_id'] = $user['id_user'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                    $_SESSION['level'] = $user['level'];

                    if (function_exists('log_activity')) {
                        log_activity($username, "Login ke sistem", "users", $user['id_user']);
                    }

                    header("Location: admin/dashboard.php");
                    exit();
                } else {
                    $error_message = "Username atau password salah!";
                }
            } else {
                $error_message = "Username atau password salah!";
            }
            $stmt->close();
        } catch (Exception $e) {
            $error_message = "Terjadi kesalahan sistem. Silakan coba lagi.";
            error_log("Login error: " . $e->getMessage());
        }
    } else {
        $error_message = "Username dan password harus diisi!";
    }
}

$company_name = '';
$has_logo = false;


// Periksa apakah file logo ada
$logo_path = 'img/logo_anunet_new.png';
$has_logo = file_exists(__DIR__ . '/' . $logo_path);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= strip_tags($company_name) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #ffffff, #e3f2fd);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            font-size: 13px;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .login-container {
            max-width: 420px;
            margin: auto;
            animation: fadeIn 0.5s ease-in-out;
        }

        .login-card {
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 0 25px rgba(0, 123, 255, 0.15);
            transition: transform 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-4px);
        }

        .login-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .login-logo img {
            max-height: 80px;
            transition: transform 0.3s ease;
        }

        .login-logo img:hover {
            transform: scale(1.05);
        }

        .login-subtitle {
            font-size: 14px;
            color: #6c757d;
        }

        .form-control {
            border-radius: 6px;
            border: 1px solid #ced4da;
            height: 44px;
            font-size: 14px;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .btn-login {
            background-color: #007bff;
            color: #fff;
            font-weight: 600;
            height: 45px;
            border-radius: 6px;
            transition: all 0.3s ease-in-out;
        }

        .btn-login:hover {
            background-color: #0056b3;
        }

        .input-group-text {
            background-color: #f1f3f5;
            border: 1px solid #ced4da;
            border-right: none;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }

        .alert {
            font-size: 13px;
        }

        .protected-footer {
            position: relative;
            margin-top: 20px;
            padding-top: 15px;
            text-align: center;
        }

        .protected-footer::before {
            content: "";
            position: absolute;
            top: 0;
            left: 25%;
            right: 25%;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(0,0,0,0.1), transparent);
        }

        .protected-footer small {
            font-size: 11px;
            color: #95a5a6;
        }

        .login-footer {
            text-align: center;
            margin-top: 20px;
            color: #95a5a6;
            font-size: 12px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="login-container">
                <div class="login-card">
                    <div class="login-header">
                        <?php if ($has_logo): ?>
                            <div class="login-logo mb-3">
                                <img src="<?= htmlspecialchars($logo_path) ?>" alt="Logo" class="img-fluid">
                            </div>
                        <?php else: ?>
                            <div class="login-logo">
                                <i class="fas fa-building fa-4x text-primary"></i>
                            </div>
                        <?php endif; ?>
                        <p class="login-subtitle"><?= $company_name ?></p>
                    </div>

                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?= htmlspecialchars($error_message) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" autocomplete="off">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="username" name="username"
                                       placeholder="Masukkan username" required
                                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group position-relative">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password"
                                       placeholder="Masukkan password" required>
                                <i class="fas fa-eye password-toggle" onclick="togglePassword()"></i>
                            </div>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-login">
                                <i class="fas fa-sign-in-alt me-2"></i> Login
                            </button>
                        </div>

                        <div class="protected-footer"></div>
                    </form>
                </div>

                <div class="login-footer">
                    <p class="mb-0">&copy; <?= date('Y') ?> <?= strip_tags($company_name) ?></p>
                    <p class="mb-0"><small>v1.0.0</small></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function togglePassword() {
        const passwordField = document.getElementById('password');
        const toggleIcon = document.querySelector('.password-toggle');
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleIcon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            passwordField.type = 'password';
            toggleIcon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const footer = document.querySelector('.protected-footer');
        if (footer) {
            const copyright = document.createElement('small');
            copyright.className = 'text-muted';
            copyright.innerHTML = '© Kinet Hotspot | ' + new Date().getFullYear();
            footer.appendChild(copyright);

            Object.defineProperty(footer, 'innerHTML', {
                writable: false,
                configurable: false
            });

            setInterval(() => {
                if (!document.querySelector('.protected-footer small')) {
                    location.reload();
                }
            }, 3000);
        }

        document.getElementById('username').focus();
    });
</script>
</body>
</html>
