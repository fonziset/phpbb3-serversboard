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
			if ($row['server_query_port'] != NULL)
			{
				$server['options'] = array('query_port' => $row['server_query_port']);
			}
			$servers[] = $server;
			/*$serverInfo = new SourceQuery();
			
			try
			{
				$serverInfo->Connect($sData[0], $sData[1], 1, SourceQuery::SOURCE);
				$info = $serverInfo->GetInfo();
				if ($info === false)
				{
					// More than likely it's down. Throw a SocketException so it's marked as offline
					throw new \xPaw\SourceQuery\Exception\SocketException("Could not fetch server data");
				}
				$stmt = "UPDATE {$table_prefix}serversboard SET server_status = 1, server_hostname = '%s', server_map = '%s', server_players = '%d / %d', server_lastupdate = %d WHERE server_id = %d";
				$this->db->sql_query(sprintf($stmt,$this->db->sql_escape($info['HostName']), $this->db->sql_escape($info['Map']), $info['Players'], $info['MaxPlayers'], time(), $row['server_id']));
				$players = $serverInfo->GetPlayers();
				if (is_array($players))
				{
					$players = $this->db->sql_escape(json_encode($players));
					$this->db->sql_query("UPDATE {$table_prefix}serversboard SET server_playerlist = '$players' WHERE server_id = {$row['server_id']}");
				}
			}
			catch (\xPaw\SourceQuery\Exception\SocketException $e)
			{
				$stmt = "UPDATE {$table_prefix}serversboard SET server_status = 0 WHERE server_id = %d";
				$this->db->sql_query(sprintf($stmt,$row['server_id']));
			}
			catch (\xPaw\SourceQuery\Exception\InvalidPacketException $e)
			{
				// Try it one more time
				try
				{
					$players = $serverInfo->GetPlayers();
					if (is_array($players))
					{
						$players = $this->db->sql_escape(json_encode($players));
						$this->db->sql_query("UPDATE {$table_prefix}serversboard SET server_playerlist = '$players' WHERE server_id = {$row['server_id']}");
					}
				}
				catch (\Exception $e)
				{
					// Clear the player list since it couldn't be updated.
					$this->db->sql_query("UPDATE {$table_prefix}serversboard SET server_playerlist = '[]' WHERE server_id = {$row['server_id']}");
				}
			}
			catch (\Exception $e)
			{
				global $user;
				// Just report it in the error log for now.
				$message = sprintf($user->lang['TRACKED_PHP_ERROR'], $e->getMessage() . $e->getTraceAsString());
				$phpbb_log->add('critical', 0, '', 'LOG_GENERAL_ERROR', false, array($message));
			} */
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
				'server_map'		=> $this->db->sql_escape($result['gq_map']),
				'server_lastupdate'	=> time(),
			);
			if (!$offline || !empty($result['gq_hostname']))
			{
				$newDetails['server_hostname'] = $this->db->sql_escape($result['gq_hostname']);
			}
			$players = array();
			foreach ($result['players'] AS $player)
			{
				$players[] = array(
					'Name'	=> $this->db->sql_escape(utf8_encode($player['name'])),
					'TimeF'	=> gmdate(($player['time'] > 3600 ? "H:i:s" : "i:s" ), $player['time']),
				);
			}
			$newDetails['server_playerlist'] = json_encode($players);
			
			$sql = 'UPDATE ' . $table_prefix . 'serversboard' . ' SET ' . $this->db->sql_build_array("UPDATE", $newDetails) . '
				WHERE server_id = ' . (int) $server;
			$this->db->sql_query($sql);
			var_dump($result);
		}
		$this->config->set('serversboard_update_last_run', time());
	}
}