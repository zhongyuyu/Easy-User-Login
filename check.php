<?php
header("Content-type:text/html;character=utf-8");
require_once('../../../wp-load.php');
date_default_timezone_set('Asia/Shanghai');
global $wpdb;
if( !is_user_logged_in() ){
	$is_name	= '/^[a-z0-9_]{1,}$/';
	$is_email   = '/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/';
	$status	    = '';
	$msg        = '';
	if ( $_POST['action'] == 'check.login' ){
		$username = $wpdb->escape( $_POST['username'] );
		$password = $wpdb->escape( $_POST['password'] );
		if ( empty( $username ) ){
			$status = 0;
			$msg    = '请输入用户名或邮箱地址';
		} else if ( empty( $password ) ){
			$status = 0;
			$msg    = '请输入密码';
		} else {
			$login_data = array();
			$login_data['user_login'] = $username;
			$login_data['user_password'] = $password;
			$login_data['remember'] = true;
			$user_verify = wp_signon( $login_data, false );
			if ( is_wp_error($user_verify) ) {
				$status = 0;
				$msg    = '用户名或密码错误';
			} else {
				$status = 1;
				$msg    = '登录成功，跳转中...';
			}
		}
		echo json_encode( array( "status" => $status, "msg" => $msg ) );
	} else if ( $_POST['action'] == 'check.register' ){
		$user_login   = sanitize_user( $_POST['user_login'] );
		$user_email   = apply_filters( 'user_registration_email', $wpdb->escape( trim( $_POST['user_email'] ) ) );
		$user_pass	= $wpdb->escape( $_POST['user_pass'] );
		$user_captcha = $wpdb->escape( $_POST['user_captcha'] );
		if ( empty( $user_login ) ) {
			$status = 0;
			$msg    = '请输入用户名';
		} else if ( !preg_match( $is_name, $user_login ) ) {
			$status = 0;
			$msg    = '用户名只能由字母数字或下划线组成';
		} else if ( strlen( $user_login ) < 6 ) {
			$status = 0;
			$msg    = '用户名长度不得小于6位';
		} else if ( strlen( $user_login ) > 10 ) {
			$status = 0;
			$msg    = '用户名长度不得大于10位';
		} else if ( username_exists( $user_login ) ) {
			$status = 0;
			$msg    = '该用户名已被注册';
		} else if ( empty( $user_email ) ) {
			$status = 0;
			$msg    = '请输入邮箱地址';
		} else if ( !preg_match( $is_email, $user_email ) ) {
			$status = 0;
			$msg    = '邮箱格式不正确';
		} else if ( email_exists( $user_email ) ) {
			$status = 0;
			$msg    = '该邮箱已被注册';
		} else if ( empty( $user_pass ) ) {
			$status = 0;
			$msg    = '请输入密码';
		} else if ( strlen( $user_pass ) < 6 ) {
			$status = 0;
			$msg    = '密码长度不得小于6位';
		} else if ( empty( $_SESSION['register_captcha'] ) ){
			$status = 0;
			$msg    = '请获取验证码';
		} else if ( empty( $user_captcha ) ) {
			$status = 0;
			$msg    = '请输入验证码';
		} else if ( ( strtotime( $_SESSION["register_captcha_time"] ) + 300 ) < time() ){
			$status = 0;
			$msg    = '您的验证码已过期，请重新获取！';
		} else if ( $user_captcha != $_SESSION['register_captcha'] ){
			$status = 0;
			$msg    = '验证码错误';
		} else if ( $_SESSION['register_email'] != $user_email ){
			$status = 0;
			$msg    = '邮箱与验证码不对应';
		} else {
			unset( $_SESSION['register_captcha'] );
			unset( $_SESSION['register_captcha_time'] );
			unset( $_SESSION['register_email'] );
			$user_data=array(
				'ID' => '',
				'user_login' => $user_login,
				'user_pass'  => $user_pass,
				'user_email' => $user_email,
				'role' => get_option('default_role'),
			);
			$user_id = wp_insert_user($user_data);
			if ( is_wp_error($user_id) ) {
				$status = 0;
				$msg    = '系统超时，请稍后重试！';
			} else {
				$status = 1;
				$msg    = '注册成功，登录中...';
				wp_set_auth_cookie( $user_id, true, false );
				$message = '<div style="background-color: #f5f5f5; margin: 0; padding: 70px 0; width: 100%; -webkit-text-size-adjust: none;"><table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tbody><tr><td align="center" valign="top"><table border="0" cellpadding="0" cellspacing="0" width="600" style="background-color: #fdfdfd; border: 1px solid #dcdcdc; box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1); border-radius: 3px;"><tbody><tr><td align="center" valign="top"><table border="0" cellpadding="0" cellspacing="0" width="600" style="background-color: #008c95; color: #ffffff; border-bottom: 0; font-weight: bold; line-height: 100%; vertical-align: middle; font-family:Helvetica, Roboto, Arial, sans-serif; border-radius: 3px 3px 0 0;"><tbody><tr><td style="padding: 36px 48px; display: block;"><h1 style="font-family: Helvetica, Roboto, Arial, sans-serif; font-size: 30px; font-weight: 300; line-height: 150%; margin: 0; text-align: left; color: #ffffff;">用户注册成功</h1></td></tr></tbody></table></td></tr><tr><td align="center" valign="top"><table border="0" cellpadding="0" cellspacing="0" width="600"><tbody><tr><td valign="top" style="background-color: #fdfdfd;"><table border="0" cellpadding="20" cellspacing="0" width="100%"><tbody><tr><td valign="top" style="padding: 48px 48px 32px;"><div style="color: #4d4d4d; font-family: Helvetica, Roboto, Arial, sans-serif; font-size: 14px; line-height: 150%; text-align: left;"><p style="margin: 0 0 16px;">尊敬的 ' . $user_login . ' 您好！</p><p style="margin: 0 0 16px;">这个通知是为了确认您在 <a href="' . home_url() . '" style="color: #000000; font-weight: 600; text-decoration: none;" rel="noopener" target="_blank">' . get_option("blogname") . '</a> 上的账户已注册成功。</p><p style="margin: 0 0 16px;">用户名：' . $user_login . '</p><p style="margin: 0 0 16px;">邮  箱：' . $user_email . '</p><p style="margin: 0 0 16px;">密  码：' . $user_pass . '</p><p style="margin: 0 0 16px;">如果您未提出此请求，请联系网站管理员！</p><p style="margin: 0 0 16px;">感谢阅读。</p></div></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></div>';
				$headers = 'Content-Type: text/html; charset=' . get_option('blog_charset') . "\n";
				wp_mail( $user_email, '用户注册成功 - ' . get_bloginfo('name'), $message, $headers );
			}
		}
		echo json_encode( array( "status" => $status, "msg" => $msg ) );
	} else if ( $_POST['action'] == 'check.register.captcha' ){
		$user_login = sanitize_user( $_POST['user_login'] );
		$user_email = apply_filters( 'user_registration_email', $wpdb->escape( trim( $_POST['user_email'] ) ) );
		$user_pass  = $wpdb->escape( $_POST['user_pass'] );
			if ( empty( $user_login ) ) {
			$status = 0;
			$msg    = '请输入用户名';
		} else if ( !preg_match( $is_name, $user_login ) ) {
			$status = 0;
			$msg    = '用户名只能由字母数字或下划线组成';
		} else if ( strlen( $user_login ) < 6 ) {
			$status = 0;
			$msg    = '用户名长度不得小于6位';
		} else if ( strlen( $user_login ) > 10 ) {
			$status = 0;
			$msg    = '用户名长度不得大于10位';
		} else if ( username_exists( $user_login ) ) {
			$status = 0;
			$msg    = '该用户名已被注册';
		} else if ( empty( $user_email ) ) {
			$status = 0;
			$msg    = '请输入邮箱地址';
		} else if ( !preg_match( $is_email, $user_email ) ) {
			$status = 0;
			$msg    = '邮箱格式不正确';
		} else if ( email_exists( $user_email ) ) {
			$status = 0;
			$msg    = '该邮箱已被注册';
		} else if ( empty( $user_pass ) ) {
			$status = 0;
			$msg    = '请输入密码';
		} else if ( strlen( $user_pass ) < 6 ) {
			$status = 0;
			$msg    = '密码长度不得小于6位';
		} else {
			$originalcode = '0,1,2,3,4,5,6,7,8,9';
			$originalcode = explode(',',$originalcode);
			$countdistrub = 10;
			$counts	   = 6;
			for( $j = 0; $j < $counts; $j++ ){
				$captcha .= $originalcode[ rand( 0, $countdistrub-1 ) ];
			}
			session_start();
			$_SESSION['register_captcha']		= strtolower($captcha);
			$_SESSION['register_captcha_time']  = date("Y-m-d H:i:s");
			$_SESSION['register_email']			= $user_email;
			$message = '<div style="background-color: #f5f5f5; margin: 0; padding: 70px 0; width: 100%; -webkit-text-size-adjust: none;"><table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tbody><tr><td align="center" valign="top"><table border="0" cellpadding="0" cellspacing="0" width="600" style="background-color: #fdfdfd; border: 1px solid #dcdcdc; box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1); border-radius: 3px;"><tbody><tr><td align="center" valign="top"><table border="0" cellpadding="0" cellspacing="0" width="600" style="background-color: #008c95; color: #ffffff; border-bottom: 0; font-weight: bold; line-height: 100%; vertical-align: middle; font-family:Helvetica, Roboto, Arial, sans-serif; border-radius: 3px 3px 0 0;"><tbody><tr><td style="padding: 36px 48px; display: block;"><h1 style="font-family: Helvetica, Roboto, Arial, sans-serif; font-size: 30px; font-weight: 300; line-height: 150%; margin: 0; text-align: left; color: #ffffff;">用户注册验证码</h1></td></tr></tbody></table></td></tr><tr><td align="center" valign="top"><table border="0" cellpadding="0" cellspacing="0" width="600"><tbody><tr><td valign="top" style="background-color: #fdfdfd;"><table border="0" cellpadding="20" cellspacing="0" width="100%"><tbody><tr><td valign="top" style="padding: 48px 48px 32px;"><div style="color: #4d4d4d; font-family: Helvetica, Roboto, Arial, sans-serif; font-size: 14px; line-height: 150%; text-align: left;"><p style="margin: 0 0 16px;">尊敬的用户您好，您的验证码是：</p><p style="margin: 0 0 16px;color: #008c95;font-size: 32px;font-weight: 300;">' . $captcha . '</p><p style="margin: 0 0 16px;">验证码的有效期为5分钟，请在有效期内输入！</p><p style="margin: 0 0 16px;">感谢阅读。</p></div></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></div>';
			$headers = 'Content-Type: text/html; charset=' . get_option('blog_charset') . "\n";
			wp_mail( $user_email, '用户注册验证码 - ' . get_bloginfo('name'), $message, $headers );
			$status = 1;
			$msg    = '已发送验证码至邮箱，可能会出现在垃圾箱里哦~';
		}
		echo json_encode( array( "status" => $status, "msg" => $msg ) );
	} else if ( $_POST['action'] == 'check.password' ){
		$user_name  = $wpdb->escape( $_POST['user_name'] );
		$user_login = '';
		$user_email = '';
		if ( empty( $user_name ) ){
			$status = 0;
			$msg    = '请输入用户名或邮箱地址';
		} else if ( !preg_match( $is_name, $user_name ) ) {
			$status = 0;
			$msg    = '用户名只能由字母数字或下划线组成';
		} else if ( strlen( $user_name ) < 6 ) {
			$status = 0;
			$msg    = '用户名长度不得小于6位';
		} else if ( strlen( $user_name ) > 10 ) {
			$status = 0;
			$msg    = '用户名长度不得大于10位';
		} else if ( !is_email( $user_name ) ) {
			$user_login = $user_name;
			if ( !username_exists( $user_login ) ){
				$status = 0;
				$msg    = '该用户名暂未注册';
			} else {
				$user_data   = get_userdatabylogin( $user_login );
				$user_email = $user_data->user_email;
				if ( empty( $user_email ) ){
					$status = 0;
					$msg    = '该用户名暂未绑定邮箱';
				}
			}
		} else if ( is_email( $user_name ) ) {
			$user_email = $user_name;
			if ( !email_exists( $user_email ) ){
				$status = 0;
				$msg    = '该邮箱暂未注册';
			} else {
				$user_data  = get_user_by_email( $user_email );
				$user_login = $user_data->user_login;
			}
		}
		$key = $wpdb->get_var( $wpdb->prepare("SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login) );
		if ( empty($key) ) {
			$key = wp_generate_password(20, false); 
			$wpdb->update($wpdb->users, array('user_activation_key' => $key), array('user_login' => $user_login)); 
		}
		$verify = get_permalink( thememee_easylogin_page('page/easy-login.php')) . "?action=password&key=".md5('thememee'.$key)."&login=" . rawurlencode($user_login); 
		$message = '<div style="background-color: #f5f5f5; margin: 0; padding: 70px 0; width: 100%; -webkit-text-size-adjust: none;"><table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tbody><tr><td align="center" valign="top"><table border="0" cellpadding="0" cellspacing="0" width="600" style="background-color: #fdfdfd; border: 1px solid #dcdcdc; box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1); border-radius: 3px;"><tbody><tr><td align="center" valign="top"><table border="0" cellpadding="0" cellspacing="0" width="600" style="background-color: #008c95; color: #ffffff; border-bottom: 0; font-weight: bold; line-height: 100%; vertical-align: middle; font-family:Helvetica, Roboto, Arial, sans-serif; border-radius: 3px 3px 0 0;"><tbody><tr><td style="padding: 36px 48px; display: block;"><h1 style="font-family: Helvetica, Roboto, Arial, sans-serif; font-size: 30px; font-weight: 300; line-height: 150%; margin: 0; text-align: left; color: #ffffff;">账户密码重置</h1></td></tr></tbody></table></td></tr><tr><td align="center" valign="top"><table border="0" cellpadding="0" cellspacing="0" width="600"><tbody><tr><td valign="top" style="background-color: #fdfdfd;"><table border="0" cellpadding="20" cellspacing="0" width="100%"><tbody><tr><td valign="top" style="padding: 48px 48px 32px;"><div style="color: #4d4d4d; font-family: Helvetica, Roboto, Arial, sans-serif; font-size: 14px; line-height: 150%; text-align: left;"><p style="margin: 0 0 16px;">尊敬的 ' . $user_login . ' 您好！</p><p style="margin: 0 0 16px;">您刚刚发起了密码重置请求，请点击下方的按钮重置密码：</p><p style="margin: 0 0 16px;color: #008c95;font-weight: 300;"><a href="' . $verify . '" target="_blank" style="display: inline-block;padding: 10px 20px;border-radius: 2px;background: #008c95;color: #fff;text-decoration: none;">立即重置</a></p><p style="margin: 0 0 16px;"></p><p style="margin: 0 0 16px;">如果您未提出此请求，请联系网站管理员！</p><p style="margin: 0 0 16px;">感谢阅读。</p></div></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></div>';
		$headers = 'Content-Type: text/html; charset=' . get_option('blog_charset') . "\n";
		wp_mail( $user_email, '账户密码重置 - ' . get_bloginfo('name'), $message, $headers );
		echo json_encode( array( "status" => $status, "msg" => $msg ) );
	} else if ( $_POST['action'] == 'check.password.reset' ){
		$reset_key	  = $_POST['reset_key'];
		$user_login   = $_POST['user_login'];
		$new_password = $_POST['new_password'];
		$cfm_password = $_POST['cfm_password'];
		if (  empty( $new_password ) ){
			$status = 0;
			$msg    = '请输入新密码';
		} else if ( strlen( $new_password ) < 6 ) {
			$status = 0;
			$msg    = '密码长度不得小于6位';
		} else if ( empty( $cfm_password ) ) {
			$status = 0;
			$msg    = '请确认新密码';
		} else if ( $new_password !== $cfm_password ) {
			$status = 0;
			$msg    = '两次输入密码不一致';
		} else {
			$user_data  = $wpdb->get_row($wpdb->prepare("SELECT ID, user_login, user_email, user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login));
			$user_login = $user_data->user_login;
			$user_email = $user_data->user_email;
			if ( !empty($reset_key) && !empty($user_data) && md5('thememee'.$user_data->user_activation_key) == $reset_key ) {
				wp_set_password( $new_password, $user_data->ID );
				$key = wp_generate_password(20, false);
				$wpdb->update( $wpdb->users, array( 'user_activation_key' => $key ), array( 'user_login' => $user_login ) );
			}
			$message = '<div style="background-color: #f5f5f5; margin: 0; padding: 70px 0; width: 100%; -webkit-text-size-adjust: none;"><table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tbody><tr><td align="center" valign="top"><table border="0" cellpadding="0" cellspacing="0" width="600" style="background-color: #fdfdfd; border: 1px solid #dcdcdc; box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1); border-radius: 3px;"><tbody><tr><td align="center" valign="top"><table border="0" cellpadding="0" cellspacing="0" width="600" style="background-color: #008c95; color: #ffffff; border-bottom: 0; font-weight: bold; line-height: 100%; vertical-align: middle; font-family:Helvetica, Roboto, Arial, sans-serif; border-radius: 3px 3px 0 0;"><tbody><tr><td style="padding: 36px 48px; display: block;"><h1 style="font-family: Helvetica, Roboto, Arial, sans-serif; font-size: 30px; font-weight: 300; line-height: 150%; margin: 0; text-align: left; color: #ffffff;">密码修改成功</h1></td></tr></tbody></table></td></tr><tr><td align="center" valign="top"><table border="0" cellpadding="0" cellspacing="0" width="600"><tbody><tr><td valign="top" style="background-color: #fdfdfd;"><table border="0" cellpadding="20" cellspacing="0" width="100%"><tbody><tr><td valign="top" style="padding: 48px 48px 32px;"><div style="color: #4d4d4d; font-family: Helvetica, Roboto, Arial, sans-serif; font-size: 14px; line-height: 150%; text-align: left;"><p style="margin: 0 0 16px;">尊敬的 ' . $user_login . ' 您好！</p><p style="margin: 0 0 16px;">这个通知是为了确认您在 <a href="' . home_url() . '" style="color: #000000; font-weight: 600; text-decoration: none;" rel="noopener" target="_blank">' . get_option("blogname") . '</a> 上的账户密码已修改成功。</p><p style="margin: 0 0 16px;">用户名：' . $user_login . '</p><p style="margin: 0 0 16px;">新密码：' . $new_password . '</p><p style="margin: 0 0 16px;">如果您未提出此请求，请联系网站管理员！</p><p style="margin: 0 0 16px;">感谢阅读。</p></div></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></div>';
			$headers = 'Content-Type: text/html; charset=' . get_option('blog_charset') . "\n";
			wp_mail( $user_email, '密码修改成功 - ' . get_bloginfo('name'), $message, $headers );
			$status = 1;
			$msg    = '密码修改成功，正在跳转至登录页面...';
		}
		echo json_encode( array( "status" => $status, "msg" => $msg ) );
	}
}