<?php
/**
*
* Camo SSL Image Proxy [English]
*
* @package language Camo SSL Image Proxy
* @copyright (c)  2016 v12mike
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	// ACP
	'ACP_CSIP_CONFIG'				=> 'Configure',
	'CSIP_ACP'						=> 'Camo SSL Image Proxy',
	'CSIP_TITLE'					=> 'Camo SSL Image Proxy Configuration',
	'CSIP_VERSION'					=> 'Version',
	'CSIP_PROXY_CONFIG'				=> 'Proxy Settings',
	'CSIP_CAMO_MODE'				=> 'Camo Mode',
	'CSIP_SIMPLE_MODE'				=> 'Simple Mode',
	'CSIP_SIMPLE_MODE_EXPLAIN'		=> 'Allows an alternate mode using a commercial proxy service',
	'CSIP_PROXY_CSIP_PROXY_API_KEY'	=> 'Camo API Key',
	'CSIP_ACTION'					=> 'Action',
	'CSIP_DOMAIN'					=> 'Directly mapped domains',
	'CSIP_SUBDOMAINS'				=> 'Subdomains',
	'CSIP_DELETE_DOMAIN'			=> 'Delete domain',
	'CSIP_ADD_DOMAIN'				=> 'Add direct mapping domain',
	'CSIP_ADD_DOMAIN_EXPLAIN'		=> 'Add domains where the url can be directly rewritten from http:// to https:// (e.g. mydomain.com)',
	'CSIP_ENABLED'					=> 'Image Proxy Enable',
	'CSIP_ENABLED_EXPLAIN'	  	  	=> 'Allows the proxy to be disabled while this control page is still available',
	'CSIP_DISABLED'					=> 'Image Proxy Disable',
	'CSIP_PROXY_ADDRESS'	   	 	=> 'Address of the image proxy',
	'CSIP_PROXY_ADDRESS_EXPLAIN' 	=> 'No protocol specifier or trailing / (e.g.: my_site/camo)',
	'CSIP_PROXY_API_KEY'			=> 'Camo API key',
	'CSIP_PROXY_API_KEY_EXPLAIN' 	=> 'A secret key shared with the camo proxy server',
	'CSIP_SAVE_PROXY'	    		=> 'Save proxy configuration',
	'CSIP_SUBDOMAINS_ENABLED'		=> 'Include subdomains',
	'CSIP_SUBDOMAINS_DISABLED'		=> 'exclude subdomains',
	'CSIP_STRUCTURE'				=> 'Structure',
	'CSIP_FIELD'					=> 'Field',
	'CSIP_DELETE_LOCATION'			=> 'Delete Location',
	'CSIP_ADD_LOCATION'				=> 'Add Location',
	'CSIP_ADD_LOCATION_EXPLAIN'		=> 'Add template locations which may contain an image URL to be remapped (may be required for some extensions)',
	'CSIP_LOCATION_COMMENT'			=> 'Comment',

));
