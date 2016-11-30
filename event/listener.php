<?php
/**
*
* @package camo-ssl-image-proxy
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\camosslimageproxy\event;

define('ONE_MONTH', '2500000'); //seconds (approximately)

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			'core.page_footer_after'	=> 'rewrite_assets',
		);
	}

	/**
	 * Rewrites an image tag into a version that can be used by a Camo asset server
	 *
	 * @param	array	$object	The array containing the data to rewrite
	 * @param	string	$key	The key into the array. The element to rewrite.
	 * @return	void
	 */
	private function rewrite_images(&$object, $key, $domains)
	{
		if (!empty($object[$key]))
		{
			if (preg_match_all('#<img [^>]*src="(http://[^"]+)" [^/]+ />#', $object[$key], $matches))
			{
				foreach ($matches[1] as $url)
				{
					foreach ($domains as $row)
					{
						$domain = $row['domain'] . '/' ;
						$subdomains = $row['subdomains'];
						$match = stripos($url, $domain);
						if ($match !== false)
						{
							if (($subdomains != 0) || ($match == 7)) // 7 chars in "http://"
							{
								// just rewrite http:// to https:// for domains (including this one) that should support it
								$object[$key] = preg_replace('#http:#', 'https:', $object[$key]);
								break;
							}
						}
					}
					// rewite others for  "simple mode" proxy (if so configured)
					if ($this->config['camosslimageproxy_simplemode'])
					{
						// the substr($url, 7) trims the leading http:// from the url
						$object[$key] = str_replace($url, 'https://' . $this->config['camosslimageproxy_proxyaddress'] . substr($url, 7) . $this->config['camosslimageproxy_proxyapikey'], $object[$key]);
					}
					//  rewrite url for Camo proxy server
					else
					{
						$digest = hash_hmac('sha1', $url, $this->config['camosslimageproxy_proxyapikey']);
						$object[$key] = str_replace($url, 'https://' . $this->config['camosslimageproxy_proxyaddress'] . '/' . $digest . '/' . bin2hex($url), $object[$key]);
					}
				}
			}
		}
	}

	/**
	 * Adds an unhandled insecure link location to the database
	 *
	 * @param	string	$key	The template name.
	 * @param	string	$location	The element name.
	 * @param	array	$locations	The array containing the 
	 *  			configured set of locations
	 * @return	void
	 */
	private function unhandled_insecure_link($location, $field, &$locations)
	{
		global $cache;

		foreach ($locations as $configured_location)
		{
			if (($configured_location['location'] == $location) && ($configured_location['field'] == $field))
			{
				// already in the database, re-enable it if disabled, otherwise ignore it
				if ($configured_location['core'] == 0)
				{
					$sql = 'UPDATE ' . $this->table_prefix . 'camo_locations SET core=2 WHERE location_id=' . $configured_location['location_id'];
					$this->db->sql_query($sql);
					$cache->destroy('sql', $this->table_prefix . 'camo_locations');
					// refresh the locations array to pick up the one we have just updated
					$sql = 'SELECT location, field, core, location_id FROM ' . $this->table_prefix . 'camo_locations';
					$result = $this->db->sql_query($sql);
					$locations = $this->db->sql_fetchrowset($result);
					$this->db->sql_freeresult($result);
				}
				return;
			}
		}
		// not found, so add it to the database
		$this->user->add_lang('acp/common');
		$this->user->add_lang_ext('phpbb/camosslimageproxy', 'info_acp_camosslimageproxy');
		$sql = 'INSERT INTO ' . $this->table_prefix . 'camo_locations' . $this->db->sql_build_array('INSERT', array(
			'location'	=> $location,
			'field'		=> $field,
			'core'		=> 2,
			'comment'	=> $this->user->lang['CSIP_ADDED_BY_TRAINING'],
		));
		$this->db->sql_query($sql);
		$cache->destroy('sql', $this->table_prefix . 'camo_locations');
		// refresh the locations array to pick up the one we have just added
		$sql = 'SELECT location, field, core, location_id FROM ' . $this->table_prefix . 'camo_locations';
		$result = $this->db->sql_query($sql);
		$locations = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);


	}

	public function rewrite_assets($event)
	{
		global $request;
		global $phpbb_container;

		if ($this->config['camosslimageproxy_enabled'] == 0)
			return;

		$context = $phpbb_container->get('template_context');
		$rootref = &$context->get_root_ref();
		$tpldata = &$context->get_data_ref();


		// get all the domains that are directly remapped
		// do it here for efficiency
		$sql = 'SELECT domain, subdomains FROM ' . $this->table_prefix . 'camo_domains';
		// cache the query for a while 
		$result = $this->db->sql_query($sql, ONE_MONTH);
		$domains = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		// get all the fields that need to be patched
		$sql = 'SELECT location, field, core, location_id FROM ' . $this->table_prefix . 'camo_locations';
		// cache the query for a while 
		$result = $this->db->sql_query($sql, ONE_MONTH);
		$locations = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		foreach ($locations as $row)
		{
			if ($row['core'] == 0)
			{
				// this one is disabled
				continue;
			}
			$location = $row['location'];
			if ($location == 'headers')
			{
				// patch header fields
				$this->rewrite_images($rootref, $row['field'], $domains);
			}
			else
			{
				// patch all other required fields
				if (isset($tpldata[$location]))
				{
					foreach ($tpldata[$location] as &$tplrow)
					{
						$this->rewrite_images($tplrow, $row['field'], $domains);
					}
				}
			}
		}

		// catch any http:// image links and add to database
		// only if in 'learning mode' and user is admin
		if (($this->config['camosslimageproxy_enabled'] == 2) && $this->auth->acl_get('a_'))
		{
			foreach ($tpldata as $key=>$object)
			{
				foreach ($object as $item)
				{
					foreach ($item as $field=>$string)
					{
						if (gettype($string) == 'string')
						{
							if (preg_match('#<img [^>]*src="http://[^"]+" [^/]+ />#', $string))
							{
								// we have found an http:// image link (after rewriting all configured locations)
								$this->unhandled_insecure_link(($key == '.')?'headers':$key, $field, $locations);
							}
						}
					}
				}
			}
		}

	}

	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, $table_prefix, $auth, $user)
	{
	   $this->config = $config;
	   $this->db = $db;
	   $this->table_prefix = $table_prefix;
	   $this->auth = $auth;
	   $this->user = $user;
	}
}
