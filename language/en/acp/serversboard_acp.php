<?php

/**
*
* @package phpBB Extension - Servers Board
* @copyright (c) 2016 Token07
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'TOKEN07_SERVERSBOARD_ACP_SERVERSBOARD'	=> 'Servers Board',
	'TOKEN07_SERVERSBOARD_ACP_ADD'			=> 'Add Servers',
	'TOKEN07_SERVERSBOARD_ACP_DISPINDEX'	=> 'Display servers board on index',
	'TOKEN07_SERVERSBOARD_ACP_UPDATEFREQ'	=> 'Server data update frequency (seconds)',
	'TOKEN07_SERVERSBOARD_ACP_SERVERIP'		=> 'Server IP',
	'TOKEN07_SERVERSBOARD_ACP_SERVERPORT'	=> 'Server Port',
	'TOKEN07_SERVERSBOARD_ACP_HOSTNAME'		=> 'Hostname (updated on data refresh)',
	'TOKEN07_SERVERSBOARD_ACP_HOSTNAME2'	=> 'Host Name',
	'TOKEN07_SERVERSBOARD_ACP_SERVERTYPE'	=> 'Server Type',
	'TOKEN07_SERVERSBOARD_ACP_INVALIDIP'	=> 'The IP address entered was invalid.',
	'TOKEN07_SERVERSBOARD_ACP_INVALIDPORT'	=> 'The port number entered was invalid.',
	'TOKEN07_SERVERSBOARD_ACP_CONFIRMDEL'	=> 'Are you sure you want to delete this server?',
	'TOKEN07_SERVERSBOARD_ACP_DELETED'		=> 'Server deleted.',
	'TOKEN07_SERVERSBOARD_ACP_ADDED'		=> 'Server Added.',
	'TOKEN07_SERVERSBOARD_ACP_NAVBAR_LINK'	=> 'Add link to servers board in navigation bar',
	'TOKEN07_SERVERSBOARD_ACP_NO_SERVER'	=> 'The specified server does not exist.',
	)
);
