<?php
/*
   Parameters: 
     None

   Returns MY_SAVED_QUERIES or FAILURE

   MY_SAVED_QUERIES looks like:

   {
     "result_type": "MY_SAVED_QUERIES",
     "success": true,
     "queries": [
       "str_id": "unique string identifier",
       "name": "Param Name", // this is what the user named the query
       "parameters": [
         {
           "param_name": "param name", // this corresponds with /js/query2_query_parameters.js
           "options": [ ] // this varies based on the param name, but it is always an array
         }
       ]
     ]
   }
*/

require_once 'database/common.php';
require_once 'api/common.php';
require_once 'api/query_helper.php';

if($_SERVER['REQUEST_METHOD'] === 'GET') {
  /* DEFAULT ARGUMENTS */
  /* PARSING ARGUMENTS */
  /* VALIDATING ARGUMENTS */
  /* VALIDATING AUTHORIZATION */ 
  require_once 'connect_and_get_loggedin.php';

  $only_defaults = true;
  if(isset($logged_in_user) && $logged_in_user !== null) {
    $only_defaults = false;
  }

  /* PERFORMING REQUEST */
  $err_prefix = 'html/api/my_saved_queries.php';
  $sql_params = array();
  $query = 'SELECT sq.id as id, sq.name as name from saved_queries sq WHERE sq.always_shared = 1';
  if(!$only_defaults) {
    $query .= ' AND sq.id NOT IN (SELECT saved_query_id FROM saved_query_users WHERE user_id = ? AND inverse = 1)';
    $sql_params[] = array('i', $logged_in_user->id);
    $query .= ' OR sq.id IN (SELECT saved_query_id FROM saved_query_users WHERE user_id = ? AND inverse = 0)';
    $sql_params[] = array('i', $logged_in_user->id);
  }
  check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare($query));
  QueryHelper::bind_params($sql_conn, $stmt, $sql_params);
  check_db_error($sql_conn, $err_prefix, $stmt->execute());
  check_db_error($sql_conn, $err_prefix, $res = $stmt->get_result());
  
  $cache = array();
  while(($row = $res->fetch_assoc()) !== null) {
    $cache[] = $row;
  }

  $res->close();
  $stmt->close();
  
  $results_to_return = array();
  foreach($cache as $cached_row) {
    $result = array();
    $result['str_id'] = dechex($cached_row['id']);
    $result['name'] = $cached_row['name'];

    $usable_id = $cached_row['id'];
    $query = 'SELECT * FROM saved_query_params WHERE saved_query_id = ?';
    check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare($query));
    check_db_error($sql_conn, $err_prefix, $stmt->bind_param('i', $usable_id));
    check_db_error($sql_conn, $err_prefix, $stmt->execute());
    check_db_error($sql_conn, $err_prefix, $res = $stmt->get_result());
    
    $params = array();
    while(($row = $res->fetch_assoc()) !== null) {
      $params[] = array(
        'param_name' => $res['name'],
        'options' => json_decode($res['options'])
      );
    }
    $result['parameters'] = $params;
    $results_to_return[] = $result;

    $res->close();
    $stmt->close();
  }

  $sql_conn->close();
  echo_success('MY_SAVED_QUERIES', array('queries' => $results_to_return)); 
}else {
  echo_fail(405, 'METHOD_NOT_ALLOWED', 'You must use a GET request at this endpoint');
}
?>
