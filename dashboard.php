<?php
require_once 'auth.php';
require_once 'functions.php';

// --- DATA & FORM LOGIC (UNCHANGED) ---
$stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
if (!$stmt->fetch()) {
    session_destroy();
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_app'])) {
    $app_name = trim($_POST['app_name']);
    if (!empty($app_name)) {
        $uuid = generateUUID();
        $stmt = $pdo->prepare("INSERT INTO apps (user_id, app_uuid, app_name) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $uuid, $app_name]);
        redirect('dashboard.php');
    }
}

$stmt = $pdo->prepare("SELECT * FROM apps WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$apps = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="<?= language() ?>" dir="<?= pageDirection() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('dashboard') ?> - LogStream</title>
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
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle"></i> <?= escape($_SESSION['username']) ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-edit"></i> <?= t('edit_profile') ?></a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> <?= t('logout') ?></a></li>
                    </ul>
                </li>
            </ul>
            <?= themeSwitcher() ?>
            <?= languageSwitcher() ?>
        </div>
    </div>
</nav>

<main class="container" style="padding-top: 80px;">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-tachometer-alt"></i> <?= t('dashboard') ?></h1>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-plus-circle"></i> <?= t('new_app') ?></h5>
                </div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label for="app_name" class="form-label"><?= t('app_name') ?></label>
                            <input type="text" class="form-control" id="app_name" name="app_name" placeholder="<?= language() === 'fa' ? 'مثلاً: اپلیکیشن فروشگاه' : 'For example: Store app' ?>" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="add_app" class="btn btn-primary"><i class="fas fa-check"></i> <?= t('create_app') ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list-ul"></i> <?= t('my_apps') ?></h5>
                </div>
                <div class="card-body">
                    <?php if (empty($apps)): ?>
                        <div class="alert alert-info text-center"><?= t('no_apps') ?></div>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($apps as $app): ?>
                                <div class="list-group-item list-group-item-action flex-column align-items-start">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1"><?= escape($app['app_name']) ?></h5>
                                        <small class="text-muted"><?= t('created_at') ?>: <?= date('Y-m-d', strtotime($app['created_at'])) ?></small>
                                    </div>
                                    <p class="mb-1 text-muted"><small>UUID: <code class="text-monospace"><?= escape($app['app_uuid']) ?></code></small></p>
                                    <div class="mt-2">
                                        <a href="view_app.php?app_uuid=<?= $app['app_uuid'] ?>" class="btn btn-sm btn-outline-primary" title="<?= t('logs') ?>"><i class="fas fa-eye"></i> <?= t('view') ?></a>
                                        <a href="apps.php?edit=<?= $app['id'] ?>" class="btn btn-sm btn-outline-secondary" title="<?= t('edit') ?>"><i class="fas fa-edit"></i> <?= t('edit') ?></a>
                                        <a href="apps.php?delete=<?= $app['id'] ?>" class="btn btn-sm btn-outline-danger" title="<?= t('delete') ?>" onclick="return confirm('<?= language() === 'fa' ? 'آیا از حذف این اپلیکیشن و تمام لاگ‌های آن مطمئن هستید؟' : 'Are you sure you want to delete this application and all of its logs?' ?>')"><i class="fas fa-trash-alt"></i> <?= t('delete') ?></a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
