<?php
require_once 'api/common.php';
require_once 'database/red_flag_subreddits.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
  // DEFAULT ARGUMENTS
  $subreddit = null;
  $description = null;

  // PARSING ARGUMENTS
  if(isset($_POST['subreddit'])) {
    $subreddit = $_POST['subreddit'];
  }

  if(isset($_POST['description'])) {
    $description = $_POST['description'];
  }

  // VALIDATING ARGUMENTS
  if($subreddit === null) {
    echo_fail(400, 'ARGUMENT_MISSING', 'subreddit cannot be missing!');
    return;
  }

  if($description === null) {
    echo_fail(400, 'ARGUMENT_MISSING', 'description cannot be missing!');
    return;
  }

  if(strlen($description) < 5) {
    echo_fail(400, 'ARGUMENT_INVALID', 'description must be at least 5 characters!');
    return;
  }

  if(substr($subreddit, 0, 3) === '/r/') {
    $subreddit = substr($subreddit, 3);
  }

  if(substr($subreddit, 0, 2) === 'r/') {
    $subreddit = substr($subreddit, 2);
  }

  if(strlen($subreddit) < 3) {
    echo_fail(400, 'ARGUMENT_INVALID', 'subreddit must be at least 3 characters!');
    return;
  }

  /* VALIDATING AUTHORIZATION */
  include_once 'connect_and_get_loggedin.php';

  if(($logged_in_user === null) || ($logged_in_user->auth < $MODERATOR_PERMISSION)) {
    echo_fail(403, 'NOT_AUTHORIZED', 'You do not have permission to do that');
    $sql_conn->close();
    return;
  }

  /* PERFORMING REQUEST */
  $existing = RedFlagSubredditsMapping::fetch_for_subreddit($sql_conn, $subreddit);
  if($existing) {
    echo_fail(400, 'ARGUMENT_INVALID', 'There is already a red flag for that subreddit!');
    $sql_conn->close();
    return;
  }

  RedFlagSubredditsMapping::add_subreddit($sql_conn, $subreddit, $description);

  echo_success('ADD_RED_FLAG_SUBREDDIT_SUCCESS', array());
  $sql_conn->close();
}else {
  echo_fail(405, 'METHOD_NOT_ALLOWED', 'You must use a POST request at this endpoint');
}
?>
