<?php
require_once 'database/common.php';
require_once 'api/common.php';
require_once 'api/select_query_helper.php';
require_once 'api/users_helper.php';

if($_SERVER['REQUEST_METHOD'] === 'GET') {
  $params = $_GET;
  $helper = new SelectQueryHelper('users');

  function handle_param_error($result, $fail_callback = null) {
    if($result !== null) {
      if($fail_callback !== null) {
        $fail_callback();
      }

      echo_fail(400, $result['error_mess'], $result['error_ident']);
      die();
      return;
    }
  }

  handle_param_error(UserParameterParser::parse_format($helper, $params));
  handle_param_error(UserParameterParser::parse_limit($helper, $params));

  handle_param_error(UserParameterParser::return_id($helper, $params));
  handle_param_error(UserParameterParser::return_claimed($helper, $params));
  handle_param_error(UserParameterParser::return_created_at($helper, $params));
  handle_param_error(UserParameterParser::return_updated_at($helper, $params));
  handle_param_error(UserParameterParser::return_username($helper, $params));

  handle_param_error(UserParameterParser::filter_by_id($helper, $params));
  handle_param_error(UserParameterParser::filter_by_username($helper, $params));


  require_once 'connect_and_get_loggedin.php';

  $auth = 0;
  if(isset($logged_in_user) && ($logged_in_user !== null)) {
    $auth = $logged_in_user->auth;
  }

  handle_param_error($helper->check_authorization($auth));
  handle_param_error($helper->check_sanity($sql_conn));

  $query = $helper->build_query();

  $err_prefix = 'html/api/users.php';
  check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare($query));
  $helper->bind_params($sql_conn, $stmt);
  check_db_error($sql_conn, $err_prefix, $stmt->execute());
  check_db_error($sql_conn, $err_prefix, $res = $stmt->get_result());

  $response_users = array();
  $row = $res->fetch_assoc();
  while($row !== null) {
    $response_users[] = $helper->create_result_from_row($row);
    $row = $res->fetch_assoc();
  }
  $res->close();
  $stmt->close();
  $sql_conn->close();

  echo_success($response_type, array( 'users' => $response_users ));
}else {
  echo_fail(405, 'METHOD_NOT_ALLOWED', 'You must use a GET request at this endpoint');
}
?>

