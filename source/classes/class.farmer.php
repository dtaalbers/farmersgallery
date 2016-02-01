<?php 

class Farmer { 

	private static $initiated = false;

	public static function init() {
		if ( ! self::$initiated ) {
			// Were initiating
			self::$initiated = true;
			// Add the wordpress hooks
			self::init_hooks();
			// Register custom post type
			self::register_custom_post_type_album();
		}
	}

	private static function init_hooks() {
		// No hooks yet
	}

	private static function register_custom_post_type_album() {	 
	    $labels = array(
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
	    );	 
	    $args = array(
	        'labels' => $labels,
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
	    );
	  	add_filter('albums', 'album_columns', 10, 8);	  
	  	function album_columns($album_columns) {
		    $new_columns['cb'] = '<input type="checkbox" />';	     
		    $new_columns['id'] = __('ID');
		    $new_columns['title'] = _x('Farmer Album', 'column name');
		    $new_columns['images'] = __('Images');
		    $new_columns['author'] = __('Author');     
		 	    return $new_columns;
		}	 
	    register_post_type( 'album', $args );
	}

	public static function plugin_activation () {

	}

	public static function plugin_deactivation () {

	}
}