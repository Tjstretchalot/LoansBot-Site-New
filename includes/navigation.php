<?php
  require_once 'api/common.php';
  require_once('database/helper.php');
  $pages = array();

  $pages[] = array(
    'link' => '/index.php',
    'name' => 'Index'
  );

  $pages[] = array(
    'link' => '/statistics.php',
    'name' => 'Statistics'
  );

  $pages[] = array(
    'link' => '/query.php',
    'name' => 'Search'
  );

  $pages[] = array(
    'link' => '/query2.php',
    'name' => 'Search v2.0'
  );

  $pages[] = array(
    'link' => '/mobile_query.php',
    'name' => 'Mobile Check'
  );


  if(!isset($logged_in_user) || $logged_in_user === null) {
    $pages[] = array(
      'link' => '/login.php',
      'name' => 'Login'
    );
  }else {
    if($logged_in_user->auth >= $MODERATOR_PERMISSION) {
      $pages[] = array(
        'link' => '/responses.php',
        'name' => 'Responses'
      );

      $pages[] = array(
        'link' => '/logs.php',
        'name' => 'Logs'
      );

      $pages[] = array(
        'link' => '/red_flag_subreddits.php',
        'name' => 'Red Flag Subs'
      );

      $pages[] = array(
        'link' => '/promotion_blacklist.php',
        'name' => 'Promo Blacklist'
      )
    }

    $lender_perms = true;
    if($logged_in_user->auth < 1) {
      $rel_loans_row = DatabaseHelper::fetch_one($sql_conn, 'SELECT COUNT(*) as num_loans_as_lend FROM loans WHERE lender_id=? AND (principal_cents = principal_repayment_cents OR unpaid = 1)', array(array('i', $logged_in_user->id)));
      if($rel_loans_row->num_loans_as_lend < 5) {
        $lender_perms = false;
      }
    }

    if($lender_perms) {
      $pages[] = array(
        'link' => '/lender_stats.php',
        'name' => 'Lender Stats'
      );
      $pages[] = array(
        'link' => '/rechecks.php',
        'name' => 'Rechecks'
      );
      $pages[] = array(
        'link' => '/red_flags_by_user.php',
        'name' => 'Red Flag Reports'
      );
    }

    $pages[] = array(
      'link' => '/logout.php',
      'name' => 'Logout'
    );
  }
?>
<!-- navigation start -->

<div class="container-fluid">
  <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="#">RedditLoans</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle Navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav mr-auto">
        <?php foreach($pages as $key=>$page): ?>
        <?php if ($_SERVER['REQUEST_URI'] == $page['link'] || ($_SERVER['REQUEST_URI'] == '/' && $page['link'] == '/index.php')): ?>
          <li class="nav-item active">
            <a class="nav-link" href="#"><?= $page['name'] ?> <span class="sr-only">(current)</span></a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= $page['link'] ?>"><?= $page['name'] ?></a>
          </li>
        <?php endif; ?>
        <?php endforeach; ?>
      </ul>
    </div>
  </nav>
</div>

<!-- navigation end -->
