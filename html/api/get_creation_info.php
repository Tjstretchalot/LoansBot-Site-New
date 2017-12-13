<?php
/*
   Parameters: 
     loan_id - either a loan id or a list of loan ids seperated by spaces

   Returns LOAN_CREATION_INFO, or FAILURE

   LOAN_CREATION_INFO looks like:

   {
     "result_type": "LOAN_CREATION_INFO",
     "success": true,
     "results": {
       "57": null // this means we found no creation info for the loan id 57
       "58": {
         "type": 0, // this means the loan was created due to an action on reddit
         "thread": "a valid url goes here" // where the action took place
       },
       "73": {
         "type": 1, // this means the loan was created on redditloans
         "user_id": 23 // this is the admin that created the loan (only provided if logged in as a moderator)
       },
       "125": {
         "type": 2 // this is a loan that was created due to a paid summon when teh database was 
                   // being regenerated in ~march 2016, but no $loan command was ever found.
       }
     }
   }
*/

require_once 'database/common.php';
require_once 'api/common.php';
require_once 'api/query_helper.php';

if($_SERVER['REQUEST_METHOD'] === 'GET') {
  /* DEFAULT ARGUMENTS */
  $loan_ids = null;

  /* PARSING ARGUMENTS */
  if(isset($_GET['loan_id'])) {
    $spl = explode(' ', $_GET['loan_id']);
    $loan_ids = array();
    foreach($spl as $str) {
      if(!is_numeric($str)) {
        echo_fail(400, 'INVALID_ARGUMENT', 'Cannot parse given loan ids to numbers after splitting using a space delimiter!');
        return;
      }

      $loan_ids[] = intval($str);
    }
  }

  /* VALIDATING ARGUMENTS */
  if($loan_ids === null) {
    echo_fail(400, 'INVALID_ARGUMENT', 'loan_id is required at this endpoint');
    return;
  }

  /* VALIDATING AUTHORIZATION */ 
  require_once 'connect_and_get_loggedin.php';

  $auth = 0;
  if(isset($logged_in_user) && ($logged_in_user !== null)) {
    $auth = $logged_in_user->auth;
  }

  /* PERFORMING REQUEST */
  $err_prefix = 'html/api/get_creation_info.php';
  $sql_params = array();
  $query = 'SELECT * FROM creation_infos WHERE loan_id IN (';
  $first = true;
  foreach($loan_ids as $lid) {
    if($first) {
      $first = false;
    }else {
      $query .= ', ';
    }
    $query .= '?';
    $sql_params[] = array('i', $lid);
  }
  $query .= ')';
  check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare($query));
  QueryHelper::bind_params($sql_conn, $stmt, $sql_params);
  check_db_error($sql_conn, $err_prefix, $stmt->execute());
  check_db_error($sql_conn, $err_prefix, $res = $stmt->get_result());

  $results_to_return = array();
  foreach($loan_ids as $lid) {
    $results_to_return[strval($lid)] = null;
  }

  while(($row = $res->fetch_assoc()) !== null) {
    $tmp = array();
    $tmp['type'] = $res['type'];
    if($res['type'] === 0) {
      $tmp['thread'] = $res['thread'];
    }elseif($res['type'] === 1) {
      if($auth >= $MODERATOR_PERMISSION) {
        $tmp['user_id'] = $res['user_id'];
      }
    }
    $results_to_return[strval($tmp['loan_id'])] = $tmp;
  }

  $res->close();
  $stmt->close();
  $sql_conn->close();
  echo_success('LOAN_CREATION_INFO', array('results' => $results_to_return)); 
}else {
  echo_fail(405, 'METHOD_NOT_ALLOWED', 'You must use a GET request at this endpoint');
}
?>
