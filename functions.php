<?php
/**
 * appMaster — functions.
 *
 * @package appmaster
 */

defined( 'ABSPATH' ) || exit;

define( 'APPMASTER_VERSION', '1.0.0' );
define( 'APPMASTER_DIR', get_template_directory() );
define( 'APPMASTER_URI', get_template_directory_uri() );

foreach ( array( 'helpers', 'icons', 'setup', 'assets', 'cpt', 'acf', 'seo', 'antispam', 'litespeed', 'login', 'content-sync' ) as $appmaster_file ) {
	require_once APPMASTER_DIR . '/inc/' . $appmaster_file . '.php';
}
