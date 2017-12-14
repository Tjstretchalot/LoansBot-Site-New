<?php
  require_once 'api/common.php';
  require_once 'database/common.php';
  require_once 'api/query_helper.php';

  class DatabaseHelper {
    public static function fetch_all($sql_conn, $query, $params) {
      $err_prefix = 'DatabaseHelper::fetch_all - ' . $query;

      check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare($query));
      QueryHelper::bind_params($sql_conn, $stmt, $params);
      check_db_error($sql_conn, $err_prefix, $stmt->execute());
      check_db_error($sql_conn, $err_prefix, $res = $stmt->get_result());

      $res_arr = array();
      while(($row = $res->fetch_assoc()) !== null) {
        $res_arr[] = new ArrayObject($row, ArrayObject::ARRAY_AS_PROPS);
      }
      $res->close();
      $stmt->close();
      return $res_arr;
    }

    public static function fetch_one($sql_conn, $query, $params) {
      $err_prefix = 'DatabaseHelper::fetch_one - ' . $query;

      check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare($query));
      QueryHelper::bind_params($sql_conn, $stmt, $params);
      check_db_error($sql_conn, $err_prefix, $stmt->execute());
      check_db_error($sql_conn, $err_prefix, $res = $stmt->get_result());

      $row = $res->fetch_assoc();

      $res->close();
      $stmt->close();

      if($row !== null)
        return new ArrayObject($row, ArrayObject::ARRAY_AS_PROPS);

      return null;
    }

    public static function execute($sql_conn, $query, $params) {
      $err_prefix = 'DatabaseHelper::execute - ' . $query;
      check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare($query));
      QueryHelper::bind_params($sql_conn, $stmt, $params);
      check_db_error($sql_conn, $err_prefix, $stmt->execute());
      $stmt->close();
      return;
    }
  }
?>
