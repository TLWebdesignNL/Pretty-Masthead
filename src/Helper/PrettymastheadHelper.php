<?php

/**
 * @package     TLWebdesign.Module
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
     * Retrieves masthead data based on menu item specific configurations, default settings,
     * and potentially the current article if within a category view.
     *
     * @param   object  $mastheads        An object containing menu-item specific mastheads.
     * @param   object  $defaultmasthead  An object containing default masthead settings.
     * @param   int     $descLength       Maximum length of the description.
     * @param   string  $descSource       Source of the description (article, note, imagealt, imagecaption, pagetitle, metadesc).
     * @param   string  $imagePriority    Priority of image source (intro, full).
     *
     * @return  array    $mastheadArray     An associative array containing masthead data (title, image, description, etc.).
     *
     * @since   0.1.0
     */

    public static function getMasthead($mastheads, $defaultmasthead, $descLength, $descSource, $imagePriority): array
    {
        $app   = Factory::getApplication();
        $input = $app->input;

        $itemId                                 = $input->get('Itemid', '', 'INT');
        $mastheadArray['image']                 = (isset($defaultmasthead['image'])) ? $defaultmasthead['image'] : '';
        $mastheadArray['title']                 = (isset($defaultmasthead['title'])) ? $defaultmasthead['title'] : '';
        $mastheadArray['description']           = (isset($defaultmasthead['description'])) ? $defaultmasthead['description'] : '';
        $mastheadArray['position']              = (isset($defaultmasthead['position'])) ? $defaultmasthead['position'] : '';
        $mastheadArray['titletag']              = (isset($defaultmasthead['titletag'])) ? $defaultmasthead['titletag'] : '';
        $mastheadArray['titleclass']            = (isset($defaultmasthead['titleclass'])) ? $defaultmasthead['titleclass'] : '';
        $mastheadArray['descriptionclass']      = (isset($defaultmasthead['descriptionclass'])) ? $defaultmasthead['descriptionclass'] : '';
        $mastheadArray['titlevisibility']       = (isset($defaultmasthead['titlevisibility'])) ? $defaultmasthead['titlevisibility'] : '';
        $mastheadArray['descriptionvisibility'] = (isset($defaultmasthead['descriptionvisibility'])) ? $defaultmasthead['descriptionvisibility'] : '';
        $mastheadArray['buttontext']            = (isset($defaultmasthead['buttontext'])) ? $defaultmasthead['buttontext'] : '';
        $mastheadArray['buttonurl']             = (isset($defaultmasthead['buttonurl'])) ? $defaultmasthead['buttonurl'] : '';
        $mastheadArray['buttonclass']           = (isset($defaultmasthead['buttonclass'])) ? $defaultmasthead['buttonclass'] : '';

        // LOOP THROUGH MENU ITEM SPECIFIC MASTHEADS
        if (isset($mastheads) && is_object($mastheads)) {
            $mastheadFound = false;
            foreach ($mastheads as $m) {
                if (!empty($itemId) && $itemId == $m->mastheadmenuitem) {
                    $mastheadFound = true;
                    self::updateMastheadArray($m,$mastheadArray);

                    // GET ACTIVE MENU TO CHECK IF WE HAVE CATEGORY VIEW AND ONLY THEN TRY TO GET ARTICLE
                    $activeMenuQuery = $app->getMenu()->getActive()->query;
                    if ($activeMenuQuery['view'] == "category") {
                        // TRY TO GRAB ARTICLE
                        $article = self::getArticle($app, $input, $descSource, $imagePriority);
                        self::updateMastheadArray($article, $mastheadArray);
                    }
                } elseif (!$mastheadFound) {
                    // IF ITEM ID DOES NOT MATCH MASTHEADMENUITEM THEN TRY TO USE ARTICLE ITEM CONTENT
                    // THIS IS MAINLY USED WHEN YOU HAVE MENU ITEMS SET FOR CATEGORY ARTICLES.
                    $article = self::getArticle($app, $input, $descSource, $imagePriority);
                    self::updateMastheadArray($article, $mastheadArray);
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
            $mastheadArray['description'] = StringHelper::truncate(
                $mastheadArray['description'],
                $descLength,
                true,
                true
            );
        }

        return $mastheadArray;
    }

    /**
     * Method to update the masthead array with data from a given object or array.
     *
     * @param   mixed  $updateData      Data to update the masthead array, should be an object or an array.
     * @param   array &$mastheadArray   Reference to the masthead array to be updated.
     *
     * @return  void
     *
     * @since   1.0.0
     */

    private static function updateMastheadArray($updateData, array &$mastheadArray)
    {
        if (!$updateData) {
            return;
        }

        $updateData = (array)$updateData;

        foreach ($updateData as $key => $value) {
            if (!empty($updateData[$key])) {
                // strip "masthead" from $key string
                $keyString = str_replace("masthead", "",$key);

                $mastheadArray[$keyString] = $value;
            }
        }
    }


    /**
     * Retrieve an article based on the current request parameters.
     *
     * This method retrieves an article from Joomla's com_content component,
     * applying various filters based on the parameters provided.
     *
     * @param   \Joomla\CMS\Application\CMSApplication  $app            The application object.
     * @param   \Joomla\Input\Input                     $input          The input object.
     * @param   string                                  $descSource     The source of the description field ('article', etc.).
     * @param   string                                  $imagePriority  The image priority ('full' or 'intro').
     *
     * @return  \stdClass|false  An object containing article details (title, image, and description), or false if the conditions are not met.
     *
     * @since   V0.3.0
     */

    public static function getArticle($app, $input, $descSource, $imagePriority)
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
            $model->setState('article.id', (int)$articleId);

            $article = $model->getItem();

            $images       = json_decode($article->images);
            $items->title = $article->title;
            $items->image = ($images->image_intro) ?: $images->image_fulltext;
            $imagealt = ($images->image_intro_alt) ?: $images->image_fulltext_alt;
            $imagecaption = ($images->image_intro_caption) ?: $images->image_fulltext_caption;
            if ($imagePriority && $imagePriority == "full") {
                $items->image = ($images->image_fulltext) ?: $images->image_intro;
                $imagealt = ($images->image_fulltext_alt) ?: $images->image_intro_alt;
                $imagecaption = ($images->image_fulltext_caption) ?: $images->image_intro_caption;
            }

            switch ($descSource) {
                case "article":
                    $items->description = strip_tags(str_replace('</p>', ' ', $article->introtext));
                    break;
                case "imagealt":
                    $items->description = $imagealt;
                    break;
                case "imagecaption":
                    $items->description = $imagecaption;
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
