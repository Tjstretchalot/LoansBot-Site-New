<?php
  include('api/common.php');
  include('database/common.php');

  $suppressDefaultSheets = true;
  $cssSheets = array();
  $cssSheets[] = 'css/site.css';
  $cssSheets[] = 'css/mobile_query.css';
  $cssSheets[] = 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css';
  $cssSheets[] = 'https://fonts.googleapis.com/css?family=Open+Sans:400,300,400italic,700,700italic';
  $cssSheets[] = 'css/footable.bootstrap.min.css';

  $pageTitle = 'RedditLoans - Mobile Query';
  
  $checkid = NULL;
  if(isset($_GET["checkid"]) && is_numeric($_GET["checkid"])) {
    $_checkid = intval($_GET["checkid"]);

    if ($_checkid > 0) {
      $checkid = $_checkid;
    }
  }

  $checkname = NULL;
  if(isset($_GET["checkname"])) {
    $checkname = $_GET["checkname"];
  }

  $limit = 25;
  if(isset($_GET["limit"]) && is_numeric($_GET["limit"])) {
    $_limit = intval($_GET["limit"]);

    if ($_limit > 0 && $_limit < 100) {
      $limit = $_limit;
    }
  }

  $offset = 0;
  if(isset($_GET["offset"]) && is_numeric($_GET["offset"])) {
    $_offset = intval($_GET["offset"]);

    if ($_offset > 0 && $_offset < 10000) {
      $offset = $_offset;
    }
  }

  $outstanding = false;
  if(isset($_GET["outstanding"]) && is_numeric($_GET["outstanding"])) {
    $outstanding = intval($_GET["outstanding"]) === 1;
  }

  $sql_conn = create_db_connection();
  if($checkname && !$checkid) {
    $query = 'select user_id from usernames where username=? limit 1';
    $stmt = $sql_conn->prepare($query);
    $stmt->bind_param('s', $checkname);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($checkname_userid);
    if(!$stmt->fetch()) {
      $stmt->close();
      $sql_conn->close();
    }else {
      $checkid = $checkname_userid;
      $stmt->close();
    }
  }
  
  $checkUsername = NULL;
  $checkLoans = NULL;
  if($checkid) { 
    $query = 'select username from usernames where user_id=? limit 1';
    $stmt = $sql_conn->prepare($query);
    $stmt->bind_param('i', $checkid);

    $stmt->execute();    
    $stmt->store_result();
    $stmt->bind_result($username);
    if(!$stmt->fetch()) {
      $checkid = NULL;
      $stmt->close();
      $sql_conn->close();
    }else {
      $checkUsername = $username;
      $stmt->close();

      $query = 'SELECT loans.id, loans.lender_id, lendusers.username, loans.borrower_id, borrusers.username, loans.principal_cents, loans.principal_repayment_cents, loans.unpaid, loans.created_at, repayments_sub.created_at FROM loans ';
      $query .= 'LEFT OUTER JOIN (';
      $query .= 'SELECT loan_id, MAX(created_at) AS created_at FROM repayments GROUP BY loan_id';
      $query .= ') AS repayments_sub ON loans.id = repayments_sub.loan_id ';
      $query .= 'INNER JOIN (';
      $query .= 'SELECT user_id, GROUP_CONCAT(username SEPARATOR \' aka \') AS username FROM usernames GROUP BY user_id';
      $query .= ') AS lendusers ON loans.lender_id=lendusers.user_id ';
      $query .= 'INNER JOIN (';
      $query .= 'SELECT user_id, GROUP_CONCAT(username SEPARATOR \' aka \') AS username FROM usernames GROUP BY user_id';
      $query .= ') AS borrusers ON loans.borrower_id=borrusers.user_id ';
      //$query .= 'INNER JOIN (';
      //$query .= 'SELECT loan_id, GROUP_CONCAT(thread SEPARATOR \' \') AS thrd FROM creation_infos GROUP BY loan_id';
      //$query .= ') AS creatinfo ON loans.id = creatinfo.loan_id ';
      $query .= 'WHERE (loans.borrower_id=? OR loans.lender_id=?) AND loans.deleted=0 ';
      if($outstanding) {
        $query .= ' AND (loans.unpaid != 1 AND loans.principal_cents != loans.principal_repayment_cents) ';
      }
      $query .= 'ORDER BY loans.created_at ASC LIMIT ? OFFSET ?';
      
      $stmt = $sql_conn->prepare($query);
      $stmt->bind_param('iiii', $checkid, $checkid, $limit, $offset);
      $stmt->execute();

      $checkLoans = array();

      $stmt->store_result();
      $stmt->bind_result($loans_id, $loans_lender_id, $lendusers_username, $loans_borrower_id, $borrusers_username, $loans_principal_cents, $loans_principal_repayment_cents, $loans_unpaid, $loans_created_at, $repayments_sub_created_at);

      while($stmt->fetch()) {
        $checkLoans[] = array(
          "id" => $loans_id,
          "lender_id" => $loans_lender_id,
          "lender_username" => $lendusers_username,
          "borrower_id" => $loans_borrower_id,
          "borrower_username" => $borrusers_username,
          "principal_cents" => $loans_principal_cents,
          "principal_repayment_cents" => $loans_principal_repayment_cents,
          "unpaid" => $loans_unpaid,
          "created_at" => $loans_created_at,
          "last_repaid_at" => $repayments_sub_created_at
//          "thread" => $thread
        );
      }

      $stmt->close();
      $sql_conn->close();
    }
  }else {
    $sql_conn->close();
  }
?>
<!doctype html>
<html lang="en">
  <head>
    <title>RedditLoans</title>
    <?php include('metatags.php'); ?>

    <link rel="stylesheet" href="css/mobile_query.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:400,300,400italic,700,700italic">
    <link rel="stylesheet" href="css/footable.bootstrap.min.css">
  </head>
  <body>
    <div class="mobile-query-outer">
      <h1>Mobile Query <small><a href="index.php">Main Site</a></small></h1>
      <form method="get">
        <div class="form-group">
          <input type="text" name="checkname" id="checkname" class="form-control" placeholder="Search for someone..." value="<?php if($checkUsername) { echo $checkUsername; }else if($checkname) { echo $checkname; }else { echo ""; } ?>">
        </div>
      </form>
      <?php if ($checkid): ?>
      <table class="table">
        <thead>
          <tr>
            <th data-breakpoints="all">ID</th>
            <th data-breakpoints="all">Lender ID</th>
            <th>Lender</th>
            <th data-breakpoints="all">Borrower ID</th>
            <th>Borrower</th>
            <th>Princ.</th>
            <th data-breakpoints="xs">Repay.</th>
            <th data-breakpoints="all">Unpaid</th>
            <th data-breakpoints="all">Created At</th>
            <th data-breakpoints="all">Repaid At</th>
            <th data-breakpoints="all">Exact Princ.</th>
            <th data-breakpoints="all">Exact Repay.</th>
            <th data-breakpoints="all">REQ Thread</th>
            <th data-breakpoints="all">Check Lender</th>
            <th data-breakpoints="all">Check Borrower</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($checkLoans as $index=>$loan): ?>
          <tr>
            <td><?php echo $loan["id"] ?></td>
            <td><?php echo $loan["lender_id"] ?></td>
            <td><?php echo $loan["lender_username"] ?></td>
            <td><?php echo $loan["borrower_id"] ?></td>
            <td><?php echo $loan["borrower_username"] ?></td>
            <td>$<?php echo floor($loan["principal_cents"] / 100) ?></td>
            <td>$<?php echo floor($loan["principal_repayment_cents"] / 100) ?></td>
            <td><?php if($loan["unpaid"]) { echo "Yes"; }else { echo "No"; } ?></td>
            <td><?php echo $loan["created_at"] ?></td>
            <td><?php echo $loan["last_repaid_at"] ?></td>
            <td>$<?php echo number_format($loan["principal_cents"] / 100.0, 2) ?></td>
            <td>$<?php echo number_format($loan["principal_repayment_cents"] / 100.0, 2) ?></td>
            <td><a class="fetch-link" href="#">Fetch</a></td>
            <td><a href="?checkid=<?php echo $loan["lender_id"] ?>">Link</a></td>
            <td><a href="?checkid=<?php echo $loan["borrower_id"] ?>">Link</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <ul class="pagination pagination-lg">
       <li class="page-item<?php if ($offset == 0) { echo " disabled"; }?>"><a class="page-link" href="<?php if($offset == 0) { echo "#"; }else { echo "mobile_query.php?checkid="; echo $checkid; echo "&limit="; echo $limit; echo "&offset="; echo ($offset - $limit); } ?><?php if($outstanding) { echo "&outstanding=1"; } ?>">Previous Page</a></li>
       <li class="page-item"><a class="page-link" href="mobile_query.php?checkid=<?php echo $checkid; ?>&limit=<?php echo $limit ?>&offset=<?php echo ($offset + $limit) ?><?php if($outstanding) { echo "&outstanding=1"; } ?>">Next Page</a></li>
      </ul>
      <?php else: ?>
      <h2>User not found</h2>
      <?php endif; ?>

    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <script src="js/moment.js"></script>
    <script src="js/footable.min.js"></script> 
    <script type="text/javascript">
    jQuery(function($){
      $('.table').footable();
    });

    $(document).ready(function() {
      $(".fetch-link").click(function(e) {
        e.preventDefault();
        // forgive me for I have sinned
        var loanID = parseInt($($(this).parent().parent().parent()[0]).children().eq(0).children().eq(1).text());
        var me = $(this);
        var request_link = $.get("https://redditloans.com/api/get_request_thread.php", { loan_id: loanID }, function(data, stat) {
          me.attr('href', data['request_thread']);
          me.text('Link');
          me.off('click');
        }).fail(function($xhr) {
          console.log($xhr.responseJSON);
        });
      });
    });
    </script>
  </body>
</html>
<?php
  $sql_conn->close();
?>
