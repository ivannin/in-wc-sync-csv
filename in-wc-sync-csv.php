<?php 
/**
 * Plugin Name: IN Woocommerce CSV Sync
 * Plugin URI: http://in-soft.pro/plugins/in-wc-sync-csv/
 * Description: This plugin synchronizes products data from CSV file to WooCommerce
 * Version: 0.1
 * Author: Ivan Nikitin and partners
 * Author URI: http://ivannikitin.com
 * Text domain: in-wc-sync-csv
 *
 * Copyright 2016 Ivan Nikitin  (email: info@ivannikitin.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

// Напрямую не вызываем!
if ( ! defined( 'ABSPATH' ) ) 
	die( '-1' );


// Определения плагина
define( 'INWCSYNC_TEXT_DOMAIN', 'in-wc-sync-csv' );			// Текстовый домен
define( 'INWCSYNC_PATH', plugin_dir_path( __FILE__ ) );		// Путь к папке плагина
define( 'INWCSYNC_URL', plugin_dir_url( __FILE__ ) );		// URL к папке плагина

// Инициализация плагина
add_action( 'init', 'inwcsync_init' );
function inwcsync_init() 
{
	// Локализация плагина
	load_plugin_textdomain( INWCSYNC_TEXT_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );		

	// Классы плагина
	require( INWCSYNC_PATH . 'classes/inwcsync_log.php' );
	require( INWCSYNC_PATH . 'classes/inwcsync_settings.php' );
	require( INWCSYNC_PATH . 'classes/inwcsync_plugin_settings.php' );
	require( INWCSYNC_PATH . 'classes/inwcsync_csv.php' );
	require( INWCSYNC_PATH . 'classes/inwcsync_plugin.php' );
	
	// Инициализация плагина
	new INWCSYNC_Plugin();
}
