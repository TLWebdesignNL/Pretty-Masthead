<?php

/**
 * @package     TLWebdesign.Module
 * @subpackage  mod_prettymasthead
 *
 * @copyright   Copyright (C) 2005 - 2020 TLWebdesign. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use TlwebNamespace\Module\Prettymasthead\Site\Helper\PrettymastheadHelper;

$mastheads = $params->get('mastheads');
$descLength = $params->get('desclength');
$mainDivClass = $params->get('maindivclass');
$minHeight = $params->get('minheight');
$maxHeight = $params->get('maxheight');
$descSource = $params->get('descsource');
$imagePriority = $params->get('imagepriority');
$defaultmasthead['image'] = $params->get('defaultmastheadimage');
$defaultmasthead['title'] = $params->get('defaultmastheadtitle');
$defaultmasthead['description'] = $params->get('defaultmastheaddescription');
$defaultmasthead['position'] = $params->get('defaultmastheadposition');
$defaultmasthead['titletag'] = $params->get('defaultmastheadtitletag');
$defaultmasthead['titleclass'] = $params->get('defaultmastheadtitleclass');
$defaultmasthead['descriptionclass'] = $params->get('defaultmastheaddescriptionclass');
$defaultmasthead['titlevisibility'] = $params->get('defaulttitlevisibility');
$defaultmasthead['descriptionvisibility'] = $params->get('defaultdescriptionvisibility');
$defaultmasthead['buttontext'] = $params->get('defaultbuttontext');
$defaultmasthead['buttonurl'] = $params->get('defaultbuttonurl');
$defaultmasthead['buttonclass'] = $params->get('defaultbuttonclass');

$masthead  = PrettymastheadHelper::getMasthead($mastheads, $defaultmasthead, $descLength, $descSource, $imagePriority);
require ModuleHelper::getLayoutPath('mod_prettymasthead', $params->get('layout', 'default'));
