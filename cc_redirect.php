<?php

// if 'cc' GET query, start counter!
if (isset($_GET['cc'])) {
	$found_it = false;
	$rootPath = dirname(__FILE__);
	$rootPathArray = explode("/", $rootPath);
	$numDirs = count($rootPathArray);
	
	for ($i = 1; $i <= $numDirs; $i++) {
	
		$path = implode("/", $rootPathArray);
		
		if (file_exists($path.'/wp-load.php')) {
			require_once($path.'/wp-load.php');
			$found_it = true;
			break;
		
		} else if (file_exists($path.'/wp-config.php')) {
			require_once($path.'/wp-config.php');
			$found_it = true;
			break;
		}
	
		array_pop($rootPathArray);
	}
	
	// WARNING
	// if you moved wp-content away from blog's default location
	// to let the plug-in work properly you need to add here your wp-load.php server path
	// for example: require_once("/home/user/public_html/myblog/wp-load.php");
	if (!$found_it)
		require_once("put_your_wp-load.php_server_path_here");

	$counter = $_GET['cc'];
	cc_add_one_to_the_counter($counter);
	// protect from site traversing
	$cimy_counter_url = esc_url(str_replace('../', '', $_GET['fn']));

	if (!empty($cimy_counter_url))
	{
		add_filter("allowed_redirect_hosts", "cimy_cc_set_whitelisted_urls");
		wp_safe_redirect($cimy_counter_url);
	}
	else
		header("location: images/blank.gif");

	exit;
}

function cimy_cc_set_whitelisted_urls($urls) {
	global $cc_options;
	$options = get_option($cc_options);

	if (!empty($options["whitelisted_urls"]))
		$urls = array_merge($urls, $options["whitelisted_urls"]);
	return $urls;
}

?>
