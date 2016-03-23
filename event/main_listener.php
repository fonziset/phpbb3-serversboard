<?php

/**
*
* @package phpBB Extension - Servers Board
* @copyright (c) 2016 Token07
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace token07\serversboard\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class main_listener implements EventSubscriberInterface
{
	protected $helper;
	protected $template;
	protected $config;
	protected $db;
	
	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'	=> 'load_language_on_setup',
			'core.index_modify_page_title'	=> 'load_serversboard',
		);
	}

	public function __construct(\phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\config\config $config, \phpbb\db\driver\factory $db)
	{
		$this->helper = $helper;
		$this->template = $template;
		$this->config = $config;
		$this->db = $db;
	}

	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'token07/serversboard',
			'lang_set' => 'common',
		);
		$event['lang_set_ext'] = $lang_set_ext;
		$this->template->assign_var('TOKEN07_SERVERSBOARD_NAVBAR_LINK_ENABLE', $this->config['serversboard_navbar_link_enable']);
		$this->template->assign_var('TOKEN07_SERVERSBOARD_URL', $this->helper->route("token07_serversboard_controller"));
	}
	
	public function load_serversboard($page_title)
	{
		global $table_prefix;
		if ($this->config['serversboard_enable'])
		{
			$this->template->assign_var('TOKEN07_SERVERSBOARD_ENABLE', true);
			$result = $this->db->sql_query("SELECT * FROM {$table_prefix}serversboard ORDER BY server_order");
			while ($row = $this->db->sql_fetchrow($result))
			{
				/*
				$tmp = array('STATUS' => $row['server_status'], 'HOSTNAME' => $row['server_hostname'], 'IP' => $row['server_ip'], 'PLAYERS' => $row['server_players'], 'MAP' => $row['server_map'], 'OPTIONS' => '');
				$tmp['LINK'] = $this->helper->route("token07_serversboard_viewdetails", array('id' => $row['server_id']));
				$this->template->assign_block_vars('serverlist', $tmp);*/
				$this->setTemplateVars($row);
			}
		}
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
}
