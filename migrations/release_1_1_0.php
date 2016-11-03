<?php
/**
*
* @package camo ssl image proxy
* @copyright (c) 2016 v12Mike
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* Note that there were no migration files for versions before 1.1.0, as these had no ACP and no database impact.
*/

namespace phpbb\camosslimageproxy\migrations;

class release_1_1_0 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['camosslimageproxy_version']) && version_compare($this->config['camosslimageproxy_version'], '1.1.0', '>=');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v31x\v312');
	}

	public function update_data()
	{
		return array(
			array('config.add', array('camosslimageproxy_version', '1.1.0')),
			array('config.add', array('camosslimageproxy_enabled', 0)),
			array('config.add', array('camosslimageproxy_simplemode', 0)),
			array('config.add', array('camosslimageproxy_logmode', 0)),
			array('config.add', array('camosslimageproxy_proxyaddress', "")),
			array('config.add', array('camosslimageproxy_proxyapikey', "")),

			array('module.add', array(
				'acp',
				'ACP_CAT_DOT_MODS',
				'CSIP_ACP'
			)),
			array('module.add', array(
				'acp',
				'CSIP_ACP',
				array(
					'module_basename'	=> '\phpbb\camosslimageproxy\acp\camosslimageproxy_module',
					'modes'				=> array('config')
				),
			)),
			array('custom', array(array($this, 'insert_camo_location_data'))),
		);
	}

	public function revert_data()
	{
		return array(
			array('config.remove', array('camosslimageproxy_version')),
			array('config.remove', array('camosslimageproxy_enabled')),
			array('config.remove', array('camosslimageproxy_simplemode')),
			array('config.remove', array('camosslimageproxy_proxyaddress')),
			array('config.remove', array('camosslimageproxy_proxyapikey')),

			array('module.remove', array(
				'acp',
				'CSIP_ACP',
				array(
					'module_basename'	=> '\phpbb\camosslimageproxy\acp\camosslimageproxy_module',
					'modes'				=> array('config'),
				),
			)),
			array('module.remove', array(
				'acp',
				'ACP_CAT_DOT_MODS',
				'CSIP_ACP'
			))
		);
	}

	//lets create the needed tables
	public function update_schema()
	{
		return array(
			'add_tables'    => array(
				$this->table_prefix . 'camo_domains' => array(
					'COLUMNS'		=> array(
						'domain_id'		=> array('UINT:8', NULL, 'auto_increment'),
						'domain'		=> array('VCHAR:50', ''),
						'subdomains'	=> array('UINT:8', 1)
					),
					'PRIMARY_KEY'    => 'domain_id',
				),
				$this->table_prefix . 'camo_locations' => array(
					'COLUMNS'		=> array(
						'location_id'	=> array('UINT:8', NULL, 'auto_increment'),
						'location'		=> array('VCHAR:50', ''),
						'field'			=> array('VCHAR:50', ''),
						'comment'		=> array('VCHAR:255', ''),
						'core'			=> array('UINT:8', 0)
					),
					'PRIMARY_KEY'    => 'location_id',
				)
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_tables'		=> array(
				$this->table_prefix . 'camo_domains',
				$this->table_prefix . 'camo_locations'
			),
		);
	}

	public function insert_camo_location_data()
	{
		$initial_data = array(
			array('location' => 'postrow','field' => 'MESSAGE','comment' => 'Core','core' => '1'),
			array('location' => 'postrow','field' => 'SIGNATURE','comment' => 'Core','core' => '1'),
			array('location' => 'postrow','field' => 'POSTER_AVATAR','comment' => 'Core','core' => '1'),
			array('location' => 'headers','field' => 'AVATAR','comment' => 'Core','core' => '1'),
			array('location' => 'headers','field' => 'SIGNATURE_PREVIEW','comment' => 'Core','core' => '1'),
			array('location' => 'headers','field' => 'PREVIEW_MESSAGE','comment' => 'Core','core' => '1'),
			array('location' => 'headers','field' => 'PREVIEW_SIGNATURE','comment' => 'Core','core' => '1'),
			array('location' => 'headers','field' => 'AUTHOR_AVATAR','comment' => 'Core','core' => '1'),
			array('location' => 'headers','field' => 'MESSAGE','comment' => 'Core','core' => '1'),
			array('location' => 'headers','field' => 'SIGNATURE','comment' => 'Core','core' => '1'),
			array('location' => 'headers','field' => 'POST_PREVIEW','comment' => 'Core','core' => '1'),
			array('location' => 'headers','field' => 'AVATAR_IMG','comment' => 'Core','core' => '1'),
			array('location' => 'headers','field' => 'CURRENT_USER_AVATAR','comment' => 'Core','core' => '1'),
			array('location' => 'topic_review_row','field' => 'MESSAGE','comment' => 'Core','core' => '1'),
			array('location' => 'history_row','field' => 'MESSAGE','comment' => 'Core','core' => '1'),
			array('location' => 'searchresults','field' => 'MESSAGE','comment' => 'Core','core' => '1'),
			array('location' => 'notifications','field' => 'AVATAR','comment' => 'Core','core' => '1'),
			array('location' => 'notification_list','field' => 'AVATAR','comment' => 'Core','core' => '1'),
			array('location' => 'topicrow','field' => 'TOPIC_PREVIEW_FIRST_AVATAR','comment' => 'Topic Preview Extension','core' => '0'),
			array('location' => 'topicrow','field' => 'TOPIC_PREVIEW_LAST_AVATAR','comment' => 'Topic Preview Extension','core' => '0'),
			array('location' => 'postrow','field' => 'TOPIC_PREVIEW_FIRST_AVATAR','comment' => 'Topic Preview Extension','core' => '0'),
			array('location' => 'postrow','field' => 'TOPIC_PREVIEW_LAST_AVATAR','comment' => 'Topic Preview Extension','core' => '0'),
			array('location' => 'searchresults','field' => 'TOPIC_PREVIEW_FIRST_AVATAR','comment' => 'Topic Preview Extension','core' => '0'),
			array('location' => 'searchresults','field' => 'TOPIC_PREVIEW_LAST_AVATAR','comment' => 'Topic Preview Extension','core' => '0'),
			);
		$this->db->sql_multi_insert($this->table_prefix.'camo_locations', $initial_data);
	}
}

