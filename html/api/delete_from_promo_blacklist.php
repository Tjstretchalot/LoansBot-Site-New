<?php
require_once 'api/common.php';
require_once 'database/red_flag_subreddits.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
  // DEFAULT ARGUMENTS
  $username = null;

  // PARSING ARGUMENTS
  if(isset($_POST['username'])) {
      $username = $_POST['username'];
  }

  // VALIDATING ARGUMENTS
  if($username === null) {
      echo_fail(400, 'ARGUMENT_MISSING', 'username cannot be missing!');
      return;
  }

  if(strlen($username) < 3) {
      echo_fail(400, 'ARGUMENT_INVALID', 'username is too short!');
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
    echo_fail(400, 'INVALID_ARGUMENT', 'There is no user in the database with that username');
    return;
  }

  $existing = DatabaseHelper::fetch_one($sql_conn, 'SELECT 1 as one FROM promo_blacklist_users WHERE user_id=? AND removed_at IS NULL', array(array('i', $user->user_id)));

  if($existing === null) {
      echo_fail(400, 'INVALID_ARGUMENT', 'that user is not on the promotion blacklist!');
      return;
  }

  DatabaseHelper::execute($sql_conn, 'UPDATE promo_blacklist_users SET removed_at=NOW() WHERE user_id=?',
    array(array('i', $user->user_id)));

  echo_success('DELETE_FROM_PROMO_BLACKLIST', array());
  $sql_conn->close();
}else {
  echo_fail(405, 'METHOD_NOT_ALLOWED', 'You must use a POST request at this endpoint');
}
?>
