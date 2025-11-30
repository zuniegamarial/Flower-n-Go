<?php
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: signin.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Flower 'n GO | Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Segoe UI,sans-serif}

/* BODY */
body{
  background:#0d0a08;
  color:white;
}

/* HEADER */
.header{
  background:#121212;
  padding:15px 40px;
  display:flex;
  justify-content:space-between;
  align-items:center;
  position:sticky;
  top:0;
  z-index:1000;
}

.logo{
  font-size:20px;
  color:#D4AF37;
}

.nav a{
  color:white;
  margin-left:20px;
  text-decoration:none;
}
.nav a:hover{color:#D4AF37}

.logout{
  background:#D4AF37;
  color:black;
  padding:6px 14px;
  border-radius:16px;
}

/* HERO */
.hero{
  padding:60px 80px;
  text-align:center;
}
.hero h1{
  font-size:38px;
  color:#D4AF37;
}
.hero p{
  color:#ccc;
  margin-top:10px;
}

/* ITEMS */
.items{
  padding:60px 80px;
}

.items h2{
  font-size:28px;
  margin-bottom:25px;
  border-left:4px solid #D4AF37;
  padding-left:12px;
}

.grid{
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
  gap:25px;
}

.card{
  background:#1c120d;
  border-radius:16px;
  overflow:hidden;
  box-shadow:0 10px 25px black;
  transition:.3s;
}

.card:hover{
  transform:translateY(-8px);
  box-shadow:0 12px 35px rgba(212,175,55,.4);
}

.card img{
  width:100%;
  height:200px;
  object-fit:cover;
}

.card-content{
  padding:15px;
}

.card-content h3{color:white}
.card-content p{
  color:#aaa;
  margin:6px 0;
  font-size:13px;
}

.price{
  color:#D4AF37;
  font-weight:bold;
  margin:6px 0;
}

.buy{
  width:100%;
  background:#D4AF37;
  border:none;
  padding:9px;
  margin-top:8px;
  border-radius:20px;
  cursor:pointer;
}

.buy:hover{
  background:white;
}

/* FOOTER */
.footer{
  text-align:center;
  background:#1b120d;
  color:#bbb;
  padding:18px;
  margin-top:50px;
}

@media(max-width:850px){
  .hero,.items{padding:40px}
}
</style>
</head>

<body>

<!-- HEADER -->
<div class="header">
  <div class="logo">🌸 Flower 'n GO</div>
  <div class="nav">
    <span>Hello, <strong><?php echo $_SESSION['user']; ?></strong></span>
    <a href="profile.php">Profile</a>
    <a href="logout.php" class="logout">Logout</a>
  </div>
</div>


<!-- WELCOME -->
<section class="hero">
<h1>Welcome to Flower 'n GO</h1>
<p>Select your favorite flowers and customize your bouquet.</p>
</section>


<!-- ITEMS -->
<section class="items">

<h2>Available Flowers</h2>

<div class="grid">

<div class="card">
<img src="rose.jpg">
<div class="card-content">
<h3>Rose Bouquet</h3>
<p>Classic love flowers</p>
<div class="price">₱950</div>
<button class="buy">Add to Cart</button>
</div>
</div>

<div class="card">
<img src="sunflower.jpg">
<div class="card-content">
<h3>Sunflowers</h3>
<p>Bright golden flowers</p>
<div class="price">₱750</div>
<button class="buy">Add to Cart</button>
</div>
</div>

<div class="card">
<img src="tulips.jpg">
<div class="card-content">
<h3>Tulips</h3>
<p>Colorful fresh blooms</p>
<div class="price">₱850</div>
<button class="buy">Add to Cart</button>
</div>
</div>

<div class="card">
<img src="orchid.jpg">
<div class="card-content">
<h3>Orchids</h3>
<p>Exotic luxury flowers</p>
<div class="price">₱1200</div>
<button class="buy">Add to Cart</button>
</div>
</div>

<div class="card">
<img src="lily.jpg">
<div class="card-content">
<h3>Lilies</h3>
<p>Peaceful scent & beauty</p>
<div class="price">₱900</div>
<button class="buy">Add to Cart</button>
</div>
</div>

<div class="card">
<img src="custom.jpg">
<div class="card-content">
<h3>Custom Bouquet</h3>
<p>Create your own</p>
<div class="price">₱1500</div>
<button class="buy">Customize</button>
</div>
</div>

</div>
</section>


<!-- FOOTER -->
<div class="footer">
© 2025 Flower 'n GO — All Rights Reserved
</div>

</body>
</html>
