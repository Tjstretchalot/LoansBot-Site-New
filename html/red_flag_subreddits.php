<?php
  include_once('api/auth.php');
  require_once('database/helper.php');
  require_once('database/red_flag_subreddits.php');

  if(!is_moderator()) {
    on_failed_auth();
    return;
  }

  $red_flag_subreddits = RedFlagSubredditsMapping::fetch_all($sql_conn);
?>
<!doctype html>
<html lang="en">
  <head>
    <title>RedditLoans</title>
    <?php include('metatags.php'); ?>

    <?php include('bootstrap_css.php'); ?>
  </head>
  <body>
    <?php include('navigation.php'); ?>
    <div class="container px-2 py-5">
      <section>
        <h1>Red Flag Subreddits</h1>
        <p>Moderators can use this page to view and update the subreddits which produce a red flag.</p>

        <h2>Index</h2>
        <ul>
        <?php foreach($red_flag_subreddits as $red_flag_sub): ?>
          <li><a href="red_flag_subreddit.php?id=<?= $red_flag_sub->id ?>"><?= $red_flag_sub->subreddit ?></a></li>
        <?php endforeach; ?>
        </ul>
        
        <h2>Add New Subreddit</h2>
        <a href="add_red_flag_subreddit.php">Click this link</a>
      </section>
    </div>
    <?php include('bootstrap_js.php') ?>
  </body>
</html>
<?php
  $sql_conn->close();
?>
