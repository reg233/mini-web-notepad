<?php
$protocol = 'http://';
if (
  isset($_SERVER['HTTPS']) &&
  ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
  isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
  $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'
) {
  $protocol = 'https://';
}
$base_url = $protocol . $_SERVER['HTTP_HOST'];

header('Cache-Control: no-store');

if (
  !isset($_GET['note']) ||
  strlen($_GET['note']) > 64 ||
  !preg_match('/^[a-zA-Z0-9_-]+$/', $_GET['note'])
) {
  header("Location: $base_url/edit/" . substr(str_shuffle('234579abcdefghjkmnpqrstwxyz'), -4));
  die;
}

$path = '_tmp' . '/' . $_GET['note'] . '.md';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $text = isset($_POST['text']) ? $_POST['text'] : file_get_contents("php://input");
  file_put_contents($path, $text);

  if (!strlen($text)) {
    unlink($path);
  }
  die;
}
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="mobile-web-app-capable" content="yes">
  <link rel="icon" href="/images/favicon-16x16.png" type="image/png" sizes="16x16">
  <link rel="icon" href="/images/favicon-32x32.png" type="image/png" sizes="32x32">
  <link rel="icon" href="/images/favicon.svg" type="image/svg+xml">
  <link rel="apple-touch-icon" href="/images/apple-icon-180.png">
  <link rel="manifest" href="/site.webmanifest">
  <title><?php print $_GET['note']; ?></title>
  <link rel="stylesheet" href="/css/github-markdown.min.css">
  <link rel="stylesheet" href="/css/index.css">
  <link rel="stylesheet" href="/css/edit.css">
</head>

<body>
  <div class="container">
    <textarea autofocus id="textarea"><?php
                                      if (is_file($path)) {
                                        print htmlspecialchars(file_get_contents($path), ENT_QUOTES, 'UTF-8');
                                      }
                                      ?></textarea>
    <div class="markdown-body" id="markdown"></div>
  </div>
  <div id="toolbar">
    <span><?php print $_GET['note']; ?></span>
    <a href="/edit/">New</a>
    <a href="/<?php print $_GET['note']; ?>">View</a>
    <a id="copy" href="" title="Copy Raw">Copy</a>
    <a href="/">List</a>
  </div>
  <script src="/js/markdown-it.min.js"></script>
  <script src="/js/markdown-it-task-lists.min.js"></script>
  <script src="/js/split.min.js"></script>
  <script src="/js/edit.js"></script>
</body>

</html>