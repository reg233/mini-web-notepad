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
if (!is_dir($directory) && !mkdir($directory)) {
  http_response_code(500);
  die;
}

$directory = $directory . DIRECTORY_SEPARATOR . $_GET['note'];
$mdFilename = $directory . '.md';

function getDirectorySize($directory)
{
  $size = 0;

  if (is_dir($directory)) {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    foreach ($files as $file) {
      if ($file->isFile()) {
        $size += $file->getSize();
      }
    }
  }

  return $size;
}

function deleteDirectory($directory)
{
  if (!is_dir($directory)) {
    return false;
  }

  $filenames = array_diff(scandir($directory), ['.', '..']);

  foreach ($filenames as $filename) {
    $filePath = $directory . DIRECTORY_SEPARATOR . $filename;

    if (is_dir($filePath)) {
      deleteDirectory($filePath);
    } else {
      unlink($filePath);
    }
  }

  return rmdir($directory);
}

function handleEdit($mdFilename, $text)
{
  $length = strlen($text);

  $success = false;
  if ($length > 24 * 1024) {
    http_response_code(400);
    return;
  } else if ($length) {
    $success = file_put_contents($mdFilename, $text);
  } else {
    $success = unlink($mdFilename);
  }
  if (!$success) {
    http_response_code(500);
  }
}

function handleDelete($directory, $mdFilename)
{
  if (
    (is_dir($directory) && !deleteDirectory($directory)) ||
    (is_file($mdFilename) && !unlink($mdFilename))
  ) {
    http_response_code(500);
  }
}

function handleFiles($directory)
{
  header('Content-Type: application/json');

  if (is_dir($directory)) {
    $filenames = array_diff(scandir($directory), array('.', '..'));
    $filenames = array_values(array_filter($filenames, function ($filename) use ($directory) {
      return is_file($directory . DIRECTORY_SEPARATOR . $filename);
    }));

    echo json_encode($filenames);
  } else {
    echo json_encode([]);
  }
}

function handleFileUpload($directory)
{
  if (getDirectorySize('_notes') > 1024 * 1024 * 1024) {
    http_response_code(400);
    return;
  }

  if ((!is_dir($directory) && !mkdir($directory))) {
    http_response_code(500);
    return;
  }

  $originalName = basename($_FILES['file']['name']);
  $filename = $directory . DIRECTORY_SEPARATOR . $originalName;

  $fileInfo = pathinfo($originalName);
  $counter = 1;
  while (file_exists($filename)) {
    $newName = $fileInfo['filename'] . '_' . $counter;
    if (!empty($fileInfo['extension'])) {
      $newName = $newName . '.' . $fileInfo['extension'];
    }
    $filename = $directory . DIRECTORY_SEPARATOR . $newName;
    $counter++;
  }

  if (!move_uploaded_file($_FILES['file']['tmp_name'], $filename)) {
    http_response_code(500);
  }
}

function handleFileRemove($directory, $filename)
{
  $filename = $directory . DIRECTORY_SEPARATOR . $filename;

  if (
    !is_file($filename) ||
    !unlink($filename) ||
    (count(scandir($directory)) === 2 && !deleteDirectory($directory))
  ) {
    http_response_code(500);
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    handleFileUpload($directory);
  } else {
    $data = json_decode(file_get_contents('php://input'), true);
    switch ($data['method'] ?? '') {
      case 'edit':
        handleEdit($mdFilename, $data['text'] ?? '');
        break;
      case 'delete':
        handleDelete($directory, $mdFilename);
        break;
      case 'files':
        handleFiles($directory);
        break;
      case 'fileRemove':
        handleFileRemove($directory, $data['filename'] ?? '');
        break;
      default:
        http_response_code(400);
        break;
    }
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
  <title><?php echo $_GET['note']; ?></title>
  <link rel="stylesheet" href="/css/github-markdown-5.6.1.min.css">
  <link rel="stylesheet" href="/css/index.css">
  <link rel="stylesheet" href="/css/edit.css">
</head>

<body>
  <div id="editor">
    <textarea autofocus id="textarea"><?php
                                      if (is_file($mdFilename)) {
                                        echo htmlspecialchars(file_get_contents($mdFilename), ENT_QUOTES, 'UTF-8');
                                      }
                                      ?></textarea>
    <div class="markdown-body" id="markdown"></div>
  </div>
  <div id="file-drop">
    <span>
      Drop your file here, or
      <a id="browse" href="">browse</a>
      (Max <?php echo ini_get('upload_max_filesize'); ?>)
    </span>
    <input id="input-file" style="display: none;" type="file">
    <div id="files"></div>
    <div id="loader">
      <div class="loader"></div>
    </div>
  </div>
  <div class="toolbar">
    <div id="status"></div>
    <span class="title"><?php echo $_GET['note']; ?></span>
    <a href="/edit/">New</a>
    <a href="/<?php echo $_GET['note']; ?>">View</a>
    <a id="copy" href="" title="Copy Raw">Copy</a>
    <a id="delete" href="">Delete</a>
    <a href="/">List</a>
  </div>
  <script src="/js/markdown-it-14.1.0.min.js"></script>
  <script src="/js/markdown-it-task-lists-2.1.0.min.js"></script>
  <script src="/js/split-1.6.5.min.js"></script>
  <script src="/js/common.js"></script>
  <script src="/js/edit.js"></script>
</body>

</html>