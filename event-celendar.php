<?php
/*
	Plugin Name: Event Calendar
	Description: Create new events and allow people add it to theirs schedule
	Version: 1.0
	Author: Alina Valovenko
	Author URI: http://www.valovenko.pro
	License: GPL2
	Domain: avec
*/
if ( ! class_exists( 'AV_Event_Calendar' ) ) {

	if ( ! defined( 'AVEC_DIR_URL' ) ) {
		define( 'AVEC_DIR_URL', plugin_dir_url( __FILE__ ) );
	}
	if ( ! defined( 'AVEC_DIR_PATH' ) ) {
		define( 'AVEC_DIR_PATH', plugin_dir_path( __FILE__ ) );
	}
	include_once 'include/controller.php';

	class AV_Event_Calendar {

		public function __construct() {
			add_action( 'admin_menu', array( $this, 'avec_add_admin_page' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'avec_enqueue_scripts' ), 99 );
			// check for plugin using plugin name
			if ( ! in_array( 'advanced-custom-fields/acf.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
				add_filter( 'acf/settings/path', array( $this, 'avec_acf_settings_path' ) );
				add_filter( 'acf/settings/dir', array( $this, 'avec_acf_settings_dir' ) );
				//add_filter('acf/settings/show_admin', '__return_false');
				include_once( plugin_dir_path( __FILE__ ) . '/acf/acf.php' );
			}
			add_action( 'init', array( $this, 'avec_acf_init' ) );
		}

		function avec_add_admin_page() {

		}

		function avec_enqueue_scripts() {
			wp_enqueue_style( 'avec-style', AVEC_DIR_URL . 'assets/styles.css' );
			wp_enqueue_script( 'jquery', 'https://code.jquery.com/jquery-3.3.1.min.js', '', '1.0.0', true );
			wp_enqueue_script( 'avec-scripts', AVEC_DIR_URL . 'assets/scripts.js', array( 'jquery' ), '1.0.0', true );
		}

		function avec_acf_settings_path( $path ) {
			$path = AVEC_DIR_PATH . '/acf/';
			return $path;
		}

		function avec_acf_settings_dir( $dir ) {
			$dir = AVEC_DIR_URL . '/acf/';
			return $dir;
		}

		function avec_acf_init() {
			$option_page = acf_add_options_page( array(
				'page_title' => __( 'Event Calendar', 'avec' ),
				'menu_title' => __( 'Event Calendar', 'avec' ),
				'menu_slug'  => 'event-calendar-page',
			) );
		}
	}

	new AV_Event_Calendar();
}