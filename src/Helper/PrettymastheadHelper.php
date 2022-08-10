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
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use mysql_xdevapi\DocResult;

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

		$article = self::getArticle();
		//echo "<pre>";var_dump($article);
		return $itemId;
	}

	public static function getArticle()
	{
		$app = Factory::getApplication();
		if (
			$app->input->get('option') === 'com_content'
			&& $app->input->get('view') === 'article'
		) {
			// Save to here all the datas you need return
			$items = new \stdClass;

			// Get the article ID
			$article_id = $app->input->getInt('id');

			// Set application parameters in model
			$appParams = $app->getParams();

			// The article model
			$model = $app->bootComponent('com_content')->getMVCFactory()->createModel('Article', 'Site', ['ignore_request' => true]);

			// Please, use any other filter as you need
			$model->setState('params', $appParams);
			$model->setState('filter.published', 1);
			$model->setState('article.id', (int) $article_id);

			$article = $model->getItem();

			// Get the custom fields
			$fields = [];
			$jcfields = FieldsHelper::getFields('com_content.article', $article);
			if (!empty($jcfields)) {
				foreach ($jcfields as $field) {
					$fields[$field->name] = $field;
				}
			}

			$items->fields = $fields;

			// rest of the code... like get the title, introtext etc
			//echo "<pre>";var_dump($article);

			return $items;
		}
	}

	public static function getMasthead($mastheads, $defaultmasthead)
	{
		$itemId         = self::getItemId();
		$mastheadArray = array();
		$setDefaultMasthead = false;

		// SET DEFAULT MASYHEAD (WILL BE OVERWRITTEN IF THERE IS A SPECIFIC MASTHEAD MATCHING THE ITEMID
		$mastheadArray['image']            = (isset($defaultmasthead['image'])) ? $defaultmasthead['image'] : '';
		$mastheadArray['title']            = (isset($defaultmasthead['title'])) ? $defaultmasthead['title'] : '';
		$mastheadArray['description']      = (isset($defaultmasthead['description'])) ? $defaultmasthead['description'] : '';
		$mastheadArray['position']         = (isset($defaultmasthead['position'])) ? $defaultmasthead['position'] : '';
		$mastheadArray['titletag']         = (isset($defaultmasthead['titletag'])) ? $defaultmasthead['titletag'] : '';
		$mastheadArray['titleclass']       = (isset($defaultmasthead['titleclass'])) ? $defaultmasthead['titleclass'] : '';
		$mastheadArray['descriptionclass'] = (isset($defaultmasthead['descriptionclass'])) ? $defaultmasthead['descriptionclass'] : '';

		// LOOP THROUGH MENU ITEM SPECIFIC MASTHEADS
		if (isset($mastheads) && is_object($mastheads))
		{
			foreach ($mastheads as $m)
			{
				if ($itemId && $itemId == $m->mastheadmenuitem)
				{
					$mastheadArray['image']            = (isset($m->mastheadimage)) ? $m->mastheadimage : '';
					$mastheadArray['title']            = (isset($m->mastheadtitle)) ? $m->mastheadtitle : '';
					$mastheadArray['description']      = (isset($m->mastheaddescription)) ? $m->mastheaddescription : '';
					$mastheadArray['position']         = (isset($m->mastheadposition)) ? $m->mastheadposition : '';
					$mastheadArray['titletag']         = (isset($m->mastheadtitletag)) ? $m->mastheadtitletag : '';
					$mastheadArray['titleclass']       = (isset($m->mastheadtitleclass)) ? $m->mastheadtitleclass : '';
					$mastheadArray['descriptionclass'] = (isset($m->mastheaddescriptionclass)) ? $m->mastheaddescriptionclass : '';
				}
			}
		}
		// FORMAT MASTHEAD IMAGE
		if ($mastheadArray['image'] != "")
		{
			$mastheadArray['image'] = HTMLHelper::_('cleanImageURL', $mastheadArray['image']);
			if ($mastheadArray['image']->url != "")
			{
				if ($mastheadArray['image']->attributes['width'] == 0 || $mastheadArray['image']->attributes['height'] == 0)
				{
					list($width, $height) = getimagesize($mastheadArray['image']->url);
					$mastheadArray['image']->attributes['width'] = $width;
					$mastheadArray['image']->attributes['height'] = $height;
				}
				$mastheadArray['image']->url = Uri::root() . $mastheadArray['image']->url;
			}
		}
		return $mastheadArray;
	}
}
