<?php

/**
*
* @package phpBB Extension - Servers Board
* @copyright (c) 2016 Token07
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace token07\serversboard\acp;

class serversboard_module
{
	var $u_action;
	function main($id, $mode)
	{
		global $config, $request, $template, $user, $db, $table_prefix, $request, $phpbb_log;
		//$user->add_lang('acp/common');
		$user->add_lang_ext('token07/serversboard', 'acp/serversboard_acp');
		switch ($mode)
		{
			case 'servers':
				if (isset($_GET['action']))
				{
					$action = $request->variable('action', '');
					switch ($action)
					{
						case 'delete':
							if (confirm_box(true))
							{
								$server_id = $request->variable('server_id', 0);
								$result = $db->sql_query("SELECT server_ip FROM {$table_prefix}serversboard WHERE server_id = $server_id");
								if ($row = $db->sql_fetchrow($result))
								{
									$db->sql_query("DELETE FROM {$table_prefix}serversboard WHERE server_id = $server_id");
									$phpbb_log->add('admin', $user->data['user_id'], $user->data['session_ip'], 'TOKEN07_SERVERSBOARD_ACP_LOG_DELETE', time(), array($row['server_ip']));
									
									trigger_error($user->lang('TOKEN07_SERVERSBOARD_ACP_DELETED') . adm_back_link($this->u_action));
								}
								trigger_error($user->lang('TOKEN07_SERVERSBOARD_ACP_NO_SERVER') . adm_back_link($this->u_action), E_USER_WARNING);
							}
							$fields = build_hidden_fields(array(
								'action' => 'delete',
								'server_id'	=> $request->variable('server_id', 0),
							));
							confirm_box(false, $user->lang('TOKEN07_SERVERSBOARD_ACP_CONFIRMDEL'), $fields);
						break;
						case 'move_up':
						case 'move_down':
							$this->move($request->variable('server_id', 0), $action == "move_up" ? 1 : -1);
							if ($request->is_ajax())
							{
								$json_response = new \phpbb\json_response;
								$json_response->send(array('success' => true));
								return;
							}
						default:
						break;
					}
				}
				$this->tpl_name = 'serversboard_manage';
				$this->page_title = $user->lang('TOKEN07_SERVERSBOARD_ACP_SERVERSBOARD');
				$result = $db->sql_query("SELECT server_id, server_order, server_ip, server_hostname, server_lastupdate FROM {$table_prefix}serversboard ORDER BY server_order ASC");
				while ($row = $db->sql_fetchrow($result))
				{
					$tmp = array(
						'NAME'			=> htmlentities($row['server_hostname']), 
						'IP'			=> $row['server_ip'],
						'LASTUPDATE'	=> $user->format_date($row['server_lastupdate']),
						'U_DELETE'		=> "{$this->u_action}&amp;action=delete&amp;server_id={$row['server_id']}",
						'U_MOVE_UP'		=> "{$this->u_action}&amp;action=move_up&amp;server_id={$row['server_id']}",
						'U_MOVE_DOWN'	=> "{$this->u_action}&amp;action=move_down&amp;server_id={$row['server_id']}",
					);
					$template->assign_block_vars('serverlist', $tmp);
				}
				//trigger_error("Not done yet" . adm_back_link($this->u_action), E_USER_WARNING);
			break;
			case 'settings':
				add_form_key('token07/serversboard');
				if ($request->is_set_post('submit'))
				{
					if (!check_form_key('token07/serversboard'))
					{
						trigger_error('FORM_INVALID', E_USER_WARNING);
					}
					$config->set('serversboard_enable', $request->variable('token07_serversboard_enable', 1));
					$config->set('serversboard_navbar_link_enable', $request->variable('token07_serversboard_navbar_link_enable', 0));
					$config->set('serversboard_update_time', $request->variable('token07_serversboard_interval', 1));
					
					$phpbb_log->add('admin', $user->data['user_id'], $user->data['session_ip'], 'TOKEN07_SERVERSBOARD_ACP_LOG_UPDATE', time());
					trigger_error($user->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
				}
				$this->tpl_name = 'serversboard_settings';
				$this->page_title = $user->lang('TOKEN07_SERVERSBOARD_ACP_SERVERSBOARD');
				$template->assign_vars(array(
					'TOKEN07_SERVERSBOARD_ENABLE'				=> $config['serversboard_enable'],
					'TOKEN07_SERVERSBOARD_INTERVAL'				=> $config['serversboard_update_time'],
					'TOKEN07_SERVERSBOARD_NAVBAR_LINK_ENABLE'	=> $config['serversboard_navbar_link_enable'],
				));
			break;
			case 'add':
				require_once(__DIR__ . '/../includes/functions_serversboard.php');
				
				add_form_key('token07/serversboard');
				$this->tpl_name = 'serversboard_add';
				$this->page_title = $user->lang('TOKEN07_SERVERSBOARD_ACP_ADD');
				if ($request->is_set_post('submit'))
				{
					if (!check_form_key('token07/serversboard'))
					{
						trigger_error('FORM_INVALID', E_USER_WARNING);
					}
					$server_ip = $request->variable('token07_serversboard_ip', '');
					$server_port = $request->variable('token07_serversboard_port', 0);
					$server_name = $request->variable('token07_serversboard_hostname', '');
					$server_protocol = $request->variable('token07_serversboard_protocol', '');
					$server_queryport = $request->variable('token07_serversboard_queryport', '');
					
					// Validate IP and port
					if (!filter_var($server_ip, FILTER_VALIDATE_IP))
					{
						trigger_error($user->lang('TOKEN07_SERVERSBOARD_ACP_INVALIDIP') . adm_back_link($this->u_action . "&amp;server_ip=$server_ip&amp;server_port=$server_port"), E_USER_WARNING);
					}
					if ($server_port <= 0 || $server_port >= 65535)
					{
						trigger_error($user->lang('TOKEN07_SERVERSBOARD_ACP_INVALIDPORT') . adm_back_link($this->u_action . "&amp;server_ip=$server_ip&amp;server_port=$server_port"), E_USER_WARNING);
					}
					
					// Find the highest id number 
					$result = $db->sql_query("SELECT MAX(server_order) AS max FROM {$table_prefix}serversboard");
					if (!$row = $db->sql_fetchrow($result))
					{
						$max = 1;
					}
					else
					{
						$max = $row['max']+1;
					}
					$db->sql_freeresult($result);
					
					// Sanitize for SQL
					$server_ip = $db->sql_escape($server_ip . ':' . $server_port);
					$server_name = $db->sql_escape($server_name);
					$columns = array(
						'server_ip'			=> $server_ip,
						'server_order'		=> $max,
						'server_hostname'	=> $server_name,
						'server_players'	=> '0 / 0',
						'server_playerlist'	=> '[]',
						'server_lastupdate'	=> 0,
						'server_query_port'	=> (empty($server_queryport)) ? NULL : $server_queryport,
					);
					if ($server_queryport != '')
					{
						$columns['server_queryport'] = $server_queryport;
					}
					$sql = 'INSERT INTO ' . $table_prefix . 'serversboard' . ' ' . $db->sql_build_array('INSERT', $columns);
					$db->sql_query($sql);
					//$db->sql_query("INSERT INTO {$table_prefix}serversboard (server_ip, server_order, server_hostname, server_players, server_playerlist, server_lastupdate) VALUES ('$server_ip', $max , '$server_name', '-', '[]', 0)");
					
					$task = new \token07\serversboard\cron\task\update_serversboard($config, $db);
					$task->run();
					
					$phpbb_log->add('admin', $user->data['user_id'], $user->data['session_ip'], 'TOKEN07_SERVERSBOARD_ACP_LOG_ADDED', time(), array($server_ip));
					trigger_error($user->lang('TOKEN07_SERVERSBOARD_ACP_ADDED'). adm_back_link($this->u_action));
				}
				$protocols = \token07\serversboard\includes\get_supported_protocols();
				$curProto = '';
				$baseProtos = array();
				$sortExceptions = array('bf3', 'quake3', 'samp', 'ase', 'starmade', 'lhmp'); // Servers that are actually games
				foreach ($protocols AS $protocol)
				{
					//var_dump($className, $protocol['protocol']);
					//var_dump($protocol['short'],in_array($protocol['short'], $sortExceptions));
					if ( ($protocol['short'] != $protocol['protocol']) || ($protocol['short'] == $protocol['protocol'] && in_array($protocol['short'], $sortExceptions)) )
					{
						if ($protocol['protocol'] != $curProto)
						{
							//print_r($protocol['protocol']);
							$curProto = $protocol['protocol'];
							$template->assign_block_vars('serversboard_base_protocols', array(
								'CATEGORY'	=> $protocol['protocol'],
							));
						}
						$template->assign_block_vars('serversboard_base_protocols.protocols', array(
							'NAME'		=> $protocol['name'],
							'VALUE'		=> $protocol['protocol'],
						));
					}
					else
					{
						$baseProtos[] = $protocol;
					}
				}
				$template->assign_block_vars('serversboard_base_protocols', array(
					'CATEGORY'	=> $user->lang('OTHER'),
				));
				foreach ($baseProtos AS $protocol)
				{
					//print_r($protocol);
					$template->assign_block_vars('serversboard_base_protocols.protocols', array(
							'NAME'		=> $protocol['name'],
							'VALUE'		=> $protocol['protocol'],
					));
				}
				//var_dump($protocols);
			break;
		}
	}
	// Some logic from includes/acp/acp_groups
	function move($id, $delta)
	{
		global $db, $table_prefix;
		
		$delta = (int) $delta;
		if (!$delta)
		{
			return false;
		}

		$move_up = ($delta > 0) ? true : false;
		// Get the current position.
		$sql = "SELECT server_order FROM {$table_prefix}serversboard WHERE server_id = $id";
		$result = $db->sql_query($sql);
		if (!$data = $db->sql_fetchrow($result))
		{
			throw new Exception("???");
		}
		$current_value = (int) $data['server_order'];
		
		if (!$move_up)
		{
			$delta = abs($delta) + 1;
		}
		$sql = 'SELECT server_order
			FROM ' . "{$table_prefix}serversboard" . '
			WHERE server_order' . (($move_up) ? ' < ' : ' > ') . $current_value . '
			ORDER BY server_order' . (($move_up) ? ' DESC' : ' ASC');
			$result = $db->sql_query_limit($sql, $delta);
	
		$sibling_count = 0;
		$sibling_limit = $delta;
		
		// Reset the delta, as we recalculate the new real delta
		$delta = 0;
		while ($row = $db->sql_fetchrow($result))
		{
			$sibling_count++;
			$delta = $current_value - $row['server_order'];

			if (!$move_up && $sibling_count == $sibling_limit)
			{
				// Remove the additional sibling we added previously
				$delta++;
			}
		}
		$db->sql_freeresult($result);
		
		if ($delta)
		{
			// First we move all items between our current value and the target value up/down 1,
			// so we have a gap for our item to move.
			$sql = 'UPDATE ' . "{$table_prefix}serversboard" . '
				SET server_order = server_order' . (($move_up) ? ' + 1' : ' - 1') . '
				WHERE server_order' . (($move_up) ? ' >= ' : ' <= ') . ($current_value - $delta) . '
					AND server_order' . (($move_up) ? ' < ' : ' > ') . $current_value;
			$db->sql_query($sql);
			//$this->cache->destroy('sql', 'phpbb_serversboard');

			// And now finally, when we moved some other items and built a gap,
			// we can move the desired item to it.
			$sql = 'UPDATE ' . "{$table_prefix}serversboard" . '
				SET server_order = server_order ' . (($move_up) ? ' - ' : ' + ') . abs($delta) . '
				WHERE server_id = ' . (int) $id;
			$db->sql_query($sql);

			$db->sql_transaction('commit');
			//$this->cache->destroy('sql', TEAMPAGE_TABLE);
			return true;
		}
	}
}