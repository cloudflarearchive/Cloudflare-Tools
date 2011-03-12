<?php
/**
 * CloudFlare Module Entry Point
 * 
 * @package    CloudFlare
 * @link https://www.cloudflare.com
 * @license        GNU/GPL, see LICENSE.php
 * mod_cloudflare is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
 
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Include the syndicate functions only once
require_once( dirname(__FILE__).DS.'helper.php' );

$hello = modCloudFlare::updateIP( $params );
require( JModuleHelper::getLayoutPath( 'mod_cloudflare' ) );

?>