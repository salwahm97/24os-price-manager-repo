<?php
/**
* Plugin Name: 24os Price Manager
* Plugin URI: http://www.24os.de/24os-price-manager
* Author URI: http://www.24os.de
* Author: 24os.de, salwahm97
* Version: 1.0.4
* Description: Manager Price Menu. Developed by salwahm97 (https://github.com/salwahm97).
*/

    if(!defined("ABSPATH"))
        exit;

    if(!defined("OS24_PRICE_MANAGER_PLUGIN_DIR_PATH"))  
        define("OS24_PRICE_MANAGER_PLUGIN_DIR_PATH", plugin_dir_path(__FILE__));

    if(!defined("OS24_PRICE_MANAGER_PLUGIN_URL"))
        define("OS24_PRICE_MANAGER_PLUGIN_URL", plugins_url()."/24os-price-manager");  

    if(!defined("OS24_PRICE_MANAGER_CUSTOM_POST_TYPE"))
        define("OS24_PRICE_MANAGER_CUSTOM_POST_TYPE", "24os_price_manager"); 

    if(!defined("OS24_PRICE_MANAGER_CUSTOM_TAXONOMY_CAT"))
        define("OS24_PRICE_MANAGER_CUSTOM_TAXONOMY_CAT", "24os_price_manager_categories"); 

    if(!defined("OS24_PRICE_MANAGER_CUSTOM_DEFAULT_CAT_1_OPTION"))
        define("OS24_PRICE_MANAGER_CUSTOM_DEFAULT_CAT_1_OPTION", "24os_price_manager_default_category_1"); 
    if(!defined("OS24_PRICE_MANAGER_CUSTOM_DEFAULT_CAT_SLUG_1_OPTION"))
        define("OS24_PRICE_MANAGER_CUSTOM_DEFAULT_CAT_SLUG_1_OPTION", "24os_price_manager_default_category_slug_1"); 

    if(!defined("OS24_PRICE_MANAGER_CUSTOM_PERMISSIONS_OPTION"))
        define("OS24_PRICE_MANAGER_CUSTOM_PERMISSIONS_OPTION", "24os_price_manager_admin_permissions_option"); 

    if(!defined("OS24_PRICE_MANAGER_CUSTOM_LANGUAGE_OPTION"))
        define("OS24_PRICE_MANAGER_CUSTOM_LANGUAGE_OPTION", "24os_price_manager_language_option"); 

    if(!defined("OS24_PRICE_MANAGER_SHORTCODE"))
        define("OS24_PRICE_MANAGER_SHORTCODE", "24osPriceMenu"); 

    if(!defined("OS24_PRICE_MANAGER_CUSTOM_POST_META"))
        define("OS24_PRICE_MANAGER_CUSTOM_POST_META", "24os_price_manager_insert_image"); 

// create custom table database on active plugin
function os24_price_manager_active()
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    // table '24os_price_manager_menu'
    $table_name_menu = $wpdb->prefix . '24os_price_manager_menu';

    $sql = "CREATE TABLE IF NOT EXISTS ". $table_name_menu ."
        (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL DEFAULT 0,
            user_id bigint(20) NULL,
            menu TEXT NULL,  
            trash BOOLEAN NOT NULL DEFAULT 0,
            active BOOLEAN NOT NULL DEFAULT 0,
            PRIMARY KEY (id)
        )". $charset_collate .";";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    // table '24os_price_manager_category'
    $table_name_category = $wpdb->prefix . '24os_price_manager_category';

    $sql = "CREATE TABLE IF NOT EXISTS ". $table_name_category ."
        (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            menu_id bigint(20) NULL,
            category_name TEXT NULL,
            category_desc TEXT NULL, 
            sort int(11) NOT NULL DEFAULT 0,
            category_img LONGTEXT NULL,
            PRIMARY KEY (id)
        )". $charset_collate .";";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    // table '24os_price_manager_item'
    $table_name_item = $wpdb->prefix . '24os_price_manager_item';

    $sql = "CREATE TABLE IF NOT EXISTS ". $table_name_item ."
        (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            category_id bigint(20) NULL,
            item_num int(11) NULL,
            item_name TEXT NULL,
            item_desc TEXT NULL,
            item_allergens TEXT NULL,
            item_prices TEXT NULL,
            sort int(11) NOT NULL DEFAULT 0,
            item_img LONGTEXT NULL,
            PRIMARY KEY (id)
        )". $charset_collate .";";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    // table '24os_price_manager_allergen'
    $table_name_allergen = $wpdb->prefix . '24os_price_manager_allergen';

    $sql = "CREATE TABLE IF NOT EXISTS ". $table_name_allergen ."
        (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            allergen VARCHAR(20) NULL,
            allergen_desc TEXT NULL,
            PRIMARY KEY (id)
        )". $charset_collate .";";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    // table '24os_price_manager_statistics'
    $table_name_statistics = $wpdb->prefix . '24os_price_manager_statistics';

    $sql = "CREATE TABLE IF NOT EXISTS ". $table_name_statistics ."
        (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            menu_id bigint(20) NULL,
            visitor_IP TEXT NULL,     
            visitor_agent TEXT NULL,
            visit_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        )". $charset_collate .";";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    // add custom permissions option for this plugin by add option   
    if(current_user_can( 'manage_options' )) // if is admin
    {
        $permissions_option['author'] = true;
        $permissions_option['administrator'] = true;
        $permissions_option['editor'] = true;
        $permissions_option['subscriber'] = true;
        $permissions_option['contributor'] = true;
        add_option(OS24_PRICE_MANAGER_CUSTOM_PERMISSIONS_OPTION, json_encode($permissions_option));
    }
    
    // add custom language option for this plugin by add option   
    add_option(OS24_PRICE_MANAGER_CUSTOM_LANGUAGE_OPTION, 'en');

}
register_activation_hook(__FILE__, "os24_price_manager_active");

// delete custom table database on uninstall plugin
function os24_price_manager_uninstall()
{
    global $wpdb;
    $table_name_menu = $wpdb->prefix . '24os_price_manager_menu';

    //delete posts
    $post_ids_deleted = $wpdb->get_results(
        "SELECT post_id from ". $table_name_menu
    ); 
    foreach ($post_ids_deleted as $post_id_deleted) {
        wp_delete_post( $post_id_deleted->post_id, false);
        
    }
    //drop 24os_price_manager_menu table
    $sql = "DROP TABLE IF EXISTS ".$table_name_menu;
    $wpdb->query($sql);

    //drop 24os_price_manager_category table
    $table_name_category = $wpdb->prefix . '24os_price_manager_category';
    $sql = "DROP TABLE IF EXISTS ".$table_name_category;
    $wpdb->query($sql);

    //drop 24os_price_manager_item table
    $table_name_item = $wpdb->prefix . '24os_price_manager_item';
    $sql = "DROP TABLE IF EXISTS ".$table_name_item;
    $wpdb->query($sql);

    //drop 24os_price_manager_allergen table
    $table_name_allergen = $wpdb->prefix . '24os_price_manager_allergen';
    $sql = "DROP TABLE IF EXISTS ".$table_name_allergen;
    $wpdb->query($sql);

    //drop 24os_price_manager_statistics table
    $table_name_statistics = $wpdb->prefix . '24os_price_manager_statistics';
    $sql = "DROP TABLE IF EXISTS ".$table_name_statistics;
    $wpdb->query($sql);
    
    // deleted all custom post type categories
    $terms = get_terms( array(
        'taxonomy' => OS24_PRICE_MANAGER_CUSTOM_TAXONOMY_CAT,
        'fields' => 'ids',
        'hide_empty' => false
    ) );
    if(count($terms) > 0)
    {
        foreach( $terms as $term_id ) {
            wp_delete_term( $term_id, OS24_PRICE_MANAGER_CUSTOM_TAXONOMY_CAT );
        }
    }

    // delete permissions option
    delete_option(OS24_PRICE_MANAGER_CUSTOM_PERMISSIONS_OPTION);
    // delete language option
    delete_option(OS24_PRICE_MANAGER_CUSTOM_LANGUAGE_OPTION);
    
    // delete POST_META for all post
    delete_post_meta_by_key( OS24_PRICE_MANAGER_CUSTOM_POST_META );
}
register_uninstall_hook(__FILE__, "os24_price_manager_uninstall");
// register_deactivation_hook(__FILE__, "os24_price_manager_uninstall");

function os24_price_manager_hide_category_delete_buttom()
{
    $tag_id_cat_1 = get_term_by('name', get_option(OS24_PRICE_MANAGER_CUSTOM_DEFAULT_CAT_1_OPTION), OS24_PRICE_MANAGER_CUSTOM_TAXONOMY_CAT)->term_id;
    
    echo '<style>
            #tag-'.$tag_id_cat_1.' td .row-actions > .delete { display: none; }
        </style>';
}
function os24_price_manager_hide_category_delete_buttom_from_term_page()
{
    $tag_id_cat_1 = get_term_by('name', get_option(OS24_PRICE_MANAGER_CUSTOM_DEFAULT_CAT_1_OPTION), OS24_PRICE_MANAGER_CUSTOM_TAXONOMY_CAT)->term_id;
    if(isset($_GET['tag_ID']) && $_GET['tag_ID'] == $tag_id_cat_1){
        echo '<style>
            #delete-link { display: none; }
        </style>';
    }
    
}

function os24_price_manager_load_scripts() {
    global $parent_file;
    global $pagenow; 
    if($pagenow == 'edit-tags.php' && isset($_GET["taxonomy"], $_GET["post_type"]) && $_GET["taxonomy"] == OS24_PRICE_MANAGER_CUSTOM_TAXONOMY_CAT && $_GET["post_type"] == OS24_PRICE_MANAGER_CUSTOM_POST_TYPE)
    {
        add_action('admin_head', "os24_price_manager_hide_category_delete_buttom");
    }
    if($pagenow == 'term.php' && isset($_GET["taxonomy"], $_GET["post_type"]) && $_GET["taxonomy"] == OS24_PRICE_MANAGER_CUSTOM_TAXONOMY_CAT && $_GET["post_type"] == OS24_PRICE_MANAGER_CUSTOM_POST_TYPE)
    {
        add_action('admin_head', "os24_price_manager_hide_category_delete_buttom_from_term_page");
    }

    if(isset($_GET["page"]))
    {
        if ('24os-price-manager' == $parent_file) 
        {
            ?>
            <style>
                @media screen and (max-width: 960px) {
                    #wpbody-content{
                        width: 98% !important;
                    }
                }
            </style>
            <?php
            $current_page = sanitize_key(wp_unslash($_GET["page"]));
            if('24os-price-manager' == $current_page)
            { 
                wp_register_script( 'os24_price_manager_ng_main', OS24_PRICE_MANAGER_PLUGIN_URL . '/main/main.77c2cd952d2cbc08.js' );
                os24_price_manager_add_rest_nonce_script('os24_price_manager_ng_main');
                wp_register_script( 'os24_price_manager_ng_polyfills', OS24_PRICE_MANAGER_PLUGIN_URL . '/main/polyfills.b9294cef6dd4a4bb.js' );
                wp_register_script( 'os24_price_manager_ng_runtime', OS24_PRICE_MANAGER_PLUGIN_URL . '/main/runtime.a82dfb2c4c09aafb.js' );
                wp_register_style( 'os24_price_manager_ng_styles', OS24_PRICE_MANAGER_PLUGIN_URL . '/main/styles.f5f03eddccccb64a.css' );
                wp_register_style( 'os24_price_manager_ng_icon', OS24_PRICE_MANAGER_PLUGIN_URL . '/main/icon.css' );

            }
            elseif('24os-price-manager-menu' == $current_page) 
            {
                wp_register_script( 'os24_price_manager_menu_ng_main', OS24_PRICE_MANAGER_PLUGIN_URL . '/menu/main.39015712c24b0983.js' );
                os24_price_manager_add_rest_nonce_script('os24_price_manager_menu_ng_main');
                wp_register_script( 'os24_price_manager_menu_ng_polyfills', OS24_PRICE_MANAGER_PLUGIN_URL . '/menu/polyfills.89a401cc5806782e.js' );
                wp_register_script( 'os24_price_manager_menu_ng_runtime', OS24_PRICE_MANAGER_PLUGIN_URL . '/menu/runtime.000e77d5744c8ec2.js' );
                wp_register_style( 'os24_price_manager_menu_ng_styles', OS24_PRICE_MANAGER_PLUGIN_URL . '/menu/styles.342d70874ff22c38.css' );
                wp_register_style( 'os24_price_manager_menu_ng_icon', OS24_PRICE_MANAGER_PLUGIN_URL . '/menu/icon.css' );

            }
            elseif('24os-price-manager-settings' == $current_page) 
            {
                wp_register_script( 'os24_price_manager_settings_ng_main', OS24_PRICE_MANAGER_PLUGIN_URL . '/settings/main.70deee0af368723d.js' );
                os24_price_manager_add_rest_nonce_script('os24_price_manager_settings_ng_main');
                wp_register_script( 'os24_price_manager_settings_ng_polyfills', OS24_PRICE_MANAGER_PLUGIN_URL . '/settings/polyfills.667ae9c27a870f7c.js' );
                wp_register_script( 'os24_price_manager_settings_ng_runtime', OS24_PRICE_MANAGER_PLUGIN_URL . '/settings/runtime.1d17a0f02de393cb.js' );
                wp_register_style( 'os24_price_manager_settings_ng_styles', OS24_PRICE_MANAGER_PLUGIN_URL . '/settings/styles.342d70874ff22c38.css' );
                wp_register_style( 'os24_price_manager_settings_ng_icon', OS24_PRICE_MANAGER_PLUGIN_URL . '/settings/icon.css' );

            }
            elseif('24os-price-manager-recovery' == $current_page) 
            {
                wp_register_script( 'os24_price_manager_recovery_ng_main', OS24_PRICE_MANAGER_PLUGIN_URL . '/recovery/main.8f697032b5a83f3c.js' );
                os24_price_manager_add_rest_nonce_script('os24_price_manager_recovery_ng_main');
                wp_register_script( 'os24_price_manager_recovery_ng_polyfills', OS24_PRICE_MANAGER_PLUGIN_URL . '/recovery/polyfills.c37d3e02ad57373f.js' );
                wp_register_script( 'os24_price_manager_recovery_ng_runtime', OS24_PRICE_MANAGER_PLUGIN_URL . '/recovery/runtime.a4c4eab95093605f.js' );
                wp_register_style( 'os24_price_manager_recovery_ng_styles', OS24_PRICE_MANAGER_PLUGIN_URL . '/recovery/styles.342d70874ff22c38.css' );
                wp_register_style( 'os24_price_manager_recovery_ng_icon', OS24_PRICE_MANAGER_PLUGIN_URL . '/recovery/icon.css' );

            }
        }
        
    }
}
add_action( 'admin_enqueue_scripts', 'os24_price_manager_load_scripts' );


function os24_price_manager_main_attach_ng_cb(){ 
    echo do_shortcode("[price_manager_app]");
}
function os24_price_manager_categories_cb(){ 
    $url = get_site_url()."/wp-admin/edit-tags.php?taxonomy=".OS24_PRICE_MANAGER_CUSTOM_TAXONOMY_CAT."&post_type=".OS24_PRICE_MANAGER_CUSTOM_POST_TYPE; 
    echo ("<script LANGUAGE='JavaScript'>
                window.location.href='".$url."';
        </script>");     
}
function os24_price_manager_menu_cb(){ 
    echo do_shortcode("[price_manager_menu_app]");     
}
function os24_price_manager_recovery_cb(){ 
    echo do_shortcode("[price_manager_recovery_app]");   
}

function os24_price_manager_settings_attach_ng_cb(){ 
    echo do_shortcode("[price_manager_settings_app]");   
}

function os24_price_manager_plugin_menus(){

    $permissions = json_decode(get_option(OS24_PRICE_MANAGER_CUSTOM_PERMISSIONS_OPTION));

    foreach ($permissions as $user => $value) {
        if(current_user_can( $user ) && $value == 1)
        {
            add_menu_page("Price Manager", 
                            "Price Manager", 
                            $user, 
                            "24os-price-manager", 
                            "os24_price_manager_main_attach_ng_cb",
                            "dashicons-money-alt");

            add_submenu_page("24os-price-manager", 
                            "Menu List", 
                            "Menu List", 
                            $user, 
                            "24os-price-manager",  
                            "os24_price_manager_main_attach_ng_cb");

            add_submenu_page("24os-price-manager", 
                            "Menu Types", 
                            "Menu Types", 
                            $user,
                            "24os-price-manager-categories", 
                            "os24_price_manager_categories_cb"); 

            add_submenu_page("24os-price-manager", 
                            "", 
                            "", 
                            $user,
                            "24os-price-manager-menu", 
                            "os24_price_manager_menu_cb"); 

            add_submenu_page("24os-price-manager", 
                            "", 
                            "", 
                            $user,
                            "24os-price-manager-recovery", 
                            "os24_price_manager_recovery_cb"); 
        }
    }
    add_submenu_page("24os-price-manager", 
                    "Settings", 
                    "Settings", 
                    "manage_options", 
                    "24os-price-manager-settings", 
                    "os24_price_manager_settings_attach_ng_cb");  
}
add_action("admin_menu", "os24_price_manager_plugin_menus");

// Hide the submenu.
function os24_price_manager_home_page_menu_link( $args ) 
{
    $args['24os-price-manager'] = true;
    return $args;
} 
function os24_price_manager_wp_admin_submenu_filter( $submenu_file )
{
    global $plugin_page;
    $hidden_submenus = array(
        '24os-price-manager-menu' => true, 
        '24os-price-manager-recovery' => true
    );  

    add_filter( 'wp_page_menu_args', 'os24_price_manager_home_page_menu_link' );
        
    // Select another submenu item to highlight (optional).
    if ( $plugin_page && isset( $hidden_submenus[ $plugin_page ] ) ) {
        $submenu_file = 'submenu_to_highlight';
    }
    // Hide the submenu.
    foreach ( $hidden_submenus as $submenu => $unused ) {
        remove_submenu_page( '24os-price-manager', $submenu );
    }
    return $submenu_file;
}
add_filter( 'submenu_file', 'os24_price_manager_wp_admin_submenu_filter' );


// Register Custom Post Type
function os24_price_manager_custom_post_type() {
    
    $labels = array(
        'name'                  => _x( 'Price Menus', 'Post Type General Name', 'text_domain' ),
        'singular_name'         => _x( 'Price Menu', 'Post Type Singular Name', 'text_domain' ),
        'menu_name'             => __( 'Price Menus', 'text_domain' ),
        'name_admin_bar'        => __( 'Price Menus', 'text_domain' ),
        'archives'              => __( 'Item Archives', 'text_domain' ),
        'attributes'            => __( 'Item Attributes', 'text_domain' ),
        'parent_item_colon'     => __( 'Parent Item:', 'text_domain' ),
        'all_items'             => __( 'All Items', 'text_domain' ),
        'add_new_item'          => __( 'Add New Item', 'text_domain' ),
        'add_new'               => __( 'Add New', 'text_domain' ),
        'new_item'              => __( 'New Item', 'text_domain' ),
        'edit_item'             => __( 'Edit Item', 'text_domain' ),
        'update_item'           => __( 'Update Item', 'text_domain' ),
        'view_item'             => __( 'View Item', 'text_domain' ),
        'view_items'            => __( 'View Items', 'text_domain' ),
        'search_items'          => __( 'Search Item', 'text_domain' ),
        'not_found'             => __( 'Not found', 'text_domain' ),
        'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
        'featured_image'        => __( 'Featured Image', 'text_domain' ),
        'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
        'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
        'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
        'insert_into_item'      => __( 'Insert into item', 'text_domain' ),
        'uploaded_to_this_item' => __( 'Uploaded to this item', 'text_domain' ),
        'items_list'            => __( 'Items list', 'text_domain' ),
        'items_list_navigation' => __( 'Items list navigation', 'text_domain' ),
        'filter_items_list'     => __( 'Filter items list', 'text_domain' ),
    );
    $args = array(
        'label'                 => __( 'Price Menu', 'text_domain' ),
        'description'           => __( 'manage price menus', 'text_domain' ),
        'labels'                => $labels,
        'supports'              => array( 'title', 'editor', 'author' , 'excerpt'),
        'hierarchical'          => false,
        'public'                => true,
        'show_in_rest'          => true,
        'show_ui'               => false,
        'show_in_menu'          => false,
        'menu_position'         => 5,
        'show_in_admin_bar'     => false,
        'show_in_nav_menus'     => false,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'rewrite'       => array('slug' => 'price-menu', 'with_front' => false),
    );
    register_post_type( OS24_PRICE_MANAGER_CUSTOM_POST_TYPE, $args );


    //register taxonomy for categoris
    register_taxonomy(  
        OS24_PRICE_MANAGER_CUSTOM_TAXONOMY_CAT,                      // This is a name of the taxonomy. Make sure it's not a capital letter and no space in between
        OS24_PRICE_MANAGER_CUSTOM_POST_TYPE,                    //post type name
        array(  
            'hierarchical' => true,  
            'label' => 'Price Manager Categories',      //Display name
            'query_var' => true,
            'has_archive' => true,
            'show_in_rest'=> true,
            'rewrite' => array('slug' => OS24_PRICE_MANAGER_CUSTOM_TAXONOMY_CAT)
        )  
    );  

    add_option(OS24_PRICE_MANAGER_CUSTOM_DEFAULT_CAT_1_OPTION, "Uncategorized Price Menu");
    add_option(OS24_PRICE_MANAGER_CUSTOM_DEFAULT_CAT_SLUG_1_OPTION, "uncategorized-price-menu");
    wp_insert_term(
        get_option(OS24_PRICE_MANAGER_CUSTOM_DEFAULT_CAT_1_OPTION), 
        OS24_PRICE_MANAGER_CUSTOM_TAXONOMY_CAT, 
        array(
            'slug' => sanitize_title(get_option(OS24_PRICE_MANAGER_CUSTOM_DEFAULT_CAT_SLUG_1_OPTION)),  
    ));
    
}
add_action( 'init', 'os24_price_manager_custom_post_type', 0 );


function os24_price_manager_after_update_default_category_1($term_id_edited)
{
    update_option(OS24_PRICE_MANAGER_CUSTOM_DEFAULT_CAT_1_OPTION,  get_term($term_id_edited)->name);
    update_option(OS24_PRICE_MANAGER_CUSTOM_DEFAULT_CAT_SLUG_1_OPTION,  get_term($term_id_edited)->slug);
}
function os24_price_manager_before_update_default_categories($term_id)
{
    if(get_option(OS24_PRICE_MANAGER_CUSTOM_DEFAULT_CAT_1_OPTION) == get_term($term_id)->name || 
        get_option(OS24_PRICE_MANAGER_CUSTOM_DEFAULT_CAT_SLUG_1_OPTION) == get_term($term_id)->slug){
        add_action("edited_term", "os24_price_manager_after_update_default_category_1");
    }
}
add_action("edit_term", "os24_price_manager_before_update_default_categories");


// Custom Endpoint API 
/**
 * READABLE   = 'GET';
 * CREATABLE  = 'POST';
 * EDITABLE   = 'POST, PUT, PATCH';
 * DELETABLE  = 'DELETE';
 * ALLMETHODS = 'GET, POST, PUT, PATCH, DELETE';
 */
function os24_price_manager_adding_custom_routes_rest_api() {
    $my_plugin = "24os-price-manager";
    $version = "1";
    $namespace = $my_plugin.'/v'.$version;

    /********************************
            menu routes
    *********************************/
    // get all menus when GET request
    // create new menu when POST request 
    // route : http://localhost/wordpress/wp-json/24os-price-manager/v1/menu
    register_rest_route( $namespace, 'menu',array(
        array(
            'methods'  => WP_REST_Server::READABLE,
            'callback' => 'os24_price_manager_get_all_menus',
            'login_user_id' => get_current_user_id(),
        ),
        array(
            'methods'  => WP_REST_Server::CREATABLE,
            'callback' => 'os24_price_manager_create_menu',
            'login_user_id' => get_current_user_id(),
        )
    ));

    // active menu when POST request 
    // route : http://localhost/wordpress/wp-json/24os-price-manager/v1/active-menu
    register_rest_route( $namespace, 'active-menu',array(
        array(
            'methods'  => WP_REST_Server::CREATABLE,
            'callback' => 'os24_price_manager_active_menu',
            'login_user_id' => get_current_user_id(),
        )
    ));

    // get menu when GET request
    // update menu when PUTCH request
    // delete menu when DELETE request
    // route : http://localhost/wordpress/wp-json/24os-price-manager/v1/menu/{id}
    register_rest_route( $namespace, 'menu/(?P<id>\d+)',array(
        array(
            'methods' =>  WP_REST_Server::READABLE,
            'callback' => 'os24_price_manager_get_menu',
            'login_user_id' => get_current_user_id(),
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric( $param );
                    }
                ),
            )
        ),
        array(
            'methods' =>  WP_REST_Server::EDITABLE,
            'callback' => 'os24_price_manager_update_menu',
            'login_user_id' => get_current_user_id(),
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric( $param );
                    }
                ),
            )
            ),
            array(
                'methods' =>  WP_REST_Server::DELETABLE,
                'callback' => 'os24_price_manager_delete_menu',
                'login_user_id' => get_current_user_id(),
                'args' => array(
                    'id' => array(
                        'validate_callback' => function($param, $request, $key) {
                            return is_numeric( $param );
                        }
                    ),
                )
            )
    ));


    /********************************
        menu settings routes
    *********************************/
    // get menu style when GET request
    // update menu style when POST request
    // route : http://localhost/wordpress/wp-json/24os-price-manager/v1/style-menu/{id}
    register_rest_route( $namespace, 'style-menu/(?P<id>\d+)',array(
        array(
            'methods' =>  WP_REST_Server::READABLE,
            'callback' => 'os24_price_manager_get_menu_style',
            'login_user_id' => get_current_user_id(),
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric( $param );
                    }
                ),
            )
        ),
        array(
            'methods' =>  WP_REST_Server::EDITABLE,
            'callback' => 'os24_price_manager_update_menu_style',
            'login_user_id' => get_current_user_id(),
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric( $param );
                    }
                ),
            )
            )
    ));

    // renumber menu when GET request
    // route : http://localhost/wordpress/wp-json/24os-price-manager/v1/renumber-menu/{id}
    register_rest_route( $namespace, 'renumber-menu/(?P<id>\d+)',array(
        array(
            'methods' =>  WP_REST_Server::READABLE,
            'callback' => 'os24_price_manager_renumber_menu',
            'login_user_id' => get_current_user_id(),
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric( $param );
                    }
                ),
            )
        )
    ));

    // inc price items in menu when POST request
    // route : http://localhost/wordpress/wp-json/24os-price-manager/v1/inc-price-menu/{id}
    register_rest_route( $namespace, 'inc-price-menu/(?P<id>\d+)',array(
        array(
            'methods' =>  WP_REST_Server::CREATABLE,
            'callback' => 'os24_price_manager_inc_price_menu',
            'login_user_id' => get_current_user_id(),
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric( $param );
                    }
                ),
            )
        )
    ));

    // get option image menu when GET request
    // update  option image menu when POST request
    // route : http://localhost/wordpress/wp-json/24os-price-manager/v1/image-menu/{id}
    register_rest_route( $namespace, 'image-menu/(?P<id>\d+)',array(
        array(
            'methods' =>  WP_REST_Server::READABLE,
            'callback' => 'os24_price_manager_get_menu_option_image',
            'login_user_id' => get_current_user_id(),
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric( $param );
                    }
                ),
            )
        ),
        array(
            'methods' =>  WP_REST_Server::EDITABLE,
            'callback' => 'os24_price_manager_update_menu_option_image',
            'login_user_id' => get_current_user_id(),
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric( $param );
                    }
                ),
            )
            )
    ));

    /********************************
            recovery routes 
    *********************************/
    // trash menu when POST request == trash
    // restore menu when POST request == restore
    // route : http://localhost/wordpress/wp-json/24os-price-manager/v1/menu-recovery/{id}
    register_rest_route( $namespace, 'menu-recovery/(?P<id>\d+)',array(
        array(
            'methods' =>  WP_REST_Server::CREATABLE,
            'callback' => 'os24_price_manager_recovery_menu',
            'login_user_id' => get_current_user_id(),
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric( $param );
                    }
                ),
            )
        )
    ));

    // get all trash menus when GET request 
    // route : http://localhost/wordpress/wp-json/24os-price-manager/v1/menu-recovery
    register_rest_route( $namespace, 'menu-recovery',array(
        array(
            'methods'  => WP_REST_Server::READABLE,
            'callback' => 'os24_price_manager_get_all_trash_menus',
            'login_user_id' => get_current_user_id(),
        )
    ));


    /********************************
            allergen routes 
    *********************************/
    // get all allergen when GET request
    // create new allergen when POST request 
    // route : http://localhost/wordpress/wp-json/24os-price-manager/v1/allergen
    register_rest_route( $namespace, 'allergen',array(
        array(
            'methods'  => WP_REST_Server::READABLE,
            'callback' => 'os24_price_manager_get_all_allergens',
            'login_user_id' => get_current_user_id(),
        ),
        array(
            'methods'  => WP_REST_Server::CREATABLE,
            'callback' => 'os24_price_manager_create_allergen',
            'login_user_id' => get_current_user_id(),
        )
    ));

    // get allergen when GET request
    // update allergen when PUTCH request
    // delete allergen when DELETE request
    // route : http://localhost/wordpress/wp-json/24os-price-manager/v1/allergen/{id}
    register_rest_route( $namespace, 'allergen/(?P<id>\d+)',array(
        array(
            'methods' =>  WP_REST_Server::READABLE,
            'callback' => 'os24_price_manager_get_allergen',
            'login_user_id' => get_current_user_id(),
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric( $param );
                    }
                ),
            )
        ),
        array(
            'methods' =>  WP_REST_Server::EDITABLE,
            'callback' => 'os24_price_manager_update_allergen',
            'login_user_id' => get_current_user_id(),
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric( $param );
                    }
                ),
            )
        ),
        array(
            'methods' =>  WP_REST_Server::DELETABLE,
            'callback' => 'os24_price_manager_delete_allergen',
            'login_user_id' => get_current_user_id(),
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric( $param );
                    }
                ),
            )
        )
    ));


    /********************************
        reset database routes 
    *********************************/
    // reset database when GET request 
    // route : http://localhost/wordpress/wp-json/24os-price-manager/v1/database
    register_rest_route( $namespace, 'database',array(
        array(
            'methods'  => WP_REST_Server::READABLE,
            'callback' => 'os24_price_manager_reset_database',
            'login_user_id' => get_current_user_id(),
        )
    ));


    /********************************
            permissions routes 
    *********************************/
    // get permissions when GET request
    // set permissions when POST request 
    // route : http://localhost/wordpress/wp-json/24os-price-manager/v1/permissions
    register_rest_route( $namespace, 'permissions',array(
        array(
            'methods'  => WP_REST_Server::READABLE,
            'callback' => 'os24_price_manager_get_permissions',
            'login_user_id' => get_current_user_id(),
        ),
        array(
            'methods'  => WP_REST_Server::CREATABLE,
            'callback' => 'os24_price_manager_set_permissions',
            'login_user_id' => get_current_user_id(),
        )
    ));

    /***************************************
        categories & items menu routes 
    ****************************************/
    // get categories and items menu when GET request
    // route : http://localhost/wordpress/wp-json/24os-price-manager/v1/categories-and-items/{menu_id}
    register_rest_route( $namespace, 'categories-and-items/(?P<menu_id>\d+)',array(
        array(
            'methods'  => WP_REST_Server::READABLE,
            'callback' => 'os24_price_manager_get_categories_and_items_menu',
            'login_user_id' => get_current_user_id(),
        )
    ));

    // create new category when POST request 
    // route : http://localhost/wordpress/wp-json/24os-price-manager/v1/categories
    register_rest_route( $namespace, 'categories',array(
        array(
            'methods'  => WP_REST_Server::CREATABLE,
            'callback' => 'os24_price_manager_create_category',
            'login_user_id' => get_current_user_id(),
        )
    ));

    // get category when GET request
    // update category when PUTCH request
    // delete category when DELETE request
    // route : http://localhost/wordpress/wp-json/24os-price-manager/v1/categories/{id}
    register_rest_route( $namespace, 'categories/(?P<id>\d+)',array(
        array(
            'methods' =>  WP_REST_Server::READABLE,
            'callback' => 'os24_price_manager_get_category',
            'login_user_id' => get_current_user_id(),
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric( $param );
                    }
                ),
            )
        ),
        array(
            'methods' =>  WP_REST_Server::EDITABLE,
            'callback' => 'os24_price_manager_update_category',
            'login_user_id' => get_current_user_id(),
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric( $param );
                    }
                ),
            )
        ),
        array(
            'methods' =>  WP_REST_Server::DELETABLE,
            'callback' => 'os24_price_manager_delete_category',
            'login_user_id' => get_current_user_id(),
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric( $param );
                    }
                ),
            )
        )
    ));

    // sorting categories when POST request
    // route : http://localhost/wordpress/wp-json/24os-price-manager/v1/sorting-categories/
    register_rest_route( $namespace, 'sorting-categories',array(
        array(
            'methods' =>  WP_REST_Server::CREATABLE,
            'callback' => 'os24_price_manager_sorting_categories',
            'login_user_id' => get_current_user_id(),
        )
    ));

    // get items by category_id when GET request
    // route : http://localhost/wordpress/wp-json/24os-price-manager/v1/items/{category_id}
    register_rest_route( $namespace, 'items/(?P<category_id>\d+)',array(
        array(
            'methods' =>  WP_REST_Server::READABLE,
            'callback' => 'os24_price_manager_get_items_by_category_id',
            'login_user_id' => get_current_user_id(),
            'args' => array(
                'category_id' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric( $param );
                    }
                ),
            )
        )
    ));

    // create item when POST request
    // route : http://localhost/wordpress/wp-json/24os-price-manager/v1/items/
    register_rest_route( $namespace, 'items',array(
        array(
            'methods' =>  WP_REST_Server::CREATABLE,
            'callback' => 'os24_price_manager_create_item',
            'login_user_id' => get_current_user_id(),
        )
    ));

    // get item when GET request
    // update item when PUTCH request
    // delete item when DELETE request
    // route : http://localhost/wordpress/wp-json/24os-price-manager/v1/items/{id}
    register_rest_route( $namespace, 'items/(?P<id>\d+)',array(
        array(
            'methods' =>  WP_REST_Server::READABLE,
            'callback' => 'os24_price_manager_get_item',
            'login_user_id' => get_current_user_id(),
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric( $param );
                    }
                ),
            )
        ),
        array(
            'methods' =>  WP_REST_Server::EDITABLE,
            'callback' => 'os24_price_manager_update_item',
            'login_user_id' => get_current_user_id(),
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric( $param );
                    }
                ),
            )
        ),
        array(
            'methods' =>  WP_REST_Server::DELETABLE,
            'callback' => 'os24_price_manager_delete_item',
            'login_user_id' => get_current_user_id(),
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric( $param );
                    }
                ),
            )
        )
    ));

    // sorting items category when POST request
    // route : http://localhost/wordpress/wp-json/24os-price-manager/v1/sorting-items/
    register_rest_route( $namespace, 'sorting-items',array(
        array(
            'methods' =>  WP_REST_Server::CREATABLE,
            'callback' => 'os24_price_manager_sorting_items',
            'login_user_id' => get_current_user_id(),
        )
    ));

    /***************************************
        statistics menu routes 
    ****************************************/
    // get statistics by menu_id when GET request
    // route : http://localhost/wordpress/wp-json/24os-price-manager/v1/statistics/{menu_id}
    register_rest_route( $namespace, 'statistics/(?P<menu_id>\d+)',array(
        array(
            'methods' =>  WP_REST_Server::READABLE,
            'callback' => 'os24_price_manager_get_statistics_by_menu_id',
            'login_user_id' => get_current_user_id(),
            'args' => array(
                'menu_id' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric( $param );
                    }
                ),
            )
        )
    ));
    

    /********************************
            language routes 
    *********************************/
    // get language when GET request
    // set language when POST request 
    // route : http://localhost/wordpress/wp-json/24os-price-manager/v1/lang
    register_rest_route( $namespace, 'lang',array(
        array(
            'methods'  => WP_REST_Server::READABLE,
            'callback' => 'os24_price_manager_get_language',
            'login_user_id' => get_current_user_id(),
        ),
        array(
            'methods'  => WP_REST_Server::CREATABLE,
            'callback' => 'os24_price_manager_set_language',
            'login_user_id' => get_current_user_id(),
        )
    ));
}
add_action('rest_api_init', "os24_price_manager_adding_custom_routes_rest_api");

/********************************
    REST API security layer
    - every route of this plugin requires a logged-in user (permission_callback)
    - cookie authentication in the REST API needs a valid X-WP-Nonce header (CSRF protection)
    - settings / destructive routes are restricted to administrators
*********************************/

// can the current user use the plugin? (administrator or a role enabled in the plugin settings)
function os24_price_manager_current_user_allowed()
{
    if(!is_user_logged_in())
        return false;
    if(current_user_can('manage_options'))
        return true;

    $permissions = json_decode(get_option(OS24_PRICE_MANAGER_CUSTOM_PERMISSIONS_OPTION), true);
    if(is_array($permissions))
    {
        foreach ($permissions as $role => $enabled) {
            if($enabled && current_user_can($role))
                return true;
        }
    }
    return false;
}

// the route callbacks read the user from the request attributes: bind the *verified* user here
function os24_price_manager_bind_request_user($request)
{
    $attrs = $request->get_attributes();
    $attrs['login_user_id'] = get_current_user_id();
    $request->set_attributes($attrs);
}

// menu id that a category / an item belongs to (0 when it does not exist)
function os24_price_manager_menu_id_of_category($category_id)
{
    global $wpdb;
    return (int) $wpdb->get_var($wpdb->prepare(
        "SELECT menu_id FROM ". $wpdb->prefix ."24os_price_manager_category WHERE id = %d", absint($category_id)));
}
function os24_price_manager_menu_id_of_item($item_id)
{
    global $wpdb;
    $category_id = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT category_id FROM ". $wpdb->prefix ."24os_price_manager_item WHERE id = %d", absint($item_id)));
    return $category_id ? os24_price_manager_menu_id_of_category($category_id) : 0;
}

// the menus a request touches (ids taken from the URL and from the JSON body)
function os24_price_manager_request_menu_ids($request)
{
    $route  = preg_replace('#^/24os-price-manager/v1#', '', $request->get_route());
    $params = $request->get_url_params();
    $ids    = array();

    if(preg_match('#^/(menu|style-menu|renumber-menu|inc-price-menu|image-menu|menu-recovery)/\d+#', $route) && isset($params['id'])){
        $ids[] = absint($params['id']);
    }
    elseif(preg_match('#^/(categories-and-items|statistics)/\d+#', $route) && isset($params['menu_id'])){
        $ids[] = absint($params['menu_id']);
    }
    elseif(preg_match('#^/categories(/\d+)?$#', $route)){
        if(isset($params['id']))
            $ids[] = os24_price_manager_menu_id_of_category($params['id']);
        if($request->get_param('menu_id'))
            $ids[] = absint($request->get_param('menu_id'));
    }
    elseif(preg_match('#^/items(/\d+)?$#', $route)){
        if(isset($params['category_id']))
            $ids[] = os24_price_manager_menu_id_of_category($params['category_id']);
        if(isset($params['id']))
            $ids[] = os24_price_manager_menu_id_of_item($params['id']);
        if($request->get_param('category_id'))
            $ids[] = os24_price_manager_menu_id_of_category($request->get_param('category_id'));
        if($request->get_param('menu_id'))
            $ids[] = absint($request->get_param('menu_id'));
    }
    elseif($route === '/sorting-categories' || $route === '/sorting-items'){
        $sorting = $request->get_param('sorting');
        foreach ((array) $sorting as $row) {
            if(!is_array($row) || !isset($row['id']))
                continue;
            $ids[] = ($route === '/sorting-categories')
                ? os24_price_manager_menu_id_of_category($row['id'])
                : os24_price_manager_menu_id_of_item($row['id']);
        }
    }
    return array_values(array_unique(array_filter($ids)));
}

// administrators may touch every menu, other users only the menus they created
function os24_price_manager_user_owns_menus($menu_ids)
{
    if(current_user_can('manage_options') || empty($menu_ids))
        return true;
    global $wpdb;
    foreach ($menu_ids as $menu_id) {
        $owner = $wpdb->get_var($wpdb->prepare(
            "SELECT user_id FROM ". $wpdb->prefix ."24os_price_manager_menu WHERE id = %d", $menu_id));
        // unknown menu: let the route callback answer with its own 404
        if($owner !== null && (int) $owner !== get_current_user_id())
            return false;
    }
    return true;
}

function os24_price_manager_rest_permission_user($request)
{
    if(!is_user_logged_in())
        return new WP_Error('rest_not_logged_in', 'You are not currently logged in.', array('status' => 401));
    if(!os24_price_manager_current_user_allowed())
        return new WP_Error('rest_forbidden', 'Sorry, you are not allowed to do that.', array('status' => 403));
    if(!os24_price_manager_user_owns_menus(os24_price_manager_request_menu_ids($request)))
        return new WP_Error('rest_forbidden', 'You are not allowed to access this menu.', array('status' => 403));
    os24_price_manager_bind_request_user($request);
    return true;
}

function os24_price_manager_rest_permission_admin($request)
{
    if(!is_user_logged_in())
        return new WP_Error('rest_not_logged_in', 'You are not currently logged in.', array('status' => 401));
    if(!current_user_can('manage_options'))
        return new WP_Error('rest_forbidden', 'Sorry, you are not allowed to do that.', array('status' => 403));
    os24_price_manager_bind_request_user($request);
    return true;
}

function os24_price_manager_secure_rest_endpoints($endpoints)
{
    $prefix = '/24os-price-manager/v1';
    foreach ($endpoints as $route => $handlers) {
        if(strpos($route, $prefix) !== 0 || !is_array($handlers))
            continue;
        $path = substr($route, strlen($prefix));

        foreach ($handlers as $i => $handler) {
            if(!is_array($handler) || !isset($handler['callback']) || !is_string($handler['callback']) ||
                strpos($handler['callback'], 'os24_price_manager_') !== 0)
                continue;

            // HTTP verbs of this handler
            $verbs = array();
            $methods = isset($handler['methods']) ? $handler['methods'] : array();
            if(is_string($methods))
                $methods = preg_split('/[\s,]+/', $methods);
            foreach ((array) $methods as $k => $v) {
                $verbs[] = strtoupper(is_int($k) ? (string) $v : (string) $k);
            }
            $is_read = in_array('GET', $verbs, true);

            $admin_only = ($path === '/database' || $path === '/permissions')
                || ($path === '/lang' && !$is_read)
                || (strpos($path, '/allergen') === 0 && !$is_read);

            $endpoints[$route][$i]['permission_callback'] = $admin_only
                ? 'os24_price_manager_rest_permission_admin'
                : 'os24_price_manager_rest_permission_user';
        }
    }
    return $endpoints;
}
add_filter('rest_endpoints', 'os24_price_manager_secure_rest_endpoints');

// send the REST nonce with every request the Angular apps make to this site's REST API
function os24_price_manager_add_rest_nonce_script($handle)
{
    $js = '(function(){
        var nonce = ' . wp_json_encode(wp_create_nonce('wp_rest')) . ';
        var open = XMLHttpRequest.prototype.open, send = XMLHttpRequest.prototype.send;
        XMLHttpRequest.prototype.open = function(method, url){
            var u = String(url);
            this.__os24Rest = (u.indexOf("wp-json/") !== -1 || u.indexOf("rest_route=") !== -1) &&
                              (u.indexOf("http") !== 0 || u.indexOf(location.origin) === 0);
            return open.apply(this, arguments);
        };
        XMLHttpRequest.prototype.send = function(){
            if(this.__os24Rest){ try { this.setRequestHeader("X-WP-Nonce", nonce); } catch(e){} }
            return send.apply(this, arguments);
        };
    })();';
    wp_add_inline_script($handle, $js, 'before');
}


/**
 *  functions for menu routes 
 **/
function os24_price_manager_get_all_menus($request) {
    global $wpdb; 
    $table_name_menu = $wpdb->prefix . '24os_price_manager_menu';
    $table_name_category = $wpdb->prefix . '24os_price_manager_category';
    $table_name_item = $wpdb->prefix . '24os_price_manager_item';
    $table_name_statistics = $wpdb->prefix . '24os_price_manager_statistics';

    $attrs =  $request->get_attributes();
    // check is login_user_id set as attr
    if( isset($attrs['login_user_id']) && intval($attrs['login_user_id']) > 0  ){

        $user_id        = intval($attrs['login_user_id']);
        $current_user   = get_user_by( 'id', $user_id );
        
        if($current_user->allcaps['manage_options'])
        {
            $menus = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * from ". $table_name_menu . " WHERE trash = 0 ORDER BY id DESC"
                )
            ); 
            $trash_menus = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * from ". $table_name_menu . " WHERE trash = 1 ORDER BY id DESC"
                )
            ); 
            $all_menus = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * from ". $table_name_menu . " ORDER BY id DESC"
                )
            );
        }
        else{
            $menus = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * from ". $table_name_menu . " WHERE user_id = ". (int) $user_id ." and trash = 0 ORDER BY id DESC"
                )
            ); 
            $trash_menus = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * from ". $table_name_menu . " WHERE user_id = ". (int) $user_id ." and trash = 1 ORDER BY id DESC"
                )
            ); 
            $all_menus = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * from ". $table_name_menu . " WHERE user_id = ". (int) $user_id ." ORDER BY id DESC"
                )
            );
        }

        $menus_array = array();
        foreach ($menus as $menu) {
            $menu_array['id'] = $menu->id;
            $menu_array['menu'] = $menu->menu;
            $menu_array['trash'] = $menu->trash;
            $menu_array['user_id'] = $menu->user_id;
            $menu_array['post_id'] = $menu->post_id;
            $menu_array['active'] = $menu->active;
            $menu_array['permalink'] = get_permalink($menu->post_id);

            if($current_user->allcaps['manage_options'])
            {
                $menu_array['shortcode'] = get_the_content(null, false, $menu->post_id);
            }
            else{
                $menu_array['shortcode'] = "";
            }
            $menuCategories = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * from ". $table_name_category . " WHERE menu_id = ". (int) $menu->id
                )
            );

            $menuAllItems = 0;
            foreach ($menuCategories as $menuCategory) {
                $menuItems = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT * from ". $table_name_item . " WHERE category_id = ". (int) $menuCategory->id
                    )
                );
                $menuAllItems += count($menuItems);
            }
            $menu_array['catCount'] = count($menuCategories);
            $menu_array['itemsCount'] = $menuAllItems;

            $menu_types_array = array();
            $menu_types = get_the_terms($menu->post_id, OS24_PRICE_MANAGER_CUSTOM_TAXONOMY_CAT);
            foreach ($menu_types as $menu_type) 
            {
                array_push($menu_types_array, $menu_type->name);
            }
            $menu_array['menu_types'] = implode(", ", $menu_types_array);

            $menu_array['menu_date'] = get_the_date( 'Y-m-d H:i:s', $menu->post_id);
            $menu_array['menu_author'] = get_the_author_meta( 'display_name', get_post_field( 'post_author', $menu->post_id));
            
            $visits = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * from ". $table_name_statistics . " WHERE menu_id = ".  (int) $menu->id
                )
            );
            $menu_array['visitsCount'] = count($visits);

            array_push($menus_array, $menu_array);

        }
        $menus_counts['menusCount'] = count($menus);
        $menus_counts['trashMenusCount'] = count($trash_menus);
        $menus_counts['allMenusCount'] = count($all_menus);
        
        $response = new WP_REST_Response(array('menus' => $menus_array, 'counts'=>$menus_counts));
        $response->set_status(200);

        return $response;
    }
    else{
        return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array('status' => 401) );
    }

}
function os24_price_manager_create_menu($request) {
    global $wpdb; 
    $table_name_menu = $wpdb->prefix . '24os_price_manager_menu';

    $attrs =  $request->get_attributes();
    // check is login_user_id set as attr
    if( isset($attrs['login_user_id']) && intval($attrs['login_user_id']) > 0  ){

        $user_id        = intval($attrs['login_user_id']);
        $current_user   = get_user_by( 'id', $user_id );

        $menu = $request['menu'];
        if(isset($menu) && !empty($menu)){
            // save data to database table
            $sql = $wpdb->insert( $table_name_menu, array(
                "user_id" => $user_id,
                "menu" => $menu,
            ));
            if ($sql==true) {

                $price_menu_inserted_id = $wpdb->insert_id;
                $post_name = sanitize_title($menu);

                $menu_types = $request['priceManagerPostCategories'];
                if(isset($menu_types) && count($menu_types) != 0)
                {
                    $category_selected = array();
                    foreach ($menu_types as $menu_type_name) {
                        array_push($category_selected, get_terms([
                                'fields' => 'ids',
                                'taxonomy' => OS24_PRICE_MANAGER_CUSTOM_TAXONOMY_CAT,
                                'name' => $menu_type_name,
                                'hide_empty' => false,
                            ])[0]
                        );
                    }
                }else{
                    $category_selected = get_terms([
                            'fields' => 'ids',
                            'taxonomy' => OS24_PRICE_MANAGER_CUSTOM_TAXONOMY_CAT,
                            'name' => get_option(OS24_PRICE_MANAGER_CUSTOM_DEFAULT_CAT_1_OPTION),
                            'slug' => get_option(OS24_PRICE_MANAGER_CUSTOM_DEFAULT_CAT_SLUG_1_OPTION),
                            'hide_empty' => false,
                    ]);
                
                }

                // Create custom post 
                $my_custom_post = array(
                    'post_title'    => wp_strip_all_tags($menu),
                    'post_name' => $post_name,
                    'post_content'  => '['.OS24_PRICE_MANAGER_SHORTCODE.' id='.$price_menu_inserted_id.' style=1]',
                    'post_status'   => 'publish',
                    'post_type'   => OS24_PRICE_MANAGER_CUSTOM_POST_TYPE,
                    'post_author'   => $user_id,
                );

                // Insert the post into the database
                $post_id = wp_insert_post( $my_custom_post );
                $post_permalink = get_permalink($post_id);
                $wpdb->update( $table_name_menu, array(
                    'post_id' => $post_id),
                    array( 'id' => $price_menu_inserted_id )
                );

                wp_set_post_terms( $post_id, $category_selected, OS24_PRICE_MANAGER_CUSTOM_TAXONOMY_CAT );
                flush_rewrite_rules( false );

                add_post_meta( $post_id, OS24_PRICE_MANAGER_CUSTOM_POST_META, 'without_image', true );

                $language = get_option(OS24_PRICE_MANAGER_CUSTOM_LANGUAGE_OPTION);

                $response = new WP_REST_Response(array("lang"=>$language, "message"=>"menu created successfully"));
                $response->set_status(200);
            
                return $response;
            }
            else{
                return new WP_Error( 'cant_create_menu', 'there was a problem creating menu', array('status' => 500) );
            }
        }
        else{
            return new WP_Error( 'cant_create_menu', 'there was a problem creating menu', array('status' => 500) );
        }
    }
    else{
        return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array('status' => 401) );
    }
    
}
function os24_price_manager_active_menu($request){
    global $wpdb; 
    $table_name_menu = $wpdb->prefix . '24os_price_manager_menu';

    $attrs =  $request->get_attributes();
    // check is login_user_id set as attr
    if( isset($attrs['login_user_id']) && intval($attrs['login_user_id']) > 0  ){

        $user_id        = intval($attrs['login_user_id']);
        $current_user   = get_user_by( 'id', $user_id );

        $menu_id = $request['id'];
        if(isset($menu_id) && is_numeric($menu_id)){
            $active = $request['active'];
            $active_menu = $wpdb->update( $table_name_menu, 
                array("active" => $active),
                array( 'id' => $menu_id )
            );
            $language = get_option(OS24_PRICE_MANAGER_CUSTOM_LANGUAGE_OPTION);

            if($active){
                if($language == "en")
                {
                    $response = new WP_REST_Response(array("lang"=>$language, "st"=> "activated"));
                }
                else{
                    $response = new WP_REST_Response(array("lang"=>$language, "st"=> "activated"));

                }
                $response->set_status(200);
                return $response;
            }
            else{
                if($language == "en")
                {
                    $response = new WP_REST_Response(array("lang"=>$language, "st"=> "unactivated"));
                }
                else{
                    $response = new WP_REST_Response(array("lang"=>$language, "st"=> "unactivated"));

                }
                $response->set_status(200);
            
                return $response;
            }
            
        }
        else{
            return new WP_Error( 'empty_menu', 'there was a problem in get menu', array('status' => 404) );
        }
    }
    else{
        return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array('status' => 401) );
    }
}
function os24_price_manager_get_menu($request) {
    global $wpdb; 
    $table_name_menu = $wpdb->prefix . '24os_price_manager_menu';
    $menu_id = $request['id'];

    $attrs =  $request->get_attributes();
    // check is login_user_id set as attr
    if( isset($attrs['login_user_id']) && intval($attrs['login_user_id']) > 0  ){

        $user_id        = intval($attrs['login_user_id']);
        $current_user   = get_user_by( 'id', $user_id );

        if(isset($menu_id) && is_numeric($menu_id)){
            $menu_row = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * from ". $table_name_menu . " where id = %d and trash = 0", $menu_id
                )
            ); 

            $menu_types_array = array();
            $menu_types = get_the_terms($menu_row->post_id, OS24_PRICE_MANAGER_CUSTOM_TAXONOMY_CAT);
            foreach ($menu_types as $menu_type) 
            {
                array_push($menu_types_array,$menu_type->name);
            }
            $menu['id'] = $menu_row->id;
            $menu['menu'] = $menu_row->menu;
            $menu['trash'] = $menu_row->trash;
            $menu['user_id'] = $menu_row->user_id;
            $menu['post_id'] = $menu_row->post_id;
            $menu['menu_types'] = $menu_types_array;
            $menu['permalink'] = get_permalink($menu_row->post_id);

            $response = new WP_REST_Response($menu);
            $response->set_status(200);
        
            return $response;
        }
        else{
            return new WP_Error( 'empty_menu', 'there was a problem in get menu', array('status' => 404) );
        }
    }
    else{
        return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array('status' => 401) );
    }
}
function os24_price_manager_update_menu($request) {
    global $wpdb; 
    $table_name_menu = $wpdb->prefix . '24os_price_manager_menu';
    $menu_id = $request['id'];
    $menu = $request['menu'];

    $attrs =  $request->get_attributes();
    // check is login_user_id set as attr
    if( isset($attrs['login_user_id']) && intval($attrs['login_user_id']) > 0  ){

        $user_id        = intval($attrs['login_user_id']);
        $current_user   = get_user_by( 'id', $user_id );

        if(isset($menu_id) && is_numeric($menu_id) && isset($menu) && !empty($menu)){
            
            $post_id_updated = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT post_id from ". $table_name_menu." where id = %d ", $menu_id
                )
            );

            $update_menu = $wpdb->update( $table_name_menu, 
                array("menu" => $menu),
                array( 'id' => $menu_id )
            );

            $menu_types = $request['priceManagerPostCategories'];
            if(isset($menu_types) && count($menu_types) != 0)
            {
                $category_selected = array();
                foreach ($menu_types as $menu_type_name) {
                    array_push($category_selected, get_terms([
                            'fields' => 'ids',
                            'taxonomy' => OS24_PRICE_MANAGER_CUSTOM_TAXONOMY_CAT,
                            'name' => $menu_type_name,
                            'hide_empty' => false,
                        ])[0]
                    );
                }
            }else{
                $category_selected = get_terms([
                        'fields' => 'ids',
                        'taxonomy' => OS24_PRICE_MANAGER_CUSTOM_TAXONOMY_CAT,
                        'name' => get_option(OS24_PRICE_MANAGER_CUSTOM_DEFAULT_CAT_1_OPTION),
                        'slug' => get_option(OS24_PRICE_MANAGER_CUSTOM_DEFAULT_CAT_SLUG_1_OPTION),
                        'hide_empty' => false,
                ]);
            
            }
            wp_set_post_terms( $post_id_updated, $category_selected, OS24_PRICE_MANAGER_CUSTOM_TAXONOMY_CAT );
            
            if($update_menu)
            {
                $my_custom_post_updated = array(
                    'ID' => $post_id_updated,
                    'post_title'    => wp_strip_all_tags($menu),
                    'post_name'    => sanitize_title($menu)
                );
                wp_update_post($my_custom_post_updated);

                flush_rewrite_rules( false );

                
            }

            $language = get_option(OS24_PRICE_MANAGER_CUSTOM_LANGUAGE_OPTION);

            $response = new WP_REST_Response(array("lang"=>$language, "message"=>"menu updated successfully"));
            $response->set_status(200);
        
            return $response;
            
        }
        else{
            return new WP_Error( 'empty_update_menu', 'there was a problem in updating menu', array('status' => 404) );
        }
    }
    else{
        return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array('status' => 401) );
    }
}
function os24_price_manager_delete_menu($request) {
    global $wpdb; 
    $table_name_menu = $wpdb->prefix . '24os_price_manager_menu';
    $table_name_category = $wpdb->prefix . '24os_price_manager_category';
    $table_name_item = $wpdb->prefix . '24os_price_manager_item';
    $table_name_statistics = $wpdb->prefix . '24os_price_manager_statistics';

    $menu_id = $request['id'];

    $attrs =  $request->get_attributes();
    // check is login_user_id set as attr
    if( isset($attrs['login_user_id']) && intval($attrs['login_user_id']) > 0  ){
        $user_id        = intval($attrs['login_user_id']);
        $current_user   = get_user_by( 'id', $user_id );

        if(isset($menu_id) && is_numeric($menu_id)){
            $post_id_deleted = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT post_id from ". $table_name_menu ." where id = %d ", $menu_id
                )
            ); 
            delete_post_meta( $post_id_deleted, OS24_PRICE_MANAGER_CUSTOM_POST_META, null);
            wp_delete_post($post_id_deleted, false);

            $category_ids = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * from ". $table_name_category ." where menu_id = %d ", $menu_id
                )
            ); 

            foreach ($category_ids as $category_id) {
                $delete_items = $wpdb->delete( $table_name_item, array( 'category_id' => $category_id->id ));      
            }
            $delete_categories = $wpdb->delete( $table_name_category, array( 'menu_id' => $menu_id ));
            $delete_menu = $wpdb->delete( $table_name_menu, array( 'id' => $menu_id ));
            $delete_statistics = $wpdb->delete( $table_name_statistics, array( 'menu_id' => $menu_id ));

            $language = get_option(OS24_PRICE_MANAGER_CUSTOM_LANGUAGE_OPTION);

            $response = new WP_REST_Response(array("lang"=>$language, "message"=>"menu deleted successfully"));  
            $response->set_status(200);
        
            return $response;
        
        }
        else{
            return new WP_Error( 'empty_delete_menu', 'there was a problem in deleting menu', array('status' => 404) );
        }
    }
    else{
        return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array('status' => 401) );
    }
}


/**
 *  functions for menu settings routes 
 **/
function os24_price_manager_get_menu_style($request){
    global $wpdb; 
    $table_name_menu = $wpdb->prefix . '24os_price_manager_menu';
    $menu_id = $request['id'];

    $attrs =  $request->get_attributes();
    // check is login_user_id set as attr
    if( isset($attrs['login_user_id']) && intval($attrs['login_user_id']) > 0  ){

        $user_id        = intval($attrs['login_user_id']);
        $current_user   = get_user_by( 'id', $user_id );

        if(isset($menu_id) && is_numeric($menu_id)){
            
            $post_id = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT post_id from ". $table_name_menu." where id = %d ", $menu_id
                )
            );

            $atts = str_replace(['[', ']'],'', shortcode_parse_atts( get_the_content(null, false, $post_id) ));

            $response = new WP_REST_Response($atts['style']);
            $response->set_status(200);
        
            return $response;
        }
        else{
            return new WP_Error( 'empty_menu', 'there was a problem in get menu', array('status' => 404) );
        }
    }
    else{
        return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array('status' => 401) );
    }
}
function os24_price_manager_update_menu_style($request){
    global $wpdb; 
    $table_name_menu = $wpdb->prefix . '24os_price_manager_menu';
    $menu_id = $request['id'];
    $style = $request['style'];

    $attrs =  $request->get_attributes();
    // check is login_user_id set as attr
    if( isset($attrs['login_user_id']) && intval($attrs['login_user_id']) > 0  ){

        $user_id        = intval($attrs['login_user_id']);
        $current_user   = get_user_by( 'id', $user_id );

        if(isset($menu_id) && is_numeric($menu_id)){
            
            $post_id = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT post_id from ". $table_name_menu." where id = %d ", $menu_id
                )
            );
            $style_updated = array(
                'ID' => $post_id,
                'post_content'  => '['.OS24_PRICE_MANAGER_SHORTCODE.' id='.$menu_id.' style='. $style .']',

            );
            wp_update_post($style_updated);

            $language = get_option(OS24_PRICE_MANAGER_CUSTOM_LANGUAGE_OPTION);

            $response = new WP_REST_Response(array("lang"=>$language, "message"=>"menu style updated successfully"));  
            $response->set_status(200);
        
            return $response;
        }
        else{
            return new WP_Error( 'empty_menu', 'there was a problem in get menu', array('status' => 404) );
        }
    }
    else{
        return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array('status' => 401) );
    }
}
function os24_price_manager_renumber_menu($request){
    global $wpdb; 
    $table_name_category = $wpdb->prefix . '24os_price_manager_category';  
    $table_name_item = $wpdb->prefix . '24os_price_manager_item'; 
    $menu_id = $request['id'];

    $attrs =  $request->get_attributes();
    // check is login_user_id set as attr
    if( isset($attrs['login_user_id']) && intval($attrs['login_user_id']) > 0  ){

        $user_id        = intval($attrs['login_user_id']);
        $current_user   = get_user_by( 'id', $user_id );

        if(isset($menu_id) && is_numeric($menu_id)){
            $categories = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * from ". $table_name_category . " Where menu_id = " . (int) $menu_id ." ORDER BY sort"
                )
            ); 
            $num = 1;            
            foreach ($categories as $category) {
                $items = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT * from ". $table_name_item . " WHERE category_id = ". (int) $category->id ." ORDER BY sort"
                    )
                ); 
            
                foreach ($items as $item) {
                    $wpdb->update( $table_name_item, 
                        array(
                            "item_num" => $num 
                        ),
                        array( 'id' => $item->id , 'category_id' => $category->id)
                    );
                    $num += 1;
                }
            }

            $language = get_option(OS24_PRICE_MANAGER_CUSTOM_LANGUAGE_OPTION);

            $response = new WP_REST_Response(array("lang"=>$language, "message"=>"menu renumbered successfully"));  
            $response->set_status(200);
        
            return $response;
        }
        else{
            return new WP_Error( 'empty_menu', 'there was a problem in get menu', array('status' => 404) );
        }
    }
    else{
        return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array('status' => 401) );
    }     
}
function os24_price_manager_inc_price_menu($request){
    global $wpdb; 
    $table_name_category = $wpdb->prefix . '24os_price_manager_category';  
    $table_name_item = $wpdb->prefix . '24os_price_manager_item';  

    $attrs =  $request->get_attributes();
    // check is login_user_id set as attr
    if( isset($attrs['login_user_id']) && intval($attrs['login_user_id']) > 0  ){

        $user_id        = intval($attrs['login_user_id']);
        $current_user   = get_user_by( 'id', $user_id );

        $menu_id =  $request['id'];
        $inc = $request['inc'];
        $type = $request['type'];

        if(isset($menu_id) && is_numeric($menu_id) && isset($inc) && is_numeric($inc)){
            $categories = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * from ". $table_name_category . " Where menu_id = " . (int) $menu_id ." ORDER BY sort"
                )
            ); 
            foreach ($categories as $category) {
                $items = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT * from ". $table_name_item . " WHERE category_id = ". (int) $category->id ." ORDER BY sort"
                    )
                ); 
            
                foreach ($items as $item) {
                    if(count(json_decode($item->item_prices)) > 0)
                    {
                        $temp = array();
                        foreach (json_decode($item->item_prices) as $key => $value) {
                            $old_price = $value->item_price;
                            $old_price_filter = filter_var($old_price, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                            if($type == 'value')
                            {
                                $new_price = $old_price_filter + $inc;
                            }
                            else{
                                $new_price = $old_price_filter + (($inc * $old_price) / 100);
                            }
                            $a['item_price'] = $new_price . preg_replace("/\d/u", "", $old_price);;
                            $a['item_size'] = $value->item_size;
                            array_push($temp, $a);
                        }
                        $wpdb->update( $table_name_item, 
                            array(
                                "item_prices" => json_encode($temp), 
                            ),
                            array( 'id' => $item->id , 'category_id' => $item->category_id)
                        );

                    }
                
                }
            }
            $language = get_option(OS24_PRICE_MANAGER_CUSTOM_LANGUAGE_OPTION);

            $response = new WP_REST_Response(array("lang"=>$language, "message"=>"The prices of all items in this menu Increased successfully"));  
            $response->set_status(200);
        
            return $response;
        }
        else{
            return new WP_Error( 'empty_menu', 'there was a problem in get menu', array('status' => 404) );
        }
    }
    else{
        return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array('status' => 401) );
    }     
}
function os24_price_manager_get_menu_option_image($request){
    global $wpdb; 
    $table_name_menu = $wpdb->prefix . '24os_price_manager_menu';
    $menu_id = $request['id'];

    $attrs =  $request->get_attributes();
    // check is login_user_id set as attr
    if( isset($attrs['login_user_id']) && intval($attrs['login_user_id']) > 0  ){

        $user_id        = intval($attrs['login_user_id']);
        $current_user   = get_user_by( 'id', $user_id );

        if(isset($menu_id) && is_numeric($menu_id)){
            
            $post_id = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT post_id from ". $table_name_menu." where id = %d ", $menu_id
                )
            );

            $option_image_menu = get_post_meta( $post_id, OS24_PRICE_MANAGER_CUSTOM_POST_META, true );

            $response = new WP_REST_Response($option_image_menu);
            $response->set_status(200);
        
            return $response;
        }
        else{
            return new WP_Error( 'empty_menu', 'there was a problem in get menu', array('status' => 404) );
        }
    }
    else{
        return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array('status' => 401) );
    }
}
function os24_price_manager_update_menu_option_image($request){
    global $wpdb; 
    $table_name_menu = $wpdb->prefix . '24os_price_manager_menu';
    $menu_id = $request['id'];
    $option = $request['option'];

    $attrs =  $request->get_attributes();
    // check is login_user_id set as attr
    if( isset($attrs['login_user_id']) && intval($attrs['login_user_id']) > 0  ){

        $user_id        = intval($attrs['login_user_id']);
        $current_user   = get_user_by( 'id', $user_id );

        if(isset($menu_id) && is_numeric($menu_id)){
            
            $post_id = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT post_id from ". $table_name_menu." where id = %d ", $menu_id
                )
            );
            update_post_meta( $post_id, OS24_PRICE_MANAGER_CUSTOM_POST_META, $option );

            $language = get_option(OS24_PRICE_MANAGER_CUSTOM_LANGUAGE_OPTION);

            $response = new WP_REST_Response(array("lang"=>$language, "message"=>"The possibility of inserting pictures updated successfully"));
            $response->set_status(200);
        
            return $response;
        }
        else{
            return new WP_Error( 'empty_menu', 'there was a problem in get menu', array('status' => 404) );
        }
    }
    else{
        return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array('status' => 401) );
    }
}

/**
 *  functions for recovery routes 
 **/
function os24_price_manager_recovery_menu($request) {
    global $wpdb; 
    $table_name_menu = $wpdb->prefix . '24os_price_manager_menu';
    $menu_id = $request['id'];

    $attrs =  $request->get_attributes();
    // check is login_user_id set as attr
    if( isset($attrs['login_user_id']) && intval($attrs['login_user_id']) > 0  ){
        $user_id        = intval($attrs['login_user_id']);
        $current_user   = get_user_by( 'id', $user_id );

        if(isset($menu_id) && is_numeric($menu_id)){
            if(isset($request['recovery']) && $request['recovery'] == 'trash'){
            
                $trash_menu = $wpdb->update( $table_name_menu, 
                    array("trash" => 1),
                    array( 'id' => $menu_id )
                );
                if($trash_menu)
                {
                    $post_id_trashed = $wpdb->get_var(
                        $wpdb->prepare(
                            "SELECT post_id from ". $table_name_menu ." where id = %d ", $menu_id
                        )
                    ); 
                    wp_trash_post($post_id_trashed);
                }
                else{
                    return new WP_Error( 'cant_trash_menu', 'there was a problem in trashing menu', array('status' => 500) );
                }

                $language = get_option(OS24_PRICE_MANAGER_CUSTOM_LANGUAGE_OPTION);

                $response = new WP_REST_Response(array("lang"=>$language, "message"=>"menu trashed successfully"));
                $response->set_status(200);
            
                return $response;
            
            }
            elseif(isset($request['recovery']) && $request['recovery'] == 'restore'){
                $restore_menu = $wpdb->update( $table_name_menu, 
                    array("trash" => 0),
                    array( 'id' => $menu_id )
                );
                if($restore_menu)
                {
                    $post_id_restored = $wpdb->get_var(
                        $wpdb->prepare(
                            "SELECT post_id from ". $table_name_menu ." where id = %d ", $menu_id
                        )
                    ); 
                    $post_restored = array(
                        'ID' => $post_id_restored,
                        'post_status'   => 'publish'
                    );
                    wp_update_post($post_restored);

                    $language = get_option(OS24_PRICE_MANAGER_CUSTOM_LANGUAGE_OPTION);

                    $response = new WP_REST_Response(array("lang"=>$language, "message"=>"menu restored successfully")); 
                    $response->set_status(200);
                
                    return $response;
                }
                else{
                    return new WP_Error( 'cant_restore_menu', 'there was a problem in restore menu', array('status' => 404) );
                }
            }
            
        }
        else{
            return new WP_Error( 'empty_menu', 'there was a problem in get menu', array('status' => 404) );
        }
    }
    else{
        return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array('status' => 401) );
    }
    
}
function os24_price_manager_get_all_trash_menus($request) {
    global $wpdb; 
    $table_name_menu = $wpdb->prefix . '24os_price_manager_menu';

    $attrs =  $request->get_attributes();
    // check is login_user_id set as attr
    if( isset($attrs['login_user_id']) && intval($attrs['login_user_id']) > 0  ){

        $user_id        = intval($attrs['login_user_id']);
        $current_user   = get_user_by( 'id', $user_id );
        if($current_user->allcaps['manage_options'])
        {
            $menus = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * from ". $table_name_menu . " WHERE trash = 0 ORDER BY id DESC"
                )
            ); 
            $trash_menus = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * from ". $table_name_menu . " WHERE trash = 1 ORDER BY id DESC"
                )
            ); 
            $all_menus = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * from ". $table_name_menu . " ORDER BY id DESC"
                )
            );
        }
        else{
            $menus = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * from ". $table_name_menu . " WHERE user_id = ". (int) $user_id ." and trash = 0 ORDER BY id DESC"
                )
            ); 
            $trash_menus = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * from ". $table_name_menu . " WHERE user_id = ". (int) $user_id ." and trash = 1 ORDER BY id DESC"
                )
            ); 
            $all_menus = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * from ". $table_name_menu . " WHERE user_id = ". (int) $user_id ." ORDER BY id DESC"
                )
            );
        }
        

        $menus_array = array();
        foreach ($trash_menus as $menu) {
            $menu_array['id'] = $menu->id;
            $menu_array['menu'] = $menu->menu;
            $menu_array['trash'] = $menu->trash;
            $menu_array['user_id'] = $menu->user_id;
            $menu_array['post_id'] = $menu->post_id;
        
            $menu_types_array = array();
            $menu_types = get_the_terms($menu->post_id, OS24_PRICE_MANAGER_CUSTOM_TAXONOMY_CAT);
            foreach ($menu_types as $menu_type) 
            {
                array_push($menu_types_array, $menu_type->name);
            }
            $menu_array['menu_types'] = implode(", ", $menu_types_array);

            $menu_array['menu_author'] = get_the_author_meta( 'display_name', get_post_field( 'post_author', $menu->post_id));
            
            array_push($menus_array, $menu_array);
        }
        $menus_counts['menusCount'] = count($menus);
        $menus_counts['trashMenusCount'] = count($trash_menus);
        $menus_counts['allMenusCount'] = count($all_menus);

        $response = new WP_REST_Response(array('menus' => $menus_array, 'counts'=>$menus_counts));
        $response->set_status(200);

        return $response;
    }
    else{
        return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array('status' => 401) );
    }

}


/**
 *   functions for allergen routes
 **/
function os24_price_manager_get_all_allergens($request){
    global $wpdb; 
    $table_name_allergen = $wpdb->prefix . '24os_price_manager_allergen';

    $attrs =  $request->get_attributes();
    // check is login_user_id set as attr
    if( isset($attrs['login_user_id']) && intval($attrs['login_user_id']) > 0  ){
        $user_id        = intval($attrs['login_user_id']);
        $current_user   = get_user_by( 'id', $user_id );

        $allergens = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * from ". $table_name_allergen . " ORDER BY id DESC"
            )
        ); 

        $response = new WP_REST_Response($allergens);
        $response->set_status(200);

        return $response;
    }
    else{
        return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array('status' => 401) );
    }
    
}
function os24_price_manager_create_allergen($request){
    global $wpdb; 
    $table_name_allergen = $wpdb->prefix . '24os_price_manager_allergen';

    $attrs =  $request->get_attributes();
    // check is login_user_id set as attr
    if( isset($attrs['login_user_id']) && intval($attrs['login_user_id']) > 0  ){

        $user_id        = intval($attrs['login_user_id']);
        $current_user   = get_user_by( 'id', $user_id );

        if($current_user->allcaps['manage_options'])
        {
            $allergen = $request['allergen'];
            $allergen_desc = $request['allergen_desc'];
            if(isset($allergen) && isset($allergen_desc) && !empty($allergen) && !empty($allergen_desc)){
                // save data to database table
                $sql = $wpdb->insert( $table_name_allergen, array(
                    "allergen" => $allergen,
                    "allergen_desc" => $allergen_desc,
                ));
                if ($sql==true) {

                    $language = get_option(OS24_PRICE_MANAGER_CUSTOM_LANGUAGE_OPTION);

                    $response = new WP_REST_Response(array("lang"=>$language, "message"=>"allergen created successfully"));                                        
                    $response->set_status(200);
                
                    return $response;
                }
                else{
                    return new WP_Error( 'cant_create_allergen', 'there was a problem creating allergen', array('status' => 500) );
                }
            }
            else{
                return new WP_Error( 'cant_create_allergen', 'there was a problem creating allergen', array('status' => 500) );
            }
        }
    }
    else{
        return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array('status' => 401) );
    }
}
function os24_price_manager_get_allergen($request){
    global $wpdb; 
    $table_name_allergen = $wpdb->prefix . '24os_price_manager_allergen';  

    $attrs =  $request->get_attributes();
    // check is login_user_id set as attr
    if( isset($attrs['login_user_id']) && intval($attrs['login_user_id']) > 0  ){
        $user_id        = intval($attrs['login_user_id']);
        $current_user   = get_user_by( 'id', $user_id );

        $allergen_id = $request['id'];
        if(isset($allergen_id) && is_numeric($allergen_id))
        {
            $allergen = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * from ". $table_name_allergen . " where id = %d", $allergen_id
                )
            ); 

            if($allergen){
                $response = new WP_REST_Response($allergen);
                $response->set_status(200);
            
                return $response;
            }
            else{
                return new WP_Error( 'empty_allergen', 'not found allergen', array('status' => 404) );
            }
            
        }
        else{
            return new WP_Error( 'empty_allergen', 'there was a problem in get allergen', array('status' => 404) );
        }
    }
    else{
        return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array('status' => 401) );
    }
}
function os24_price_manager_update_allergen($request){
    global $wpdb; 
    $table_name_allergen = $wpdb->prefix . '24os_price_manager_allergen';  

    $attrs =  $request->get_attributes();
    // check is login_user_id set as attr
    if( isset($attrs['login_user_id']) && intval($attrs['login_user_id']) > 0  ){

        $user_id        = intval($attrs['login_user_id']);
        $current_user   = get_user_by( 'id', $user_id );

        if($current_user->allcaps['manage_options'])
        {
            $allergen_id = $request['id'];
            $allergen = $request['allergen'];
            $allergen_desc = $request['allergen_desc'];
            if(isset($allergen_id) && is_numeric($allergen_id)){
                if(isset($allergen) && isset($allergen_desc) && !empty($allergen) && !empty($allergen_desc)){
                    
                    $wpdb->update( $table_name_allergen, 
                        array("allergen" => $allergen, "allergen_desc" => $allergen_desc),
                        array( 'id' => $allergen_id )
                    );

                    $language = get_option(OS24_PRICE_MANAGER_CUSTOM_LANGUAGE_OPTION);

                    $response = new WP_REST_Response(array("lang"=>$language, "message"=>"allergen updated successfully"));                    
                    $response->set_status(200);
                
                    return $response;
                }
                else{
                    return new WP_Error( 'cant_update_allergen', 'there was a problem updating allergen', array('status' => 500) );
                }
            }
            else{
                return new WP_Error( 'empty_allergen', 'there was a problem in get allergen', array('status' => 404) );
            }
        }
    }
    else{
        return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array('status' => 401) );
    }
}
function os24_price_manager_delete_allergen($request){
    global $wpdb; 
    $table_name_allergen = $wpdb->prefix . '24os_price_manager_allergen';  

    $attrs =  $request->get_attributes();
    // check is login_user_id set as attr
    if( isset($attrs['login_user_id']) && intval($attrs['login_user_id']) > 0  ){

        $user_id        = intval($attrs['login_user_id']);
        $current_user   = get_user_by( 'id', $user_id );
        if($current_user->allcaps['manage_options'])
        {
            $allergen_id = $request['id'];
            if(isset($allergen_id) && is_numeric($allergen_id)){
                $delete = $wpdb->delete( $table_name_allergen, array( 'id' => $allergen_id ));
                if($delete){
                    $language = get_option(OS24_PRICE_MANAGER_CUSTOM_LANGUAGE_OPTION);

                    $response = new WP_REST_Response(array("lang"=>$language, "message"=>"allergen deleted successfully"));                    
                    $response->set_status(200);
                
                    return $response;
                }
                else{
                    return new WP_Error( 'cant_delete_allergen', 'there was a problem deleting allergen', array('status' => 500) );

                }
            
            }
            else{
                return new WP_Error( 'empty_allergen', 'there was a problem in get allergen', array('status' => 404) );
            }
        }
    }
    else{
        return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array('status' => 401) );
    }
}


/**
 *   functions for reset database routes
 **/
function os24_price_manager_reset_database($request){
    global $wpdb; 
    $table_name_menu = $wpdb->prefix . '24os_price_manager_menu';
    $table_name_category = $wpdb->prefix . '24os_price_manager_category';
    $table_name_item = $wpdb->prefix . '24os_price_manager_item';
    $table_name_allergen = $wpdb->prefix . '24os_price_manager_allergen';
    $table_name_statistics = $wpdb->prefix . '24os_price_manager_statistics';

    $attrs =  $request->get_attributes();
    // check is login_user_id set as attr
    if( isset($attrs['login_user_id']) && intval($attrs['login_user_id']) > 0  ){

        $user_id        = intval($attrs['login_user_id']);
        $current_user   = get_user_by( 'id', $user_id );

        if($current_user->allcaps['manage_options'])
        {
            $post_ids_deleted = $wpdb->get_results(
                "SELECT post_id from ". $table_name_menu
            ); 
            foreach ($post_ids_deleted as $post_id_deleted) {
                wp_delete_post( $post_id_deleted->post_id, false);
                
            }
            $wpdb->query( "DELETE from ". $table_name_menu );      
            $wpdb->query( "DELETE from ". $table_name_category );
            $wpdb->query( "DELETE from ". $table_name_item );      
            $wpdb->query( "DELETE from ". $table_name_allergen );
            $wpdb->query( "DELETE from ". $table_name_statistics );      
            
            // deleted all custom post type categories
            $terms = get_terms( array(
                'taxonomy' => OS24_PRICE_MANAGER_CUSTOM_TAXONOMY_CAT,
                'fields' => 'ids',
                'hide_empty' => false
            ) );
            if(count($terms) > 0)
            {
                foreach( $terms as $term_id ) {
                    wp_delete_term( $term_id, OS24_PRICE_MANAGER_CUSTOM_TAXONOMY_CAT );
                }
            }
            // delete POST_META for all post
            delete_post_meta_by_key( OS24_PRICE_MANAGER_CUSTOM_POST_META );

            $language = get_option(OS24_PRICE_MANAGER_CUSTOM_LANGUAGE_OPTION);

            $response = new WP_REST_Response(array("lang"=>$language, "message"=>"database reset successfully"));                     
            $response->set_status(200);

            return $response;
        }
    }
    else{
        return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array('status' => 401) );
    }
}

/**
 *   functions for set permissions routes
 **/
function os24_price_manager_get_permissions($request){
    $attrs =  $request->get_attributes();
    // check is login_user_id set as attr
    if( isset($attrs['login_user_id']) && intval($attrs['login_user_id']) > 0  ){

        $user_id        = intval($attrs['login_user_id']);
        $current_user   = get_user_by( 'id', $user_id );

        if($current_user->allcaps['manage_options'])
        {
            $permissions = json_decode( get_option(OS24_PRICE_MANAGER_CUSTOM_PERMISSIONS_OPTION));
            $response = new WP_REST_Response($permissions);
            $response->set_status(200);

            return $response;
        }
    }
    else{
        return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array('status' => 401) );
    }
}
function os24_price_manager_set_permissions($request){
    $attrs =  $request->get_attributes();
    // check is login_user_id set as attr
    if( isset($attrs['login_user_id']) && intval($attrs['login_user_id']) > 0  ){

        $user_id        = intval($attrs['login_user_id']);
        $current_user   = get_user_by( 'id', $user_id );

        if($current_user->allcaps['manage_options'])
        {
            $permissions_option['author'] = $request['author'];
            $permissions_option['administrator'] = true;
            $permissions_option['editor'] = $request['editor'];
            $permissions_option['subscriber'] = $request['subscriber'];
            $permissions_option['contributor'] = $request['contributor'];

            update_option(OS24_PRICE_MANAGER_CUSTOM_PERMISSIONS_OPTION, json_encode($permissions_option));

            $language = get_option(OS24_PRICE_MANAGER_CUSTOM_LANGUAGE_OPTION);

            $response = new WP_REST_Response(array("lang"=>$language, "message"=>"permissions updated successfully"));                                 
            $response->set_status(200);

            return $response;
        }
    }
    else{
        return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array('status' => 401) );
    }
}

/**
 *   functions for categories and items menu routes
 **/
function os24_price_manager_get_categories_and_items_menu($request){

    global $wpdb; 
    $table_name_category = $wpdb->prefix . '24os_price_manager_category';  
    $table_name_item = $wpdb->prefix . '24os_price_manager_item';  

    $attrs =  $request->get_attributes();
    // check is login_user_id set as attr
    if( isset($attrs['login_user_id']) && intval($attrs['login_user_id']) > 0  ){

        $user_id        = intval($attrs['login_user_id']);
        $current_user   = get_user_by( 'id', $user_id );

        $menu_id =  $request['menu_id'];
        if(isset($menu_id) && is_numeric($menu_id))
        {
            $categories = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * from ". $table_name_category . " Where menu_id = " . (int) $menu_id ." ORDER BY sort"
                )
            ); 

            $categoriesArray = array();
            
            foreach ($categories as $category) {
                
                $categoryData['category_id'] = $category->id;
                $categoryData['menu_id'] = $category->menu_id;
                $categoryData['category_name'] = $category->category_name;
                $categoryData['category_desc'] = $category->category_desc;
                $categoryData['category_img'] = $category->category_img;
                $categoryData['index'] = $category->sort;

                $items = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT * from ". $table_name_item . " WHERE category_id = ". (int) $category->id ." ORDER BY sort"
                    )
                ); 
            
                $itemsArray = array();
                foreach ($items as $item) {
                
                    $itemData['item_id'] = $item->id;
                    $itemData['item_num'] = $item->item_num;
                    $itemData['item_name'] = $item->item_name;
                    $itemData['item_desc'] = $item->item_desc;
                    $itemData['item_allergens'] = json_decode($item->item_allergens);
                    $itemData['item_img'] = $item->item_img;
                    $itemData['index'] = $item->sort;
                    $itemData['item_prices'] = json_decode($item->item_prices);
                    array_push($itemsArray, $itemData);
                }
                
                $categoryData['category_items'] = $itemsArray;
                $categoryData['items_count'] = count($items);
                array_push($categoriesArray, $categoryData);
            }
            

            $response = new WP_REST_Response($categoriesArray);
            $response->set_status(200);

            return $response;
        }
        else{
            return new WP_Error( 'empty_menu', 'not found this menu', array('status' => 404) );

        }
            
    }
    else{
        return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array('status' => 401) );
    }
}
function os24_price_manager_create_category($request){
    global $wpdb; 
    $table_name_category = $wpdb->prefix . '24os_price_manager_category';

    $attrs =  $request->get_attributes();
    // check is login_user_id set as attr
    if( isset($attrs['login_user_id']) && intval($attrs['login_user_id']) > 0  ){

        $user_id        = intval($attrs['login_user_id']);
        $current_user   = get_user_by( 'id', $user_id );

        $menu_id =  $request['menu_id'];
        $index =  $request['index'];
        $category_img =  $request['preview'];
        $category_name = $request['data']['category_name'];
        $category_desc = $request['data']['category_desc'];
        if(isset($menu_id) && is_numeric($menu_id))
        {
            if(isset($category_name) && !empty($category_name))
            {
                // save data to database table
                $sql = $wpdb->insert( $table_name_category, array(
                "menu_id" => $menu_id,
                "category_name" => $category_name,
                "category_desc" => $category_desc,
                "category_img" => $category_img,
                "sort" => intval($index) 
                ));
                if ($sql==true) {

                    $language = get_option(OS24_PRICE_MANAGER_CUSTOM_LANGUAGE_OPTION);

                    $response = new WP_REST_Response(array("lang"=>$language, "message"=>"category created successfully"));
                    $response->set_status(200);
                
                    return $response;
                }
                else{
                    return new WP_Error( 'cant_create_category', 'there was a problem creating category', array('status' => 500) );
                }
            }
        }
        else{
            return new WP_Error( 'empty_menu', 'not found this menu', array('status' => 404) );
        }

    }
    else{
        return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array('status' => 401) );
    }
}
function os24_price_manager_get_category($request){
    global $wpdb; 
    $table_name_category = $wpdb->prefix . '24os_price_manager_category';  

    $attrs =  $request->get_attributes();
    // check is login_user_id set as attr
    if( isset($attrs['login_user_id']) && intval($attrs['login_user_id']) > 0  ){
        $user_id        = intval($attrs['login_user_id']);
        $current_user   = get_user_by( 'id', $user_id );

        $category_id = $request['id'];
        if(isset($category_id) && is_numeric($category_id))
        {
            $category = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * from ". $table_name_category . " where id = %d", $category_id
                )
            ); 

            if($category){
                $response = new WP_REST_Response($category);
                $response->set_status(200);
            
                return $response;
            }
            else{
                return new WP_Error( 'empty_category', 'not found category', array('status' => 404) );
            }
            
        }
        else{
            return new WP_Error( 'empty_category', 'there was a problem in get category', array('status' => 404) );
        }
    }
    else{
        return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array('status' => 401) );
    }
}
function os24_price_manager_update_category($request){
    global $wpdb; 
    $table_name_category = $wpdb->prefix . '24os_price_manager_category';  

    $attrs =  $request->get_attributes();
    // check is login_user_id set as attr
    if( isset($attrs['login_user_id']) && intval($attrs['login_user_id']) > 0  ){

        $user_id        = intval($attrs['login_user_id']);
        $current_user   = get_user_by( 'id', $user_id );

        $category_id =  $request['id'];
        $menu_id =  $request['menu_id'];
        $category_img =  $request['preview'];
        $category_name = $request['data']['category_name'];
        $category_desc = $request['data']['category_desc'];
        if(isset($menu_id) && is_numeric($menu_id) && isset($category_id) && is_numeric($category_id))
        {
            if(isset($category_name) && !empty($category_name))
            {
                $wpdb->update( $table_name_category, 
                    array(
                        "category_name" => $category_name, 
                        "category_desc" => $category_desc,
                        "category_img" => $category_img
                    ),
                    array( 'id' => $category_id , 'menu_id' => $menu_id)
                );

                $language = get_option(OS24_PRICE_MANAGER_CUSTOM_LANGUAGE_OPTION);

                $response = new WP_REST_Response(array("lang"=>$language, "message"=>"category updated successfully"));
                $response->set_status(200);
            
                return $response;
            }
            else{
                return new WP_Error( 'cant_update_category', 'there was a problem updating category', array('status' => 500) );
            }
        }
        else{
            return new WP_Error( 'empty_menu', 'not found this menu', array('status' => 404) );
        }
    
    }
    else{
        return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array('status' => 401) );
    }
}
function os24_price_manager_delete_category($request){
    global $wpdb; 
    $table_name_category = $wpdb->prefix . '24os_price_manager_category';  
    $table_name_item = $wpdb->prefix . '24os_price_manager_item';  

    $attrs =  $request->get_attributes();
    // check is login_user_id set as attr
    if( isset($attrs['login_user_id']) && intval($attrs['login_user_id']) > 0  ){

        $user_id        = intval($attrs['login_user_id']);
        $current_user   = get_user_by( 'id', $user_id );

        $category_id =  $request['id'];
        if(isset($category_id) && is_numeric($category_id))
        {
            $category = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * from ". $table_name_category . " where id = %d", $category_id
                )
            ); 
            $category_index =  $category->sort;
            $category_menu_id =  $category->menu_id;

            $delete_category = $wpdb->delete( $table_name_category, array( 'id' => $category_id ));
            $delete_items = $wpdb->delete( $table_name_item, array( 'category_id' => $category_id ));

            $categories = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * from ". $table_name_category . " where menu_id = %d and sort > ". (int) $category_index, $category_menu_id
                )
            );

            foreach ($categories as $category_sort) {
                $category_sort_id = $category_sort->id;
                $category_sort_index = $category_sort->sort - 1;

                $wpdb->update( $table_name_category, 
                    array('sort' => $category_sort_index),
                    array( 'id' => $category_sort_id)
                );
            }

            $language = get_option(OS24_PRICE_MANAGER_CUSTOM_LANGUAGE_OPTION);

            $response = new WP_REST_Response(array("lang"=>$language, "message"=>"category deleted successfully"));
            $response->set_status(200);
        
            return $response;
        
        }
        else{
            return new WP_Error( 'empty_category', 'not found this category', array('status' => 404) );
        }
    }
    else{
        return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array('status' => 401) );
    }
}
function os24_price_manager_sorting_categories($request){
    global $wpdb; 
    $table_name_category = $wpdb->prefix . '24os_price_manager_category';  

    $attrs =  $request->get_attributes();
    // check is login_user_id set as attr
    if( isset($attrs['login_user_id']) && intval($attrs['login_user_id']) > 0  ){

        $user_id        = intval($attrs['login_user_id']);
        $current_user   = get_user_by( 'id', $user_id );

        $sortingArray = $request['sorting'];
        foreach ($sortingArray as $category) {
            $category_id = $category['id'];
            $category_index = $category['index'];

            $wpdb->update( $table_name_category, 
                array('sort' => $category_index),
                array( 'id' => $category_id)
            );

        }
        $language = get_option(OS24_PRICE_MANAGER_CUSTOM_LANGUAGE_OPTION);

        $response = new WP_REST_Response(array("lang"=>$language, "message"=>"categories sorting updated successfully"));
        $response->set_status(200);
    
        return $response;
    }
    else{
        return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array('status' => 401) );
    }
}
function os24_price_manager_get_items_by_category_id($request){
    global $wpdb; 
    $table_name_item = $wpdb->prefix . '24os_price_manager_item';
    
    $attrs =  $request->get_attributes();
    // check is login_user_id set as attr
    if( isset($attrs['login_user_id']) && intval($attrs['login_user_id']) > 0  ){

        $user_id        = intval($attrs['login_user_id']);
        $current_user   = get_user_by( 'id', $user_id );

        $category_id = $request['category_id'];
        if(isset($category_id) && is_numeric($category_id))
        {
            $items = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * from ". $table_name_item . " where category_id = %d ORDER BY sort", $category_id
                )
            ); 
            $response = new WP_REST_Response($items);
            $response->set_status(200);
        
            return $response;
        }
        else{
            return new WP_Error( 'empty_category', 'there was a problem in get category', array('status' => 404) );
        }
    }
    else{
        return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array('status' => 401) );
    }
}
function os24_price_manager_create_item($request){
    global $wpdb; 
    $table_name_item = $wpdb->prefix . '24os_price_manager_item';
    $table_name_category = $wpdb->prefix . '24os_price_manager_category';

    $attrs =  $request->get_attributes();
    // check is login_user_id set as attr
    if( isset($attrs['login_user_id']) && intval($attrs['login_user_id']) > 0  ){

        $user_id        = intval($attrs['login_user_id']);
        $current_user   = get_user_by( 'id', $user_id );

        $category_id =  $request['category_id'];
        $index =  $request['index'];
        $menu_id =  $request['menu_id'];
        $item_img =  $request['preview'];
        if(isset($menu_id) && isset($category_id) && is_numeric($category_id))
        {
            $item_num = $request['data']['item_num'];
            $item_name = $request['data']['item_name'];
            $item_desc = $request['data']['item_desc'];
            $allergens = $request['data']['allergens'];
            $prices = $request['data']['prices'];
            
            if(isset($item_num) && is_numeric($item_num) && isset($item_name) && !empty($item_name)
                && isset($item_desc) && !empty($item_desc))
            {
                $categories = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT * from ". $table_name_category . " Where menu_id = " . (int) $menu_id 
                    )
                ); 

                $flag = true;
                foreach ($categories as $c) {
                    $items = $wpdb->get_results(
                        $wpdb->prepare(
                            "SELECT * from ". $table_name_item . " Where category_id = " . (int) $c->id 
                        )
                    ); 
                    foreach ($items as $item) {
                        if($item->item_num == $item_num){
                            $flag = false;
                            break;
                        }
                    }
                    if($flag == false){ break;}
                }
                
                $language = get_option(OS24_PRICE_MANAGER_CUSTOM_LANGUAGE_OPTION);

                if($flag){
                    // save data to database table
                    $sql = $wpdb->insert( $table_name_item, array(
                        "category_id" => $category_id,
                        "item_num" => $item_num,
                        "item_name" => $item_name,
                        "item_desc" => $item_desc,
                        "item_img" => $item_img,
                        "item_allergens" => json_encode($allergens),
                        "item_prices" => json_encode($prices),
                        "sort" => intval($index) 
                        ));
                        if ($sql==true) {

                            $response = new WP_REST_Response(array("lang"=>$language, "message"=>"item created successfully"));
                            $response->set_status(200);
                        
                            return $response;
                        }
                        else{
                            return new WP_Error( 'cant_create_item', 'there was a problem creating item', array('status' => 500) );
                        }
                }
                else{
                    if($language == "en"){
                        $response = new WP_REST_Response(array("error"=>"Error", "message"=>"The item number already exists"));
                    }
                    else{
                        $response = new WP_REST_Response(array("error"=>"Fehler", "message"=>"Die Artikelnummer existiert bereits"));
                    }
                    $response->set_status(200);
                
                    return $response;
                }
            
            }
            
        
        }
        else{
            return new WP_Error( 'empty_category', 'not found this category', array('status' => 404) );
        }

    }
    else{
        return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array('status' => 401) );
    }
}
function os24_price_manager_get_item($request){
    global $wpdb; 
    $table_name_item = $wpdb->prefix . '24os_price_manager_item';  

    $attrs =  $request->get_attributes();
    // check is login_user_id set as attr
    if( isset($attrs['login_user_id']) && intval($attrs['login_user_id']) > 0  ){
        $user_id        = intval($attrs['login_user_id']);
        $current_user   = get_user_by( 'id', $user_id );

        $item_id = $request['id'];
        if(isset($item_id) && is_numeric($item_id))
        {
            $item = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * from ". $table_name_item . " where id = %d", $item_id
                )
            ); 

            if($item){
                $response = new WP_REST_Response($item);
                $response->set_status(200);
            
                return $response;
            }
            else{
                return new WP_Error( 'empty_item', 'not found item', array('status' => 404) );
            }
            
        }
        else{
            return new WP_Error( 'empty_item', 'there was a problem in get item', array('status' => 404) );
        }
    }
    else{
        return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array('status' => 401) );
    }
}
function os24_price_manager_update_item($request){
    global $wpdb; 
    $table_name_item = $wpdb->prefix . '24os_price_manager_item';  
    $table_name_category = $wpdb->prefix . '24os_price_manager_category';  

    $attrs =  $request->get_attributes();
    
    // check is login_user_id set as attr
    if( isset($attrs['login_user_id']) && intval($attrs['login_user_id']) > 0  ){

        $user_id        = intval($attrs['login_user_id']);
        $current_user   = get_user_by( 'id', $user_id );

        $item_id =  $request['id'];
        $category_id =  $request['category_id'];
        $item_num = $request['data']['item_num'];
        $item_name = $request['data']['item_name'];
        $item_desc = $request['data']['item_desc'];
        $item_allergens = $request['data']['allergens'];
        $item_prices = $request['data']['prices'];
        $menu_id =  $request['menu_id'];
        $item_img = $request['preview'];
        if(isset($menu_id) && isset($category_id) && is_numeric($category_id) && isset($item_id) && is_numeric($item_id))
        {
            if(isset($item_num) && is_numeric($item_num) &&
                isset($item_name) && !empty($item_name) &&
                isset($item_desc) && !empty($item_desc))
            {
                $categories = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT * from ". $table_name_category . " Where menu_id = " . (int) $menu_id 
                    )
                ); 

                $flag = true;
                foreach ($categories as $c) {
                    $items = $wpdb->get_results(
                        $wpdb->prepare(
                            "SELECT * from ". $table_name_item . " Where category_id = " . (int) $c->id 
                        )
                    ); 
                    foreach ($items as $item) {
                        if($item->item_num == $item_num && $item->id != $item_id ){
                            $flag = false;
                            break;
                        }
                    }
                    if($flag == false){ break;}
                }

                $language = get_option(OS24_PRICE_MANAGER_CUSTOM_LANGUAGE_OPTION);

                if($flag){
                    $wpdb->update( $table_name_item, 
                        array(
                            "item_num" => $item_num, 
                            "item_name" => $item_name,
                            "item_desc" => $item_desc, 
                            "item_img" => $item_img, 
                            "item_allergens" => json_encode($item_allergens),
                            "item_prices" => json_encode($item_prices), 
                        ),
                        array( 'id' => $item_id , 'category_id' => $category_id)
                    );

                    $response = new WP_REST_Response(array("lang"=>$language, "message"=>"item updated successfully"));
                    $response->set_status(200);
                
                    return $response;
                }
                else{
                    if($language == "en"){
                        $response = new WP_REST_Response(array("error"=>"Error", "message"=>"The item number already exists"));
                    }
                    else{
                        $response = new WP_REST_Response(array("error"=>"Fehler", "message"=>"Die Artikelnummer existiert bereits"));
                    }
                    $response->set_status(200);
                
                    return $response;
                }
            }
            else{
                return new WP_Error( 'cant_update_item', 'there was a problem updating item', array('status' => 500) );
            }
        }
        else{
            return new WP_Error( 'empty_category', 'not found this category', array('status' => 404) );
        }
    
    }
    else{
        return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array('status' => 401) );
    }
}
function os24_price_manager_delete_item($request){
    global $wpdb; 
    $table_name_item = $wpdb->prefix . '24os_price_manager_item';  

    $attrs =  $request->get_attributes();
    // check is login_user_id set as attr
    if( isset($attrs['login_user_id']) && intval($attrs['login_user_id']) > 0  ){

        $user_id        = intval($attrs['login_user_id']);
        $current_user   = get_user_by( 'id', $user_id );

        $item_id =  $request['id'];
        if(isset($item_id) && is_numeric($item_id))
        {
            $item = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * from ". $table_name_item . " where id = %d", $item_id
                )
            ); 
            $item_index =  $item->sort;
            $item_category_id =  $item->category_id;

            $delete_items = $wpdb->delete( $table_name_item, array( 'id' => $item_id ));

            $items = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * from ". $table_name_item . " where category_id = %d and sort > ". (int) $item_index, $item_category_id
                )
            );

            foreach ($items as $item_sort) {
                $item_sort_id = $item_sort->id;
                $item_sort_index = $item_sort->sort - 1;

                $wpdb->update( $table_name_item, 
                    array('sort' => $item_sort_index),
                    array( 'id' => $item_sort_id)
                );
            }
            $language = get_option(OS24_PRICE_MANAGER_CUSTOM_LANGUAGE_OPTION);

            $response = new WP_REST_Response(array("lang"=>$language, "message"=>"item deleted successfully"));   
            $response->set_status(200);
        
            return $response;
        
        }
        else{
            return new WP_Error( 'empty_category', 'not found this category', array('status' => 404) );
        }
    }
    else{
        return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array('status' => 401) );
    }
}
function os24_price_manager_sorting_items($request){
    global $wpdb; 
    $table_name_item = $wpdb->prefix . '24os_price_manager_item';  

    $attrs =  $request->get_attributes();
    // check is login_user_id set as attr
    if( isset($attrs['login_user_id']) && intval($attrs['login_user_id']) > 0  ){

        $user_id        = intval($attrs['login_user_id']);
        $current_user   = get_user_by( 'id', $user_id );

        $sortingArray = $request['sorting'];
        foreach ($sortingArray as $item) {
            $item_id = $item['id'];
            $item_index = $item['index'];

            $wpdb->update( $table_name_item, 
                array('sort' => $item_index),
                array( 'id' => $item_id)
            );

        }
        $language = get_option(OS24_PRICE_MANAGER_CUSTOM_LANGUAGE_OPTION);
        $response = new WP_REST_Response(array("lang"=>$language, "message"=>"items sorting updated successfully"));     
        $response->set_status(200);
    
        return $response;
    }
    else{
        return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array('status' => 401) );
    }
}

/**
 *   functions for statistics menu routes
 **/
function os24_price_manager_get_statistics_by_menu_id($request){
    global $wpdb; 
    $table_name_statistics = $wpdb->prefix . '24os_price_manager_statistics';
    
    $attrs =  $request->get_attributes();
    // check is login_user_id set as attr
    if( isset($attrs['login_user_id']) && intval($attrs['login_user_id']) > 0  ){

        $user_id        = intval($attrs['login_user_id']);
        $current_user   = get_user_by( 'id', $user_id );

        $menu_id = $request['menu_id'];
        if(isset($menu_id) && is_numeric($menu_id))
        {
            $statistics = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * from ". $table_name_statistics . " where menu_id = %d", $menu_id
                )
            ); 

            $visitors_visit_last = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * from ".$table_name_statistics." WHERE id IN(
                    SELECT MAX(id) from ".$table_name_statistics." WHERE menu_id = %d GROUP BY visitor_IP)", $menu_id
                )
            );
            $statisticsArray['total_visits_count'] = count($statistics);
            $statisticsArray['visitors_count'] = count($visitors_visit_last);
            $temp = array();
            foreach ($visitors_visit_last as $visitor_visit_last) {
                
                $menu_id =  $visitor_visit_last->menu_id;
                $visitor_IP =  $visitor_visit_last->visitor_IP;

                $visitor_visits = $wpdb->get_results(
                    $wpdb->prepare("SELECT * from " . $table_name_statistics . " WHERE menu_id = %d and visitor_IP = %s ORDER BY id DESC", $menu_id, $visitor_IP)
                );

                $info_visit['visitor_visit_last'] =  $visitor_visit_last;   
                $info_visit['visitor_visits_count'] = count($visitor_visits);
                $info_visit['visitor_old_visits'] = $visitor_visits;
                array_push($temp, $info_visit);
            }
            $statisticsArray['visitors_visit_last'] = $temp;

            $response = new WP_REST_Response($statisticsArray);
            $response->set_status(200);
        
            return $response;
        }
        else{
            return new WP_Error( 'empty_category', 'there was a problem in get category', array('status' => 404) );
        }
    }
    else{
        return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array('status' => 401) );
    }
}

/**
 *   functions for set language routes
 **/
function os24_price_manager_get_language($request){
    $attrs =  $request->get_attributes();
    // check is login_user_id set as attr
    if( isset($attrs['login_user_id']) && intval($attrs['login_user_id']) > 0  ){

        $user_id        = intval($attrs['login_user_id']);
        $current_user   = get_user_by( 'id', $user_id );

        
        $language = get_option(OS24_PRICE_MANAGER_CUSTOM_LANGUAGE_OPTION);
        $response = new WP_REST_Response($language);
        $response->set_status(200);

        return $response;
        
    }
    else{
        return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array('status' => 401) );
    }
}
function os24_price_manager_set_language($request){
    $attrs =  $request->get_attributes();
    // check is login_user_id set as attr
    if( isset($attrs['login_user_id']) && intval($attrs['login_user_id']) > 0  ){

        $user_id        = intval($attrs['login_user_id']);
        $current_user   = get_user_by( 'id', $user_id );

        // only languages that ship with the plugin (assets/lang/<code>.json)
        $language = sanitize_key($request['lang']);
        if(!in_array($language, array('en', 'de'), true)){
            return new WP_Error( 'invalid_language', 'unsupported language', array('status' => 400) );
        }

        update_option(OS24_PRICE_MANAGER_CUSTOM_LANGUAGE_OPTION, $language);

        $response = new WP_REST_Response(array("lang"=>$language, "message"=>"language switched successfully"));
        $response->set_status(200);

        return $response;
        
    }
    else{
        return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array('status' => 401) );
    }
}

//scripts for custom post this plugin
function os24_price_manager_add_custom_post_scripts(){
    global $post;
    // if( OS24_PRICE_MANAGER_CUSTOM_POST_TYPE === $post->post_type || "post" === $post->post_type){
    if( OS24_PRICE_MANAGER_CUSTOM_POST_TYPE === $post->post_type){
        wp_enqueue_script("jquery");
        wp_enqueue_script("24os-price-menu-script", OS24_PRICE_MANAGER_PLUGIN_URL."/post/24os_price_menu_script.js", "", true);
        wp_localize_script("24os-price-menu-script", "menupricemanagervisitorajaxurl", admin_url("admin-ajax.php")); 
    }
}
add_action("wp_enqueue_scripts", "os24_price_manager_add_custom_post_scripts");

// AJAX for visitor :  handling AJAX requests from unauthenticated users
function os24_price_manager_visitor_ajax_handler()
{
    global $wpdb;
    $table_name_statistics = $wpdb->prefix . '24os_price_manager_statistics';
    $table_name_menu = $wpdb->prefix . '24os_price_manager_menu';

    $param   = isset($_REQUEST['param']) ? sanitize_text_field(wp_unslash($_REQUEST['param'])) : '';
    $menu_id = isset($_REQUEST['menu_id']) ? absint($_REQUEST['menu_id']) : 0;

    if($param !== "save_visitor_info" || $menu_id < 1)
    {
        wp_send_json(array("status"=>-1, "message"=>"Something went wrong."));
    }

    // only count visits of existing, published menus
    $menu_active = $wpdb->get_var(
        $wpdb->prepare("SELECT active FROM ". $table_name_menu ." WHERE id = %d AND trash = 0", $menu_id)
    );
    if(!$menu_active)
    {
        wp_send_json(array("status"=>-1, "message"=>"Something went wrong."));
    }

    $user_agent = ! empty( $_SERVER['HTTP_USER_AGENT'] )
        ? substr( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ), 0, 256 ) : '';
    // REMOTE_ADDR only: HTTP_CLIENT_IP / X-Forwarded-For are client-controlled and can be spoofed.
    // Sites behind a trusted proxy can supply the real IP through the 'wpb_get_ip' filter.
    $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
    $ip = apply_filters( 'wpb_get_ip', $ip );
    if(!filter_var($ip, FILTER_VALIDATE_IP))
    {
        wp_send_json(array("status"=>0, "message"=>"invalid visitor ip"));
    }
    if($user_agent === '')
    {
        wp_send_json(array("status"=>0, "message"=>"user agent is warning"));
    }

    $saved = $wpdb->insert( $table_name_statistics, array(
        "menu_id"       => $menu_id,
        "visitor_IP"    => $ip,
        "visitor_agent" => $user_agent,
    ), array('%d','%s','%s'));

    if ($saved) {
        wp_send_json(array("status"=>1, "message"=>"user info save successfully"));
    }
    wp_send_json(array("status"=>0, "message"=>"user info not save"));
}
add_action('wp_ajax_menupricemanagervisitorlibrary', 'os24_price_manager_visitor_ajax_handler');
add_action('wp_ajax_nopriv_menupricemanagervisitorlibrary', 'os24_price_manager_visitor_ajax_handler');

// escape an image source: http(s) URLs and base64 images (data:image/...) only
function os24_price_manager_esc_img_src($src)
{
    $src = trim((string) $src);
    if(preg_match('#^data:image/(png|jpe?g|gif|webp);base64,[A-Za-z0-9+/=\s]+$#i', $src))
        return esc_attr($src);
    return esc_url($src, array('http', 'https'));
}

// add shortcode
function os24_price_manager_view_price_menu_cb($attr)
{
    if(isset($attr['id']) && !empty($attr['id']))
    {
        $attr_price_menu_id = absint($attr['id']);
        $attr_price_menu_style = isset($attr['style']) ? absint($attr['style']) : 1;

        global $wpdb;
        $table_name_menu = $wpdb->prefix . '24os_price_manager_menu';
        $table_name_category = $wpdb->prefix . '24os_price_manager_category';  
        $table_name_item = $wpdb->prefix . '24os_price_manager_item';  

        $price_menu = $wpdb->get_row( 
            $wpdb->prepare( 
                "SELECT * FROM ". $table_name_menu ." WHERE id = %d", $attr_price_menu_id
            )
        );
        if($price_menu)
        {
            if($price_menu->active){
                $p_id = get_post($price_menu->post_id);
                
                $categories = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT * from ". $table_name_category . " Where menu_id = " . (int) $price_menu->id ." ORDER BY sort"
                    )
                );     
                $data = array();            
                foreach ($categories as $category) {
                    $temp = array();

                    array_push($temp, $category->category_name);
                    array_push($temp, $category->category_img);

                    $items = $wpdb->get_results(
                        $wpdb->prepare(
                            "SELECT * from ". $table_name_item . " WHERE category_id = ". (int) $category->id ." ORDER BY sort"
                        )
                    ); 

                    $itemsData = array();
                    foreach ($items as $item) {
                    
                        $itemData['item_num'] = $item->item_num;
                        $itemData['item_name'] = $item->item_name;
                        $itemData['item_desc'] = $item->item_desc;
                        $itemData['item_allergens'] = [];
                        if(json_decode($item->item_allergens) != "")
                        {
                            $itemData['item_allergens'] = json_decode($item->item_allergens);
                        }
                        
                        $itemData['item_prices'] = json_decode($item->item_prices);
                        $itemData['item_img'] = $item->item_img;
                        array_push($itemsData, $itemData);
                    }
                    array_push($temp, $itemsData);
                    array_push($data, $temp);
                    
                }

                $option_image_menu = get_post_meta( $price_menu->post_id, OS24_PRICE_MANAGER_CUSTOM_POST_META, true );
                $html = '';
                if($attr_price_menu_style == 1)
                {
                    $html .= '<div class="menu_id" id="'.esc_attr($attr_price_menu_id).'" style="display:none;"></div>';
                    $html .= '<div style="box-shadow: 0 2px 7px 1px #aaa;background:#938661;padding:20px;"class="menu-price-manager">';
                    
                    foreach ($data as $c) {
                        $html .= '<div class="category">
                                    <div class="title" style="text-align: center;color: #fef;">
                                        <h4 style="margin:20px 0;text-transform: capitalize;">
                                            '. esc_html($c[0]) .'
                                        </h4>
                                    </div>';
                        foreach ($c[2] as $i) {
                            $html .= '<div class="item" style="margin-bottom:20px;color: #fff;min-width:80%;">
                            <div style="display:flex;align-items:baseline;">

                                <span style="width:5%;">'. esc_html($i['item_num']) .'.</span>
                                <div style="width:95%;">
                                    <div class="price-display" style="display: flex;align-items: end;justify-content: space-between;font-size: 1.2em;">
                                        <span class="item-name" style="font-weight: bold;margin-right: 0px;text-align: left;height:29px;"> 
                                        '. esc_html($i['item_name']) .'
                                        </span>
                                        <span style="font-size:.8rem;
                                            color: #ddd;
                                            padding-left:2px;padding-bottom: 6px">';
                                            if(count($i['item_allergens'])>0)
                                            {
                                                $html .= '(' . esc_html(implode(', ', $i['item_allergens'])) . ')';
                                            }  
                                        $html .='</span>';
                                        $html .= '<span class="price-separator" style="flex: 1;border-bottom: 2px dotted #ddd;margin: 0.6em 5px 0;"></span>
                                        <span class="item-price" style="display:flex;font-size:1.1rem;font-weight: bold;text-align: right;">';
                                            if(count($i['item_prices']) > 0)
                                            {
                                                $html .= '<div style="height:25px;margin-right:2px;">
                                                        '. esc_html($i['item_prices'][0]->item_price) .'
                                                        <span style="font-size:.8rem;
                                                                font-style: italic;
                                                                color: #ddd;">
                                                            ('. esc_html($i['item_prices'][0]->item_size) .')
                                                        </span> 
                                                    </div> '; 
                                            }
                                        
                                    $html .='</span>
                                    </div>';
                                    if(count($i['item_prices']) > 1)
                                    {
                                        for ($j=1; $j < count($i['item_prices']); $j++) { 
                                
                                            $html .= '<span style="margin-right:2px;
                                                                font-style: italic;
                                                                font-family:Arial, Helvetica, sans-serif;
                                                                color: #fff;
                                                                font-weight: 600;
                                                                width:100%;
                                                                font-size: .9rem;"">
                                                    '. esc_html($i['item_prices'][$j]->item_price) .'
                                                    <span style="font-size:.8rem;
                                                            font-style: italic;
                                                            color: #ddd;
                                                            font-weight: 300;">
                                                        ('. esc_html($i['item_prices'][$j]->item_size) .')
                                                    </span> 
                                                </span> '; 
                                                if($j < count($i['item_prices']) - 1)
                                                {
                                                    $html .= '<span style="font-size:.8rem;
                                                            color: #ddd;">|</span>';
                                                }
                                                
                                        }
                                    }
                                    $html .='<p style="margin: 0;
                                        font-style: italic;
                                        font-family:Arial, Helvetica, sans-serif;
                                        color: #eee;
                                        width:100%;
                                        line-break:auto;
                                        font-size: .9rem;">

                                        '. esc_html($i['item_desc']) .'
                                    </p>
                                </div>
                            
                            </div>
                            
                            </div>';
                        }
                    
                
                        $html .= '</div>';
                    }
                    $html .= '</div>';
                }
                elseif($attr_price_menu_style == 2)
                {
                    $html .= '<div class="menu_id" id="'.esc_attr($attr_price_menu_id).'" style="display:none;"></div>';
                    $html .= '<div style="box-shadow: 0 2px 7px 1px #aaa;background: darkcyan;padding:20px;"class="menu-price-manager">';
                    
                    foreach ($data as $c) {
                        $html .= '<div class="category">
                                    <div class="title" style="display:flex;justify-content:space-between;color: #fef;padding:10px 10px 10px 30px;">
                                        <h4 style="text-transform: capitalize;margin: 10px 0;font-weight: 500;">
                                            '. esc_html($c[0]) .'
                                        </h4>';
                                        if($option_image_menu == 'category_image')
                                        {
                                            $html .= '<img style="border: 3px solid #fff;height:120px; width:150px;
                                            border-radius: 5px;object-fit: cover;" src="'. os24_price_manager_esc_img_src($c[1]) .'">'; 
                                        }
                                        
                                    $html .= '</div>';
                                    
                        foreach ($c[2] as $i) {
                            $html .= '<div class="item" style="margin-bottom:20px;color: #fff;min-width:80%;">
                                <div style="display: flex;
                                padding: 10px;
                                background: #5f9ea091;
                                border-radius: 5px;
                                border: 1px solid cadetblue;>

                                <span style="width:5%;">'. esc_html($i['item_num']) .'.</span>
                                
                                <div style="width:85%;">
                                    <div class="price-display" style="display: flex;align-items: end;justify-content: space-between;font-size: 1.2em;">
                                        <span class="item-name" style="font-weight: bold;margin-right: 0px;text-align: left;height:29px;"> 
                                        '. esc_html($i['item_name']) .'
                                        </span>
                                        <span style="font-size:.8rem;
                                            color: #ddd;
                                            padding-left:2px;padding-bottom: 6px">';
                                            if(count($i['item_allergens'])>0)
                                            {
                                                $html .= '(' . esc_html(implode(', ', $i['item_allergens'])) . ')';
                                            }  
                                        $html .='</span>';
                                        $html .= '<span class="price-separator" style="flex: 1;border-bottom: 2px dotted #ddd;margin: 0.6em 5px 0;"></span>
                                        
                                    </div>';
                                    if(count($i['item_prices']) > 1)
                                    {
                                        for ($j=0; $j < count($i['item_prices']); $j++) { 
                                
                                            $html .= '<span style="margin-right:2px;
                                                                font-style: italic;
                                                                font-family:Arial, Helvetica, sans-serif;
                                                                color: #fff;
                                                                font-weight: 600;
                                                                width:100%;
                                                                font-size: .9rem;"">
                                                    '. esc_html($i['item_prices'][$j]->item_price) .'
                                                    <span style="font-size:.8rem;
                                                            font-style: italic;
                                                            color: #ddd;
                                                            font-weight: 300;">
                                                        ('. esc_html($i['item_prices'][$j]->item_size) .')
                                                    </span> 
                                                </span> '; 
                                                if($j < count($i['item_prices']) - 1)
                                                {
                                                    $html .= '<span style="font-size:.8rem;
                                                            color: #ddd;">|</span>';
                                                }
                                                
                                        }
                                    }
                                    $html .='<p style="margin: 0;
                                        font-style: italic;
                                        font-family:Arial, Helvetica, sans-serif;
                                        color: #eee;
                                        width:100%;
                                        line-break:auto;
                                        font-size: .9rem;">

                                        '. esc_html($i['item_desc']) .'
                                    </p>
                                </div>';
                            
                                if($option_image_menu == 'item_image')
                                {
                                    $html .= '<img style="border: 3px solid #fff;height:120px; width:150px;
                                            border-radius: 5px;object-fit: cover;" src="'. os24_price_manager_esc_img_src($i['item_img']) .'">';
                                }
                                
                            $html .= '</div> </div>';
                        }
                    
                
                        $html .= '</div>';
                    }
                    $html .= '</div>';
                }
                else{
                    $html .= '<h3>
                                The price menu <b>Style</b> is not available at the moment. 
                                Please visit later
                            </h3>';
    
                }
                return $html;
            }
            else{
                return '<h3>
                            The price menu for this publication is not available at the moment. 
                            Please visit later
                        </h3>';
            }
        }
        else{
            return '<div>
                        <h3>
                            No Price Menu here !
                        </h3>
                    </div>';
        }
    }

}
add_shortcode( OS24_PRICE_MANAGER_SHORTCODE, 'os24_price_manager_view_price_menu_cb' );


// angular app
function os24_price_manager_price_main_shortcode_cb() {
    wp_enqueue_script( 'os24_price_manager_ng_main' );
    wp_enqueue_script( 'os24_price_manager_ng_polyfills' );
    wp_enqueue_script( 'os24_price_manager_ng_runtime' );
    wp_enqueue_style( 'os24_price_manager_ng_styles' );
    wp_enqueue_style( 'os24_price_manager_ng_icon' );
    
    return "<app-root></app-root>";
}
add_shortcode( 'price_manager_app', 'os24_price_manager_price_main_shortcode_cb' );

function os24_price_manager_price_menu_shortcode_cb() {
    wp_enqueue_script( 'os24_price_manager_menu_ng_main' );
    wp_enqueue_script( 'os24_price_manager_menu_ng_polyfills' );
    wp_enqueue_script( 'os24_price_manager_menu_ng_runtime' );
    wp_enqueue_style( 'os24_price_manager_menu_ng_styles' );
    wp_enqueue_style( 'os24_price_manager_menu_ng_icon' );

    return "<app-root></app-root>";
}
add_shortcode( 'price_manager_menu_app', 'os24_price_manager_price_menu_shortcode_cb' );

function os24_price_manager_settings_shortcode_cb() {
    wp_enqueue_script( 'os24_price_manager_settings_ng_main' );
    wp_enqueue_script( 'os24_price_manager_settings_ng_polyfills' );
    wp_enqueue_script( 'os24_price_manager_settings_ng_runtime' );
    wp_enqueue_style( 'os24_price_manager_settings_ng_styles' );
    wp_enqueue_style( 'os24_price_manager_settings_ng_icon' );

    return "<app-root></app-root>";
}
add_shortcode( 'price_manager_settings_app', 'os24_price_manager_settings_shortcode_cb' );

function os24_price_manager_recovery_shortcode_cb() {
    wp_enqueue_script( 'os24_price_manager_recovery_ng_main' );
    wp_enqueue_script( 'os24_price_manager_recovery_ng_polyfills' );
    wp_enqueue_script( 'os24_price_manager_recovery_ng_runtime' );
    wp_enqueue_style( 'os24_price_manager_recovery_ng_styles' );
    wp_enqueue_style( 'os24_price_manager_recovery_ng_icon' );

    return "<app-root></app-root>";
}
add_shortcode( 'price_manager_recovery_app', 'os24_price_manager_recovery_shortcode_cb' );
?>