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

   GetRecentPosts();


}

function CleanupFeatured(){

global $wpdb;
$table = "wp_term_relationships";

$ids = $wpdb->get_col( "SELECT object_id FROM $wpdb->term_relationships WHERE term_taxonomy_id = 8" );

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
    foreach($result as $id){
        echo $id->ID;
    }

    foreach($rand_keys as $rand_id){
        echo "The chosen ones";
        echo $rand_id;
    }
}

add_action('admin_menu', 'create_plugin_settings_page');

?>