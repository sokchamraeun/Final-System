<?php
declare(strict_types=1);
/* Settings — key-value configuration page. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'settings';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'settings';
$errors    = [];
$settings  = [];

/* Load current settings from DB. */
$rows = Database::instance()->all("SELECT setting_key, setting_value FROM settings");
$settings = [];
foreach ($rows as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        flash('Security token mismatch. Please try again.', 'error');
        redirect(url('pages/settings/index.php'));
    }

    $submitted = $_POST['settings'] ?? [];
    $v = new Validator($submitted);
    $v->numeric('tax_rate')->numeric('khr_exchange_rate')
      ->numeric('happy_hour_discount')->numeric('buy_x_count')
      ->numeric('stand_count');

    if ($v->passes()) {
        $db = Database::instance();
        /* Happy Hour enabled: checkboxes don't send a value when unchecked. */
        $submitted['happy_hour_enabled'] = isset($_POST['settings']['happy_hour_enabled']) ? '1' : '0';
        $submitted['loyalty_enabled']    = isset($_POST['settings']['loyalty_enabled']) ? '1' : '0';

        foreach ($submitted as $key => $value) {
            $db->execute(
                "REPLACE INTO settings (setting_key, setting_value) VALUES (?, ?)",
                [(string) $key, (string) $value]
            );
        }
        flash('Settings saved.', 'success');
        redirect(url('pages/settings/index.php'));
    }
    $errors = $v->errors();
    /* Re-populate from submitted data on error */
    foreach (($_POST['settings'] ?? []) as $k => $v) {
        $settings[$k] = $v;
    }
}

$pageTitle = 'Settings';

component('layout/header', ['pageTitle' => $pageTitle]);
component('layout/main',   ['title' => 'Settings', 'crumbs' => ['Settings']]);
component('settings/settings-form', ['settings' => $settings, 'errors' => $errors]);
component('layout/footer');
