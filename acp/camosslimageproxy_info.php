<?php

/**
*
* @package camo-ssl-image-proxy
* @copyright (c) 2016 v12Mike
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\camosslimageproxy\acp;

/**
* @package module_install
*/
class camosslimageproxy_info
{
	function module()
	{
		return array(
			'filename'	=> 'phpbb/camosslimageproxy/acp/camosslimageproxy_module',
			'title'		=> 'Camo SSL Image Proxy',
			'version'	=> '1.1.0',
			'modes'		=> array(
				'config'	=> array('title' => 'ACP_CSIP_CONFIG', 
									 'auth' => 'ext_phpbb/camosslimageproxy', 
									 'cat'	=> array('CSIP_EXT')),
			),
		);
	}
}
