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
$content = '';
if (is_file($path)) {
  $content = htmlspecialchars(file_get_contents($path), ENT_QUOTES, 'UTF-8');
} else {
  header("Location: $base_url/edit/" . $_GET['note']);
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
  <div id="toolbar">
    <span><?php print $_GET['note']; ?></span>
    <a href="/edit/">New</a>
    <a href="/edit/<?php print $_GET['note']; ?>">Edit</a>
    <a id="copy" href="" title="Copy Raw">Copy</a>
    <a href="/">List</a>
  </div>
  <script src="/js/markdown-it.min.js"></script>
  <script src="/js/markdown-it-task-lists.min.js"></script>
  <script>
    const copy = document.getElementById("copy");
    copy.addEventListener("click", function(e) {
      e.preventDefault();

      if (copy.innerText !== "Copied") {
        navigator.clipboard.writeText(content);
        copy.innerText = "Copied";
        setTimeout(function() {
          copy.innerText = "Copy";
        }, 1000);
      }
    });

    let content = <?php echo json_encode($content); ?>;
    const doc = new DOMParser().parseFromString(content, "text/html");
    content = doc.documentElement.textContent;

    const md = window
      .markdownit({
        html: true,
        linkify: true
      })
      .use(window.markdownitTaskLists);

    // Remember the old renderer if overridden, or proxy to the default renderer.
    const defaultRender = md.renderer.rules.link_open || function(tokens, idx, options, _, self) {
      return self.renderToken(tokens, idx, options);
    };
    // Add target="_blank" to all other links
    md.renderer.rules.link_open = function(tokens, idx, options, env, self) {
      try {
        const map = new Map(tokens[idx].attrs);
        const url = new URL(map.get("href"));
        if (url.origin !== window.location.origin) {
          // Add a new `target` attribute, or replace the value of the existing one.
          tokens[idx].attrSet("target", "_blank");
        }
      } catch (error) {}

      // Pass the token to the default renderer.
      return defaultRender(tokens, idx, options, env, self);
    }

    document.getElementById("markdown").innerHTML = md.render(content);
  </script>
</body>

</html>