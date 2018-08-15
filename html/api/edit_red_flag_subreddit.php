<?php
require_once 'api/common.php';
require_once 'database/red_flag_subreddits.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
  // DEFAULT ARGUMENTS
  $id = null;
  $description = null;

  // PARSING ARGUMENTS
  if(isset($_POST['id']) && is_numeric($_POST['id'])) {
    $id = intval($_POST['id']);
  }

  if(isset($_POST['description'])) {
    $description = $_POST['description'];
  }

  // VALIDATING ARGUMENTS
  if($id === null) {
    echo_fail(400, 'ARGUMENT_MISSING', 'id cannot be missing!');
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

  /* VALIDATING AUTHORIZATION */
  include_once 'connect_and_get_loggedin.php';

  if(($logged_in_user === null) || ($logged_in_user->auth < $MODERATOR_PERMISSION)) {
    echo_fail(403, 'NOT_AUTHORIZED', 'You do not have permission to do that');
    $sql_conn->close();
    return;
  }

  /* PERFORMING REQUEST */
  $existing = RedFlagSubredditsMapping::fetch_by_id($sql_conn, $id);
  if($existing === null) {
    echo_fail(400, 'ARGUMENT_INVALID', 'There is no red flag with that id!');
    $sql_conn->close();
    return;
  }

  RedFlagSubredditsMapping::update_description($sql_conn, $id, $description);

  echo_success('EDIT_RED_FLAG_SUBREDDIT_SUCCESS', array());
  $sql_conn->close();
}else {
  echo_fail(405, 'METHOD_NOT_ALLOWED', 'You must use a POST request at this endpoint');
}
?>
