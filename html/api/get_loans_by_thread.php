<?php
/*
  Parameters:
    thread - the url of the request thread you want the loans from

  Result:
    {
      'result_type': 'LOANS_ULTRACOMPACT'
      'success': true,
      loans: [loan_id, loan_id, ....]
    }
*/

require_once 'database/common.php';
require_once 'api/common.php';
require_once 'database/helper.php';

if($_SERVER['REQUEST_METHOD'] === 'GET') {
  /* DEFAULT ARGUMENTS */
  $thread = null;

  /* PARSING ARGUMENTS */
  if(isset($_GET['thread']) && strlen($_GET['thread']) > 0) {
    $thread = $_GET['thread'];
  }

  /* VALIDATING ARGUMENTS */
  if($thread === null) {
    echo_fail(400, 'INVALID_ARGUMENT', 'thread is required at this endpoint!');
    return;
  }

  if(strlen($thread) < 3) {
    echo_fail(400, 'INVALID_ARGUMENT', 'thread must be a valid url');
    return;
  }

  /* VALIDATING AUTHORIZATION */
  $err_prefix = 'html/api/get_request_thread.php';
  require_once 'api/auth.php';

  /* PERFORMING REQUEST */
  $sql = <<<SQL
SELECT creation_infos.loan_id AS loan_id FROM creation_infos
JOIN loans ON loans.id=creation_infos.loan_id
WHERE loans.deleted = 0 AND creation_infos.thread = ?
SQL;

  $creation_infos = DatabaseHelper::fetch_all($sql_conn, $sql, array(array('s', $thread)));
  $compact = array();
  foreach($creation_infos as $ci) {
    $compact[] = $ci->loan_id;
  }
  echo_success('LOANS_ULTRACOMPACT', array('loans' => $compact));
}else {
  echo_fail(405, 'METHOD_NOT_ALLOWED', 'You must use a GET request at this endpoint');
}
?>
