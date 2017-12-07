<?php
require_once 'database/common.php';

if(isset($_COOKIE['session_id'])) {
  require_once 'database/site_sessions.php';
  
  $sql_conn = create_db_connection();
  $row = SiteSessionMapping::fetch_by_session_id($sql_conn, $_COOKIE['session_id']);
  if($row !== null) {
    SiteSessionMapping::delete_by_id($sql_conn, $row->id);
  }
  $sql_conn->close();

  unset($_COOKIE['session_id']);
  setcookie('session_id', '', time() - 3600, '/');
}

header('Location: https://redditloans.com');
?>
