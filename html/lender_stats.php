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
      
      <h3 class="pt-3">Percent Requests Fulfilled</h3>
      <div class="container-fluid alert" id="perc-req-fulfilled-status" style="display: none"></div>
      <p>For this table you may configure when and who to look at:</p>
      <form id="perc-req-fulfilled-form">
        <div class="form-group row justify-content-between mb-3">
          <div class="col">
            <label for="perc-req-fulfilled-start-date">Start</label>
            <input type="date" id="perc-req-fulfilled-start-date" class="form-control" aria-describedby="#perc-req-fulfilled-start-date-help"> 
            <small id="perc-req-fulfilled-start-date-help" class="form-text text-muted">Only includes loans starting at midnight on this day in the below statistics</small>
          </div>
          <div class="col">
            <label for="perc-req-fulfilled-end-date">End</label>
            <input type="date" id="perc-req-fulfilled-end-date" class="form-control" aria-describedby="#perc-req-fulfilled-end-date-help"> 
            <small id="perc-req-fulfilled-end-date-help" class="form-text text-muted">Only includes loans before midnight on this day in the below statistics</small>
          </div>
        </div>
        <div class="form-group mb-3">
          <label for="perc-req-fulfilled-who-select">Who to include?</label>
          <select multiple id="perc-req-fulfilled-who-select" aria-describedby="perc-req-fulfilled-who-help" class="form-control">
            <option value="top5">Top 5 over this Period</option>
          </select>
          <small id="perc-req-fulfilled-who-help" class="form-text text-muted">Who is included in the below statistics</small>
        </div>
        <div class="form-group row justify-content-start align-items-center mb-3">
          <div class="col">
            <label for="perc-req-fulfilled-add-person">Add option to who</label>
            <input class="form-control" type="text" id="perc-req-fulfilled-add-person" aria-describedby="perc-req-fulfilled-add-person-help" placeholder="Username">
            <small id="perc-req-fulfilled-add-person-help" class="form-text text-muted">Adds a username option to Who to include?</small>
          </div>
          <div class="col">
            <button type="button" class="btn btn-primary" id="perc-req-fulfilled-add-person-button">Add Option</button>
          </div>
        </div>
      </form>
      <table id="percent-requests-fulfilled">
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

