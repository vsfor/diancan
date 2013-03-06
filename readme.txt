管理员用户
admin   admin123

建立数据库后导入 diancan.sql 文件

数据库配置，修改config.php 文件
//数据库配置
$config['DB_TYPE']='mysql';//数据库类型，一般不需要修改
$config['DB_HOST']='localhost';//数据库主机，一般不需要修改
$config['DB_USER']='root';//数据库用户名
$config['DB_PWD']='wj';//数据库密码
$config['DB_PORT']=3306;//数据库端口，mysql默认是3306，一般不需要修改
$config['DB_NAME']='diancan';//数据库名
$config['DB_CHARSET']='utf8';//数据库编码，一般不需要修改
$config['DB_PREFIX']='';//数据库前缀


暂未测试  数据库前缀   建议留空