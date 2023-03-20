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
use Joomla\CMS\HTML\Helpers\StringHelper;

\defined('_JEXEC') or die;

/**
 * Helper for mod_prettymasthead
 *
 * @since  V1.0.0
 */
class PrettymastheadHelper
{
    /**
     * Retrieve Masthead
     *
     * @param $mastheads
     * @param $defaultmasthead
     * @param $descLength
     * @param $descSource
     *
     * @return array
     */
    public static function getMasthead($mastheads, $defaultmasthead, $descLength, $descSource): array
    {
        $app   = Factory::getApplication();
        $input = $app->input;

        $itemId        = $input->get('Itemid', '', 'INT');
        $mastheadArray = array();

        // SET DEFAULT MASTHEAD (WILL BE OVERWRITTEN IF THERE IS A SPECIFIC MASTHEAD MATCHING THE ITEMID
        $mastheadArray['image']            = (isset($defaultmasthead['image'])) ? $defaultmasthead['image'] : '';
        $mastheadArray['title']            = (isset($defaultmasthead['title'])) ? $defaultmasthead['title'] : '';
        $mastheadArray['description']      = (isset($defaultmasthead['description'])) ? $defaultmasthead['description'] : '';
        $mastheadArray['position']         = (isset($defaultmasthead['position'])) ? $defaultmasthead['position'] : '';
        $mastheadArray['titletag']         = (isset($defaultmasthead['titletag'])) ? $defaultmasthead['titletag'] : '';
        $mastheadArray['titleclass']       = (isset($defaultmasthead['titleclass'])) ? $defaultmasthead['titleclass'] : '';
        $mastheadArray['descriptionclass'] = (isset($defaultmasthead['descriptionclass'])) ? $defaultmasthead['descriptionclass'] : '';
        $mastheadArray['titlevisibility'] = (isset($defaultmasthead['titlevisibility'])) ? $defaultmasthead['titlevisibility'] : '';
        $mastheadArray['descriptionvisibility'] = (isset($defaultmasthead['descriptionvisibility'])) ? $defaultmasthead['descriptionvisibility'] : '';

        // LOOP THROUGH MENU ITEM SPECIFIC MASTHEADS
        if (isset($mastheads) && is_object($mastheads)) {
            foreach ($mastheads as $m) {
                if (!empty($itemId) && $itemId == $m->mastheadmenuitem) {
                    $mastheadArray['image']            = (isset($m->mastheadimage)) ? $m->mastheadimage : '';
                    $mastheadArray['title']            = (isset($m->mastheadtitle)) ? $m->mastheadtitle : '';
                    $mastheadArray['description']      = (isset($m->mastheaddescription)) ? $m->mastheaddescription : '';
                    $mastheadArray['position']         = (isset($m->mastheadposition)) ? $m->mastheadposition : '';
                    $mastheadArray['titletag']         = (isset($m->mastheadtitletag)) ? $m->mastheadtitletag : '';
                    $mastheadArray['titleclass']       = (isset($m->mastheadtitleclass)) ? $m->mastheadtitleclass : '';
                    $mastheadArray['descriptionclass'] = (isset($m->mastheaddescriptionclass)) ? $m->mastheaddescriptionclass : '';
                    $mastheadArray['titlevisibility'] = (isset($m->mastheadtitlevisibility)) ? $m->mastheadtitlevisibility : '';
                    $mastheadArray['descriptionvisibility'] = (isset($m->mastheaddescriptionvisibility)) ? $m->mastheaddescriptionvisibility : '';

                    // GET ACTIVE MENU TO CHECK IF WE HAVE CATEGORY VIEW AND ONLY THEN TRY TO GET ARTICLE
                    $activeMenuQuery = $app->getMenu()->getActive()->query;

                    if ($activeMenuQuery['view'] == "category") {
                        // TRY TO GRAB ARTICLE
                        $article = self::getArticle($app, $input, $descSource);

                        if (isset($article->image) && !empty($article->image)) {
                            $mastheadArray['image'] = $article->image;
                        }

                        if (isset($article->title) && !empty($article->title)) {
                            $mastheadArray['title'] = $article->title;
                        }

                        if (isset($article->description) && !empty($article->description)) {
                            $mastheadArray['description'] = $article->description;
                        }
                    }
                }
            }
        }

        // FORMAT MASTHEAD IMAGE
        if ($mastheadArray['image'] != "") {
            $mastheadArray['image'] = HTMLHelper::_('cleanImageURL', $mastheadArray['image']);

            if ($mastheadArray['image']->url != "") {
                if ($mastheadArray['image']->attributes['width'] == 0 || $mastheadArray['image']->attributes['height'] == 0) {
                    list($width, $height) = getimagesize($mastheadArray['image']->url);
                    $mastheadArray['image']->attributes['width']  = $width;
                    $mastheadArray['image']->attributes['height'] = $height;
                }

                $mastheadArray['image']->url = Uri::root() . $mastheadArray['image']->url;
            }
        }

        // Truncate description if descLength is set
        if (isset($descLength) && !empty($descLength)) {
            $mastheadArray['description'] = StringHelper::truncate($mastheadArray['description'], $descLength, true, true);
        }

        return $mastheadArray;
    }

    /**
     * Retrieve Article
     *
     * @param $app
     * @param $input
     * @param $descSource
     *
     * @return false|object
     */
    public static function getArticle($app, $input, $descSource)
    {
        if ($input->get('option') === 'com_content' && $input->get('view') === 'article') {
            // Save all the data you need to return
            $items = new \stdClass();

            // Get the article ID
            $articleId = $app->input->getInt('id');

            // Set application parameters in model
            $appParams = $app->getParams();

            // The article model
            $model = $app->bootComponent('com_content')
                ->getMVCFactory()->createModel('Article', 'Site', ['ignore_request' => true]);

            // Please, use any other filter as you need
            $model->setState('params', $appParams);
            $model->setState('filter.published', 1);
            $model->setState('article.id', (int) $articleId);

            $article = $model->getItem();

            $images             = json_decode($article->images);
            $items->title       = $article->title;
            $items->image       = ($images->image_intro) ? : $images->image_fulltext;
            switch ($descSource) {
                case "article":
                    $items->description = strip_tags(str_replace('</p>', ' ', $article->introtext));
                    break;
                case "note":
                    $items->description = $article->note;
                    break;
                case "imagealt":
                    $items->description = $items->image;
                    break;
                case "imagecaption":
                    $items->description = $items->image;
                    break;
                case "pagetitle":
                    $items->description = $article->pagetitle;
                    break;
                case "metadesc":
                    $items->description = $article->metadesc;
                    break;
            }

            return $items;
        }

        return false;
    }
}
