<?php
require_once 'auth.php';
require_once 'functions.php';

$stmt = $pdo->prepare("SELECT id, user_token FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$current_user = $stmt->fetch();
if (!$current_user) {
    session_destroy();
    redirect('index.php');
}
$user_token = $current_user['user_token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_app'])) {
    $app_name = trim($_POST['app_name']);
    if (!empty($app_name)) {
        $uuid = generateUUID();
        $stmt = $pdo->prepare("INSERT INTO apps (user_id, app_token, app_name) VALUES (?, ?, ?)");
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
    <link rel="stylesheet" href="css/style.css">
    <script src="js/theme.js"></script>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg bg-light fixed-top shadow-sm">
    <div class="container-fluid">

        <a class="navbar-brand" href="dashboard.php"><i class="fas fa-stream"></i> LogStream</a>
        <?php if ($user_token): ?>
        <div class="navbar-text me-3">
            <button type="button" class="btn btn-sm btn-outline-primary" id="copyToken" title="<?= t('copy_token') ?>">
                <i class="fas fa-key"></i> <span class="d-none d-lg-inline"><?= t('my_token') ?>:</span> <code class="text-monospace" style="color: var(--bs-body-color);"><?= escape(substr($user_token, 0, 8)) ?>...</code>
                <i class="fas fa-copy ms-1"></i>
            </button>
        </div>
        <?php endif; ?>
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
                            <input type="text" class="form-control" id="app_name" name="app_name" placeholder="<?= t('app_name_placeholder') ?>" required>
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
                                    <p class="mb-1 text-muted"><small><?= t('app_token') ?>: <code class="text-monospace"><?= escape($app['app_token']) ?></code></small></p>
                                    <div class="mt-2">
                                        <a href="view_app.php?app_token=<?= $app['app_token'] ?>" class="btn btn-sm btn-outline-primary" title="<?= t('logs') ?>"><i class="fas fa-eye"></i> <?= t('view') ?></a>
                                        <a href="apps.php?edit=<?= $app['id'] ?>" class="btn btn-sm btn-outline-secondary" title="<?= t('edit') ?>"><i class="fas fa-edit"></i> <?= t('edit') ?></a>
                                        <a href="apps.php?delete=<?= $app['id'] ?>" class="btn btn-sm btn-outline-danger" title="<?= t('delete') ?>" onclick="return confirm('<?= t('delete_app_confirm') ?>')"><i class="fas fa-trash-alt"></i> <?= t('delete') ?></a>
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
<script>
    const fullToken = '<?= escape($user_token) ?>';
    document.getElementById('copyToken')?.addEventListener('click', async function () {
        const button = this;
        try {
            if (navigator.clipboard) {
                await navigator.clipboard.writeText(fullToken);
            } else {
                const input = document.createElement('input');
                input.value = fullToken;
                document.body.appendChild(input);
                input.select();
                document.execCommand('copy');
                input.remove();
            }
            button.innerHTML = '<i class="fas fa-check"></i> <span class="d-none d-lg-inline"><?= t("my_token") ?>:</span> <code class="text-monospace" style="color: var(--bs-body-color);"><?= escape(substr($user_token, 0, 8)) ?>...</code> <i class="fas fa-copy ms-1"></i>';
            setTimeout(function () {
                button.innerHTML = '<i class="fas fa-key"></i> <span class="d-none d-lg-inline"><?= t("my_token") ?>:</span> <code class="text-monospace" style="color: var(--bs-body-color);"><?= escape(substr($user_token, 0, 8)) ?>...</code> <i class="fas fa-copy ms-1"></i>';
            }, 1500);
        } catch (error) {}
    });
</script>
</body>
</html>
