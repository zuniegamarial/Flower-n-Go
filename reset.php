<?php
session_start();
include "db.php";
$msg = "";

$token = $_GET['token'] ?? '';

if(!$token){ die("Invalid link"); }

$q = mysqli_prepare($conn,"SELECT pr.id, pr.user_id, pr.expires_at, u.email FROM password_resets pr JOIN users u ON pr.user_id=u.id WHERE pr.token = ?");
mysqli_stmt_bind_param($q,"s",$token);
mysqli_stmt_execute($q);
$res = mysqli_stmt_get_result($q);

if(!$res || mysqli_num_rows($res) != 1){
  die("Invalid or expired token.");
}

$row = mysqli_fetch_assoc($res);
if(strtotime($row['expires_at']) < time()){
  die("Token expired. Request a new reset link.");
}

if(isset($_POST['reset'])){
  $pw = $_POST['password'];
  $pw2 = $_POST['password2'];
  if($pw !== $pw2){ $msg = "Passwords do not match."; }
  elseif(strlen($pw) < 6){ $msg = "Password must be at least 6 characters."; }
  else {
    $hash = password_hash($pw, PASSWORD_DEFAULT);
    $upd = mysqli_prepare($conn,"UPDATE users SET password = ? WHERE id = ?");
    mysqli_stmt_bind_param($upd,"si",$hash,$row['user_id']);
    mysqli_stmt_execute($upd);

    // remove used token
    $del = mysqli_prepare($conn,"DELETE FROM password_resets WHERE id = ?");
    mysqli_stmt_bind_param($del,"i",$row['id']);
    mysqli_stmt_execute($del);

    echo "<script>alert('Password updated. You may now login.'); window.location='signin.php';</script>";
    exit;
  }
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Reset Password</title>
<link rel="stylesheet" href="style.css"></head><body>
<nav class="navbar"><div class="logo">🌸 <span class="logo-text">Flower 'n GO</span></div></nav>

<div class="center-page">
  <div class="form-card">
    <h2>Create new password</h2>
    <?php if($msg): ?><div class="error"><?=htmlspecialchars($msg)?></div><?php endif; ?>

    <form method="POST">
      <div class="field">
        <svg class="icon" width="16" height="16" viewBox="0 0 24 24"><path d="M8 10V7a4 4 0 118 0v3" stroke="#666" fill="none"/></svg>
        <input id="p1" type="password" name="password" placeholder="New password" required>
        <button type="button" class="pw-toggle" onclick="tog('p1')">👁️</button>
      </div>

      <div class="field">
        <svg class="icon" width="16" height="16" viewBox="0 0 24 24"><path d="M8 10V7a4 4 0 118 0v3" stroke="#666" fill="none"/></svg>
        <input id="p2" type="password" name="password2" placeholder="Confirm password" required>
        <button type="button" class="pw-toggle" onclick="tog('p2')">👁️</button>
      </div>

      <button class="btn-primary" name="reset">Set password</button>
    </form>

  </div>
</div>

<script>
function tog(id){ const el=document.getElementById(id); el.type = el.type==='password' ? 'text' : 'password'; }
</script>

</body></html>
