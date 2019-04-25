<?php
  include_once('connect_and_get_loggedin.php');
?>
<!doctype html>
<html lang="en">
  <head>
    <title>RedditLoans</title>
    <?php include('metatags.php'); ?>

    <?php include('bootstrap_css.php'); ?>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
  </head>
  <body>
    <?php include('navigation.php'); ?>
    <div class="container px-2 py-5">
      <section>
        <div class="container-fluid alert" id="statusText" style="display: none"></div>
        <form id="login-form">
          <div class="form-group row">
            <input type="text" class="form-control" id="username" aria-label="Username" placeholder="Username" aria-describedby="usernameHelpBlock">
            <small id="usernameHelpBlock" class="form-text text-muted">This will match your reddit username. You will need to <a href="/create_account.php">register your account</a> here first.</small>
          </div>
          <div class="form-group row">
            <input type="password" class="form-control" id="password" aria-label="Password", placeholder="Password" aria-describedby="passwordHelpBlock">
            <small id="passwordHelpBlock" class="form-text text-muted">This is at least 8 characters and is not necessarily the same as your reddit password. You can <a href="/reset_password.php">reset your password</a> if necessary.</small>
          </div>
          <div class="form-group row justify-content-between ml-0 mr-0">
            <label class="font-weight-bold col-sm pl-0" style="flex-grow: 1000">Session Duration <a href="#" data-toggle="tooltip" title="How long before your session automatically expires? Logging out will always expire your session." data-placement="top">&#x1f6c8;</a></label>
            <div style="flex-basis: 330px; flex-grow: 1">
              <div class="row justify-content-between">
                <div class="form-check col-auto">
                  <label class="form-check-label">
                    <input class="form-check-input" type="radio" name="durationRadios" id="permanentRadio"> Permanent
                  </label>
                </div>
                <div class="form-check col-auto">
                  <label class="form-check-label">
                    <input class="form-check-input" type="radio" name="durationRadios" id="30daysRadio"> 30 Days
                  </label>
                </div>
                <div class="form-check col-auto">
                  <label class="form-check-label">
                    <input class="form-check-input" type="radio" name="durationRadios" id="1dayRadio" checked> 1 Day
                  </label>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group row">
            <?php if ($_SERVER['LOANSSITE_RECAPTCHA_ENABLED'] === 'true'): ?>
            <div class="g-recaptcha" data-sitekey="<?= $_SERVER['LOANSSITE_RECAPTCHA_SITEKEY'] ?>"></div>
            <?php endif; ?>
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
      $("#login-form").on('submit', function(e) {
        e.preventDefault();

        var username = $("#username").val();
        var password = $("#password").val();
        var duration = "forever";
        if($("#30daysRadio").prop("checked")) {
          duration = "30days";
        }else if($("#1dayRadio").prop("checked")) {
          duration = "1day";
        }
        $("#username").removeClass("is-invalid");
        $("#password").removeClass("is-invalid");

        <?php if ($_SERVER['LOANSSITE_RECAPTCHA_ENABLED'] === 'true'): ?>
        var token = grecaptcha.getResponse();
        <?php endif; ?>
        if (username && password && duration <?php if ($_SERVER['LOANSSITE_RECAPTCHA_ENABLED'] === 'true'): ?> && token <?php endif; ?>) {
          var statusText = $("#statusText");
          statusText.fadeOut('fast', function() {
            statusText.removeClass("alert-danger").removeClass("alert-success");
            statusText.addClass("alert-info");
            statusText.html("<span class=\"glyphicon glyphicon-refresh glyphicon-refresh-animate\"></span> Logging in...");
            statusText.fadeIn('fast');
          });
          $("#submit-button").attr('disabled', true);
          $.post("/api/login.php", { username: username, password: password, duration: duration
          <?php if ($_SERVER['LOANSSITE_RECAPTCHA_ENABLED'] === 'true'): ?>, token: token <?php endif; ?> }, function(data, stat) {
            window.location.href = "/index.php";
          }).fail(function(xhr) {
            console.log(xhr.responseJSON);
            grecaptcha.reset();
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
        }else {
          if (!username) {
            $("#username").addClass("is-invalid");
          }
          if (!password) {
            $("#password").addClass("is-invalid");
          }

          grecaptcha.reset();
        }
      });
    </script>
  </body>
</html>
<?php
  $sql_conn->close();
?>
