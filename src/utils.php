<?php
require_once '../libs/JWT/JWTExceptionWithPayloadInterface.php';
require_once '../libs/JWT/BeforeValidException.php';
require_once '../libs/JWT/ExpiredException.php';
require_once '../libs/JWT/SignatureInvalidException.php';
require_once '../libs/JWT/JWT.php';
require_once '../libs/JWT/Key.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function checkPrivateMode($type)
{
  if (!USER_ID || !USERNAME || !PASSWORD || !JWT_KEY || !PRIVATE_MODE) {
    return;
  }

  if (PRIVATE_MODE === 'all') {
    checkToken(null);
  } else {
    $privateModes = explode(',', PRIVATE_MODE);
    if (in_array($type, $privateModes)) {
      $location = null;
      if ($type === 'list' && !in_array('edit', $privateModes)) {
        $location = '/edit/';
      }
      checkToken($location);
    }
  }
}

function checkToken($location)
{
  try {
    if (!isset($_COOKIE['token'])) {
      throw new Exception('Token is missing!');
    }

    $jwt = $_COOKIE['token'];

    $decoded = JWT::decode($jwt, new Key(JWT_KEY, 'HS256'));

    if ($decoded->sub !== USER_ID) {
      throw new Exception('Invalid token!');
    }
  } catch (Exception $e) {
    if ($location) {
      header("Location: $location");
    } else {
      $redirect = urlencode($_SERVER['REQUEST_URI']);
      header("Location: /login?redirect=$redirect");
    }
    die;
  }
}

function checkNoteName()
{
  if (
    !isset($_GET['note']) ||
    strlen($_GET['note']) > 64 ||
    !preg_match('/^[a-zA-Z0-9_-]+$/', $_GET['note'])
  ) {
    header("Location: /edit/" . substr(str_shuffle('234579abcdefghjkmnpqrstwxyz'), -4));
    die;
  }
}

function checkDirectory($directory)
{
  if (!is_dir($directory) && !mkdir($directory)) {
    http_response_code(500);
    die;
  }
}
