<?php
  include_once('api/auth.php');
  require_once('database/helper.php');

  if(!is_trusted()) {
    on_failed_auth();
    return;
  }
?>
<!doctype html>
<html lang="en">
  <head>
    <title>RedditLoans</title>
    <?php include('metatags.php'); ?>

    <?php include('bootstrap_css.php'); ?>
    <link rel="stylesheet" href="/css/basictable.css">
    <link rel="stylesheet" href="/css/lender_stats.css">
  </head>
  <body>
    <?php include('navigation.php'); ?>
    <div class="container px-2 py-5">
      <h1>Lender Statistics</h1>

      <h2>Overview</h2>
      <p>This page is designed to provide information that lenders often find meaningful, but would either be unhelpful to the general masses or potentially malicious. Note that the information on this page is calculated from publicly visible information, so a dedicated user could find it either way. The access wall on this page is meant to stop the most likely issue - very fresh borrowers determining who to PM for a loan. For reference, the access wall is either trusted permissions or at least 5 loans completed as lender.</p>
      
      <h2>Stats</h2>
      <div class="container-fluid alert" id="stats-status" style="display: none"></div>
      
      <h3>Most Active Lenders (Overall)</h3>
      <table id="most-active-lenders-overall">
      </table>

      <h3 class="pt-3">Most Active Lenders <span id="most-active-lenders-recent-since">(Recent)</span></h3>
      <table id="most-active-lenders-recent">
      </table>
    </div>
    <?php include('bootstrap_js.php') ?>
    <script src="js/jquery.basictable.min.js"></script>
    <script src="js/status_text_utils.js"></script>
    <script src="js/lender_stats.js"></script>
  </body>
</html>
<?php
  $sql_conn->close();
?>

