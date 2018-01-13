<?php
require_once 'api/auth.php';
require_once 'api/common.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
  if(!is_moderator()) {
    echo_fail(403, 'NOT_AUTHORIZED', 'You do not have permission to use this endpoint');
    $sql_conn->close();
    return;
  }
  $sql_conn->close();

  exec('touch /home/timothy/Documents/LoansBot/restart_requested.touchme');

  echo_success('RESTART_SUCCESS', array());
}else {
  echo_fail(405, 'METHOD_NOT_ALLOWED', 'You must use a POST request at this endpoint');
}
?>

