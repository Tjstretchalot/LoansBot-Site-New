<?php
  include_once('connect_and_get_loggedin.php');
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
        <div class="container-fluid alert" id="statusText" style="display: none"></div>
        <form id="create-account-form">
          <div class="form-group row">
            <input type="text" class="form-control" id="user-id" aria-label="Username" placeholder="Username" aria-describedby="usernameHelpBlock">
            <small id="usernameHelpBlock" class="form-text text-muted">What is the username of the reddit account you want to claim?</small>
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

      var form = $("create-account-form");
      form.on('submit', function(e) {
        e.preventDefault();
        
        var username = $("#username").val();
        $("#username").removeClass("is-invalid");
        if (username) {
          var statusText = $("#statusText");
          statusText.fadeOut('fast', function() {
            statusText.removeClass("alert-danger").removeClass("alert-success");
            statusText.addClass("alert-info");
            statusText.html("<span class=\"glyphicon glyphicon-refresh glyphicon-refresh-animate\"></span> Making request...");
            statusText.fadeIn('fast');
          });
          $("#submit-button").attr('disabled', true);
          $.post("/api/create_account_1.php", { username: username }, function(data, stat) {
            statusText.fadeOut('fast', function() {
              statusText.removeClass("alert-danger").removeClass("alert-info");
              statusText.addClass("alert-success");
              statusText.html("<span class=\"glyphicon glyphicon-ok\"></span> Success! You should recieve a message within the next few minutes from the LoansBot with information on how to proceed.");
              statusText.fadeIn('fast');
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
            });
          });
        }else {
          if (!username) {
            $("#username").addClass("is-invalid");
          }
        }
      });
    </script>
  </body>
</html>
<?php
  $sql_conn->close();
?>
