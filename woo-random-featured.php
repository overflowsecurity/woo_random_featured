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

    RunFeatured();

}

function CleanupFeatured(){

global $wpdb;
$table = "wp_term_relationships";
$term = term_exists('featured');

$ids = $wpdb->get_col( "SELECT object_id FROM $wpdb->term_relationships WHERE term_taxonomy_id = %d", $term );

if ( count( $ids ) > 0 ) 
    foreach($ids as $id){
        echo $id;
        $wpdb->delete( $table, array( 'object_id' => $id ) );
        
}

else
    echo "No Featured Products :(";
}

function GetRecentPosts(){

    global $wpdb;

    $query = "SELECT ID FROM $wpdb->posts ORDER BY ID DESC LIMIT 0,20";

    $result = $wpdb->get_results($query);
    $rand_keys = array_rand($result, 10);

     foreach ($rand_keys as $rand_id){
        $randarray[] = $result[$rand_id]->ID;
    } 
    return $randarray;
}

function SetFeaturedProducts($rand_ids){

    global $wpdb;
    $term = term_exsists('featured');
    $tag = array( $term );
    $taxonomy = 'product_visibility';
    $append = True;
    foreach ($rand_ids as $id){

        wp_set_object_terms( $id, $tag, $taxonomy, $append );
    }
}

function RunFeatured(){
    CleanupFeatured();
    $rand_ids = GetRecentPosts();
     SetFeaturedProducts($rand_ids);
}


add_action('admin_menu', 'create_plugin_settings_page');
add_action( 'on_woo_featured_cron_hook2', 'RunFeatured' );
add_filter( 'cron_schedules', 'on_add_cron_interval' );
function on_add_cron_interval( $schedules ) { 
    $schedules['one_week'] = array(
        'interval' => 604800,
        'display'  => esc_html__( 'Every Week' ), );
    return $schedules;
}

if ( ! wp_next_scheduled( 'on_woo_featured_cron_hook2' ) ) {
    wp_schedule_event( time(), 'one_week', 'on_woo_featured_cron_hook2' );
}

?>