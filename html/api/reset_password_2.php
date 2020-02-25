<?php
require_once 'api/common.php';
require_once 'database/common.php';
require_once 'database/users.php';
require_once 'database/site_sessions.php';
require_once 'database/reset_password_requests.php';
require_once 'database/helper.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
  /* DEFAULT ARGUMENTS */
  $userid = null;
  $password = null;
  $token = null;

  /* PARSING ARGUMENTS */
  if(isset($_POST['user_id']) && is_numeric($_POST['user_id'])) {
    $userid = intval($_POST['user_id']);
  }

  if(isset($_POST['password'])) {
    $password = $_POST['password'];
  }

  if(isset($_POST['token'])) {
    $token = $_POST['token'];
  }

  /* VALIDATING ARGUMENTS */
  if($userid === null) {
    echo_fail(400, 'ARGUMENT_MISSING', 'User id cannot be empty!');
    return;
  }

  if($password === null) {
    echo_fail(400, 'ARGUMENT_MISSING', 'Password cannot be empty!');
    return;
  }

  if(strlen($password) < 8) {
    echo_fail(400, 'ARGUMENT_INVALID', 'Password must be at least 8 characters!');
    return;
  }

  if(strlen($password) > 255) {
    echo_fail(400, 'ARGUMENT_INVALID', 'Password may not exceed 255 characters!');
    return;
  }

  if($token === null) {
    echo_fail(400, 'ARGUMENT_MISSING', 'Token cannot be empty!');
    return;
  }

  /* VALIDATING AUTHORIZATION */
  if(isset($_COOKIE['session_id'])) {
    echo_fail(403, 'ALREADY_LOGGED_IN', 'You must be logged out to do that!');
    return;
  }

  /* PERFORMING REQUEST */
  $conn = create_db_connection();
  $user = UserMapping::fetch_by_id($conn, $userid);

  if($user === null || $user->claimed === 0) {
    echo_fail(400, 'ACCOUNT_NOT_CLAIMED', 'That account is not claimed. Perhaps you need to <a href="/create_account.php">create your account</a>?');
    $conn->close();
    return;
  }

  $bad_token_iden = 'BAD_TOKEN';
  $bad_token_message = 'That token does not match our records.';

  $rpr = ResetPasswordRequestMapping::fetch_latest_by_user_id($conn, $user->id);
  if($rpr === null) {
    echo_fail(400, $bad_token_iden, $bad_token_message);
    $conn->close();
    return;
  }

  if($rpr->reset_code_sent === 0) {
    echo_fail(400, $bad_token_iden, $bad_token_message);
    $conn->close();
    return;
  }

  if($rpr->reset_code_used !== 0) {
    echo_fail(400, $bad_token_iden, $bad_token_message);
    $conn->close();
    return;
  }

  if($rpr->reset_code !== $token) {
    echo_fail(400, $bad_token_iden, $bad_token_message);
    $conn->close();
    return;
  }

  $new_digest = password_hash($password, PASSWORD_DEFAULT);
  ResetPasswordRequestMapping::update_used($conn, $rpr->id);
  UserMapping::update_password_by_id($conn, $user->id, $new_digest);
  DatabaseHelper::execute($conn, 'DELETE FROM site_sessions WHERE user_id=?', array(array('i', $user->id)));
  echo_success('RESET_PASSWORD_2_SUCCESS', array());
  $conn->close();
}else {
  echo_fail(405, 'METHOD_NOT_ALLOWED', 'You must use a POST request at this endpoint');
}
?>


