<?php

/**
 * @file ExtendedUserBlockPlugin.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ExtendedUserBlockPlugin
 * @ingroup plugins_blocks_user
 *
 * @brief Class for user block plugin
 */

// $Id$


import('lib.pkp.classes.plugins.BlockPlugin');

class ExtendedUserBlockPlugin extends BlockPlugin {
	function register($category, $path) {
		$success = parent::register($category, $path);
		if ($success) {
			AppLocale::requireComponents(array(LOCALE_COMPONENT_PKP_USER));
		}
		return $success;
	}

	/**
	 * Install default settings on system install.
	 * @return string
	 */
	function getInstallSitePluginSettingsFile() {
		return $this->getPluginPath() . '/settings.xml';
	}

	/**
	 * Install default settings on journal creation.
	 * @return string
	 */
	function getContextSpecificPluginSettingsFile() {
		return $this->getPluginPath() . '/settings.xml';
	}

	/**
	 * Get the display name of this plugin.
	 * @return String
	 */
	function getDisplayName() {
		return __('plugins.block.extendedUser.displayName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return __('plugins.block.extendedUser.description');
	}

	function getContents(&$templateMgr) {
		if (!defined('SESSION_DISABLE_INIT')) {
			$session =& Request::getSession();
			$templateMgr->assign_by_ref('userSession', $session);
			$templateMgr->assign('loggedInUsername', $session->getSessionVar('username'));
			$loginUrl = Request::url(null, 'login', 'signIn');
		        $templateMgr->assign('paperPackageUpPlugin',"/index.php/mr2/PaperPackageUpPlugin/view/");
			if (Config::getVar('security', 'force_login_ssl')) {
				$loginUrl = String::regexp_replace('/^http:/', 'https:', $loginUrl);
			}
			$templateMgr->assign('userBlockLoginUrl', $loginUrl);
		}
		return parent::getContents($templateMgr);
	}
}

?>
