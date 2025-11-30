<?php
// verify.php
session_start();
include 'db.php';

$token = $_GET['token'] ?? '';
if(!$token) die('Invalid verification link.');

$tok_e = mysqli_real_escape_string($conn, $token);
$sql = "SELECT id FROM users WHERE email_verification_token = '$tok_e' LIMIT 1";
$res = mysqli_query($conn, $sql);
if(!$res || mysqli_num_rows($res) === 0){
  die('Invalid or expired token.');
}
$row = mysqli_fetch_assoc($res);
$uid = $row['id'];

// mark verified and clear token
$u = mysqli_query($conn, "UPDATE users SET email_verified = 1, email_verification_token = NULL WHERE id = $uid");
if($u){
  echo "Email verified — you may now <a href='signin.php'>sign in</a>.";
} else {
  echo "Could not verify — try again or contact support.";
}
