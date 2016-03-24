<?php

/**
*
* @package phpBB Extension - Servers Board
* @copyright (c) 2016 Token07
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace token07\serversboard\cron\task;

require(dirname(__FILE__) . "/../../vendor/autoload.php");

class update_serversboard extends \phpbb\cron\task\base
{
	protected $config;
	protected $db;
	
	/**
	* Constructor.
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\factory $db)
	{
		$this->config = $config;
		$this->db = $db;
	}
	public function is_runnable()
	{
		return true;
		return (bool) $this->config['serversboard_enable'];
	}
	public function should_run()
	{
		return $this->config['serversboard_update_last_run'] < time() - $this->config['serversboard_update_time'];
	}
	public function run()
	{
		global $phpbb_log, $table_prefix, $phpbb_log;

		$GameQ = new \GameQ\GameQ();
		$servers = array();
		$result = $this->db->sql_query("SELECT * FROM {$table_prefix}serversboard");
		
		while ($row = $this->db->sql_fetchrow($result))
		{
			$sData = explode(":", $row['server_ip']);
			$server = array(
				'type'	=> $row['server_type'],
				'host'	=> $row['server_ip'],
				'id'	=> $row['server_id'],
			);
			if ($server['type'] == 'teamspeak3' && empty($row['server_query_port']))
			{
				$row['server_query_port'] = 10011;
			}
			elseif ($server['type'] == 'teamspeak2' && empty($row['server_query_port']))
			{
				$row['server_query_port'] = 51234;
			}
			if ($row['server_query_port'] != NULL)
			{
				$server['options'] = array('query_port' => $row['server_query_port']);
			}
			$servers[] = $server;
		}
		$GameQ->addServers($servers);
		$GameQ->setOption('timeout', 5);
		$results = $GameQ->process();
		//print_r($results);
		foreach ($results as $server => $result)
		{
			$offline = (!empty($result['gq_online'])) ? $result['gq_online'] : 0;
			$newDetails = array(
				'server_status'		=> $offline,
				'server_players'	=> sprintf('%d / %d', $result['gq_numplayers'], $result['gq_maxplayers']),
				'server_map'		=> (isset($result['map']) ? $this->db->sql_escape($result['map']) : '',
				'server_lastupdate'	=> time(),
				'server_join_link'	=> $this->db->sql_escape($result['gq_joinlink']),
			);
			if (!$offline || !empty($result['gq_hostname']))
			{
				$newDetails['server_hostname'] = $this->db->sql_escape($result['gq_hostname']);
			}
			$players = array();
			foreach ($result['players'] AS $player)
			{
				if (empty($player['time']))
				{
					$player['time'] = 0;
				}
				$players[] = array(
					'Name'	=> $this->db->sql_escape(utf8_encode($player['gq_name'])),
					'TimeF'	=> gmdate(($player['time'] > 3600 ? "H:i:s" : "i:s" ), $player['time']),
				);
			}
			$newDetails['server_playerlist'] = json_encode($players);
			
			$sql = 'UPDATE ' . $table_prefix . 'serversboard' . ' SET ' . $this->db->sql_build_array("UPDATE", $newDetails) . '
				WHERE server_id = ' . (int) $server;
			$this->db->sql_query($sql);
			//var_dump($result);
		}
		$this->config->set('serversboard_update_last_run', time());
	}
}