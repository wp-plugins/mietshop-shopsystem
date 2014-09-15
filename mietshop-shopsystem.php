<?php
/*
Plugin Name: Mietshop Shopsystem
Plugin URI: http://wordpress.org/extend/plugins/mietshop-shopsystem/
Description: Shopsoftware Modul für WordPress. Artikel des Onlineshops per Widget in die Seite integrieren. Einfache Bedienung & Integration via Wysiwyg Editor.
Version: 0.1
Author: Hagen Drees, Zaunz
Author URI: www.mietshop.de?utm_source=wordpress&utm_medium=plugin&utm_campaign=mietshop-wordpress
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if (!defined('ABSPATH')) {
   die('Please do not load this file directly.');
} // if

// Klasse mit Funktionen laden
require_once("cosmoshop-widget-functions.php");

if (class_exists('CosmoshopWidget')) { 
   define('CosmoshopWidget_FILE', __FILE__);
   $CosmoshopWidget = new CosmoshopWidget();
} // if

?>
