<?php
  include_once('api/auth.php');
  require_once('database/helper.php');
  require_once('database/red_flag_subreddits.php');

  if(!is_moderator()) {
    on_failed_auth();
    return;
  }

  $id = null;
  if(isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);
  }

  if($id === null) {
    header('Location: https://redditloans.com/red_flag_subreddits.php');
    $sql_conn->close();
    return;
  }

  $red_flag_subreddit = RedFlagSubredditsMapping::fetch_by_id($sql_conn, $id);
  if($red_flag_subreddit === null) {
    header('Location: https://redditloans.com/red_flag_subreddits.php');
    $sql_conn->close();
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
        <h1>View / Edit Red Flag Subreddit</h1>
        <p>Moderators can use this page to view or edit a red flag subreddit. The form is prefilled with the existing values.</p>

        <div class="container-fluid alert" id="statusText" style="display: none"></div>
        <form id="edit-red-flag-subreddit-form">
          <div class="form-group row">
            <input type="number" class="form-control" id="id" aria-label="ID" placeholder="ID" aria-describedby="idHelpBlock" value="<?= $id ?>">
            <small id="idHelpBlock" class="form-text text-muted">The id of the red flag subreddit you are viewing/modifying.</small>
          </div>
          <div class="form-group row">
            <input type="text" class="form-control" id="subreddit" aria-label="Subreddit" placeholder="Subreddit" aria-describedby="subredditHelpBlock" value="<?= $red_flag_subreddit->subreddit ?>"> 
            <small id="subredditHelpBlock" class="form-text text-muted">This is the subreddit that you want to raise a red flag</small>
          </div>
          <div class="form-group row">
            <input type="text" class="form-control" id="description" aria-label="Description" placeholder="Description" aria-describedby="descriptionHelpBlock" value="<?= $red_flag_subreddit->description ?>">
            <small id="descriptionHelpBlock" class="form-text text-muted">This should describe why this subreddit should be a concern. It is displayed inside the table as raw markdown. Must include the subreddit name!</small>
          </div>
          <div class="form-group row">
            <button id="submit-button" type="submit" class="col-auto btn btn-primary">Edit</button>
            <button id="delete-button" type="button" class="col-auto btn btn-danger">Delete</button>
          </div>
        </form>
      </section>
    </div>
    <?php include('bootstrap_js.php') ?>
    <script type="text/javascript">
      $(function () {
        $('[data-toggle="tooltip"]').tooltip();
      });
      $("#edit-red-flag-subreddit-form").on('submit', function(e) {
        e.preventDefault();

        var id = parseInt($("#id").val());
        var description = $("#description").val();
       
        $("#submit-button").attr('disabled', true);
        $("#delete-button").attr('disabled', true);
        var statusText = $("#status-text");
        statusText.fadeOut('fast', function() {
          statusText.removeClass("alert-danger").removeClass("alert-success");
          statusText.addClass("alert-info");
          statusText.html("<span class=\"glyphicon glyphicon-refresh glyphicon-refresh-animate\"></span> Editting red flag...");
          statusText.fadeIn('fast', function() {
            $.post("/api/edit_red_flag_subreddit.php", { id: id, description: description }, function(data, stat) {
              statusText.fadeOut('fast', function() {
                statusText.removeClass("alert-info").addClass("alert-success");
                statusText.html("<span class=\"glyphicon glyphicon-ok\"></span> Success! Refresh the page and you should see the changes stick.");
                statusText.fadeIn('fast');
                $("#submit-button").removeAttr('disabled');
                $("#delete-button").removeAttr('disabled');
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
                $("#delete-button").removeAttr('disabled');
              });
            });
          });
        });
      });
      $("#delete-button").click(function(e) {
        e.preventDefault();

        var id = parseInt($("#id").val());

        $("#submit-button").attr('disabled', true);
        $("#delete-button").attr('disabled', true);
        var statusText = $("#status-text");
        statusText.fadeOut('fast', function() {
          statusText.removeClass("alert-danger").removeClass("alert-success");
          statusText.addClass("alert-info");
          statusText.html("<span class=\"glyphicon glyphicon-refresh glyphicon-refresh-animate\"></span> Deleting red flag...");
          statusText.fadeIn('fast', function() {
            $.post("/api/delete_red_flag_subreddit.php", { id: id }, function(data, stat) {
              statusText.fadeOut('fast', function() {
                statusText.removeClass("alert-info").addClass("alert-success");
                statusText.html("<span class=\"glyphicon glyphicon-ok\"></span> Success! Refresh the page to return to the index page");
                statusText.fadeIn('fast');
                $("#submit-button").removeAttr('disabled');
                $("#delete-button").removeAttr('disabled');
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
                $("#delete-button").removeAttr('disabled');
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
