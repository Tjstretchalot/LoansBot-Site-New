<?php
/*
 * Contains various authorization like functions that are used outside of 
 * logging in and logging out. Acts as an extension to connect_and_get_loggedin.php
 * so this also opens the sql connection
 */

include_once 'connect_and_get_loggedin.php';
include_once 'database/helper.php';

/*
 * Determine if the user is logged in
 */
function is_logged_in() {
  global $logged_in_user;

  return (isset($logged_in_user) && $logged_in_user !== null);
}

/*
 * Determine if the user is logged in and is "trusted" insofar as either
 * a manual flag or completed 5 loans as lender
 */
function is_trusted() {
  global $logged_in_user, $sql_conn;

  if(!is_logged_in()) {
    return false;
  }
  if($logged_in_user->auth < 1) {
    $rel_loans_row = DatabaseHelper::fetch_one($sql_conn, 'SELECT COUNT(*) as num_loans_as_lend FROM loans WHERE lender_id=? AND (principal_cents = principal_repayment_cents OR unpaid = 1)', array(array('i', $logged_in_user->id)));
    if($rel_loans_row->num_loans_as_lend < 5) {
      return false;
    }

    $blacklist_row = DatabaseHelper::fetch_one($sql_conn, 'SELECT 1 as x FROM promo_blacklist_users WHERE user_id=? AND removed_at IS NULL LIMIT 1', array(array('i', $logged_in_user->id)));
    if($blacklist_row !== null) {
      return false;
    }
  }
  return true;
}

function is_moderator() {
  global $logged_in_user;

  if(!is_logged_in())
    return false;

  return $logged_in_user->auth >= 5;
}

/*
 * Set up our response to indicate that the user is not authorized. Must
 * be called prior to any actual HTML being outputted 
 */
function on_failed_auth() {
  global $sql_conn;

  http_response_code(401);
  echo '<html><head><title>Not Authorized</title></head><body><p>You are not authorized to view this page. <a href="/index.php">Go Back</a></p></body></html>';

  if(isset($sql_conn) && $sql_conn !== null) {
    $sql_conn->close();
  }
}
?>
