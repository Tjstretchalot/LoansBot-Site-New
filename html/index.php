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

        <h2>Intended Audience</h2>
        <p>Since this website mainly allows for examining statistics or per-user, anonymous queries, it tends to be
        used either for its API or by power-users. Thus, some degree of technical competence and statistical understanding
        is assumed throughout the site. This page is designed so that the raw data is clearly separated from the analysis,
        so acquiring the latest numbers can be done without re-reading excessively.</p>
        
        <p>The <a href="/statistics.php">analysis</a> that is provided was written by me - and I'm not a statistics major. These conclusions are a result 
        of intuition combined with comprehensive research. An attempt is made to source any claims that could require some
        mental gymnastics to get to. Nonetheless, this is simply my explanation of the vast amount of data that has been
        acquired through the subreddit; There may be different interpretations that suggest wildly different results - feel free
        to <a href="mailto:mtimothy984@gmail.com">tell me about it</a>!</p>

        <h2>Source of Funding</h2>
        <p>This website and the LoansBot are both funded entirely on donations by generous users. Thanks to these donations,
        the website will continue to be completely free to use and advertisement-free. If you were helped by this website
        or its bot, consider <a href="/donations.php">donating</a>.</p>
      </section>
    </div>
    <?php include('bootstrap_js.php') ?>
  </body>
</html>
<?php
  $sql_conn->close();
?>
