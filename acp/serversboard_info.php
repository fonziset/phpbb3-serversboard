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
					'title' => 'Servers',
					'auth'	=> 'acl_a_',
					'cat'	=> 'Servers Board',
				),
				'settings'	=> array(
					'title' => 'Settings',
					'auth'	=> 'acl_a_',
					'cat'	=> 'Servers Board',
				),
				'add'	=> array(
					'title' => 'Add',
					'auth'	=> 'acl_a_',
					'cat'	=> 'Servers Board',
				),
			),
		);
	}
}