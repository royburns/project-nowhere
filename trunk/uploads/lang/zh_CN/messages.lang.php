<?php

$language = array
(
	'403_forbidden'=>'噢!天呐!你似乎在尝试进入Nowhere的内核,为了地球的和平与安全,请不要这么做……',
	'undefined_action' => '页面不存在,或者您必须登录才能访问此页。',
	'page_nonexistence' => '啊噢,你访问的页面似乎被管理猿吃掉了,请仔细检查你所访问的URL或Check RP',
	'submit_invalid' => '您的请求来路不正确或验证字串不符，无法提交。如果您安装了某种默认屏蔽来路信息的个人防火墙软件(如 Norton Internet Security)，请设置其不要禁止来路信息后再试。',
	'not_loggedin' => '抱歉，当前操作需要您 <a href=\"'.NWDIR.'/login\" 登录</a> 后才能继续进行',
	'max_pages' => '管理员设置了本内容可以被翻阅到的最大页数为 $pages 页，如需查看相关内容，请返回并指定查询条件后再试。',
	'login_invalid' => '用户名无效，密码错误或安全问题回答错误，您可以有至多 5 次尝试。',
	'login_strike' => '累计 5 次错误尝试，15 分钟内您将不能登录本站。',
	'login_succeed' => '{$nw_nick} ，欢迎您回来。现在将转入登录前页面。',
	'logout_succeed' => '您已成功登出，我们随时欢迎您回来。',
	
	'password_set_succeed' => '你所设置的新密码已经生效，你可能需要重新登录，现在将为你跳转至登录页',
    'profile_passwd_notmatch' => '两次输入的密码不一致，请返回检查后重试。',
    'profile_passwd_wrong' => '原密码不正确，您不能修改密码，请返回。',	
	'profile_passwd_illegal' => '密码空或包含非法字符，请返回重新填写。',
);

?>