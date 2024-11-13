<?php
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
    
    // mysqli_query($conn, $sql);
    // 创建表格这一坨其实用了一次就可以注释掉了
    
    $sql = "SELECT COUNT(*) as count FROM short_links";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        // 输出信息条数
        $row = $result->fetch_assoc();
        $count = $row['count'];
    } else {
        $count = 0;
    }
    echo $count;
    
    // 关闭数据库连接
    mysqli_close($conn);
?>