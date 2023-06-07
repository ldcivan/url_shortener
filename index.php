<?php
// 连接数据库
$conn = mysqli_connect("127.0.0.1", "root", "Ldc123456", "shortener");

// 检查连接是否成功
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// 如果表格不存在，创建它
$sql = "CREATE TABLE IF NOT EXISTS short_links (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    original_link VARCHAR(255) NOT NULL,
    short_link VARCHAR(20) NOT NULL
)";

mysqli_query($conn, $sql);

// 生成短链接函数
function generate_short_link() {
  $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $code = '';
  for ($i = 0; $i < 6; $i++) { //生成6位随机码
    $code .= $characters[rand(0, strlen($characters) - 1)];
  }
  return $code;
}

// 将链接插入数据库表中
if (isset($_POST['u'])||isset($_GET['u'])) {
    if(isset($_POST['u']))
        $original_link = $_POST['original_link'];
    else
        $original_link = $_GET['u'];
    if((strpos($original_link, "http://") !== 0 && strpos($original_link, "https://") !== 0) || !filter_var($original_link, FILTER_VALIDATE_URL)){
        echo("<script>alert('Make sure your input is a legal link with http:// or https://')</script>");
    }
    else{
        $short_link = generate_short_link();
        
        $sql = "SELECT COUNT(*) as count FROM short_links WHERE original_link = '$original_link'";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        if ($row['count'] == 0) {
            $sql = "INSERT INTO short_links (original_link, short_link) VALUES ('$original_link', '$short_link')";
            mysqli_query($conn, $sql);
            echo("Your short link：<a href=\"{$short_link}\" target=\"_blank\">http://s.pro-ivan.com/{$short_link}</a>");
        } else {
            echo "The link has been recorded before：<a href=\"{$short_link}\" target=\"_blank\">http://s.pro-ivan.com/{$short_link}</a>";
        }
    }
}


// 获取原始链接并重定向用户
if (isset($_GET['short_link'])) {
    $short_link = $_GET['short_link'];
    
    $sql = "SELECT original_link FROM short_links WHERE short_link = '$short_link'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $original_link = $row['original_link'];
        header("Location: $original_link");
        exit();
    }
}

// 关闭数据库连接
mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>URL Shortener</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="shortcut icon" href="/favicon.ico">
</head>
<body>
    <h1>URL Shortener</h1>
    
    <form method="POST" action="">
        <label for="original_link">Original Link:</label>
        <input type="text" name="original_link" id="original_link">
        <br><br>
        <input type="submit" name="u" value="Shorten Link">
    </form>
    
    <br><br>
    
    <h3>Shortened Links List:</h3>
    
    <?php
    // 连接数据库
    $conn = mysqli_connect("127.0.0.1", "root", "Ldc123456", "shortener");
    
    // 检查连接是否成功
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    
    // 显示短链接列表
    $sql = "SELECT * FROM short_links";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        echo "<ul>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<li><a href=\"{$row['short_link']}\" target=\"_blank\">http://s.pro-ivan.com/{$row['short_link']}</a> ({$row['original_link']})</li>";
        }
        echo "</ul>";
    }
    // 关闭数据库连接
    mysqli_close($conn);
    ?>
    
    <footer>
        <h4><a href="//pro-ivan.com" target="_blank">A <b>FREE</b> URL Shortener Create by Pro-Ivan</a></h4>
        <p>Type your link in the textarea then click '<b>shorten link</b>', <br>or POST/GET your link to this page with the keyname '<b>u</b>'<br><a href="https://www.upyun.com/?utm_source=lianmeng&utm_medium=referral" target="_blank">This website is provided by <img src="/upyun.png" height=18px align="center"> with CDN services</a></p>
    </footer>
</body>
</html>
