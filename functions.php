<?php
function generateUUID() {
    return bin2hex(random_bytes(16));
}

function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function escape($html) {
    return htmlspecialchars($html, ENT_QUOTES, 'UTF-8');
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function currentUser($pdo) {
    if (!isLoggedIn()) return null;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function initializeLanguage() {
    if (isset($_GET['lang']) && in_array($_GET['lang'], ['fa', 'en'], true)) {
        $_SESSION['language'] = $_GET['lang'];
    }
    if (!isset($_SESSION['language'])) {
        $_SESSION['language'] = 'fa';
    }
}

function language() {
    return $_SESSION['language'] ?? 'fa';
}

function pageDirection() {
    return language() === 'fa' ? 'rtl' : 'ltr';
}

function languageUrl($language) {
    $query = $_GET;
    $query['lang'] = $language;
    return basename($_SERVER['PHP_SELF']) . '?' . http_build_query($query);
}

function languageSwitcher() {
    $current = language();
    return '<div class="language-switcher btn-group btn-group-sm" role="group" aria-label="Language selector">'
        . '<a class="btn ' . ($current === 'fa' ? 'btn-primary' : 'btn-outline-primary') . '" href="' . escape(languageUrl('fa')) . '">FA</a>'
        . '<a class="btn ' . ($current === 'en' ? 'btn-primary' : 'btn-outline-primary') . '" href="' . escape(languageUrl('en')) . '">EN</a>'
        . '</div>';
}

function themeSwitcher() {
    return '<button type="button" class="btn btn-outline-secondary btn-sm theme-toggle" aria-label="Toggle color theme" title="Toggle color theme">'
        . '<i class="fas fa-moon" aria-hidden="true"></i>'
        . '<span class="visually-hidden">Toggle color theme</span>'
        . '</button>';
}

function t($key, array $replace = []) {
    $translations = [
        'fa' => [
            'login' => 'ورود', 'register' => 'ثبت‌نام', 'username' => 'نام کاربری', 'password' => 'رمز عبور',
            'email_optional' => 'ایمیل (اختیاری)', 'security_code' => 'کد امنیتی', 'enter_code' => 'کد را وارد کنید',
            'dashboard' => 'داشبورد', 'back_dashboard' => 'بازگشت به داشبورد', 'edit_profile' => 'ویرایش پروفایل',
            'logout' => 'خروج', 'create_app' => 'ایجاد اپلیکیشن', 'app_name' => 'نام اپلیکیشن',
            'new_app' => 'افزودن اپلیکیشن جدید', 'my_apps' => 'اپلیکیشن‌های من', 'view' => 'مشاهده',
            'edit' => 'ویرایش', 'delete' => 'حذف', 'cancel' => 'انصراف', 'update' => 'به‌روزرسانی',
            'profile' => 'پروفایل کاربری', 'change_username' => 'تغییر نام کاربری', 'new_username' => 'نام کاربری جدید',
            'change_email' => 'تغییر ایمیل', 'new_email' => 'ایمیل جدید', 'change_password' => 'تغییر رمز عبور',
            'current_password' => 'رمز عبور فعلی', 'new_password' => 'رمز عبور جدید', 'confirm_password' => 'تکرار رمز عبور جدید',
            'save' => 'ذخیره', 'logs' => 'لاگ‌ها', 'devices' => 'دستگاه‌ها', 'analytics' => 'آمار',
            'api_docs' => 'مستندات API', 'manage_app' => 'مدیریت اپ', 'filter_logs' => 'فیلتر کردن لاگ‌ها',
            'filter' => 'فیلتر', 'clear' => 'پاک کردن', 'tag' => 'تگ', 'message' => 'متن پیام',
            'client' => 'شناسه کلاینت', 'from_date' => 'از تاریخ', 'to_date' => 'تا تاریخ',
            'actions' => 'عملیات', 'time' => 'زمان', 'no_logs' => 'هیچ لاگی یافت نشد.',
            'no_devices' => 'هیچ دستگاهی برای این اپلیکیشن ثبت نشده است.', 'copy_uuid' => 'کپی UUID اپ',
            'copied' => 'کپی شد', 'copy_failed' => 'کپی ناموفق بود', 'application_uuid' => 'UUID اپلیکیشن',
            'edit_app' => 'ویرایش اپلیکیشن', 'new_app_name' => 'نام جدید اپلیکیشن',
            'captcha_incorrect' => 'کد امنیتی اشتباه است.', 'invalid_login' => 'نام کاربری یا رمز عبور اشتباه است.',
            'credentials_short' => 'نام کاربری باید حداقل ۳ کاراکتر و رمز عبور باید حداقل ۶ کاراکتر باشد.',
            'registration_success' => 'ثبت‌نام با موفقیت انجام شد. اکنون می‌توانید وارد شوید.',
            'user_exists' => 'این نام کاربری یا ایمیل قبلاً استفاده شده است.', 'try_again' => 'خطایی رخ داد. لطفاً دوباره تلاش کنید.',
            'updated_successfully' => 'با موفقیت به‌روز شد.', 'name_required' => 'نام اپلیکیشن نمی‌تواند خالی باشد.',
            'no_apps' => 'هیچ اپلیکیشنی برای نمایش وجود ندارد. یکی جدید بسازید!', 'created_at' => 'ایجاد شده در',
        ],
        'en' => [
            'login' => 'Login', 'register' => 'Register', 'username' => 'Username', 'password' => 'Password',
            'email_optional' => 'Email (optional)', 'security_code' => 'Security code', 'enter_code' => 'Enter the code',
            'dashboard' => 'Dashboard', 'back_dashboard' => 'Back to dashboard', 'edit_profile' => 'Edit profile',
            'logout' => 'Logout', 'create_app' => 'Create application', 'app_name' => 'Application name',
            'new_app' => 'Add new application', 'my_apps' => 'My applications', 'view' => 'View',
            'edit' => 'Edit', 'delete' => 'Delete', 'cancel' => 'Cancel', 'update' => 'Update',
            'profile' => 'User profile', 'change_username' => 'Change username', 'new_username' => 'New username',
            'change_email' => 'Change email', 'new_email' => 'New email', 'change_password' => 'Change password',
            'current_password' => 'Current password', 'new_password' => 'New password', 'confirm_password' => 'Confirm new password',
            'save' => 'Save', 'logs' => 'Logs', 'devices' => 'Devices', 'analytics' => 'Analytics',
            'api_docs' => 'API documentation', 'manage_app' => 'Manage application', 'filter_logs' => 'Filter logs',
            'filter' => 'Filter', 'clear' => 'Clear', 'tag' => 'Tag', 'message' => 'Message',
            'client' => 'Client identifier', 'from_date' => 'From date', 'to_date' => 'To date',
            'actions' => 'Actions', 'time' => 'Time', 'no_logs' => 'No logs found.',
            'no_devices' => 'No devices have been registered for this application.', 'copy_uuid' => 'Copy application UUID',
            'copied' => 'Copied', 'copy_failed' => 'Copy failed', 'application_uuid' => 'Application UUID',
            'edit_app' => 'Edit application', 'new_app_name' => 'New application name',
            'captcha_incorrect' => 'The security code is incorrect.', 'invalid_login' => 'The username or password is incorrect.',
            'credentials_short' => 'Username must be at least 3 characters and password at least 6 characters.',
            'registration_success' => 'Registration completed successfully. You can now sign in.',
            'user_exists' => 'This username or email is already in use.', 'try_again' => 'An error occurred. Please try again.',
            'updated_successfully' => 'Updated successfully.', 'name_required' => 'Application name cannot be empty.',
            'no_apps' => 'There are no applications to display. Create one!', 'created_at' => 'Created at',
        ],
    ];
    $text = $translations[language()][$key] ?? $translations['fa'][$key] ?? $key;
    foreach ($replace as $name => $value) {
        $text = str_replace(':' . $name, $value, $text);
    }
    return $text;
}
?>
