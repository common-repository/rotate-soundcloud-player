<?php
/**
 * Rotate Soundcloud Player
 *
 *
 * @package   Rotate_Soundcloud_Player
 * @author    Gavin Rehkemper <gavin@gavinr.com>
 * @license   Copyright 2016 Gavin Rehkemper
 * @link      http://www.gavinr.com/rotate-soundcloud-player-wordpress-widget
 * @copyright 2016 Gavin Rehkemper
 *
 * @wordpress-plugin
 * Plugin Name:       Rotate Soundcloud Player
 * Plugin URI:        http://www.gavinr.com/rotate-soundcloud-player-wordpress-widget/
 * Description:       A widget that features the latest track in a soundcloud playlist, with the option to rotate to older tracks. Great for podcasts.
 * Version:           1.1
 * Author:            Gavin Rehkemper
 * Author URI:        http://gavinr.com
 * Text Domain:       rotate-soundcloud-player
 * License:           Copyright 2016 Gavin Rehkemper
 * Domain Path:       /lang
 */
 
 // Prevent direct file access
if ( ! defined ( 'ABSPATH' ) ) {
	exit;
}

class Rotate_Soundcloud_Player extends WP_Widget {

    /**
     * Unique identifier for your widget.
     *
     *
     * The variable name is used as the text domain when internationalizing strings
     * of text. Its value should match the Text Domain file header in the main
     * widget file.
     *
     * @since    1.0.0
     *
     * @var      string
     */
    protected $widget_slug = 'rotate-soundcloud-player';

	/*--------------------------------------------------*/
	/* Constructor
	/*--------------------------------------------------*/

	/**
	 * Specifies the classname and description, instantiates the widget,
	 * loads localization files, and includes necessary stylesheets and JavaScript.
	 */
	public function __construct() {

		// load plugin text domain
		add_action( 'init', array( $this, 'widget_textdomain' ) );

		// Hooks fired when the Widget is activated and deactivated
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		parent::__construct(
			$this->get_widget_slug(),
			__( 'Rotate Soundcloud Player', $this->get_widget_slug() ),
			array(
				'classname'  => $this->get_widget_slug().'-class',
				'description' => __( 'Soundcloud player to focus on each track individually.', $this->get_widget_slug() )
			)
		);

		// Register admin styles and scripts
		add_action( 'admin_print_styles', array( $this, 'register_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ) );

		// Register site styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_scripts' ) );

		// Refreshing the widget's cached output with each new post
		add_action( 'save_post',    array( $this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
		add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );

	} // end constructor


    /**
     * Return the widget slug.
     *
     * @since    1.0.0
     *
     * @return    Plugin slug variable.
     */
    public function get_widget_slug() {
        return $this->widget_slug;
    }

	/*--------------------------------------------------*/
	/* Widget API Functions
	/*--------------------------------------------------*/

	/**
	 * Outputs the content of the widget.
	 *
	 * @param array args  The array of form elements
	 * @param array instance The current instance of the widget
	 */
	public function widget( $args, $instance ) {

		
		// Check if there is a cached output
		$cache = wp_cache_get( $this->get_widget_slug(), 'widget' );

		if ( !is_array( $cache ) )
			$cache = array();

		if ( ! isset ( $args['widget_id'] ) )
			$args['widget_id'] = $this->id;

		if ( isset ( $cache[ $args['widget_id'] ] ) )
			return print $cache[ $args['widget_id'] ];
		
		$cur_arg = array(
			'title'	=> $instance['title'],
			'soundcloud_username'	=> $instance['soundcloud_username'],
			'soundcloud_user_id' => $instance['soundcloud_user_id'],
			'client_id'	=> $instance['client_id'],
			'client_secret'	=> $instance['client_secret'],
			'playlist'	=> $instance['playlist'],
			'show_play_count'	=> $instance['show_play_count'],
			'show_download_count'	=> $instance['show_download_count'],
			'show_favoritings_count'	=> $instance['show_favoritings_count']
		);
		extract( $cur_arg );


		extract( $args, EXTR_SKIP );

		$widget_string = $before_widget;

		// TODO: Here is where you manipulate your widget's values based on their input fields
		ob_start();
		include( plugin_dir_path( __FILE__ ) . 'views/widget.php' );
		$widget_string .= ob_get_clean();
		$widget_string .= $after_widget;


		$cache[ $args['widget_id'] ] = $widget_string;

		wp_cache_set( $this->get_widget_slug(), $cache, 'widget' );

		print $widget_string;

	} // end widget
	
	
	public function flush_widget_cache() 
	{
    	wp_cache_delete( $this->get_widget_slug(), 'widget' );
	}

	// public function get_userid($userName, $clientId) {
	// 	$retId = "";
	// 	if($userName != "" && $clientId != "") {
	// 		$cId =  $clientId;
	// 		$sUn =  $userName;
	// 		$url = trim("http://api.soundcloud.com/users?q=" . $sUn . "&client_id=" . $cId);
	// 		$ch = curl_init($url);
	// 		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	// 		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // most hosts do not include list of ssl valids
	// 		$response_body = curl_exec($ch);
	// 		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	// 		if (intval($status) != 200) {
	// 			// nothing - error
	// 		} else {
	// 			$response = json_decode($response_body);
	// 			$retId = $response[0]->id;
	// 		}
	// 	}
	// 	return $retId;
	// }
	/**
	 * Processes the widget's options to be saved.
	 *
	 * @param array new_instance The new instance of values to be generated via the update.
	 * @param array old_instance The previous instance of values before the update.
	 */
	public function update( $new_instance, $old_instance ) {

		function get_userid($userName, $clientId) {
			$retId = "";
			if($userName != "" && $clientId != "") {
				$cId =  $clientId;
				$sUn =  $userName;
				$url = trim("http://api.soundcloud.com/users?q=" . $sUn . "&client_id=" . $cId);
				$ch = curl_init($url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // most hosts do not include list of ssl valids
				$response_body = curl_exec($ch);
				$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				if (intval($status) != 200) {
					// nothing - error
				} else {
					$response = json_decode($response_body);
					$retId = $response[0]->id;
				}
			}
			return $retId;
		}

		$instance = $old_instance;

		$instance['title'] = strip_tags($new_instance['title']);

		if(strip_tags($new_instance['soundcloud_username']) !== strip_tags($instance['soundcloud_username'])) {
			$new_instance['playlist'] = '';
		}
		$instance['soundcloud_username'] = strip_tags($new_instance['soundcloud_username']);
		$instance['client_id'] = strip_tags($new_instance['client_id']);
		$instance['soundcloud_user_id'] = get_userid($instance['soundcloud_username'], $instance['client_id']);
		$instance['client_secret'] = strip_tags($new_instance['client_secret']);

		$playlistInput = strip_tags($new_instance['playlist']);
		if($playlistInput != '') {
			$instance['playlist'] = $playlistInput;
		} else {
			$instance['playlist'] = 'allUser';
		}
		

		$instance['show_play_count'] = strip_tags($new_instance['show_play_count']);
		$instance['show_download_count'] = strip_tags($new_instance['show_download_count']);
		$instance['show_favoritings_count'] = strip_tags($new_instance['show_favoritings_count']);

		return $instance;

	} // end widget

	/**
	 * Generates the administration form for the widget.
	 *
	 * @param array instance The array of keys and values for the widget.
	 */
	public function form( $instance ) {

		// TODO: Define default values for your variables
		$instance = wp_parse_args(
			(array) $instance
		);

		// TODO: Store the values of the widget in their own variable

		// Display the admin form
		include( plugin_dir_path(__FILE__) . 'views/admin.php' );

	} // end form

	/*--------------------------------------------------*/
	/* Public Functions
	/*--------------------------------------------------*/

	/**
	 * Loads the Widget's text domain for localization and translation.
	 */
	public function widget_textdomain() {

		// TODO be sure to change 'rotate-soundcloud-player' to the name of *your* plugin
		load_plugin_textdomain( $this->get_widget_slug(), false, plugin_dir_path( __FILE__ ) . 'lang/' );

	} // end widget_textdomain

	/**
	 * Fired when the plugin is activated.
	 *
	 * @param  boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
	 */
	public function activate( $network_wide ) {
		// TODO define activation functionality here
	} // end activate

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @param boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
	 */
	public function deactivate( $network_wide ) {
		// TODO define deactivation functionality here
	} // end deactivate

	/**
	 * Registers and enqueues admin-specific styles.
	 */
	public function register_admin_styles() {

		wp_enqueue_style( $this->get_widget_slug().'-admin-styles', plugins_url( 'css/admin.css', __FILE__ ) );

	} // end register_admin_styles

	/**
	 * Registers and enqueues admin-specific JavaScript.
	 */
	public function register_admin_scripts() {

		wp_enqueue_script( $this->get_widget_slug().'-admin-script', plugins_url( 'js/admin.js', __FILE__ ), array('jquery') );

	} // end register_admin_scripts

	/**
	 * Registers and enqueues widget-specific styles.
	 */
	public function register_widget_styles() {

		wp_enqueue_style( $this->get_widget_slug().'-widget-styles', plugins_url( 'css/widget.css', __FILE__ ) );

	} // end register_widget_styles

	/**
	 * Registers and enqueues widget-specific scripts.
	 */
	public function register_widget_scripts() {

		wp_enqueue_script( $this->get_widget_slug().'-script', plugins_url( 'js/widget.js', __FILE__ ), array('jquery') );

	} // end register_widget_scripts

} // end class

add_action( 'widgets_init', create_function( '', 'register_widget("Rotate_Soundcloud_Player");' ) );
