<?php
// Logout aman hanya melalui metode POST (Task 0.9 / P-05)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header("Location: index.php");
    exit;
}

session_start();

// Kosongkan seluruh data sesi
$_SESSION = [];

// Hapus cookie sesi dari browser jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Hancurkan sesi di server
session_destroy();

header("Location: index.php");
exit;
?>
