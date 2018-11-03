<?php
/*
 * Returns a list of red flag reports on a given username, which can be 
 * fetched with red_flag_report (no s)
 *
 * Params:
 *   username - who you want reports on
 *
 * Result:
 *   reports = { { id = int, created_at=timestamp or null, started_at=timestamp or null, completed_at=timestamp or null },... }
 */
require_once 'api/common.php';
require_once 'database/helper.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
  // DEFAULT ARGUMENTS
  $username = null;

  // PARSING ARGUMENTS
  if (isset($_POST['username'])) {
    $username = $_POST['username'];
  }

  // VALIDATING ARGUMENTS 
  if ($username === null) {
    echo_fail(400, 'ARGUMENT_MISSING', 'username cannot be missing!');
    return;    
  }

  if (strlen($username) <= 3) {
    echo_fail(400, 'ARGUMENT_INVALID', 'username is too short (must be at least 3 characters)');
    return;
  }

  if (strpos($username, '/') !== false) {
    echo_fail(400, 'ARGUMENT_INVALID', 'username contains invalid character \'/\'');
    return;
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
    echo_success('RED_FLAG_REPORTS_SUCCESS', array(
      'reports' => array()
    ));
    $sql_conn->close();
    return;
  }

  $cleaned_reports = array();
  $reports = DatabaseHelper::fetch_all($sql_conn, 'SELECT * FROM red_flag_reports WHERE username_id=? ORDER BY created_at DESC', array(array('i', $username_obj->id)));
  foreach($reports as $report) {
    $cleaned_reports[] = array(
      'id' => $report->id,
      'created_at' => strtotime($report->created_at) * 1000,
      'started_at' => ($report->started_at === null) ? null : (strtotime($report->started_at) * 1000),
      'completed_at' => ($report->completed_at === null) ? null : (strtotime($report->completed_at) * 1000)
    );
  }

  echo_success('RED_FLAG_REPORTS_SUCCESS', array(
    'reports' => $cleaned_reports
  ));
  $sql_conn->close();
}else {
  echo_fail(405, 'METHOD_NOT_ALLOWED', 'You must use a POST request at this endpoint');
}
?>

