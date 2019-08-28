<?php
include_once('api/auth.php');
require_once('database/helper.php');


$is_trusted = is_trusted();

if (!is_logged_in()) {
  on_failed_auth();
  return;
}

$response_opt_out = DatabaseHelper::fetch_one($sql_conn, 'SELECT 1 FROM response_opt_outs WHERE user_id=?', array(array('i', $logged_in_user->id)));
$response_opt_out = ($response_opt_out !== null);

$borrower_req_pm_opt_out = DatabaseHelper::fetch_one($sql_conn, 'SELECT 1 FROM borrower_req_pm_opt_outs WHERE user_id=?', array(array('i', $logged_in_user->id)));
$borrower_req_pm_opt_out = ($borrower_req_pm_opt_out !== null);
?>
<!doctype html>
<html lang="en">

<head>
  <title>RedditLoans - Settings</title>
  <?php include('metatags.php'); ?>

  <?php include('bootstrap_css.php'); ?>
</head>

<body>
  <?php include('navigation.php'); ?>
  <div class="container px-2 py-5">
    <section>
      <h1>Settings</h1>
      <p>This page allows you to configure various settings for interacting with the LoansBot.</p>

      <?php if($is_trusted): ?>
      <h2>Non-REQ Response Opt Out</h2>
      <p>This configures if the LoansBot will respond to non-request submissions posted to /r/borrow with an automatic check. If you are not
        opted out (i.e., this is not checked), then the LoansBot will respond with a check to any non-meta submission you post to /r/borrow. If
        you are opted out (i.e., this is checked), the bot will not automatically respond with a check to any non-meta submission, unless that
        submission is a request. </p>

      <p>Regardless of this setting, the bot will respond normally to comments.</p>


      <div class="container-fluid alert" id="response-opt-out-status" style="display: none"></div>
      <form id="response-opt-out-form">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="" id="response-opt-out" <?php if ($response_opt_out) {
                                                                                            echo (' checked');
                                                                                          } ?>>
          <label class="form-check-label" for="response-opt-out">
            Response Opt-Out
          </label>
        </div>
        <div class="form-group row">
          <button id="response-opt-out-submit-button" type="submit" class="col-auto btn btn-primary">Submit</button>
        </div>
      </form>
      <?php endif; ?>

      <h2>Borrower REQ PM Opt Out</h2>
      <p>This configures if the LoansBot will send you a pm when a borrower you have an active loan with makes a REQ thread on /r/borrow.
        The default, unchecked, option is that the LoansBot sends you such a PM. If you check this, the LoansBot will no longer send you
        a PM in this circumstance.</p>

      <div class="container-fluid alert" id="borrower-req-pm-opt-out-status" style="display: none"></div>
      <form id="borrower-req-pm-out-form">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="" id="borrower-req-pm-opt-out" <?php if ($borrower_req_pm_opt_out) {
                                                                                            echo (' checked');
                                                                                          } ?>>
          <label class="form-check-label" for="borrower-req-pm-opt-out">
            Borrower REQ PM Opt-Out
          </label>
        </div>
        <div class="form-group row">
          <button id="borrower-req-pm-opt-out-submit-button" type="submit" class="col-auto btn btn-primary">Submit</button>
        </div>
      </form>
    </section>
  </div>
  <?php include('bootstrap_js.php') ?>
  <script src="js/status_text_utils.js"></script>
  <script type="text/javascript">
    $(function() {
      $('[data-toggle="tooltip"]').tooltip();
    });

    <?php if ($is_trusted): ?>
    $("#response-opt-out-form").on('submit', function(e) {
      e.preventDefault();

      var optout = $("#response-opt-out").is(":checked");
      var statusText = $("#response-opt-out-status");
      set_status_text(statusText, LOADING_GLYPHICON + ' Saving...', 'info', true);
      $("#response-opt-out-submit-button").attr('disabled', true);
      $.post("/api/response_opt_out.php", {
        optout: (optout ? 1 : 0)
      }, function(data, stat) {
        set_status_text(statusText, SUCCESS_GLYPHICON + ' Saved!', 'success', true);
      }).fail(function(xhr) {
        set_status_text_from_xhr(statusText, xhr);
      });
    });
    <?php endif; ?>

    $("#borrower-req-pm-opt-out-form").on('submit', function(e) {
      e.preventDefault();

      var optout = $("#borrower-req-pm-opt-out").is(":checked");
      var statusText = $("#borrower-req-pm-opt-out-status");
      set_status_text(statusText, LOADING_GLYPHICON + ' Saving...', 'info', true);
      $("#borrower-req-pm-opt-out-submit-button").attr('disabled', true);
      $.post("/api/borrower_req_pm_opt_out.php", {
        optout: (optout ? 1 : 0)
      }, function(data, stat) {
        set_status_text(statusText, SUCCESS_GLYPHICON + ' Saved!', 'success', true);
      }).fail(function(xhr) {
        set_status_text_from_xhr(statusText, xhr);
      });
    });
  </script>
</body>

</html>
<?php
$sql_conn->close();
?>