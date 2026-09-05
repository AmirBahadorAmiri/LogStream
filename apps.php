<?php
require_once 'auth.php';

// --- Delete Logic ---
if (isset($_GET['delete'])) {
    $app_id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("SELECT id FROM apps WHERE id = ? AND user_id = ?");
    $stmt->execute([$app_id, $_SESSION['user_id']]);
    if ($stmt->rowCount() > 0) {
        // Also delete associated devices
        $device_del_stmt = $pdo->prepare("DELETE FROM devices WHERE app_id = ?");
        $device_del_stmt->execute([$app_id]);
        
        // Also delete associated logs
        $log_del_stmt = $pdo->prepare("DELETE FROM logs WHERE app_id = ?");
        $log_del_stmt->execute([$app_id]);
        
        // Delete the app itself
        $app_del_stmt = $pdo->prepare("DELETE FROM apps WHERE id = ?");
        $app_del_stmt->execute([$app_id]);
    }
    redirect('dashboard.php');
}

// --- Edit Logic ---
if (isset($_GET['edit'])) {
    $app_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM apps WHERE id = ? AND user_id = ?");
    $stmt->execute([$app_id, $_SESSION['user_id']]);
    $app = $stmt->fetch();
    if (!$app) {
        redirect('dashboard.php');
    }

    $error = '';
    $success = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_app'])) {
        $new_name = trim($_POST['app_name']);
        if (!empty($new_name)) {
            $stmt = $pdo->prepare("UPDATE apps SET app_name = ? WHERE id = ?");
            $stmt->execute([$new_name, $app_id]);
            $success = t('updated_successfully');
            $app['app_name'] = $new_name;
        } else {
            $error = t('name_required');
        }
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['regenerate_token'])) {
        $confirm = trim($_POST['confirm_token'] ?? '');
        $lang = language();
        $required_confirm = $lang === 'fa' ? 'تغییر توکن' : 'confirm';
        if ($confirm !== $required_confirm) {
            $error = t('confirm_invalid');
        } else {
            $new_token = bin2hex(random_bytes(32));
            $stmt = $pdo->prepare("UPDATE apps SET app_token = ? WHERE id = ?");
            $stmt->execute([$new_token, $app_id]);
            $success = t('app_token_regenerated');
            $app['app_token'] = $new_token;
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="<?= language() ?>" dir="<?= pageDirection() ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= t('edit_app') ?> - LogStream</title>
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
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-edit"></i> <?= t('edit_app') ?>: <?= escape($app['app_name']) ?></h5>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= escape($error) ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= escape($success) ?></div>
                        <?php endif; ?>

                        <form method="post">
                            <div class="mb-3">
                                <label for="app_name" class="form-label"><?= t('new_app_name') ?></label>
                                <input type="text" class="form-control" id="app_name" name="app_name" value="<?= escape($app['app_name']) ?>" required>
                            </div>
                            <div class="d-flex justify-content-between">
                                <button type="submit" name="update_app" class="btn btn-primary"><i class="fas fa-save"></i> <?= t('update') ?></button>
                                <a href="dashboard.php" class="btn btn-secondary"><i class="fas fa-times"></i> <?= t('cancel') ?></a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Regenerate App Token -->
                <div class="card shadow-sm">
                    <div class="card-header"><h5 class="mb-0"><i class="fas fa-key"></i> <?= t('change_token') ?></h5></div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label"><?= t('app_token') ?></label>
                                <code class="form-control text-monospace d-block mb-2"><?= escape($app['app_token']) ?></code>
                                <div class="alert alert-warning">
                                    <small><?= t('confirm_token_change_desc') ?></small>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="text" class="form-control" name="confirm_token" placeholder="<?= t('confirm_token_placeholder') ?>" required>
                                    <button type="submit" name="regenerate_token" class="btn btn-warning" style="white-space: nowrap;" onclick="return confirm('<?= t('confirm_token_action') ?>')">
                                        <i class="fas fa-sync-alt"></i> <?= t('regenerate_app_token') ?>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

    <script src="js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
    exit;
}

// If no action is specified, redirect to dashboard
redirect('dashboard.php');
?>
