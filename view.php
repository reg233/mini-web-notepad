<?php
header('Cache-Control: no-store');

if (
  !isset($_GET['note']) ||
  strlen($_GET['note']) > 64 ||
  !preg_match('/^[a-zA-Z0-9_-]+$/', $_GET['note'])
) {
  header("Location: /edit/" . substr(str_shuffle('234579abcdefghjkmnpqrstwxyz'), -4));
  die;
}

$directory = '_notes';
$filename = $directory . '/' . $_GET['note'] . '.md';
$content = '';
if (is_file($filename)) {
  $content = htmlspecialchars(file_get_contents($filename), ENT_QUOTES, 'UTF-8');
} else {
  header("Location: /edit/" . $_GET['note']);
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
  <style>
    #markdown {
      height: 100%;
    }
  </style>
</head>

<body>
  <div class="markdown-body" id="markdown"></div>
  <div class="toolbar">
    <span class="title"><?php print $_GET['note']; ?></span>
    <a href="/edit/">New</a>
    <a href="/edit/<?php print $_GET['note']; ?>">Edit</a>
    <a id="copy" href="" title="Copy Raw">Copy</a>
    <a id="delete" href="">Delete</a>
    <a href="/">List</a>
  </div>
  <script src="/js/markdown-it.min.js"></script>
  <script src="/js/markdown-it-task-lists.min.js"></script>
  <script src="/js/common.js"></script>
  <script>
    const getCopyText = () => content;
    const getDeleteUrl = () => `${window.location.origin}/edit${window.location.pathname}`;

    let content = <?php echo json_encode($content); ?>;
    const doc = new DOMParser().parseFromString(content, "text/html");
    content = doc.documentElement.textContent;

    const md = initMarkdownIt();
    document.getElementById("markdown").innerHTML = md.render(content);
  </script>
</body>

</html>