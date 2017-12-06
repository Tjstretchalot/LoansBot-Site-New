<?php
  /**
   * Initializes a connection to the MySQL database on the
   * production server and returns it as a mysqli
   *
   * @return new mysqli connection
   */
  function create_db_connection() {
    $conn = new mysqli("localhost", $_SERVER["LOANSSITE_MYSQL_USER"], $_SERVER["LOANSSITE_MYSQL_PASS"], "loans");  
    if($conn->connect_errno) {
      error_log("Failed to connect to mysql database! Err No: " . $conn->connect_errno . ", Message: " . $conn->connect_error);
      exit(1);
      return null;
    }
    return $conn;
  }

  function check_db_error($sql_conn, $prefix, $thing) {
    if(!$thing) {
      error_log($prefix . " - errno = " . $sql_conn->errno . ", errmes = " . $sql_conn->error);
      if(!headers_sent()) {
        http_response_code(500);
      }
      exit(1);
    }
  }
?>
