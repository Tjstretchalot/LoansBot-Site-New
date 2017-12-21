<?php
/*
   Parameters: 
     name - string (0-255 characters) does not need to be unique
     params - an array of arrays. Each inner array must start with a param name based on /js/query2_query_parameters.js
              after that, the array may have no more than 5 primitive types (number of string). The strings must not be 
              longer than 255 characters. No single parameter can have more than 1000characters when converted to 
              minified json. No more than 100 parameters are allowed.

   Returns SAVED_QUERY or FAILURE

   SAVED_QUERY looks like:

   {
     "result_type": "SAVED_QUERY",
     "success": true,
     "str_id": "unique_string123"
   }
*/

require_once 'database/common.php';
require_once 'api/common.php';
require_once 'api/query_helper.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
  /* DEFAULT ARGUMENTS */
  $name = null;
  $params = null;

  /* PARSING ARGUMENTS */
  if(isset($_POST['name'])) {
    $name = $_POST['name'];
  }

  if(isset($_POST['params'])) {
    $params = $_POST['params'];
  }

  /* VALIDATING ARGUMENTS */
  if('string' !== gettype($name)) {
    echo_fail(400, 'INVALID_ARGUMENT', 'Name is required and must be a string (0-255 characters)');
    return;
  }
  if(strlen($name) > 255) {
    echo_fail(400, 'INVALID_ARGUMENT', 'Name is too long (255 character max, got ' . strlen($name) . ' characters');
    return;
  }
  if('array' !== gettype($params)) {
    echo_fail(400, 'INVALID_ARGUMENT', 'Params must be an array, but it is ' . gettype($params));
    return;
  }
  if(count($params) > 100) {
    echo_fail(400, 'INVALID_ARGUMENT', 'Maximum parameter limit (100 is limit, you gave ' . count($params) . ')');
    return;
  }
  foreach($params as $ind=>$param) {
    if('array' !== gettype($param)) {
      echo_fail(400, 'INVALID_ARGUMENT', 'Expected params to be an array of arrays, but params[' . $ind . '] is a ' . gettype($param));
      return;
    }

    if(count($param) < 1) {
      echo_fail(400, 'INVALID_ARGUMENT', 'Params may not include empty arrays, but params[' . $ind . '] has no elements');
      return;
    }
    if('string' !== gettype($param[0])) {
      echo_fail(400, 'INVALID_ARGUMENT', 'Params must have a string for the first element in each array, but params[' . $ind . '][0] is a ' . gettype($param[0]));
      return;
    }
    if(count($param) > 6) {
      echo_fail(400, 'INVALID_ARGUMENT', 'Params may not include any arrays with more than 6 elements, but params[' . $ind . '] has ' . count($param) . ' elements');
      return;
    }

    foreach($param as $inner_ind=>$inner_param) {
      if('string' !== gettype($inner_param) && 'integer' !== gettype($inner_param) && 'double' !== gettype($inner_param) && 'boolean' !== gettype($inner_param)) {
        echo_fail(400, 'INVALID_ARGUMENT', 'Expected params to be an array of arrays of primitives but params[' . $ind . '][' . $inner_ind . '] is a ' . gettype($inner_param));
        return;
      }
    }
  }
  
  /* VALIDATING AUTHORIZATION */ 
  require_once 'connect_and_get_loggedin.php';

  if(!isset($logged_in_user) || $logged_in_user === null) {
    echo_fail(400, 'NOT_AUTHORIZED', 'You must be logged in to save queries!');
    $sql_conn->close();
    return;
  }

  /* PERFORMING REQUEST */
  $err_prefix = 'html/api/save_query.php';
  $sql_params = array();
  $query = 'INSERT INTO saved_queries (name, shared, always_shared, created_at, updated_at) VALUES (?, 0, 0, NOW(), NOW())';
  $sql_params[] = array('s', $name);
  
  check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare($query));
  QueryHelper::bind_params($sql_conn, $stmt, $sql_params);
  check_db_error($sql_conn, $err_prefix, $stmt->execute());

  $result_id = $sql_conn->insert_id;
  $stmt->close();

  $sql_params = array();
  $query = 'INSERT INTO saved_query_users (saved_query_id, user_id, owned, inverse) VALUES (?, ?, 1, 0)';
  $sql_params[] = array('i', $result_id);
  $sql_params[] = array('i', $logged_in_user->id);

  check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare($query));
  QueryHelper::bind_params($sql_conn, $stmt, $sql_params);
  check_db_error($sql_conn, $err_prefix, $stmt->execute());
  $stmt->close();

  $query = 'INSERT INTO saved_query_params (saved_query_id, name, options) VALUES (?, ?, ?)';
  foreach($params as $param) {
    $sql_params = array();
    $sql_params[] = array('i', $result_id);
    $sql_params[] = array('s', $param[0]);

    $formattable_options = array_slice($param, 1);
    $sql_params[] = array('s', json_encode($formattable_options));

    check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare($query));
    QueryHelper::bind_params($sql_conn, $stmt, $sql_params);
    check_db_error($sql_conn, $err_prefix, $stmt->execute());
    $stmt->close();
  }
  
  $sql_conn->close();
  echo_success('SAVED_QUERY', array('str_id' => dechex($result_id))); 
}else {
  echo_fail(405, 'METHOD_NOT_ALLOWED', 'You must use a POST request at this endpoint');
}
?>
