<?php
  include_once 'connect_and_get_loggedin.php';
  require_once 'database/helper.php';

  if(($logged_in_user === null) || ($logged_in_user->auth < 5)) {
    http_response_code(403);
    $sql_conn->close();
    return;
  }
  
  $id = null;
  if(isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);
  }
  
  if($id === null || $id < 1) {
    http_response_code(400);
    $sql_conn->close();
    return;
  }
  
  $response = DatabaseHelper::fetch_one($sql_conn, 'SELECT * FROM responses WHERE id=?', array(array('i', $id)));

  if($response === null) {
    http_response_code(404);
    $sql_conn->close();
    return;
  }

  $history = DatabaseHelper::fetch_all($sql_conn, 'SELECT rh.response_id as response_id, rh.old_raw as old_raw, rh.new_raw as new_raw, rh.reason as reason, rh.created_at as created_at, rh.updated_at as updated_at, un.username AS username FROM response_histories rh INNER JOIN usernames un ON rh.user_id = un.user_id WHERE rh.response_id=? ORDER BY rh.created_at DESC LIMIT 10', array(array('i', $id)));
?>
<html>
  <head>
    <title>RedditLoans - Responses</title>
    <?php include('metatags.php'); ?>

    <?php include('bootstrap_css.php'); ?>
    <link rel="stylesheet" type="text/css" href="css/blockquote.css"
  </head>
  <body>
    <?php include('navigation.php'); ?>
    <div class="container px-2 py-5">
      <section>
        <h1>Response</h1>
        <h2><?= $response->name ?></h2>
        <form id="edit-form">
          <div class="form-group">
            <textarea class="form-control" id="new-response-body" style="font-family: monospace, monospace;" rows="15" pattern=".{5,}" required><?= htmlspecialchars($response->response_body) ?></textarea>
          </div>
          <div class="form-group">
            <textarea class="form-control" id="edit-reason" placeholder="Reason for edit" rows="3" pattern=".{5,}" required></textarea>
            <small class="form-text text-muted">Must be at least 5 characters long - be as descriptive as possible without repeating yourself</small>
          </div>
          <button type="submit" id="submit-button" class="btn btn-primary">Update</button>
        </form>
        <small class="text-muted">Last updated <?= $response->updated_at ?></small>
      </section>
      <section>
        <h1>History</h1>
        <?php foreach($history as $hist): ?>
        <blockquote class="lavander">
          <h1><span class="Clavander"><?= $hist->username ?></span> - <?= $hist->created_at ?></h1>
          <p><?= $hist->reason ?></p>
        </blockquote>

        <h2>Prior to edit by <?= $hist->username ?>:</h2>
        <form>
          <div class="form-group">
            <textarea class="form-control" rows="15" readonly><?= $hist->old_raw ?></textarea>
          </div>
        </form>
        <hr />
        <?php endforeach ?>
      </section>
    </div>
    <?php include('bootstrap_js.php') ?>
    <script type="text/javascript">
      $(function () {
        $('[data-toggle="tooltip"]').tooltip();
      });
      
      $("#edit-form").on("submit", function(e) {
        e.preventDefault();

        var new_body = $("#new-response-body").val();
        var reason = $("#edit-reason").val();

        var statusText = $("#statusText");
        statusText.fadeOut('fast', function() {
          statusText.removeClass("alert-danger").removeClass("alert-success");
          statusText.addClass("alert-info");
          statusText.html("<span class=\"glyphicon glyphicon-refresh glyphicon-refresh-animate\"></span> Updating...");
          statusText.fadeIn('fast');
        });
        $("#submit-button").attr('disabled', true);
        $.post("https://redditloans.com/api/edit_response.php", { id: <?= $id ?>, body: new_body, reason: reason }, function(data, stat) {
          location.reload();
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
    </script>
  </body> 
</html>
<?php
  $sql_conn->close();
?>
