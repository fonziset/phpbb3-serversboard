<?php

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
			'FORUM_NAME'	=> "Servers Board",
			'U_VIEW_FORUM'	=> $this->helper->route('token07_serversboard_controller'),
		));
	}
	private function setTemplateVars($row)
	{
		$tmp = array('STATUS' => $row['server_status'], 'HOSTNAME' => $row['server_hostname'], 'IP' => $row['server_ip'], 'PLAYERS' => $row['server_players'], 'MAP' => $row['server_map'], 'OPTIONS' => '');
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
		$this->setBreadcrumbs();
		$result = $this->db->sql_query("SELECT `server_id`, `server_order`, `server_ip`, `server_status`, `server_hostname`, `server_map`, `server_players` FROM phpbb_serversboard");
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->setTemplateVars($row);
		}
		return $this->helper->render('serversboard_body.html', 'Servers Board');
	}
	
	public function viewDetails($id)
	{
		$this->setBreadcrumbs();
		$result = $this->db->sql_query("SELECT * FROM phpbb_serversboard WHERE server_id = $id");
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
			return $this->helper->render('serversboard_viewserver.html', 'Servers Board');
		}
	}
}
