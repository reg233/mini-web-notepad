<?php
require_once '../libs/JWT/JWTExceptionWithPayloadInterface.php';
require_once '../libs/JWT/BeforeValidException.php';
require_once '../libs/JWT/ExpiredException.php';
require_once '../libs/JWT/SignatureInvalidException.php';
require_once '../libs/JWT/JWT.php';
require_once '../libs/JWT/Key.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function checkLogged($location = null)
{
  if (!USER_ID || !USERNAME || !PASSWORD || !JWT_KEY || !PRIVATE_MODE) {
    return false;
  }

  try {
    if (!isset($_COOKIE['token'])) {
      throw new Exception('Token is missing!');
    }

    $jwt = $_COOKIE['token'];

    $decoded = JWT::decode($jwt, new Key(JWT_KEY, 'HS256'));

    if ($decoded->sub !== USER_ID) {
      throw new Exception('Invalid token!');
    }

    return true;
  } catch (Exception $e) {
    if ($location === null) {
      return false;
    } else {
      if ($location === '') {
        $requestUri = $_SERVER['REQUEST_URI'];
        if ($requestUri === '/') {
          header('Location: /login');
        } else {
          header('Location: /login?redirect=' . urlencode($requestUri));
        }
      } else {
        header("Location: $location");
      }
      die;
    }
  }
}

function checkPrivateMode($type)
{
  if (!USER_ID || !USERNAME || !PASSWORD || !JWT_KEY || !PRIVATE_MODE) {
    return;
  }

  if (PRIVATE_MODE === 'all') {
    checkLogged('');
  } else {
    $privateModes = explode(',', PRIVATE_MODE);
    if (in_array($type, $privateModes)) {
      $location = '';
      if ($type === 'list' && !in_array('edit', $privateModes)) {
        $location = '/edit/';
      }
      checkLogged($location);
    }
  }
}

function checkNoteName()
{
  if (
    !isset($_GET['note']) ||
    mb_strlen($_GET['note'], 'UTF-8') > 64 ||
    !preg_match('/^[a-zA-Z0-9\x{4e00}-\x{9fa5}_-]+$/u', $_GET['note'])
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
