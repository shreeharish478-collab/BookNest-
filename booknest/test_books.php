<?php
include("config.php");

$result = $conn->query("SELECT * FROM books");

while($row = $result->fetch_assoc()){
echo $row['title']."<br>";
}
?>