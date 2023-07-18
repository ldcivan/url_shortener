# url_shortener
一个基于PHP与MySQL的短链站/A link shortener base on PHP and MySQL

### 使用方式/How to use
* 将项目内的所有文件置于同一个文件夹（建议放置于网站根目录），之后更改<code>index.php</code>以及<code>redirect.php</code>内的MySQL数据库信息为你自己的；
* 设置MySQL数据库，你只需要建立一个新的数据库，其中的数表PHP会自行建立；
* 设置网站的重定向。比如，你的<code>index.php</code>等文件放置于根目录（/）下，你需要将所有访问根目录下任何路径的请求重定向到<code>redirect.php</code>。下面，以Apache为例进行设置：

  在<code>.htaccess</code>（伪静态）中添加以下内容：
  <pre>
  RewriteEngine On
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule ^(.*)$ /redirect.php?short=$1 [L] 
  </pre>

### 注意/Notice
* 以Nginx建站的服务器，在上面的假定情况中可尝试将伪静态改为：
  <pre>
  location / {
    if (!-e $request_filename){
      rewrite ^/(.*)$ /redirect.php?short=$1 last;
    }
  } 
  </pre>
* <code>style.css</code>中的内容可根据您自身需求更改
