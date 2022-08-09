<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_prettymasthead
 *
 * @copyright   Copyright (C) 2022 TLWebdesign. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace TlwebNamespace\Module\Prettymasthead\Site\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

\defined('_JEXEC') or die;

/**
 * Helper for mod_prettymasthead
 *
 * @since  V1.0.0
 */
class PrettymastheadHelper
{
	/**
	 * Retrieve ItemId
	 *
	 * @return  string
	 */

	public static function getItemId()
	{
		$input  = Factory::getApplication()->input;
		$itemId = $input->get('Itemid', '', 'INT');

		return $itemId;
	}

	public static function getMasthead($mastheads, $defaultmasthead)
	{
		$itemId         = self::getItemId();
		$mastheadsArray = array();
		$setDefaultMasthead = false;

		if (isset($mastheads) && is_object($mastheads))
		{
			foreach ($mastheads as $m)
			{
				if ($itemId && $itemId == $m->mastheadmenuitem)
				{
					$mastheadsArray[$m->mastheadmenuitem]['image']            = (isset($m->mastheadimage)) ? $m->mastheadimage : '';
					$mastheadsArray[$m->mastheadmenuitem]['title']            = (isset($m->mastheadtitle)) ? $m->mastheadtitle : '';
					$mastheadsArray[$m->mastheadmenuitem]['description']      = (isset($m->mastheaddescription)) ? $m->mastheaddescription : '';
					$mastheadsArray[$m->mastheadmenuitem]['position']         = (isset($m->mastheadposition)) ? $m->mastheadposition : '';
					$mastheadsArray[$m->mastheadmenuitem]['titletag']         = (isset($m->mastheadtitletag)) ? $m->mastheadtitletag : '';
					$mastheadsArray[$m->mastheadmenuitem]['titleclass']       = (isset($m->mastheadtitleclass)) ? $m->mastheadtitleclass : '';
					$mastheadsArray[$m->mastheadmenuitem]['descriptionclass'] = (isset($m->mastheaddescriptionclass)) ? $m->mastheaddescriptionclass : '';
				}
				else
				{
					$setDefaultMasthead = true;
				}
			}
		}
		else
		{
			$setDefaultMasthead = true;
		}

		if ($setDefaultMasthead)
		{
			$mastheadsArray[1]['image']            = (isset($defaultmasthead['image'])) ? $defaultmasthead['image'] : '';
			$mastheadsArray[1]['title']            = (isset($defaultmasthead['title'])) ? $defaultmasthead['title'] : '';
			$mastheadsArray[1]['description']      = (isset($defaultmasthead['description'])) ? $defaultmasthead['description'] : '';
			$mastheadsArray[1]['position']         = (isset($defaultmasthead['position'])) ? $defaultmasthead['position'] : '';
			$mastheadsArray[1]['titletag']         = (isset($defaultmasthead['titletag'])) ? $defaultmasthead['titletag'] : '';
			$mastheadsArray[1]['titleclass']       = (isset($defaultmasthead['titleclass'])) ? $defaultmasthead['titleclass'] : '';
			$mastheadsArray[1]['descriptionclass'] = (isset($defaultmasthead['descriptionclass'])) ? $defaultmasthead['descriptionclass'] : '';
		}
		foreach ($mastheadsArray as $key => $mh)
		{
			if ($mh['image'] != "")
			{
				$mastheadsArray[$key]['image'] = HTMLHelper::_('cleanImageURL', $mh['image']);
				if ($mastheadsArray[$key]['image']->url != "")
				{
					if ($mastheadsArray[$key]['image']->attributes['width'] == 0 || $mastheadsArray[$key]['image']->attributes['height'] == 0)
					{
						list($width, $height) = getimagesize($mastheadsArray[$key]['image']->url);
						$mastheadsArray[$key]['image']->attributes['width'] = $width;
						$mastheadsArray[$key]['image']->attributes['height'] = $height;
					}
					$mastheadsArray[$key]['image']->url = Uri::root() . $mastheadsArray[$key]['image']->url;
				}

			}
		}
		return $mastheadsArray;
	}
}
