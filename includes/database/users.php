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
      return new ArrayObject($row, ArrayObject::ARRAY_AS_PROPS);
    }

    public static function fetch_by_username($sql_conn, $username) {
      $err_prefix = 'UserMapping::fetch_by_username';
      check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('SELECT * FROM usernames WHERE username=?'));
      check_db_error($sql_conn, $err_prefix, $stmt->bind_param('s', $username));
      check_db_error($sql_conn, $err_prefix, $stmt->execute());
      check_db_error($sql_conn, $err_prefix, $res = $stmt->get_result());

      $username_row = $res->fetch_assoc();
      $res->close();
      $stmt->close();
      if($username_row === null) { return null; }

      $usable_user_id = $username_row['user_id'];
      check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('SELECT * FROM users WHERE id=?'));
      check_db_error($sql_conn, $err_prefix, $stmt->bind_param('i', $usable_user_id));
      check_db_error($sql_conn, $err_prefix, $stmt->execute());
      check_db_error($sql_conn, $err_prefix, $res = $stmt->get_result());

      $row = $res->fetch_assoc();
      $res->close();
      $stmt->close();
      if($row === null) {
        error_log('no corresponding user for username=' . $username . ', user_id=' . $username_row['user_id']);
        return null; 
      }
      return new ArrayObject($row, ArrayObject::ARRAY_AS_PROPS); 
    }
  }
?>
