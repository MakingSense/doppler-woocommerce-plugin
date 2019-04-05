<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.fromdoppler.com/
 * @since      1.0.0
 *
 * @package    Doppler_For_Woocommerce
 * @subpackage Doppler_For_Woocommerce/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Doppler_For_Woocommerce
 * @subpackage Doppler_For_Woocommerce/admin
 * @author     Doppler LLC <info@fromdoppler.com>
 */
class Doppler_For_Woocommerce_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	private $doppler_service;

	private $connectionStatus;

	private $credentials;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, $doppler_service ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->doppler_service = $doppler_service;
		$this->connectionStatus = $this->checkConnectionStatus();

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/doppler-for-woocommerce-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/doppler-for-woocommerce-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Registers the admin menu
	 */
	public function dplrwoo_init_menu() {

		add_menu_page(
			__('Doppler for WooCommerce', 'doppler-for-woocommerce'),
		    __('Doppler for WooCommerce', 'doppler-for-woocommerce'),
			'manage_options',
			'doppler_for_woocommerce_menu',
			array($this, "dplrwoo_admin_page"),
			plugin_dir_url( __FILE__ ) . 'img/icon-doppler-menu.png'
		);
	
	}

	/*
	public function dplrwoo_init_submenues(){

		if( $this->connectionStatus === true ){

			add_submenu_page(
				'doppler_for_woocommerce_menu',
				__('Settings', 'doppler-for-woocommerce'),
				__('Settings', 'doppler-for-woocommerce'),
				'manage_options',
				'doppler_for_woocommerce_menu',
				array($this, 'dplrwoo_admin_page'));
	
			add_submenu_page(
				'doppler_for_woocommerce_menu',
				__('View lists', 'doppler-for-woocommerce'),
				__('View lists', 'doppler-for-woocommerce'),
				'manage_options',
				'doppler_for_woocommerce_menu_lists',
				array($this, 'dplrwoo_lists_page'));
	
			add_submenu_page(
				'doppler_for_woocommerce_menu',
				__('Fields mapping', 'doppler-for-woocommerce'),
				__('Fields mapping', 'doppler-for-woocommerce'),
				'manage_options',
				'doppler_for_woocommerce_menu_mapping',
				array($this, 'dplrwoo_mapping_page'));

		}

	}
	*/

	/**
	 * Shows the admin settings screen
	 */
	public function dplrwoo_admin_page(){
		
		include('partials/doppler-for-woocommerce-settings.php');

	}

	/**
	 * Shows the Fields Mapping screen
	 */
	public function dplrwoo_mapping_page(){

		$fields = $this->getCheckoutFields();

		var_dump($fields);

	}


	/**
	 * Register the plugin settings and fields for doppler_for_woocommerce_menu.
	 */
	public function dplrwoo_settings_init(){

		// Add the section to doppler_for_woocommerce_menu settings so we can add our
		// fields to it
		add_settings_section(
			'dplrwoo_setting_section',
			'Example settings section in reading',
			array($this,'eg_setting_section_callback_function'),
			'doppler_for_woocommerce_menu'
		);

		// register a new field in the "dplrwoo_setting_section" section, inside the "doppler_for_woocommerce_menu" page
		//@id: Slug-name to identify the field. Used in the 'id' attribute of tags.
		//@title: Formatted title of the field. Shown as the label for the field during output.
		//@callback: Function that fills the field with the desired form inputs. The function should echo its output.
		//@page: The slug-name of the settings page on which to show the section (general, reading, writing, ...).
		//@section: The slug-name of the section of the settings page in which to show the box. Default value: 'default'
		//@args: Extra arguments used when outputting the field.
		//	@label_for: When supplied, the setting title will be wrapped in a <label> element, its for attribute populated with this value.
		// 	@class: CSS Class to be added to the <tr> element when the field is output.
		add_settings_field(
			'dplrwoo_user', // as of WP 4.6 this value is used only internally
			// use $args' label_for to populate the id inside the callback
			__( 'User Email', 'doppler-for-woocommerce' ),
			array($this,'display_user_field'),
			'doppler_for_woocommerce_menu',
			'dplrwoo_setting_section',
			[
			'label_for' => 'dplrwoo_user',
			'class' => 'dplrwoo_user_row',
			//'wporg_custom_data' => 'custom',
			]
		);

		add_settings_field(
			'dplrwoo_key', // as of WP 4.6 this value is used only internally
			// use $args' label_for to populate the id inside the callback
			__( 'API Key', 'doppler-for-woocommerce' ),
			array($this,'display_key_field'),
			'doppler_for_woocommerce_menu',
			'dplrwoo_setting_section',
			[
			'label_for' => 'dplrwoo_key',
			'class' => 'dplrwoo_key_row',
			//'wporg_custom_data' => 'custom',
			]
		);

		// Register the fields
		register_setting( 'doppler_for_woocommerce_menu', 'dplrwoo_user' );
		register_setting( 'doppler_for_woocommerce_menu', 'dplrwoo_key' );

	}

	/**
	 * Shows user field.
	 */
	function display_user_field( $args ){

		$option = get_option( 'dplrwoo_user' );
		?>
			<input type="text" value="<?php echo $option ?>" name="dplrwoo_user" />
		<?php
	
	}

	/**
	 * Shows API Key field
	 */
	function display_key_field( $args ){
		
		$option = get_option( 'dplrwoo_key' );
		?>
			<input type="text" value="<?php echo $option ?>" name="dplrwoo_key" />
		<?php
	
	}

	/**
	 * Example for section text.
	 */
	function eg_setting_section_callback_function( $args ) {
		
		?>
			<p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Follow the white rabbit.', 'wporg' ); ?></p>
		<?php
	
	}

	/**
	 * Handles ajax connection with API
	 * used by "connect" button in dopper-for-woocommerce-settings.php
	 */
	public function dplrwoo_api_connect(){
		
		$connected = $this->doppler_service->setCredentials(['api_key' => $_POST['key'], 'user_account' => $_POST['user']]);
		echo ($connected)? 1:0;
		exit();

	}

	/**
	 * Check connection status.
	 * If user and key are not stored returns false.
	 * If user and key are stored checks if transient exists.
	 * If transient exits congrants you are connected.
	 * If transient doesnt exists calls api with dprwoo_api_connect and saves transient to avoid more calls.
	 * IMPORTANT: Don't forget to delete transient when pressing "disconnect" button in plugin settings.
	 */
	public function checkConnectionStatus(){

		$user = get_option('dplrwoo_user');
		$key = get_option('dplrwoo_key');

		if( !empty($user) && !empty($key) ){

			$this->credentials = array('api_key' => $key, 'user_account' => $user);

			/*
			
			//Too complex approach?
			//Why dont just check if user has credentials (connected)
			//and if api is offline just show a warning but keep user as connected?
			// (By user connected it means submenues are shown and disconnect button available in settings)

			$connection_status = get_transient('_dplrwoo_connection_status');
			
			if( $connection_status == 1 ){
				
				return true;
			
			}else{
				
				$connected = $this->doppler_service->setCredentials(['api_key' => $key, 'user_account' => $user]);
				
				if( $connected == 1 ){
					set_transient( '_dplrwoo_connection_status', 1, 3600 );
					return true;
				}

				return false;
			}

			*/

			return true;

		}

		$this->credentials = null;

		return false;

	}
	
	public function getCheckoutFields(){

		if ( ! class_exists( 'WC_Session' ) ) {
			include_once( WP_PLUGIN_DIR . '/woocommerce/includes/abstracts/abstract-wc-session.php' );
		}

		/*
		* First lets start the session. You cant use here WC_Session directly
		* because it's an abstract class. But you can use WC_Session_Handler which
		* extends WC_Session
		*/
		WC()->session = new WC_Session_Handler;

		/*
		* Next lets create a customer so we can access checkout fields
		* If you will check a constructor for WC_Customer class you will see
		* that if you will not provide user to create customer it will use some
		* default one. Magic.
		*/
		WC()->customer = new WC_Customer;

		/*
		* Done. You can browse all chceckout fields (including custom ones)
		*/
		return WC()->checkout->checkout_fields;

	}
	
}
