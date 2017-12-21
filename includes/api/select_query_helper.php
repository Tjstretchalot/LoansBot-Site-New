<?php
require_once 'api/common.php';
require_once 'api/query_helper.php';

/*
 * Contains one section of the query callback. These will typically either
 * limit the result (using the $where_callback and $bind_where_callback) or
 * or expand it (setting $param_callback, $result_callback, and potentially
 * $join_callback and $bind_join_callback). If appropriate, they will also
 * check for authorization and sanity
 */
class SelectQueryCallback {
  // An identifier for this callback
  public $identifier;

  // Array containing anything this callback wants to share with other callbacks
  public $parsed;

  // may be null
  // 1 argument ($helper), returns a string to append to CREATE TEMPORARY TABLE (here is appended) SELECT
  // should have no leading or trailing spaces or commas
  public $temporary_table_columns_callback;

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
  // called after all callbacks have been loaded
  // accepts ($helper, $sql_conn) and returns null if the command passes the sanity check, and an array('error_mess'=>'a string', 'error_ident'=> 'another string')
  // otherwise
  public $sanity_callback;

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

  // accepts ($helper, $row, &$array) where row is fetch_assoc() from the database, $array
  // is the output for the current row, and $format is the format that was requested (numeric)
  // may be null
  public $result_callback;
  
  public function __construct($identifier, $parsed) {
    $this->identifier = $identifier;
    $this->parsed = $parsed;
  }
}

// Combines SelectQueryCallbacks together
// to make a single long SELECT query
class SelectQueryHelper {
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

  // if set and not null, the query will insert into
  // this table rather than return as a result set
  public $use_temporary_table;

  // the table being used
  public $table;

  public function __construct($table) {
    $this->callbacks = new ArrayObject(array());
    $this->callbacks_dict = new ArrayObject(array());
    $this->table = $table;
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

  public function check_sanity($sql_conn) {
    foreach ($this->callbacks as $callback) {
      if($callback->sanity_callback !== null) {
        $tmp = $callback->santiy_callback;
        $res = $tmp($this, $sql_conn);
        if($res !== null) {
          return $res;
        }
      }
    }
  }

  public function build_query() {
    $query = '';
    if($this->use_temporary_table !== null) {
      $query .= 'CREATE TEMPORARY TABLE ' . $this->use_temporary_table . ' ';

      $first = true;
      foreach ($this->callbacks as $callback) {
        if($callback->temporary_table_columns_callback !== null) {
          $tmp = $callback->temporary_table_columns_callback;
          $res = $tmp($this);
          if($res !== null) {
            if($first) {
              $query .= '(';
              $first = false;
            }else {
              $query .= ', ';
            }

            $query .= $res;
          }
        }
      }
      if(!$first) {
        $query .= ') ';
      }
    }

    $query .= 'SELECT ';

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

    $query .= ' FROM ' . $this->table . ' ';

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
    
    QueryHelper::bind_params($sql_conn, $stmt, $all_params);
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
?>
