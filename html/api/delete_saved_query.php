<?php
/*
   Parameters: 
     str_id - the string identifier for the query

   Returns DELETE_SAVED_QUERY or FAILURE

   DELETE_SAVED_QUERY looks like:

   {
     "result_type": "DELETE_SAVED_QUERY",
     "success": true
   }
*/

require_once 'database/common.php';
require_once 'api/common.php';
require_once 'api/query_helper.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
  /* DEFAULT ARGUMENTS */
  $str_id = null;

  /* PARSING ARGUMENTS */
  if(isset($_POST['str_id'])) {
    $str_id = $_POST['str_id'];
  }

  /* VALIDATING ARGUMENTS */
  if($str_id === null) {
    echo_fail(400, 'INVALID_ARGUMENT', 'str_id is required!');
    return;
  }
 
  $act_id = hexdec($str_id);
  if($act_id < 0) {
    echo_fail(400, 'UNKNOWN_QUERY', 'No query with that identifier found!');
    return;
  }

  /* VALIDATING AUTHORIZATION */ 
  require_once 'connect_and_get_loggedin.php';

  if(!isset($logged_in_user) || $logged_in_user === null) {
    echo_fail(400, 'NOT_AUTHORIZED', 'You must be logged in to delete saved queries!');
    $sql_conn->close();
    return;
  }

  /* PERFORMING REQUEST */
  $err_prefix = 'html/api/delete_saved_query.php';
  $sql_params = array();
  $query = 'SELECT id, always_shared FROM saved_queries WHERE id = ?';
  $sql_params[] = array('i', $act_id);
  
  check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare($query));
  QueryHelper::bind_params($sql_conn, $stmt, $sql_params);
  check_db_error($sql_conn, $err_prefix, $stmt->execute());
  check_db_error($sql_conn, $err_prefix, $res = $stmt->get_result());

  $saved_query_row = $res->fetch_assoc();
  $res->close();
  $stmt->close();

  if($saved_query_row === null) {
    echo_fail(400, 'UNKNOWN_QUERY', 'No query with that identifier found!');
    $sql_conn->close();
    return;
  }


  $sql_params = array();
  $query = 'SELECT id, saved_query_id, user_id, owned, inverse FROM saved_query_users WHERE saved_query_id=? AND user_id=?';
  $sql_params[] = array('i', $act_id);
  $sql_params[] = array('i', $logged_in_user->id);
  check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare($query));
  QueryHelper::bind_params($sql_conn, $stmt, $sql_params);
  check_db_error($sql_conn, $err_prefix, $stmt->execute());
  check_db_error($sql_conn, $err_prefix, $res = $stmt->get_result());

  $row = $res->fetch_assoc();
  $res->close();
  $stmt->close();

  if($saved_query_row['always_shared'] === 1) {
    if($row !== null) {
      if($row['inverse'] === 1) {
        echo_fail(400, 'UNKNOWN_QUERY', 'No query with that identifier found!');
        return;
      }else {
        $sql_params = array();
        $query = 'UPDATE saved_query_users SET owned=0, inverse=1 WHERE id=?';
        $sql_params[] = array('i', $row['id']);
        check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare($query));
        QueryHelper::bind_params($sql_conn, $stmt, $sql_params);
        check_db_error($sql_conn, $err_prefix, $stmt->execute());
        $stmt->close();
        $sql_conn->close();
        echo_success('DELETE_SAVED_QUERY', array()); 
        return;
      }
    }

    $sql_params = array();
    $query = 'INSERT INTO saved_query_users (saved_query_id, user_id, owned, inverse) VALUES (?, ?, 0, 1)';
    $sql_params[] = array('i', $act_id);
    $sql_params[] = array('i', $logged_in_user->id);
    check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare($query));
    QueryHelper::bind_params($sql_conn, $stmt, $sql_params);
    check_db_error($sql_conn, $err_prefix, $stmt->execute());
    $stmt->close();
    $sql_conn->close();
    echo_success('DELETE_SAVED_QUERY', array()); 
    return;
  }

  if($row === null) {
    echo_fail(400, 'UNKNOWN_QUERY', 'No query with that identifier found!');
    $sql_conn->close();
    return;
  }

  $binding_id = $row['id'];
  $actually_delete_it = $row['owned'] === 1;

  $sql_params = array();
  $query = 'DELETE FROM saved_query_users WHERE id=?';
  $sql_params[] = array('i', $binding_id);
  check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare($query));
  QueryHelper::bind_params($sql_conn, $stmt, $sql_params);
  check_db_error($sql_conn, $err_prefix, $stmt->execute());
  $stmt->close();

  if($actually_delete_it) {
    $sql_params = array();
    $query = 'DELETE FROM saved_query_params WHERE saved_query_id=?';
    $sql_params[] = array('i', $act_id);

    check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare($query));
    QueryHelper::bind_params($sql_conn, $stmt, $sql_params);
    check_db_error($sql_conn, $err_prefix, $stmt->execute());
    $stmt->close();
    
    $sql_params = array();
    $query = 'DELETE FROM saved_queries WHERE id=?';
    $sql_params[] = array('i', $act_id);

    check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare($query));
    QueryHelper::bind_params($sql_conn, $stmt, $sql_params);
    check_db_error($sql_conn, $err_prefix, $stmt->execute());
    $stmt->close();
  }

  $sql_conn->close();
  echo_success('DELETE_SAVED_QUERY', array()); 
}else {
  echo_fail(405, 'METHOD_NOT_ALLOWED', 'You must use a POST request at this endpoint');
}
?>

