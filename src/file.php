<?php
require_once '../config/config.php';
require_once 'utils.php';

checkPrivateMode('file');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  http_response_code(405);
  header('Allow: GET');
  die;
}

if (
  !isset($_GET['note']) ||
  strlen($_GET['note']) > 64 ||
  !preg_match('/^[a-zA-Z0-9_-]+$/', $_GET['note']) ||
  !isset($_GET['filename'])
) {
  http_response_code(400);
  die;
}

$inlineMimeType = [
  'application/pdf',
  'audio/mpeg',
  'image/bmp',
  'image/gif',
  'image/jpeg',
  'image/png',
  'image/svg+xml',
  'image/vnd.microsoft.icon',
  'image/webp',
  'text/plain',
  'video/mp4'
];

$directory = '../_notes' . DIRECTORY_SEPARATOR . $_GET['note'];
$filename = $directory . DIRECTORY_SEPARATOR . basename($_GET['filename']);

if (file_exists($filename)) {
  $finfo = finfo_open(FILEINFO_MIME_TYPE);
  $mimeType = finfo_file($finfo, $filename);
  if ($mimeType === 'image/svg') {
    $mimeType = 'image/svg+xml';
  }
  finfo_close($finfo);

  if (in_array($mimeType, $inlineMimeType)) {
    header('Content-Disposition: inline; filename="' . basename($filename) . '"');
  } else {
    header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
  }
  header('Content-Length: ' . filesize($filename));
  header('Content-Type: ' . $mimeType);
  header('Cache-Control: no-store, no-cache, must-revalidate');
  header('Pragma: no-cache');
  header('Expires: 0');

  ob_clean();
  flush();

  readfile($filename);

  die;
} else {
  http_response_code(404);
}
