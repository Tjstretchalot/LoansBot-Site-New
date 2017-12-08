<?php
require_once 'database/common.php';
require_once 'api/common.php';
require_once 'api/loans_helper.php';

if($_SERVER['REQUEST_METHOD'] === 'GET') {
  $params = $_GET;
  $helper = new LoansHelper();

  function handle_param_error($result) {
    if($result !== null) {
      echo_fail(400, $result['err_mess'], $result['err_ident']);
      die();
      return;
    }
  }

  // order irrelevant params
  handle_param_error(ParameterParser::parse_format($helper, $params));
  handle_param_error(ParameterParser::parse_limit($helper, $params));

  // result order irrelevant because they are split out with a return_ function
  // and are not coupled with anything
  handle_param_error(ParameterParser::parse_after_time($helper, $params));
  handle_param_error(ParameterParser::parse_before_time($helper, $params));

  // result order relevant because things are coupled (couples are seperated with
  // a newline)
  handle_param_error(ParameterParser::parse_borrower_id($helper, $params));
  handle_param_error(ParameterParser::parse_borrower_name($helper, $params));

  handle_param_error(ParameterParser::parse_lender_id($helper, $params));
  handle_param_error(ParameterParser::parse_lender_name($helper, $params));

  handle_param_error(ParameterParser::parse_includes_user_id($helper, $params));

  // order relevant params
  handle_param_error(ParameterParser::parse_id($helper, $params));
  handle_param_error(ParameterParser::return_lender_id($helper, $params));
  handle_param_error(ParameterParser::return_borrower_id($helper, $params));
  handle_param_error(ParameterParser::return_created_at($helper, $params));

  // params that aren't processed unless order is irrelevant (format >= 2)
  handle_param_error(ParameterParser::fetch_usernames($helper, $params));

  require_once 'connect_and_get_loggedin.php';

  $auth = 0;
  if(isset($logged_in_user) && ($logged_in_user !== null)) {
    $auth = $logged_in_user->auth;
  }

  handle_param_error($helper->check_authorization($auth));

  $query = $helper->build_query();
  
  $err_prefix = 'html/api/loans.php';
  check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare($query));
  $helper->bind_params($sql_conn, $stmt);
  check_db_error($sql_conn, $err_prefix, $stmt->execute());
  check_db_error($sql_conn, $err_prefix, $res = $stmt->get_result());

  $response_loans = array();
  $row = $res->fetch_assoc();
  while($row !== null) {
    $response_loans[] = $helper->create_result_from_row($row);
    $row = $res->fetch_assoc();
  }
  $res->close();
  $stmt->close();
  
  $response_type = 'LOANS_ULTRACOMPACT';
  if($helper->format === 1) {
    $response_type = 'LOANS_COMPACT';
  }elseif($helper->format === 2) {
    $response_type = 'LOANS_STANDARD';
  }elseif($helper->format === 3) {
    $response_type = 'LOANS_EXTENDED';
  }

  echo_success($response_type, array( 'loans' => $response_loans ));
}else {
  echo_fail(405, 'METHOD_NOT_ALLOWED', 'You must use a GET request at this endpoint');
}
?>
