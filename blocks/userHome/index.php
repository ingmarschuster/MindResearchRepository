<?php

/**
 * @defgroup plugins_blocks_userHome
 */
 
/**
 * @file plugins/blocks/userHome/index.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_blocks_userHome
 * @brief Wrapper for "user Home" block plugin.
 *
 */

// $Id$


require_once('UserHomeBlockPlugin.inc.php');

return new UserHomeBlockPlugin();

?> 
