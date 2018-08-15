<?php
require_once 'api/common.php';
require_once 'database/common.php';
require_once 'database/helper.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
  /* DEFAULT ARGUMENTS */
  $id = null;
  $body = null;
  $reason = null;

  /* PARSING ARGUMENTS */
  if(isset($_POST['id']) && is_numeric($_POST['id'])) {
    $id = intval($_POST['id']);
  }
  if(isset($_POST['body'])) {
    $body = $_POST['body'];
  }
  if(isset($_POST['reason'])) {
    $reason = $_POST['reason'];
  }
  /* VALIDATING ARGUMENTS */
  if($id === null) {
    echo_fail(400, 'ARGUMENT_MISSING', 'ID cannot be empty!');
    return;
  }
  if($body === null) {
    echo_fail(400, 'ARGUMENT_MISSING', 'Body cannot be empty!');
    return;
  }
  if($reason === null) {
    echo_fail(400, 'ARGUMENT_MISSING', 'Reason cannot be empty!');
    return;
  }
  if(strlen($reason) < 5) {
    echo_fail(400, 'ARGUMENT_INVALID', 'Reason must be at least 5 characters long!');
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
  $response = DatabaseHelper::fetch_one($sql_conn, 'SELECT id, response_body FROM responses WHERE id=?', array(array('i', $id)));
  if($response === null) {
    echo_fail(400, 'RESPONSE_NOT_FOUND', 'Failed to find any response with given id');
    $sql_conn->close();
    return;
  }

  DatabaseHelper::execute($sql_conn, 'INSERT INTO response_histories (response_id, user_id, old_raw, new_raw, reason, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())', array(
        array('i', $response->id),
        array('i', $logged_in_user->id),
        array('s', $response->response_body),
        array('s', $body),
        array('s', $reason)
  ));

  DatabaseHelper::execute($sql_conn, 'UPDATE responses SET response_body=?, updated_at=NOW() WHERE id=?', array(
        array('s', $body),
        array('i', $response->id)
  ));
  echo_success('EDIT_RESPONSE_SUCCESS', array());
  $sql_conn->close();
}else {
  echo_fail(405, 'METHOD_NOT_ALLOWED', 'You must use a POST request at this endpoint');
}
?>

