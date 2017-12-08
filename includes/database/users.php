<?php
  require_once 'database/common.php';

  class UserMapping {
    public static function fetch_by_id($sql_conn, $id) {
      $err_prefix = 'UserMapping::fetch_by_id';
      check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('SELECT * FROM users WHERE id=?'));
      check_db_error($sql_conn, $err_prefix, $stmt->bind_param('i', $id));
      check_db_error($sql_conn, $err_prefix, $stmt->execute());
      check_db_error($sql_conn, $err_prefix, $res = $stmt->get_result());

      $row = $res->fetch_assoc();
      $res->close();
      $stmt->close();
      if($row === null) { return null; }
      return new ArrayObject($row, ArrayObject::ARRAY_AS_PROPS);
    }

    public static function fetch_by_username($sql_conn, $username) {
      $err_prefix = 'UserMapping::fetch_by_username';
      check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('SELECT * FROM usernames WHERE username=?'));
      check_db_error($sql_conn, $err_prefix, $stmt->bind_param('s', $username));
      check_db_error($sql_conn, $err_prefix, $stmt->execute());
      check_db_error($sql_conn, $err_prefix, $res = $stmt->get_result());

      $username_row = $res->fetch_assoc();
      $res->close();
      $stmt->close();
      if($username_row === null) { return null; }

      $usable_user_id = $username_row['user_id'];
      check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('SELECT * FROM users WHERE id=?'));
      check_db_error($sql_conn, $err_prefix, $stmt->bind_param('i', $usable_user_id));
      check_db_error($sql_conn, $err_prefix, $stmt->execute());
      check_db_error($sql_conn, $err_prefix, $res = $stmt->get_result());

      $row = $res->fetch_assoc();
      $res->close();
      $stmt->close();
      if($row === null) {
        error_log('no corresponding user for username=' . $username . ', user_id=' . $username_row['user_id']);
        return null; 
      }
      return new ArrayObject($row, ArrayObject::ARRAY_AS_PROPS); 
    }

    public static function create_by_username($sql_conn, $username) {
      $err_prefix = 'UserMapping::create_by_username';

      check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('INSERT INTO users (auth, claimed, claim_link_sent_at, created_at, updated_at) values (0, 0, null, now(), now())'));
      check_db_error($sql_conn, $err_prefix, $stmt->execute());

      $user_id = $conn->insert_id;

      $stmt->close();


      check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('INSERT INTO usernames (user_id, username, created_at, updated_at) values (?, ?, now(), now())'));
      check_db_error($sql_conn, $err_prefix, $stmt->bind_param('is', $user_id, $username));
      check_db_error($sql_conn, $err_prefix, $stmt->execute());

      $stmt->close();

      return UserMapping::fetch_by_id($conn, $user_id);
    }

    public static function update_user_claim_code($sql_conn, $user) {
      $err_prefix = 'UserMapping::update_user_claim_code';
      
      $usable_claim_code = $user->claim_code;
      $usable_updated_at = date(time(), 'Y-m-d H:i:s');
      $usable_user_id = $user->id;

      check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('UPDATE users SET claim_code=?, claim_link_sent_at=NULL, updated_at=? WHERE id=?'));
      check_db_error($sql_conn, $err_prefix, $stmt->bind_param('si', $usable_claim_code, $usable_updated_at, $usable_user_id));
      check_db_error($sql_conn, $err_prefix, $stmt->execute());

      $user->updated_at = $usable_updated_at;
      $user->claim_link_sent_at = null;

      $stmt->close();
    }

    public static function update_user_after_claimed($sql_conn, $user, $email, $name, $street_address, $city, $state, $zip, $country) {
      $err_prefix = 'UserMapping::update_user_after_claimed';

      $usable_user_id = $user->id;
      $usable_now = date(time(), 'Y-m-d H:i:s');

      check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('UPDATE users SET claimed=1, email=?, name=?, street_address=?, city=?, state=?, zip=?, country=?, updated_at=? WHERE id=?'));
      check_db_error($sql_conn, $err_prefix, $stmt->bind_param('ssssssssi', $email, $name, $street_address, $city, $state, $zip, $country, $usable_now, $usable_user_id));
      check_db_error($sql_conn, $err_prefix, $stmt->execute());

      $stmt->close();
    }

    public static function update_password_by_id($sql_conn, $user_id, $new_digest) {
      check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('UPDATE users SET password_digest=? WHERE id=?'));
      check_db_error($sql_conn, $err_prefix, $stmt->bind_param('si', $new_digest, $user_id));
      check_db_error($sql_conn, $err_prefix, $stmt->execute());

      $stmt->close();
    }
  }
?>
