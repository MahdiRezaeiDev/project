<?php
// Database connection ($pdo) must be established before this code


// Start of main logic

if (!isset($_GET['user'])) {
    showAlertAndExit("نام کاربر مشخص نیست.", 'error');
}

$username = $_GET['user'];

// Prepare and execute query to find user
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    showAlertAndExit("کاربر یافت نشد.", 'error');
}

// If token already exists
if ($user['access_token']) {
    showAlertAndExit("این کاربر قبلاً دستگاه خود را ثبت کرده است.", 'info');
}

// Generate token and store in database
$token = bin2hex(random_bytes(16));

$update = $pdo->prepare("UPDATE users SET access_token = ? WHERE id = ?");
$update->execute([$token, $user['id']]);

// Show success message after registering token
showAlertAndExit("ثبت دستگاه با موفقیت انجام شد. توکن شما: {$token}", 'success');
