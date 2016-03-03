<?php

/**
*
* @package phpBB Extension - Servers Board
* @copyright (c) 2016 Token07
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace token07\serversboard\acp;

class serversboard_module
{
	var $u_action;
		
	function main($id, $mode)
	{
		global $config, $request, $template, $user;
		//$user->add_lang('acp/common');
		$user->add_lang_ext('token07/serversboard', 'acp/serversboard_acp');
		add_form_key('token07/serversboard');
		switch ($mode)
		{
			case 'servers':
					trigger_error("Not done yet" . adm_back_link($this->u_action), E_USER_WARNING);
			break;
			case 'settings':
				if ($request->is_set_post('submit'))
				{
					if (!check_form_key('token07/serversboard'))
					{
						trigger_error('FORM_INVALID');
					}
					$config->set('serversboard_enable', $request->variable('token07_serversboard_enable', 1));
					$config->set('serversboard_update_time', $request->variable('token07_serversboard_interval', 1));
					
					trigger_error($user->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
				}
				$this->tpl_name = 'serversboard_settings';
				$this->page_title = $user->lang('TOKEN07_SERVERSBOARD_ACP_SERVERSBOARD');
				$template->assign_vars(array(
					'TOKEN07_SERVERSBOARD_ENABLE'	=> $config['serversboard_enable'],
					'TOKEN07_SERVERSBOARD_INTERVAL'	=> $config['serversboard_update_time'],
				));
			break;
			case 'add':
				$this->tpl_name = 'serversboard_add';
				$this->page_title = $user->lang('TOKEN07_SERVERSBOARD_ACP_EDIT');
				if ($request->is_set_post('submit'))
				{
					if (!check_form_key('token07/serversboard'))
					{
						trigger_error('FORM_INVALID');
					}
					trigger_error("Not done yet" . adm_back_link($this->u_action), E_USER_WARNING);
				}
			break;
		}
	}
}