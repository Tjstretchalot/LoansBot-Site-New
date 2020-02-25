<?php
require_once 'api/common.php';
require_once 'database/common.php';
require_once 'database/users.php';
require_once 'database/site_sessions.php';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
  /* DEFAULT ARGUMENTS */
  $username = null;
  $token = null;
  $password = null;
  $email = null;
  $name = null;
  $street_address = null;
  $city = null;
  $state = null;
  $zip = null;
  $country = null;

  /* PARSING ARGUMENTS */
  if(isset($_POST['username'])) {
    $username = $_POST['username'];
  }

  if(isset($_POST['token'])) {
    $token = $_POST['token'];
  }

  if(isset($_POST['password'])) {
    $password = $_POST['password'];
  }

  if(isset($_POST['email'])) {
    $email = $_POST['email'];
  }

  if(isset($_POST['name'])) {
    $name = $_POST['name'];
  }

  if(isset($_POST['street_address'])) {
    $street_address = $_POST['street_address'];
  }

  if(isset($_POST['city'])) {
    $city = $_POST['city'];
  }

  if(isset($_POST['state'])) {
    $state = $_POST['state'];
  }

  if(isset($_POST['zip'])) {
    $zip = $_POST['zip'];
  }

  if(isset($_POST['country'])) {
    $country = $_POST['country'];
  }

  /* VALIDATING ARGUMENTS */
  if($username === null) {
    echo_fail(400, 'ARGUMENT_MISSING', 'Username cannot be empty!');
    return;
  }

  if($token === null) {
    echo_fail(400, 'ARGUMENT_MISSING', 'Token cannot be empty!');
    return;
  }

  if($password === null) {
    echo_fail(400, 'ARGUMENT_MISSING', 'Password cannot be empty!');
    return;
  }

  if(strlen($password) < 8) {
    echo_fail(400, 'ARGUMENT_INVALID', 'Password must be at least 8 characters!');
    return;
  }

  if(strlen($password) > 255) {
    echo_fail(400, 'ARGUMENT_INVALID', 'Password may not exceed 255 characters!');
    return;
  }


  if($email === null) {
    echo_fail(400, 'ARGUMENT_MISSING', 'Email cannot be empty!');
    return;
  }

  if($name === null) {
    echo_fail(400, 'ARGUMENT_MISSING', 'Name cannot be empty!');
    return;
  }

  if($street_address === null) {
    echo_fail(400, 'ARGUMENT_MISSING', 'Street address cannot be empty!');
    return;
  }

  if($city === null) {
    echo_fail(400, 'ARGUMENT_MISSING', 'City cannot be empty!');
    return;
  }

  if($state === null) {
    echo_fail(400, 'ARGUMENT_MISSING', 'State cannot be empty!');
    return;
  }

  if($zip === null) {
    echo_fail(400, 'ARGUMENT_MISSING', 'ZIP cannot be empty!');
    return;
  }

  if($country === null) {
    echo_fail(400, 'ARGUMENT_MISSING', 'Country cannot be empty!');
    return;
  }

  /* PERFORMING REQUEST */
  $conn = create_db_connection();
  $user = UserMapping::fetch_by_username($conn, $username);

  $bad_token_iden = 'BAD_TOKEN';
  $bad_token_message = 'That username/token combination does not match our records.';

  if($user === null) {
    echo_fail(400, $bad_token_iden, $bad_token_message . ' (Error Code 001)');
    $conn->close();
    return;
  }

  if($user->claimed !== 0) {
    echo_fail(400, $bad_token_iden, $bad_token_message . ' (Error Code 002)');
    $conn->close();
    return;
  }

  if($user->claim_link_sent_at === null) {
    echo_fail(400, $bad_token_iden, $bad_token_message . ' (Error Code 003)');
    $conn->close();
    return;
  }

  if($user->claim_code !== $token) {
    echo_fail(400, $bad_token_iden, $bad_token_message . ' (Error Code 004)');
    $conn->close();
    return;
  }

  $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
  UserMapping::update_user_after_claimed($conn, $user, $email, $name, $street_address, $city, $state, $zip, $country);
  UserMapping::update_password_by_id($conn, $user->id, $hashed_pass);
  echo_success('CLAIM_ACCOUNT_2_SUCCESS', array());
  $conn->close();
}else {
  echo_fail(405, 'METHOD_NOT_ALLOWED', 'You must use a POST request at this endpoint');
}
?>
