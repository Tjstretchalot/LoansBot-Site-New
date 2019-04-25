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
        <h2>Important Notice for Borrowers</h2>
        <p>Do not reveal, for any reason, to any member of /r/borrow, all, the last 4 digits, or any part of your social security number, your passport, or your birth certificate because this information is almost solely used for identity theft. Additionally, do not provide information about <b>criminal investigations</b>, state or federal tax information, social welfare information, school records, communication between your attorney or government clients, or sealed settlements as this information is considered confidential. If this information is requested, immediately <a href="https://www.reddit.com/message/compose/?to=%2Fr%2Fborrow%0D%0A">contact the moderators</a>.</p>
      </section>
      <section>
        <h2>Claim your account</h2>
        <div class="container-fluid alert" id="statusText" style="display: none"></div>
        <form id="create-account-form">
          <div class="form-group row">
            <input type="text" class="form-control" id="username" aria-label="Username" placeholder="Username" aria-describedby="usernameHelpBlock" <?php if(isset($_GET['username'])) { echo 'value="'.htmlspecialchars($_GET['username']).'"'; } ?> required>
            <small id="usernameHelpBlock" class="form-text text-muted">What is the username of the reddit account you want to claim?</small>
          </div>
          <div class="form-group row">
            <input type="text" class="form-control" id="token" aria-label="Token" placeholder="Token" aria-describedby="tokenHelpBlock" <?php if(isset($_GET['claim_code'])) { echo 'value="' . htmlspecialchars($_GET['claim_code']) . '"'; } ?> required>
            <small id="tokenHelpBlock" class="form-text text-muted">The long and random string that the LoansBot sent only to you, which proves you recieved its message.</small>
          </div>
          <div class="form-group row">
            <input type="password" class="form-control" id="password-1" aria-label="Password" placeholder="Password" aria-describedby='password1HelpBlock' pattern=".{8,}$" required>
            <small id="password1HelpBlock" class="form-text text-muted">The password you will use on this website. Must be at least 8 characters long.</small>
          </div>
          <div class="form-group row">
            <input type="password" class="form-control" id="password-2" aria-label="Password" placeholder="Password" aria-describedby='password2HelpBlock' pattern=".{8,}$" required>
            <small id="password2HelpBlock" class="form-text text-muted">Repeat your password.</small>
          </div>
          <div class="form-group row">
            <input type="email" class="form-control" id="email" aria-label="Email" placeholder="Email" aria-describedby="emailHelpBlock" required>
            <small id="emailHelpBlock" class="form-text text-muted">Your email address, in case we need to get a hold of you outside reddit.</small>
          </div>
          <div class="form-group row">
            <input type="text" class="form-control" id="name" aria-label="Your full name" placeholder="Name" aria-describedby="nameHelpBlock" required>
            <small id="nameHelpBlock" class="form-text text-muted">Your full name. This will be cross-referenced against what you give to lenders/borrowers at their discretion.</small>
          </div>
          <div class="form-group row">
            <input type="text" class="form-control" id="country" aria-label="Country" placeholder="Country" aria-describedby="countryHelpBlock" required>
            <small id="countryHelpBlock" class="form-text text-muted">If you live in a major country (top 30 by GDP per capita) please use a 2-digit code (e.g. United States would be US). Otherwise use the full country name.</small>
          </div>
          <div class="form-group row">
            <input type="text" class="form-control" id="state" aria-label="State" placeholder="State" aria-describedby="stateHelpBlock" required>
            <small id="stateHelpBlock" class="form-text text-muted">If your country is divided into regions with partial or complete sovereignty, please list that region here. Otherwise, put N/A</small>
          </div>
          <div class="form-group row">
            <input type="text" class="form-control" id="city" aria-label="City" placeholder="City" aria-describedby="cityHelpBlock" required>
            <small id="stateHelpBlock" class="form-text text-muted">Your city - if ambiguous, use the smallest named area that contains housing, transportation, sanitation, land use, and communication in which you reside.</small>
          </div>
          <div class="form-group row">
            <input type="text" class="form-control" id="streetAddress" aria-label="Street address" placeholder="Street Address" aria-describedby="streetAddressHelpBlock" required>
            <small id="stateHelpBlock" class="form-text text-muted">Your street address - if ambiguous, use the smallest amount of information that a non-local could use to uniquely identify your house if they know your country, state, and city.</small>
          </div>
          <div class="form-group row">
            <input type="text" class="form-control" id="zipAddress" aria-label="ZIP" placeholder="ZIP" aria-describedby="zipHelpBlock" required>
            <small id="zipHelpBlock" class="form-text text-muted">Your zip code - If you live in the U.S. what is your 5 digit zip code (the 4 digit extension is not required but is allowed). Otherwise put N/A</small>
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

      var form = $("#create-account-form");
      form.on('submit', function(e) {
        e.preventDefault();

        var username = $("#username").val();
        var password1 = $("#password-1").val();
        var password2 = $("#password-2").val();

        if(password1 != password2) {
          statusText.fadeOut('fast', function() {
            statusText.removeClass("alert-danger").removeClass("alert-info");
            statusText.addClass("alert-danger");
            statusText.html("<span class=\"glyphicon glyphicon-remove\"></span> Password fields do not match!");
            statusText.fadeIn('fast');
          });
          return;
        }

        var token = $("#token").val();
        var email = $("#email").val();
        var name = $("#name").val();
        var country = $("#country").val();
        var state = $("#state").val();
        var city = $("#city").val();
        var streetAddress = $("#streetAddress").val();
        var zip = $("#zipAddress").val();

        var statusText = $("#statusText");
        statusText.fadeOut('fast', function() {
          statusText.removeClass("alert-danger").removeClass("alert-success");
          statusText.addClass("alert-info");
          statusText.html("<span class=\"glyphicon glyphicon-refresh glyphicon-refresh-animate\"></span> Making request...");
          statusText.fadeIn('fast');
        });
        $("#submit-button").attr('disabled', true);
        $.post("/api/create_account_2.php", { username: username, password: password1, token: token, name: name, email: email, country: country, state: state, city: city, street_address: streetAddress, zip: zip }, function(data, stat) {
          statusText.fadeOut('fast', function() {
            statusText.removeClass("alert-danger").removeClass("alert-info");
            statusText.addClass("alert-success");
            statusText.html("<span class=\"glyphicon glyphicon-ok\"></span> Success! Your account has been claimed. You will be redirected to the index page automatically in 5 seconds.");
            statusText.fadeIn('fast');

            setTimeout(function() {
              window.location.href = "<?php include('urlroot.php'); ?>";
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

