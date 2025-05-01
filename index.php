<?php
$conn = new mysqli("localhost", "root", "", "users_db");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Kode rentan terhadap SQL Injection
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        echo "Login berhasil!";
    } else {
        echo "Username atau password salah.";
    }
}
?>

<form method="post" action="">
    <label>Username</label>
    <input type="text" name="username">
    <label>Password</label>
    <input type="password" name="password">
    <input type="submit" value="Login">
</form>
