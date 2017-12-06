<?php
  class UserMapping {
    public static function fetch_by_id($sql_conn, $id) {
      $err_prefix = 'UserMapping::fetch_by_id';
      check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('SELECT * FROM users WHERE id=?'));
      check_db_error($sql_conn, $err_prefix, $stmt->bind_param('i', $id));
      check_db_error($sql_conn, $err_prefix, $stmt->execute());
      check_db_error($sql_conn, $err_prefix, $res = $stmt->get_result());

      $row = $res->fetch_assoc();
      $res->close();
      $stmt->close();
      if($row === null) { return null; }
      return new ArrayObject($row);
    }

    public static function fetch_by_username($sql_conn, $username) {
      $err_prefix = 'UserMapping::fetch_by_username';
      check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('SELECT * FROM users WHERE username=?'));
      check_db_error($sql_conn, $err_prefix, $stmt->bind_param('s', $username));
      check_db_error($sql_conn, $err_prefix, $stmt->execute());
      check_db_error($sql_conn, $err_prefix, $res = $stmt->get_result());

      $row = $res->fetch_assoc();
      $res->close();
      $stmt->close();
      if($row === null) { return null; }
      return new ArrayObject($row); 
    }
  }
?>
