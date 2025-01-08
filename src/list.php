<?php
require_once '../config/config.php';
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
  <link rel="icon" href="/public/images/favicon-16x16.png" type="image/png" sizes="16x16">
  <link rel="icon" href="/public/images/favicon-32x32.png" type="image/png" sizes="32x32">
  <link rel="icon" href="/public/images/favicon.svg" type="image/svg+xml">
  <link rel="apple-touch-icon" href="/public/images/apple-icon-180.png">
  <link rel="manifest" href="/public/manifest.json">
  <title>List</title>
  <link rel="stylesheet" href="/public/css/github-markdown-5.8.1.min.css">
  <link rel="stylesheet" href="/public/css/index.css">
</head>

<body>
  <div class="menu">
    <span class="title">Mini Web Notepad</span>
    <a href="/edit/">New</a>
    <?php if (checkLogged()): ?>
      <a href="" id="logout">Logout</a>
    <?php endif; ?>
    <a href="https://github.com/reg233/mini-web-notepad" target="_blank">GitHub</a>
  </div>
  <div class="markdown-body" id="markdown"></div>
  <script src="/public/js/markdown-it-14.1.0.min.js"></script>
  <script>
    const logoutElement = document.getElementById("logout");
    if (logoutElement) {
      logoutElement.addEventListener("click", async (e) => {
        e.preventDefault();

        try {
          const response = await fetch("/logout", {
            method: "POST"
          });
          if (response.ok) {
            location.reload();
          } else {
            throw new Error();
          }
        } catch {
          alert("Logout failed!");
        }
      });
    }

    const notes = <?php echo json_encode($notes); ?>;
    const list = notes.map((note, i) => `${i + 1}. [${note}](${note})`).join("\n");
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