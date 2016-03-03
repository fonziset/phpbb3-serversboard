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
	'TOKEN07_SERVERSBOARD_ACP_EDIT'			=> 'Edit Servers',
	'TOKEN07_SERVERSBOARD_ACP_DISPINDEX'	=> 'Display servers board on index',
	'TOKEN07_SERVERSBOARD_ACP_UPDATEFREQ'	=> 'Server data update frequency (seconds)',
	'TOKEN07_SERVERSBOARD_ACP_SERVERIP'		=> 'Server IP (in IP:Port format)',
	'TOKEN07_SERVERSBOARD_ACP_HOSTNAME'		=> 'Hostname (updated on data refresh)',
	'TOKEN07_SERVERSBOARD_ACP_SERVERTYPE'	=> 'Server Type',
	)
);
