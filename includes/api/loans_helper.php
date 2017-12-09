<?php
require_once 'api/common.php';
require_once 'api/select_query_helper.php'


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

    $result = new SelectQueryCallback('loan_id', array('id' => $id));

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

  public static function return_borrower_id($helper, &$params) {
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

  public static function return_lender_id($helper, &$params) {
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

  public static function parse_lender_name($helper, &$params) {
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

    $result = new SelectQueryCallback('includes_user_name', array('includes_user_name' => $includes_user_name));
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

  public static function return_principal_cents($helper, &$params) {
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

  public static function parse_principal_cents($helper, &$params) {
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
   
  public static function return_principal_repayment_cents($helper, &$params) {
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

  public static function parse_principal_repayment_cents($helper, &$params) {
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

  public static function return_unpaid($helper, &$params) {
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

  public static function parse_unpaid($helper, &$params) {
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
  
  public static function parse_repaid($helper, &$params) {
    $repaid = null;
    if(isset($params['repaid']) && is_numeric($params['repaid'])) {
      $_unpaid = intval($params['repaid']);

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

  public static function return_updated_at($helper, &$params) {
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

  public static function parse_include_latest_repayment_at($helper, &$params) {
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

  public static function fetch_usernames($helper, &$params) {
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
?>
