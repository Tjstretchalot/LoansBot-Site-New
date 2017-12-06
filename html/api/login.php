<?php
require_once 'api/common.php';
require_once 'database/users.php';
require_once 'database/site_sessions.php';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
  /* DEFAULT ARGUMENTS */
  $username = null;
  $password = null;
  $duration = 'forever';
  /* PARSING ARGUMENTS */
  if(isset($_POST['username'])) {
    $username = $_POST['username'];
  }
  if(isset($_POST['password'])) {
    $password = $_POST['password'];
  }
  if(isset($_POST['duration'])) {
    $duration = $_POST['duration'];
  }
  /* VALIDATING ARGUMENTS */
  if($username === null) {
    echo_fail(400, 'ARGUMENT_MISSING', 'Username cannot be empty!');
    return;
  }
  if($password === null) {
    echo_fail(400, 'ARGUMENT_MISSING', 'Password cannot be empty!');
    return;
  }
  if($duration === null) {
    echo_fail(400, 'ARGUMENT_MISSING', 'Duration cannot be empty!');
    return;
  }
  if($duration !== 'forever' && $duration !== '1day' && $duration !== '30days') {
    echo_fail(400, 'ARGUMENT_INVALID', 'Duration must be forever, 1day, or 30days');
    return;
  }
  /* VALIDATING AUTHORIZATION */
  if(isset($_COOKIE['session_id'])) {
    echo_fail(403, 'ALREADY_LOGGED_IN', 'You must be logged out to do that!');
    return;
  }
  /* PERFORMING REQUEST */
  $conn = create_db_connection(); 
  $person = UserMapping::fetch_by_username($conn, $username);
  if($person === null) {
    echo_fail(400, 'BAD_PASSWORD', 'That username/password combination is not correct.');
    $conn->close();
    return;
  }
  if($person->password_digest === null || !password_verify($password, $person->password_digest)) {
    echo_fail(400, 'BAD_PASSWORD', 'That username/password combination is not correct.');
    $conn->close();
    return;
  }

  $session_id = bin2hex(random_bytes(32));
  $expires_at = null;
  $cookie_expires_at = time() + (60 * 60 * 24 * 365 * 10); // 10 years
  if($duration === '30days') {
    $expires_at = time() + 60 * 60 * 24 * 30;
    $cookie_expires_at = $expires_at;
  }elseif($duration === '1day') {
    $expires_at = time() + 60 * 60 * 24;
    $cookie_expires_at = $expires_at;
  }
  SiteSessionMapping::create_and_save($conn, $session_id, $person->id, time(), $expires_at);
  setcookie('session_id', $session_id, $cookie_expires_at, '/'); 
  echo_success('LOGIN_SUCCESS', array('session_id' => $session_id));
  $conn->close();
}else {
  echo_fail(405, 'METHOD_NOT_ALLOWED', 'You must use a POST request at this endpoint');
}
?>
