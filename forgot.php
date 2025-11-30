<?php
// forgot.php (top)
session_start();
include "db.php";
$config = include __DIR__ . '/config.php';
$msg = "";

if(isset($_POST['send'])){
  $email = trim($_POST['email']);
  $q = mysqli_prepare($conn,"SELECT id,name FROM users WHERE email=?");
  mysqli_stmt_bind_param($q,"s",$email);
  mysqli_stmt_execute($q);
  $res = mysqli_stmt_get_result($q);

  if($res && mysqli_num_rows($res) == 1){
    $row = mysqli_fetch_assoc($res);
    $user_id = $row['id'];
    $token = bin2hex(random_bytes(16));
    $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour

    $ins = mysqli_prepare($conn,"INSERT INTO password_resets (user_id, token, expires_at) VALUES (?,?,?)");
    mysqli_stmt_bind_param($ins,"iss",$user_id,$token,$expires);
    mysqli_stmt_execute($ins);

    // Build reset link
    $resetLink = $config['base_url'] . "/reset.php?token=" . $token;

    // Send email with PHPMailer
    require __DIR__ . '/vendor/autoload.php'; // composer autoload
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    $mail = new PHPMailer(true);
    try {
      // SMTP config
      $mail->isSMTP();
      $mail->Host       = $config['smtp']['host'];
      $mail->SMTPAuth   = true;
      $mail->Username   = $config['smtp']['username'];
      $mail->Password   = $config['smtp']['password'];
      $mail->SMTPSecure = $config['smtp']['secure'];
      $mail->Port       = $config['smtp']['port'];

      $mail->setFrom($config['smtp']['from_email'], $config['smtp']['from_name']);
      $mail->addAddress($email, $row['name']);

      $mail->isHTML(true);
      $mail->Subject = 'Password reset for Flower n GO';
      $mail->Body    = "
        <p>Hello " . htmlspecialchars($row['name']) . ",</p>
        <p>We received a request to reset your password. Click the link below to set a new password (link expires in 1 hour):</p>
        <p><a href='$resetLink'>$resetLink</a></p>
        <p>If you did not request this, ignore this email.</p>
      ";

      $mail->send();
      $msg = "We sent a reset email. Please check your inbox.";
    } catch (Exception $e) {
      // fallback: show link on-screen for local dev
      $msg = "Could not send email — here is the reset link (local dev): <a href='$resetLink'>$resetLink</a>";
    }
  } else {
    $msg = "No account found with that email.";
  }
}
