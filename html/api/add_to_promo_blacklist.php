<?php
require_once 'api/common.php';
require_once 'database/red_flag_subreddits.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
  // DEFAULT ARGUMENTS
  $username = null;
  $reason = null;

  // PARSING ARGUMENTS
  if(isset($_POST['username'])) {
      $username = $_POST['username'];
  }

  if(isset($_POST['reason'])) {
      $reason = $_POST['reason'];
  }

  // VALIDATING ARGUMENTS
  if($username === null) {
      echo_fail(400, 'ARGUMENT_MISSING', 'username cannot be missing!');
      return;
  }

  if($reason === null) {
      echo_fail(400, 'ARGUMENT_MISSING', 'reason cannot be missing!');
      return;
  }

  if(strlen($username) < 3) {
      echo_fail(400, 'ARGUMENT_INVALID', 'username is too short!');
      return;
  }

  if(strlen($reason) < 5) {
      echo_fail(400, 'ARGUMENT_INVALID', 'reason is too short!');
      return;
  }

  foreach($username as $c) {
    if(!ctype_alnum($c) && $c !== '_' && $c !== '-') {
      echo_fail(400, 'INVALID_ARGUMENT', 'username contains invalid character ' . $c);
      return;
    }
  }

  // VALIDATING AUTHORIZATION
  require_once 'api/auth.php';

  if(!is_moderator()) {
    on_failed_auth();
    return;
  }

  // SECONDARY VALIDATING ARGUMENTS
  $user = DatabaseHelper::fetch_one($sql_conn, 'SELECT user_id FROM usernames WHERE username=?',
    array(array('s', $username)));

  if($user === null) {
    echo_fail(400, 'INVALID_ARGUMENT', 'There is no user in the database with that username - start the create account process for them to force them to be added to the database');
    return;
  }

  $existing = DatabaseHelper::fetch_one($sql_conn, 'SELECT 1 FROM promo_blacklist_users WHERE user_id=? AND removed_at IS NOT NULL', array(array('i', $user->user_id)));

  if($existing !== null) {
      echo_fail(400, 'INVALID_ARGUMENT', 'that user is already on the promotion blacklist!');
      return;
  }

  DatabaseHelper::execute($sql_conn, 'INSERT INTO promo_blacklist_users (user_id, mod_user_id, reason, added_at, removed_at) VALUES (?, ?, ?, NOW(), NULL)',
    array(array('i', $user->user_id), array('i', $logged_in_user->id), array('s', $reason)));

  echo_success('ADD_TO_PROMO_BLACKLIST');
  $sql_conn->close();
}else {
  echo_fail(405, 'METHOD_NOT_ALLOWED', 'You must use a POST request at this endpoint');
}
?>
