<?php
require_once 'api/common.php';
require_once 'api/select_query_helper.php';
require_once 'api/update_query_helper.php';

class ParameterParser {
  public static function parse_format($helper, $params) {
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

  public static function parse_limit($helper, $params) {
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

  public static function parse_id($helper, $params) {
    $id = null;
    if(isset($params['id']) && is_numeric($params['id'])) {
      $_id = intval($params['id']);

      if($_id <= 0) {
        return array('error_ident' => 'INVALID_PARAMETER', 'error_mess' => 'ID must be strictly positive');
      }

      $id = $_id;
    }

    $result = new SelectQueryCallback('loan_id', array('id' => $id));

    $result->temporary_table_columns_callback = function($helper) {
      return 'PRIMARY KEY (loan_id)';
    };

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

  public static function return_created_at($helper, $params) {
    if($helper->format === 0) 
      return;

    $result = new SelectQueryCallback('created_at', array());
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

  public static function parse_after_time($helper, $params) {
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

    $result = new SelectQueryCallback('after_time', array('after_time' => $after_time));
    $result->where_callback = function($helper) {
      return 'loans.created_at > ?';
    };
    $result->bind_where_callback = function($helper) use ($after_time) {
      return array(array('s', $after_time));
    };
    $helper->add_callback($result);
    return null;
  }

  public static function parse_before_time($helper, $params) {
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

    $result = new SelectQueryCallback('before_time', array('before_time' => $before_time));
    $result->where_callback = function($helper) {
      return 'loans.created_at < ?';
    };
    $result->bind_where_callback = function($helper) use ($before_time) {
      return array(array('s', $before_time));
    };
    $helper->add_callback($result);
    return null;
  }

  public static function return_borrower_id($helper, $params) {
    if($helper->format === 0) 
      return null;

    $result = new SelectQueryCallback('borrower_id', array());
    
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

  public static function parse_borrower_id($helper, $params) {
    $filter_borrower_id = null;
    if(isset($params['borrower_id']) && is_numeric($params['borrower_id'])) {
      $_borrower_id = intval($params['borrower_id']);
      
      if($_borrower_id !== 0) {
        $filter_borrower_id = $_borrower_id;
      }
    }
    
    if($filter_borrower_id === null)
      return null;
    
    $result = new SelectQueryCallback('filter_borrower_id', array('filter_borrower_id' => $filter_borrower_id));
    $result->where_callback = function($helper) {
      return 'loans.borrower_id = ?';
    };
    $result->bind_where_callback = function($helper) use ($filter_borrower_id) {
      return array(array('i', $filter_borrower_id));
    };
    $helper->add_callback($result);
    return null;
  }

  public static function return_lender_id($helper, $params) {
    if($helper->format === 0) {
      return null;
    }
    $result = new SelectQueryCallback('lender_id', array());
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

  public static function parse_lender_id($helper, $params) {
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
    
    $result = new SelectQueryCallback('filter_lender_id', array('filter_lender_id' => $filter_lender_id));
    $result->where_callback = function($helper) {
      return 'loans.lender_id = ?';
    };
    $result->bind_where_callback = function($helper) use ($filter_lender_id) {
      return array(array('i', $filter_lender_id));
    };
    $helper->add_callback($result);
    return null;
  }

  public static function parse_includes_user_id($helper, $params) {
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
    
    $result = new SelectQueryCallback('includes_user_id', array('includes_user_id' => $includes_user_id));
    $result->where_callback = function($helper) {
      return '(loans.lender_id = ? OR loans.borrower_id = ?)';
    };
    $result->bind_where_callback = function($helper) use ($includes_user_id) {
      return array(array('i', $includes_user_id), array('i', $includes_user_id));
    };
    $helper->add_callback($result);
    return null;
  }

  public static function parse_borrower_name($helper, $params) {
    $borrower_name = null;
    if(isset($params['borrower_name'])) {
      $borrower_name = $params['borrower_name'];
    }
    
    if($borrower_name === null)
      return;
    
    if(isset($helper->callbacks_dict['filter_borrower_id'])) {
      return array('error_ident' => 'INVALID_PARAMETER', 'error_mess' => 'Cannot set both borrower_id and borrower_name');
    }
    
    $result = new SelectQueryCallback('borrower_name', array('borrower_name' => $borrower_name));
    $result->where_callback = function($helper) {
      return '(borrower_id = (SELECT user_id FROM usernames WHERE username LIKE ? LIMIT 1))';
    };
    $result->bind_where_callback = function($helper) use ($borrower_name) {
      return array(array('s', $borrower_name));
    };
    $helper->add_callback($result);
    return null;
  }

  public static function parse_lender_name($helper, $params) {
    $lender_name = null;
    if(isset($params['lender_name'])) {
      $lender_name = $params['lender_name'];
    }

    if($lender_name === null)
      return;

    if(isset($helper->callbacks_dict['filter_lender_id']))
      return array('error_ident' => 'INVALID_PARAMETER', 'error_mess' => 'Cannot set both lender_id and lender_name');

    $result = new SelectQueryCallback('lender_name', array('lender_name' => $lender_name));
    $result->where_callback = function($helper) {
      return '(lender_id = (SELECT user_id FROM usernames WHERE username LIKE ? LIMIT 1))';
    };
    $result->bind_where_callback = function($helper) use ($lender_name) {
      return array(array('s', $lender_name));
    };
    $helper->add_callback($result);
    return null;
  }

  public static function parse_includes_user_name($helper, $params) {
    $includes_user_name = null;
    if(isset($params['includes_user_name'])) {
      $includes_user_name = $params['includes_user_name'];
    }

    if($includes_user_name === null)
      return null;

    if(isset($helper->callbacks_dict['includes_user_id'])) {
      return array('error_ident' => 'INVALID_PARAMETER', 'error_mess' => 'You cannot set includes_user_id AND includes_user_name');
    }

    $result = new SelectQueryCallback('includes_user_name', array('includes_user_name' => $includes_user_name));
    $result->where_callback = function($helper) {
      return '(loans.lender_id = (SELECT user_id FROM usernames WHERE username LIKE ?) OR loans.borrower_id = (SELECT user_id FROM usernames WHERE username LIKE ?))';
    };
    $result->bind_where_callback = function($helper) use ($includes_user_name) {
      return array(array('s', $includes_user_name), array('s', $includes_user_name));
    };
    $helper->add_callback($result);
  }

  public static function parse_include_deleted($helper, $params) {
    $include_deleted = false;
    if(isset($params['include_deleted']) && is_numeric($params['include_deleted'])) {
      $_include_deleted = intval($params['include_deleted']);
      if($_include_deleted === 1) {
        $include_deleted = true;
      }
    }

    if(!$include_deleted) {
      $result = new SelectQueryCallback('exclude_deleted_at', array());
      $result->where_callback = function($helper) {
        return 'loans.deleted = 0';
      };
      $helper->add_callback($result);
    }else {
      $result = new SelectQueryCallback('include_deleted_at', array());
      $result->authorization_callback = function($helper, $auth) {
        if($auth < 5) { // MODERATOR_PERMISSION
          return array('error_ident' => 'NOT_AUTHORIZED', 'error_mess' => 'You do not have permission to view deleted loans');
        }
        return null;
      };
      $helper->add_callback($result);
    }
  }

  public static function return_deleted_information($helper, $params) {
    $result = new SelectQueryCallback('return_deleted_information', array());

    $result->param_callback = function($helper) {
      return 'loans.deleted AS loan_deleted, loans.deleted_at as loan_deleted_at, loans.deleted_reason as loan_deleted_reason';
    };

    $tmp = &$result->params;
    $result->authorization_callback = function($helper, $auth) use (&$tmp) {
      $tmp['can_view_deleted'] = $auth >= 5; // MODERATOR_PERMISSION
    }; 
    $result->result_callback = function($helper, &$row, &$response_res) use (&$tmp) {
      if($tmp['can_view_deleted']) {
        $response_res['deleted'] = $row['loan_deleted'];
        if($row['loan_deleted_at'] !== null) {
          $response_res['deleted_at'] = strtotime($row['loan_deleted_at']) * 1000;
        }else {
          $response_res['deleted_at'] = null;
        }
        $response_res['deleted_reason'] = $row['loan_deleted_reason'];
      }
    };
    $helper->add_callback($result);
    return null;
  }

  public static function return_principal_cents($helper, $params) {
    if($helper->format === 0) {
      return null;
    }
    $result = new SelectQueryCallback('principal_cents', array());
    $result->param_callback = function($helper) {
      return 'loans.principal_cents as loan_principal_cents';
    };
    $result->result_callback = function($helper, &$row, &$response_res) {
      $val = $row['loan_principal_cents'];
      if($helper->format === 1) {
        $response_res[] = $val;
      }else { 
        $response_res['principal_cents'] = $val;
      }
    };
    $helper->add_callback($result);
    return null;
  }

  public static function parse_principal_cents($helper, $params) {
    $filter_principal_cents = null;
    if(isset($params['principal_cents']) && is_numeric($params['principal_cents'])) {
      $_principal_cents = intval($params['principal_cents']);

      if($_principal_cents !== -1) {
        $filter_principal_cents = $_principal_cents;
      }
    }
    
    if($filter_principal_cents === null) {
      return null;
    }
    
    $result = new SelectQueryCallback('filter_principal_cents', array());
    $result->where_callback = function($helper) {
      return 'loans.principal_cents = ?';
    };
    $result->bind_where_callback = function($helper) use ($filter_principal_cents) {
      return array(array('i', $filter_principal_cents));
    };
    $helper->add_callback($result);
    return null;
  }
   
  public static function return_principal_repayment_cents($helper, $params) {
    if($helper->format === 0) {
      return null;
    }
    $result = new SelectQueryCallback('principal_repayment_cents', array());
    $result->param_callback = function($helper) {
      return 'loans.principal_repayment_cents as loan_principal_repayment_cents';
    };
    $result->result_callback = function($helper, &$row, &$response_res) {
      $val = $row['loan_principal_repayment_cents'];
      if($helper->format === 1) {
        $response_res[] = $val;
      }else { 
        $response_res['principal_repayment_cents'] = $val;
      }
    };
    $helper->add_callback($result);
    return null;
  }

  public static function parse_principal_repayment_cents($helper, $params) {
    $filter_principal_repayment_cents = null;
    if(isset($params['principal_repayment_cents']) && is_numeric($params['principal_repayment_cents'])) {
      $_principal_repayment_cents = intval($params['principal_repayment_cents']);

      if($_principal_repayment_cents !== -1) {
        $filter_principal_repayment_cents = $_principal_repayment_cents;
      }
    }
    
    if($filter_principal_repayment_cents === null) {
      return null;
    }
    
    $result = new SelectQueryCallback('filter_principal_repayment_cents', array());
    $result->where_callback = function($helper) {
      return 'loans.principal_repayment_cents = ?';
    };
    $result->bind_where_callback = function($helper) use ($filter_principal_repayment_cents) {
      return array(array('i', $filter_principal_repayment_cents));
    };
    $helper->add_callback($result);
    return null;
  }

  public static function return_unpaid($helper, $params) {
    if($helper->format === 0) {
      return null;
    }
    $result = new SelectQueryCallback('unpaid', array());
    $result->param_callback = function($helper) {
      return 'loans.unpaid as loan_unpaid';
    };
    $result->result_callback = function($helper, &$row, &$response_res) {
      $val = $row['loan_unpaid'];
      if($helper->format === 1) {
        $response_res[] = $val;
      }else { 
        $response_res['unpaid'] = $val;
      }
    };
    $helper->add_callback($result);
    return null;
  }

  public static function parse_unpaid($helper, $params) {
    $unpaid = null;
    if(isset($params['unpaid']) && is_numeric($params['unpaid'])) {
      $_unpaid = intval($params['unpaid']);

      if($_unpaid === 1) {
        $unpaid = 1;
      }elseif($_unpaid === 0) {
        $unpaid = 0;
      }
    }

    if($unpaid === null)
      return;

    if($unpaid === 1) {
      $result = new SelectQueryCallback('only_unpaid', array());
      $result->where_callback = function($helper) {
        return 'loans.unpaid = 1';
      };
      $helper->add_callback($result);
      return null;
    }else {
      $result = new SelectQueryCallback('no_unpaid', array());
      $result->where_callback = function($helper) {
        return 'loans.unpaid = 0';
      };
      $helper->add_callback($result);
      return null;
    }
  }
  
  public static function parse_repaid($helper, $params) {
    $repaid = null;
    if(isset($params['repaid']) && is_numeric($params['repaid'])) {
      $_repaid = intval($params['repaid']);

      if($_repaid === 1) {
        $repaid = 1;
      }elseif($_repaid === 0) {
        $repaid = 0;
      }
    }

    if($repaid === null)
      return;

    if($repaid === 1) {
      $result = new SelectQueryCallback('only_repaid', array());
      $result->where_callback = function($helper) {
        return 'loans.principal_cents = loans.principal_repayment_cents';
      };
      $helper->add_callback($result);
      return null;
    }else {
      $result = new SelectQueryCallback('no_repaid', array());
      $result->where_callback = function($helper) {
        return 'loans.principal_cents != loans.principal_repayment_cents';
      };
      $helper->add_callback($result);
      return null;
    }
  }

  public static function return_updated_at($helper, $params) {
    if($helper->format === 0) {
      return null;
    }
    $result = new SelectQueryCallback('updated_at', array());
    $result->param_callback = function($helper) {
      return 'loans.updated_at as loan_updated_at';
    };
    $result->result_callback = function($helper, &$row, &$response_res) {
      $val = strtotime($row['loan_updated_at']) * 1000;
      if($helper->format === 1) {
        $response_res[] = $val;
      }else { 
        $response_res['updated_at'] = $val;
      }
    };
    $helper->add_callback($result);
    return null;
  }

  public static function parse_include_latest_repayment_at($helper, $params) {
    if($helper->format === 0)
      return null;

    $ret_lat_rep_at = 0;
    if(isset($params['include_latest_repayment_at']) && is_numeric($params['include_latest_repayment_at'])) {
      $_inclatrepat = intval($params['include_latest_repayment_at']);

      if(in_array($_inclatrepat, array(0, 1))) {
        $ret_lat_rep_at = $inclatrepat;
      }
    }

    if($ret_lat_rep_at === 0)
      return null;
    
    $result = new SelectQueryCallback('include_latest_repayment_at', array());
    $result->param_callback = function($helper) {
      return 'lrepays.created_at as latest_repayment_at';
    };
    $result->result_callback = function($helper, &$row, &$response_res) {
      $val = null;
      if(isset($row['latest_repayment_at']) && $row['latest_repayment_at'] !== null) {
        $val = strtotime($row['latest_repayment_at']) * 1000;
      }

      if($helper->format === 1) {
        $response_res[] = $val;
      }else {
        $response_res['latest_repayment_at'] = $val;
      }
    };
    $result->join_callback = function($helper) {
      return 'LEFT OUTER JOIN (SELECT loan_id, MAX(created_at) as created_at FROM repayments GROUP BY loan_id) lrepays ON loans.id = lrepays.loan_id';
    };
    $helper->add_callback($result);
    return null;
  }

  public static function fetch_usernames($helper, $params) {
    if($helper->format < 3) {
      return null;
    }

    $result = new SelectQueryCallback('fetch_lender_username', array());
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

    $result = new SelectQueryCallback('fetch_borrower_username', array());
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

class ModifyParameterParser {
  public static function add_number_results_sanity_limits($sel_helper, $upd_helper, $params) {
    $result = new UpdateQueryCallback('number_results_sanity', array('min' => 1, 'max' => 10));
    
    $result->sanity_callback = function($helper, &$params, $sql_conn) {
      $err_prefix = 'add_number_results_sanity_limits#sanity_callback';
      check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('SELECT COUNT(*) FROM ' . $helper->temporary_table));
      check_db_error($sql_conn, $err_prefix, $stmt->execute());
      check_db_error($sql_conn, $err_prefix, $res = $stmt->get_result());

      $row = $res->fetch_row();
      $count = $row[0];
      $res->close();
      $stmt->close();

      if($count < $params['min'] || $count > $params['max']) {
        return array('error_ident' => 'COUNT_SANITY_FAILED', 'error_mess' => "You are attempting to modify $count rows at once, but the sanity limits are between " . strval($params['min']) . ' and ' . strval($params['max']));
      }

      return null;
    };
    
    $upd_helper->add_callback($result);
    return null;
  }

  public static function limit_to_returned_rows($sel_helper, $upd_helper, $params) {
    $result = new UpdateQueryCallback('limit_to_returned_rows', array());
    
    $result->where_callback = function($helper, &$params) {
      return 'id IN (SELECT loan_id FROM ' . $helper->temporary_table . ')';
    };
    
    $upd_helper->add_callback($result);
    return null;
  }

  public static function update_updated_at($sel_helper, $upd_helper, $params) {
    $result = new UpdateQueryCallback('update_updated_at', array('new_updated_at' => time()));

    $result->set_callback = function($helper, &$params) {
      return 'updated_at = ?';
    };

    $result->bind_set_callback = function($helper, &$params) {
      return array(array('s', date('Y-m-d H:i:s', $params['new_updated_at'])));
    };

    $result->result_callback = function($helper, &$params, $row, &$res) {
      $res['new_updated_at'] = $params['new_updated_at'] * 1000;
    };

    $upd_helper->add_callback($result);
    return null;
  }

  public static function return_boilerplate($sel_helper, $upd_helper, $params) {
    $result = new UpdateQueryCallback('return_boilerplate', array());

    $result->result_callback = function($helper, &$params, $row, &$res) {
      if(!isset($res['new_lender_id']))
        $res['new_lender_id'] = $row['loan_lender_id'];
      if(!isset($res['new_borrower_id']))
        $res['new_borrower_id'] = $row['loan_borrower_id'];
      if(!isset($res['new_principal_cents']))
        $res['new_principal_cents'] = $row['loan_principal_cents'];
      if(!isset($res['new_principal_repayment_cents']))
        $res['new_principal_repayment_cents'] = $row['loan_principal_repayment_cents'];
      if(!isset($res['new_unpaid']))
        $res['new_unpaid'] = $row['loan_unpaid'];
      if(!isset($res['new_deleted']) && isset($row['loan_deleted'])) 
          $res['new_deleted'] = $row['loan_deleted'];
      if(!isset($res['new_deleted_reason']) && isset($row['loan_deleted_reason'])) 
        $res['new_deleted_reason'] = $row['loan_deleted_reason'];
      if(!isset($res['new_updated_at']))
        $res['new_updated_at'] = $row['loan_updated_at'];
      if(!isset($res['new_deleted_at']) && isset($row['loan_deleted_at'])) 
        $res['new_deleted_at'] = $row['loan_deleted_at'];
    };

    $upd_helper->add_callback($result);
    return null;
  }

  public static function parse_set_borrower_id($sel_helper, $upd_helper, $params) {
    $set_borrower_id = null;
    if(isset($params['set_borrower_id']) && is_numeric($params['set_borrower_id'])) {
      $set_borrower_id = intval($params['set_borrower_id']);
    }
    
    if($set_borrower_id === null) {
      return null;
    }
    
    $result = new UpdateQueryCallback('set_borrower_id', array('set_borrower_id' => $set_borrower_id));
    
    $result->sanity_callback = function($helper, &$params, $sql_conn) {
      // First verify that there does exist a user with that id
      $err_prefix = 'parse_set_borrower_id#sanity_callback';
      check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('SELECT id FROM users WHERE id=?'));
      check_db_error($sql_conn, $err_prefix, $stmt->bind_param('i', $params['set_borrower_id']));
      check_db_error($sql_conn, $err_prefix, $stmt->execute());
      check_db_error($sql_conn, $err_prefix, $res = $stmt->get_result());
      
      $row = $res->fetch_row();
      $row_exists = $row !== null;
      unset($row);
      $res->close();
      $stmt->close();
      
      if(!$row_exists) {
        return array('error_ident' => 'SET_BORROWER_ID_SANITY_FAILED', 'error_mess' => 'You attempted to set the borrower id to ' . $params['set_borrower_id'] . ' - but no user has that id!');
      }
      
      // Then verify that EITHER we are setting the lender id to something else, or flag
      // resolved_lending_to_self_conflicts to false
      
      $params['resolved_lending_to_self_conflicts'] = true;
      if(isset($helper->callbacks_dict['set_lender_id'])) {
        $set_lender_id = $helper->callbacks_dict['set_lender_id'];
        if($set_lender_id === $params['set_borrower_id']) 
          return array('error_ident' => 'SET_BORROWER_ID_SANITY_FAILED', 'error_mess' => 'You attempted to set the borrower id and lender id to the same thing!');
      }elseif($helper->callbacks_dict['set_lender_name']) {
        $set_lender_name = $helper->callbacks_dict['set_lender_name'];
        if(isset($set_lender_name->params['set_lender_id'])) {
          if($set_lender_name->params['set_lender_id'] === $params['set_borrower_id'])
            return array('error_ident' => 'SET_BORROWER_ID_SANITY_FAILED', 'error_mess' => 'You attempted to set the borrower id and the lender id (through the lender name) to the same thing!');
        }else {
          if(!isset($set_lender_name->params['resolve_id_callbacks'])) 
            $set_lender_name->params['resolve_id_callbacks'] = array();
          
          $set_lender_name->params['resolve_id_callbacks'][] = function($helper, $resolved_id) use (&$params) {
            if($resolved_id === $params['set_borrower_id'])
              return array('error_ident' => 'SET_BORROWER_ID_SANITY_FAILED', 'error_mess' => 'You attempted to set the borrower id and the lender id (through the lender name) to the same thing!');
            return null;
          };
        }
      }else {
        $params['resolved_lending_to_self_conflicts'] = false;
      }
      
      // Then verify that we aren't also using set_lender_name
      if(isset($helper->callbacks_dict['set_lender_name'])) {
        return array('error_ident' => 'SET_BORROWER_ID_SANITY_FAILED', 'error_mess' => 'You attempted to set the borrower id and the borrower name - these ultimately do the same thing, so this is a conflict'); 
      }
      
      return null;
    };
    
    $result->preview_callback = function($helper, &$params, $sql_conn) {
      if(!$params['resolved_lending_to_self_conflicts']) {
        // verify nothing in the temporary table has a loan_lender_id thats the same as 
        // what we're trying to set the loan borrower id to
        $err_prefix = 'parse_set_borrower_id#preview_callback';
        check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('SELECT loan_id FROM ' . $helper->temporary_table . ' WHERE loan_lender_id=?'));
        check_db_error($sql_conn, $err_prefix, $stmt->bind_param('i', $params['set_borrower_id']));
        check_db_error($sql_conn, $err_prefix, $stmt->execute());
        check_db_error($sql_conn, $err_prefix, $res = $stmt->get_result());
        
        $failed_ids = array();
        while(($row = $res->fetch_row()) !== null) {
          $failed_ids[] = $row[0];
        }
        
        $res->close();
        $stmt->close();
        
        if(count($failed_ids) > 0) {
          $concatted = implode(', ', $failed_ids);
          return array('error_ident' => 'SET_BORROWER_ID_PREVIEW_FAILED', 'error_mess' => 'If this operation had been allowed to go through, the following loan ids would have the same lender and borrower: ' . $concatted);
        }
        $params['resolved_lending_to_self_conflicts'] = true;
      }
    };
    
    $result->set_callback = function($helper, &$params) {
      return 'borrower_id = ?';
    };
    
    $result->bind_set_callback = function($helper, &$params) {
      return array(array('i', $params['set_borrower_id']));
    };
    
    $result->result_callback = function($helper, &$params, $row, &$res) {
      $res['new_borrower_id'] = $params['set_borrower_id'];
    };

    $upd_helper->add_callback($result);
    return null;
  }
  
  public static function parse_set_lender_id($sel_helper, $upd_helper, $params) {
    $set_lender_id = null;
    if(isset($params['set_lender_id']) && is_numeric($params['set_lender_id'])) {
      $set_lender_id = intval($params['set_lender_id']);
    }
    
    if($set_lender_id === null)
      return null;
    
    $result = new UpdateQueryCallback('set_lender_id', array('set_lender_id' => $set_lender_id));
    $result->sanity_callback = function($helper, &$params, $sql_conn) {
      $err_prefix = 'parse_set_lender_id#sanity_callback';
      check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('SELECT id FROM users WHERE id=?'));
      check_db_error($sql_conn, $err_prefix, $stmt->bind_param('i', $params['set_lender_id']));
      check_db_error($sql_conn, $err_prefix, $stmt->execute());
      check_db_error($sql_conn, $err_prefix, $res = $stmt->get_result());
      
      $row = $res->fetch_row();
      $row_exists = $row !== null;
      unset($row);
      $res->close();
      $stmt->close();
      
      if(!$row_exists) {
        return array('error_ident' => 'SET_LENDER_ID_SANITY_FAILED', 'error_mess' => 'You attempted to set the lender id to ' . $params['set_lender_id'] . ' - but no user has that id!');
      }
      
      // Then verify that EITHER we are setting the borrower id to something else, or flag
      // resolved_lending_to_self_conflicts to false
      
      $params['resolved_lending_to_self_conflicts'] = true;
      if(isset($helper->callbacks_dict['set_borrower_id'])) {
        $set_borrower_id = $helper->callbacks_dict['set_borrower_id'];
        if($set_borrower_id === $params['set_lender_id']) 
          return array('error_ident' => 'SET_LENDER_ID_SANITY_FAILED', 'error_mess' => 'You attempted to set the borrower id and lender id to the same thing!');
      }elseif($helper->callbacks_dict['set_borrower_name']) {
        $set_borrower_name = $helper->callbacks_dict['set_borrower_name'];
        if(isset($set_borrower_name->params['set_borrower_id'])) {
          if($set_borrower_name->params['set_borrower_id'] === $params['set_lender_id'])
            return array('error_ident' => 'SET_LENDER_ID_SANITY_FAILED', 'error_mess' => 'You attempted to set the borrower id (through the borrower name) and the lender id to the same thing!');
        }else {
          if(!isset($set_borrower_name->params['resolve_id_callbacks'])) 
            $set_borrower_name->params['resolve_id_callbacks'] = array();
          
          $set_borrower_name->params['resolve_id_callbacks'][] = function($helper, $resolved_id) use (&$params) {
            if($resolved_id === $params['set_lender_id'])
              return array('error_ident' => 'SET_LENDER_ID_SANITY_FAILED', 'error_mess' => 'You attempted to set the borrower id (through the borrower name) and the lender id to the same thing!');
            return null;
          };
        }
      }else {
        $params['resolved_lending_to_self_conflicts'] = false;
      }
      
      // Then verify that we aren't also using set_lender_name
      if(isset($helper->callbacks_dict['set_lender_name'])) {
        return array('error_ident' => 'SET_LENDER_ID_SANITY_FAILED', 'error_mess' => 'You attempted to set the lender id and the lender name - these ultimately do the same thing, so this is a conflict'); 
      }
      
      return null;
    };
    
    $result->preview_callback = function($helper, &$params, $sql_conn) {
      if(!$params['resolved_lending_to_self_conflicts']) {
        // verify nothing in the temporary table has a loan_borrower_id thats the same as 
        // what we're trying to set the loan lender id to
        $err_prefix = 'parse_set_lender_id#preview_callback';
        check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('SELECT loan_id FROM ' . $helper->temporary_table . ' WHERE loan_borrower_id=?'));
        check_db_error($sql_conn, $err_prefix, $stmt->bind_param('i', $params['set_lender_id']));
        check_db_error($sql_conn, $err_prefix, $stmt->execute());
        check_db_error($sql_conn, $err_prefix, $res = $stmt->get_result());
        
        $failed_ids = array();
        while(($row = $res->fetch_row()) !== null) {
          $failed_ids[] = $row[0];
        }
        
        $res->close();
        $stmt->close();
        
        if(count($failed_ids) > 0) {
          $concatted = implode(', ', $failed_ids);
          return array('error_ident' => 'SET_LENDER_ID_PREVIEW_FAILED', 'error_mess' => 'If this operation had been allowed to go through, the following loan ids would have the same lender and borrower: ' . $concatted);
        }
        $params['resolved_lending_to_self_conflicts'] = true;
      }
    };
    
    $result->set_callback = function($helper, &$params) {
      return 'lender_id = ?';
    };
    
    $result->bind_set_callback = function($helper, &$params) {
      return array(array('i', $params['set_lender_id']));
    };
    
    
    $result->result_callback = function($helper, &$params, $row, &$res) {
      $res['new_lender_id'] = $params['set_lender_id'];
    };

    $upd_helper->add_callback($result);
    return null;
  }
  
  public static function parse_set_borrower_name($sel_helper, $upd_helper, $params) {
    $set_borrower_name = null;
    if(isset($params['set_borrower_name'])) {
      $set_borrower_name = $params['set_borrower_name'];
    }
    
    if($set_borrower_name === null)
      return null;
    
    $result = new UpdateQueryCallback('set_borrower_name', array('set_borrower_name' => $set_borrower_name));
    $result->sanity_callback = function($helper, &$params, $sql_conn) {
      // Resolve the name to an id
      $err_prefix = 'parse_set_borrower_name#sanity_callback';
      check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('SELECT user_id FROM usernames WHERE username=?'));
      check_db_error($sql_conn, $err_prefix, $stmt->bind_param('s', $params['set_borrower_name']));
      check_db_error($sql_conn, $err_prefix, $stmt->execute());
      check_db_error($sql_conn, $err_prefix, $res = $stmt->get_result());
      
      $row = $res->fetch_row();
      if($row !== null) {
        $params['set_borrower_id'] = $row[0];
      }
      $res->close();
      $stmt->close();
      
      if(!isset($params['set_borrower_id'])) {
        return array('error_ident' => 'SET_BORROWER_NAME_SANITY_FAILED', 'error_mess' => 'You attempted to set the borrower id through the borrower name proxy, but there is no corresponding user with the given name. If the user has not ever interacted with the LoansBot before, reconsider your actions or contact Tjstrechalot. More likely, you just made a typo in the username');
      }
      
      if(isset($params['resolve_id_callbacks'])) {
        $cbs = $params['resolve_id_callbacks'];
        foreach($cbs as $cb) {
          $tmp = $cb($helper, $params['set_borrower_id']);
          if($tmp !== null)
            return $tmp;
        }
      }
       
      // Either ensure we are going to set the lender id to something else or 
      // flag resolved_lending_to_self as false
      
      $params['resolved_lending_to_self'] = true;
      if(isset($helper->callbacks_dict['set_lender_id'])) {
        $set_lender_id = $helper->callbacks_dict['set_lender_id'];
        if($set_lender_id->params['set_lender_id'] === $params['set_borrower_id'])
          return array('error_ident' => 'SET_BORROWER_NAME_SANITY_FAILED', 'error_mess' => 'You attempted to set the lender id and the borrower id (through the borrower name) to the same thing!');
      }elseif(isset($helper->callbacks_dict['set_lender_name'])) {
        $set_lender_name = $helper->callbacks_dict['set_lender_name'];
        if(isset($set_lender_name->params['set_lender_id'])) {
          if($set_lender_name->params['set_lender_id'] === $params['set_borrower_id'])
            return array('error_ident' => 'SET_BORROWER_NAME_SANITY_FAILED', 'error_mess' => 'You attempted to set the lender id (through the lender name) and the borrower id (through the borrower name) to the same thing!');
        }else {
          if(!isset($set_lender_name->params['resolve_id_callbacks']))
            $set_lender_name->params['resolve_id_callbacks'] = array();
          
          $set_lender_name->params['resolve_id_callbacks'][] = function($helper, $resolved_id) use ($params) {
            if($resolved_id === $params['set_borrower_id'])
              return array('error_ident' => 'SET_BORROWER_NAME_SANITY_FAILED', 'error_mess' => 'You attempted to set the lender id (through the lender name) and the borrower id (through the borrower name) to the same thing!');
          };
        }
      }else {
        $params['resolved_lending_to_self'] = false;
      }
    };
    
    $result->preview_callback = function($helper, &$params, $sql_conn) {
      if(!$params['resolved_lending_to_self']) {
        // verify nothing in the temporary table has a loan_lender_id thats the same as 
        // what we're trying to set the loan borrower id to
        $err_prefix = 'parse_set_borrower_name#preview_callback';
        check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('SELECT loan_id FROM ' . $helper->temporary_table . ' WHERE loan_lender_id=?'));
        check_db_error($sql_conn, $err_prefix, $stmt->bind_param('i', $params['set_borrower_id']));
        check_db_error($sql_conn, $err_prefix, $stmt->execute());
        check_db_error($sql_conn, $err_prefix, $res = $stmt->get_result());
        
        $failed_ids = array();
        while(($row = $res->fetch_row()) !== null) {
          $failed_ids[] = $row[0];
        }
        
        $res->close();
        $stmt->close();
        
        if(count($failed_ids) > 0) {
          $concatted = implode(', ', $failed_ids);
          return array('error_ident' => 'SET_BORROWER_NAME_PREVIEW_FAILED', 'error_mess' => 'If this operation had been allowed to go through, the following loan ids would have the same lender and borrower: ' . $concatted);
        }
        $params['resolved_lending_to_self'] = true;
      }
    };
    
    $result->set_callback = function($helper, &$params) {
      return 'borrower_id = ?';
    };
    
    $result->bind_set_callback = function($helper, &$params) {
      return array(array('i', $params['set_borrower_id']));
    };
    
    
    $result->result_callback = function($helper, &$params, $row, &$res) {
      $res['new_borrower_id'] = $params['set_borrower_id'];
    };

    $upd_helper->add_callback($result);
    return $result;
  }
  
  public static function parse_set_lender_name($sel_helper, $upd_helper, $params) {
    $set_lender_name = null;
    if(isset($params['set_lender_name'])) {
      $set_lender->name = $params['set_lender_name'];
    }
    
    if($set_lender_name === null)
      return null;
    
    $result = new UpdateQueryCallback('set_lender_name', array('set_lender_name' => $set_lender_name));
    $result->sanity_callback = function($helper, &$params, $sql_conn) {
      // Resolve the name to an id
      $err_prefix = 'parse_set_lender_name#sanity_callback';
      check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('SELECT user_id FROM usernames WHERE username=?'));
      check_db_error($sql_conn, $err_prefix, $stmt->bind_param('s', $params['set_lender_name']));
      check_db_error($sql_conn, $err_prefix, $stmt->execute());
      check_db_error($sql_conn, $err_prefix, $res = $stmt->get_result());
      
      $row = $res->fetch_row();
      if($row !== null) {
        $params['set_lender_id'] = $row[0];
      }
      $res->close();
      $stmt->close();
      
      if(!isset($params['set_lender_id'])) {
        return array('error_ident' => 'SET_LENDER_NAME_SANITY_FAILED', 'error_mess' => 'You attempted to set the lender id through the lender name proxy, but there is no corresponding user with the given name. If the user has not ever interacted with the LoansBot before, reconsider your actions or contact Tjstrechalot. More likely, you just made a typo in the username');
      }
      
      if(isset($params['resolve_id_callbacks'])) {
        $cbs = $params['resolve_id_callbacks'];
        foreach($cbs as $cb) {
          $tmp = $cb($helper, $params['set_lender_id']);
          if($tmp !== null)
            return $tmp;
        }
      }
       
      // Either ensure we are going to set the lender id to something else or 
      // flag resolved_lending_to_self as false
      
      $params['resolved_lending_to_self'] = true;
      if(isset($helper->callbacks_dict['set_borrower_id'])) {
        $set_borrower_id = $helper->callbacks_dict['set_borrower_id'];
        if($set_borrower_id->params['set_borrower_id'] === $params['set_lender_id'])
          return array('error_ident' => 'SET_LENDER_NAME_SANITY_FAILED', 'error_mess' => 'You attempted to set the lender id (through the lender naem) and the borrower id to the same thing!');
      }elseif(isset($helper->callbacks_dict['set_borrower_name'])) {
        $set_borrower_name = $helper->callbacks_dict['set_borrower_name'];
        if(isset($set_borrower_name->params['set_borrower_id'])) {
          if($set_borrower_name->params['set_borrower_id'] === $params['set_lender_id'])
            return array('error_ident' => 'SET_LENDER_NAME_SANITY_FAILED', 'error_mess' => 'You attempted to set the lender id (through the lender name) and the borrower id (through the borrower name) to the same thing!');
        }else {
          if(!isset($set_borrower_name->params['resolve_id_callbacks']))
            $set_borrower_name->params['resolve_id_callbacks'] = array();
          
          $set_borrower_name->params['resolve_id_callbacks'][] = function($helper, $resolved_id) use ($params) {
            if($resolved_id === $params['set_lender_id'])
              return array('error_ident' => 'SET_LENDER_NAME_SANITY_FAILED', 'error_mess' => 'You attempted to set the lender id (through the lender name) and the borrower id (through the borrower name) to the same thing!');
          };
        }
      }else {
        $params['resolved_lending_to_self'] = false;
      }
    };
    
    $result->preview_callback = function($helper, &$params, $sql_conn) {
      if(!$params['resolved_lending_to_self']) {
        // verify nothing in the temporary table has a loan_borrower_id thats the same as 
        // what we're trying to set the loan lender id to
        $err_prefix = 'parse_set_lender_name#preview_callback';
        check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('SELECT loan_id FROM ' . $helper->temporary_table . ' WHERE loan_borrower_id=?'));
        check_db_error($sql_conn, $err_prefix, $stmt->bind_param('i', $params['set_lender_id']));
        check_db_error($sql_conn, $err_prefix, $stmt->execute());
        check_db_error($sql_conn, $err_prefix, $res = $stmt->get_result());
        
        $failed_ids = array();
        while(($row = $res->fetch_row()) !== null) {
          $failed_ids[] = $row[0];
        }
        
        $res->close();
        $stmt->close();
        
        if(count($failed_ids) > 0) {
          $concatted = implode(', ', $failed_ids);
          return array('error_ident' => 'SET_LENDER_NAME_PREVIEW_FAILED', 'error_mess' => 'If this operation had been allowed to go through, the following loan ids would have the same lender and borrower: ' . $concatted);
        }
        $params['resolved_lending_to_self'] = true;
      }
      return null;
    };
    
    $result->set_callback = function($helper, &$params) {
      return 'lender_id = ?';
    };
    
    $result->bind_set_callback = function($helper, &$params) {
      return array(array('i', $params['set_lender_id']));
    };
    
    $result->result_callback = function($helper, &$params, $row, &$res) {
      $res['new_lender_id'] = $params['set_lender_id'];
    };

    $upd_helper->add_callback($result);
    return null;
  }

  public static function parse_set_principal_cents($sel_helper, $upd_helper, $params) {
    $set_principal_cents = null;
    if(isset($params['set_principal_cents']) && is_numeric($params['set_principal_cents'])) {
      $set_principal_cents = intval($params['set_principal_cents']);

      if($set_principal_cents <= 0) {
        return array('error_ident' => 'INVALID_ARGUMENT', 'error_mess' => 'You cannot set the principal a value less than 1 cent');
      }
    }

    if($set_principal_cents === null)
      return;

    $result = new UpdateQueryCallback('set_principal_cents', array('set_principal_cents' => $set_principal_cents));

    $result->sanity_callback = function($helper, &$params, $sql_conn) {
      // if we're setting the principal repayment, make sure its greater than/equal to the principal. 
      // otherwise, flag resolved_greater_repayment_than_principal to false

      if(isset($helper->callbacks_dict['set_principal_repayment_cents'])) {
        $params['resolved_greater_repayment_than_principal'] = true;

        $set_principal_repayment_cents = $helper->callbacks_dict['set_principal_repayment_cents'];
        if($params['set_principal_cents'] < $set_principal_repayment_cents->params['set_principal_repayment_cents']) {
          return array('error_ident' => 'SET_PRINCIPAL_CENTS_SANITY_FAILED', 'error_mess' => 'You are attempting to set the principal repayment to less than the principal!');
        }
      }else {
        $params['resolved_greater_repayment_than_principal'] = false;
      }

      return null;
    };

    $result->preview_callback = function($helper, &$params, $sql_conn) {
      // verify we wont set principal cents over the principal repayment
      if(!$params['resolved_greater_repayment_than_principal']) {
        $err_prefix = 'parse_set_principal_cents#preview_callback';
        check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('SELECT loan_id FROM ' . $helper->temporary_table . ' WHERE loan_principal_repayment_cents > ?'));
        check_db_error($sql_conn, $err_prefix, $stmt->bind_param('i', $params['set_principal_cents']));
        check_db_error($sql_conn, $err_prefix, $stmt->execute());
        check_db_error($sql_conn, $err_prefix, $res = $stmt->get_result());
        
        $failed_ids = array();
        while(($row = $res->fetch_row()) !== null) {
          $failed_ids[] = $row[0];
        }
        
        $res->close();
        $stmt->close();
        
        if(count($failed_ids) > 0) {
          $concatted = implode(', ', $failed_ids);
          return array('error_ident' => 'SET_PRINCIPAL_CENTS_PREVIEW_FAILED', 'error_mess' => 'If this operation had been allowed to go through, the following loan ids would have a greater repayment than principal: ' . $concatted);
        }

        $params['resolved_greater_repayment_than_principal'] = true;
      }
    };

    $result->set_callback = function($helper, &$params) {
      return 'principal_cents = ?';
    };

    $result->bind_set_callback = function($helper, &$params) {
      return array(array('i', $params['set_principal_cents']));
    };

    $result->result_callback = function($helper, &$params, $row, &$res) {
      $res['new_principal_cents'] = $params['set_principal_cents'];
    };

    $upd_helper->add_callback($result);
    return null;
  }

  public static function parse_set_principal_repayment_cents($sel_helper, $upd_helper, $params) {
    $set_principal_repayment_cents = null;
    if(isset($params['set_principal_repayment_cents']) && is_numeric($params['set_principal_repayment_cents'])) {
      $set_principal_repayment_cents = intval($params['set_principal_repayment_cents']);

      if($set_principal_repayment_cents < 0) {
        return array('error_ident' => 'INVALID_ARGUMENT', 'error_mess' => 'You cannot set the principal repayment to a negative value.');
      }
    }

    if($set_principal_repayment_cents === null)
      return null;

    $result = new UpdateQueryCallback('set_principal_repayment_cents', array('set_principal_repayment_cents' => $set_principal_repayment_cents));
    $result->sanity_callback = function($helper, &$params, $sql_conn) {
      // if we're setting the principal, make sure its less than / equal to the repayment.
      // otherwise, flag resolved_greater_repayment_than_principal to false

      if(isset($helper->callbacks_dict['set_principal_cents'])) {
        $params['resolved_greater_repayment_than_principal'] = true;

        $set_principal_cents = $helper->callbacks_dict['set_principal_cents'];
        if($set_principal_cents->params['set_principal_cents'] < $params['set_principal_repayment_cents']) {
          return array('error_ident' => 'SET_PRINCIPAL_REPAYMENT_CENTS_SANITY_FAILED', 'error_mess' => 'You are attempting to set the principal repayment to less than the principal!');
        }
      }else {
        $params['resolved_greater_repayment_than_principal'] = false;
      }

      return null;
    };

    $result->preview_callback = function($helper, &$params, $sql_conn) {
      // verify we won't set the principal repayment to greater than the principal
      if(!$params['resolved_greater_repayment_than_principal']) {
        $err_prefix = 'parse_set_principal_repayment_cents#preview_callback';
        check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('SELECT loan_id FROM ' . $helper->temporary_table . ' WHERE loan_principal_cents < ?'));
        check_db_error($sql_conn, $err_prefix, $stmt->bind_param('i', $params['set_principal_repayment_cents']));
        check_db_error($sql_conn, $err_prefix, $stmt->execute());
        check_db_error($sql_conn, $err_prefix, $res = $stmt->get_result());
        
        $failed_ids = array();
        while(($row = $res->fetch_row()) !== null) {
          $failed_ids[] = $row[0];
        }
        
        $res->close();
        $stmt->close();
        
        if(count($failed_ids) > 0) {
          $concatted = implode(', ', $failed_ids);
          return array('error_ident' => 'SET_PRINCIPAL_REPAYMENT_CENTS_PREVIEW_FAILED', 'error_mess' => 'If this operation had been allowed to go through, the following loan ids would have a greater repayment than principal: ' . $concatted);
        }

        $params['resolved_greater_repayment_than_principal'] = true;
      }
    };

    $result->set_callback = function($helper, &$params) {
      return 'principal_repayment_cents = ?';
    };

    $result->bind_set_callback = function($helper, &$params) {
      return array(array('i', $params['set_principal_repayment_cents']));
    };

    $result->result_callback = function($helper, &$params, $row, &$res) {
      $res['new_principal_repayment_cents'] = $params['set_principal_repayment_cents'];
    };

    $upd_helper->add_callback($result);
    return null;
  }

  public static function parse_set_unpaid($sel_helper, $upd_helper, $params) {
    $set_unpaid = null;

    if(isset($params['set_unpaid']) && is_numeric($params['set_unpaid'])) {
      $set_unpaid = intval($params['set_unpaid']);

      if(!in_array($set_unpaid, array(0, 1))) {
        return array('error_ident' => 'INVALID_ARGUMENT', 'error_mess' => 'API Usage Error: set_unpaid can only be 0 or 1, but you tried to use ' . strval($set_unpaid));
      }
    }

    if($set_unpaid === null) 
      return null;

    $result = new UpdateQueryCallback('set_unpaid', array('set_unpaid' => $set_unpaid));
    $result->sanity_callback = function($helper, &$params, $sql_conn) {
      $params['resolved_unpaid_and_repaid'] = false;
 
      if($params['set_unpaid'] === 0) {
        $params['resolved_unpaid_and_repaid'] = true;
        return null;
      }

      if(isset($helper->callbacks_dict['set_principal_repayment_cents'])) {
        $set_principal_repayment_cents = $helper->callbacks_dict['set_principal_repayment_cents'];

        if($set_principal_repayment_cents->params['set_principal_repayment_cents'] === 0)
          $params['resolved_unpaid_and_repaid'] = true;
      }

      return null;
    };

    $result->preview_callback = function($helper, &$params, $sql_conn) {
      $err_prefix = 'parse_set_unpaid#preview_callback';
      if(!$params['resolved_unpaid_and_repaid']) {
        $params['resolved_unpaid_and_repaid'] = true;
        $stmt = null;
        if(isset($helper->callbacks_dicts['set_principal_cents'])) {
          $set_principal_cents = $helper->callbacks_dict['set_principal_cents'];

          if(isset($helper->callbacks_dict['set_principal_repayment_cents'])) {
            $set_principal_repayment_cents = $helper->callbacks_dict['set_principal_repayment_cents'];

            if($set_principal_cents->params['set_principal_cents'] === $set_principal_repayment_cents->params['set_principal_repayment_cents']) {
              return array('error_ident' => 'SET_UNPAID_PREVIEW_FAILED', 'error_mess' => 'You are attempting to set both the principal and repayment to the same thing while also flagging unpaid.');
            }else {
              return null;
            }
          }else {
            check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('SELECT loan_id FROM ' . $helper->temporary_table . ' WHERE loan_principal_repayment_cents = '));
            check_db_error($sql_conn, $err_prefix, $stmt->bind_param('i', $set_principal_cents->params['set_principal_cents']));
          }
        }elseif(isset($helper->callbacks_dict['set_principal_repayment_cents'])) {
          $set_principal_repayment_cents = $helper->callbacks_dict['set_principal_repayment_cents'];
          check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('SELECT loan_id FROM ' . $helper->temporary_table . ' WHERE loan_principal_cents = '));
          check_db_error($sql_conn, $err_prefix, $stmt->bind_param('i', $set_principal_repayment_cents->params['set_principal_repayment_cents']));
        }

        check_db_error($sql_conn, $err_prefix, $stmt->execute());
        check_db_error($sql_conn, $err_prefix, $res = $stmt->get_result());
        
        $failed_ids = array();
        while(($row = $res->fetch_row()) !== null) {
          $failed_ids[] = $row[0];
        }
        
        $res->close();
        $stmt->close();
        
        if(count($failed_ids) > 0) {
          $concatted = implode(', ', $failed_ids);
          return array('error_ident' => 'SET_UNPAID_PREVIEW_FAILED', 'error_mess' => 'If this operation had been allowed to go through, the following loan ids would be both fully repaid and unpaid: ' . $concatted);
        }
      }
    };

    $result->set_callback = function($helper, &$params) {
      return 'unpaid = ?';
    };

    $result->bind_set_callback = function($helper, &$params) {
      return array(array('i', $params['set_unpaid']));
    };

    $result->result_callback = function($helper, &$params, $row, &$res) {
      $res['new_unpaid'] = $params['set_unpaid'];
    };

    $upd_helper->add_callback($result);
    return null;
  }

  public static function parse_set_deleted($sel_helper, $upd_helper, $params) {
    $set_deleted = null;
    if(isset($params['set_deleted']) && is_numeric($params['set_deleted'])) {
      $set_deleted = intval($params['set_deleted']);

      if(!in_array($set_deleted, array(0, 1)))
        return array('error_ident' => 'INVALID_ARGUMENT', 'error_mess' => 'API Usage Error: set_deleted must be 0 or 1 but you tried to set it to ' . strval($set_deleted));
    }

    if($set_deleted === null)
      return;

    $result = new UpdateQueryCallback('set_deleted', array('set_deleted' => $set_deleted));
    if($set_deleted === 1) {
      $result->params['new_deleted_at'] = time();
    }

    $result->sanity_callback = function($helper, &$params, $sql_conn) {
      if($params['set_deleted'] === 0) {
        if(isset($helper->callbacks_dict['set_deleted_reason'])) 
          return array('error_ident' => 'SET_DELETED_SANITY_FAILED', 'error_mess' => 'You tried to undelete loans and provide a reason for why they are deleted.');

        return null;
      }

      if(isset($helper->callbacks_dict['set_deleted_reason'])) 
        return null;

      return array('error_ident' => 'SET_DELETED_SANITY_FAILED', 'error_mess' => 'You tried to flag loans as deleted without providing a valid reason');
    };
    $result->preview_callback = function($helper, &$params, $sql_conn) {
      if($params['set_deleted'] === 0) {
        return null;
      }

      $err_prefix = 'parse_set_deleted#preview_callback';
      check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('SELECT loan_id FROM ' . $helper->temporary_table . ' WHERE deleted = 1'));
      check_db_error($sql_conn, $err_prefix, $stmt->execute());
      check_db_error($sql_conn, $err_prefix, $res = $stmt->get_result());

      $failed_ids = array();
      while(($row = $res->fetch_row()) !== null) {
        $failed_ids[] = $row[0];
      }
      
      $res->close();
      $stmt->close();
      
      if(count($failed_ids) > 0) {
        $concatted = implode(', ', $failed_ids);
        return array('error_ident' => 'SET_DELETED_PREVIEW_FAILED', 'error_mess' => 'You cannot set deleted to true for loans that are already deleted. You may modify the deleted reason by just setting deleted reason without setting deleted. The offending loan id(s) is/are: ' . $concatted);
      }
    };
    $result->set_callback = function($helper, &$params) {
      if($params['set_deleted'] === 1) {
        return 'deleted = 1, deleted_at = ?';
      }else {
        return 'deleted = 0, deleted_reason = null, deleted_at = null';
      }
    };

    $result->bind_set_callback = function($helper, &$params) {
      if($params['set_deleted'] === 1)
        return array(array('s', date('Y-m-d H:i:s', $params['new_deleted_at'])));
      else
        return array();
    };

    $result->result_callback = function($helper, &$params, $row, &$res) {
      $res['new_deleted'] = $params['set_deleted'];

      if($params['set_deleted'] === 0) {
        $res['new_deleted_reason'] = null;
        $res['new_deleted_at'] = null;
      }else {
        $res['new_deleted_at'] = $params['new_deleted_at'] * 1000;
      }
    };

    $upd_helper->add_callback($result);
    return null;
  }

  public static function parse_set_deleted_reason($sel_helper, $upd_helper, $params) {
    $set_deleted_reason = null;
    if(isset($params['set_deleted_reason'])) {
      $set_deleted_reason = $params['set_deleted_reason'];

      if(strlen($set_deleted_reason) < 5)
        return array('error_ident' => 'INVALID_ARGUMENT', 'error_mess' => 'The deleted reason must be at least 5 characters long');
    }

    if($set_deleted_reason === null)
      return;

    $result = new UpdateQueryCallback('set_deleted_reason', array('set_deleted_reason' => $set_deleted_reason));
    $result->sanity_callback = function($helper, &$params, $sql_conn) {
      $params['resolved_reason_without_deleted'] = false;

      if(isset($helper->callbacks_dict['set_deleted'])) {
        $set_deleted = $helper->callbacks_dict['set_deleted'];
        if($set_deleted->params['set_deleted'] === 0)
          return array('error_ident' => 'SET_DELETED_REASON_SANITY_FAILED', 'error_mess' => 'You are trying to set deleted to 0 while also setting a deleted reason');
        $params['resolved_reason_without_deleted'] = true;
      }
    };
    $result->preview_callback = function($helper, &$params, $sql_conn) {
      if(!$params['resolved_reason_without_deleted']) {
        $err_prefix = 'parse_set_deleted_reason#preview_callback';
        check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('SELECT loan_id FROM ' . $helper->temporary_table . ' WHERE deleted = 0'));
        check_db_error($sql_conn, $err_prefix, $stmt->execute());
        check_db_error($sql_conn, $err_prefix, $res = $stmt->get_result());

        $failed_ids = array();
        while(($row = $res->fetch_row()) !== null) {
          $failed_ids[] = $row[0];
        }
        
        $res->close();
        $stmt->close();
        
        if(count($failed_ids) > 0) {
          $concatted = implode(', ', $failed_ids);
          return array('error_ident' => 'SET_DELETED_PREVIEW_FAILED', 'error_mess' => 'You cannot set the deleted reason for loans that are not deleted. The offending loan id(s) is/are: ' . $concatted);
        }
      }
    };
    $result->set_callback = function($helper, &$params) {
      return 'deleted_reason = ?';
    };
    $result->bind_set_callback = function($helper, &$params) {
      return array(array('s', $params['set_deleted_reason']));
    };
    $result->result_callback = function($helper, &$params, $row, &$res) {
      $res['new_deleted_reason'] = $params['set_deleted_reason'];
    };

    $upd_helper->add_callback($result);
    return null;
  }
}
?>
