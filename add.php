<?php
$reference = $_SERVER['HTTP_REFERER'] ?? '';

// 检查 reference 是否不为空并且不匹配指定的模式
if (!empty($reference) && !preg_match('/\.pro-ivan\./', $reference)) {
    echo '<center><video controls autoplay><source src="https://vdse.bdstatic.com//192d9a98d782d9c74c96f09db9378d93.mp4" type="video/mp4"></video></center>';
    exit('<center><b>Invalid request, please contact developer</b></center>');
}

// 配置
$rateLimit = 2; // 每小时允许的请求数
$timeWindow = 60; // 时间窗口，单位秒（1小时）

function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        // 检查共享互联网 IP
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // 检查通过代理服务器的 IP
        $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($ipList[0]); // 可能有多个 IP，取第一个
    } else {
        // 默认情况下使用 REMOTE_ADDR
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

// 获取用户 IP
$userIP = getUserIP();

// 定义存储文件路径
$storageFile = sys_get_temp_dir() . '/rate_limit_' . md5($userIP) . '.json';

// 初始化或读取存储文件
if (file_exists($storageFile)) {
    $data = json_decode(file_get_contents($storageFile), true);
} else {
    $data = ['requests' => 0, 'startTime' => time()];
}
/// 检查时间窗口
$currentTime = time();
if ($currentTime - $data['startTime'] > $timeWindow) {
    // 重置计数器
    $data['requests'] = 0;
    $data['startTime'] = $currentTime;
}
//echo "<center>".$userIP.":".$data['requests']."</center>";

if (isset($_POST['original_link'])||isset($_GET['original_link'])) {
    // 增加请求计数
    $data['requests']++;
}

// 检查是否超过限制
if ($data['requests'] > $rateLimit) {
    header('HTTP/1.1 429 Too Many Requests');
    echo "<center><b>Sorry! You can only submit the form twice per minute.</b></center>";
} else {
    // 连接数据库
    $conn = mysqli_connect("localhost", "username", "password", "shortener");
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
    
    mysqli_query($conn, $sql);
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
    if (isset($_POST['original_link'])||isset($_GET['original_link'])) {
        if(isset($_POST['original_link']))
            $original_link = $_POST['original_link'];
        else
            $original_link = $_GET['original_link'];
        if(strlen($original_link)>500) {
            echo("<center><b>Too long URL. Length of URL should less than 500.</b></center>");
        }
        elseif(strpos($original_link, "http://") !== false || strpos($original_link, "https://") !== false || filter_var(idn_to_ascii($original_link), FILTER_VALIDATE_URL) !== false){
            $short_link = generate_short_link($conn);
            
            $sql = "SELECT COUNT(*) as count FROM short_links WHERE original_link = '$original_link'";
            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($result);
            if ($row['count'] == 0) {
                $sql = "INSERT INTO short_links (original_link, short_link, time, last_used) VALUES ('$original_link', '$short_link', NOW(), NOW())";
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
            echo("<center><b>Make sure your input is a legal link with http:// or https://".filter_var(idn_to_ascii($original_link), FILTER_VALIDATE_URL)."</b></center>");
        }
    }
    
    $_SESSION['last_submit_time'] = time();
}

// 保存更新后的数据
file_put_contents($storageFile, json_encode($data));
?>