<?php
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
      return new ArrayObject($row);
    }

    public static function delete_by_id($sql_conn, $id) {
      $err_prefix = 'SiteSessionMapping::delete_by_id';
      check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('DELETE FROM site_sessions WHERE id=?'));
      check_db_error($sql_conn, $err_prefix, $stmt->bind_param('i', $id));
      check_db_error($sql_conn, $err_prefix, $stmt->execute());

      $stmt->close();
    }
  }
?>
