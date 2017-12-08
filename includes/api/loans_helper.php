<?php
/*
 * This class makes it easier to generate the very general
 * queries that can be spit out of html/api/loans.php. 
 * 
 * It generates queries of the form
 *
 * SELECT loans.asdf, john_sub.asdf[, ...] FROM loans INNER JOIN (SELECT john.hi FROM john ORDER BY date ASC) john_sub ON john_sub.hi = loans.asdf WHERE loan.asdf=? ORDER BY loans.asdf DESC LIMIT 10';
 */
class LoanQueryCallback {
  // may not be null
  // the unique string that identifies this callback, in case other callbacks need to reference
  // it
  public $identifier;

  // may not be null
  // an array that contains any data that was parsed from parameters that help describe what this
  // callback does
  public $parsed;

  // may be null
  // 1 argument ($helper), returns a string to append with no leading or trailing spaces or commas
  public $param_callback;

  // may be null
  // 1 argument ($helper), returns a string to append with no leading or trailing spaces or commas
  public $join_callback;

  // may be null
  // 1 argument ($helper), returns a string to append with no leading or trailing spaces or commas
  public $where_callback;
  
  // may be null
  // accepts ($helper, $auth_level) and returns null if the command is valid, and an array('error_mess'=>'a string', 'error_ident' => 'another string') 
  // if it is not valid
  public $authorization_callback;

  // may be null
  // 1 argument ($helper)
  // if you bind 1 param in your join_callback like 'INNER JOIN (SELECT * FROM asdf WHERE asdf.id=?) a ON asdf.loan_id=loans.loan_id'
  // the result would be array(array( 'i', 5 ))
  public $bind_join_callback;

  // may be null
  // 1 argument ($helper)
  // if you had something like 'WHERE loans.id=? OR loans.id=?' then this would return array((array('i', 5), array('i', 6)))
  // for example
  public $bind_where_callback;

  // accepts ($helper, &$row, &$array) where row is fetch_assoc() from the database, $array
  // is the output for the current row, and $format is the format that was requested (numeric)
  // may be null
  public $result_callback;

  public function __construct($identifier, $parsed, $param_callback, $join_callback, $where_callback, $authorization_callback, $bind_join_callback, $bind_where_callback, $result_callback) {
    $this->identifier = $identifier;
    $this->parsed = $parsed;
    $this->param_callback = $param_callback;
    $this->join_callback = $join_callback;
    $this->where_callback = $where_callback;
    $this->authorization_callback = $authorization_callback;
    $this->bind_join_callback = $bind_join_callback;
    $this->bind_where_callback = $bind_where_callback;
    $this->result_callback = $result_callback;
  }
}

class LoansHelper {
  // acts like an array of callbacks
  public $callbacks;

  // acts like a dictionary of callback identifiers to callbacks
  public $callbacks_dict;

  // should return something like 'ORDER BY loans.x DESC'
  public $order_by_callback;

  // should return something like 'LIMIT 10'
  public $limit_callback;

  // the format being used
  public $format;

  public function __construct() {
    $this->callbacks = new ArrayObject(array());
    $this->callbacks_dict = new ArrayObject(array());
  }

  public function add_callback($callback) {
    $this->callbacks[] = $callback;
    $this->callbacks_dict[$callback->identifier] = $callback;
  }

  public function check_authorization($auth_level) {
    foreach ($this->callbacks as $callback) {
      if($callback->authorization_callback !== null) {
        $tmp = $callback->authorization_callback;
        $res = $tmp($this, $auth_level);
        if($res !== null) {
          return $res;
        }
      }
    }
  }

  public function build_query() {
    $query = 'SELECT ';

    $first = true;
    foreach ($this->callbacks as $callback) {
      if($callback->param_callback !== null) {
        if($first) {
          $first = false;
        }else {
          $query .= ', ';
        }
        $tmp = $callback->param_callback;
        $query .= $tmp($this);
      }
    }

    $query .= ' FROM loans ';

    $first = true;
    foreach ($this->callbacks as $callback) {
      if($callback->join_callback !== null) {
        if($first) {
          $first = false;
        }else { 
          $query .= ' ';
        }
        $tmp = $callback->join_callback;
        $query .= $tmp($this);
      }
    }
    
    $first = true;
    foreach ($this->callbacks as $callback) {
      if($callback->where_callback !== null) {
        if($first) {
          $query .= ' WHERE ';
          $first = false;
        }else {
          $query .= ' AND ';
        }
        $tmp = $callback->where_callback;
        $query .= $tmp($this);
      }
    }

    if($this->order_by_callback !== null) {
      $query .= ' ';
      $tmp = $this->order_by_callback;
      $query .= $tmp($this);
    }

    if($this->limit_callback !== null) {
      $query .= ' ';
      $tmp = $this->limit_callback;
      $query .= $tmp($this);
    }
    
    error_log($query);
    return $query;
  }
  
  public function bind_params($sql_conn, $stmt) {
    $all_params = array();
    foreach ($this->callbacks as $callback) {
      if($callback->bind_join_callback !== null) {
        $tmp = $callback->bind_join_callback;
        $all_params = $all_params + $tmp($this);
      }
    }

    foreach ($this->callbacks as $callback) {
      if($callback->bind_where_callback !== null) {
        $tmp = $callback->bind_where_callback;
        $all_params = $all_params + $tmp($this);
      }
    }

    if(count($all_params) === 0) {
      error_log("no params to bind");
      return;
    }

    $param_types_str = '';
    $param_values_arr = array();
    $param_values_arr[] = $param_types_str; // we will reset this later but we need to reserve the slot
    foreach ($all_params as $param) {
      $param_types_str .= $param[0];
      $param_values_arr[] = &$param[1];
    }

    $param_values_arr[0] = $param_types_str;

    error_log( print_r( $param_values_arr, true ) );
    call_user_func_array(array($stmt, 'bind_param'), $param_values_arr);
  }

  public function create_result_from_row($row) {
    $result = array();
    
    foreach($this->callbacks as $callback) {
      if($callback->result_callback !== null) {
        $tmp = $callback->result_callback;
        $tmp($this, $row, $result);
      }
    }

    return $result;
  }
}

class ParameterParser {
  //public __construct($identifier, $parsed, $param_callback, $join_callback, $where_callback, $authorization_callback, $result_callback) {
  public static function parse_format($helper, &$params) {
    $format = 1;
    if(isset($params['format']) && is_numeric($params['format'])) {
      $_format = intval($params['format']);

      if(in_array($_format, array(0, 1, 2, 3))) {
        $format = $_format;
      }else {
        return array('error_ident' => 'INVALID_PARAMETER', 'error_mess' => 'Format must be 0, 1, 2, or 3');
      }
    }

    $helper->format = $format;
    return null;
  }

  public static function parse_limit($helper, &$params) {
    $limit = 10;
    if(isset($params['limit']) && is_numeric($params['limit'])) {
      $_limit = intval($params['limit']);
      if($_limit >= 0 && $_limit <= 1000) {
        $limit = $_limit;
      }else {
        return array('error_ident' => 'INVALID_PARAMETER', 'error_mess' => 'Limit must be 0 or a positive integer less than 1000');
      }
    }

    if($limit !== 0) {
      $helper->limit_callback = function() use ($limit) {
        return 'LIMIT ' . strval($limit);
      };
    }

    return null;
  }

  public static function parse_id($helper, &$params) {
    $id = null;
    if(isset($params['id']) && is_numeric($params['id'])) {
      $_id = intval($params['id']);

      if($_id <= 0) {
        return array('error_ident' => 'INVALID_PARAMETER', 'error_mess' => 'ID must be strictly positive');
      }

      $id = $_id;
    }

    $result = new LoanQueryCallback('loan_id', array('id' => $id), null, null, null, null, null, null, null);

    $result->param_callback = function($helper) {
      return 'loans.id as loan_id';
    };

    $result->result_callback = function($helper, &$row, &$response_res) {
      if($helper->format === 0 || $helper->format === 1) {
        $response_res[] = $row['loan_id'];
      }else {
        $response_res['loan_id'] = $row['loan_id'];
      }
    };

    if($id !== null) {
      $result->where_callback = function($helper) {
        return 'loans.id = ?';
      };
      $result->bind_where_callback = function($helper) use ($id) {
        return array(array('i', $id));
      };
    }

    $helper->add_callback($result);
    return null;
  }
}
?>
