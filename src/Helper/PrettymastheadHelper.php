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

	public static function getMasthead($mastheads)
	{
		$itemId         = self::getItemId();
		$mastheadsArray = array();
		if (isset($mastheads) && is_object($mastheads))
		{
			foreach ($mastheads as $m)
			{
				if ($itemId && $itemId == $m->mastheadmenuitem)
				{
					$mastheadsArray[$m->mastheadmenuitem]['image']       = $m->mastheadimage;
					$mastheadsArray[$m->mastheadmenuitem]['title']       = $m->mastheadtitle;
					$mastheadsArray[$m->mastheadmenuitem]['description'] = $m->mastheaddescription;
					$mastheadsArray[$m->mastheadmenuitem]['position']    = $m->mastheadposition;
				}
			}
		}
		return $mastheadsArray;
	}
}
