<?php
$conn = new mysqli(hostname: "localhost", username: "root", password: "", database: "users_db");

if (isset($_GET["username"])) {
    $username = $_GET["username"];
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        echo "Username exists";
    } else {
        echo "Username does not exist";
    }
}
?>

<form method="get" action="">
    <input type="text" name="username">
    <input type="submit" value="Check">
</form>
