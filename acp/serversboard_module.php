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
		global $config, $request, $template, $user, $db, $table_prefix, $request;
		//$user->add_lang('acp/common');
		$user->add_lang_ext('token07/serversboard', 'acp/serversboard_acp');
		switch ($mode)
		{
			case 'servers':
				if (isset($_GET['action']))
				{
					$action = request_var('action', '');
					switch ($action)
					{
						case 'delete':
							if (confirm_box(true))
							{
								trigger_error("It wasn't actually deleted (yet)" . adm_back_link($this->u_action));
							}
							$fields = build_hidden_fields(array(
								'action' => 'delete',
								'server_id'	=> request_var('server_id', 0),
							));
							confirm_box(false, "Are you sure you want to delete this server?", $fields);
						break;
						case 'move_up':
						case 'move_down':
							$this->move(request_var('server_id', 0), $action == "move_up" ? 1 : -1);
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
					$tmp = array('NAME' => $row['server_hostname'], 'IP' => $row['server_ip']);
					$tmp['LASTUPDATE'] = $user->format_date($row['server_lastupdate']);
					// Links
					$tmp['U_DELETE'] = "{$this->u_action}&amp;action=delete&amp;server_id={$row['server_id']}";
					$tmp['U_MOVE_UP'] = "{$this->u_action}&amp;action=move_up&amp;server_id={$row['server_id']}";
					$tmp['U_MOVE_DOWN'] = "{$this->u_action}&amp;action=move_down&amp;server_id={$row['server_id']}";
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
					$config->set('serversboard_update_time', $request->variable('token07_serversboard_interval', 1));
					
					trigger_error($user->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
				}
				$this->tpl_name = 'serversboard_settings';
				$this->page_title = $user->lang('TOKEN07_SERVERSBOARD_ACP_SERVERSBOARD');
				$template->assign_vars(array(
					'TOKEN07_SERVERSBOARD_ENABLE'	=> $config['serversboard_enable'],
					'TOKEN07_SERVERSBOARD_INTERVAL'	=> $config['serversboard_update_time'],
				));
			break;
			case 'add':
				add_form_key('token07/serversboard');
				$this->tpl_name = 'serversboard_add';
				$this->page_title = $user->lang('TOKEN07_SERVERSBOARD_ACP_EDIT');
				if ($request->is_set_post('submit'))
				{
					if (!check_form_key('token07/serversboard'))
					{
						trigger_error('FORM_INVALID', E_USER_WARNING);
					}
					trigger_error("Not done yet" . adm_back_link($this->u_action), E_USER_WARNING);
				}
			break;
		}
	}
	// Some logic from includes/acp/acp_groups
	function move($id, $delta)
	{
		global $db;
		
		$delta = (int) $delta;
		if (!$delta)
		{
			return false;
		}

		$move_up = ($delta > 0) ? true : false;
		// Get the current position.
		$sql = "SELECT server_order FROM phpbb_serversboard WHERE server_id = $id";
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
			FROM ' . "phpbb_serversboard" . '
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
			$sql = 'UPDATE ' . 'phpbb_serversboard' . '
				SET server_order = server_order' . (($move_up) ? ' + 1' : ' - 1') . '
				WHERE server_order' . (($move_up) ? ' >= ' : ' <= ') . ($current_value - $delta) . '
					AND server_order' . (($move_up) ? ' < ' : ' > ') . $current_value;
			$db->sql_query($sql);
			//$this->cache->destroy('sql', 'phpbb_serversboard');

			// And now finally, when we moved some other items and built a gap,
			// we can move the desired item to it.
			$sql = 'UPDATE ' . 'phpbb_serversboard' . '
				SET server_order = server_order ' . (($move_up) ? ' - ' : ' + ') . abs($delta) . '
				WHERE server_id = ' . (int) $id;
			$db->sql_query($sql);

			$db->sql_transaction('commit');
			//$this->cache->destroy('sql', TEAMPAGE_TABLE);
			return true;
		}
	}
}