<?php
/*
    Plugin Name: Woocommerce Random Featured Products
    description: This plugin takes the last 20 products uploaded and picks 10 at random to set as "Featured Products"
    Author: Justin Tharpe
    Version: Beta 1.0.0
*/


if (!defined('ABSPATH')) die('No direct access allowed');

function create_plugin_settings_page()
{

    // Add the menu item and page
    $page_title = 'Woo Random Featured';
    $menu_title = 'WooCommerce Random Featured Products';
    $capability = 'manage_options';
    $slug = 'woo_random_featured';
    $callback = 'plugin_settings_page_content';
    $icon = 'dashicons-admin-plugins';
    $position = 100;   
    add_menu_page($page_title, $menu_title, $capability, $slug, $callback, $icon, $position);
    }

function plugin_settings_page_content(){
    global $content;
    global $wpdb;

    ?><center><h1>WooCommerce Random Featured Products</h1></center><?php

    ?>
    <form method="post" action="options.php"> <?php
    settings_fields( "header_section" );
    do_settings_sections( "woo_random_featured" );
    submit_button();
    ?></form><?php
    
    //RunFeatured();
    //echo "<h2>Done!</h2>";
    var_dump(get_option( 'jt_num_to_keep' ));

}

function jt_wrf_display_options(){

    add_settings_section( 'header_section', 'Product Selection Settings', 'jt_header_func', 'woo_random_featured' );
    add_settings_field( 'jt_num_to_keep', 'How Many Prodcuts to Keep', 'jt_num_to_keep', 'woo_random_featured', 'header_section' );
    add_settings_field( 'jt_when_to_change', 'How Often Should Featured Products Change (In Seconds)', 'jt_when_to_change', 'woo_random_featured', 'header_section' );
    register_setting( 'header_section', 'jt_num_to_keep' );
    register_setting( 'header_section', 'jt_when_to_change' );
}

function jt_header_func(){echo "This will configured various options associated with the plugin.";}

function jt_num_to_keep(){

    ?>
    <input type="text" name="jt_num_to_keep" id="jt_num_to_keep" value="<?php echo get_option( 'jt_num_to_keep' ); ?>" />
    <?php
}

function jt_when_to_change(){

    ?>
    <input type="text" name="jt_when_to_change" id="jt_when_to_change" value="<?php echo get_option( 'jt_when_to_change' ); ?>" />
    <?php
}

function reconfigure_options(){
    RunFeatured();
}


function CleanupFeatured(){

global $wpdb;
$table = "wp_term_relationships";
$term = term_exists('featured');
$tag = array( (int)$term );
$taxonomy = 'product_visibility';

$ids = $wpdb->get_col( "SELECT object_id FROM $wpdb->term_relationships WHERE term_taxonomy_id = " . $term );

if ( count( $ids ) > 0 ) 
    foreach($ids as $id){
        wp_remove_object_terms( $id, $tag, $taxonomy );
        
}

else
    echo "No Featured Products :(";
}

function GetRecentPosts(){

    global $wpdb;
    $prod = 'product';

    $query = $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = %s ORDER BY ID DESC LIMIT 0,20", $prod );

    $item_count = get_option( 'jt_num_to_keep' );
    $result = $wpdb->get_results($query);
    $rand_keys = array_rand($result, (int)$item_count);

     foreach ($rand_keys as $rand_id){
        $randarray[] = $result[$rand_id]->ID;
    } 
    $item_count = "";
    return $randarray;
}

function SetFeaturedProducts($rand_ids){

    global $wpdb;
    $term = term_exists('featured');
    $tag = array( (int)$term );
    $taxonomy = 'product_visibility';
    $append = True;
    foreach ($rand_ids as $id){

        wp_set_object_terms( $id, $tag, $taxonomy, true );
    }
}

function RunFeatured(){
    CleanupFeatured();
    $rand_ids = GetRecentPosts();
    SetFeaturedProducts($rand_ids);
}



 function on_add_cron_interval( $schedules ) { 
    $interval = get_option( 'jt_when_to_change' );
    $schedules['one_week'] = array(
        'interval' => (int)$interval,
        'display'  => esc_html__( 'Every Week' ), );
    return $schedules;
} 

$timestamp = wp_next_scheduled( 'on_woo_featured_cron_hook' );
wp_unschedule_event( $timestamp, 'on_woo_featured_cron_hook' );

if ( ! wp_next_scheduled( 'on_woo_featured_cron_hook' ) ) {
    wp_schedule_event( time(), 'one_week', 'on_woo_featured_cron_hook' );
}


add_action('admin_menu', 'create_plugin_settings_page');
add_action( 'on_woo_featured_cron_hook', 'RunFeatured' );
add_filter( 'cron_schedules', 'on_add_cron_interval' );
add_action( 'admin_init', 'jt_wrf_display_options' );
//add_action('added_option', 'reconfigure_options');
add_action('updated_option', 'reconfigure_options');

?>