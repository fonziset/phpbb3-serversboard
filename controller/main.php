<?php

/**
*
* @package phpBB Extension - Servers Board
* @copyright (c) 2016 Token07
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace token07\serversboard\controller;

class main
{
	protected $config;
	protected $helper;
	protected $template;
	protected $user;
	protected $db;
	
	public function __construct(\phpbb\config\config $config, \phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\user $user, \phpbb\db\driver\factory $db)
	{
		$this->config = $config;
		$this->helper = $helper;
		$this->template = $template;
		$this->user = $user;
		$this->db = $db;
	}
	private function setBreadcrumbs()
	{
		$this->template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> $this->user->lang('TOKEN07_SERVERSBOARD_SERVERSBOARD'),
			'U_VIEW_FORUM'	=> $this->helper->route('token07_serversboard_controller'),
		));
	}
	private function setTemplateVars($row)
	{
		$tmp = array(
			'STATUS'	=> $row['server_status'],
			'HOSTNAME'	=> $row['server_hostname'],
			'IP'		=> $row['server_ip'],
			'PLAYERS'	=> $row['server_players'],
			'MAP'		=> $row['server_map'],
			'JOINLINK'	=> $row['server_join_link'],
		);
		$proto = substr($row['server_join_link'], 0, strpos($row['server_join_link'], ':'));
		switch ($proto)
		{
			case 'steam':
				$tmp['ICON'] = 'steam';
				$tmp['GAMETRACKER'] = true;
			break;
			case 'teamspeak':
			case 'ts3server':
				$tmp['ICON'] = 'teamspeak';
			break;
			case 'minecraft':
				$tmp['ICON'] = 'minecraft';
				$tmp['GAMETRACKER'] = true;
			break;
		}
		if (!$row['server_status'])
		{
			$row['server_players'] = '0 / 0';
			$row['server_playerlist'] = '[]';
		}
		$this->template->assign_var('SERVERSBOARD_SERVER_STATUS', $row['server_status']);
		$tmp['LINK'] = $this->helper->route("token07_serversboard_viewdetails", array('id' => $row['server_id']));
		$this->template->assign_block_vars('serverlist', $tmp);
	}
	public function handle()
	{
		global $table_prefix;
		$this->setBreadcrumbs();
		$result = $this->db->sql_query("SELECT server_id, server_order, server_ip, server_status, server_hostname, server_map, server_players, server_join_link FROM {$table_prefix}serversboard ORDER BY server_order");
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->setTemplateVars($row);
		}
		$this->template->assign_var('TOKEN07_SERVERSBOARD_ENABLE', true);
		$this->template->assign_var('SERVERSBOARD_LAST_UPDATE', sprintf($this->user->lang('TOKEN07_SERVERSBOARD_LASTUPDATED'), $this->user->format_date($this->config['serversboard_update_last_run'])));
		return $this->helper->render('serversboard_body.html', $this->user->lang('TOKEN07_SERVERSBOARD_SERVERSBOARD'));
	}
	
	public function viewDetails($id)
	{
		global $request, $table_prefix;
		
		if ($request->is_ajax())
		{
			$json_response = new \phpbb\json_response;
			$result = $this->db->sql_query("SELECT server_hostname, server_playerlist FROM {$table_prefix}serversboard WHERE server_id = $id");
			if ($row = $this->db->sql_fetchrow($result))
			{
				$playerList = $row['server_playerlist'];
				$html = "<ul style=\"list-style: none;\">";
				foreach (json_decode($playerList) AS $player)
				{
					$html .= "<li>" . htmlentities($player->Name) . "</li>";
				}
				$html .= "</ul>";
			}
			$json_response->send(array('MESSAGE_TITLE' => sprintf($this->user->lang('TOKEN07_SERVERSBOARD_PLAYERLIST', htmlentities($row['server_hostname']))), 'MESSAGE_TEXT' => $html));
			return;
		}
		$this->setBreadcrumbs();
		$this->template->assign_var('TOKEN07_SERVERSBOARD_ENABLE', true);
		$result = $this->db->sql_query("SELECT * FROM {$table_prefix}serversboard WHERE server_id = $id");
		if ($row = $this->db->sql_fetchrow($result))
		{
			$playerList = $row['server_playerlist'];
			unset($row['server_playerlist']);
			$this->setTemplateVars(array_map('htmlentities',$row));
			$this->template->assign_var('SERVER_EMPTY', $playerList == "[]");
			foreach (json_decode($playerList) AS $player)
			{
				$pData = array_map('htmlentities', array('NAME' => $player->Name, 'TIME' => $player->TimeF));
				$this->template->assign_block_vars('players', $pData);
			}
			$this->template->assign_var('SERVERSBOARD_SERVER_LAST_UPDATE', $this->user->lang('TOKEN07_SERVERSBOARD_LASTUPDATED', $this->user->format_date($row['server_lastupdate'])) );
			return $this->helper->render('serversboard_viewserver.html', $this->user->lang('TOKEN07_SERVERSBOARD_SERVERSBOARD'));
		}
		throw new \phpbb\exception\http_exception(404, 'TOKEN07_SERVERSBOARD_INVALID');
	}
}
