<?php
require_once 'api/common.php';
require_once 'database/common.php';
require_once 'database/users.php';
require_once 'database/site_sessions.php';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
  /* DEFAULT ARGUMENTS */
  $username = null;

  /* PARSING ARGUMENTS */
  if(isset($_POST['username'])) {
    $_username = $_POST['username'];
    if($_username !== null && strlen($_username) > 2 && strlen(trim($_username)) > 2) {
      $username = trim($_username);
    }
  }

  /* VALIDATING ARGUMENTS */
  if($username === null) {
    echo_fail(400, 'ARGUMENT_MISSING', 'Username cannot be empty!');
    return;
  }

  if(strlen($username) < 3) {
    echo_fail(400, 'ARGUMENT_INVALID', 'Username is too short');
    return;
  }

  for($i = 0; $i < strlen($username); $i++) {
    $ch = $username[$i];
    if((!ctype_alnum($ch) && $ch !== '_' && $ch !== '-') || ctype_space($ch)) {
      echo_fail(400, 'ARGUMENT_INVALID', "Invalid character in username (pos $i has '$ch')");
      return;
    }
  }

  /* VALIDATING AUTHORIZATION */
  if(isset($_COOKIE['session_id'])) {
    echo_fail(403, 'ALREADY_LOGGED_IN', 'You must be logged out to do that!');
    return;
  }

  /* PERFORMING REQUEST */
  $conn = create_db_connection();
  $user = UserMapping::fetch_by_username($conn, $username);

  if($user !== null && $user->claimed !== 0) {
    echo_fail(400, 'ACCOUNT_ALREADY_CLAIMED', 'That account is already claimed. Perhaps you need to <a href="/reset_password.php">reset your password</a>?');
    $conn->close();
    return;
  }

  if($user === null) {
    $user = UserMapping::create_by_username($conn, $username);
  }

  if($user->claim_code !== null && $user->claim_link_sent_at === null) {
    echo_fail(429, 'TOO_MANY_REQUESTS', 'That request has been made too many times too recently');
    $conn->close();
    return;
  }

  $user->claim_code = bin2hex(openssl_random_pseudo_bytes(32));
  UserMapping::update_user_claim_code($conn, $user);
  echo_success('CLAIM_ACCOUNT_1_SUCCESS', array());
  $conn->close();
}else {
  echo_fail(405, 'METHOD_NOT_ALLOWED', 'You must use a POST request at this endpoint');
}
?>
