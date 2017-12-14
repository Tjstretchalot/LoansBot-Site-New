<?php
  include_once 'connect_and_get_loggedin.php';
  require_once 'database/helper.php';

  if(($logged_in_user === null) || ($logged_in_user->auth < 5)) {
    http_response_code(403);
    $sql_conn->close();
    return;
  }
  
  $response_names = DatabaseHelper::fetch_all($sql_conn, 'SELECT id, name FROM responses ORDER BY name ASC', array());
?>
<html>
  <head>
    <title>RedditLoans - Responses</title>
    <?php include('metatags.php'); ?>

    <?php include('bootstrap_css.php'); ?>
  </head>
  <body>
    <?php include('navigation.php'); ?>
    <div class="container px-2 py-5">
      <section>
        <h1>What is this</h1>
        <p>This is a moderator-only interface to allow you to change the responses that the LoansBot makes. Each
        response is formed from the large definition string, which is then processed and portions that
        are inside angle brackets (&lt; and &gt;) is matched with something relevant to the response.</p>

        <p>Be careful, putting incorrect values in the responses might cause the LoansBot to crash, since
        this section is not user-facing it does not have as robust error checking. If you are unsure, 
        feel free to contact /u/Tjstretchalot for verification. The worst thing that happens is your
        change needs to be reverted (the old version is backed up for you when you make a change) and the LoansBot
        has to be restarted.</p>

        <p>Below is a list of all the names of the responses. Click on any to view additional information
        or to edit.</p>
      </section>
      <section>
        <h2>Index</h2>
        <ul>
        <?php foreach($response_names as $resp): ?>
          <li><a href="response.php?id=<?= $resp->id ?>"><?= $resp->name ?></a></li>
        <?php endforeach; ?>
        </ul>
      </section>
    </div>
    <?php include('bootstrap_js.php') ?>
    <script type="text/javascript">
      $(function () {
        $('[data-toggle="tooltip"]').tooltip();
      });
    </script>
  </body>
  
</html>
<?php
  $sql_conn->close();
?>
