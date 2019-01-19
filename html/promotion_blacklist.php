<?php
  include_once('api/auth.php');

  if(!is_moderator()) {
    on_failed_auth();
    return;
  }
?>
<!doctype html>
<html lang="en">
  <head>
    <title>RedditLoans - Promo Blacklist</title>
    <?php include('metatags.php'); ?>

    <?php include('bootstrap_css.php'); ?>
    <link rel="stylesheet" href="/css/basictable.css">
    <link rel="stylesheet" href="/css/query2.css">
  </head>
  <body>
    <?php include('navigation.php'); ?>
    <div class="container px-2 py-5">
      <div class="container-fluid alert" id="status-text" style="display: none"></div>
    </div>
  </body>
</html>
