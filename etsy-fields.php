<?php
/**
 * Include and setup custom metaboxes and fields.
 *
 * @category Etsy Importer
 * @package  Metaboxes
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress
 */

/**
 * Get the bootstrap! If using the plugin from wordpress.org, REMOVE THIS!
 */
if ( file_exists( dirname( __FILE__ ) . '/cmb2/init.php' ) ) {
	require_once dirname( __FILE__ ) . '/cmb2/init.php';
} elseif ( file_exists( dirname( __FILE__ ) . '/CMB2/init.php' ) ) {
	require_once dirname( __FILE__ ) . '/CMB2/init.php';
}

/**
 * Define the metabox and field configurations.
 *
 * @param  array $meta_boxes
 * @return array
 */
function etsy_metaboxes( array $meta_boxes ) {

	// Start with an underscore to hide fields from custom fields list
	$prefix = '_etsy_product_';

	$meta_boxes['etsy_metaboxes'] = array(
		'id'         => 'etsy_product_info',
		'title'      => __( 'Product Information', 'etsy' ),
		'object_types'      => array( 'etsy_products', ), // Post type
		'context'    => 'normal',
		'priority'   => 'high',
		'show_names' => true, // Show field names on the left
		// 'cmb_styles' => true, // Enqueue the CMB stylesheet on the frontend
		'fields'     => array(
			array(
				'name' => __( 'Price', 'etsy' ),
				'id'   => $prefix . 'price',
				'type' => 'text_small',
			),
			array(
				'name' => __( 'Etsy Link', 'etsy' ),
				'id'   => $prefix . 'url',
				'type' => 'text',
			),
			array(
				'name' => __( 'Production Year', 'etsy' ),
				'id'   => $prefix . 'made',
				'type' => 'text_medium',
			),
			array(
				'name' => __( 'Made For', 'etsy' ),
				'id'   => $prefix . 'made_for',
				'type' => 'text_medium',
			),
		)
	);

	// Add other metaboxes as needed

	return $meta_boxes;
}
add_filter( 'cmb2_meta_boxes', 'etsy_metaboxes' );


class Etsy_Options_Admin {

    /**
     * Option key, and option page slug
     * @var string
     */
    private $key = 'etsy_options';

    /**
     * Array of metaboxes/fields
     * @var array
     */
    protected $option_metabox = array();

    /**
     * Options Page title
     * @var string
     */
    protected $title = '';

    /**
     * Options Page hook
     * @var string
     */
    protected $options_page = '';

    /**
     * Constructor
     * @since 0.1.0
     */
    public function __construct() {
        // Set our title
        $this->title = __( 'Etsy Importer Settings', 'etsy_importer' );

        // Set our CMB2 fields
        $this->fields = array(
            array(
                'name' => __( 'API Key', 'etsy_importer' ),
                'desc' => $this->api_key_description(),
                'id'   => 'etsy_importer_api_key',
                'type' => 'text'
            ),
            array(
                'name' => __( 'Store ID', 'etsy_importer' ),
                'desc' => $this->store_id_description(),
                'id'   => 'etsy_importer_store_id',
                'type' => 'text'
            ),
            array(
                'name' => __( 'Disable updating of post status on product import', 'etsy_importer' ),
                'desc' => __( 'When left unchecked, any product in your Etsy shop which is no longer active will be automatically set to draft status; any product in your Etsy shop which was previously inactive and has become active will be automatically set to publish status.', 'etsy_importer' ),
                'id'   => 'etsy_importer_status_checkbox',
                'type' => 'checkbox'
            ),
        );
    }

    /**
     * Initiate our hooks
     * @since 0.1.0
     */
    public function hooks() {
        add_action( 'admin_init', array( $this, 'init' ) );
        add_action( 'admin_menu', array( $this, 'add_options_page' ) );
    }

    /**
     * Register our setting to WP
     * @since  0.1.0
     */
    public function init() {
        register_setting( $this->key, $this->key );
    }

    /**
     * Add menu options page
     * @since 0.1.0
     */
    public function add_options_page() {
        $this->options_page = add_menu_page( $this->title, $this->title, 'manage_options', $this->key, array( $this, 'admin_page_display' ) );
    }

    /**
     * Admin page markup. Mostly handled by CMB2
     * @since  0.1.0
     */
    public function admin_page_display() {
        ?>
        <div class="wrap cmb2_options_page <?php echo $this->key; ?>">
            <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
            <?php cmb2_metabox_form( $this->option_metabox(), $this->key ); ?>
        </div>
        <?php
    }

    /**
     * Defines the theme option metabox and field configuration
     * @since  0.1.0
     * @return array
     */
    public function option_metabox() {
        return array(
            'id'         => 'option_metabox',
            'show_on'    => array( 'key' => 'options-page', 'value' => array( $this->key, ), ),
            'show_names' => true,
            'fields'     => $this->fields,
        );
    }

    /**
     * Retrieve the existing API Key from preivously registered settings
     * @return mixed Description text
     */
    public function api_key_description() {

    	$options    = get_option( 'etsy_store_settings' );
		$old_value  = $options['settings_etsy_api_key'];
		$plugin_dir = plugins_url( '/', __FILE__ );

    	if( isset( $old_value ) ) {
	    	$output = sprintf( __( 'Used our plugin before? If you don\'t see your existing API Key in the field above, don\'t worry - you\'ll see it right here: %s', 'etsy_importer' ), $old_value );
    	} else {
    		$output = __( 'Enter your API Key above.', 'etsy_importer' );
    	}

    	$output .= '<p class="cmb2-metabox-description">' . sprintf( __( 'Need help? <a href="%s" class="thickbox">Click here</a> for a walkthrough on how to setup your Etsy Application.', 'etsy_importer' ), '#TB_inline?&height=600&width=800&inlineId=etsy-api-instructions' ) . '</p>';
    	$output .= '<div id="etsy-api-instructions" style="display: none; width:800px;">';
    	$output .= '<p>' . sprintf( __( 'In order to import your products, you first need to register an application with Etsy.  <a href="%s" target="_blank">Click here</a> to begin registering your application.  You should see a screen similar to the image below:', 'etsy_importer' ), 'https://www.etsy.com/developers/register' ) . '<br />';
    	$output .= '<img src="' . $plugin_dir . 'screenshot-create-an-app01.jpg" /></p>';
    	$output .= '<p>' . __( 'Once you have created your app, click "Apps You\'ve Made" in the sidebar and select your new app.  On the app detail page, copy the value in the Keystring input field.  This is your API Key.', 'etsy_importer' ) . '<br />';
		$output .= '<img src="' . $plugin_dir . 'screenshot-create-an-app02.jpg" /></p>';
		$output .= '</div>';

		return $output;

    }

    /**
     * Retrieve the existing Store ID from preivously registered settings
     * @return mixed Description text
     */
    public function store_id_description() {

    	$options    = get_option( 'etsy_store_settings' );
		$old_value  = $options['settings_etsy_store_id'];
		$plugin_dir = plugins_url( '/', __FILE__ );

    	if( isset( $old_value ) ) {
	    	$output = sprintf( __( 'Used our plugin before? If you don\'t see your existing Store ID in the field above, don\'t worry - you\'ll see it right here: %s', 'etsy_importer' ), $old_value );
    	} else {
    		$output = __( 'Enter your Store ID above.', 'etsy_importer' );
    	}

    	$output .= '<p class="cmb2-metabox-description">' . sprintf( __( 'Need help? <a href="%s" class="thickbox">Click here</a> for a walkthrough on how to find your Etsy store ID.', 'etsy_importer' ), '#TB_inline?&height=420&width=800&inlineId=etsy-store-id-instructions' ) . '</p>';
    	$output .= '<div id="etsy-store-id-instructions" style="display: none;">';
    	$output .= '<p>' . __( 'Visit your Etsy store\'s front page.  View the page source and copy the number in the URL of the "al:ios:url" property.  This is your shop ID.', 'etsy_importer' ) . '<br />';
		$output .= '<img src="' . $plugin_dir . 'screenshot-shop-id01.jpg" /></p>';
		$output .= '</div>';

		return $output;
    }

    /**
     * Public getter method for retrieving protected/private variables
     * @since  0.1.0
     * @param  string  $field Field to retrieve
     * @return mixed          Field value or exception is thrown
     */
    public function __get( $field ) {

        // Allowed fields to retrieve
        if ( in_array( $field, array( 'key', 'fields', 'title', 'options_page' ), true ) ) {
            return $this->{$field};
        }
        if ( 'option_metabox' === $field ) {
            return $this->option_metabox();
        }

        throw new Exception( 'Invalid property: ' . $field );
    }

}

// Get it started
$GLOBALS['Etsy_Options_Admin'] = new Etsy_Options_Admin();
$GLOBALS['Etsy_Options_Admin']->hooks();

/**
 * Wrapper function around cmb2_get_option
 * @since  0.1.0
 * @param  string  $key Options array key
 * @return mixed        Option value
 */
function etsy_options_get_option( $key = '' ) {
    global $Etsy_Options_Admin;
    return cmb2_get_option( $Etsy_Options_Admin->key, $key );
}
