<?php
/*
 * Returns information specific to the given red flag report on the given user.
 *
 * Parameters:
 *   id - the id of the report
 *
 * Returns:
 *   username - who the report is about
 *   created_at - when the row was added to the database
 *   started_at - when the report first started being generated
 *   completed_at - when the report was completed
 *   flags = {
 *     {
 *        type - what type of flag this is 
 *        identifier - depends on the type
 *        description - describes why the flag was raised
 *        count - how many times this flag was raised
 *     },...
 *   }
 */
require_once 'api/common.php';
require_once 'database/helper.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
  // DEFAULT ARGUMENTS
  $id = null;

  // PARSING ARGUMENTS
  if(isset($_POST['id']) && is_numeric($_POST['id'])) {
    $id = intval($_POST['id']);
  }

  // VALIDATING ARGUMENTS 
  if($id === null) {
    echo_fail(400, 'ARGUMENT_MISSING', 'missing required argument id');
    return;
  }

  if($id < 0) {
    echo_fail(400, 'ARGUMENT_INVALID', 'id is always positive');
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
  $report = DatabaseHelper::fetch_one($sql_conn, 'SELECT * FROM red_flag_reports WHERE id=?', array(array('i', $id)));
  if($report === null) {
    echo_fail(404, 'NOT_FOUND', 'no report by that id');
    $sql_conn->close();
    return;
  }

  $username_obj = DatabaseHelper::fetch_one($sql_conn, 'SELECT username FROM usernames WHERE id=?', array(array('i', $report->username_id)));
  $username = $username_obj->username;

  $flags = DatabaseHelper::fetch_all($sql_conn, 'SELECT * FROM red_flags WHERE report_id=?', array(array('i', $id)));

  $cleaned = array(
    'username' => $username,
    'created_at' => strtotime($report->created_at) * 1000,
    'started_at' => ($report->started_at === null) ? null : (strtotime($report->started_at) * 1000),
    'completed_at' => ($report->completed_at === null) ? null : (strtotime($report->completed_at) * 1000)
  );

  $cleaned_flags = array();
  foreach($flags as $flag) {
    $cleaned_flags[] = array(
      'id' => $flag->id,
      'type' => $flag->type,
      'identifier' => $flag->identifier,
      'description' => $flag->description,
      'count' => $flag->count
    );
  }

  $cleaned['flags'] = $cleaned_flags
  echo_success('RED_FLAG_REPORT_SUCCESS', $cleaned);
  $sql_conn->close();
}else {
  echo_fail(405, 'METHOD_NOT_ALLOWED', 'You must use a POST request at this endpoint');
}
?>

