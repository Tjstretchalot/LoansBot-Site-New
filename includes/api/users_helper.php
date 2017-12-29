<?php
require_once 'api/common.php';
require_once 'api/select_query_helper.php';
require_once 'api/update_query_helper.php';

class UserParameterParser {
  // META FUNCTIONS
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

      $result = new SelectQueryCallback('limit', array('limit' => $limit));
      $helper->add_callback($result);
    }

    return null;
  }

  // RETURN FUNCTIONS
  public static function return_id($helper, $params) {
    $result = new SelectQueryCallback('return_id', array());

    $result->param_callback = function($helper) {
      return 'users.id AS user_id';
    };

    $result->result_callback = function($helper, $row, &$response_res) {
      $id = $row['user_id'];
      if($helper->format === 0 || $helper->format === 1) {
        $response_res[] = $id;
      }else {
        $response_res['user_id'] = $id;
      }
    };

    $helper->add_callback($result);
    return null;
  }

  public static function return_claimed($helper, $params) {
    if($helper->format < 1)
      return;

    $result = new SelectQueryCallback('return_claimed', array());

    $result->param_callback = function($helper) {
      return 'users.claimed AS user_claimed';
    };

    $result->result_callback = function($helper, $row, &$response_res) {
      $claimed = ($row['user_claimed'] === 1) ? true : false;
      if($helper->format === 1) {
        $response_res[] = $claimed;
      }else {
        $response_res['claimed'] = $claimed;
      }
    };

    $helper->add_callback($result);
    return null;
  }

  public static function return_created_at($helper, $params) {
    if($helper->format < 1)
      return;

    $result = new SelectQueryCallback('return_created_at', array());

    $result->param_callback = function($helper) {
      return 'users.created_at AS user_created_at';
    };

    $result->result_callback = function($helper, $row, &$response_res) {
      $created_at = strtotime($row['user_created_at']) * 1000;
      if($helper->format === 1) {
        $response_res[] = $created_at;
      }else {
        $response_res['created_at'] = $created_at;
      }
    };

    $helper->add_callback($result);
    return null;
  }

  public static function return_updated_at($helper, $params) {
    if($helper->format < 1)
      return;

    $result = new SelectQueryCallback('return_updated_at', array());

    $result->param_callback = function($helper) {
      return 'users.updated_at AS user_updated_at';
    };

    $result->result_callback = function($helper, $row, &$response_res) {
      $updated_at = strtotime($row['user_updated_at']) * 1000;
      if($helper->format === 1) {
        $response_res[] = $updated_at;
      }else {
        $response_res['updated_at'] = $updated_at;
      }
    };

    $helper->add_callback($result);
    return null;
  }

  public static function return_username($helper, $params) {
    if($helper->format < 3)
      return;

    $result = new SelectQueryCallback('return_username', array());

    $result->param_callback = function($helper) {
      return 'unames.concat_username AS user_username';
    };

    $result->join_callback = function($helper) {
      return 'LEFT OUTER JOIN (SELECT user_id, GROUP_CONCAT(username SEPERATOR \' AKA \') AS concat_username FROM usernames) unames ON users.id = unames.user_id';
    };

    $result->result_callback = function($helper, $row, &$response_res) {
      $response_res['username'] = $row['user_username'];
    };

    $helper->add_callback($result);
    return null;
  }

  // FILTERS

  public static function filter_by_id($helper, $params) {
    $id = null;
    if(isset($params['id']) && is_numeric($params['id'])) {
      $id = intval($params['id']);

      if($id < 0) 
        return array('error_ident' => 'INVALID_ARGUMENT', 'error_mess' => 'id must be positive');
    }

    if($id === null)
      return;

    $result = new SelectQueryCallback('filter_id', array('id' => $id));

    $result->where_callback = function($helper) {
      return 'WHERE users.id = ?';
    };

    $result->bind_where_callback = function($helper) use ($id) {
      return array(array('i', $id));
    };

    $helper->add_callback($result);
    return null;
  }

  public static function filter_by_username($helper, $params) {
    $username = null;
    if(isset($params['username'])) {
      $username = $params['username'];
    }

    if($username === null)
      return;

    if(isset($helper->callbacks_dict['filter_id']))
      return array('error_ident' => 'INVALID_ARGUMENT', 'error_mess' => 'Cannot filter by id and by username!');

    $result = new SelectQueryCallback('filter_username', array('username' => $username));

    $result->where_callback = function($helper) {
      return 'WHERE users.id IN (SELECT user_id FROM usernames WHERE username LIKE ?)'
    };

    $result->bind_where_callback = function($helper) use ($username) {
      return array(array('s', $username));
    };

    $helper->add_callback($result);
    return null;
  }
}
?>

