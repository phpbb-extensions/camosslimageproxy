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
			'core.page_footer'	=> 'rewrite_assets',
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
		$sql = 'SELECT location, field FROM ' . $this->table_prefix . 'camo_locations';
		// cache the query for a while 
		$result = $this->db->sql_query($sql, ONE_MONTH);
		$locations = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		foreach ($locations as $row)
		{
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
	}

	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, $table_prefix)
	{
	   $this->config = $config;
	   $this->db = $db;
	   $this->table_prefix = $table_prefix;
	}
}
