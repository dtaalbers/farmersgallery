<?php 

class Farmer { 

	private static $initiated = false;
    private static $plugin_slug = "farmersgallery";

	public static function init() {
		if (!self::$initiated) {
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
		register_activation_hook(__FILE__, array('Farmer', 'plugin_activation'));
		// Add hook to plugin deactivation event
		register_deactivation_hook(__FILE__, array('Farmer', 'plugin_deactivation'));
        add_action('save_post', array('Farmer', 'save_meta_data'));
        add_action('edit_form_after_title', function() {
            global $post, $wp_meta_boxes;
            do_meta_boxes(get_current_screen(), 'advanced', $post);
            unset($wp_meta_boxes[get_post_type($post)]['advanced']);
        });
        add_action( 'init', array($this, 'load_plugin_textdomain') );
	}

	private static function register_custom_post_type_album() {	  	  
	    register_post_type('album', 
		    array(
		        'labels' => array(
			        'name' => __('Farmer Albums', self::$plugin_slug),
			        'singular_name' => __('Farmer Album', self::$plugin_slug),
			        'add_new' => __('Add New', self::$plugin_slug ),
			        'add_new_item' => __('Add New Farmer Album', self::$plugin_slug),
			        'edit_item' => __('Edit Farmer Album', self::$plugin_slug),
			        'new_item' => __('New Farmer Album', self::$plugin_slug),
			        'view_item' => __('View Farmer Album', self::$plugin_slug),
			        'search_items' => __('Search Farmer Albums', self::$plugin_slug),
			        'not_found' => __('No Farmer Albums found', self::$plugin_slug),
			        'not_found_in_trash' => __('No Farmer Albums found in trash', self::$plugin_slug),
			        'parent_item_colon' => __('Parent Album:', self::$plugin_slug),
			        'menu_name' => __('Farmer Albums', self::$plugin_slug),
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
		// CSS files
		wp_register_style('main', FARMERSGALLERY_PLUGIN_URL.'css/style.css');
		wp_register_style('grid', FARMERSGALLERY_PLUGIN_URL.'css/grid.css');
        wp_register_style('font-awsome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css');        
		// Scripts
		wp_register_script('knockout', 'https://cdnjs.cloudflare.com/ajax/libs/knockout/3.4.0/knockout-min.js');
        wp_register_script('jquery-easing', FARMERSGALLERY_PLUGIN_URL.'js/jquery.easing.1.3.js');	
        wp_register_script('jquery-mousewheel', FARMERSGALLERY_PLUGIN_URL.'js/jquery.mousewheel.min.js');        
        wp_register_script('custom', FARMERSGALLERY_PLUGIN_URL.'js/custom.min.js');
        wp_register_script('jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js');   
	}
    


	private static function add_album_filter() {
		add_filter('manage_edit-album_columns', function() {
  		    $new_columns['cb'] = '<input type="checkbox" />';	 
		    $new_columns['title'] = __('Title', self::$plugin_slug);
		    $new_columns['author'] = __('Author', self::$plugin_slug);
            $new_columns['date'] = __('Date', self::$plugin_slug);      
	 	    return $new_columns;
	 	}, 10, 8);
	}

	public static function add_meta_box_albums() {
		add_action('add_meta_boxes', function() {
		    add_meta_box('image_id',
                __("Add images to this album", self::$plugin_slug),
		        array('Farmer', 'add_meta_box_albums_callback'),
                'album',
                'advanced',
                'high'
		    );
		});
	}
    
    public static function add_meta_box_albums_callback($post) {	        
        $data = get_post_meta( $post->ID, 'images', true );    
        if($data != "") {            
            $images = explode(",", $data);  
        }    
        wp_nonce_field('creating_images', 'album_nonce');        
        require_once(FARMERSGALLERY_PLUGIN_DIR.'views/metabox.php');
	}
    
    public static function save_meta_data($post_id) {     
        // Checks save status
        $is_autosave = wp_is_post_autosave( $post_id );
        $is_revision = wp_is_post_revision( $post_id );
        $is_valid_nonce = (isset( $_POST[ 'album_nonce' ]) && wp_verify_nonce( $_POST[ 'album_nonce' ], 'images')) ? 'true' : 'false';
        // Exits script depending on save status
        if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
            return;
        }
        // Checks for input and sanitizes/saves if needed
        if(isset( $_POST[ 'images' ])) {
            update_post_meta($post_id, 'images', $_POST[ 'images' ]);
        }
    }

	public static function plugin_activation () {
        
	}

	public static function plugin_deactivation () {

	}
}