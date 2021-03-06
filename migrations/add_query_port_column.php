<?php

/**
*
* @package phpBB Extension - Servers Board
* @copyright (c) 2016 Token07
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace token07\serversboard\migrations;

// The order of the array was ignored (column dropped and not readded)
//  in my test so migration was split.
class add_query_port_column extends \phpbb\db\migration\migration
{
	static public function depends_on()
    {
        return array('\token07\serversboard\migrations\server_type_column_fix_pt2');
	}
	public function update_schema()
	{
		return array(
			'add_columns'	=> array(
				$this->table_prefix . 'serversboard'	=> array(
					'server_query_port'	=> array('UINT', NULL),
				),
			),
		);
	}
	public function revert_schema()
	{
		return array(
			'drop_columns'	=> array(
				$this->table_prefix . 'serversboard'	=> array(
					'server_query_port',
				),
			),
		);
	}
}