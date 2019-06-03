<?php
require_once 'api/common.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  /* DEFAULT ARGUMENTS */
  $optout = null;

  /* PARSING ARGUMENTS */
  if (isset($_POST['optout']) && is_numeric($_POST['optout'])) {
    $optout = intval($_POST['optout']);
  }

  /* VERIFYING ARGUMENTS */
  if ($optout !== null && $optout !== 0 && $optout !== 1) {
    echo_fail(400, 'INVALID_ARGUMENT', 'optout must be either 0, 1, or omitted');
    return;
  }

  /* VALIDATING AUTHORIZATION */
  require_once 'api/auth.php';
  if (!is_trusted()) {
    echo_fail(403, 'NOT_AUTHORIZED', 'You do not have permission to use this endpoint');
    $sql_conn->close();
    return;
  }
  $sql_conn->close();

  /* PERFORMING REQUEST */
  require_once 'database/helper.php';
  if ($optout === 0) {
    DatabaseHelper::execute($sql_conn, 'DELETE FROM response_opt_outs WHERE user_id=?', array(array('i', $logged_in_user->id)));
  } else if ($optout === 1) {
    $existing = DatabaseHelper::fetch_one($sql_conn, 'SELECT 1 FROM response_opt_outs WHERE user_id=?', array(array('i', $logged_in_user->id)));
    if (!$existing) {
      DatabaseHelper::execute($sql_conn, 'INSERT INTO response_opt_outs (user_id) VALUES (?)', array(array('i', $logged_in_user->id)));
    }
  } else {
    $existing = DatabaseHelper::fetch_one($sql_conn, 'SELECT 1 FROM response_opt_outs WHERE user_id=?', array(array('i', $logged_in_user->id)));
    echo_success('OPT_OUT_RESULT', array('opted_out' => ($existing !== null)));
    $sql_conn->close();
    return;
  }

  echo_success('OPT_OUT_SUCCESS', array());
  $sql_conn->close();
} else {
  echo_fail(405, 'METHOD_NOT_ALLOWED', 'You must use a POST request at this endpoint');
}
