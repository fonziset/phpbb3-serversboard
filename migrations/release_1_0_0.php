<?php

namespace token07\serversboard\migrations;

class release_1_0_0 extends \phpbb\db\migration\migration
{
	public function update_data()
	{
		return array(
			array(
				'config.add', array('serversboard_enable', 0)
			),
			array(
				'config.add', array('serversboard_update_time', 300)
			),
			array(
				'config.add', array('serversboard_update_last_run', 0)
			),
			array(
				'module.add', array(
					'acp',
					'ACP_CAT_DOT_MODS',
					'Servers Board'
				)
			),
			array(
				'module.add', array(
					'acp',
					'Servers Board',
					array(
						'module_basename'	=> '\token07\serversboard\acp\serversboard_module'
					),
				)
			),
		);
	}
	public function update_schema()
	{
		return array(
			'add_tables'	=> array(
				$this->table_prefix . 'serversboard' => array(
					'COLUMNS'	=> array(
						'server_id'			=> array('UINT', NULL, 'auto_increment'),
						'server_order'		=> array('UINT', 1),
						'server_ip'			=> array('VCHAR:60', NULL),
						'server_status'		=> array('UINT', NULL),
						'server_hostname'	=> array('VCHAR:255', NULL),
						'server_map'		=> array('VCHAR:32', NULL),
						'server_players'	=> array('TEXT', '0 / 0'),
						'server_playerlist'	=> array('TEXT', '[]'),
					),
					'PRIMARY_KEY'	=> 'server_id',
				)
			)
		);
	}
	public function revert_schema()
	{
		return array(
			'drop_tables' => array(
				$this->table_prefix . 'serversboard',
			)
		);
	}
}
