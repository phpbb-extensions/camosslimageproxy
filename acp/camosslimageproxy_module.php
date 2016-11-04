<?php
/**
*
* @package camo-ssl-image-proxy
* @copyright (c) 2016 v12Mike
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\camosslimageproxy\acp;

class camosslimageproxy_module
{
	var $u_action;

	function main($id, $mode)
	{
		global $db, $user, $auth, $template, $cache, $request;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx, $table_prefix;

		$this->config = $config;
		$this->request = $request;
		$this->template = $template;
		$this->db = $db;
		$this->cache = $cache;

		$user->add_lang('acp/common');
		$user->add_lang_ext('phpbb/camosslimageproxy', 'info_acp_camosslimageproxy');
		$this->tpl_name = 'acp_camosslimageproxy';
		$this->page_title = $user->lang['CAMO_PROXY'];
		add_form_key('acp_camosslimageproxy');

		if ($request->is_set_post('submit'))
		{
			if (!check_form_key('acp_camosslimageproxy'))
			{
				trigger_error('FORM_INVALID');
			}
			if (!function_exists('validate_data'))
			{
				include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
			}

			$check_row = array('camosslimageproxy_proxyaddress' => $request->variable('camosslimageproxy_proxyaddress', 0));
			$validate_row = array('camosslimageproxy_proxyaddress' => array('string', false, 0, 5));
			$error = validate_data($check_row, $validate_row);

			// Replace "error" strings with their real, localised form
			$error = array_map(array($user, 'lang'), $error);

			// more data validation could be inserted here...

			if (!sizeof($error))
			{
				$config->set('camosslimageproxy_enabled',      	$request->variable('camosslimageproxy_enabled', 0));
				$config->set('camosslimageproxy_simplemode',	$request->variable('camosslimageproxy_simplemode', 0));
				$config->set('camosslimageproxy_proxyaddress',  $request->variable('camosslimageproxy_proxyaddress', "", true));
				$config->set('camosslimageproxy_proxyapikey', 	$request->variable('camosslimageproxy_proxyapikey', "", true));
			}
		}
		elseif ($request->is_set_post('delete_domain_') && $request->variable('delete_domain_', array(0 => '')))
		{
			// deletion of configured domain has been requested
			$domain_id = array_keys($request->variable('delete_domain_', array(0 => '')));
			$sql = 'DELETE FROM ' . $table_prefix . 'camo_domains' . ' WHERE domain_id = ' . $domain_id[0];
			$this->db->sql_query($sql);
			$this->cache->destroy('sql', $table_prefix . 'camo_domains');
		}
		elseif ($request->is_set_post('delete_location_') && $request->variable('delete_location_', array(0 => '')))
		{
			// deletion of configured location has been requested
			$location_id = array_keys($request->variable('delete_location_', array(0 => '')));
			$sql = 'DELETE FROM ' . $table_prefix . 'camo_locations' . ' WHERE location_id = ' . $location_id[0];
			$this->db->sql_query($sql);
		}
		elseif ($request->is_set_post('add_domain'))
		{
			// add a new domain to the db
			$sql = 'INSERT INTO ' . $table_prefix . 'camo_domains' . $this->db->sql_build_array('INSERT', array(
				'domain'		=> $request->variable('camosslimageproxy_adddomain', "", true),
				'subdomains'	=> $request->variable('camosslimageproxy_subdomains', 1),
			));
			$this->db->sql_query($sql);
			$this->cache->destroy('_camo_domains');
		}
		elseif ($request->is_set_post('add_location'))
		{
			// add a new location to the db
			$sql = 'INSERT INTO ' . $table_prefix . 'camo_locations' . $this->db->sql_build_array('INSERT', array(
				'location'		=> $request->variable('camosslimageproxy_addlocation', "", true),
				'field'	=> $request->variable('camosslimageproxy_addfield', "", true),
				'comment'	=> $request->variable('camosslimageproxy_addcomment', "", true),
			));
			$this->db->sql_query($sql);
		}


		// display the list of configured domains
	   	$sql = 'SELECT domain_id, domain, subdomains FROM ' . $table_prefix . 'camo_domains ';
		$result = $this->db->sql_query_limit($sql, $this->config['topics_per_page']);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('directurlrewrites', array(
				'DOMAIN_ID'		=> $row['domain_id'],
				'DOMAIN'		=> $row['domain'],
				'SUBDOMAINS'	=> $row['subdomains'],
				));
		}
		$this->db->sql_freeresult($result);

		// display the list of configured locations
	   	$sql = 'SELECT location_id, location, field, comment, core FROM ' . $table_prefix . 'camo_locations ';
		$result = $this->db->sql_query_limit($sql, $this->config['topics_per_page']);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('camolocations', array(
				'LOCATION_ID'	=> $row['location_id'],
				'LOCATION'		=> $row['location'],
				'FIELD'			=> $row['field'],
				'COMMENT'		=> $row['comment'],
				'CORE'	=> $row['core'],
				));
		}
		$this->db->sql_freeresult($result);

		// fill-in the template
		$template->assign_vars(array(
			'ENABLED'			=> (!empty($this->config['camosslimageproxy_enabled'])) ? true : false,
			'SIMPLE_MODE'		=> (!empty($this->config['camosslimageproxy_simplemode'])) ? true : false,
			'PROXY_ADDRESS'		=> (!empty($this->config['camosslimageproxy_proxyaddress'])) ? $this->config['camosslimageproxy_proxyaddress'] : "",
			'PROXY_API_KEY'		=> (!empty($this->config['camosslimageproxy_proxyapikey'])) ? $this->config['camosslimageproxy_proxyapikey'] : "",
			'CSIP_VERSION'		=> $this->config['camosslimageproxy_version'],
			'IR_ERROR'	        => isset($error) ? ((sizeof($error)) ? implode('<br />', $error) : '') : '',
			'U_ACTION'			=> $this->u_action,
		));

	}
}
