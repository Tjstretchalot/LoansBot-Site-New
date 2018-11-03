<?php
/*
 * Queues a specific user to get a red flag report. This does not get around the 1-month limit
 * between reports.
 *
 * Parameters:
 *   username - who you want to generate a report on
 */

require_once 'api/common.php';
require_once 'database/helper.php';
require_once 'database/users.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
  // DEFAULT ARGUMENTS
  $username = null;

  // PARSING ARGUMENTS
  if(isset($_POST['username'])) {
    $username = $_POST['username'];
  }

  // VALIDATING ARGUMENTS
  if($username === null) {
    echo_fail(400, 'ARGUMENT_MISSING', 'missing required argument username'); 
    return;
  }
  if(strlen($username) < 3) {
    echo_fail(400, 'ARGUMENT_INVALID', 'username must be at least 3 characters');
    return;
  }
  if(strlen($username) > 254) {
    echo_fail(400, 'ARGUMENT_INVALID', 'username is too long!');
    return;
  }

  for($i = 0; $i < strlen($username); $i++) {
    $c = $username[$i];
    if(!ctype_alnum($c) && $c !== '_' && $c !== '-') {
      echo_fail(400, 'ARGUMENT_INVALID', 'username contains invalid character: ' . $c);
      return;
    }
  }

  // VALIDATING AUTHORIZATION
  include_once 'api/auth.php';

  if(!is_trusted()) {
    echo_fail(403, 'NOT_AUTHORIZED', 'You do not have permission to do that');
    $sql_conn->close();
    return;
  }

  // PERFORMING REQUEST
  $username_obj = DatabaseHelper::fetch_one($sql_conn, 'SELECT * FROM usernames WHERE username=?', array(array('s', $username))); 
  if($username_obj === null) {
    UserMapping::create_by_username($sql_conn, $username);
    $username_obj = DatabaseHelper::fetch_one($sql_conn, 'SELECT * FROM usernames WHERE username=?', array(array('s', $username)));
    if($username_obj === null) {
      echo_fail(500, 'ASSUMPTION_FAILED', 'Failed to create username in database!');
      $sql_conn->close();
      return;
    }
  }

  DatabaseHelper::execute($sql_conn, 'INSERT INTO red_flag_queue_spots (report_id, username_id, created_at, started_at, completed_at) VALUES (NULL, ?, NOW(), NULL, NULL)', array(array('i', $username_obj->id)));
  echo_success('REQUEST_RED_FLAG_REPORT_SUCCESS', array());
  $sql_conn->close();
}else {
  echo_fail(405, 'METHOD_NOT_ALLOWED', 'You must use a POST request at this endpoint');
}
?>
