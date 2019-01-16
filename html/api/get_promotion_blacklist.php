<?php
/*
  Parameters:
    username - (optional) the username to search for. Compared using LIKE

  Result:
    {
      'result_type': 'PROMOTION_BLACKLIST'
      'success': true,
      'list': {
          {
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

  /* PARSING ARGUMENTS */
  if(isset($_GET['username'])) {
    $username = $_GET['username'];
  }

  /* VALIDATING ARGUMENTS */
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

  $query = 'SELECT users_username.username as username, ';
  $query .= 'mods_username.username as mod_username, promo_blacklist_users.reason as reason, ';
  $query .= 'UNIX_TIMESTAMP(promo_blacklist_users.added_at) * 1000 as added_at ';
  $query .= 'FROM promo_blacklist_users ';
  $query .= 'INNER JOIN usernames AS users_username ON users_username.user_id = promo_blacklist_users.user_id ';
  $query .= 'INNER JOIN usernames AS mods_username ON mods_username.user_id = promo_blacklist_users.mod_user_id ';
  $query .= 'WHERE promo_blacklist_users.removed_at IS NULL';
  $args = array();
  if($username !== null) {
    $query .= ' AND users_username LIKE ?';
    $args[] = array('s', $username);
  }

  $result = DatabaseHelper::fetch_all($sql_conn, $query, $args);

  echo_success('PROMOTION_BLACKLIST', array('list' => $result));
  $sql_conn->close();
}else {
  echo_fail(405, 'METHOD_NOT_ALLOWED', 'You must use a GET request at this endpoint');
}
?>