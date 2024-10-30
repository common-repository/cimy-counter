<?php
/*
Plugin Name: Cimy Counter
Plugin URI: http://www.marcocimmino.net/cimy-wordpress-plugins/cimy-counter/
Description: Add a counter to your downloads or page hits
Version: 1.1.1
Author: Marco Cimmino
Author URI: mailto:cimmino.marco@gmail.com
*/

/*

Cimy Counter - Allows to count downloads and views
Copyright (c) 2007-2012 Marco Cimmino

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.


The full copy of the GNU General Public License is available here: http://www.gnu.org/licenses/gpl.txt

*/

// prevent direct access to the plugin
if (!function_exists("get_option"))
	return;

// added for WordPress >=2.5 compatibility
global $wpdb, $cc_table, $cc_domain;

$cc_name = "Cimy Counter";
$cc_url = "http://www.marcocimmino.net/cimy-wordpress-plugins/cimy-counter/";
$cc_version = "1.1.1";

$cc_options = "cimy_counter_options";
$cc_options_descr = "Cimy Counter options are stored here and modified only by admin";

$cc_max_length_names = 250;
$cc_table = $wpdb->prefix."cimy_counter";
$cc_siteurl = get_option("siteurl");

// pre 2.6 compatibility or if not defined
if (!defined("WP_CONTENT_DIR"))
	define("WP_CONTENT_DIR", ABSPATH."/wp_content");

$cc_plugin_name = basename(__FILE__);
$cc_plugin_path = plugin_basename(dirname(__FILE__))."/";
$cc_js_webpath = plugins_url($cc_plugin_path."js/");
$cc_webpath = plugins_url($cc_plugin_path);

// add admin menu
add_action('admin_menu', 'cc_admin_menu_permissions');

// function that is executed during activation of the plug-in
add_action('activate_'.$cc_plugin_path.$cc_plugin_name,'cc_plugin_install');

// function that is executed during de-activation of the plug-in
//add_action('deactivate_'.$cc_plugin_path.$cc_plugin_name,'cc_plugin_uninstall');

// add filtering engine for posts/pages
add_filter("the_content", "cc_filter_post");


$cc_domain = 'cimy_counter';
$cc_i18n_is_setup = 0;
cc_i18n_setup();

function cc_i18n_setup() {
	global $cc_domain, $cc_i18n_is_setup, $cc_plugin_path;

	if ($cc_i18n_is_setup)
		return;

	load_plugin_textdomain($cc_domain, false, $cc_plugin_path.'langs/');
}

function cc_add_one_to_the_counter($name) {
	global $wpdb, $cc_table;

	$name = $wpdb->escape($name);
	
	if (isset($name)) {
		$sql = "UPDATE $cc_table SET COUNTER=COUNTER+1 WHERE NAME='".$name."'";
		$wpdb->query($sql);
	}
}

function cc_get_counter($name) {
	global $wpdb, $cc_table;

	$name = $wpdb->escape($name);
	
	if (isset($name)) {
		$sql = "SELECT COUNTER FROM $cc_table WHERE NAME='".$name."'";
		$counter = $wpdb->get_var($sql);
	
		if (isset($counter))
			return $counter;
		else
			return -1;
	}
	else
		return -1;
}

function cc_get_display_str($name, $date_format="") {
	global $wpdb, $cc_table, $cc_domain;

	if ($date_format == "")
		$date_format = get_option("date_format");

	$name = $wpdb->escape($name);
	
	if (isset($name)) {
		$sql = "SELECT COUNTER, SINCE, DISPLAY_STR FROM $cc_table WHERE NAME='".$name."'";
		$result = $wpdb->get_results($sql);

		if (isset($result[0]))
			return sprintf(__($result[0]->DISPLAY_STR, $cc_domain), date($date_format, $result[0]->SINCE), intval($result[0]->COUNTER));
		else
			return false;
	}
	else
		return false;
}

function cc_get_since($name, $date_format="") {
	global $wpdb, $cc_table;

	if ($date_format == "")
		$date_format = get_option("date_format");

	$name = $wpdb->escape($name);
	
	if (isset($name)) {
		$sql = "SELECT SINCE FROM $cc_table WHERE NAME='".$name."'";
		$since = $wpdb->get_var($sql);

		if (isset($since))
			return date($date_format, $since);
		else
			return false;
	}
	else
		return false;
}

function cc_plugin_uninstall() {
	global $wpdb, $cc_table;

	if (!current_user_can('activate_plugins'))
		return;

	$sql = "DROP TABLE ".$cc_table;
	$wpdb->query($sql);
}

function cc_plugin_install() {
	global $wpdb, $cc_table, $cc_domain, $cc_options, $cc_version, $cc_options_descr;

	if (!current_user_can('activate_plugins'))
		return;

	$options = get_option($cc_options);

	// no options, might be upgrading from <=0.9.3 need to check all links!
	if (!$options) {
		$options = array(
			'version' => $cc_version,
			'whitelisted_urls' => array()
		);

		update_option($cc_options, $options, $cc_options_descr, "no");

		$sql = "SELECT ID,post_content FROM ".$wpdb->posts." WHERE post_type!='attachment'";
		$all_posts = $wpdb->get_results($sql);

		foreach ($all_posts as $post) {
			$new_post = str_replace("/Cimy_Counter/", "/cimy-counter/", $post->post_content);

			if ($new_post != $post->post_content) {
				$new_post = $wpdb->escape($new_post);

				$sql = "UPDATE ".$wpdb->posts." SET post_content='".$new_post."' WHERE ID=".$post->ID;
				$wpdb->query($sql);
			}
		}
	}
	else {
		if ($options['version'] <= "1.0.0")
			$options['whitelisted_urls'] = array();

		$options['version'] = $cc_version;
		update_option($cc_options, $options, $cc_options_descr, "no");
	}

	$charset_collate = "";
	
	// try to get proper charset and collate
	if ( $wpdb->supports_collation() ) {
		if ( ! empty($wpdb->charset) )
			$charset_collate = " DEFAULT CHARACTER SET $wpdb->charset";
		if ( ! empty($wpdb->collate) )
			$charset_collate .= " COLLATE $wpdb->collate";
	}

	if ($wpdb->get_var("SHOW TABLES LIKE '$cc_table'") != $cc_table) {

		$sql = "CREATE TABLE ".$cc_table." (ID bigint(20) NOT NULL AUTO_INCREMENT, NAME VARCHAR(255) NOT NULL, COUNTER bigint(20), DISPLAY_STR TEXT, SINCE bigint(20), PRIMARY KEY (ID), UNIQUE KEY NAME (NAME))".$charset_collate.";";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
	return $options;
}

function cc_admin_menu_permissions() {
	global $cc_domain, $cimy_top_menu;

	if (isset($cimy_top_menu)) {
		$cc_page = add_submenu_page('cimy_series.php', 'Counter', 'Counter', 'manage_options', 'cimy_counter', 'cc_admin');
	}
	else {
		$cc_page = add_options_page('Cimy Counter', 'Cimy Counter', 'manage_options', 'cimy_counter', 'cc_admin');
	}
	if (!empty($cc_page))
		add_action('admin_print_scripts-'.$cc_page, 'cimy_cc_admin_scripts');
}

function cimy_cc_admin_scripts() {
	global $cc_js_webpath;

	wp_register_script("cc_invert_sel", $cc_js_webpath."invert_sel.js", false, false);
	wp_register_script("cc_date_manager", $cc_js_webpath."js_calendar/dhtmlgoodies_calendar/dhtmlgoodies_calendar.js?random=20060118", false, false);
	wp_enqueue_script("cc_invert_sel");
	wp_enqueue_script("cc_date_manager");

	wp_register_style("cc_date_mng", $cc_js_webpath."js_calendar/dhtmlgoodies_calendar/dhtmlgoodies_calendar.css?random=20051112", false, false);
	wp_enqueue_style("cc_date_mng");
	wp_print_styles();
}

function cc_filter_counter($matches) {
	$type = explode("#", $matches[0]);
	$return_value = "";

	switch ($type[0]) {
		case "[cc_string":
			$return_value = cc_get_display_str($matches[1]);
			break;

		case "[cc_since":
			$since = cc_get_since($matches[1]);

			if ($since != false)
				$return_value = $since;
			break;

		case "[cc_counter":
			$counter = cc_get_counter($matches[1]);

			if ($counter >= 0)
				$return_value = $counter;
			break;
	}

	return $return_value;
}

// This filter parses post content and replaces markup with the correct counter
// types: [cc_since#name] | [cc_counter#name] | [cc_string#name]
function cc_filter_post($content) {
	$content = preg_replace_callback(array("/\[cc_since#(.*?)\]/", "/\[cc_counter#(.*?)\]/", "/\[cc_string#(.*?)\]/"), "cc_filter_counter", $content);

	return $content;
}

function cc_save_options() {
	global $cc_options;

	$results = array();
	$options = get_option($cc_options);

	if (isset($_POST["force_activation"])) {
		cc_plugin_install();
		return $results;
	}

	if ($options) {
		if (!empty($_GET["order"])) {
			$order = strtoupper($_GET["order"]);
	
			if (!empty($_GET["o_type"]) && strtoupper($_GET["o_type"]) == "DESC")
				$options["counter_order_type"] = "desc";
			else
				$options["counter_order_type"] = "";
	
			$options["counter_order"] = $order;
		}

		if (isset($_POST["submit_add_whitelisted_url"]) && !empty($_POST["cc_add_url_to_whitelist"]))
			$options["whitelisted_urls"] = array_unique(array_merge($options["whitelisted_urls"], array($_POST["cc_add_url_to_whitelist"])));
		else if (isset($_POST["submit_delete_whitelisted_urls"]) && !empty($_POST["cc_whitelist_urls"]))
			$options["whitelisted_urls"] = array_diff($options["whitelisted_urls"], $_POST["cc_whitelist_urls"]);

		update_option($cc_options, $options);
	}

	return $results;
}

function cc_show_options($options, $results) {
	global $cc_name, $cc_url, $cc_version, $cc_domain;

	?>
	<div class="wrap" id="options">
	<?php
		if (function_exists("screen_icon"))
			screen_icon("options-general");
	?>
	<h2>Cimy Counter</h2><?php

	// print successes if there are some
	if (count($results) > 0) {
	?>
		<div class="updated">
		<h3><?php _e("SUCCESSFUL", $cc_domain); ?></h3>
		<ul>
			<?php 
			foreach ($results as $result)
				echo "<li>".$result."</li>";
			?>
		</ul>
		<br />
		</div>
	<?php
	}

	?><form method="post" action="#options">
	<?php wp_nonce_field('cimy_cc_admin', 'cimy_cc_admin_nonce', false); ?>
	<h3><?php _e("General"); ?></h3>
	<table class="form-table">
		<tr>
			<th scope="row" width="40%">
				<strong><a href="<?php echo $cc_url; ?>"><?php echo $cc_name; ?></a></strong>
			</th>
			<td width="60%">v<?php echo $options['version'];
				if ($cc_version != $options['version']) {
					?> (<?php _e("installed is", $cc_domain); ?> v<?php echo $cc_version; ?>)<?php
				}
				
				if (!$options) {
					?><br /><h4><?php _e("OPTIONS DELETED!", $cc_domain); ?></h4>
					<input type="hidden" name="do_not_save_options" value="1" />

					<p class="submit" style="border-width: 0px;"><input class="button-primary" type="submit" name="force_activation" value="<?php _e("Fix the problem", $cc_domain); ?>" onclick="return confirm('<?php _e("This operation will create/update all missing tables/options, do you want to proceed?", $cc_domain); ?>');" /></p><?php
				}
				else if ($cc_version != $options['version']) {
					?><br /><h4><?php _e("VERSIONS MISMATCH! This because you haven't de-activated and re-activated the plug-in after the update! This could give problems...", $cc_domain); ?></h4>

					<p class="submit" style="border-width: 0px;"><input class="button-primary" type="submit" name="force_activation" value="<?php _e("Fix the problem", $cc_domain); ?>" onclick="return confirm('<?php _e("This operation will create/update all missing tables/options, do you want to proceed?", $cc_domain); ?>');" /></p><?php
				}
				?>
			</td>
		</tr>
	</table>
	<input type="hidden" name="cimy_options" value="1" />
	</form>
	</div>
	<br />
	<?php
}


function cc_admin() {
	global $wpdb, $cc_table, $cc_max_length_names, $cc_domain, $cc_plugin_path, $cc_siteurl, $cc_js_webpath, $cc_webpath, $cc_options, $cc_version;
	
	if (!current_user_can('manage_options'))
		return;

	if (!empty($_POST)) {
		if (!empty($_POST["submit_add_new_counter"])) {
			if (!check_admin_referer('cimy_cc_admin_add_counter', 'cimy_cc_admin_add_counter_nonce'))
				return;
		}
		else if ((!empty($_POST["submit_add_whitelisted_url"])) || (!empty($_POST["submit_delete_whitelisted_urls"]))) {
			if (!check_admin_referer('cimy_cc_admin_add_whitelist_url', 'cimy_cc_admin_add_whitelist_url_nonce'))
				return;
		}
		else if (!check_admin_referer('cimy_cc_admin', 'cimy_cc_admin_nonce'))
			return;
	}

	$results = cc_save_options();
	$options = get_option($cc_options);
	if (isset($options['version']) && $cc_version != $options['version'])
		$options = cc_plugin_install();

	cc_show_options($options, $results);

	$delSel_caption = __("Delete selected counters", $cc_domain);
	$display_str = __("Since %1\$s downloaded %2\$d times", $cc_domain);

	$date_format = "Y/m/d";
	$time_now = time();
	$time_now_formatted = date($date_format, $time_now);

	if (isset($_POST['cc_new_counter'])) {
		$new_counter_name = trim($_POST['cc_new_counter']);

		$sql = "SELECT ID FROM $cc_table WHERE NAME='".$new_counter_name."'";
		$exist = $wpdb->get_results($sql);

		if ($new_counter_name == "")
			$errors['name'] = __("Name empty", $cc_domain);
		else if (!empty($exist))
			$errors['name'] = __("Name already exists", $cc_domain);
		else {
			$sql = "INSERT INTO $cc_table SET NAME='".$new_counter_name."', COUNTER=0, SINCE=".$time_now.", DISPLAY_STR='".$display_str."'";
			$wpdb->query($sql);
		}
	}
	else if (isset($_POST["submit_delete_counters"])) {
		if (count($_POST['cc_items']) <= 0)
			$errors['selected'] = __("Nothing selected", $cc_domain);
		else if ($_POST["submit_delete_counters"] == $delSel_caption) {
			$sql = "DELETE FROM ".$cc_table." WHERE ";
			
			$sql.= "ID=".implode(" OR ID=", $_POST['cc_items']);
			$wpdb->query($sql);
		}
	}
	else if (isset($_POST["submit_set_counters_value"])) {
		if (count($_POST['cc_items']) <= 0)
			$errors['selected'] = __("Nothing selected", $cc_domain);
		else if ($_POST['cc_set_counter_value'] == "")
			$errors['set_counter'] = __("Insert a number to set", $cc_domain);
		else {
			$set_counter = intval($_POST['cc_set_counter_value']);
			$sql = "UPDATE ".$cc_table." SET COUNTER=$set_counter WHERE ";
		
			$sql.= "ID=".implode(" OR ID=", $_POST['cc_items']);
			$wpdb->query($sql);
		}
	}
	else if (isset($_POST["submit_set_counters_date"])) {
		if (count($_POST['cc_items']) <= 0)
			$errors['selected'] = __("Nothing selected", $cc_domain);
		else if ($_POST['cc_set_counter_date'] == "")
			$errors['set_counter'] = __("Insert a date to set", $cc_domain);
		else {
			$set_counter = strtotime($_POST['cc_set_counter_date']);

			if (($set_counter == false) || ($set_counter == -1))
				$set_counter = $time_now;

			$sql = "UPDATE ".$cc_table." SET SINCE=$set_counter WHERE ";
		
			$sql.= "ID=".implode(" OR ID=", $_POST['cc_items']);
			$wpdb->query($sql);
		}
	}
	else if (isset($_POST["submit_set_counters_string"])) {
		if (count($_POST['cc_items']) <= 0)
			$errors['selected'] = __("Nothing selected", $cc_domain);
		else if ($_POST['cc_set_counter_string'] == "")
			$errors['set_counter'] = __("Insert a string to set", $cc_domain);
		else {
			$set_counter = $wpdb->escape($_POST['cc_set_counter_string']);

			$sql = "UPDATE ".$cc_table." SET DISPLAY_STR='$set_counter' WHERE ";
		
			$sql.= "ID=".implode(" OR ID=", $_POST['cc_items']);
			$wpdb->query($sql);
		}
	}

	if (!empty($_GET["order"]))
		$order_by = strtoupper($wpdb->escape($_GET["order"]));
	else if (isset($options["counter_order"]))
		$order_by = $options["counter_order"];
	else
		$order_by = "NAME";

	$order_desc = array();

	$arrow_down = '<img src="'.$cc_siteurl.'/wp-admin/images/screen-options-right.gif" alt="" />';
	$arrow_up = '<img src="'.$cc_siteurl.'/wp-admin/images/screen-options-right-up.gif" alt="" />';
	$close_tag = '">';

	$order_desc["ID"] = $close_tag;
	$order_desc["NAME"] = $close_tag;
	$order_desc["COUNTER"] = $close_tag;
	$order_desc["SINCE"] = $close_tag;
	$order_desc["DISPLAY_STR"] = $close_tag;

	$order_desc[$order_by] = '&amp;o_type=desc'.$close_tag.$arrow_down;

	if ((!empty($_GET["o_type"]) && $_GET["o_type"] == "desc") || ($options["counter_order_type"] == "desc")) {
		$order_desc[$order_by] = $close_tag.$arrow_up;
		$order_by.= " DESC";
	}

	$sql_counters = "SELECT ID, NAME, COUNTER, SINCE, DISPLAY_STR FROM $cc_table";
	$sql_counters.= " ORDER BY ".$order_by;
	
	$items = $wpdb->get_results($sql_counters);

?>
	<div class="wrap">
<?php

	// print errors if there are some
	if (!empty($errors) > 0) {
		?><div class="error">
			<h3><?php _e('ERROR', $cc_domain); ?></h3>
		<ul><?php
		
		foreach ($errors as $error) {
			?><li><?php echo $error; ?></li><?php
		}
			
		?></ul>
		</div><?php
	}
	
	// print successes if there are some
	if (!empty($display_results) > 0) {
		?><div class="updated">
			<h3><?php _e('SUCCESSFUL', $cc_domain); ?></h3>
		<ul><?php
		
		foreach ($display_results as $result) {
			?><li><?php echo $result; ?></li><?php
		}
			
		?></ul>
		</div><?php
	}

	?>

	<br />
	<form id="cc_add_counter" method="post" action="#top">
		<?php wp_nonce_field('cimy_cc_admin_add_counter', 'cimy_cc_admin_add_counter_nonce', false); ?>
		<?php _e("New counter:", $cc_domain); ?> <input class="regular-text" name="cc_new_counter" type="text" maxlength="<?php echo $cc_max_length_names; ?>" value="" />
		<input class="button-primary" type="submit" name="submit_add_new_counter" value="<?php _e("Add", $cc_domain); ?>" />
	</form>
	<br /><br />
	
	<form name="cc_form" id="cc_form" method="post" action="#top">
	<?php wp_nonce_field('cimy_cc_admin', 'cimy_cc_admin_nonce', false); ?>
	<input class="button" type="button" value="<?php _e("Invert selection", $cc_domain); ?>" onclick="this.value=invert_sel('cc_form', 'cc_items', '<?php _e("Invert selection", $cc_domain); ?>')" />
	<input class="button" name="submit_delete_counters" type="submit" value="<?php echo $delSel_caption ?>" onclick="return confirm('<?php _e("Are you sure you want to delete selected counters?", $cc_domain); ?>');" />
	
	<?php
		$thead = '<th width="40px"><a href="options-general.php?page=cimy_counter&amp;order=id'.$order_desc["ID"].'ID</a></th>
		<th width="60px"><a href="options-general.php?page=cimy_counter&amp;order=name'.$order_desc["NAME"].__("Name").'</a></th>
		<th width="90px" align="center"><a href="options-general.php?page=cimy_counter&amp;order=counter'.$order_desc["COUNTER"].__("Counter", $cc_domain).'</a></th>
		<th><a href="options-general.php?page=cimy_counter&amp;order=since'.$order_desc["SINCE"].__("Since", $cc_domain).'</a></th>
		<th><a href="options-general.php?page=cimy_counter&amp;order=display_str'.$order_desc["DISPLAY_STR"].__("Display string", $cc_domain).'</a></th>
		<th>'.__("Downloads Counter link", $cc_domain).'</th>
		<th>'.__("Views Counter link", $cc_domain).'</th>';
	?>

	<table class="widefat">
		<thead>
		<tr class="thead">
			<?php echo $thead; ?>
		</tr></thead>
		<tfoot>
		<tr class="thead">
			<?php echo $thead; ?>
		</tr></tfoot>
		<tbody><?php

	$alternate = true;

	foreach ($items as $item) {
		
		if ($alternate) {
			echo '<tr class="alternate">';
			$alternate = false;
		}
		else {
			echo '<tr>';
			$alternate = true;
		}
		
		$default_link_downloads = esc_attr('<a href="'.$cc_webpath.'cc_redirect.php?cc='.$item->NAME.'&fn=">Link</a>');
		
		$default_link_views = esc_attr('<img src="'.$cc_webpath.'cc_redirect.php?cc='.$item->NAME.'" alt="" />');

		?>
		
			<td><input name="cc_items[]" type="checkbox" value="<?php echo $item->ID; ?>" />
			<?php echo $item->ID; ?></td>
			<td><?php echo $item->NAME; ?></td>
			<td align="center"><?php echo $item->COUNTER; ?></td>
			<td><?php echo date(get_option("date_format"), $item->SINCE); ?></td>
			<td><?php echo $item->DISPLAY_STR; ?></td>
			<td><input type="text" size="25" value="<?php echo $default_link_downloads; ?>" /></td>
			<td><input type="text" size="25" value="<?php echo $default_link_views; ?>" /></td>
		</tr>
		<?php
	}
	
	echo '</tbody></table>';
	
	?>
	<input class="button" type="button" value="<?php _e("Invert selection", $cc_domain); ?>" onclick="this.value=invert_sel('cc_form', 'cc_items', '<?php _e("Invert selection", $cc_domain); ?>')" />
	<input class="button" name="submit_delete_counters" type="submit" value="<?php echo $delSel_caption ?>" onclick="return confirm('<?php _e("Are you sure you want to delete selected counters?", $cc_domain); ?>');" />
	<br /><br />
	<p>
	<?php _e("Set selected counters to:", $cc_domain); ?> <input name="cc_set_counter_value" type="text" size="5" maxlength="10" />
	<input class="button-primary" type="submit" name="submit_set_counters_value" value="<?php _e("Apply"); ?>" />
	</p>
	<p>
	<?php _e("Set selected counters' date to:", $cc_domain); ?> <input name="cc_set_counter_date" type="text" size="15" maxlength="20" value="<?php echo $time_now_formatted; ?>" onfocus="displayCalendar(document.cc_form.cc_set_counter_date,'yyyy/mm/dd',this)" />
	<input class="button-primary" type="submit" name="submit_set_counters_date" value="<?php _e("Apply"); ?>" />
	</p>
	<p>
	<?php _e("Set selected counters' string to:", $cc_domain); ?> <input name="cc_set_counter_string" type="text" size="40" maxlength="255" value="<?php echo $display_str; ?>" />
	<input class="button-primary" type="submit" name="submit_set_counters_string" value="<?php _e("Apply"); ?>" />
	</form>
	</p>
	<br />
	<?php _e("If you want to redirect to an external website this must be white listed for security reason.", $cc_domain); ?><br />
	<?php _e("Note: do not prepend 'http://' or 'ftp://' to the address.", $cc_domain); ?><br />
	<form id="whitelisted_urls" name="whitelisted_urls" method="post" action="#whitelisted_urls">
	<?php wp_nonce_field('cimy_cc_admin_add_whitelist_url', 'cimy_cc_admin_add_whitelist_url_nonce', false); ?>
	<input type="text" name="cc_add_url_to_whitelist" value="" size="40" /><input class="button-primary" type="submit" name="submit_add_whitelisted_url" value="<?php _e("Add url to the white-list", $cc_domain); ?>" />
	<br /><select name="cc_whitelist_urls[]" multiple="multiple" size="10">
		<?php
			if (empty($options["whitelisted_urls"]))
				echo "<option value=''>".__("Empty list", $cc_domain)."</option>";
			else {
				foreach ($options["whitelisted_urls"] as $url)
					echo "<option value='".esc_attr($url)."'>".esc_html($url)."</option>";
			}
		?>
	</select>
	<br /><input class="button-primary" type="submit" name="submit_delete_whitelisted_urls" value="<?php _e("Delete selected urls", $cc_domain); ?>" />
	</form>
	
	</div>
	<?php
}

?>