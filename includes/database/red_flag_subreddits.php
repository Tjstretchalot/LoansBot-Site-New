<?php
  require_once 'database/common.php';

  class RedFlagSubredditsMapping {
    public static function fetch_all($sql_conn) {
      $err_prefix = 'RedFlagSubredditMapping#fetch_all';
      check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('SELECT * FROM red_flag_subreddits'));
      check_db_error($sql_conn, $err_prefix, $stmt->execute());
      check_db_error($sql_conn, $err_prefix, $res = $stmt->get_result());
      
      $result = array();

      while($row = $res->fetch_assoc()) {
        $result[] = new ArrayObject($row, ArrayObject::ARRAY_AS_PROPS);
      }

      $res->close();
      $stmt->close();
      return $result;
    }

    public static function fetch_subreddits($sql_conn) {
      $err_prefix = 'RedFlagSubredditMapping#fetch_subreddits';
      check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('SELECT subreddit FROM red_flag_subreddits'));
      check_db_error($sql_conn, $err_prefix, $stmt->execute());
      check_db_error($sql_conn, $err_prefix, $res = $stmt->get_result());
      
      $result = array();

      while($row = $res->fetch_assoc()) {
        $result[] = $row->subreddit;
      }

      $res->close();
      $stmt->close();
      return $result;
    }

    public static function fetch_by_id($sql_conn, $id) {
      $err_prefix = 'RedFlagSubredditMapping#fetch_by_id';
      check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('SELECT * FROM red_flag_subreddits WHERE id=?'));
      check_db_error($sql_conn, $err_prefix, $stmt->bind_param('i', $id));
      check_db_error($sql_conn, $err_prefix, $stmt->execute());
      check_db_error($sql_conn, $err_prefix, $res = $stmt->get_result());
      
      $row = $res->fetch_assoc();
      $res->close();
      $stmt->close();
      if($row === null) { return null; }
      return new ArrayObject($row, ArrayObject::ARRAY_AS_PROPS);
    }

    public static function fetch_for_subreddit($sql_conn, $subreddit) {
      $err_prefix = 'RedFlagSubredditMapping#fetch_for_subreddit';
      check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('SELECT * FROM red_flag_subreddits WHERE subreddit=?'));
      check_db_error($sql_conn, $err_prefix, $stmt->bind_param('s', $subreddit));
      check_db_error($sql_conn, $err_prefix, $stmt->execute());
      check_db_error($sql_conn, $err_prefix, $res = $stmt->get_result());
      
      $row = $res->fetch_assoc();
      $res->close();
      $stmt->close();
      if($row === null) { return null; }
      return new ArrayObject($row, ArrayObject::ARRAY_AS_PROPS);
    }

    public static function update_description($sql_conn, $id, $description) {
      $err_prefix = 'RedFlagSubredditMapping#update_description';
      error_log('$id=' . strval($id) .'; description='.strval($description)) 
      check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('UPDATE red_flag_subreddits SET description=? WHERE id=?'));
      check_db_error($sql_conn, $err_prefix, $stmt->bind_param('si', $subreddit, $id));
      check_db_error($sql_conn, $err_prefix, $stmt->execute());
      $stmt->close();
    }

    public static function add_subreddit($sql_conn, $subreddit, $description) {
      $err_prefix = 'RedFlagSubredditsMapping#add_subreddit';
      check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('INSERT INTO red_flag_subreddits (subreddit, description, created_at) VALUES (?, ?, NOW())'));
      check_db_error($sql_conn, $err_prefix, $stmt->bind_param('ss', $subreddit, $description));
      check_db_error($sql_conn, $err_prefix, $stmt->execute());
      $stmt->close();
    }

    public static function delete_by_id($sql_conn, $id) {
      $err_prefix = 'RedFlagSubredditsMapping#delete_by_id';
      check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare('DELETE FROM red_flag_subreddits WHERE id=?'));
      check_db_error($sql_conn, $err_prefix, $stmt->bind_param('i', $id));
      check_db_error($sql_conn, $err_prefix, $stmt->execute());
      $stmt->close();
    }
  }
?>
