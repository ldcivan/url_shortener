<!DOCTYPE html>
<?php
// 配置
$rateLimit = 2; // 每小时允许的请求数
$timeWindow = 60; // 时间窗口，单位秒（1小时）

// 获取用户 IP
$userIP = $_SERVER['REMOTE_ADDR'];

// 定义存储文件路径
$storageFile = sys_get_temp_dir() . '/rate_limit_' . md5($userIP) . '.json';

// 初始化或读取存储文件
if (file_exists($storageFile)) {
    $data = json_decode(file_get_contents($storageFile), true);
} else {
    $data = ['requests' => 0, 'startTime' => time()];
}
?>
<html>
<head>
    <meta charset="utf-8">
    <meta name="keywords" content="短链接,链接缩短,URL缩短,链接优化,在线工具,网站优化,网址生成,链接管理,营销工具,Short link,URL shortener,Link optimization,Online tool,Website optimization,URL generator,Link management,Marketing tool,URL shortening service">
    <title>URL Shortener</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="shortcut icon" href="/favicon.ico">
</head>
<body>
    <h1>URL Shortener<br><small>In the past few hours, the shorten function has been down. Sorry for this!!</small></h1>
    
    <form method="POST" action="">
        <label for="original_link">Original Link:</label>
        <input type="text" name="original_link" id="original_link">
        <br><br>
        <input type="submit" name="u" value="Shorten Link">
    </form>
    
    <br><br>
    
<?php
/// 检查时间窗口
$currentTime = time();
if ($currentTime - $data['startTime'] > $timeWindow) {
    // 重置计数器
    $data['requests'] = 0;
    $data['startTime'] = $currentTime;
}

if (isset($_POST['u'])||isset($_GET['u'])) {
    // 增加请求计数
    $data['requests']++;
}

// 检查是否超过限制
if ($data['requests'] > $rateLimit) {
    header('HTTP/1.1 429 Too Many Requests');
    echo "<script>alert('You can only submit the form twice per minute.');</script>";
} else {
    // 连接数据库
    $conn = mysqli_connect("localhost", "root", "password", "shortener");
    mysqli_set_charset($conn, "utf8");
    
    // 检查连接是否成功
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    
    // 如果表格不存在，创建它
    $sql = "CREATE TABLE IF NOT EXISTS short_links (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        original_link VARCHAR(512) NOT NULL,
        short_link VARCHAR(20) NOT NULL,
        time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    // mysqli_query($conn, $sql);
    // 创建表格这一坨其实用了一次就可以注释掉了
    
    // 生成短链接函数
    function generate_short_link($conn) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';
        $isUnique = false;
    
        while (!$isUnique) {
            $code = '';
            for ($i = 0; $i < 6; $i++) {
                $code .= $characters[rand(0, strlen($characters) - 1)];
            }
    
            // 查询数据库，检查短码是否已存在
            $sql = "SELECT COUNT(*) as count FROM short_links WHERE short_link = '$code'";
            $result = mysqli_query($conn, $sql);
    
            if (!$result) {
                die("Query failed: " . mysqli_error($conn));
            }
    
            $row = mysqli_fetch_assoc($result);
    
            if ($row['count'] == 0) {
                $isUnique = true;
            }
    
            mysqli_free_result($result);
        }
    
        return $code;
    }
    
    // 将链接插入数据库表中
    if (isset($_POST['u'])||isset($_GET['u'])) {
        if(isset($_POST['u']))
            $original_link = $_POST['original_link'];
        else
            $original_link = $_GET['u'];
        if(strlen($original_link)>500) {
            echo("Too long URL. Length of URL should less than 500.");
        }
        elseif(strpos($original_link, "http://") !== false || strpos($original_link, "https://") !== false || filter_var(idn_to_ascii($original_link), FILTER_VALIDATE_URL) !== false){
            $short_link = generate_short_link($conn);
            
            $sql = "SELECT COUNT(*) as count FROM short_links WHERE original_link = '$original_link'";
            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($result);
            if ($row['count'] == 0) {
                $sql = "INSERT INTO short_links (original_link, short_link, time) VALUES ('$original_link', '$short_link', NOW())";
                mysqli_query($conn, $sql);
                echo("<center><b>Your short link</b><br><li><a href=\"{$short_link}\" target=\"_blank\">http://s.pro-ivan.com/{$short_link}</a> (".$original_link.")</li></center>");
            } else {
                $sql = "SELECT * FROM short_links WHERE original_link = '$original_link'";
                $result = mysqli_query($conn, $sql);
                $row = mysqli_fetch_assoc($result);
                echo "<center><b>The link has been recorded before</b><br><li><a href=\"{$row['short_link']}\" target=\"_blank\">http://s.pro-ivan.com/{$row['short_link']}</a> (".$original_link.")</li></center>";
            }
        }
        else{
            echo("<script>alert('Make sure your input is a legal link with http:// or https://".filter_var(idn_to_ascii($original_link), FILTER_VALIDATE_URL)."')</script>");
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
    
    $sql = "SELECT COUNT(*) as count FROM short_links";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        // 输出信息条数
        $row = $result->fetch_assoc();
        $count = $row['count'];
    } else {
        $count = 0;
    }
    echo '<center><p style="font-size: 1.2rem; font-weight: bold; line-height: 1.8;">We have recorded <big>'.$count.'</big> URLs, thank you for your trust!</p></center>';
    
    // 关闭数据库连接
    mysqli_close($conn);
    
    $_SESSION['last_submit_time'] = time();
}

// 保存更新后的数据
file_put_contents($storageFile, json_encode($data));
?>
    
    <br><br>
    <!--
    <h3>Shortened Links List</h3>
    <center style="margin-bottom: 14rem">不公开已记录短链，请在记录时自行保存好短链<br>No longer disclose recorded shortened links, please save the shortened links when shortening</center>
    -->
    <?php
    /*
    // 连接数据库
    $conn = mysqli_connect("localhost", "root", "password", "shortener");
    
    // 检查连接是否成功
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    
    // 显示短链接列表
    $sql = "SELECT * FROM short_links ORDER BY id DESC";
    $result = mysqli_query($conn, $sql);
    function simplifyURL($url) {
      $maxLength = 40; // 简化后的 URL 最大长度
      $ellipsis = '...'; // 省略号
    
      if (strlen($url) <= $maxLength) {
        return $url; // 如果 URL 长度已经小于等于最大长度，则直接返回原始 URL
      } else {
        $simplifiedURL = substr($url, 0, $maxLength - strlen($ellipsis)) . $ellipsis;
        return $simplifiedURL;
      }
    }
    
    if (mysqli_num_rows($result) > 0) {
        echo "<div style='display: flex; justify-content: center;'><ul>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<li><a href=\"{$row['short_link']}\" target=\"_blank\">http://s.pro-ivan.com/{$row['short_link']}</a> (".simplifyURL($row['original_link']).")</li>";
        }
        echo "</ul></div>";
    }
    // 关闭数据库连接
    mysqli_close($conn);
    */
    ?>
    
    <footer>
        <div style="display:inline-block;">
            <h4><a href="//pro-ivan.com" target="_blank">A <b>FREE</b> URL Shortener Created by Pro-Ivan</a></h4>
            <p>Type your link in the textarea then click '<b>shorten link</b>', <br>or POST/GET your link to this page with the keyname '<b>u</b>'<br><a href="https://www.upyun.com/?utm_source=lianmeng&utm_medium=referral" target="_blank">This website is provided by <img src="/upyun.png" height=18px align="center"> with CDN services</a><br>We will delete records with a lifespan longer than 2 years</p>
        </div>
        <div style="width:500px;max-width:90%;display:inline-block;margin-left:10px;">
            <div style="display:inline-flex;align-items:center;">
                <img src="/sponsor/weixin.webp" style="margin:5px;width:32%;display:inline-block;"><img src="/sponsor/alipay.webp" style="margin:5px;width:32%;display:inline-block;"><img src="/sponsor/paypal.webp" style="margin:5px;width:32%;display:inline-block;"><br>
            </div>
            <p style="display:block;margin-top:5px;">Would you like by me cup of milktea?</p>
        </div>
    </footer>
</body>
</html>
