<?php
$path = '_notes';
$files = array_diff(scandir($path), array('.', '..', '.htaccess'));
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
    <a href="/edit/">New</a>
  </div>
  <script src="/js/markdown-it.min.js"></script>
  <script>
    const files = <?php echo json_encode($files); ?>;
    const list = Object.entries(files)
      .map(([, name], i) => {
        const prefix = name.replaceAll(".md", "");
        return `${i + 1}. [${prefix}](${prefix})&nbsp;&nbsp;&nbsp;[Edit](/edit/${prefix})`;
      })
      .join("\n");
    const content = `# List\n\n${list}`;

    const md = window.markdownit();
    document.getElementById("markdown").innerHTML = md.render(content);
  </script>
</body>

</html>