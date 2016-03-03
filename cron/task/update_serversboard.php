<?php
namespace token07\serversboard\cron\task;

require(dirname(__FILE__) . "/../../vendor/autoload.php");

use \xPaw\SourceQuery\SourceQuery;


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
		global $phpbb_log, $table_prefix;
		//print_r($phpbb_log);
		$result = $this->db->sql_query("SELECT * FROM {$table_prefix}serversboard");
		while ($row = $this->db->sql_fetchrow($result))
		{
			$sData = explode(":", $row['server_ip']);
			//var_dump($row);
			//var_dump($sData);
			$serverInfo = new SourceQuery();
			try
			{
				$serverInfo->Connect( $sData[0], $sData[1], 1, SourceQuery::SOURCE );
				$info = $serverInfo->GetInfo();
				$stmt = "UPDATE {$table_prefix}serversboard SET server_status = 1, server_hostname = '%s', server_map = '%s', server_players = '%d / %d' WHERE server_id = %d";
				$this->db->sql_query(sprintf($stmt,$this->db->sql_escape($info['HostName']), $this->db->sql_escape($info['Map']), $info['Players'], $info['MaxPlayers'], $row['server_id']));
				$players = $serverInfo->GetPlayers();
				if (is_array($players))
				{
					$players = $this->db->sql_escape(json_encode($players));
					$this->db->sql_query("UPDATE {$table_prefix}serversboard SET server_playerlist = '$players' WHERE server_id = {$row['server_id']}");
				}
			}
			catch (SocketException $e)
			{
				$stmt = "UPDATE {$table_prefix}serversboard SET server_status = 0 WHERE server_id = %d";
				$this->db->sql_query(sprintf($stmt,$row['server_id']));
				return false;
			}
		}
		$this->config->set('serversboard_update_last_run', time());
	}
}