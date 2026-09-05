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
            'login' => 'ورود', 'register' => 'ثبت‌نام', 'username' => 'نام کاربری', 'password' => 'رمز عبور', 'password_min_hint' => 'حداقل ۶ کاراکتر',
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
            'edit_app' => 'ویرایش اپلیکیشن', 'new_app_name' => 'نام جدید اپلیکیشن', 'regenerate_app_token' => 'تولید مجدد توکن برنامه', 'app_token_regenerated' => 'توکن برنامه با موفقیت تغییر یافت.',
            'captcha_incorrect' => 'کد امنیتی اشتباه است.', 'invalid_login' => 'نام کاربری یا رمز عبور اشتباه است.',
            'credentials_short' => 'نام کاربری باید حداقل ۳ کاراکتر و رمز عبور باید حداقل ۶ کاراکتر باشد.',
            'registration_success' => 'ثبت‌نام با موفقیت انجام شد. اکنون می‌توانید وارد شوید.',
            'user_exists' => 'این نام کاربری یا ایمیل قبلاً استفاده شده است.', 'try_again' => 'خطایی رخ داد. لطفاً دوباره تلاش کنید.',
            'delete_app_confirm' => 'آیا از حذف این اپلیکیشن و تمام لاگ‌های آن مطمئن هستید؟',
            'updated_successfully' => 'با موفقیت به‌روز شد.', 'name_required' => 'نام اپلیکیشن نمی‌تواند خالی باشد.',
            'no_apps' => 'هیچ اپلیکیشنی برای نمایش وجود ندارد. یکی جدید بسازید!', 'created_at' => 'ایجاد شده در',
            'app_name_placeholder' => 'مثلاً: اپلیکیشن فروشگاه',
            'username_updated' => 'نام کاربری با موفقیت به‌روز شد.',
            'username_taken' => 'این نام کاربری قبلاً انتخاب شده است.',
            'username_error' => 'خطا در به‌روزرسانی نام کاربری.',
            'username_min' => 'نام کاربری باید حداقل ۳ کاراکتر باشد.',
            'wrong_password' => 'رمز عبور فعلی شما صحیح نیست.',
            'password_min' => 'رمز عبور جدید باید حداقل ۶ کاراکتر باشد.',
            'password_mismatch' => 'تکرار رمز عبور جدید مطابقت ندارد.',
            'password_updated' => 'رمز عبور با موفقیت تغییر یافت.',
            'email_updated' => 'ایمیل با موفقیت به‌روز شد.',
            'email_taken' => 'این ایمیل قبلاً توسط کاربر دیگری ثبت شده است.',
            'email_error' => 'خطا در به‌روزرسانی ایمیل.',
            'email_invalid' => 'فرمت ایمیل وارد شده نامعتبر است.',
            'my_token' => 'توکن من', 'copy_token' => 'کپی توکن', 'change_token' => 'تغییر توکن',
            'generate_new_token' => 'تولید توکن جدید', 'token_copied' => 'توکن کپی شد', 'app_token' => 'توکن برنامه',
            'user_token' => 'توکن کاربر',
            'confirm_token_change' => 'تولید توکن جدید', 'confirm_token_change_desc' => 'توجه: توکن قبلی دیگر کار نخواهد کرد. برای تایید عبارت "تغییر توکن" را بنویسید.',
            'confirm_token_change_placeholder' => 'تغییر توکن را تایپ کنید', 'confirm_token_placeholder' => 'تغییر توکن را تایپ کنید', 'token_change_success' => 'توکن با موفقیت تغییر یافت.',
            'delete_log_confirm' => 'آیا مطمئن هستید؟', 'api_error_docs' => 'به مستندات رسمی مراجعه فرمایید',
            'confirm_invalid' => 'عبارت تایید را به درستی وارد نکردهاید.',
            'confirm_token_action' => 'آیا مطمئن هستید که میخواهید توکن جدید تولید کنید؟ توکن قبلی دیگر کار نخواهد کرد.',
            'showing_logs' => 'نمایش :count از :total لاگ',
            'no_devices_app' => 'هیچ دستگاهی برای این اپلیکیشن ثبت نشده است.',
            'client_id' => 'شناسه کلاینت', 'os_type' => 'نوع سیستمعامل', 'os_version' => 'نسخه سیستمعامل',
            'device_model' => 'مدل دستگاه', 'last_update' => 'آخرین بروزرسانی',
            'api_requests_info' => 'درخواستها باید با روش :method و فرمت :format ارسال شوند.',
            'base_url' => 'آدرس پایه', 'for_each_request' => 'برای هر درخواست، توکن همین اپ را در پارامتر :param ارسال کنید.',
            'required' => 'الزامی', 'description' => 'توضیح', 'optional' => 'خیر',
            'log_param_desc' => 'متن لاگ', 'client_param_desc' => 'شناسه یکتای کلاینت یا کاربر',
            'tag_param_desc' => 'برچسب لاگ؛ مقدار پیشفرض :default', 'curl_sample' => 'نمونه درخواست cURL',
            'success_response' => 'پاسخ موفق', 'register_log' => 'ثبت لاگ', 'register_device' => 'ثبت یا بهروزرسانی دستگاه',
            'device_param_desc' => 'شناسه یکتای دستگاه/کلاینت', 'os_type_param_desc' => 'نوع سیستمعامل، مانند :example',
            'responses_errors' => 'پاسخها و خطاها', 'success_code' => 'درخواست با موفقیت انجام شد.',
            'missing_params' => 'پارامترهای اجباری ارسال نشدهاند.', 'invalid_token' => 'مقدار :param معتبر نیست.',
            'wrong_method' => 'روش درخواست باید :method باشد.',
            'overall_os_percent' => 'درصد کلی سیستمعاملها', 'overall_model_percent' => 'درصد کلی مدلهای دستگاه (۱۵ مدل برتر)',
            'os_stats' => 'آمار سیستمعامل:', 'version_percent' => 'درصد نسخهها', 'model_percent' => 'درصد مدلهای دستگاه (۱۰ مدل برتر)',
            'delete_log' => 'حذف لاگ', 'no_data' => 'داده‌ای برای نمایش وجود ندارد.',
            'log_uuid'=> 'یونیک آیدی لاگ',
        ],
        'en' => [
            'login' => 'Login', 'register' => 'Register', 'username' => 'Username', 'password' => 'Password', 'password_min_hint' => 'at least 6 characters',
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
            'edit_app' => 'Edit application', 'new_app_name' => 'New application name', 'regenerate_app_token' => 'Regenerate app token', 'app_token_regenerated' => 'App token changed successfully.',
            'captcha_incorrect' => 'The security code is incorrect.', 'invalid_login' => 'The username or password is incorrect.',
            'credentials_short' => 'Username must be at least 3 characters and password at least 6 characters.',
            'registration_success' => 'Registration completed successfully. You can now sign in.',
            'user_exists' => 'This username or email is already in use.', 'try_again' => 'An error occurred. Please try again.',
            'delete_app_confirm' => 'Are you sure you want to delete this application and all of its logs?',
            'updated_successfully' => 'Updated successfully.', 'name_required' => 'Application name cannot be empty.',
            'no_apps' => 'There are no applications to display. Create one!', 'created_at' => 'Created at',
            'app_name_placeholder' => 'For example: Store app',
            'username_updated' => 'Username updated successfully.',
            'username_taken' => 'This username is already taken.',
            'username_error' => 'Error updating username.',
            'username_min' => 'Username must be at least 3 characters.',
            'wrong_password' => 'Current password is incorrect.',
            'password_min' => 'New password must be at least 6 characters.',
            'password_mismatch' => 'New password confirmation does not match.',
            'password_updated' => 'Password changed successfully.',
            'email_updated' => 'Email updated successfully.',
            'email_taken' => 'This email is already registered by another user.',
            'email_error' => 'Error updating email.',
            'email_invalid' => 'Invalid email format.',
            'my_token' => 'My Token', 'copy_token' => 'Copy token', 'change_token' => 'Change Token',
            'generate_new_token' => 'Generate new token', 'token_copied' => 'Token copied', 'app_token' => 'App Token',
            'user_token' => 'User Token',
            'confirm_token_change' => 'Generate new token', 'confirm_token_change_desc' => 'Note: The old token will no longer work. Type "confirm" to proceed.',
            'confirm_token_change_placeholder' => 'Type confirm', 'confirm_token_placeholder' => 'Type confirm', 'token_change_success' => 'Token changed successfully.',
            'delete_log_confirm' => 'Are you sure?', 'api_error_docs' => 'Please refer to the official documentation',
            'confirm_invalid' => 'You have not entered the confirmation correctly.',
            'confirm_token_action' => 'Are you sure you want to generate a new token? The old token will no longer work.',
            'showing_logs' => 'Showing :count of :total logs',
            'no_devices_app' => 'No devices have been registered for this application.',
            'client_id' => 'Client ID', 'os_type' => 'OS Type', 'os_version' => 'OS Version',
            'device_model' => 'Device Model', 'last_update' => 'Last Update',
            'api_requests_info' => 'Requests must be sent with :method method and :format format.',
            'base_url' => 'Base URL', 'for_each_request' => 'For each request, send the app token in the :param parameter.',
            'required' => 'Required', 'description' => 'Description', 'optional' => 'Optional',
            'log_param_desc' => 'Log message', 'client_param_desc' => 'Unique client or user identifier',
            'tag_param_desc' => 'Log tag; default is :default', 'curl_sample' => 'cURL request sample',
            'success_response' => 'Success response', 'register_log' => 'Register Log', 'register_device' => 'Register or Update Device',
            'device_param_desc' => 'Unique device/client identifier', 'os_type_param_desc' => 'OS type, like :example',
            'responses_errors' => 'Responses and Errors', 'success_code' => 'Request completed successfully.',
            'missing_params' => 'Required parameters are not sent.', 'invalid_token' => 'The :param value is invalid.',
            'wrong_method' => 'Request method must be :method.',
            'overall_os_percent' => 'Overall OS Percentage', 'overall_model_percent' => 'Overall Device Models (Top 15)',
            'os_stats' => 'OS Statistics:', 'version_percent' => 'Version Percentage', 'model_percent' => 'Device Models (Top 10)',
            'delete_log' => 'Delete Log', 'no_data' => 'No data to display',
            'log_uuid'=> 'Log UUID',
        ],
    ];
    $text = $translations[language()][$key] ?? $translations['fa'][$key] ?? $key;
    foreach ($replace as $name => $value) {
        $text = str_replace(':' . $name, $value, $text);
    }
    return $text;
}
?>
