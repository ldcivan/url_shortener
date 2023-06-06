<?php
// 获取短链接参数
$short_link = $_GET['short'];

// 连接到MySQL数据库
$conn = mysqli_connect("localhost", "username", "password", "database_name");

// 查询短链接对应的原始链接
$sql = "SELECT original_link FROM short_links WHERE short_link='$short_link'";
$result = mysqli_query($conn, $sql);

// 如果找到了对应的原始链接，则进行重定向
if (mysqli_num_rows($result) > 0) {
  $row = mysqli_fetch_assoc($result);
  $original_link = $row['original_link'];
  if(strpos($url, "http://") !== 0 && strpos($url, "https://") !== 0){
      $original_link = 'http://'. $original_link;
  }
  header("Location: $original_link");
  exit;
} else {
  // 如果没有找到对应的原始链接，则显示错误信息
  echo "Error: Short link not found.";
}
?>
