jQuery(document).ready(function($) {

// 错误提示
var _sign_error_tips
function sign_error(str){
	if( !str ) return false
	_sign_error_tips && clearTimeout(_sign_error_tips)
	$('.page-template-easy-login .content h1').after('<div class="tips error">' + str + '</div>').slideDown();
	$('.page-template-easy-login .error').slideDown();
	_sign_error_tips = setTimeout(function(){
		$('.page-template-easy-login .error').slideUp();
		_sign_error_tips = setTimeout(function(){
			$('.page-template-easy-login .error').remove();
		}, 500)
	}, 3000)
}

// 成功提示
var _sign_success_tips
function sign_success(str){
	if( !str ) return false
	_sign_success_tips && clearTimeout(_sign_success_tips)
	$('.page-template-easy-login .content h1').after('<div class="tips success">' + str + '</div>').slideDown();
	$('.page-template-easy-login .success').slideDown();
	_sign_success_tips = setTimeout(function(){
		$('.page-template-easy-login .success').slideUp();
		_sign_error_tips = setTimeout(function(){
			$('.page-template-easy-login .success').remove();
		}, 500)
	}, 3000)
}

// 登录提交
$('.page-template-easy-login .sign-in').on('click', function(){
	$('.page-template-easy-login .sign-in').html('<div class="loading"></div>').attr( 'disabled', true ).fadeTo( 'slow', 0.9 );
	$.ajax(
		_easylogin.url+'/check.php',
		{
			type: 'POST',
			dataType: 'json',
			data: {
				action: "check.login",
				username: $('input[name="username"]').val(),
				password: $('input[name="password"]').val(),
			},
			success: function (data) {
				if ( data.status === 0 ) {
					if (data.msg) {
						setTimeout(function(){
							sign_error(data.msg);
							$('.page-template-easy-login .sign-in').html("立即登录");
						}, 500)
						setTimeout(function(){
							$('.page-template-easy-login .sign-in').attr( 'disabled', false ).fadeTo( 'slow', 1 );
						}, 3000)
					}
					return false;
				} else {
					setTimeout( function () {
						sign_success(data.msg);
						setTimeout( function () {
							location.reload();
						}, 500 );
					}, 500 );
				}
			}
		}
	);
});

// 注册提交
$('.page-template-easy-login .sign-up').on('click', function(){
	$('.page-template-easy-login .sign-up').html('<div class="loading"></div>').attr( 'disabled', true ).fadeTo( 'slow', 0.9 );
	$('.page-template-easy-login .send-captcha').attr( 'disabled', true );
	$.ajax(
		_easylogin.url+'/check.php',
		{
			type: 'POST',
			dataType: 'json',
			data: {
				action: "check.register",
				user_login: $('input[name="user_login"]').val(),
				user_email: $('input[name="user_email"]').val(),
				user_pass: $('input[name="user_pass"]').val(),
				user_captcha: $('input[name="user_captcha"]').val(),
			},
			success: function (data) {
				if ( data.status === 0 ) {
					if (data.msg) {
						setTimeout(function(){
							sign_error(data.msg);
							$('.page-template-easy-login .sign-up').html("立即注册");
						}, 500)
						setTimeout(function(){
							$('.page-template-easy-login .sign-up').attr( 'disabled', false ).fadeTo( 'slow', 1 );
							$('.page-template-easy-login .send-captcha').attr( 'disabled', false );
						}, 3000)
					}
					return false;
				} else {
					setTimeout( function () {
						sign_success(data.msg);
						setTimeout( function () {
							location.reload();
						}, 500 );
					}, 500 );
				}
			}
		}
	);
});

// 用户注册 - 验证码
$('.page-template-easy-login .send-captcha').bind('click', function() {
	$('.page-template-easy-login .send-captcha').html('<div class="loading"></div>').attr( 'disabled', true );
	$('.page-template-easy-login .sign-up').attr( 'disabled', true );
	$.ajax(
		_easylogin.url + "/check.php",
		{
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'check.register.captcha',
				user_login: $('input[name="user_login"]').val(),
				user_email: $('input[name="user_email"]').val(),
				user_pass: $('input[name="user_pass"]').val(),
			},
			success: function (data) {
				if ( data.status === 0 ) {
					if (data.msg) {
						setTimeout(function(){
							sign_error(data.msg);
							$('.page-template-easy-login .send-captcha').html("发送验证码");
						}, 500)
						setTimeout(function(){
							$('.page-template-easy-login .sign-up').attr( 'disabled', false ).fadeTo( 'slow', 1 );
							$('.page-template-easy-login .send-captcha').attr( 'disabled', false );
						}, 3000)
					}
					return false;
				} else {
					sign_success(data.msg);
					var countdown = 60;
					settime()
					function settime() {
						if (countdown === 0) {
							$('.page-template-easy-login .sign-up').attr( 'disabled', false ).fadeTo( 'slow', 1 );
							$('.page-template-easy-login .send-captcha').attr( 'disabled', false ).removeClass('disabled');
							$('.page-template-easy-login .send-captcha').html("重新发送");
							countdown = 60;
							return;
						} else {
							setTimeout(function(){
							$('.page-template-easy-login .sign-up').attr( 'disabled', false ).fadeTo( 'slow', 1 );
							}, 3000)
							$('.page-template-easy-login .send-captcha').attr( 'disabled', true ).addClass('disabled');
							$('.page-template-easy-login .send-captcha').html("重新发送(" + countdown + ")");
							countdown--;
						}
						setTimeout(function () {
							settime();
						}, 1000)
					}
				}
			}
		}
	);
});

// 忘记密码
$('.page-template-easy-login .recover').on('click', function(){
	$('.page-template-easy-login .recover').html('<div class="loading"></div>').attr( 'disabled', true ).fadeTo( 'slow', 0.9 );
	$.ajax(
		_easylogin.url+'/check.php',
		{
			type: 'POST',
			dataType: 'json',
			data: {
				action: "check.password",
				user_name: $('input[name="user_name"]').val(),
			},
			success: function (data) {
				if ( data.status === 0 ) {
					if (data.msg) {
						setTimeout(function(){
						    sign_error(data.msg);
							$('.page-template-easy-login .recover').html('找回密码');
						}, 500)
						setTimeout(function(){
							$('.page-template-easy-login .recover').attr( 'disabled', false ).fadeTo( 'slow', 1 );
						}, 3000)
					}
					return false;
				} else {
					$('.page-template-easy-login .recover').attr( 'disabled', true );
					$('.page-template-easy-login .tips').slideUp();
					sign_success("确认链接已经发送到您的邮箱，请查收并确认。");
					$('.page-template-easy-login .recover').html('重新发送');
					setTimeout(function(){
						$('.page-template-easy-login .tips').html('如操作失误，请重新发送确认链接至您的邮箱。').slideDown();
						$('.page-template-easy-login .recover').attr( 'disabled', false ).fadeTo( 'slow', 1 );
					}, 3500);
				}
			}
		}
	);
});

// 重置密码
$('.page-template-easy-login .reset').on('click', function(){
	$('.page-template-easy-login .reset').attr( 'disabled', true ).fadeTo( 'slow', 0.9 ).html('<div class="loading"></div>');
	$.ajax(
		_easylogin.url+'/check.php',
		{
			type: 'POST',
			dataType: 'json',
			data: {
				action: "check.password.reset",
				reset_key: $('input[name="reset_key"]').val(),
				user_login: $('input[name="reset_login"]').val(),
				new_password: $('input[name="new_password"]').val(),
				cfm_password: $('input[name="cfm_password"]').val(),
			},
			success: function (data) {
				if ( data.status === 0 ) {
					if (data.msg) {
						setTimeout(function(){
						    sign_error(data.msg);
							$('.page-template-easy-login .reset').html('修改密码');
						}, 500)
						setTimeout(function(){
							$('.page-template-easy-login .reset').attr( 'disabled', false ).fadeTo( 'slow', 1 );
						}, 3000)
					}
					return false;
				} else {
					setTimeout( function () {
						sign_success(data.msg);
						setTimeout( function () {
							location.href=_easylogin.sign;
						}, 3000 );
					}, 500 );
				}
			}
		}
	);
});

console.log('\n' + ' %c EasyLogin Designed by 中与雨 %c https://zhongyuyu.cn ' + '\n', 'color: #fadfa3; background: #030307; padding:5px 0;', 'background: #fadfa3; padding:5px 0;');
});