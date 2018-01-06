<?php
require_once 'database/common.php';
require_once 'api/query_helper.php';
require_once 'database/helper.php';
require_once 'api/common.php';

/*
 * api/rechecks.php
 *
 * GET PARAMETERS:
 *   fullname = the fullname of the thing
 *  
 *   RESULTS:
 *     Results/FAILURE or 
 *     {
 *       success: true,
 *       result_type: "RECHECK_CHECK"
 *       found: true / false
 *     }
 *     
 * POST PARAMETERS
 *   fullname = the fullname of the thing
 *   forget   = 'true' or 'false' to indicate if the loansbot should forget about it
 *   recheck  = 'true' or 'false' to indicate if the recheck should be queued in the recheck pool (more reliable)
 *
 *   RESULTS:
 *     Results/FAILURE or
 *     {
 *       success: true,
 *       result_type: "RECHECK_MODIFY"
 *     }
 */
if($_SERVER['REQUEST_METHOD'] === 'GET') {
  $fullname = null;

  if(isset($_GET['fullname'])) {
    $fullname = $_GET['fullname'];
  }

  if($fullname === null) {
    echo_failure(400, 'INVALID_ARGUMENT', 'You must provide a fullname');
    return;
  }

  if(strlen($fullname) < 4) {
    echo_failure(400, 'INVALID_ARGUMENT', 'Fullnames must include the prefix! (t1_ or t3_)');
    return;
  }

  $prefix = substr($fullname, 0, 3);
  if($prefix !== 't1_' && $prefix !== 't3_') {
    echo_failure(400, 'INVALID_ARGUMENT', 'Fullnames must include the prefix! (t1_ or t3_)');
    return;
  }

  require_once 'api/auth.php';
  if(!is_trusted()) {
    echo_failure(401, 'NOT_AUTHORIZED', 'You are not authorized to make that request.');
    $sql_conn->close();
    return;
  }

  $row = DatabaseHelper::fetch_one($sql_conn, 'SELECT * FROM fullnames WHERE fullname = ?', array(array('s', $fullname)));
  $sql_conn->close();

  echo_success('RECHECK_CHECK', array('found' => $row !== null));
}elseif($_SERVER['REQUEST_METHOD'] === 'POST') {
  $fullname = null;
  $forget = null;
  $recheck = null;

  if(isset($_POST['fullname'])) {
    $fullname = $_POST['fullname'];
  }

  if(isset($_POST['forget'])) {
    if($_POST['forget'] === 'true') {
      $forget = true;
    }elseif($_POST['forget'] === 'false') {
      $forget = false;
    }
  }

  if(isset($_POST['recheck'])) {
    if($_POST['recheck'] === 'true') { 
      $recheck = true;
    }elseif($_POST['recheck'] === 'false') {
      $recheck = false;
    }
  }

  if($fullname === null) {
    echo_failure(400, 'INVALID_ARGUMENT', 'You must provide a fullname');
    return;
  }

  if(strlen($fullname) < 4) {
    echo_failure(400, 'INVALID_ARGUMENT', 'Fullnames must include the prefix! (t1_ or t3_)');
    return;
  }

  $prefix = substr($fullname, 0, 3);
  if($prefix !== 't1_' && $prefix !== 't3_') {
    echo_failure(400, 'INVALID_ARGUMENT', 'Fullnames must include the prefix! (t1_ or t3_)');
    return;
  }

  if($forget === null) {
    echo_failure(400, 'INVALID_ARGUMENT', 'forget must be the string \'true\' or \'false\'');
    return;
  };

  if($recheck === null) {
    echo_failure(400, 'INVALID_ARGUMENT', 'recheck must be the string \'true\' or \'false\'');
    return;
  }

  require_once 'api/auth.php';
  if(!is_trusted()) {
    echo_failure(401, 'NOT_AUTHORIZED', 'You are not authorized to make that request.');
    $sql_conn->close();
    return;
  }

  if($forget) {
    DatabaseHelper::execute($sql_conn, 'DELETE FROM fullnames WHERE fullname = ?', array(array('s', $fullname)));
  }

  if($recheck) {
    DatabaseHelper::execute($sql_conn, 'INSERT INTO rechecks (fullname, created_at, updated_at) VALUES (?, now(), now())', array(array('s', $fullname)));
  }

  echo_success('RECHECK_MODIFY', array());
  $sql_conn->close();
}else {
  echo_failure(405, 'METHOD_NOT_ALLOWED', 'You must use a GET or POST at this endpoint'); 
}
?>
