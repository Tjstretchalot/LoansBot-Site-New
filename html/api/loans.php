<?php
require_once('database/common.php');
require_once('api/common.php');

if($_SERVER['REQUEST_METHOD'] === 'GET') {
  echo_fail(400, 'REPAIRS_IN_PROGRESS', 'Repairs in progress');
}else {
  echo_fail(405, 'METHOD_NOT_ALLOWED', 'You must use a GET request at this endpoint');
}
?>
