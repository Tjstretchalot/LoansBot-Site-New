<?php
  require_once 'database/common.php';

  class SiteSessionMapping {
    public static function fetch_by_session_id($sql_conn, $session_id) {
      $err_prefix = 'SiteSessionMapping::fetch_by_session_id';
      check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('SELECT * FROM site_sessions WHERE session_id=?'));
      check_db_error($sql_conn, $err_prefix, $stmt->bind_param('s', $session_id));
      check_db_error($sql_conn, $err_prefix, $stmt->execute());
      check_db_error($sql_conn, $err_prefix, $res = $stmt->get_result());

      $row = $res->fetch_assoc();
      $res->close();
      $stmt->close();
      if($row === null) { return null; }
      return new ArrayObject($row, ArrayObject::ARRAY_AS_PROPS);
    }

    public static function delete_by_id($sql_conn, $id) {
      $err_prefix = 'SiteSessionMapping::delete_by_id';
      check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('DELETE FROM site_sessions WHERE id=?'));
      check_db_error($sql_conn, $err_prefix, $stmt->bind_param('i', $id));
      check_db_error($sql_conn, $err_prefix, $stmt->execute());

      $stmt->close();
    }

    public static function create_and_save($sql_conn, $session_id, $user_id, $created_at, $expires_at) {
      $usable_created_at = $created_at;
      $usable_expires_at = $expires_at;

      if($usable_created_at !== null) {
        $usable_created_at = date('Y-m-d H:i:s', $usable_created_at);
      }

      if($usable_expires_at !== null) {
        $usable_expires_at = date('Y-m-d H:i:s', $usable_expires_at);
      }

      $err_prefix = 'SiteSessionMapping::create_and_save';
      check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('INSERT INTO site_sessions (session_id, user_id, created_at, expires_at) VALUES (?, ?, ?, ?)'));
      check_db_error($sql_conn, $err_prefix, $stmt->bind_param('siss', $session_id, $user_id, $usable_created_at, $usable_expires_at));
      check_db_error($sql_conn, $err_prefix, $stmt->execute());

      $stmt->close();
    }
  }
?>
