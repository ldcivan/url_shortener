    <?php
    // 连接数据库
    $conn = mysqli_connect("localhost", "username", "password", "shortener");
    
    // 检查连接是否成功
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    
    // 显示短链接列表
    $sql = "SELECT * FROM short_links ORDER BY id DESC LIMIT 50";
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
    ?>