<?php
define('INSTALL_LANG', 'SC_UTF8');

$lang = array
(
	'SC_UTF8' => '简体中文 UTF8 版',
	
	'username' => '管理员账号:',
	'password' => '管理员密码:',
	'repeat_password' => '重复密码:',
	'admin_email' => '管理员 Email:',

	'succeed' => '成功',
	'enabled' => '允许',
	'writeable' => '可写',
	'readable' => '可读',
	'unwriteable' => '不可写',
	'yes' => '可',
	'no' => '不可',
	'unlimited' => '不限',
	'support' => '支持',
	'unsupport' => '<span class="redfont">不支持</span>',
	'old_step' => '上一步',
	'new_step' => '下一步',
	'tips_message' => '提示信息',
	'return' => '返回',

	'env_os' => '操作系统',
	'env_php' => 'PHP 版本',
	'env_mysql' => 'MySQL 支持',
	'env_attach' => '附件上传',
	'env_diskspace' => '磁盘空间',
	'env_dir_writeable' => '目录写入',

	'init_log' => '初始化记录',
	'clear_dir' => '清空目录',
	'select_db' => '选择数据库',
	'create_table' => '建立数据表',

	'install_wizard' => '安装向导',
	'current_process' => '当前状态:',
	'check_config' => '检查配置文件状态',
	'check_catalog_file_name' => '目录文件名称',
	'check_need_status' => '所需状态',
	'check_currently_status' => '当前状态',
	'edit_config' => '浏览/编辑当前配置',
	'variable' => '设置选项',
	'value' => '当前值',
	'comment' => '注释',
	'dbhost' => '数据库服务器:',
	'dbhost_comment' => '数据库服务器地址, 一般为 localhost',
	'dbuser' => '数据库用户名:',
	'dbuser_comment' => '数据库账号用户名',
	'dbpw' => '数据库密码:',
	'dbpw_comment' => '数据库账号密码',
	'dbname' => '数据库名:',
	'dbname_comment' => '数据库名称',
	'email' => '系统 Email:',
	'email_comment' => '用于发送程序错误报告',
	'tablepre' => '表名前缀:',
	'tablepre_comment' => '同一数据库安装多Nowhere时可改变默认',

	'recheck_config' => '重新检查设置',
	'check_env' => '检查当前服务器环境',
	'env_required' => 'Nowhere 所需配置',
	'env_best' => 'Nowhere 最佳配置',
	'env_current' => '当前服务器',
	'install_note' => '安装向导提示',
	'add_admin' => '设置管理员账号',
	'start_install' => '开始安装 Nowhere',
	'dbname_invalid' => '数据库名为空，请填写数据库名称',
	'admin_username_invalid' => '用户名空, 长度超过限制或包含非法字符。',
	'admin_password_invalid' => '两次输入密码不一致。',
	'admin_email_invalid' => 'Email 地址无效',
	'admin_invalid' => '您的信息没有填写完整。',

	'config_comment' => '请在下面填写您的数据库账号信息, 通常情况下不需要修改红色选项内容。',
	'config_unwriteable' => '安装向导无法写入配置文件, 请核对现有信息, 如需修改, 请通过 FTP 将改好的 config.inc.php 上传。',

	'database_errno_2003' => '无法连接数据库，请检查数据库是否启动，数据库服务器地址是否正确',
	'database_errno_1044' => '无法创建新的数据库，请检查数据库名称填写是否正确',
	'database_errno_1045' => '无法连接数据库，请检查数据库用户名或者密码是否正确',

	'dbpriv_createtable' => '没有CREATE TABLE权限，无法安装Nowhere',
	'dbpriv_insert' => '没有INSERT权限，无法安装Nowhere',
	'dbpriv_select' => '没有SELECT权限，无法安装Nowhere',
	'dbpriv_update' => '没有UPDATE权限，无法安装Nowhere',
	'dbpriv_delete' => '没有DELETE权限，无法安装Nowhere',
	'dbpriv_droptable' => '没有DROP TABLE权限，无法安装Nowhere',

	'php_version_406' => '您的 PHP 版本小于 4.0.6, 无法使用 Nowhere。',
	'attach_enabled' => '允许/最大尺寸 ',
	'attach_enabled_info' => '您可以上传附件的最大尺寸: ',
	'attach_disabled' => '不允许上传附件',
	'attach_disabled_info' => '附件上传或相关操作被服务器禁止。',
	'mysql_version_323' => '您的 MySQL 版本低于 3.23，安装无法继续进行。',
	'mysql_unsupport' => '您的服务器不支持MySql数据库，无法安装Nowhere程序',
	'avatar_unwriteable' => '头像存储目录(./avatar)属性非 777 或无法写入，您将不能自定义头像。',
	'data_unwriteable' => '数据目录(./data)属性非 777 或无法写入，安装无法继续进行。',
	'cachetpl_unwriteable' => '编译模板目录(./data/templates)属性非 777 或无法写入，安装无法继续进行。',
	'cache_unwriteable' => '数据缓存目录(./data/cache)属性非 777 或无法写入，安装无法继续进行。',
	'tablepre_invalid' => '您指定的数据表前缀包含点字符(".")，请返回修改。',
	'db_invalid' => '指定的数据库不存在, 系统也无法自动建立, 无法安装 Nowhere。',
	'db_auto_created' => '指定的数据库不存在, 但系统已成功建立, 可以继续安装。',
	'db_not_null' => '数据库中已经安装过 Nowhere, 继续安装会清空原有数据。',
	'db_drop_table_confirm' => '继续安装会清空全部原有数据，您确定要继续吗?',
	'install_in_processed' => '正在安装...',
	'install_succeed' => '恭喜您Nowhere安装成功，点击进入Nowhere首页',

	'preparation' => '
		<li>修改 ./inc/config.inc.php 中的 NWDIR 以为程序当前目录</li>
		<li>如果您使用非 WINNT 系统请修改以下属性：<br>&nbsp; &nbsp; <b>./avatar</b> 目录 777;<br />&nbsp; &nbsp;  <b>./data/下的所有目录</b> 777;&nbsp; &nbsp; <br></li>',

);

$msglang = array(

	'lock_exists' => '您已经安装过Nowhere，为了保证Nowhere数据安全，请手动删除 installer.php 文件 和 ./installer 文件夹下的所有文件，如果您想重新安装Nowhere，请删除 data/nowhere_install.lock 文件，再次运行安装文件。',
	'short_open_tag_invalid' => '对不起，请将php.ini中的short_open_tag设置为On，否则无法继续安装Nowhere。',
	'database_nonexistence' => '您的 ./core/DBCore.php 不存在, 无法继续安装, 请用 FTP 将该文件上传后再试。',
	'config_nonexistence' => '您的 config.inc.php 不存在, 无法继续安装, 请用 FTP 将该文件上传后再试。',

);

?>