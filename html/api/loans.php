<?php
require_once 'database/common.php';
require_once 'api/common.php';
require_once 'api/select_query_helper.php';
require_once 'api/loans_helper.php';

if($_SERVER['REQUEST_METHOD'] === 'GET') {
  error_log('query.php start');
  $before_params = microtime();
  $params = $_GET;
  $helper = new SelectQueryHelper('loans');

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

  // order irrelevant params
  handle_param_error(ParameterParser::parse_format($helper, $params));
  handle_param_error(ParameterParser::parse_limit($helper, $params));
  handle_param_error(ParameterParser::parse_include_deleted($helper, $params));
  handle_param_error(ParameterParser::parse_order_by($helper, $params));

  // result order irrelevant because they are split out with a return_ function
  // and are not coupled with anything
  handle_param_error(ParameterParser::parse_after_time($helper, $params));
  handle_param_error(ParameterParser::parse_before_time($helper, $params));
  handle_param_error(ParameterParser::parse_principal_cents($helper, $params));
  handle_param_error(ParameterParser::parse_principal_repayment_cents($helper, $params));

  // result order relevant because things are coupled (couples are seperated with
  // a newline)
  handle_param_error(ParameterParser::parse_borrower_id($helper, $params));
  handle_param_error(ParameterParser::parse_borrower_name($helper, $params));

  handle_param_error(ParameterParser::parse_lender_id($helper, $params));
  handle_param_error(ParameterParser::parse_lender_name($helper, $params));

  handle_param_error(ParameterParser::parse_includes_user_id($helper, $params));
  handle_param_error(ParameterParser::parse_includes_user_name($helper, $params));

  handle_param_error(ParameterParser::parse_unpaid($helper, $params));
  handle_param_error(ParameterParser::parse_repaid($helper, $params));

  // order relevant params
  handle_param_error(ParameterParser::parse_id($helper, $params));
  handle_param_error(ParameterParser::return_lender_id($helper, $params));
  handle_param_error(ParameterParser::return_borrower_id($helper, $params));
  handle_param_error(ParameterParser::return_principal_cents($helper, $params));
  handle_param_error(ParameterParser::return_principal_repayment_cents($helper, $params));
  handle_param_error(ParameterParser::return_unpaid($helper, $params));
  handle_param_error(ParameterParser::return_created_at($helper, $params));
  handle_param_error(ParameterParser::return_updated_at($helper, $params));
  handle_param_error(ParameterParser::return_deleted_information($helper, $params));
  handle_param_error(ParameterParser::parse_include_latest_repayment_at($helper, $params));

  // params that aren't processed unless order is irrelevant (format >= 2)
  handle_param_error(ParameterParser::fetch_usernames($helper, $params));

  // determining if modification is requested
  $modify = 0;
  if(isset($params['modify']) && is_numeric($params['modify'])) {
    $_modify = intval($params['modify']);

    if(in_array($_modify, array(0, 1))) {
      $modify = $_modify;
    }
  }

  if($modify !== 0 && $helper->format < 2) {
    handle_param_error(array('error_ident' => 'INVALID_ARGUMENTS', 'error_mess' => 'Modifying requires a format of 2 or higher'));
    return;
  }


  require_once 'connect_and_get_loggedin.php';

  $auth = 0;
  if(isset($logged_in_user) && ($logged_in_user !== null)) {
    $auth = $logged_in_user->auth;
  }

  if($modify === 1 && $auth < 5) {
    handle_param_error(array('error_mess' => 'You do not have authorization to modify loans.', 'error_ident' => 'NOT_AUTHORIZED'));
    return;
  }

  if($modify === 1) {
    $helper->use_temporary_table = 'loans_modify_result_tmp';
  }

  handle_param_error($helper->check_authorization($auth));
  handle_param_error($helper->check_sanity());

  $after_params = microtime();
  error_log('parsing params took: ' . ($after_params - $before_params));
  unset($after_params);
  unset($before_params);

  $before_build_query = microtime();
  $query = $helper->build_query();
  $after_build_query = microtime();
  error_log('building query took: ' . ($after_build_query - $before_build_query));
  unset($before_build_query);
  unset($after_build_query);

  $before_run_query = microtime():
  $err_prefix = 'html/api/loans.php';
  check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare($query));
  $helper->bind_params($sql_conn, $stmt);
  check_db_error($sql_conn, $err_prefix, $stmt->execute());
  $after_run_query = microtime();
  error_log('running the select query took ' . ($after_run_query - $before_run_query));
  unset($before_run_query);
  unset($after_run_query);

  if($modify === 0) {
    $before_results = microtime();
    check_db_error($sql_conn, $err_prefix, $res = $stmt->get_result());
    
    $response_loans = array();
    $row = $res->fetch_assoc();
    while($row !== null) {
      $response_loans[] = $helper->create_result_from_row($row);
      $row = $res->fetch_assoc();
    }
    $res->close();
    $after_results = microtime();
    error_log('fetching results took ' . ($after_results - $before_results));
    unset($after_results);
    unset($before_results);
  }
  $stmt->close();
  
  $drop_temp_table = function() use ($helper, $sql_conn) {
    $err_prefix = 'loans.php#drop_temp_table';
    check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('DROP TABLE ' . $helper->use_temporary_table));
    check_db_error($sql_conn, $err_prefix, $stmt->execute());
    $stmt->close();
  };

  if($modify === 1) {
    $modify_reason = null;
    if(isset($params['modify_reason'])) {
      $modify_reason = $params['modify_reason'];

      if(strlen($modify_reason) < 5)
        handle_param_error(array('error_ident' => 'INVALID_ARGUMENT', 'error_mess' => 'Modify reason must be at least 5 characters long'));
    }else {
      handle_param_error(array('error_ident' => 'INVALID_ARGUMENT', 'error_mess' => 'Modifying requires a modify_reason!'));
    }

    $upd_helper = new UpdateQueryHelper('loans', $helper->use_temporary_table); 
    
    // parsing modify related parameters
    handle_param_error(ModifyParameterParser::add_number_results_sanity_limits($helper, $upd_helper, $params), $drop_temp_table);
    handle_param_error(ModifyParameterParser::limit_to_returned_rows($helper, $upd_helper, $params), $drop_temp_table);
    handle_param_error(ModifyParameterParser::return_boilerplate($helper, $upd_helper, $params), $drop_temp_table);
    handle_param_error(ModifyParameterParser::update_updated_at($helper, $upd_helper, $params), $drop_temp_table);
    handle_param_error(ModifyParameterParser::parse_set_borrower_id($helper, $upd_helper, $params), $drop_temp_table);
    handle_param_error(ModifyParameterParser::parse_set_lender_id($helper, $upd_helper, $params), $drop_temp_table);
    handle_param_error(ModifyParameterParser::parse_set_borrower_name($helper, $upd_helper, $params), $drop_temp_table);
    handle_param_error(ModifyParameterParser::parse_set_lender_name($helper, $upd_helper, $params), $drop_temp_table);
    handle_param_error(ModifyParameterParser::parse_set_principal_cents($helper, $upd_helper, $params), $drop_temp_table);
    handle_param_error(ModifyParameterParser::parse_set_principal_repayment_cents($helper, $upd_helper, $params), $drop_temp_table);
    handle_param_error(ModifyParameterParser::parse_set_unpaid($helper, $upd_helper, $params), $drop_temp_table);
    handle_param_error(ModifyParameterParser::parse_set_deleted($helper, $upd_helper, $params), $drop_temp_table);
    handle_param_error(ModifyParameterParser::parse_set_deleted_reason($helper, $upd_helper, $params), $drop_temp_table);

    
    // final verification that the parameters make sense
    handle_param_error($upd_helper->check_sanity($sql_conn), $drop_temp_table);
    handle_param_error($upd_helper->run_previews($sql_conn), $drop_temp_table);
    handle_param_error($upd_helper->check_authorization($sql_conn, $auth), $drop_temp_table);

    // executing the query
    $query = $upd_helper->build_query();
    error_log($query);
    check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare($query));
    $upd_helper->bind_params($sql_conn, $stmt);
    check_db_error($sql_conn, $err_prefix, $stmt->execute());

    $stmt->close();

    // save the admin update
    $query =  'INSERT INTO admin_updates (';
    $query .=  'loan_id, ';
    $query .=  'user_id, ';
    $query .=  'reason, ';
    $query .=  'old_lender_id, ';
    $query .=  'old_borrower_id, ';
    $query .=  'old_principal_cents, ';
    $query .=  'old_principal_repayment_cents, ';
    $query .=  'old_unpaid, ';
    $query .=  'old_deleted, ';
    $query .=  'old_deleted_reason,';
    $query .=  'new_lender_id, ';
    $query .=  'new_borrower_id, ';
    $query .=  'new_principal_cents, ';
    $query .=  'new_principal_repayment_cents, ';
    $query .=  'new_unpaid, ';
    $query .=  'new_deleted, ';
    $query .=  'new_deleted_reason, ';
    $query .=  'created_at, ';
    $query .=  'updated_at';
    $query .= ') ';
    $query .= 'SELECT ';
    $query .=   'new_info.id, ';
    $query .=   '?, ';
    $query .=   '?, ';
    $query .=   'old_info.loan_lender_id, ';
    $query .=   'old_info.loan_borrower_id, ';
    $query .=   'old_info.loan_principal_cents, ';
    $query .=   'old_info.loan_principal_repayment_cents, ';
    $query .=   'old_info.loan_unpaid, ';
    $query .=   'old_info.loan_deleted, ';
    $query .=   'old_info.loan_deleted_reason, ';
    $query .=   'new_info.lender_id, ';
    $query .=   'new_info.borrower_id, ';
    $query .=   'new_info.principal_cents, ';
    $query .=   'new_info.principal_repayment_cents, ';
    $query .=   'new_info.unpaid, ';
    $query .=   'new_info.deleted, ';
    $query .=   'new_info.deleted_reason, ';
    $query .=   'NOW() as now1, ';
    $query .=   'NOW() as now2 ';
    $query .= 'FROM ';
    $query .= $helper->use_temporary_table . ' AS old_info ';
    $query .= 'INNER JOIN ';
    $query .= 'loans as new_info ';
    $query .= 'ON old_info.loan_id = new_info.id';
    error_log($query); 
    check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare($query));
    check_db_error($sql_conn, $err_prefix, $stmt->bind_param('is', $logged_in_user->id, $modify_reason));
    check_db_error($sql_conn, $err_prefix, $stmt->execute());

    $stmt->close();

    // generate response
    check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('SELECT * FROM ' . $helper->use_temporary_table));
    check_db_error($sql_conn, $err_prefix, $stmt->execute());
    check_db_error($sql_conn, $err_prefix, $res = $stmt->get_result());
    $response_loans = array();
    $row = $res->fetch_assoc();
    while($row !== null) {
      $tmp = $helper->create_result_from_row($row);
      $upd_helper->modify_result_from_row($row, $tmp);
      $response_loans[] = $tmp;
      $row = $res->fetch_assoc();
    }
    $res->close();
    $stmt->close();
    
    // final cleanup
    $upd_helper->run_cleanup($sql_conn);
    $drop_temp_table();
  }

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
