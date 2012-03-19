<?php

defined( '_JEXEC' ) or die( 'Restricted access' );
define('CLOUDFLARE_VERSION', '0.1.4');
require_once("ip_in_range.php");
 
jimport( 'joomla.plugin.plugin' );
 
class PlgSystemCloudFlare extends JPlugin
{
	public function __construct( &$subject, $config )
	{
		parent::__construct( $subject, $config );
	}
 
	function onAfterInitialise()
	{
		global $is_cf;

		$is_cf = FALSE;
		$cf_ip_ranges = array("204.93.240.0/24", "204.93.177.0/24", "199.27.128.0/21", "173.245.48.0/20", "103.22.200.0/22", "141.101.64.0/18", "108.162.192.0/18");
		foreach ($cf_ip_ranges as $range) {
		    if (ip_in_range($_SERVER["REMOTE_ADDR"], $range)) {
			if ($_SERVER["HTTP_CF_CONNECTING_IP"]) {
			    $_SERVER["REMOTE_ADDR"] = $_SERVER["HTTP_CF_CONNECTING_IP"];
			    $is_cf = TRUE;    
			}
			break;
		    }
		}

		// Let people know that the CF plugin is turned on.
		if (!headers_sent()) 
		{
		    header("X-CF-Powered-By: CF-Joomla " . CLOUDFLARE_VERSION);
		}

	}

}
