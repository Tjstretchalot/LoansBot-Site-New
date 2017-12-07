<?php
require_once 'api/common.php';
require_once 'database/common.php';
require_once 'database/users.php';
require_once 'database/site_sessions.php';
require_once 'database/reset_password_requests.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
  /* DEFAULT ARGUMENTS */
  $username = null;

  /* PARSING ARGUMENTS */
  if(isset($_POST['username'])) {
    $_username = $_POST['username'];
    if($_username !== null && strlen($_username) > 2) {
      $username = $_username;
    }
  }

  /* VALIDATING ARGUMENTS */
  if($username === null) {
    echo_fail(400, 'ARGUMENT_MISSING', 'Username cannot be empty!');
    return;
  }

  /* VALIDATING AUTHORIZATION */
  if(isset($_COOKIE['session_id'])) {
    echo_fail(403, 'ALREADY_LOGGED_IN', 'You must be logged out to do that!');
    return;
  }

  /* PERFORMING REQUEST */
  $conn = create_db_connection(); 
  $user = UserMapping::fetch_by_username($conn, $username);

  if($user === null || $user->claimed === 0) {
    echo_fail(400, 'ACCOUNT_NOT_CLAIMED', 'That account is not claimed. Perhaps you need to <a href="/create_account.php">create your account</a>?');
    $conn->close();
    return;
  }

  $rpr = ResetPasswordRequestMapping::fetch_latest_by_user_id($conn, $user->id);
  if($rpr !== null) {
    $rpr_createdat_php = strtotime($rpr->created_at);
    if($rpr_createdat_php > time() - (60 * 60 * 24)) {
      echo_fail(429, 'TOO_MANY_REQUESTS', 'That request has been made too many times too recently');
      $conn->close();
      return;
    }
  }

  $token = bin2hex(openssl_random_pseudo_bytes(32));
  ResetPasswordRequestMapping::insert($conn, $user->id, $token);
  echo_success('RESET_PASSWORD_1_SUCCESS', array());
  $conn->close();
}else {
  echo_fail(405, 'METHOD_NOT_ALLOWED', 'You must use a POST request at this endpoint');
}
?>

