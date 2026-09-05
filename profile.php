<?php
require_once 'auth.php';
$user = currentUser($pdo);
$error = '';
$success = '';

// --- FORM SUBMISSION LOGIC (UNCHANGED) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_username'])) {
        $new_username = trim($_POST['username']);
        if (strlen($new_username) >= 3) {
            try {
                $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
                $stmt->execute([$new_username, $_SESSION['user_id']]);
                $_SESSION['username'] = $new_username;
                $success = t('username_updated');
            } catch (PDOException $e) {
                if ($e->errorInfo[1] == 1062) { $error = t('username_taken'); } 
                else { $error = t('username_error'); }
            }
        } else { $error = t('username_min'); }
    }

    if (isset($_POST['update_password'])) {
        $old = $_POST['old_password'];
        $new = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];

        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $row = $stmt->fetch();

        if (!$row || !password_verify($old, $row['password'])) { $error = t('wrong_password'); } 
        elseif (strlen($new) < 6) { $error = t('password_min'); } 
        elseif ($new !== $confirm) { $error = t('password_mismatch'); } 
        else {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $_SESSION['user_id']]);
            $success = t('password_updated');
        }
    }

    if (isset($_POST['update_email'])) {
        $new_email = trim($_POST['email']);
        if (empty($new_email) || filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            try {
                $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
                $stmt->execute([$new_email, $_SESSION['user_id']]);
                $success = t('email_updated');
            } catch (PDOException $e) {
                if ($e->errorInfo[1] == 1062) { $error = t('email_taken'); } 
                else { $error = t('email_error'); }
            }
        } else { $error = t('email_invalid'); }
    }
    if (isset($_POST['update_token'])) {
        $confirm = trim($_POST['confirm_token'] ?? '');
        $lang = language();
        $required_confirm = $lang === 'fa' ? 'تغییر توکن' : 'confirm';
        if ($confirm !== $required_confirm) {
            $error = t('confirm_invalid');
        } else {
            $new_token = bin2hex(random_bytes(32));
            $stmt = $pdo->prepare("UPDATE users SET user_token = ? WHERE id = ?");
            $stmt->execute([$new_token, $_SESSION['user_id']]);
            $success = t('token_change_success');
        }
    }
    // Refresh user data after updates
    $user = currentUser($pdo);
}
?>
<!DOCTYPE html>
<html lang="<?= language() ?>" dir="<?= pageDirection() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('profile') ?> - LogStream</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=3">
    <script src="js/theme.js"></script>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg bg-light fixed-top shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php"><i class="fas fa-stream"></i> LogStream</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php"><i class="fas fa-arrow-left"></i> <?= t('back_dashboard') ?></a>
                </li>
            </ul>
            <?= themeSwitcher() ?>
            <?= languageSwitcher() ?>
        </div>
    </div>
</nav>

<main class="container" style="padding-top: 80px;">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-user-edit"></i> <?= t('profile') ?>: <?= escape($user['username']) ?></h1>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= escape($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= escape($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Update Username -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header"><h5 class="mb-0"><i class="fas fa-user"></i> <?= t('change_username') ?></h5></div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label for="username" class="form-label"><?= t('new_username') ?></label>
                            <input type="text" class="form-control" id="username" name="username" value="<?= escape($user['username']) ?>" required>
                        </div>
                        <button type="submit" name="update_username" class="btn btn-primary"><i class="fas fa-save"></i> <?= t('update') ?></button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Update Email -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header"><h5 class="mb-0"><i class="fas fa-envelope"></i> <?= t('change_email') ?></h5></div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label for="email" class="form-label"><?= t('new_email') ?></label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= escape($user['email']) ?>" placeholder="<?= t('email_optional') ?>">
                        </div>
                        <button type="submit" name="update_email" class="btn btn-primary"><i class="fas fa-save"></i> <?= t('update') ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Password -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header"><h5 class="mb-0"><i class="fas fa-key"></i> <?= t('change_password') ?></h5></div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label for="old_password" class="form-label"><?= t('current_password') ?></label>
                            <input type="password" class="form-control" id="old_password" name="old_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label"><?= t('new_password') ?></label>
                            <input type="password" class="form-control" id="new_password" name="new_password" placeholder="<?= t('password_min_hint') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label"><?= t('confirm_password') ?></label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit" name="update_password" class="btn btn-warning"><i class="fas fa-sync-alt"></i> <?= t('change_password') ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Token -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header"><h5 class="mb-0"><i class="fas fa-key"></i> <?= t('change_token') ?></h5></div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label"><?= t('my_token') ?></label>
                            <code class="form-control text-monospace d-block mb-2"><?= escape($user['user_token'] ?? '') ?></code>
                            <div class="alert alert-warning">
                                <small><?= t('confirm_token_change_desc') ?></small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <input type="text" class="form-control" name="confirm_token" placeholder="<?= t('confirm_token_placeholder') ?>" required>
                                <button type="submit" name="update_token" class="btn btn-warning" style="white-space: nowrap;" onclick="return confirm('<?= t('confirm_token_action') ?>')">
                                    <i class="fas fa-sync-alt"></i> <?= t('generate_new_token') ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
