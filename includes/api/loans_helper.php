<?php
require_once 'api/common.php';

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
        $all_params = array_merge($all_params, $tmp($this));
      }
    }

    foreach ($this->callbacks as $callback) {
      if($callback->bind_where_callback !== null) {
        $tmp = $callback->bind_where_callback;
        $all_params = array_merge($all_params, $tmp($this));
      }
    }

    if(count($all_params) === 0) {
      error_log("no params to bind");
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

  public static function return_created_at($helper, &$params) {
    if($helper->format === 0) 
      return;

    $result = new LoanQueryCallback('created_at', array(), null, null, null, null, null, null, null);
    $result->param_callback = function($helper) {
      return 'loans.created_at as loan_created_at';
    };

    $result->result_callback = function($helper, &$row, &$response_res) {
      $val = strtotime($row['loan_created_at']) * 1000;
      if($helper->format === 1) {
        $response_res[] = $val;
      }else {
        $response_res['created_at'] = $val;
      }
    };

    $helper->add_callback($result);
    return null;
  }

  public static function parse_after_time($helper, &$params) {
    $after_time = null;
    if(isset($params['after_time']) && is_numeric($params['after_time'])) {
      $_after_time = intval($params['after_time']);

      if($_after_time !== 0) {
        if($_after_time < 0) {
          return array('error_ident' => 'INVALID_PARAMETER', 'error_mess' => 'After time cannot be before 1970');
        }
        $after_time = date('Y-m-d H:i:s', $_after_time / 1000);
      }
    }

    if($after_time === null)
      return null;

    $result = new LoanQueryCallback('after_time', array('after_time' => $after_time), null, null, null, null, null, null, null);
    $result->where_callback = function($helper) {
      return 'loans.created_at > ?';
    };
    $result->bind_where_callback = function($helper) use ($after_time) {
      return array(array('s', $after_time));
    };
    $helper->add_callback($result);
    return null;
  }

  public static function parse_before_time($helper, &$params) {
    $before_time = null;
    if(isset($params['before_time']) && is_numeric($params['before_time'])) {
      $_before_time = intval($params['before_time']);

      if($_before_time !== 0) {
        if($_before_time < 0) {
          return array('error_ident' => 'INVALID_PARAMETER', 'error_mess' => 'Before time cannot be before 1970');
        }
        $before_time = date('Y-m-d H:i:s', $_before_time / 1000);
      }
    }

    if($before_time === null)
      return null;

    $result = new LoanQueryCallback('before_time', array('before_time' => $before_time), null, null, null, null, null, null, null);
    $result->where_callback = function($helper) {
      return 'loans.created_at < ?';
    };
    $result->bind_where_callback = function($helper) use ($before_time) {
      return array(array('s', $before_time));
    };
    $helper->add_callback($result);
    return null;
  }

  public static function return_borrower_id($helper, &$params) {
    if($helper->format === 0) 
      return null;

    $result = new LoanQueryCallback('borrower_id', array(), null, null, null, null, null, null, null);
    $result->param_callback = function($helper) {
      return 'loans.borrower_id as loan_borrower_id';
    };

    $result->result_callback = function($helper, &$row, &$response_res) {
      $val = $row['loan_borrower_id'];
      if($helper->format === 1) {
        $response_res[] = $val;
      }else {
        $response_res['borrower_id'] = $val;
      }
    };

    $helper->add_callback($result);
    return null;
  }

  public static function parse_borrower_id($helper, &$params) {
    $filter_borrower_id = null;
    if(isset($params['borrower_id']) && is_numeric($params['borrower_id'])) {
      $_borrower_id = intval($params['borrower_id']);
      
      if($_borrower_id !== 0) {
        $filter_borrower_id = $_borrower_id;
      }
    }
    
    if($filter_borrower_id === null)
      return null;
    
    $result = new LoanQueryCallback('filter_borrower_id', array('filter_borrower_id' => $filter_borrower_id), null, null, null, null, null, null, null);
    $result->where_callback = function($helper) {
      return 'loans.borrower_id = ?';
    };
    $result->bind_where_callback = function($helper) use ($filter_borrower_id) {
      return array(array('i', $filter_borrower_id));
    };
    $helper->add_callback($result);
    return null;
  }

  public static function return_lender_id($helper, &$params) {
    if($helper->format === 0) {
      return null;
    }
    $result = new LoanQueryCallback('lender_id', array(), null, null, null, null, null, null, null);
    $result->param_callback = function($helper) {
      return 'loans.lender_id as loan_lender_id';
    };
    $result->result_callback = function($helper, &$row, &$response_res) {
      $val = $row['loan_lender_id'];
      if($helper->format === 1) {
        $response_res[] = $val;
      }else { 
        $response_res['lender_id'] = $val;
      }
    };
    $helper->add_callback($result);
    return null;
  }

  public static function parse_lender_id($helper, &$params) {
    $filter_lender_id = null;
    if(isset($params['lender_id']) && is_numeric($params['lender_id'])) {
      $_lender_id = intval($params['lender_id']);

      if($_lender_id !== 0) {
        $filter_lender_id = $_lender_id;
      }
    }
    
    if($filter_lender_id === null) {
      return null;
    }
    
    $result = new LoanQueryCallback('filter_lender_id', array(), null, null, null, null, null, null, null);
    $result->where_callback = function($helper) {
      return 'loans.lender_id = ?';
    };
    $result->bind_where_callback = function($helper) use ($filter_lender_id) {
      return array(array('i', $filter_lender_id));
    };
    $helper->add_callback($result);
    return null;
  }

  public static function parse_includes_user_id($helper, &$params) {
    $includes_user_id = null;
    if(isset($params['includes_user_id']) && is_numeric($params['includes_user_id'])) {
      $_includes_user_id = intval($params['includes_user_id']);
      
      if($_includes_user_id !== 0) {
        $includes_user_id = $_includes_user_id;
      }
    }
    
    if($includes_user_id === null) {
      return null;
    }
    
    $result = new LoanQueryCallback('includes_user_id', array(), null, null, null, null, null, null, null);
    $result->where_callback = function($helper) {
      return '(loans.lender_id = ? OR loans.borrower_id = ?)';
    };
    $result->bind_where_callback = function($helper) use ($includes_user_id) {
      return array(array('i', $includes_user_id), array('i', $includes_user_id));
    };
    $helper->add_callback($result);
    return null;
  }

  public static function parse_borrower_name($helper, &$params) {
    $borrower_name = null;
    if(isset($params['borrower_name'])) {
      $borrower_name = $params['borrower_name'];
    }
    
    if($borrower_name === null)
      return;
    
    if(isset($helper->callbacks_dict['filter_borrower_id'])) {
      return array('error_ident' => 'INVALID_PARAMETER', 'error_mess' => 'Cannot set both borrower_id and borrower_name');
    }
    
    $result = new LoanQueryCallback('borrower_name', array(), null, null, null, null, null, null, null);
    $result->where_callback = function($helper) {
      return '(borrower_id = (SELECT user_id FROM usernames WHERE username LIKE ? LIMIT 1))';
    };
    $result->bind_where_callback = function($helper) use ($borrower_name) {
      return array(array('s', $borrower_name));
    };
    $helper->add_callback($result);
    return null;
  }

  public static function parse_lender_name($helper, &$params) {
    $lender_name = null;
    if(isset($params['lender_name'])) {
      $lender_name = $params['lender_name'];
    }

    if($lender_name === null)
      return;

    if(isset($helper->callbacks_dict['filter_lender_id']))
      return array('error_ident' => 'INVALID_PARAMETER', 'error_mess' => 'Cannot set both lender_id and lender_name');

    $result = new LoanQueryCallback('lender_name', array(), null, null, null, null, null, null, null);
    $result->where_callback = function($helper) {
      return '(lender_id = (SELECT user_id FROM usernames WHERE username LIKE ? LIMIT 1))';
    };
    $result->bind_where_callback = function($helper) use ($lender_name) {
      return array(array('s', $lender_name));
    };
    $helper->add_callback($result);
    return null;
  }

  public static function parse_includes_user_name($helper, &$params) {
    $includes_user_name = null;
    if(isset($params['includes_user_name'])) {
      $includes_user_name = $params['includes_user_name'];
    }

    if($includes_user_name)
      return null;

    if(isset($helper->callbacks_dict['includes_user_id'])) {
      return array('error_ident' => 'INVALID_PARAMETER', 'error_mess' => 'You cannot set includes_user_id AND includes_user_name');
    }

    $result = new LoanQueryCallback('includes_user_name', array(), null, null, null, null, null, null, null);
    $result->where_callback = function($helper) {
      return '(loans.lender_id = (SELECT user_id FROM usernames WHERE username LIKE ?) OR loans.borrower_id = (SELECT user_id FROM usernames WHERE username LIKE ?))';
    };
    $result->bind_where_callback = function($helper) use ($includes_user_name) {
      return array(array('s', $includes_user_name), array('s', $includes_user_name));
    };
    $helper->add_callback($result);
  }

  public static function parse_include_deleted($helper, &$params) {
    $include_deleted = false;
    if(isset($params['include_deleted']) && is_numeric($params['include_deleted'])) {
      $_include_deleted = intval($params['include_deleted']);
      if($_include_deleted === 1) {
        $include_deleted = true;
      }
    }

    if(!$include_deleted) {
      $result = new LoanQueryCallback('exclude_deleted_at', array(), null, null, null, null, null, null, null);
      $result->where_callback = function($helper) {
        return 'loans.deleted = 0';
      };
      $helper->add_callback($result);
    }else {
      $result = new LoanQueryCallback('include_deleted_at', array(), null, null, null, null, null, null, null);
      $result->authorization_callback = function($helper, $auth) use($MODERATOR_PERMISSION) {
        if($auth < $MODERATOR_PERMISSION) {
          return array('error_ident' => 'NOT_AUTHORIZED', 'error_message' => 'You do not have permission to view deleted loans');
        }
        return null;
      };
      $helper->add_callback($result);
    }
  }

  public static function fetch_usernames($helper, &$params) {
    if($helper->format < 3) {
      return null;
    }

    $result = new LoanQueryCallback('fetch_lender_username', array(), null, null, null, null, null, null, null);
    $result->param_callback = function($helper) {
      return 'lunames.username as lender_username';
    };
    $result->result_callback = function($helper, &$row, &$response_res) {
      $response_res['lender_name'] = $row['lender_username'];
    };
    $result->join_callback = function($helper) {
      return 'INNER JOIN (SELECT user_id, GROUP_CONCAT(username SEPARATOR \' aka \') AS username FROM usernames GROUP BY user_id) lunames ON loans.lender_id = lunames.user_id';
    };
    $helper->add_callback($result);

    $result = new LoanQueryCallback('fetch_borrower_username', array(), null, null, null, null, null, null, null);
    $result->param_callback = function($helper) {
      return 'bunames.username as borrower_username';
    };
    $result->result_callback = function($helper, &$row, &$response_res) {
      $response_res['borrower_name'] = $row['borrower_username'];
    };
    $result->join_callback = function($helper) {
      return 'INNER JOIN (SELECT user_id, GROUP_CONCAT(username SEPARATOR \' aka \') AS username FROM usernames GROUP BY user_id) bunames ON loans.borrower_id = bunames.user_id';
    };
    $helper->add_callback($result);
  }

}
?>
