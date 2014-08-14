<?php
/*
Plugin Name: Admin Featured Thumbnail
Plugin URI: http://www.wphigh.com/portfolio/admin-featured-thumbnail-wordpress-plugin
Description: Add post thumbnails to admin list of posts.
Version: 1.0.0
Author: wphigh
Author URI: http://www.wphigh.com
License: GPLv2 or later
Text Domain: aft
*/

class Aft_Admin_Featured_Thumbnail {
	
	/**
	 * Current class instance.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var object $instance
	 */
	private static $instance;
	
	/**
	 * Prevent to instance directly.
	 *
	 * @since 1.0.0
	 * @private
	 */
	private function __construct() {}

	/**
	 * Prevent to clone.
	 *
	 * @since 1.0.0
	 * @access private
	 */	
	private function __clone() {}
	
	/**
	 * Get instance only once.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return object	Current class instance.
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}
		
		return self::$instance;
	}
	
	/**
	 * Run
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function run() {		
		add_action( 'plugins_loaded', 										array( $this, 'load_plugin_textdomain' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ) , 	array( $this, 'donate_link' ) );
		
 		global $pagenow;
		if ( 'edit.php' != $pagenow )
			return;
		
		// Add hooks
		add_action( 'admin_init', array( $this, 'add_hooks' ) );
	}
	
	/**
	 * Add hooks
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function add_hooks() {
		$post_types = get_post_types( array( 'public' => true ) );
		unset( $post_types['attachment'] );
		if ( empty( $post_types ) )
			return;
		
		foreach ( $post_types as $post_type ) {
			add_filter( "manage_{$post_type}_posts_columns",			array( $this, 'thumbnail_column' ) );
			add_action( "manage_{$post_type}_posts_custom_column",		array( $this, 'show_thumbnail' ), 10, 2 );
		}
		
		add_action( 'admin_enqueue_scripts',							array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'admin_print_styles',								array( $this, 'admin_header_print_styles' ) );
		add_action( 'admin_print_footer_scripts',						array( $this, 'admin_print_footer_scripts' ) );		
	}
	
	/**
	 * Load plugin textdomain for transration
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'aft', false, basename( dirname( __FILE__ ) ) . '/languages' );
	}
	
	/**
	 * Add donate link to plugin action links
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $actions
	 * @return array
	 */
	public function donate_link( $actions ) {
		$actions['settings'] = '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=JM4997SM3VK8Y">' . __( 'Buy Me A Beer', 'aft' ) . '</a>';
		return $actions;		
	}
	
	/**
	 * Add thumbnail column
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array  $columns An array of column names.	 
	 * @return array
	 */
	public function thumbnail_column( $columns ) {
		$columns['aft_featured_thumbnail'] = __( 'Thumbnail', 'aft' );
		return $columns;
	}
	
	/**
	 * Show post featured image
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $column_name The name of the column to display.
	 * @param int    $post_id     The current post ID.	 
	 * @return void
	 */
	public function show_thumbnail( $column_name, $post_id ) {
		if ( 'aft_featured_thumbnail' != $column_name )
			return;
		
		$tid = get_post_thumbnail_id( $post_id );
		if ( empty( $tid ) )
			return;
			
		$image_attributes = wp_get_attachment_image_src( $tid, 'full' );
		$url = $image_attributes[0];
		
		// Print thumbnail elements
		printf( '<a href="%1$s" class="%2$s">%3$s</a>', 
			esc_url( $url ),
			'aft-magnific-link',
			get_the_post_thumbnail( $post_id, 'thumbnail' )
		);
	}
	
	/**
	 * Enqueue magnigic scripts
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts( $hook ) {
		wp_enqueue_style( 'aft-magnific', plugins_url( 'magnific-popup-min.css', __FILE__ ) );
		wp_enqueue_script( 'aft-magnific', plugins_url( 'jquery.magnific-popup.min.js', __FILE__ ), array( 'jquery' ), '0.9.9', true );
	}
	
	/**
	 * Print style code
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function admin_header_print_styles() {
		$css = '.aft_featured_thumbnail img { width: 40px;height: auto;vertical-align: middle; border-radius: 5px; } .aft-magnific-link{ margin-left: 20px; display: inline-block; }';
		wp_add_inline_style( 'wp-admin', $css );
	}
	
	/**
	 * Print script code at the footer
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function admin_print_footer_scripts() {
?>
<script type="text/javascript">
jQuery(document).ready(function($) {
	$('.wp-list-table').magnificPopup({
		delegate: '.aft-magnific-link',
		type: 'image',
		gallery:{
			enabled:true
		}
	});
});	
</script>
<?php
	}
	
}


// Run
if ( is_admin() ) {
	Aft_Admin_Featured_Thumbnail::get_instance()->run();
}