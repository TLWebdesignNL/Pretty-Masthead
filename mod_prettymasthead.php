<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_prettymasthead
 *
 * @copyright   Copyright (C) 2005 - 2020 TLWebdesign. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use TlwebNamespace\Module\Prettymasthead\Site\Helper\PrettymastheadHelper;

$itemId    = PrettymastheadHelper::getItemId();
$mastheads = $params->get('mastheads');
$masthead  = PrettymastheadHelper::getMasthead($mastheads);

require ModuleHelper::getLayoutPath('mod_prettymasthead', $params->get('layout', 'default'));
