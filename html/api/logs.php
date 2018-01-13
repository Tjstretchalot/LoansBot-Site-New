<?php
require_once 'api/auth.php';

if($_SERVER['REQUEST_METHOD'] === 'GET') {
  if(!is_moderator()) {
    echo_fail(403, 'NOT_AUTHORIZED', 'You do not have permission to use this endpoint');
    $sql_conn->close();
    return;
  }
  $sql_conn->close();
  
  $s = file_get_contents( '/home/timothy/Documents/LoansBot/rolling/all.log' );
  if(!$s) {
    echo_fail(500, 'SERVER ERROR', 'Failed to locate log file');
    return;
  }

  echo $s;
}else {
  echo_fail(405, 'METHOD_NOT_ALLOWED', 'You must use a GET request at this endpoint');
}
?>
?>
