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

//$test  = PrettymastheadHelper::getText();

$block            = $params->get('block');
$customouterclass = $params->get('customouterclass');
$before           = $params->get('before');
$buttons          = $params->get('buttons');
$after            = $params->get('after');

require ModuleHelper::getLayoutPath('mod_prettymasthead', $params->get('layout', 'default'));
