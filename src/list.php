<?php
require_once '../config.php';
require_once 'utils.php';

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$directory = '../_notes';

checkPrivateMode('list');
checkDirectory($directory);

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
    const hosted = "<?php echo HOSTED_ON; ?>";
    const hostedUrl = "<?php echo HOSTED_ON_URL; ?>";

    let content = `# List\n\n${list}`;
    if (hosted && hostedUrl) {
      content = `${content}\n\nHosted on <a href="${hostedUrl}" target="_blank">${hosted}</a>`;
    }

    const md = window.markdownit({
      html: true
    });
    document.getElementById("markdown").innerHTML = md.render(content);
  </script>
</body>

</html>