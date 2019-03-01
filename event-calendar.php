<?php
/**
 * Plugin Name: Event Calendar
 * Plugin URI:  https://github.com/alinavalovenko/event-calendar
 * Description: Create new events and allow people add it to theirs schedule
 * Version:     1.0.0
 * Author:      Alina Valovenko
 * Author URI:  http://www.valovenko.pro
 * Text Domain: avec
 * Domain Path: /languages
 * License:     GPL2
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'AV_Event_Calendar' ) ) {

	if ( ! defined( 'AVEC_DIR_URL' ) ) {
		define( 'AVEC_DIR_URL', plugin_dir_url( __FILE__ ) );
	}
	if ( ! defined( 'AVEC_DIR_PATH' ) ) {
		define( 'AVEC_DIR_PATH', plugin_dir_path( __FILE__ ) );
	}

	class AV_Event_Calendar {

		public function __construct() {
			add_action( 'wp_enqueue_scripts', array( $this, 'avec_enqueue_scripts' ), 99 );
			if ( ! in_array( 'advanced-custom-fields/acf.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
				add_filter( 'acf/settings/path', array( $this, 'avec_acf_settings_path' ) );
				add_filter( 'acf/settings/dir', array( $this, 'avec_acf_settings_dir' ) );
				add_filter( 'acf/settings/show_admin', '__return_false' );
				include_once( plugin_dir_path( __FILE__ ) . 'assets/acf/acf.php' );
			}
			add_shortcode( 'av-calendar', array( $this, 'avec_calendar_render' ) );
			add_action( 'init', array( $this, 'avec_register_post_types' ) );
			add_action( 'publish_event', array( $this, 'avec_publish_event_callback' ), 10, 2 );
			add_action( 'init', array( $this, 'avec_translations' ) );
			add_action( 'manage_event_posts_custom_column', array( $this, 'avec_event_date_column' ), 10, 2 );
			add_filter( 'manage_event_posts_columns', array( $this, 'avec_set_event_date_column' ) );

			include_once 'include/controller.php';
			include_once 'include/class-ics.php';
		}

		/***
		 * Add translation files to WP Core
		 */
		function avec_translations() {
			load_plugin_textdomain( 'avec', false, 'event-calendar/languages' );
		}

		/***
		 * Connect scripts and styles
		 */
		function avec_enqueue_scripts() {
			wp_enqueue_style( 'avec-style', AVEC_DIR_URL . 'assets/styles.css' );
			wp_enqueue_script( 'jquery', 'https://code.jquery.com/jquery-3.3.1.min.js', '', '1.0.0', true );
			wp_enqueue_script( 'avec-scripts', AVEC_DIR_URL . 'assets/scripts.js', array( 'jquery' ), '1.0.0', true );
		}

		/***
		 * Customize acf uri path
		 *
		 * @param $path
		 *
		 * @return string
		 */
		function avec_acf_settings_path( $path ) {
			$path = AVEC_DIR_PATH . '/assets/acf/';

			return $path;
		}

		/***
		 * Customize acf directory path
		 *
		 * @param $dir
		 *
		 * @return string
		 */
		function avec_acf_settings_dir( $dir ) {
			$dir = AVEC_DIR_URL . 'assets/acf/';

			return $dir;
		}

		/***
		 * Register Event Post type
		 */
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

		/***
		 * Shortcode body render
		 *
		 * @return false|string
		 */
		function avec_calendar_render() {
			$output       = '';
			$current_year = date( "Y" );
			$events       = $this->avec_get_events_by_year();
			if ( ! empty( $events ) ) {
				ob_start();
				echo '<div class="calendar-wrap">';
				foreach ( $events as $year => $events_list ) {
					echo '<div class="calendar-year">';
					if ( $current_year == $year ) {
						echo '<a href="#avec-' . $year . '" class="avec-link-toggle opened">' . $year . '</a>';
						echo '<div id="avec-' . $year . '" class="event-table">';
					} else {
						echo '<a href="#avec-' . $year . '" class="avec-link-toggle">' . $year . '</a>';
						echo '<div id="avec-' . $year . '" class="event-table avec-hide">';
					}
					echo '<div class="event-table-head"><div>' . esc_html__( 'Date', 'avec' ) . '</div><div>' . esc_html__( 'Event', 'avec' ) . '</div><div>' . esc_html__( 'Link', 'avec' ) . '</div></div>';
					echo '<div class="event-table-body">';
					foreach ( $events_list as $event ) {
						echo '<div class="event-table-row">';
						$date        = get_field( 'avec_date', $event );
						$title       = $event->post_title;
						$description = get_field( 'avec_decription', $event );
						echo '<div class="avec-date-value">' . $date . '</div>';
						if ( time() < strtotime( $date ) ) {
							$download_link = get_post_meta( $event->ID, 'evec_download_link', true );
							echo '<div class="avec-title-value">' . esc_html__( $title, 'avec' ) . '<div class="avec-small">' . $description . '</div></div>';
							echo '<div class="avec-link-value"><a href="' . $download_link . '" target="_blank">' . esc_html__( 'add to calendar', 'avec' ) . '</a></div>';
						} else {
							$summary = get_field( 'avec_summary', $event );

							echo '<div class="avec-title-value"><a href="' . $summary . '" target="_blank">' . esc_html__( $title, 'avec' ) . '</a></div>';
							echo '<div></div>';
						}
						echo '</div>';
					}
					echo '</div>';
					echo '</div>';
					echo '</div>';
				}
				echo '</div>';
				$output = ob_get_contents();
				ob_end_clean();

			}

			return $output;
		}

		/***
		 * Sorting events by year
		 *
		 * @return array
		 */
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
			if ( ! empty( $events ) ) {
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
			}

			return $events_sorted;
		}

		/***
		 * Convert date of the even to string with year value
		 *
		 * @param $date
		 *
		 * @return string
		 */
		function avec_get_year_from_date( $date ) {
			$date = DateTime::createFromFormat( 'd.m.Y', $date );

			return $date->format( 'Y' );
		}

		/***
		 * Save link to the file to Database
		 *
		 * @param $post_id
		 * @param $ics_data
		 *
		 * @return string
		 */
		function avec_create_download_link( $post_id, $ics_data ) {
			$path      = wp_upload_dir();
			$file_name = 'avec-' . $post_id . '.ics';

			$ics_link = $path['url'] . '/' . $file_name;
			$ics_path = $path['path'] . DIRECTORY_SEPARATOR . $file_name;
			file_put_contents( $ics_path, $ics_data );
			update_post_meta( $post_id, 'evec_download_link', $ics_link );

			return $ics_link;
		}

		/***
		 * Create file according to event with file extension .ics
		 *
		 * @param $post_id
		 * @param $post
		 */
		public function avec_publish_event_callback( $post_id, $post ) {
			if ( isset( $_POST['acf'] ) ) {
				$ics = new ICS( array(
					'description' => $_POST['acf']['field_5c7515bf37d87'],
					'dtstart'     => $_POST['acf']['field_5c75147e53881'],
					'summary'     => $post->post_title,
				) );

				$this->avec_create_download_link( $post_id, $ics->to_string() );

			}
		}

		/***
		 * Add the custom columns to the event post type:
		 *
		 * @param $columns
		 *
		 * @return mixed
		 */
		function avec_set_event_date_column( $columns ) {
			unset( $columns['author'] );
			$columns['event_date'] = __( 'Event Date', 'avec' );
			return $columns;
		}

		/***
		 * Add the data to the custom columns for the event post type:
		 *
		 * @param $column
		 * @param $post_id
		 */
		function avec_event_date_column( $column, $post_id ) {
			switch ( $column ) {

				case 'event_date' :
					$event_date = get_post_meta($post_id, 'avec_date', true);
					if ( $event_date ) {
						echo date('d.m.Y',strtotime($event_date));
					} else {
						_e( 'Unable to get date of the event', 'avec' );
					}
					break;
			}
		}

	}

	new AV_Event_Calendar();
}