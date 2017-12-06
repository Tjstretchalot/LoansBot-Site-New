<?php
  include_once('connect_and_get_loggedin.php');
?>
<!doctype html>
<html lang="en">
  <head>
    <title>RedditLoans</title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <?php include('bootstrap_css.php') ?>
  </head>
  <body>
    <div class="container px-2">
      <section>
        <h1>What is this?</h1>
        <p>This site was made to accompany the <a href="https://github.com/Tjstretchalot/LoansBot">friendly bot</a>
        over at <a href="http://reddit.com/r/borrow">/r/Borrow</a>, which facilitates the temporary transfer of money 
        from those in a stable position to those experiencing a sudden crisis of liquidity. The LoansBot does <i>not</i> 
        cost any money to use, nor does it in any way profit off loans between users. Furthermore, lenders are encouraged 
        to only require interest to the extent that they can recoup losses so they can continue lending, rather than as a 
        form of profit.</p>
        <p>The website provides a way to interact with its database, both through insightful visualizations, a comprehensive query 
        interface, and a professional application protocol interface (API), which allows users to predict malicious
        loans, keep track of trends on the subreddit, and evaluate the history of other users on the subreddit. One example 
        of this is the unpaid percentage of loans, which can be used as a basis for an appropriate interest rate on an average
        loan.</p>
      </section>
    </div>
    <?php include('bootstrap_js.php') ?>
  </body>
</html>
<?php
  $sql_conn->close();
?>
