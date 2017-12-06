<?php
  // Set $sql_conn to an active database connection and set $logged_in_user to a User 
  // if the user is logged in

  require('database/common.php');
  require('database/site_sessions.php');
  require('database/users.php');

  if($sql_conn === null) {
    $sql_conn = create_db_connection();
  }

  if($_COOKIE['session_id']) {
    $row = SiteSessionMapping::fetch_by_session_id($sql_conn, $_COOKIE['session_id']);


    if($row !== null) {
      $expires_at_php = strtotime($row->expires_at);
      if(time() > $expires_at_php) {
        unset($_COOKIE['session_id']);
        setcookie('session_id', '', time() - 3600, '/');
        SiteSessionMapping::delete_by_id($row->id);
      }else {
        $logged_in_user = UserMapping::fetch_by_id($sql_conn, $row->user_id);
      }
    }else {
      unset($_COOKIE['session_id']);
      setcookie('session_id', '', time() - 3600, '/');
    }
  }
?>