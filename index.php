<?php
header('Cache-Control: no-store');

$directory = '_notes';
if (!is_dir($directory) && !mkdir($directory)) {
  http_response_code(500);
  die;
}

$filenames = array_diff(scandir($directory), array('.', '..'));
$notes = array_values(array_unique(array_filter(array_map(function ($filename) use ($directory) {
  return pathinfo($directory . DIRECTORY_SEPARATOR . $filename, PATHINFO_FILENAME);
}, $filenames), function ($filename) {
  return !empty($filename);
})));
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
  <title>List</title>
  <link rel="stylesheet" href="/css/github-markdown-5.6.1.min.css">
  <link rel="stylesheet" href="/css/index.css">
</head>

<body>
  <div class="toolbar" style="justify-content: space-between;">
    <a href="/edit/">New</a>
    <a href="https://github.com/reg233/mini-web-notepad" target="_blank">GitHub</a>
  </div>
  <div class="markdown-body" id="markdown"></div>
  <script src="/js/markdown-it-14.1.0.min.js"></script>
  <script>
    const notes = <?php echo json_encode($notes); ?>;
    const list = notes
      .map((note, i) => {
        return `${i + 1}. [${note}](${note})&nbsp;&nbsp;&nbsp;[Edit](/edit/${note})`;
      })
      .join("\n");
    const content = `# List\n\n${list}`;

    const md = window.markdownit();
    document.getElementById("markdown").innerHTML = md.render(content);
  </script>
</body>

</html>