<?php
$conn = new mysqli("localhost", "root", "", "users_db");

if (isset($_GET["search"])) {
    $search = $_GET["search"];
    $query = "SELECT id, name, description FROM products WHERE name LIKE '%$search%'";
    $result = $conn->query($query);

    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row["id"] . "<br>";
        echo "Name: " . $row["name"] . "<br>";
        echo "Description: " . $row["description"] . "<br><br>";
    }
}
?>

<form method="get" action="">
    <input type="text" name="search">
    <input type="submit" value="Search">
</form>
