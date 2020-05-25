<?php
/*
Plugin Name: Easy User Login
Plugin URI: https://zhongyuyu.cn/easy-user-login.html
Description: WordPress Free Easy User Login Plugin
Version: 1.0.0
Author: 中与雨
Author URI: https://zhongyuyu.cn/
*/

define( 'PLUGIN_VERSION', '1.0.0' );
define( 'PLUGIN_DIR', plugin_dir_url( __FILE__ ) );
define( 'PLUGIN_URL', plugins_url('', __FILE__) );
define( 'PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

function thememee_easylogin_static() {
	if ( get_permalink( thememee_easylogin_page('page/easy-login.php') ) ) {
		wp_enqueue_style( 'easylogin.css', PLUGIN_DIR . 'css/easy-login.css', array(), PLUGIN_VERSION, 'all' );
		wp_enqueue_script('jquery');
		wp_enqueue_script( 'easylogin.js', PLUGIN_DIR . 'js/easy-login.js', false, PLUGIN_VERSION, true );
	}
}
add_action('wp_enqueue_scripts', 'thememee_easylogin_static',20,1);

function thememee_easylogin_javascript() {
	echo '<script>window._easylogin = {url: "' . PLUGIN_URL . '", login: "' . get_permalink( thememee_easylogin_page('page/easy-login.php') ) .'?action=login' . '"}</script>';
}
add_action( 'wp_footer', 'thememee_easylogin_javascript' );

function thememee_easylogin_page( $template ) {
	global $wpdb;
	$page_id = $wpdb->get_var( $wpdb->prepare( "SELECT `post_id` FROM `$wpdb->postmeta`, `$wpdb->posts` WHERE `post_id` = `ID` AND `post_status` = 'publish' AND `meta_key` = '_wp_page_template' AND `meta_value` = %s LIMIT 1;", $template ) );
	return $page_id;
}

function thememee_easylogin_register_session() {
	if ( !session_id() ) {
		session_start();
	}
}
add_action( 'init','thememee_easylogin_register_session' );

class PageTemplater {
	private static $instance;
	protected $templates;
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new PageTemplater();
		}
		return self::$instance;
	}
	private function __construct() {
		$this->templates = array();
		if ( version_compare( floatval( get_bloginfo( 'version' ) ), '4.7', '<' ) ) {
			add_filter( 'page_attributes_dropdown_pages_args', array( $this, 'register_project_templates' ) );
		} else {
			add_filter( 'theme_page_templates', array( $this, 'add_new_template' ) );
		}
		add_filter( 'wp_insert_post_data', array( $this, 'register_project_templates' ) );
		add_filter( 'template_include', array( $this, 'view_project_template') );
		$this->templates = array(
			'page/easy-login.php' => '简易登录页面',
		);
	}
	public function add_new_template( $posts_templates ) {
		$posts_templates = array_merge( $posts_templates, $this->templates );
		return $posts_templates;
	}
	public function register_project_templates( $atts ) {
		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );
		$templates = wp_get_theme()->get_page_templates();
		if ( empty( $templates ) ) {
			$templates = array();
		}
		wp_cache_delete( $cache_key , 'themes');
		$templates = array_merge( $templates, $this->templates );
		wp_cache_add( $cache_key, $templates, 'themes', 1800 );
		return $atts;
	}
	public function view_project_template( $template ) {
		global $post;
		if ( !$post ) {
			return $template;
		}
		if ( !isset( $this->templates[get_post_meta( $post->ID, '_wp_page_template', true )] ) ) {
			return $template;
		}
		$file = PLUGIN_PATH. get_post_meta( $post->ID, '_wp_page_template', true );
		if ( file_exists( $file ) ) {
			return $file;
		} else {
			echo $file;
		}
		return $template;
	}
}
add_action( 'plugins_loaded', array( 'PageTemplater', 'get_instance' ) );