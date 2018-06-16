<?php
/*
  Parameters:
    loan_id - the id of the loan whose request thread you want

  Result:
    { 
      'result_type': 'LOAN_REQUEST_THREAD'
      'success': true,
      'request_thread': '<thread_url>'
    }
*/

require_once 'database/common.php';
require_once 'api/common.php';
require_once 'api/query_helper.php';

if($_SERVER['REQUEST_METHOD'] === 'GET') {
  /* DEFAULT ARGUMENTS */
  $loan_id = null;

  /* PARSING ARGUMENTS */
  if(isset($_GET['loan_id']) && is_numeric($_GET['loan_id'])) {
    $loan_id = intval($_GET['loan_id']);
  }

  /* VALIDATING ARGUMENTS */
  if($loan_id === null) {
    echo_fail(400, 'INVALID_ARGUMENT', 'loan_id is required at this endpoint!');
  }

  /* VALIDATING AUTHORIZATION */
  $err_prefix = 'html/api/get_request_thread.php';
  require_once 'auth.php';

  if(!is_moderator()) {
    $is_deleted = DatabaseHelper::fetch_one($sql_conn, 'SELECT deleted FROM loans where id=?', array(array('i', $loan_id)));
    if($is_deleted === null || $is_deleted->deleted) {
      echo_fail(404, 'LOAN_NOT_FOUND', 'There is no loan with the specified id!');
      return;
    }
  }

  /* PERFORMING REQUEST */
  $creation_info = DatabaseHelper::fetch_one($sql_conn, 'SELECT * FROM creation_infos WHERE loan_id=?', array(array('i', $loan_id)));
  
  if($creation_info === null) {
    echo_fail(404, 'LOAN_NOT_FOUND', 'There is no loan with the specified id!');
    return;
  }

  if($creation_info->type !== 0) {
    echo_fail(404, 'LOAN_EXISTS_NOT_BY_THREAD', 'The specified loan exists and has creation info, but not as a reddit url');
    return;
  }

  echo_success('LOAN_REQUEST_THREAD', array('request_thread' => $creation_info->thread));
}else {
  echo_fail(405, 'METHOD_NOT_ALLOWED', 'You must use a GET request at this endpoint');
}
?>
