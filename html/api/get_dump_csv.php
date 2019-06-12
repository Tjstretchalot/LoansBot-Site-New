<?php
/*
 * Can be used to download the database in csv-format. This isn't the most efficient way to
 * access the database, but it's helpful for quickly importing the data into other tools.
 */

require_once 'api/common.php';

if($_SERVER['REQUEST_METHOD'] == 'GET') {
    require_once 'api/auth.php';
    require_once 'database/helper.php';

    if(!is_logged_in()) {
        echo_fail(403, 'FORBIDDEN', 'You must be logged in to use this endpoint');
        $sql_conn->close();
        return;
    }

    $query = <<<'SQL'
SELECT loans.id as id,
       loans.lender_id as lender_id,
       loans.borrower_id as borrower_id,
       loans.principal_cents as principal_cents,
       loans.principal_repayment_cents as principal_repayment_cents,
       loans.created_at as created_at,
       loans.updated_at as updated_at
FROM loans
WHERE loans.deleted = 0
SQL
;
    $err_prefix = 'get_dump_csv.php - ' . $query;

    check_db_error($sql_conn, $err_prefix, $stmt = $sql_conn->prepare($query));
    QueryHelper::bind_params($sql_conn, $stmt, $params);
    check_db_error($sql_conn, $err_prefix, $stmt->execute());
    check_db_error($sql_conn, $err_prefix, $res = $stmt->get_result());

    echo('id,lender_id,borrower_id,principal_cents,principal_repayment_cents,created_at,updated_at');
    echo(PHP_EOL);
    while(($row = $res->fetch_row()) !== null) {
        for($i = 0; $i < 7; $i++) {
            if($i !== 0) { echo ','; }
            echo strval($row[$i]);
        }
        echo PHP_EOL;
    }
    $res->close();
    $stmt->close();
    $sql_conn->close();
}else {
  echo_fail(405, 'METHOD_NOT_ALLOWED', 'You must use a GET request at this endpoint');
}