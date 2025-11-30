<?php
// signup.php - full working file
session_start();
include "db.php"; // make sure this points to your database and is correct

$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    // Collect & sanitize
    $fname = trim($_POST['fname'] ?? '');
    $lname = trim($_POST['lname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password_raw = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $postal = trim($_POST['postal'] ?? '');
    $country = trim($_POST['country'] ?? 'USA');

    // Basic validation
    if ($fname === '') $errors[] = "First name is required.";
    if ($lname === '') $errors[] = "Last name is required.";
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if ($password_raw === '' || strlen($password_raw) < 6) $errors[] = "Password is required (minimum 6 characters).";

    if (empty($errors)) {
        // Check duplicate
        $checkSql = "SELECT id FROM users WHERE email = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $checkSql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = "An account with that email already exists.";
            mysqli_stmt_close($stmt);
        } else {
            mysqli_stmt_close($stmt);

            // Insert user (prepared statement)
            $name = $fname . ' ' . $lname;
            $password_hash = password_hash($password_raw, PASSWORD_DEFAULT);

            $insertSql = "INSERT INTO users (name, email, password, phone, address, city, postal_code, country, created_at) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $ins = mysqli_prepare($conn, $insertSql);
            mysqli_stmt_bind_param($ins, "ssssssss", $name, $email, $password_hash, $phone, $address, $city, $postal, $country);

            if (mysqli_stmt_execute($ins)) {
                $newId = mysqli_insert_id($conn);
                mysqli_stmt_close($ins);

                // Successful signup: set session and redirect
                $_SESSION['user_id'] = $newId;
                $_SESSION['user'] = $name;

                // Optionally set a success message and redirect
                header("Location: dashboard.php");
                exit();
            } else {
                $errors[] = "Database error: could not create account. Please try again.";
                mysqli_stmt_close($ins);
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Sign Up — Flower 'n GO</title>
<style>
/* Simple, self-contained styling (matches the sign-in look) */
* { box-sizing: border-box; font-family: "Segoe UI", Arial, sans-serif; margin:0; padding:0; }
body { min-height:100vh; background: radial-gradient(circle at top left,#5c1f0f,#1a0d08); display:flex; align-items:stretch; }
.left { width:40%; display:flex; align-items:center; justify-content:center; }
.brand { background:#000; color:#D4AF37; padding:24px 40px; font-size:26px; border-radius:8px; box-shadow:0 8px 30px rgba(0,0,0,.6); font-weight:700; }
.right { width:60%; background:#fff; padding:56px 64px; overflow:auto; }
h1 { font-size:28px; margin-bottom:6px; color:#222; text-align:center; }
.subtitle { color:#666; text-align:center; margin-bottom:22px; }
.form-wrap { max-width:700px; margin:0 auto; }
form { display:block; }
.row { display:flex; gap:12px; }
.col { flex:1; }
.field { margin-bottom:14px; }
label { font-size:12px; color:#777; display:block; margin-bottom:6px; }
input[type="text"], input[type="email"], input[type="password"] { width:100%; padding:12px 14px; border-radius:8px; border: none; background:#f2f2f2; outline:none; font-size:14px; }
button[type="submit"] { width:100%; padding:12px 14px; border-radius:10px; border:none; background:#D4AF37; color:#000; font-weight:700; cursor:pointer; margin-top:6px; }
button[type="submit"]:hover{ opacity:.95; }

/* messages */
.msg { padding:12px; border-radius:8px; margin-bottom:14px; font-size:14px; }
.error { background:#ffe6e6; color:#b01919; }
.success { background:#e7ffd9; color:#0a7a0a; }

/* small footer */
.help { text-align:center; margin-top:12px; font-size:14px; }
.help a { color:#5b2b12; text-decoration:none; font-weight:600; }

/* responsive */
@media (max-width:900px) {
  .left { display:none; }
  .right { width:100%; padding:28px; }
  .form-wrap { width:100%; }
  .row { flex-direction:column; }
}
</style>
</head>
<body>

<div class="left">
  <div class="brand">🌸 Flower 'n GO</div>
</div>

<div class="right">
  <div class="form-wrap">
    <h1>Sign Up</h1>
    <p class="subtitle">Let's start with some facts about you</p>

    <?php if (!empty($errors)): ?>
      <div class="msg error">
        <?php echo htmlspecialchars(array_shift($errors)); // show first error safely ?>
      </div>
    <?php endif; ?>

    <form method="POST" novalidate>
      <div class="row">
        <div class="col field">
          <label for="fname">FIRST NAME</label>
          <input id="fname" name="fname" type="text" required value="<?php echo isset($fname) ? htmlspecialchars($fname) : '' ?>">
        </div>
        <div class="col field">
          <label for="lname">LAST NAME</label>
          <input id="lname" name="lname" type="text" required value="<?php echo isset($lname) ? htmlspecialchars($lname) : '' ?>">
        </div>
      </div>

      <div class="field">
        <label for="email">EMAIL</label>
        <input id="email" name="email" type="email" required value="<?php echo isset($email) ? htmlspecialchars($email) : '' ?>">
      </div>

      <div class="field">
        <label for="password">PASSWORD</label>
        <input id="password" name="password" type="password" required>
      </div>

      <div class="field">
        <label for="phone">PHONE</label>
        <input id="phone" name="phone" type="text" value="<?php echo isset($phone) ? htmlspecialchars($phone) : '' ?>">
      </div>

      <div class="field">
        <label for="address">ADDRESS</label>
        <input id="address" name="address" type="text" value="<?php echo isset($address) ? htmlspecialchars($address) : '' ?>">
      </div>

      <div class="row">
        <div class="col field">
          <label for="city">CITY</label>
          <input id="city" name="city" type="text" value="<?php echo isset($city) ? htmlspecialchars($city) : '' ?>">
        </div>
        <div class="col field">
          <label for="postal">POSTAL CODE</label>
          <input id="postal" name="postal" type="text" value="<?php echo isset($postal) ? htmlspecialchars($postal) : '' ?>">
        </div>
      </div>

      <div class="field">
        <label for="country">COUNTRY</label>
        <input id="country" name="country" type="text" value="<?php echo isset($country) ? htmlspecialchars($country) : 'USA' ?>">
      </div>

      <button type="submit" name="signup">Sign Up</button>
    </form>

    <div class="help">
      Already have an account? <a href="signin.php">Sign in</a>
    </div>
  </div>
</div>

</body>
</html>
