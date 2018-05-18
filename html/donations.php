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
        <h1>Donate</h1>
        <p>The RedditLoans bot requires some cash each month for the server and website fees. In addition, it comes from the same pool of money that the USLBot (Universal Scammer List, see <a href="https://universalscammerlist.com">the website</a>). If you have recieved a benefit from its services or my assistance, please contributing to the <a href="https://www.patreon.com/tjstretchalot">patreon</a> or send cash directly to mtimothy984@gmail.com for one-time donations. You won't receive much for this, except (by request) access to a few niche-utilities (which can also be unlocked by completing 5 loans as lender). And, of course, you can feel free to use this as leverage to request features, just start off a pm to me by mentioning that you donated some cash and are interested in seeing a particular request X. This won't guarantee it is built but it will move it up the bucket list pretty quick.</p>
        <p>I cover any missing cash for the servers, which typically total about $42/month. You can check the patreon to see about how much I'm covering every month.</p>
      </section>
    </div>
    <?php include('bootstrap_js.php') ?>
  </body>
</html>
<?php
  $sql_conn->close();
?>
