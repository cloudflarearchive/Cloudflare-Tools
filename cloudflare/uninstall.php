<?php
if(!defined('ABSPATH') && !DEFINED('WP_UNINSTALL_PLUGIN'))
	exit();

	global $wpdb;

	// Delete CloudFlare Options
	delete_option( 'cloudflare_protocol_rewrite' );
	delete_option( 'cloudflare_zone_name' );
	delete_option( 'cloudflare_zone_name_set_once' );
	delete_option( 'cloudflare_api_key' );
	delete_option( 'cloudflare_api_key_set_once' );
	delete_option( 'cloudflare_api_email' );
	delete_option( 'cloudflare_api_email_set_once' );
