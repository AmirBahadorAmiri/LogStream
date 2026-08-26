<?php
require_once 'config.php';
require_once 'functions.php';

initializeLanguage();

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';
$success = '';
$active_tab = 'login'; // Default to login tab

// --- Registration Logic ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $active_tab = 'register';
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $email = trim($_POST['email']);
    $captcha = $_POST['captcha'];

    if (!isset($_SESSION['captcha']) || strtolower($captcha) != strtolower($_SESSION['captcha'])) {
        $error = t('captcha_incorrect');
    } elseif (strlen($username) < 3 || strlen($password) < 6) {
        $error = t('credentials_short');
    } else {
        try {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
            $stmt->execute([$username, $hashed, $email]);
            $success = t('registration_success');
            $active_tab = 'login'; // Switch to login tab on success
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $error = t('user_exists');
            } else {
                $error = t('try_again');
            }
        }
    }
    unset($_SESSION['captcha']);
}

// --- Login Logic ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $active_tab = 'login';
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $captcha = $_POST['captcha'];

    if (!isset($_SESSION['captcha']) || strtolower($captcha) != strtolower($_SESSION['captcha'])) {
        $error = t('captcha_incorrect');
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            redirect('dashboard.php');
        } else {
            $error = t('invalid_login');
        }
    }
    unset($_SESSION['captcha']);
}
?>
<!DOCTYPE html>
<html lang="<?= language() ?>" dir="<?= pageDirection() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LogStream - <?= t('login') ?> / <?= t('register') ?></title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=3">
    <script src="js/theme.js"></script>
    <style>
        body {
            background-color: #f4f7f6;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 80px 12px 24px;
            font-family: 'Vazirmatn', sans-serif;
        }
        .auth-card {
            max-width: 450px;
            width: 100%;
        }
        .captcha-img {
            cursor: pointer;
            border-radius: .25rem;
        }
        .auth-card .form-control {
            direction: rtl;
            text-align: right;
        }
        .auth-card .form-floating > label {
            right: 0;
            left: auto;
            text-align: right;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg bg-light fixed-top shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php"><i class="fas fa-stream"></i> LogStream</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="باز کردن منو">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <button class="nav-link auth-tab-trigger <?= $active_tab === 'login' ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#pills-login" type="button"><?= t('login') ?></button>
                </li>
                <li class="nav-item">
                    <button class="nav-link auth-tab-trigger <?= $active_tab === 'register' ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#pills-register" type="button"><?= t('register') ?></button>
                </li>
            </ul>
            <?= themeSwitcher() ?>
            <?= languageSwitcher() ?>
        </div>
    </div>
</nav>

<div class="auth-card">
    <div class="card shadow-lg border-0">
        <div class="card-header bg-dark text-white text-center py-3">
            <h1 class="h4 mb-0"><i class="fas fa-stream"></i> LogStream</h1>
        </div>
        <div class="card-body p-4">
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= escape($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= escape($success) ?></div>
            <?php endif; ?>

            <ul class="nav nav-pills nav-fill mb-3" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link auth-tab-trigger <?= $active_tab === 'login' ? 'active' : '' ?>" id="pills-login-tab" data-bs-toggle="pill" data-bs-target="#pills-login" type="button" role="tab"><?= t('login') ?></button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link auth-tab-trigger <?= $active_tab === 'register' ? 'active' : '' ?>" id="pills-register-tab" data-bs-toggle="pill" data-bs-target="#pills-register" type="button" role="tab"><?= t('register') ?></button>
                </li>
            </ul>

            <div class="tab-content" id="pills-tabContent">
                <!-- Login Form -->
                <div class="tab-pane fade <?= $active_tab === 'login' ? 'show active' : '' ?>" id="pills-login" role="tabpanel">
                    <form method="post">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="login_username" name="username" placeholder="<?= t('username') ?>" required>
                            <label for="login_username"><?= t('username') ?></label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" id="login_password" name="password" placeholder="<?= t('password') ?>" required>
                            <label for="login_password"><?= t('password') ?></label>
                        </div>
                        <div class="mb-3">
                            <label for="login_captcha" class="form-label"><?= t('security_code') ?></label>
                            <div class="input-group">
                                <img src="captcha.php" alt="کپچا" id="login_captcha_img" class="captcha-img" onclick="this.src='captcha.php?'+Math.random()">
                                <input type="text" class="form-control" id="login_captcha" name="captcha" placeholder="<?= t('enter_code') ?>" required>
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="login" class="btn btn-primary btn-lg"><i class="fas fa-sign-in-alt"></i> <?= t('login') ?></button>
                        </div>
                    </form>
                </div>

                <!-- Register Form -->
                <div class="tab-pane fade <?= $active_tab === 'register' ? 'show active' : '' ?>" id="pills-register" role="tabpanel">
                    <form method="post">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="register_username" name="username" placeholder="<?= t('username') ?>" required>
                            <label for="register_username"><?= t('username') ?></label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" id="register_password" name="password" placeholder="<?= t('password') ?>" required>
                            <label for="register_password"><?= t('password') ?> (<?= language() === 'fa' ? 'حداقل ۶ کاراکتر' : 'at least 6 characters' ?>)</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" id="register_email" name="email" placeholder="<?= t('email_optional') ?>">
                            <label for="register_email"><?= t('email_optional') ?></label>
                        </div>
                        <div class="mb-3">
                            <label for="register_captcha" class="form-label"><?= t('security_code') ?></label>
                            <div class="input-group">
                                <img src="captcha.php" alt="کپچا" id="register_captcha_img" class="captcha-img" onclick="this.src='captcha.php?'+Math.random()">
                                <input type="text" class="form-control" id="register_captcha" name="captcha" placeholder="<?= t('enter_code') ?>" required>
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="register" class="btn btn-primary btn-lg"><i class="fas fa-user-plus"></i> <?= t('register') ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelectorAll('.auth-tab-trigger').forEach((trigger) => {
        trigger.addEventListener('shown.bs.tab', (event) => {
            const target = event.target.dataset.bsTarget;
            document.querySelectorAll('.auth-tab-trigger').forEach((item) => {
                const isActive = item.dataset.bsTarget === target;
                item.classList.toggle('active', isActive);
                item.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });
        });
    });
</script>
</body>
</html>
