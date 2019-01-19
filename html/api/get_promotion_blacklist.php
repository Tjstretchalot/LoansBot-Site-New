<?php
/*
  Parameters:
    username - (optional) the username to search for. Compared using LIKE
    min_id - (optional) sets the minimum id to return, helpful for pagination
    max_id - (optional) sets the maximum id to return, helpful for pagination
    limit - (optional) sets the maximum number of results to return, helpful for pagination

  Result:
    {
      'result_type': 'PROMOTION_BLACKLIST'
      'success': true,
      'list': {
          {
              id: 1,
              username: 'johndoe',
              mod_username: 'Tjstretchalot',
              reason: 'some text here',
              added_at: <utc milliseconds>
          },
          ...
      }
    }
*/

require_once 'database/common.php';
require_once 'api/common.php';
require_once 'database/helper.php';

if($_SERVER['REQUEST_METHOD'] === 'GET') {
  /* DEFAULT ARGUMENTS */
  $username = null;

  $min_id = null;
  $max_id = null;
  $limit = null;

  /* PARSING ARGUMENTS */
  if(isset($_GET['username'])) {
    $username = $_GET['username'];
  }

  if(isset($_GET['min_id']) && is_numeric($_GET['min_id'])) {
    $min_id = intval($_GET['min_id']);
  }

  if(isset($_GET['max_id']) && is_numeric($_GET['max_id'])) {
    $max_id = intval($_GET['max_id']);
  }

  if(isset($_GET['limit']) && is_numeric($_GET['limit'])) {
    $limit = intval($_GET['limit']);
    if($limit <= 0) {
      $limit = null;
    }
  }

  /* VALIDATING ARGUMENTS */
  if($username !== null && strlen($username) === 0) {
    $username = null;
  }

  if($username !== null) {
    foreach($username as $c) {
      if(!ctype_alnum($c) && $c !== '_' && $c !== '-' && $c !== '%') {
        echo_fail(400, 'INVALID_ARGUMENT', 'username contains invalid character ' . $c);
        return;
      }
    }
  }

  /* VALIDATING AUTHORIZATION */
  require_once 'api/auth.php';

  if(!is_moderator()) {
    on_failed_auth();
    return;
  }

  $query = 'SELECT promo_blacklist_users.id as id, users_username.username as username, ';
  $query .= 'mods_username.username as mod_username, promo_blacklist_users.reason as reason, ';
  $query .= 'UNIX_TIMESTAMP(promo_blacklist_users.added_at) * 1000 as added_at ';
  $query .= 'FROM promo_blacklist_users ';
  $query .= 'INNER JOIN usernames AS users_username ON users_username.user_id = promo_blacklist_users.user_id ';
  $query .= 'INNER JOIN usernames AS mods_username ON mods_username.user_id = promo_blacklist_users.mod_user_id ';
  $query .= 'WHERE promo_blacklist_users.removed_at IS NULL';
  $args = array();
  if($username !== null) {
    $query .= ' AND users_username.username LIKE ?';
    $args[] = array('s', $username);
  }
  if($min_id !== null) {
    $query .= ' AND promo_blacklist_users.id >= ?';
    $args[] = array('i', $min_id);
  }
  if($max_id !== null) {
    $query .= ' AND promo_blacklist_users.id <= ?';
    $args[] = array('i', $max_id);
  }

  if($max_id !== null && $min_id === null) {
    $query .= ' ORDER BY promo_blacklist_users.id DESC';
  }else {
    $query .= ' ORDER BY promo_blacklist_users.id ASC';
  }

  if($limit !== null) {
    $query .= ' LIMIT ?';
    $args[] = array('i', $limit);
  }

  $result = DatabaseHelper::fetch_all($sql_conn, $query, $args);

  echo_success('PROMOTION_BLACKLIST', array('list' => $result));
  $sql_conn->close();
}else {
  echo_fail(405, 'METHOD_NOT_ALLOWED', 'You must use a GET request at this endpoint');
}
?>
