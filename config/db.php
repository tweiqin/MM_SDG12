<?php
// Terraform will output the RDS address, or you can use an environment variable
$servername = getenv('DB_HOST') ?: "makanmystery-db.your-endpoint.aws.com";
$username = "admin";
$password = "030219#Mm";
$database = "mm_sdg12";

$conn = new mysqli($servername, $username, $password, $database);
?>

<?php
$servername = getenv('DB_HOST') ?: "localhost";
$username = "admin";
$password = "030219#Mm";
$dbname = "mm_sdg12";

$conn = new mysqli($servername, $username, $password, $dbname);
?>