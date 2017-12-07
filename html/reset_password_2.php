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
        <form id="reset-password-form">
          <div class="form-group row">
            <input type="number" class="form-control" id="user-id" aria-label="User ID" placeholder="User ID" aria-describedby="useridHelpBlock" step="1" min="1" <?php if(isset($_GET['user_id'])) { echo 'value="'.$_GET['user_id'].'"'; } ?> required>
            <small id="useridHelpBlock" class="form-text text-muted">What is the user ID of the reddit account? This should have been provided.</small>
          </div>
          <div class="form-group row">
            <input type="text" class="form-control" id="token" aria-label="Token" placeholder="Token" aria-describedby="tokenHelpBlock" <?php if(isset($_GET['code'])) { echo 'value="' . $_GET['code'] . '"'; } ?> required>
            <small id="tokenHelpBlock" class="form-text text-muted">The long and random string that the LoansBot sent only to you, which proves you recieved its message.</small>
          </div>
          <div class="form-group row">
            <input type="password" class="form-control" id="password-1" aria-label="Password" placeholder="Password" aria-describedby='password1HelpBlock' pattern=".{8,}$" required>
            <small id="password1HelpBlock" class="form-text text-muted">The new password you will use on this website. Must be at least 8 characters long.</small>
          </div>
          <div class="form-group row">
            <input type="password" class="form-control" id="password-2" aria-label="Password" placeholder="Password" aria-describedby='password2HelpBlock' pattern=".{8,}$" required>
            <small id="password2HelpBlock" class="form-text text-muted">Repeat your new password.</small>
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

      var form = $("#reset-password-form");
      form.on('submit', function(e) {
        e.preventDefault();
        
        var username = $("#username").val();
        var password1 = $("#password-1").val();
        var password2 = $("#password-2").val();

        if(!password1.equals(password2)) {
          statusText.fadeOut('fast', function() {
            statusText.removeClass("alert-danger").removeClass("alert-info");
            statusText.addClass("alert-danger");
            statusText.html("<span class=\"glyphicon glyphicon-remove\"></span> Password fields do not match!");
            statusText.fadeIn('fast');
          }
          return;
        }

        var token = $("#token").val();

        var statusText = $("#statusText");
        statusText.fadeOut('fast', function() {
          statusText.removeClass("alert-danger").removeClass("alert-success");
          statusText.addClass("alert-info");
          statusText.html("<span class=\"glyphicon glyphicon-refresh glyphicon-refresh-animate\"></span> Making request...");
          statusText.fadeIn('fast');
        });
        $("#submit-button").attr('disabled', true);
        $.post("/api/reset_password_2.php", { username: username, password: password1, token: token }, function(data, stat) {
          statusText.fadeOut('fast', function() {
            statusText.removeClass("alert-danger").removeClass("alert-info");
            statusText.addClass("alert-success");
            statusText.html("<span class=\"glyphicon glyphicon-ok\"></span> Success! Your password has been reset. You will be automatically redirected to the login page in 5 seconds.");
            statusText.fadeIn('fast');

            setTimeout(function() {
              window.location.href = "https://redditloans.com/login.php"; 
            }, 5000);
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
      });
    </script>
  </body>
</html>
<?php
  $sql_conn->close();
?>


