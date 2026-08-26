<?php
require_once 'auth.php';

// --- DATA RETRIEVAL LOGIC (UNCHANGED) ---
$app_uuid = $_GET['app_uuid'] ?? '';
if (!$app_uuid) {
    redirect('dashboard.php');
}

$stmt = $pdo->prepare("SELECT * FROM apps WHERE app_uuid = ? AND user_id = ?");
$stmt->execute([$app_uuid, $_SESSION['user_id']]);
$app = $stmt->fetch();
if (!$app) {
    redirect('dashboard.php');
}

$current_view = $_GET['view'] ?? 'logs';
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$base_path = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$api_base_url = $scheme . '://' . $_SERVER['HTTP_HOST'] . $base_path;
$app_id = $app['id'];
$page = (int)($_GET['page'] ?? 1);
$filter_params = [];

if ($current_view === 'logs') {
    if (isset($_GET['delete_log'])) {
        $log_id = (int)$_GET['delete_log'];
        $check_stmt = $pdo->prepare("SELECT id FROM logs WHERE id = ? AND app_id = ?");
        $check_stmt->execute([$log_id, $app_id]);
        if ($check_stmt->rowCount() > 0) {
            $del_stmt = $pdo->prepare("DELETE FROM logs WHERE id = ?");
            $del_stmt->execute([$log_id]);
        }
        $redirect_url = "view_app.php?app_uuid=" . urlencode($app_uuid) . "&view=logs";
        $query_params = $_GET;
        unset($query_params['delete_log']);
        if (!empty($query_params)) {
            $redirect_url .= '&' . http_build_query($query_params);
        }
        redirect($redirect_url);
    }

    $tag = $_GET['tag'] ?? '';
    $message = $_GET['message'] ?? '';
    $client = $_GET['client_identifier'] ?? '';
    $log_uuid = $_GET['log_uuid'] ?? '';
    $from = $_GET['from'] ?? '';
    $to = $_GET['to'] ?? '';
    $perPage = 50;
    $offset = ($page - 1) * $perPage;

    $sql = "SELECT * FROM logs WHERE app_id = ?";
    $params = [$app_id];

    if ($tag) { $sql .= " AND tag LIKE ?"; $params[] = "%$tag%"; $filter_params['tag'] = $tag; }
    if ($message) { $sql .= " AND message LIKE ?"; $params[] = "%$message%"; $filter_params['message'] = $message; }
    if ($client) { $sql .= " AND client_identifier = ?"; $params[] = $client; $filter_params['client_identifier'] = $client; }
    if ($log_uuid) { $sql .= " AND log_uuid = ?"; $params[] = $log_uuid; $filter_params['log_uuid'] = $log_uuid; }
    if ($from) { $sql .= " AND created_at >= ?"; $params[] = $from; $filter_params['from'] = $from; }
    if ($to) { $sql .= " AND created_at <= ?"; $params[] = $to . ' 23:59:59'; $filter_params['to'] = $to; }

    $countSql = str_replace("SELECT *", "SELECT COUNT(*)", $sql);
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $total = $countStmt->fetchColumn();

    $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) { $stmt->bindValue($key + 1, $value); }
    $stmt->bindValue(count($params) + 1, $perPage, PDO::PARAM_INT);
    $stmt->bindValue(count($params) + 2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $logs = $stmt->fetchAll();
    $totalPages = ceil($total / $perPage);
}
elseif ($current_view === 'devices') {
    $stmt = $pdo->prepare("SELECT * FROM devices WHERE app_id = ? ORDER BY updated_at DESC");
    $stmt->execute([$app_id]);
    $devices = $stmt->fetchAll();
}
elseif ($current_view === 'analytics') {
    $os_stats_stmt = $pdo->prepare("SELECT os_type, COUNT(*) as count FROM devices WHERE app_id = ? AND os_type IS NOT NULL GROUP BY os_type");
    $os_stats_stmt->execute([$app_id]);
    $os_stats = $os_stats_stmt->fetchAll();

    $distinct_os_stmt = $pdo->prepare("SELECT DISTINCT os_type FROM devices WHERE app_id = ? AND os_type IS NOT NULL");
    $distinct_os_stmt->execute([$app_id]);
    $distinct_os_types = $distinct_os_stmt->fetchAll(PDO::FETCH_COLUMN);

    $os_version_stats = [];
    foreach ($distinct_os_types as $os_type) {
        $stmt = $pdo->prepare("SELECT os_version, COUNT(*) as count FROM devices WHERE app_id = ? AND os_type = ? AND os_version IS NOT NULL GROUP BY os_version");
        $stmt->execute([$app_id, $os_type]);
        $os_version_stats[$os_type] = $stmt->fetchAll();
    }

    $os_model_stats = [];
    foreach ($distinct_os_types as $os_type) {
        $stmt = $pdo->prepare("SELECT device_model, COUNT(*) as count FROM devices WHERE app_id = ? AND os_type = ? AND device_model IS NOT NULL GROUP BY device_model ORDER BY count DESC LIMIT 10");
        $stmt->execute([$app_id, $os_type]);
        $os_model_stats[$os_type] = $stmt->fetchAll();
    }

    $overall_model_stats_stmt = $pdo->prepare("SELECT device_model, COUNT(*) as count FROM devices WHERE app_id = ? AND device_model IS NOT NULL GROUP BY device_model ORDER BY count DESC LIMIT 15");
    $overall_model_stats_stmt->execute([$app_id]);
    $overall_model_stats = $overall_model_stats_stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="<?= language() ?>" dir="<?= pageDirection() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('manage_app') ?>: <?= escape($app['app_name']) ?></title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=3">
    <script src="js/theme.js"></script>
    <style>
        body {
            padding-top: 70px; /* To offset for fixed navbar */
        }
        .dropdown:hover .dropdown-menu {
            display: block;
            margin-top: 0;
        }
        .app-uuid {
            direction: ltr;
            font-size: .8rem;
            flex: 1 1 auto;
            min-width: 0;
        }
        .app-uuid-container {
            min-width: 0;
            margin: .5rem 0;
        }
        .app-uuid-container code {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .navbar-actions {
            gap: 0;
        }
        @media (min-width: 992px) {
            .app-uuid-container {
                margin: 0 1rem;
            }
            .navbar-actions {
                margin-inline-start: auto;
            }
        }
        .api-code {
            direction: ltr;
            text-align: left;
            white-space: pre-wrap;
            font-size: .8rem;
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg bg-light fixed-top shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand" href="#"><i class="fas fa-cogs"></i> <?= t('manage_app') ?>: <?= escape($app['app_name']) ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php
                        if ($current_view == "logs")
                            echo t('logs');
                        elseif ($current_view == "devices")
                            echo t('devices');
                        elseif ($current_view == "analytics")
                            echo t('analytics');
                        elseif ($current_view == "documentation")
                            echo t('api_docs');
                        ?>
                    </a>
                    <ul class="dropdown-menu text-end" aria-labelledby="navbarDropdown">
                        <li>
                            <a class="dropdown-item <?= $current_view === 'logs' ? 'active' : '' ?>" href="?app_uuid=<?= escape($app_uuid) ?>&view=logs">
                                <?= t('logs') ?>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item <?= $current_view === 'devices' ? 'active' : '' ?>" href="?app_uuid=<?= escape($app_uuid) ?>&view=devices">
                                <?= t('devices') ?>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item <?= $current_view === 'analytics' ? 'active' : '' ?>" href="?app_uuid=<?= escape($app_uuid) ?>&view=analytics">
                                <?= t('analytics') ?>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item <?= $current_view === 'documentation' ? 'active' : '' ?>" href="?app_uuid=<?= escape($app_uuid) ?>&view=documentation">
                                <?= t('api_docs') ?>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
            <div class="app-uuid-container navbar-text d-flex align-items-center gap-2">
                <button type="button" id="copyAppUuid" class="btn btn-sm btn-outline-secondary" aria-label="<?= t('copy_uuid') ?>" title="<?= t('copy_uuid') ?>">
                    <i class="fas fa-copy"></i>
                </button>
                <code id="appUuid" class="app-uuid text-dark"><?= escape($app_uuid) ?></code>
                <span class="text-muted small" dir="ltr"><?= t('application_uuid') ?>:</span>
            </div>
            <div class="navbar-actions d-flex flex-column flex-lg-row align-items-lg-center">
                <a class="nav-link me-2 ms-2" href="dashboard.php"><i class="fas fa-arrow-left"></i> <?= t('back_dashboard') ?></a>
                <?= themeSwitcher() ?>
                <?= languageSwitcher() ?>
            </div>
        </div>
    </div>
</nav>

<main class="container-fluid mt-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <?php
            if ($current_view === 'logs') echo '<i class="fas fa-clipboard-list"></i> ' . t('logs');
            elseif ($current_view === 'devices') echo '<i class="fas fa-mobile-alt"></i> ' . t('devices');
            elseif ($current_view === 'analytics') echo '<i class="fas fa-chart-pie"></i> ' . t('analytics');
            elseif ($current_view === 'documentation') echo '<i class="fas fa-book"></i> ' . t('api_docs');
            ?>
        </h1>
    </div>

    <?php if ($current_view === 'logs'): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-filter"></i> <?= t('filter_logs') ?></h5>
            </div>
            <div class="card-body">
                <form method="get" class="row g-3 align-items-end">
                    <input type="hidden" name="app_uuid" value="<?= escape($app_uuid) ?>">
                    <input type="hidden" name="view" value="logs">
                    <div class="col-md-4"><input type="text" name="tag" class="form-control" placeholder="<?= t('tag') ?>" value="<?= escape($tag ?? '') ?>"></div>
                    <div class="col-md-4"><input type="text" name="message" class="form-control" placeholder="<?= t('message') ?>" value="<?= escape($message ?? '') ?>"></div>
                    <div class="col-md-4"><input type="text" name="client_identifier" class="form-control" placeholder="<?= t('client') ?>" value="<?= escape($client ?? '') ?>"></div>
                    <div class="col-md-4"><input type="text" name="log_uuid" class="form-control" placeholder="UUID <?= t('logs') ?>" value="<?= escape($log_uuid ?? '') ?>"></div>
                    <div class="col-md-2"><input type="date" name="from" class="form-control" title="<?= t('from_date') ?>" value="<?= escape($from ?? '') ?>"></div>
                    <div class="col-md-2"><input type="date" name="to" class="form-control" title="<?= t('to_date') ?>" value="<?= escape($to ?? '') ?>"></div>
                    <div class="col-md-4 d-flex">
                        <button type="submit" class="btn btn-primary me-2"><i class="fas fa-search"></i> <?= t('filter') ?></button>
                        <a style="margin-right: 8px" href="view_app.php?app_uuid=<?= escape($app_uuid) ?>&view=logs" class="btn btn-secondary"><i class="fas fa-undo"></i> <?= t('clear') ?></a>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <?php if (empty($logs)): ?>
                <div class="alert alert-info text-center"><i class="fas fa-info-circle"></i> <?= t('no_logs') ?></div>
            <?php else: ?>
                <p class="text-muted">نمایش <?= count($logs) ?> از <?= $total ?> لاگ</p>
                <table class="table table-striped table-hover table-bordered table-sm align-middle">
                    <thead class="table-dark">
                    <tr>
                        <th>UUID</th><th>کلاینت</th><th>تگ</th><th>پیام</th><th>IP</th><th>زمان</th><th>عملیات</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><small class="text-monospace"><?= escape($log['log_uuid']) ?></small></td>
                            <td><?= escape($log['client_identifier']) ?></td>
                            <td><span class="badge bg-<?php
                                    if ( $log['tag'] == "info" ) echo 'info';
                                    else if ( $log['tag'] == "debug" ) echo 'success';
                                    else if ( $log['tag'] == "notice" ) echo 'warning';
                                    else if ( $log['tag'] == "warning" ) echo 'warning';
                                    else if ( $log['tag'] == "alert" ) echo 'danger';
                                    else if ( $log['tag'] == "error" ) echo 'danger';
                                    else if ( $log['tag'] == "critical" ) echo 'dark';
                                    else if ( $log['tag'] == "emergency" ) echo 'dark';
                                    else echo 'secondary';
                                ?>"><?= escape($log['tag']) ?></span></td>
                            <td class="log-message"><?= nl2br(escape($log['message'])) ?></td>
                            <td><?= escape($log['ip_address']) ?></td>
                            <td><small><?= $log['created_at'] ?></small></td>
                            <td>
                                <a href="view_app.php?view=logs&delete_log=<?= $log['id'] ?>&<?= http_build_query(array_merge(['app_uuid' => $app_uuid], $filter_params, ['page' => $page])) ?>"
                                   onclick="return confirm('آیا مطمئن هستید؟')" class="btn btn-sm btn-outline-danger" title="حذف لاگ">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    <?php elseif ($current_view === 'devices'): ?>
        <div class="table-responsive">
            <?php if (empty($devices)): ?>
                <div class="alert alert-info text-center"><i class="fas fa-info-circle"></i> هیچ دستگاهی برای این اپلیکیشن ثبت نشده است.</div>
            <?php else: ?>
                <table class="table table-striped table-hover table-bordered align-middle">
                    <thead class="table-dark">
                    <tr>
                        <th>شناسه کلاینت</th><th>نوع سیستم‌عامل</th><th>نسخه سیستم‌عامل</th><th>مدل دستگاه</th><th>آخرین بروزرسانی</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($devices as $device): ?>
                        <tr>
                            <td><?= escape($device['client_identifier']) ?></td>
                            <td><?= escape($device['os_type']) ?></td>
                            <td><?= escape($device['os_version']) ?></td>
                            <td><?= escape($device['device_model']) ?></td>
                            <td><small><?= escape($device['updated_at']) ?></small></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    <?php elseif ($current_view === 'documentation'): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            درخواست‌ها باید با روش <code dir="ltr">POST</code> و فرمت <code dir="ltr">application/x-www-form-urlencoded</code> ارسال شوند.
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header"><i class="fas fa-link"></i> آدرس پایه</div>
            <div class="card-body">
                <code class="api-code d-block"><?= escape($api_base_url) ?></code>
                <small class="text-muted">برای هر درخواست، UUID همین اپ را در پارامتر <code>app_uuid</code> ارسال کنید.</small>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-clipboard-list"></i> ثبت لاگ</span>
                <span class="badge bg-primary">POST</span>
            </div>
            <div class="card-body p-3">
                <p class="mb-2"><code class="api-code d-inline-block"><?= escape($api_base_url) ?>/api/log.php</code></p>
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light"><tr><th>پارامتر</th><th>الزامی</th><th>توضیح</th></tr></thead>
                        <tbody>
                            <tr><td><code>app_uuid</code></td><td>بله</td><td>UUID اپلیکیشن</td></tr>
                            <tr><td><code>message</code></td><td>بله</td><td>متن لاگ</td></tr>
                            <tr><td><code>client_identifier</code></td><td>بله</td><td>شناسه یکتای کلاینت یا کاربر</td></tr>
                            <tr><td><code>tag</code></td><td>خیر</td><td>برچسب لاگ؛ مقدار پیش‌فرض <code>general</code></td></tr>
                        </tbody>
                    </table>
                </div>
                <p class="mb-1 fw-bold">نمونه درخواست cURL</p>
                <pre class="api-code bg-dark text-light rounded p-2 mb-2"><code>curl -X POST "<?= escape($api_base_url) ?>/api/log.php" \
  -d "app_uuid=<?= escape($app_uuid) ?>" \
  -d "message=عملیات با موفقیت انجام شد" \
  -d "tag=info" \
  -d "client_identifier=user-123"</code></pre>
                <p class="mb-1 fw-bold">پاسخ موفق</p>
                <pre class="api-code bg-light border rounded p-2 mb-0"><code>{"status":"success","log_uuid":"شناسه-لاگ"}</code></pre>
            </div>
        </div>
            </div>

            <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-mobile-alt"></i> ثبت یا به‌روزرسانی دستگاه</span>
                <span class="badge bg-primary">POST</span>
            </div>
            <div class="card-body p-3">
                <p class="mb-2"><code class="api-code d-inline-block"><?= escape($api_base_url) ?>/api/device.php</code></p>
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light"><tr><th>پارامتر</th><th>الزامی</th><th>توضیح</th></tr></thead>
                        <tbody>
                            <tr><td><code>app_uuid</code></td><td>بله</td><td>UUID اپلیکیشن</td></tr>
                            <tr><td><code>client_identifier</code></td><td>بله</td><td>شناسه یکتای دستگاه/کلاینت</td></tr>
                            <tr><td><code>os_type</code></td><td>بله</td><td>نوع سیستم‌عامل، مانند <code>Android</code></td></tr>
                            <tr><td><code>os_version</code></td><td>بله</td><td>نسخه سیستم‌عامل</td></tr>
                            <tr><td><code>device_model</code></td><td>بله</td><td>مدل دستگاه</td></tr>
                        </tbody>
                    </table>
                </div>
                <p class="mb-1 fw-bold">نمونه درخواست cURL</p>
                <pre class="api-code bg-dark text-light rounded p-2 mb-0"><code>curl -X POST "<?= escape($api_base_url) ?>/api/device.php" \
  -d "app_uuid=<?= escape($app_uuid) ?>" \
  -d "client_identifier=device-001" \
  -d "os_type=Android" \
  -d "os_version=14" \
  -d "device_model=Pixel 8"</code></pre>
                <p class="mt-2 fw-bold">پاسخ موفق</p>
                <pre class="api-code bg-light border rounded p-2 mb-0"><code>{"status":"success","message":"Device information saved successfully"}</code></pre>
            </div>
        </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header"><i class="fas fa-circle-exclamation"></i> پاسخ‌ها و خطاها</div>
            <div class="card-body">
                <ul class="mb-0">
                    <li><code>200</code>: درخواست با موفقیت انجام شد.</li>
                    <li><code>400</code>: پارامترهای اجباری ارسال نشده‌اند.</li>
                    <li><code>404</code>: مقدار <code>app_uuid</code> معتبر نیست.</li>
                    <li><code>405</code>: روش درخواست باید <code>POST</code> باشد.</li>
                </ul>
            </div>
        </div>

    <?php elseif ($current_view === 'analytics'): ?>
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header">درصد کلی سیستم‌عامل‌ها</div>
                    <div class="card-body"><canvas id="osChart"></canvas></div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header">درصد کلی مدل‌های دستگاه (۱۵ مدل برتر)</div>
                    <div class="card-body"><canvas id="overallModelChart"></canvas></div>
                </div>
            </div>
        </div>

        <?php foreach ($distinct_os_types as $os_type): ?>
            <h3 class="h4 mt-4 mb-3"><i class="fab fa-<?php
                if ( $os_type == "ios" || $os_type == "mac" ) echo "apple";
                else echo strtolower(escape($os_type)) ?? 'question-circle';
                ?>"></i> آمار سیستم‌عامل: <?= escape($os_type) ?></h3>
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header">درصد نسخه‌ها</div>
                        <div class="card-body"><canvas id="osVersionChart-<?= escape($os_type) ?>"></canvas></div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header">درصد مدل‌های دستگاه (۱۰ مدل برتر)</div>
                        <div class="card-body"><canvas id="osModelChart-<?= escape($os_type) ?>"></canvas></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</main>

<script src="js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('copyAppUuid').addEventListener('click', async function () {
        const button = this;
        const appUuid = document.getElementById('appUuid').textContent.trim();

        try {
            if (navigator.clipboard) {
                await navigator.clipboard.writeText(appUuid);
            } else {
                const input = document.createElement('input');
                input.value = appUuid;
                document.body.appendChild(input);
                input.select();
                document.execCommand('copy');
                input.remove();
            }

            button.innerHTML = '<i class="fas fa-check"></i>';
            button.classList.replace('btn-outline-secondary', 'btn-success');
            button.title = 'کپی شد';
            setTimeout(function () {
                button.innerHTML = '<i class="fas fa-copy"></i>';
                button.classList.replace('btn-success', 'btn-outline-secondary');
                button.title = 'کپی UUID اپ';
            }, 1500);
        } catch (error) {
            button.title = 'کپی ناموفق بود';
        }
    });
</script>
<?php if ($current_view === 'analytics'): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // --- CHART.JS LOGIC (MODIFIED FOR BOOTSTRAP) ---
    const dashboardCharts = [];

    function chartTextColor() {
        return getComputedStyle(document.documentElement).getPropertyValue('--bs-body-color').trim() || '#212529';
    }

    function updateChartTheme() {
        const color = chartTextColor();
        dashboardCharts.forEach((chart) => {
            chart.options.color = color;
            chart.options.plugins.legend.labels.color = color;
            chart.update();
        });
    }

    function generateColors(num) {
        const palette = [
            '#ef476f', '#06d6a0', '#81c3d7', '#ffd166',
            '#0177B6', '#84A98C', '#365053', '#CAF0F8',
            '#E9C36B', '#FDF0D5', '#9F86C0', '#80ED9A',
            '#F48D07', '#d90429', '#d9ed92'
        ];
        const colors = [];
        for (let i = 0; i < num; i++) {
            colors.push(palette[i % palette.length]);
        }
        return colors;
    }

    function createPieChart(canvasId, chartData, chartLabel) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;
        if (!chartData || !chartData.labels || chartData.labels.length === 0) {
            ctx.parentElement.innerHTML = '<div class="alert alert-warning text-center">داده‌ای برای نمایش وجود ندارد.</div>';
            return;
        }

        const chart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: chartLabel,
                    data: chartData.values,
                    backgroundColor: generateColors(chartData.values.length),
                    hoverOffset: 4
                }]
            },
            options: {
                color: chartTextColor(),
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: chartTextColor(),
                            font: { family: "'Vazirmatn', sans-serif" }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) { label += ': '; }
                                if (context.parsed !== null) {
                                    const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed / total) * 100).toFixed(2) + '%';
                                    label += `${context.raw} (${percentage})`;
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
        dashboardCharts.push(chart);
    }

    window.addEventListener('logstream-theme-change', updateChartTheme);

    // Init charts
    createPieChart('osChart', {
        labels: <?= json_encode(array_column($os_stats, 'os_type')) ?>,
        values: <?= json_encode(array_column($os_stats, 'count')) ?>
    }, 'سیستم‌عامل');

    createPieChart('overallModelChart', {
        labels: <?= json_encode(array_column($overall_model_stats, 'device_model')) ?>,
        values: <?= json_encode(array_column($overall_model_stats, 'count')) ?>
    }, 'مدل دستگاه');

    <?php foreach ($distinct_os_types as $os_type):
        $js_os_type = str_replace(['-', '.'], '_', escape($os_type));
    ?>
        createPieChart('osVersionChart-<?= escape($os_type) ?>', {
            labels: <?= json_encode(array_column($os_version_stats[$os_type], 'os_version')) ?>,
            values: <?= json_encode(array_column($os_version_stats[$os_type], 'count')) ?>
        }, 'نسخه');

        createPieChart('osModelChart-<?= escape($os_type) ?>', {
            labels: <?= json_encode(array_column($os_model_stats[$os_type], 'device_model')) ?>,
            values: <?= json_encode(array_column($os_model_stats[$os_type], 'count')) ?>
        }, 'مدل');
    <?php endforeach; ?>
</script>
<?php endif; ?>
</body>
</html>
