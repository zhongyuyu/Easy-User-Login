<?php
if( is_user_logged_in() ){
	header( "Location:" .'/' );
	// 登录成功后跳转至首页
}
get_header();
?>
<div class="main">
	<div class="panel">
		<div class="box">
		<?php if($_GET['action'] == 'register'){ ?>
			<div class="content">
				<h1>用户注册</h1>
				<div class="input">
					 <input class="form-control" type="text" name="user_login" placeholder="用户名">
				</div>
				<div class="input">
					<input class="form-control" type="email" name="user_email" placeholder="邮箱">
				</div>
				<div class="input">
					<input class="form-control" type="password" name="user_pass" placeholder="密码">
				</div>
				<div class="input">
					<input class="form-control" type="text" name="user_captcha" placeholder="验证码">
					<button class="send-captcha" type="button">发送验证码</button>
				</div>
				<div class="submit">
					<button type="button" class="sign-up">立即注册</button>
				</div>
				<div class="description">
					已有账户？<a href="?action=login">立即登录</a>
				</div>
			</div>
		<?php } else if ($_GET['action'] == 'forget'){ ?>
			<div class="content">
				<h1>找回密码</h1>
				<div class="tips">请输入您的用户名或电子邮箱地址，您会收到一封包含创建新密码链接的电子邮件。</div>
				<div class="input">
					<input class="form-control" type="text" name="user_name" placeholder="用户名/电子邮箱" >
				</div>
				<div class="submit">
					<button type="button" class="recover">找回密码</button>
				</div>
				<div class="description">
					密码想起来了？<a href="?action=login">返回登录</a>
				</div>
			</div>
		<?php } else if ( $_GET['action'] == 'password' ){ ?>
			<div class="content">
				<h1>重置密码</h1>
				<?php
					$reset_key   = $_GET['key']; 
					$user_login  = $_GET['login']; 
					$user_data   = $wpdb->get_row($wpdb->prepare("SELECT ID, user_login, user_email, user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login));
					$user_login  = $user_data->user_login;   
					$user_email  = $user_data->user_email;   
					if(!empty($reset_key) && !empty($user_data) && md5('thememee'.$user_data->user_activation_key) == $reset_key) {
				?>
				<div class="input">
					<input class="form-control" type="password" name="new_password" placeholder="新密码">
				</div>
				<div class="input">
					<input class="form-control" type="password" name="cfm_password" placeholder="确认新密码">
				</div>
				<div class="submit">
					<input type="hidden" name="reset_key" value="<?php echo $reset_key;?>">
					<input type="hidden" name="reset_login" value="<?php echo $user_login;?>">
					<button type="button" class="reset">修改密码</button>
				</div>
				<div class="description">密码想起来了？<a href="?action=login">返回登录</a></div>
				<?php } else { ?>
				<span>错误的请求，请查看邮箱里的重置密码链接。</span>
				<?php } ?>
			</div>
		<?php } else { ?>
			<div class="content">
				<h1>用户登录</h1>
				<div class="input">
					<input class="form-control" type="text" name="username" placeholder="用户名/电子邮箱">
				</div>
				<div class="input">
					<input class="form-control" type="password" name="password" placeholder="密码">
				</div>
				<div class="forget">
					<a href="?action=forget">忘记密码？</a>
				</div>
				<div class="submit">
					<button type="button" class="sign-in">立即登录</button>
				</div>
				<div class="description">
					还没有注册账号？<a href="?action=register">新用户注册</a>
				</div>
			</div>
		<?php } ?>
		</div>
	</div>
	<div class="background" style="background:url(<?php echo PLUGIN_URL .'/img/background.png';?>) center center / cover no-repeat"></div>
</div>
<?php get_footer(); ?>