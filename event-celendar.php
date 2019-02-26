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
			add_shortcode( 'av-calendar', array( $this, 'avec_calendar_render' ) );
			add_action( 'init', array( $this, 'avec_register_post_types' ) );

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

		function avec_register_post_types() {
			$args = array(
				'labels'       => array(
					'name' => 'Event'
				),
				'public'       => true,
				'show_in_menu' => true,
				'rewrite'      => false,
				'supports'     => array( 'title' )
			);
			register_post_type( 'event', $args );
		}

		function avec_calendar_render() {
			global $post;
			$events = $this->avec_get_events_by_year();
			$today  = time();
			ob_start();
			echo '<div class="calendar-wrap">';
			foreach ( $events as $year => $events_list ) {
				echo '<div class="calendar-year">';
				echo '<a href="#avec-' . $year . '" class="avec-link-toggle">' . $year . '</a>';
				echo '<div class="event-table">';
				echo '<div class="event-table-head"><div>' . esc_html__( 'Date', 'avec' ) . '</div><div>' . esc_html__( 'Event', 'avec' ) . '</div><div>' . esc_html__( 'Link', 'avec' ) . '</div></div>';
				foreach ( $events_list as $event ) {
					$date        = get_field( 'avec_date', $event );
					$title       = $event->post_title;
					$description = get_field( 'avec_decription', $event );
					$summary     = get_field( 'avec_summary', $event );
					echo '<div>' . $date . '</div>';
					if ( time() < strtotime( $date ) ) {
						echo '<div>' . esc_html__( $title, 'avec' ) . '<small>' . $description . '</small></div>';
						echo '<div><a href="' . $this->avec_create_download_link( $event ) . '">' . esc_html__( 'add to calendar', 'avec' ) . '</a></div>';
					} else {
						echo '<div><a href="' . $summary . '" target="_blank">' . esc_html__( $title, 'avec' ) . '</a></div>';
						echo '<div></div>';
					}

				}
				echo '</div>';
				echo '</div>';
			}
			echo '</div>';
			$output = ob_get_contents();
			ob_end_clean();

			return $output;
		}

		function avec_get_events_by_year() {
			$events_sorted = array();
			$args          = array(
				'post_type'   => 'event',
				'numberposts' => '-1',
				'order'       => 'DESC',
				'orderby'     => 'avec_date',
				'post_status' => 'publish',
				'meta_key'    => 'avec_date'
			);

			$events = get_posts( $args );

			$last_ID   = $events[0]->ID;
			$last_year = $this->avec_get_year_from_date( get_field( 'avec_date', $last_ID ) );

			$first_ID   = end( $events )->ID;
			$first_year = $this->avec_get_year_from_date( get_field( 'avec_date', $first_ID ) );
			for ( $i = $last_year; $i >= $first_year; $i -- ) {
				foreach ( $events as $event ) {
					$event_year = $this->avec_get_year_from_date( get_field( 'avec_date', $event ) );
					if ( $i == $event_year ) {
						$events_sorted[ $i ][] = $event;
					}

				}
			}

			return $events_sorted;
		}

		function avec_get_year_from_date( $date ) {
			$date = DateTime::createFromFormat( 'd.m.Y', $date );

			return $date->format( 'Y' );
		}

		function avec_create_download_link( $event ) {
			$link = '#';

			return $link;
		}

	}

	new AV_Event_Calendar();
}