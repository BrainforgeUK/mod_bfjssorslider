<?php
/**
 * @version		
 * @package		Joomla.Site
 * @subpackage	mod_bfjssorslider
 * @copyright	Copyright (C) 2015 Jonathan Brain. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

echo '<div class="mod_bfjssorslider">';
$modulelayout = $params->get('modulelayout', 'default');
require JModuleHelper::getLayoutPath('mod_bfjssorslider', $modulelayout . '.php');
echo '</div>';
