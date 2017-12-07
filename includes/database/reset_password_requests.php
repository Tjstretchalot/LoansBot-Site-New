<?php
  require_once 'database/common.php';

  class ResetPasswordRequestMapping {
    public static function fetch_by_id($sql_conn, $id) {
      $err_prefix = 'ResetPasswordRequestMapping::fetch_by_id';
      check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('SELECT * FROM reset_password_requests WHERE id=?'));
      check_db_error($sql_conn, $err_prefix, $stmt->bind_param('i', $id));
      check_db_error($sql_conn, $err_prefix, $stmt->execute());
      check_db_error($sql_conn, $err_prefix, $res = $stmt->get_result());

      $row = $res->fetch_assoc();
      $res->close();
      $stmt->close();
      if($row === null) { return null; }
      return new ArrayObject($row, ArrayObject::ARRAY_AS_PROPS);
    }
 
    public static function fetch_latest_by_user_id($sql_conn, $user_id) {
      $err_prefix = 'ResetPasswordRequestMapping::fetch_latest_by_user_id';
      check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('SELECT * FROM reset_password_requests WHERE user_id=? ORDER BY created_at DESC LIMIT 1'));
      check_db_error($sql_conn, $err_prefix, $stmt->bind_param('i', $user_id));
      check_db_error($sql_conn, $err_prefix, $stmt->execute());
      check_db_error($sql_conn, $err_prefix, $res = $stmt->get_result());

      $row = $res->fetch_assoc();
      $res->close();
      $stmt->close();
      if($row === null) { return null; }
      return new ArrayObject($row, ArrayObject::ARRAY_AS_PROPS);
    }

    public static function insert($sql_conn, $user_id, $reset_code) {
      $err_prefix = 'ResetPasswordRequestMapping::insert';
      check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('INSERT INTO reset_password_requests (user_id, reset_code, reset_code_sent, reset_code_used, created_at, updated_at) VALUES (?, ?, NULL, NULL, NOW(), NOW())'));
      check_db_error($sql_conn, $err_prefix, $stmt->bind_param('is', $user_id, $reset_code));
      check_db_error($sql_conn, $err_prefix, $stmt->execute());

      $result = $sql_conn->insert_id;

      $stmt->close();

      return $result;
    }

    public static function update_used($sql_conn, $id) {
      $err_prefix = 'ResetPasswordRequestMapping::update_used';

      check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('UPDATE reset_password_requests SET reset_code_used=NOW(), updated_at=NOW() WHERE id=?'));
      check_db_error($sql_conn, $err_prefix, $stmt->bind_param('i', $id));
      check_db_error($sql_conn, $err_prefix, $stmt->execute());

      $stmt->close();
    }
  }
?>
