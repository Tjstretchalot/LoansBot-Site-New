
<?php
  include_once('api/auth.php');

  if (!is_trusted()) {
    on_failed_auth();
    return;
  }

  require_once('database/helper');

  $spots = DatabaseHelper::fetch_all($sql_conn, 'SELECT * FROM red_flag_queue_spots WHERE completed_at IS NULL ORDER BY created_at ASC', array());
?>
<!doctype html>
<html lang="en">
  <head>
    <title>RedditLoans</title>
    <?php include('metatags.php'); ?>

    <?php include('bootstrap_css.php'); ?>
    <link rel="stylesheet" href="/css/basictable.css">
  </head>
  <body>
    <?php include('navigation.php'); ?>
    <div class="container px-2 py-5">
      <section>
      <h1>Red Flag Report Queue</h1>
      <p>This page shows the current queue for the loansbot for red flag reports. Refresh the page to refresh the report.</p>

      <table id="queue_table">
        <thead>
          <tr>
            <th>Username</th>
            <th>Created At</th>
            <th>Started?</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($spots as $spot): ?>
          <tr>
            <td data-th="Username"><?= DatabaseHelper::fetch_one($sql_conn, 'SELECT username FROM usernames WHERE id=?', array(array('i', $spot->username_id)))->username ?></td>
            <td data-th="Created At" class="pretty-time"><?= $spot->created_at ?></td>
            <td data-th="Started?"><?= ($spot->started_at !== null ? 'Yes' : 'No') ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      </section>
    </div>
    <?php include('bootstrap_js.php') ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.21.0/moment-with-locales.min.js"></script>
    <script src="js/jquery.basictable.min.js"></script>
    <script src="js/status_text_utils.js"></script>
    <script type="text/javascript">
      $(function () {
        $('[data-toggle="tooltip"]').tooltip();
        
        $('.pretty-time').each(function() {
          var me = $(this);

          me.text(moment(parseInt(me.text())).format('LLLL'));
        });

        $('#queue_table').basictable({
          tableWrap: true,
          breakpoint: 991
        });
      });
    </script>
  </body>
</html>
<?php
  $sql_conn->close();
?>
