<?php
include 'db.php';

$result = mysqli_query($conn, "SHOW TABLES");

while ($row = mysqli_fetch_array($result)) {
  echo $row[0] . "<br>";
}
?>
