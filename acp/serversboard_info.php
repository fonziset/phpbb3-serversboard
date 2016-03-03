<?php

namespace token07\serversboard\acp;

class serversboard_info
{
	function module()
	{
		return array(
			'filename'	=> '\token07\serversboard\acp\serversboard_module',
			'title'		=> 'Servers',
			'modes'		=> array(
				'servers'	=> array(
					'title' => 'TOKEN07_SERVERSBOARD_ACP_MANAGE',
					'auth'	=> 'acl_a_',
					'cat'	=> 'TOKEN07_SERVERSBOARD_ACP_SERVERSBOARD',
				),
				'settings'	=> array(
					'title' => 'GENERAL_SETTINGS',
					'auth'	=> 'acl_a_',
					'cat'	=> 'TOKEN07_SERVERSBOARD_ACP_SERVERSBOARD',
				),
				'add'	=> array(
					'title' => 'TOKEN07_SERVERSBOARD_ACP_ADD',
					'auth'	=> 'acl_a_',
					'cat'	=> 'TOKEN07_SERVERSBOARD_ACP_SERVERSBOARD',
				),
			),
		);
	}
}