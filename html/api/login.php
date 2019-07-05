<?php
require_once 'api/common.php';
require_once 'database/common.php';
require_once 'database/users.php';
require_once 'database/site_sessions.php';
require_once 'database/helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  /* DEFAULT ARGUMENTS */
  $username = null;
  $password = null;
  $duration = 'forever';
  $token = null;

  /* PARSING ARGUMENTS */
  if (isset($_POST['username'])) {
    $username = $_POST['username'];
  }
  if (isset($_POST['password'])) {
    $password = $_POST['password'];
  }
  if (isset($_POST['duration'])) {
    $duration = $_POST['duration'];
  }
  if (isset($_POST['token'])) {
    $token = $_POST['token'];
  }

  /* VALIDATING ARGUMENTS */
  if ($username === null) {
    echo_fail(400, 'ARGUMENT_MISSING', 'Username cannot be empty!');
    return;
  }
  if ($password === null) {
    echo_fail(400, 'ARGUMENT_MISSING', 'Password cannot be empty!');
    return;
  }
  if ($duration === null) {
    echo_fail(400, 'ARGUMENT_MISSING', 'Duration cannot be empty!');
    return;
  }
  if ($duration !== 'forever' && $duration !== '1day' && $duration !== '30days') {
    echo_fail(400, 'ARGUMENT_INVALID', 'Duration must be forever, 1day, or 30days');
    return;
  }
  /* VALIDATING AUTHORIZATION */
  if (isset($_COOKIE['session_id'])) {
    echo_fail(403, 'ALREADY_LOGGED_IN', 'You must be logged out to do that!');
    return;
  }

  /* BRUTE FORCE PREVENTION */
  $conn = create_db_connection();
  if ($token === null) {
    $num_failed_recently = DatabaseHelper::fetch_one($conn, 'SELECT COUNT(*) FROM failed_login_attempts WHERE attempted_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE)', array());
    if ($num_failed_recently > 10) {
      echo_fail(429, 'TOO_MANY_REQUESTS', 'There have been too many failed login attempts recently to service unauthenticated logins. Use a recaptcha or wait 10 minutes');
      $conn->close();
      return;
    }
  } else {
    if ($_SERVER['LOANSSITE_RECAPTCHA_ENABLED'] !== 'true') {
      echo_fail(503, 'RECAPTCHA_UNSUPPORTED', 'ReCAPTCHA is only supported on the production server');
      $conn->close();
      return;
    }
    $context = stream_context_create(
      array(
        'http' => array(
          'method' => 'POST',
          'header' => 'Content-Type: application/x-www-form-urlencoded',
          'content' => http_build_query(array(
            'secret' => $_SERVER['LOANSSITE_RECAPTCHA_SECRET'],
            'response' => $token
          ))
        )
      )
    );
    $captcha_success = json_decode(file_get_contents(
      'https://www.google.com/recaptcha/api/siteverify',
      false,
      $context
    ));

    if(!$captcha_success->success) {
      echo_fail(403, 'RECAPTCHA_FAILED', 'The specified recaptcha token is invalid or expired');
      $conn->close();
      return;
    }

    error_log('User passed recaptcha (success=' . strval($captcha_success->success) . ')');
  }

  /* PERFORMING REQUEST */
  $person = UserMapping::fetch_by_username($conn, $username);
  if ($person === null) {
    DatabaseHelper::execute($conn, 'INSERT INTO failed_login_attempts (username) VALUES (?)', array(array('s', $username)));

    echo_fail(400, 'BAD_PASSWORD', 'That username/password combination is not correct.');
    $conn->close();
    return;
  }
  if ($person->password_digest === null || !password_verify($password, $person->password_digest)) {
    DatabaseHelper::execute($conn, 'INSERT INTO failed_login_attempts (username) VALUES (?)', array(array('s', $username)));

    echo_fail(400, 'BAD_PASSWORD', 'That username/password combination is not correct.');
    $conn->close();
    return;
  }

  $session_id = bin2hex(openssl_random_pseudo_bytes(32));
  $cookie_expires_at = time() + (60 * 60 * 24 * 365 * 10); // 10 years
  $expires_at = $cookie_expires_at;
  if ($duration === '30days') {
    $expires_at = time() + 60 * 60 * 24 * 30;
    $cookie_expires_at = $expires_at;
  } elseif ($duration === '1day') {
    $expires_at = time() + 60 * 60 * 24;
    $cookie_expires_at = $expires_at;
  }
  SiteSessionMapping::create_and_save($conn, $session_id, $person->id, time(), $expires_at);

  if($_SERVER['LOANSSITE_STRONG_COOKIES_ENABLED'] !== 'true') {
    setcookie('session_id', $session_id, $cookie_expires_at, '/');
  }else {
    if (PHP_VERSION_ID < 70300) {
      setcookie('session_id', $session_id, $cookie_expires_at, '/; SameSite=strict', "", true, true);
    }else {
      setcookie('session_id', $session_id, $cookie_expires_at, '/', '', true, true, 'strict');
    }
  }
  echo_success('LOGIN_SUCCESS', array('session_id' => $session_id));
  $conn->close();
} else {
  echo_fail(405, 'METHOD_NOT_ALLOWED', 'You must use a POST request at this endpoint');
}
