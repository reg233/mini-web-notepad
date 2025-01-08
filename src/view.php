<?php
require_once '../config/config.php';
require_once 'utils.php';

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

checkNoteName();
checkPrivateMode('view');

$directory = '../_notes' . DIRECTORY_SEPARATOR . $_GET['note'];
$mdFilename = $directory . '.md';

$content = '';
$filenames = [];
if (is_file($mdFilename)) {
  $content = htmlspecialchars(file_get_contents($mdFilename), ENT_QUOTES, 'UTF-8');
}
if (is_dir(($directory))) {
  $filenames = array_diff(scandir($directory), array('.', '..'));
  $filenames = array_values(array_filter($filenames, function ($filename) use ($directory) {
    return is_file($directory . DIRECTORY_SEPARATOR . $filename);
  }));
}
if (empty($content) && empty($filenames)) {
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
  <link rel="icon" href="/public/images/favicon-16x16.png" type="image/png" sizes="16x16">
  <link rel="icon" href="/public/images/favicon-32x32.png" type="image/png" sizes="32x32">
  <link rel="icon" href="/public/images/favicon.svg" type="image/svg+xml">
  <link rel="apple-touch-icon" href="/public/images/apple-icon-180.png">
  <link rel="manifest" href="/public/manifest.json">
  <title><?php echo $_GET['note']; ?></title>
  <link rel="stylesheet" href="/public/css/github-markdown-5.8.1.min.css">
  <link rel="stylesheet" href="/public/css/index.css">
  <style>
    .gutter {
      align-items: center;
      color: var(--borderColor-default);
      display: flex;
      justify-content: center;
    }

    .gutter.gutter-vertical {
      cursor: row-resize;
    }

    .gutter+#files {
      margin-top: 0;
    }

    #files {
      background-color: var(--bgColor-default);
      border: 1px solid var(--borderColor-default);
      height: calc(20% - 8px);
      margin-top: 16px;
      overflow: auto;
      padding: 16px;
    }

    <?php if (!empty($filenames)): ?>#markdown {
      height: calc(80% - 8px);
    }

    <?php endif; ?>
  </style>
</head>

<body>
  <div class="menu">
    <span class="title"><?php echo $_GET['note']; ?></span>
    <div class="menu-item">
      <a href="">File</a>
      <div class="menu-dropdown">
        <a class="menu-dropdown-item" href="/edit/">New</a>
        <a class="menu-dropdown-item" href="/edit/<?php echo $_GET['note']; ?>">Edit</a>
      </div>
    </div>
    <div class="menu-item">
      <a href="">Copy</a>
      <div class="menu-dropdown">
        <a class="menu-dropdown-item" href="" id="copy-raw">Raw</a>
        <a class="menu-dropdown-item" href="" id="copy-link">Link</a>
      </div>
    </div>
    <a href="/">List</a>
  </div>
  <div class="markdown-body" id="markdown"></div>
  <?php if (!empty($filenames)): ?>
    <div class="markdown-body" id="files"></div>
  <?php endif; ?>
  <script src="/public/js/markdown-it-14.1.0.min.js"></script>
  <script src="/public/js/markdown-it-anchor-9.2.0.min.js"></script>
  <script src="/public/js/markdown-it-footnote-4.0.0.min.js"></script>
  <script src="/public/js/markdown-it-task-lists-2.1.0.min.js"></script>
  <script src="/public/js/split-1.6.5.min.js"></script>
  <script src="/public/js/common.js"></script>
  <script>
    const getCopyRawText = () => content;

    let content = <?php echo json_encode($content); ?>;
    const doc = new DOMParser().parseFromString(content, "text/html");
    content = doc.documentElement.textContent;

    const md = initMarkdownIt();

    const markdownElement = document.getElementById("markdown");
    markdownElement.innerHTML = md.render(content);

    let files = <?php echo json_encode($filenames); ?>;
    if (files.length) {
      files = files
        .map((file) => {
          return `[${file}](/file/<?php echo $_GET['note']; ?>/${encodeURIComponent(file)})`;
        })
        .join(" , ");
      document.getElementById("files").innerHTML = md.render(`Files\n\n${files}`);

      initSplit(["#markdown", "#files"], "vertical", "v");
    }
  </script>
</body>

</html>