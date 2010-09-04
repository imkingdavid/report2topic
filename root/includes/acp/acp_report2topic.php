<?php
/**
 *
 * @package report2topic++
 * @copyright (c) 2010 report2topic++ http://github.com/report2topic
 * @author Erik Frèrejean ( N/A ) http://www.erikfrerejean.nl
 * @author David King (imkingdavid) http://www.phpbbdevelopers.net
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 */

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

class acp_report2topic
{
	public $u_action;

	/**
	 * @var report2topic_core The report2topic_core object
	 */
	private $core = null;

	/**
	 * @var String The form key used for this page
	 */
	private $form_key = '';

	/**
	 * @var Boolean A form submitted or not
	 */
	private $submit = false;

	/**
	 * Load the module
	 * @param	String $id		Module ID
	 * @param	String $mode	Module mode
	 * @return	void
	 */
	public function main($id, $mode)
	{
		// Collect some common vars
		$this->submit = (isset($_POST['submit'])) ? true : false;

		// Set the core
		$this->core = report2topic_core::getInstance();

		// Do the right page
		if (method_exists($this, '_' . $mode))
		{
			call_user_func(array($this, '_' . $mode));
		}

		add_form_key($this->form_key);
	}

	/**
	 * Main report2topic++ configuration
	 * @todo	Destination forum should be made more flexible. A forum should
	 * 			be able to define which forum shall be used for the reports. This
	 * 			is only for development atm.
	 * @return void
	 */
	private function _config()
	{
		// Setup the page
		$this->tpl_name		= 'mods/report2topic++/report2topic++_config';
		$this->page_title	= 'ACP_REPORT2TOPIC_CONFIG';
		$this->form_key		= 'report2topic++_config';

		// Submit
		if ($this->submit)
		{
			// Get teh vars
			$df	= request_var('report2topic_post_forum', 0);
			$pm_title		= utf8_normalize_nfc(request_var('report2topic_pm_title', '', true));
			$post_title		= utf8_normalize_nfc(request_var('report2topic_post_title', '', true));
			$pm_template	= utf8_normalize_nfc(request_var('report2topic_pm_template', '', true));
			$post_template	= utf8_normalize_nfc(request_var('report2topic_post_template', '', true));

			// Get the dest forum
			$sql = 'SELECT forum_id
				FROM ' . FORUMS_TABLE . '
				WHERE forum_id = ' . (int) $df;
			$result	= $this->core->db->sql_query($sql);
			$fid	= $this->core->db->sql_fetchfield('forum_id', false, $result);
			$this->core->db->sql_freeresult($result);

			// The forum exists save
			if ($fid !== false)
			{
				set_config('r2t_dest_forum', $fid);
			}

			// Save topic title
			set_config('r2t_pm_title', $pm_title);
			set_config('r2t_post_title', $post_title);

			// Save the templates
			set_config('r2t_pm_template', $pm_template);
			set_config('r2t_post_template', $post_template);

			trigger_error($this->core->user->lang('ACP_REPORT2TOPIC_CONFIG_SUCCESS') . adm_back_link($this->u_action));
		}

		// Output the page
		$this->core->template->assign_vars(array(
			'S_DEST_FORUM'		=> (isset($this->core->config['r2t_dest_forum'])) ? $this->core->config['r2t_dest_forum'] : '',
			'S_PM_TEMPLATE'		=> (isset($this->core->config['r2t_pm_template'])) ? $this->core->config['r2t_pm_template'] : '',
			'S_PM_TITLE'		=> (isset($this->core->config['r2t_pm_title'])) ? $this->core->config['r2t_pm_title'] : '',
			'S_POST_TEMPLATE'	=> (isset($this->core->config['r2t_post_template'])) ? $this->core->config['r2t_post_template'] : '',
			'S_POST_TITLE'		=> (isset($this->core->config['r2t_post_title'])) ? $this->core->config['r2t_post_title'] : '',

			'U_ACTION'	=> $this->u_action,
		));

		// Add tokens
		foreach ($this->core->user->lang['r2t_tokens'] as $token => $explain)
		{
			$this->core->template->assign_block_vars('token', array(
				'TOKEN'		=> '{' . $token . '}',
				'EXPLAIN'	=> $explain,
			));
		}
	}
}