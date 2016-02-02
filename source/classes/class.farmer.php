<?php 

class Farmer { 

	private static $initiated = false;
	private static $meta_box_title = "Add images to this album";

	public static function init() {
		if ( ! self::$initiated ) {
			// Were initiating
			self::$initiated = true;
			// Add the wordpress hooks
			self::init_hooks();
			// Register custom post type
			self::register_custom_post_type_album();
			// Register custom files
			self::register_custom_plugin_files();
			// Add custom meta box to post type album
			self::add_meta_box_albums();
		}
	}

	private static function init_hooks() {
		// Add hook to plugin activation event
		register_activation_hook( __FILE__, array( 'Farmer', 'plugin_activation' ) );
		// Add hook to plugin deactivation event
		register_deactivation_hook( __FILE__, array( 'Farmer', 'plugin_deactivation' ) );
	}

	private static function register_custom_post_type_album() {	  	  
	    register_post_type( 'album', 
		    array(
		        'labels' => array(
			        'name' => _x( 'Farmer Albums', 'album' ),
			        'singular_name' => _x( 'Farmer Album', 'album' ),
			        'add_new' => _x( 'Add New', 'album' ),
			        'add_new_item' => _x( 'Add New Farmer Album', 'album' ),
			        'edit_item' => _x( 'Edit Farmer Album', 'album' ),
			        'new_item' => _x( 'New Farmer Album', 'album' ),
			        'view_item' => _x( 'View Farmer Album', 'album' ),
			        'search_items' => _x( 'Search Farmer Albums', 'album' ),
			        'not_found' => _x( 'No Farmer Albums found', 'album' ),
			        'not_found_in_trash' => _x( 'No Farmer Albums found in Trash', 'album' ),
			        'parent_item_colon' => _x( 'Parent Album:', 'album' ),
			        'menu_name' => _x( 'Farmer Albums', 'album' ),
			    ),
		        'hierarchical' => true,
		        'description' => 'Farmer Albums',
		        'supports' => array( 'title', 'editor', 'thumbnail', 'custom-fields', 'revisions', 'page-attributes' ),
		        //'taxonomies' => array( 'category' ),
		        'public' => true,
		        'show_ui' => true,
		        'show_in_menu' => true,
		        'menu_position' => 5,
		        'menu_icon' => 'dashicons-format-gallery',
		        'show_in_nav_menus' => true,
		        'publicly_queryable' => true,
		        'exclude_from_search' => false,
		        'has_archive' => true,
		        'query_var' => true,
		        'can_export' => true,
		        'rewrite' => true,
		        'capability_type' => 'post'
		    )
	    );
		// Create album filter
		self::add_album_filter();
	}

	private static function register_custom_plugin_files() {
		// Main css file
		wp_register_style( 'main', FARMERSGALLERY_PLUGIN_URL . 'css/main.css' );
		// Bootstrap grid css	
		wp_register_style( 'grid', FARMERSGALLERY_PLUGIN_URL . 'css/grid.css' );
		// Knockout script
		wp_register_script( 'knockout', 'https://cdnjs.cloudflare.com/ajax/libs/knockout/3.4.0/knockout-min.js' );		
	}

	private static function add_album_filter() {
		add_filter('albums', function() {
  		    $new_columns['cb'] = '<input type="checkbox" />';	     
		    $new_columns['id'] = __('ID');
		    $new_columns['title'] = _x('Farmer Album', 'column name');
		    $new_columns['images'] = __('Images');
		    $new_columns['author'] = __('Author');     
	 	    return $new_columns;
	 	}, 10, 8);
	}

	private static function add_meta_box_albums() {
		add_action( 'add_meta_boxes', function() {
		    add_meta_box(
		        'image_id',
		        __( self::$meta_box_title, 'myplugin_textdomain' ),
		        function() {
		        	require_once( FARMERSGALLERY_PLUGIN_DIR . 'views/metabox.php' );
		        }
		    );
		});
	}

	public static function plugin_activation () {
		
	}

	public static function plugin_deactivation () {

	}
}