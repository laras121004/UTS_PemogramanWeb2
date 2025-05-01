|Keterangan|Data|
|----|----|
|Nama|Laras Sakti|
|Nim|312310627|
|Kelas|TI.23.A6|
|Mata Kuliah|Pemograman Web 2|

# Mengungkap Celah Keamanan: Eksplorasi dan Mitigasi SQL Injection pada Aplikasi Web Modern

## Pendahuluan

SQL Injection merupakan salah satu kerentanan keamanan web yang paling umum dan berbahaya. Serangan ini terjadi ketika penyerang dapat menyisipkan kode SQL berbahaya ke dalam input yang kemudian diproses oleh database aplikasi web. Meskipun sudah dikenal selama lebih dari dua dekade, SQL Injection masih menjadi ancaman serius bagi aplikasi web modern. Menurut laporan OWASP (Open Web Application Security Project), SQL Injection tetap berada dalam daftar 10 kerentanan keamanan teratas pada tahun 2021.

Artikel ini bertujuan untuk menjelaskan konsep SQL Injection secara mendalam, mendemonstrasikan bagaimana serangan ini bekerja melalui eksperimen praktis, menganalisis dampaknya, dan memberikan strategi pencegahan yang efektif. Pemahaman tentang kerentanan ini sangat penting bagi pengembang web untuk membangun aplikasi yang aman.

## Memahami SQL Injection

## Apa itu SQL Injection?

SQL Injection adalah teknik serangan di mana penyerang memasukkan atau “menyuntikkan” perintah SQL berbahaya melalui input aplikasi web. Ketika aplikasi tidak memvalidasi atau membersihkan input ini dengan benar sebelum menggabungkannya ke dalam query database, penyerang dapat memanipulasi logika query asli, mengakses data yang tidak sah, memodifikasi database, atau bahkan mengambil alih server.

## Jenis-Jenis SQL Injection

Berdasarkan eksperimen yang saya lakukan, SQL Injection dapat diklasifikasikan menjadi beberapa tipe:

1. In-band SQL Injection: Penyerang mendapatkan hasil serangan melalui saluran komunikasi yang sama dengan yang digunakan untuk meluncurkan serangan.

  - Error-based: Memanfaatkan pesan kesalahan yang ditampilkan oleh database untuk mendapatkan informasi tentang struktur database.
  - Union-based: Menggunakan operator UNION SQL untuk menggabungkan hasil query berbahaya dengan query asli.
2. Blind SQL Injection: Penyerang tidak dapat melihat hasil serangan secara langsung.
  - Boolean-based: Mengirim query dan menganalisis perbedaan dalam respons aplikasi.
  - Time-based: Mengirim query yang menyebabkan database menunda responsnya untuk beberapa waktu jika kondisi tertentu terpenuhi.
3. Out-of-band SQL Injection: Penyerang menggunakan saluran alternatif untuk mendapatkan hasil (misalnya melalui DNS atau HTTP requests).

# Eksperimen SQL Injection

Untuk memahami SQL Injection secara lebih mendalam, saya telah melakukan serangkaian eksperimen dalam lingkungan terkontrol. Berikut adalah penjelasan langkah demi langkah dari eksperimen tersebut.

## Setup Lingkungan Pengujian

1. Saya membuat aplikasi web sederhana menggunakan:

  - PHP 8.0 sebagai bahasa pemrograman server
  - MySQL 8.0 sebagai database
  - HTML dan CSS untuk frontend
  - XAMPP sebagai server lokal

2. Saya membuat database users dengan struktur berikut:
   ![image](https://github.com/user-attachments/assets/cfe6d82c-c688-4748-85eb-e0713c540fc9)

   ![image](https://github.com/user-attachments/assets/10ad5c60-484c-481e-b5bd-ab5db59a0039)

3. Saya membuat halaman login sederhana dengan kode PHP yang rentan:

   ```
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
```

## Eksperimen 1: Login Bypass Sederhana
Dalam eksperimen pertama, saya mencoba melakukan bypass pada form login.
Langkah-langkah:
1.	Saya membuka halaman login
2.	Pada kolom username, saya memasukkan: ' OR '1'='1
3.	Pada kolom password, saya memasukkan: ' OR '1'='1
Hasil:
•	Query yang dieksekusi menjadi: SELECT * FROM users WHERE username = '' OR '1'='1' AND password = '' OR '1'='1'
•	Kondisi '1'='1' selalu bernilai TRUE, sehingga WHERE clause selalu terpenuhi
•	Aplikasi menampilkan "Login berhasil!" meskipun saya tidak memasukkan kredensial yang valid
•	Saya berhasil login sebagai pengguna pertama dalam database (dalam hal ini, admin)

## Eksperimen 2: Ekstraksi Data dengan UNION Attack
Selanjutnya, saya mencoba mengekstrak informasi dari database menggunakan UNION attack.
Langkah-langkah:
1.	Saya membuat halaman pencarian produk sederhana yang rentan:
   ## product_search.php

   ```
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
```

2.	Saya memasukkan payload berikut di kolom pencarian:
' UNION SELECT id, username, password FROM users WHERE '1'='1
Hasil:
•	Query yang dieksekusi menjadi: SELECT id, name, description FROM products WHERE name LIKE '%' UNION SELECT id, username, password FROM users WHERE '1'='1%'
•	Halaman menampilkan semua username dan password dari tabel users
•	Saya berhasil mendapatkan kredensial semua pengguna, termasuk admin

Tampilan hasil nya:
![image](https://github.com/user-attachments/assets/d2f2aeca-efa6-46ec-8685-ed0bcde9e1b4)

## Eksperimen 3: Blind SQL Injection
Dalam eksperimen ketiga, saya mensimulasikan situasi di mana aplikasi tidak menampilkan pesan kesalahan SQL atau hasil query secara langsung.
Langkah-langkah:
1.	Saya membuat halaman pengecekan username yang hanya menampilkan apakah username ada atau tidak:
   ```
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
```




