<?php
/*
Plugin Name: CloudFlare
Plugin URI: http://www.cloudflare.com/wiki/CloudFlareWordPressPlugin
Description: CloudFlare integrates your blog with the CloudFlare platform.
Version: 1.1.3
Author: Ian Pye (CloudFlare Team)
License: GPLv2
*/

/*
This program is free software; you can redistribute it and/or modify 
it under the terms of the GNU General Public License as published by 
the Free Software Foundation; version 2 of the License.

This program is distributed in the hope that it will be useful, 
but WITHOUT ANY WARRANTY; without even the implied warranty of 
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
GNU General Public License for more details. 

You should have received a copy of the GNU General Public License 
along with this program; if not, write to the Free Software 
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA 

Plugin adapted from the Akismet WP plugin.

*/	

define('CLOUDFLARE_VERSION', '1.1.3');
require_once("ip_in_range.php");

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
	exit;
}

function cloudflare_init() {
	global $cf_api_host, $cf_api_port, $is_cf;

    $cf_api_host = "ssl://www.cloudflare.com";
    $cf_api_port = 443;
    $cf_ip_ranges = array("204.93.240.0/24", "204.93.177.0/24", "204.93.173.0/24", "199.27.128.0/21", "173.245.48.0/20");
    $is_cf = ($_SERVER["HTTP_CF_CONNECTING_IP"])? TRUE: FALSE;    

    // Update the REMOTE_ADDR value if the current REMOTE_ADDR value is in the specified range.
    foreach ($cf_ip_ranges as $range) {
        if (ip_in_range($_SERVER["REMOTE_ADDR"], $range)) {
            if ($_SERVER["HTTP_CF_CONNECTING_IP"]) {
                $_SERVER["REMOTE_ADDR"] = $_SERVER["HTTP_CF_CONNECTING_IP"];
            }
            break;
        }
    }

    // Let people know that the CF WP plugin is turned on.
    if (!headers_sent()) {
        header("X-CF-Powered-By: WP " . CLOUDFLARE_VERSION);
    }
	add_action('admin_menu', 'cloudflare_config_page');
	cloudflare_admin_warnings();
}
add_action('init', 'cloudflare_init');

function cloudflare_admin_init() {
    
}

add_action('admin_init', 'cloudflare_admin_init');

function cloudflare_config_page() {
	if ( function_exists('add_submenu_page') ) {
		add_submenu_page('plugins.php', __('CloudFlare Configuration'), __('CloudFlare'), 'manage_options', 'cloudflare', 'cloudflare_conf');
    }
}

function load_cloudflare_keys () {
    global $cloudflare_api_key, $cloudflare_api_email;
    if (!$cloudflare_api_key) {
        $cloudflare_api_key = get_option('cloudflare_api_key');
    }
    if (!$cloudflare_api_email) {
        $cloudflare_api_email = get_option('cloudflare_api_email');
    }
}

function cloudflare_conf() {
    if ( function_exists('current_user_can') && !current_user_can('manage_options') )
        die(__('Cheatin&#8217; uh?'));
    global $cloudflare_api_key, $cloudflare_api_email, $is_cf;
    global $wpdb;

    $db_results = array();
               
	if ( isset($_POST['submit']) && !($_POST['optimize']) ) {
		if ( function_exists('current_user_can') && !current_user_can('manage_options') ) {
			die(__('Cheatin&#8217; uh?'));
        }

		$key = $_POST['key'];
		$email = $_POST['email'];
		if ( empty($key) ) {
			$key_status = 'empty';
			$ms[] = 'new_key_empty';
			delete_option('cloudflare_api_key');
		} else {
            $ms[] = 'new_key_valid';
			update_option('cloudflare_api_key', $key);
            update_option('cloudflare_api_key_set_once', "TRUE");
        }

		if ( empty($email) ) {
			$email_status = 'empty';
			$ms[] = 'new_email_empty';
			delete_option('cloudflare_api_email');
		} else {
			$ms[] = 'new_email_valid';
			update_option('cloudflare_api_email', $email);
            update_option('cloudflare_api_email_set_once', "TRUE");
        }

        $messages = array(
                          'new_key_empty' => array('color' => 'aa0', 'text' => __('Your key has been cleared.')),
                          'new_key_valid' => array('color' => '2d2', 'text' => __('Your key has been verified. Happy blogging!')),
                          'new_email_empty' => array('color' => 'aa0', 'text' => __('Your email has been cleared.')),
                          'new_email_valid' => array('color' => '2d2', 'text' => __('Your email has been verified. Happy blogging!')),
                          );
    } else if ( isset($_POST['submit']) && isset($_POST['optimize']) ) {
        update_option('cloudflare_api_db_last_run', time());
        if(current_user_can('edit_files')) {
            remove_action('admin_notices', 'cloudflare_warning');
            $tables = $wpdb->get_col("SHOW TABLES");
            foreach($tables as $table_name) {
                $optimize = $wpdb->query("OPTIMIZE TABLE `$table_name`");
                $analyze = $wpdb->query("ANALYZE TABLE `$table_name`");
                if (!$optimize || !$analyze) {
                    $db_results[] = "Error optimizing $table_name";
                }
            }
            if (count($db_results) == 0) {
                $db_results[] = "All tables optimized without error.";
            }
        } else {
            $db_results[] = "The current user does not have the permission \"manage_database\". Please run the command again with an appropriate user.";
        }
    }

    ?>
    <?php if ( !empty($_POST['submit'] ) && !($_POST['optimize']) ) { ?>
    <div id="message" class="updated fade"><p><strong><?php _e('Options saved.') ?></strong></p></div>
    <?php } else if ( isset($_POST['submit']) && isset($_POST['optimize']) ) {
    foreach ($db_results as $res) {
        ?><div id="message" class="updated fade"><p><strong><?php _e($res) ?></strong></p></div><?php
    }
} 
    ?>
    <div class="wrap">

    <?php if ($is_cf) { ?>
        <h3>You are currently using CloudFlare!</h3>
    <?php } ?>

    <h4><?php _e('CLOUDFLARE WORDPRESS PLUGIN:'); ?></h4>
        <?php //    <div class="narrow"> ?>

CloudFlare has developed a plugin for WordPress. By using the CloudFlare WordPress Plugin, you receive: 
<ol>
<li>Correct IP Address information for comments posted to your site</li>
<li>Optimization of your server database (optional)</li>
<li>Better protection as spammers from your WordPress blog get reported to CloudFlare (coming soon)</li>
</ol>

<h4>VERSION COMPATIBILITY:</h4>

The plugin is compatible with WordPress version 2.8.6 and later. The plugin will not install unless you have a compatible platform.

<h4>THINGS YOU NEED TO KNOW:</h4>

<ol>
<li>The main purpose of this plugin is to ensure you have no change to your originating IPs when using CloudFlare. Since CloudFlare acts a reverse proxy, connecting IPs now come from CloudFlare's range. This plugin will ensure you can continue to see the originating IP. Once you install the plugin, the IP benefit will be activated.</li>
 
<li>This plugin can also help to ensure your server database is running optimally. If you are going to run the Database Optimizer associated with this plugin, then run it at a low traffic time. While the Database Optimizer is running, your site will go into Read Only mode, which means that you or your visitors will not be allowed to post. The optimizer should run quickly. Once the optimizer is done running, you will be able to post to your site again. To run the Database Optimizer, click the icon below.</li>

<li>Coming soon: Every time you click the 'spam' button on your blog, this threat information will get sent to CloudFlare to ensure you are constantly getting the best site protection.</li>

<li>We recommend that any user on CloudFlare with WordPress use this plugin. </li>

<li>NOTE: This plugin is complimentary to Akismet and W3 Total Cache. We recommend that you continue to use those services.</li> 

</ol>

<h4>MORE INFORMATION ON CLOUDFLARE:</h4>

CloudFlare is a service that makes websites load faster and protects sites from online spammers and hackers. Any website with a root domain (ie www.mydomain.com) can use CloudFlare. On average, it takes less than 5 minutes to sign up. You can learn more here: <a href="http://www.cloudflare.com/">CloudFlare.com</a>.

    <form action="" method="post" id="cloudflare-db">
    <input type="hidden" name="optimize" value="1" />

    <h4><label for="optimize_db"><?php _e('DATABASE OPTIMIZER (optional): Make your site run even faster.'); ?></label>
    <input type="submit" name="submit" value="<?php _e('Run the optimizer'); ?>" /> (<?php _e('<a href="http://www.cloudflare.com/wiki/WordPressDBOptimizer">What is this?</a>'); ?>)</h4>

    </form>

    <?php if ($is_cf) { ?>

    <hr />

    <form action="" method="post" id="cloudflare-conf">
    <?php if (get_option('cloudflare_api_key') && get_option('cloudflare_api_email')) { ?>
    <?php } else { ?> 
        <p><?php printf(__('Input your API key from your CloudFlare Accounts Settings page here. To find your API key, log in to <a href="%1$s">CloudFlare</a> and go to \'Account\'.'), 'https://www.cloudflare.com/my-account.html'); ?></p>
    <?php } ?>
    <?php if ($ms) { foreach ( $ms as $m ) { ?>
    <p style="padding: .5em; color: #<?php echo $messages[$m]['color']; ?>; font-weight: bold;"><?php echo $messages[$m]['text']; ?></p>
    <?php } } ?>
    <h3><label for="key"><?php _e('CloudFlare API Key'); ?></label></h3>
    <p><input id="key" name="key" type="text" size="50" maxlength="48" value="<?php echo get_option('cloudflare_api_key'); ?>" style="font-family: 'Courier New', Courier, mono; font-size: 1.5em;" /> (<?php _e('<a href="https://www.cloudflare.com/my-account.html">Get this?</a>'); ?>)</p>
    <h3><label for="email"><?php _e('CloudFlare API Email'); ?></label></h3>
    <p><input id="email" name="email" type="text" size="50" maxlength="48" value="<?php echo get_option('cloudflare_api_email'); ?>" style="font-family: 'Courier New', Courier, mono; font-size: 1.5em;" /> (<?php _e('<a href="https://www.cloudflare.com/my-account.html">Get this?</a>'); ?>)</p>

    <p class="submit"><input type="submit" name="submit" value="<?php _e('Update options &raquo;'); ?>" /></p>
    </form>
    
    <?php } ?>

        <?php //    </div> ?>
    </div>
    <?php
}

function cloudflare_admin_warnings() {
    
    global $cloudflare_api_key, $cloudflare_api_email; 
    load_cloudflare_keys();

    /**
	if ( !get_option('cloudflare_api_key_set_once') && !$cloudflare_api_key && !isset($_POST['submit']) ) {
		function cloudflare_warning() {
			echo "
			<div id='cloudflare-warning' class='updated fade'><p><strong>".__('CloudFlare is almost ready.')."</strong> ".sprintf(__('You must <a href="%1$s">enter your CloudFlare API key</a> for it to work.'), "plugins.php?page=cloudflare")."</p></div>
			";
		}
		add_action('admin_notices', 'cloudflare_warning');
		return;
	} else if ( !get_option('cloudflare_api_key_set_once') && !$cloudflare_api_email && !isset($_POST['submit']) ) {
		function cloudflare_warning() {
			echo "
			<div id='cloudflare-warning' class='updated fade'><p><strong>".__('CloudFlare is almost ready.')."</strong> ".sprintf(__('You must <a href="%1$s">enter your CloudFlare API email</a> for it to work.'), "plugins.php?page=cloudflare")."</p></div>
			";
		}
		add_action('admin_notices', 'cloudflare_warning');
		return;
	} 
    */
    
    // Check to see if they should optimized their DB
    $last_run_time = (int)get_option('cloudflare_api_db_last_run');
    if (!$last_run_time) {
        $last_run_time = time();
    }
    if (time() - $last_run_time > 5259487) { // 2 Months (avg)
        function cloudflare_warning() {
			echo "
			<div id='cloudflare-warning' class='updated fade'><p><strong>".__('Your Database is due to be optimized again.')."</strong> ".sprintf(__('We recommend that you <a href="%1$s">run the CloudFlare optimizer</a> every two months to keep your blog running quickly. It\'s time to run it again.'), "plugins.php?page=cloudflare")."</p></div>
			";
		}
		add_action('admin_notices', 'cloudflare_warning');
		return;
    }
}

// Now actually allow CF to see when a comment is approved/not-approved.
function cloudflare_set_comment_status($id, $status) {
    global $cf_api_host, $cf_api_port, $cloudflare_api_key, $cloudflare_api_email; 
    if (!$cf_api_host || !$cf_api_port) {
        return;
    }
    load_cloudflare_keys();
    if (!$cloudflare_api_key || !$cloudflare_api_email) {
        return;
    }

    // ajax/external-event.html?email=ian@cloudflare.com&t=94606855d7e42adf3b9e2fd004c7660b941b8e55aa42d&evnt_v={%22dd%22:%22d%22}&evnt_t=WP_SPAM
    $comment = get_comment($id);
    $value = array("a" => $comment->comment_author, 
                   "am" => $comment->comment_author_email,
                   "ip" => $comment->comment_author_IP,
                   "con" => substr($comment->comment_content, 0, 100));
    $url = "/ajax/external-event.html?evnt_v=" . urlencode(json_encode($value)) . "&u=$cloudflare_api_email&tkn=$cloudflare_api_key&evnt_t=";
     
    // If spam, send this info over to CloudFlare.
    if ($status == "spam") {
        $url .= "WP_SPAM";
        $fp = @fsockopen($cf_api_host, $cf_api_port, $errno, $errstr, 30);
        if ($fp) {
            $out = "GET $url HTTP/1.1\r\n";
            $out .= "Host: www.cloudflare.com\r\n";
            $out .= "Connection: Close\r\n\r\n";
            fwrite($fp, $out);
            $res = "";
            while (!feof($fp)) {
                $res .= fgets($fp, 128);
            }
            fclose($fp);
        }
    }
}

add_action('wp_set_comment_status', 'cloudflare_set_comment_status', 1, 2);

?>