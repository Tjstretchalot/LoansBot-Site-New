<?php
require_once 'api/common.php';
require_once 'api/query_helper.php';

// Contains one section of the update query callback
// note that we expect to be using a temporary table
// to affect what rows to update
class UpdateQueryCallback {
  // The identifier to distinguish this callback from others
  public $identifier;

  // Any information that was parsed to affect this callback
  public $params;

  // may be null
  // called immediately prior to previewing results
  // accepts 3 arguments - ($helper, &$params, $sql_conn)
  public $begin_preview_callback;

  // may be null
  // returns array('error_mess' => 'a string', 'error_ident' => 'a string') or null
  // accepts 4 arguments - ($helper, &$params, $sql_conn)
  public $preview_callback;

  // may be null
  // called immediately after previewing results
  // accepts 3 arguments - ($helper, &$params, $sql_conn)
  public $post_preview_callback;

  // may be null
  // returns something like 'name = ?' with no trailing or leading spaces or commas
  // this would go in the SET portion of the query (UPDATE loans SET name=?)
  // accepts 2 arguments ($helper, &$params)
  public $set_callback;
  
  // may be null
  // returns something like array(array('s', 'john')) 
  // an array of arrays, where the first element is the sql type and the second element
  // is the value to bind
  // This must correspond with the number of ? in the set_callback
  // accepts 2 arguments ($helper, &$params)
  public $bind_set_callback;

  // may be null
  // returns something like 'id = ?' 
  // accepts 2 arguments ($helper, &$params)
  public $where_callback;

  // may be null
  // returns something like array(array('i', 5))
  // accepts 2 arguments ($helper, &$params)
  public $bind_where_callback;

  // may be null
  // returns null or array('error_mess' => 'a string', 'error_ident' => 'a string')
  // accepts 3 arguments ($helper, &$params, $auth_level)
  public $authorization_callback;

  // may be null
  // returns null or array('error_mess' => 'a string', 'error_ident' => 'a string')
  // accepts 3 arguments ($helper, &$params, $sql_conn)
  public $sanity_callback;

  // may be null
  // accepts 4 arguments - ($helper, &$params, $row, &$res)
  // should modify res (the json-formattable array we are going to return)
  public $result_callback;

  // may be null
  // called after the previews have occurred and either an error was
  // raised or the query completed successfully
  // accepts 3 arguments - ($helper, &$params, $sql_conn)
  public $cleanup_callback;
  
  public function __construct($identifier, $params) {
    $this->identifier = $identifier;
    $this->params = $params;
  }
}

class UpdateQueryHelper {
  public $callbacks;
  public $callbacks_dict;

  // may be null
  // returns something like 'LIMIT ?'
  // accepts 1 argument ($helper)
  public $limit_callback;

  // may be null
  // returns something like array(array('i', 10))
  // accepts 1 argument ($helper)
  public $bind_limit_callback;

  // may be null
  // returns something like 'ORDER BY id DESC'
  public $order_by_callback;

  // a string for the table name, i.e. 'loans'
  public $table;

  // a string for the temporary result table name, i.e. 'cached-loans-tmp'
  public $temporary_table;

  // all active cleanup callbacks
  public $cleanup_callbacks;

  // also the cleanup callbacks but in a searchable format
  public $cleanup_callbacks_dict;

  public function __construct($table, $tmp_table) {
    $this->callbacks = array();
    $this->callbacks_dict = array();
    $this->cleanup_callbacks = array();
    $this->cleanup_callbacks_dict = array();
    $this->table = $table;
    $this->temporary_table = $tmp_table;
  }

  public function add_callback($callback) {
    if(isset($this->callbacks_dict[$callback->identifier])) {
      error_log('update_query_helper has a callback with the identifier ' . $callback->identifier . ' when another with the same identifier was added');
      error_log('complete list of callback identifiers so far:');
      for($this->callbacks as $callback) {
        error_log('  ' . $callback->identifier);
      }
      die();
    }
    $this->callbacks[] = $callback;
    $this->callbacks_dict[$callback->identifier] = $callback;
  }

  public function add_cleanup($callback) {
    if(isset($this->cleanup_callbacks_dict[$callback->identifier])) {
      return;
    }
    
    $cleanup_callbacks[] = $callback;
    $cleanup_callbacks_dict[$callback->identifier] = $callback;
  }

  public function run_cleanup($sql_conn) {
    foreach($this->cleanup_callbacks as $callback) {
      $tmp = $callback->cleanup_callback;

      if($tmp !== null) {
        $tmp($this, &$callback->params, $sql_conn);
      }
    }
    
    $this->cleanup_callbacks = array();
    $this->cleanup_callbacks_dict = array();
  }

  public function run_previews($sql_conn) {
    foreach($this->callbacks as $callback) {
      $tmp = $callback->begin_preview_callback;

      if($tmp !== null) {
        $this->add_cleanup($callback);
        $tmp($this, $callback->params, $sql_conn); 
      }
    }

    foreach($this->callbacks as $callback) {
      $tmp = $callback->preview_callback;
      if($tmp !== null) { 
        $this->add_cleanup($callback);
        $res = $tmp($this, $callback->params, $sql_conn);
        if($res !== null) {
          $this->run_cleanup($sql_conn);
          return $res;
        }
      }
    }
    
    foreach($this->callbacks as $callback) {
      $tmp = $callback->post_preview_callback;

      if($tmp !== null) {
        $this->add_cleanup($callback);
        $tmp($this, $callback->params, $sql_conn); 
      }
    }
  }

  public function check_authorization($sql_conn, $auth_level) {
    foreach($this->callbacks as $callback) {
      if($callback->authorization_callback !== null) {
        $tmp = $callback->authorization_callback;
        $result = $tmp($this, $callback->params, $auth_level);
        if($result !== null) {
          $this->run_cleanup($sql_conn);
          return $result;
        }
      }
    }
  }

  public function check_sanity($sql_conn) {
    foreach($this->callbacks as $callback) {
      if($callback->sanity_callback !== null) {
        $tmp = $callback->sanity_callback;
        $result = $tmp($this, $callback->params, $sql_conn);
        if($result !== null) {
          $this->run_cleanup($sql_conn);
          return $result;
        }
      }
    }
  }

  public function build_query() {
    $query = 'UPDATE ' . $this->table . ' SET ';
    
    $first = true;
    foreach($this->callbacks as $callback) {
      if($callback->set_callback != null) {
        if($first) {
          $first = false;
        }else {
          $query .= ', ';
        }
        $tmp = $callback->set_callback;
        $query .= $tmp($this, $callback->params);
      }
    }
    
    $first = true;
    foreach($this->callbacks as $callback) {
      if($callback->where_callback) {
        if($first) {
          $query .= ' WHERE ';
          $first = false;
        }else {
          $query .= ' AND ';
        }
        $tmp = $callback->where_callback;
        $query .= $tmp($this, $callback->params);
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

    return $query;
  }

  public function bind_params() {
    $all_params = array();
    foreach($this->callbacks as $callback) {
      if($callback->bind_set_callback !== null) {
        $tmp = $callback->bind_set_callback;
        $all_params = array_merge($all_params, $tmp($this, $callback->params));
      }
    }
    
    foreach($this->callback as $callback) {
      if($callback->bind_where_callback !== null) {
        $tmp = $callback->bind_where_callback;
        $all_params = array_merge($all_params, $tmp($this, $callback->params));
      }
    }
    
    if($this->bind_limit_callback !== null) {
      $tmp = $this->bind_limit_callback;
      $all_params = array_merge($all_params, $tmp($this));
    }
    
    QueryHelper::bind_params($sql_conn, $stmt, $all_params);
  }

  public function modify_result_from_row($row, &$res) {
    foreach($this->callbacks as $callback) {
      if($callback->result_callback !== null) {
        $tmp = $callback->result_callback;
        $tmp($this, $callback->params, $row, $res);
      }
    }
  }
}
?>
