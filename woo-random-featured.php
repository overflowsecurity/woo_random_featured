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

    submit_button();
    ?><center><h1>WooCommerce Random Featured Products</h1></center><?php

    ?>
    <form method='post' action=''> <?php
    settings_fields( "header_section" );
    do_settings_sections( "woo_random_featured" );
    ?></form><?php
    //RunFeatured();
    //echo "<h2>Done!</h2>";

}

function jt_wrf_display_options(){

    add_settings_section( 'header_section', 'Pluggin Settings', 'test_header_func', 'woo_random_featured' );
    add_settings_field( 'jt-num-to-keep', 'How Many Prodcuts to Keep', 'jt_num_to_keep', 'woo_random_featured', 'header_section' );
    add_settings_field( 'jt-when-to-change', 'How Often Should Featured Products Change>', 'jt_when_to_change', 'woo_random_featured', 'header_section' );
    register_setting( 'header_section', 'jt-num-to-keep' );
    register_setting( 'header_section', 'jt-when-to-change' );
}

function test_header_func(){echo "This is a test";}

function jt_num_to_keep(){

    ?>
    <input type="text" name="jt_num_to_keep" id="jt_num_to_keep" value="" />
    <?php
}

function jt_when_to_change(){

    ?>
    <input type="text" name="jt_when_to_change" id="jt_when_to_change" value="" />
    <?php
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


    $result = $wpdb->get_results($query);
    $rand_keys = array_rand($result, 10);

     foreach ($rand_keys as $rand_id){
        $randarray[] = $result[$rand_id]->ID;
    } 
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
    $schedules['one_week'] = array(
        'interval' => 600000,
        'display'  => esc_html__( 'Every Week' ), );
    return $schedules;
} 

if ( ! wp_next_scheduled( 'on_woo_featured_cron_hook' ) ) {
    wp_schedule_event( time(), 'one_week', 'on_woo_featured_cron_hook' );
}


add_action('admin_menu', 'create_plugin_settings_page');
add_action( 'on_woo_featured_cron_hook', 'RunFeatured' );
add_filter( 'cron_schedules', 'on_add_cron_interval' );
add_action( 'admin_init', 'jt_wrf_display_options' );
?>