<?php
  $MODERATOR_PERMISSION = 5;
  function echo_fail($resp_code, $err_type, $err_mess) {
    http_response_code($resp_code);
    header('Content-Type: application/json');
    echo(json_encode(array(
      'success' => false,
      'result_type' => 'FAILURE',
      'errors' => array(
        array(
          'error_type' => $err_type,
          'error_message' => $err_mess
        )
      )
    )));
  }

  function echo_success($result_type, $data) {
    http_response_code(200);
    header('Content-Type: application/json');
    echo(json_encode(array(
      'result_type' => $result_type
      'success' => true,
    ) + $data));
  }
?>
