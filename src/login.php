<?php
require_once '../config/config.php';
require_once '../libs/JWT/JWT.php';
require_once '../libs/JWT/Key.php';

use Firebase\JWT\JWT;

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$failed = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';

  if ($username === USERNAME && $password === PASSWORD) {
    $issuedAt = time();
    $expirationTime = $issuedAt + 60 * 60 * 24;

    $payload = [
      'iss' => 'https://mini-web-notepad.com',
      'aud' => 'https://mini-web-notepad.com',
      'iat' => $issuedAt,
      'exp' => $expirationTime,
      'sub' => USER_ID,
    ];

    $jwt = JWT::encode($payload, JWT_KEY, 'HS256');

    setcookie('token', $jwt, [
      'expires' => $expirationTime,
      'path' => '/',
      'secure' => true,
      'httponly' => true,
      'samesite' => 'Strict',
    ]);

    $redirect = '/';
    if (isset($_GET['redirect'])) {
      $redirect = urldecode($_GET['redirect']);
      if (!preg_match('#^/.*$#', $redirect)) {
        $redirect = '/';
      }
    }
    header("Location: $redirect");
    die;
  } else {
    $failed = true;
  }
}
?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="mobile-web-app-capable" content="yes">
  <link rel="icon" href="/public/images/favicon-16x16.png" type="image/png" sizes="16x16">
  <link rel="icon" href="/public/images/favicon-32x32.png" type="image/png" sizes="32x32">
  <link rel="icon" href="/public/images/favicon.svg" type="image/svg+xml">
  <link rel="apple-touch-icon" href="/public/images/apple-icon-180.png">
  <link rel="manifest" href="/public/manifest.json">
  <title>Sign in to Notepad</title>
  <link rel="stylesheet" href="/public/css/index.css">
  <link rel="stylesheet" href="/public/css/login.css">
</head>

<body>
  <img alt="Logo" class="logo" src="/public/images/favicon.svg">
  <h1>Sign in to Notepad</h1>
  <?php if ($failed): ?>
    <div class="error">Incorrect username or password.</div>
  <?php endif; ?>
  <form action="" id="form" method="POST">
    <label for="username">Username</label>
    <input autocapitalize="off" autocomplete="username" autocorrect="off" autofocus="autofocus" id="username" name="username" required="required" type="text" />
    <label for="password">Password</label>
    <input autocomplete="current-password" id="password" name="password" required="required" type="password" />
    <input type="submit" value="Sign in" />
  </form>
  <script>
    const form = document.getElementById("form");

    form.addEventListener("submit", (e) => {
      e.preventDefault();

      const submitButton = form.querySelector('input[type="submit"]');
      submitButton.disabled = true;
      submitButton.value = "Signing in…";

      form.submit();
    });
  </script>
</body>

</html>