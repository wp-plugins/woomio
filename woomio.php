<?php

/*
Plugin Name: Woomio

Plugin URI: https://www.woomio.com/en/

Description: This plugin eases the use of Woomio for WordPress users. With Woomio, anyone can post their purchases - and get a revenue from it.

Author: The Woomio Team

Version: 1.0.1
*/

if (!defined('ABSPATH') || !function_exists('is_admin'))
{
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}



if(!class_exists("Woomio_Blogger"))
{
	class Woomio_Blogger
	{
		function __construct()
        {
            if (is_admin()) {
                if (!class_exists("WoomioBloggerSettingsPage")) {
                    require_once plugin_dir_path(__FILE__) . 'woomio-settings.php';
                }

                $this->settings = new WoomioBloggerSettingsPage();
            } else {

            }
        }
	}

}

global $woomioblogger;
if(!$woomioblogger)
{
	$woomioblogger = new Woomio_Blogger();
}


add_action('wp_head', 'woomio_statis_js');

if(woomio_convertlink_check())
{
  add_action('wp_head', 'woomio_convert_link_js');
}

function woomio_statis_js()
{
	$data = get_option("woomio_blogger_option_name");
  	echo '<script src="https://www.woomio.com/assets/js/analytics/co.js" id="wa" data-u='.$data["woomio_blogger_id"].'></script>';
}

function woomio_convert_link_js()
{
 	$data = get_option("woomio_blogger_option_name");
	echo '<script src="https://www.woomio.com/assets/js/tools/lnk.js" id="wlnk" data-u='.$data["woomio_blogger_id"].'></script>';
}

function woomio_convertlink_check()
{
	$data = get_option("woomio_blogger_option_name");
	if(isset($data["woomio_convertlink_checkbox"]))
	{
		return $data["woomio_convertlink_checkbox"]=="on" ? true : false;
	}else
		return false; 
	
}

?>