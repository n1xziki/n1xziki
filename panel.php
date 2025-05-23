<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "dashboard");

// إنشاء الجدول إذا ما كان موجود
$mysqli->query("CREATE TABLE IF NOT EXISTS items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255),
  description TEXT,
  image VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// بيانات دخول الأدمن (ثابتة)
$adminUser = "admin";
$adminPass = "12345";

// تسجيل دخول
if (isset($_POST['login'])) {
    if ($_POST['username'] == $adminUser && $_POST['password'] == $adminPass) {
        $_SESSION['logged_in'] = true;
    } else {
        echo "<p style='color:red;'>بيانات الدخول غير صحيحة</p>";
    }
}

// تسجيل خروج
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: panel.php");
}

// إضافة عنصر
if (isset($_POST['add']) && $_SESSION['logged_in']) {
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $imgName = $_FILES['image']['name'];
    $imgTmp = $_FILES['image']['tmp_name'];

    if (!is_dir("uploads")) mkdir("uploads");
    move_uploaded_file($imgTmp, "uploads/" . $imgName);

    $stmt = $mysqli->prepare("INSERT INTO items (title, description, image) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $desc, $imgName);
    $stmt->execute();
}

// حذف عنصر
if (isset($_GET['delete']) && $_SESSION['logged_in']) {
    $id = intval($_GET['delete']);
    $mysqli->query("DELETE FROM items WHERE id = $id");
}

// واجهة
echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>لوحة التحكم</title>
<style>
body { background:#111; color:#fff; font-family:tahoma; padding:20px }
form, .item { background:#222; padding:15px; margin-bottom:15px; border-radius:10px }
input, textarea, button { display:block; width:100%; margin:10px 0; padding:10px; background:#333; color:#fff; border:none; border-radius:5px }
img { max-width:100px; border-radius:5px }
a { color:#f55; text-decoration:none }
</style>
</head><body>";

if (!isset($_SESSION['logged_in'])) {
    echo "<h2>تسجيل دخول</h2>
    <form method='post'>
        <input type='text' name='username' placeholder='اسم المستخدم'>
        <input type='password' name='password' placeholder='كلمة السر'>
        <button name='login'>دخول</button>
    </form>";
} else {
    echo "<h2>لوحة التحكم</h2>
    <a href='?logout=true'>تسجيل خروج</a>
    <form method='post' enctype='multipart/form-data'>
        <input type='text' name='title' placeholder='العنوان'>
        <textarea name='description' placeholder='الوصف'></textarea>
        <input type='file' name='image'>
        <button name='add'>إضافة</button>
    </form>";

    $result = $mysqli->query("SELECT * FROM items ORDER BY id DESC");
    while ($row = $result->fetch_assoc()) {
        echo "<div class='item'>
            <h3>{$row['title']}</h3>
            <img src='uploads/{$row['image']}'><br>
            <p>{$row['description']}</p>
            <a href='?delete={$row['id']}'>حذف</a>
        </div>";
    }
}

echo "</body></html>";
?>