<?php
/**
 * Helper class for CloudFlare module
 * 
 * @package    CloudFlare
 * @link https://www.cloudflare.com
 * @license        GNU/GPL, see LICENSE.php
 * mod_helloworld is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

define('CLOUDFLARE_VERSION', '0.1.0');
require_once("ip_in_range.php");

class modCloudFlare {

    /**
     * Updates the IP which PHP sees, if necessary.
     *
     * @param array $params An object containing the module parameters
     * @access public
     * @side-effect -- sets the global var is_cf
     */    
    function updateIP( $params ) {
        global $is_cf;

        $is_cf = FALSE;
        $cf_ip_ranges = array("204.93.240.0/24", "204.93.177.0/24", "204.93.173.0/24", "199.27.128.0/21", "173.245.48.0/20");
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
        if (!headers_sent()) {
            header("X-CF-Powered-By: Mod-CF-Joomla " . CLOUDFLARE_VERSION);
        }

        return $_SERVER["REMOTE_ADDR"];
    }
}
?>