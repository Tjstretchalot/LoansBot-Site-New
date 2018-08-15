<?php
  include_once('api/auth.php');
  require_once('database/helper.php');
  require_once('database/red_flag_subreddits.php');

  if(!is_moderator()) {
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
  </head>
  <body>
    <?php include('navigation.php'); ?>
    <div class="container px-2 py-5">
      <section>
        <h1>Add Red Flag Subreddit</h1>
        <p>Moderators can use this page to add a new red flag subreddit</p>

        <div class="container-fluid alert" id="status-text" style="display: none"></div>
        <form id="add-red-flag-subreddit-form">
          <div class="form-group row">
            <input type="text" class="form-control" id="subreddit" aria-label="Subreddit" placeholder="Subreddit" aria-describedby="subredditHelpBlock"> 
            <small id="subredditHelpBlock" class="form-text text-muted">This is the subreddit that you want to raise a red flag</small>
          </div>
          <div class="form-group row">
            <input type="text" class="form-control" id="description" aria-label="Description" placeholder="Description" aria-describedby="descriptionHelpBlock">
            <small id="descriptionHelpBlock" class="form-text text-muted">This should describe why this subreddit should be a concern. It is displayed inside the table as raw markdown. Must include the subreddit name!</small>
          </div>
          <div class="form-group row">
            <button id="submit-button" type="submit" class="col-auto btn btn-primary">Submit</button>
          </div>
        </form>
      </section>
    </div>
    <?php include('bootstrap_js.php') ?>
    <script type="text/javascript">
      $(function () {
        $('[data-toggle="tooltip"]').tooltip();
      });
      $("#add-red-flag-subreddit-form").on('submit', function(e) {
        e.preventDefault();

        var subreddit = $("#subreddit").val();
        var description = $("#description").val();
       
        $("#submit-button").attr('disabled', true);
        var statusText = $("#status-text");
        statusText.fadeOut('fast', function() {
          statusText.removeClass("alert-danger").removeClass("alert-success");
          statusText.addClass("alert-info");
          statusText.html("<span class=\"glyphicon glyphicon-refresh glyphicon-refresh-animate\"></span> Adding red flag...");
          statusText.fadeIn('fast', function() {
            $.post("/api/add_red_flag_subreddit.php", { subreddit: subreddit, description: description }, function(data, stat) {
              statusText.fadeOut('fast', function() {
                statusText.removeClass("alert-info").addClass("alert-success");
                statusText.html("<span class=\"glyphicon glyphicon-ok\"></span> Success! Return to the index page and it should be there.");
                statusText.fadeIn('fast');
                $("#submit-button").removeAttr('disabled');
              });
            }).fail(function(xhr) {
              console.log(xhr.responseJSON);
              var json_resp = xhr.responseJSON;
              var err_type = json_resp.errors[0].error_type;
              var err_mess = json_resp.errors[0].error_message;
              console.log(err_type + ": " + err_mess);
              statusText.fadeOut('fast', function() {
                statusText.removeClass("alert-success").removeClass("alert-info");
                statusText.addClass("alert-danger");
                statusText.html("<span class=\"glyphicon glyphicon-remove\"></span> " + err_mess);
                statusText.fadeIn('fast');
                $("#submit-button").removeAttr('disabled');
              });
            });
          });
        });
      });
    </script>
  </body>
</html>
<?php
  $sql_conn->close();
?>
