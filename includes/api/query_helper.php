<?php
// Contains generic helpful static functions
class QueryHelper {
  public static function bind_params($sql_conn, $stmt, $all_params) {
    if(count($all_params) === 0) {
      return;
    }
    $param_types_str = '';
    $param_values_arr = array();
    $param_values_arr = array_pad($param_values_arr, count($all_params) + 1, 0);
    $tmp_holding_arr = array();
    foreach ($all_params as $ind=>$param) {
      // i tried using param as the holding arr but it doesn't work
      $param_types_str .= $param[0];
      $tmp_holding_arr[] = $param[1];
      $param_values_arr[$ind+1] = &$tmp_holding_arr[$ind];
    }

    $param_values_arr[0] = $param_types_str;
    
    call_user_func_array(array($stmt, 'bind_param'), $param_values_arr);
  }
}
?>
