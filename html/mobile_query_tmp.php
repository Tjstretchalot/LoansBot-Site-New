<?php
  include_once('connect_and_get_loggedin.php');
?>
<!doctype html>
<html lang="en">
  <head>
    <title>RedditLoansi - Mobile Query</title>
    <?php include('metatags.php'); ?>

    <?php include('bootstrap_css.php'); ?>
  </head>
  <body>
    <?php include('navigation.php'); ?>
    <div class="container px-2 py-5">
      <section>
        <h1>Sorry!</h1>

        <p>The website is currently undergoing repairs. See <a href="https://www.reddit.com/r/borrow/comments/7hzneq/website_issues_the_website_temporarily_down/">the thread</a></p>
      </section>
    </div>
    <?php include('bootstrap_js.php') ?>
  </body>
</html>
<?php
  $sql_conn->close();
?> 
