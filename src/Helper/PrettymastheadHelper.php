<?php

/**
 * @package     TLWebdesign.Module
 * @subpackage  mod_prettymasthead
 *
 * @copyright   Copyright (C) 2022 TLWebdesign. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace TlwebNamespace\Module\Prettymasthead\Site\Helper;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\HTML\Helpers\StringHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use TlwebNamespace\Module\Prettymasthead\Site\Rule\ButtonurlRule;

\defined('_JEXEC') or die;

/**
 * Helper for mod_prettymasthead
 *
 * @since  V1.0.0
 */
class PrettymastheadHelper
{
    /**
     * Allowed values for the title tag, content position and title/description visibility.
     *
     * @since  1.1.0
     */
    private const TITLE_TAGS   = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
    private const POSITIONS    = ['start', 'center', 'end'];
    private const VISIBILITIES = ['', 'sm', 'md', 'lg', 'none'];

    /**
     * Retrieves masthead data based on menu item specific configurations, default settings,
     * and potentially the current article if within a category view.
     *
     * @param   Registry         $params  The module parameters.
     * @param   SiteApplication  $app     The application.
     *
     * @return  array    $mastheadArray     An associative array containing masthead data (title, image, description, etc.).
     *
     * @since   0.1.0
     */

    public function getMasthead(Registry $params, SiteApplication $app): array
    {
        $input = $app->getInput();

        $mastheads     = $params->get('mastheads');
        $descLength    = $params->get('desclength');
        $descSource    = $params->get('descsource');
        $imagePriority = $params->get('imagepriority');

        $defaultmasthead = [
            'image'                 => $params->get('defaultmastheadimage'),
            'title'                 => $params->get('defaultmastheadtitle'),
            'description'           => $params->get('defaultmastheaddescription'),
            'position'              => $params->get('defaultmastheadposition'),
            'titletag'              => $params->get('defaultmastheadtitletag'),
            'titleclass'            => $params->get('defaultmastheadtitleclass'),
            'descriptionclass'      => $params->get('defaultmastheaddescriptionclass'),
            'titlevisibility'       => $params->get('defaulttitlevisibility'),
            'descriptionvisibility' => $params->get('defaultdescriptionvisibility'),
            'buttontext'            => $params->get('defaultbuttontext'),
            'buttonurl'             => $params->get('defaultbuttonurl'),
            'buttonclass'           => $params->get('defaultbuttonclass'),
        ];

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
                    $this->updateMastheadArray($m,$mastheadArray);

                    // GET ACTIVE MENU TO CHECK IF WE HAVE CATEGORY VIEW AND ONLY THEN TRY TO GET ARTICLE
                    $activeMenuQuery = $app->getMenu()->getActive()->query;
                    if ($activeMenuQuery['view'] == "category") {
                        // TRY TO GRAB ARTICLE
                        $article = $this->getArticle($app, $input, $descSource, $imagePriority);
                        $this->updateMastheadArray($article, $mastheadArray);
                    }
                } elseif (!$mastheadFound) {
                    // IF ITEM ID DOES NOT MATCH MASTHEADMENUITEM THEN TRY TO USE ARTICLE ITEM CONTENT
                    // THIS IS MAINLY USED WHEN YOU HAVE MENU ITEMS SET FOR CATEGORY ARTICLES.
                    $article = $this->getArticle($app, $input, $descSource, $imagePriority);
                    $this->updateMastheadArray($article, $mastheadArray);
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
                (int) $descLength,
                true,
                false
            );
        }

        $mastheadArray['buttonurl'] = $this->getButtonUrl((string) $mastheadArray['buttonurl']);

        // Only allow known values, as these end up in element names and class names
        $mastheadArray['titletag']              = $this->allowedValue($mastheadArray['titletag'], self::TITLE_TAGS, 'h2');
        $mastheadArray['position']              = $this->allowedValue($mastheadArray['position'], self::POSITIONS, 'center');
        $mastheadArray['titlevisibility']       = $this->allowedValue($mastheadArray['titlevisibility'], self::VISIBILITIES, '');
        $mastheadArray['descriptionvisibility'] = $this->allowedValue($mastheadArray['descriptionvisibility'], self::VISIBILITIES, '');
        $mastheadArray['titlevisibilityclass']       = $this->getVisibilityClass($mastheadArray['titlevisibility']);
        $mastheadArray['descriptionvisibilityclass'] = $this->getVisibilityClass($mastheadArray['descriptionvisibility']);

        return $mastheadArray;
    }

    /**
     * Returns the value if it is one of the allowed values, otherwise the fallback.
     *
     * @param   mixed     $value     The value to check.
     * @param   string[]  $allowed   The allowed values.
     * @param   string    $fallback  The value to use when $value is not allowed.
     *
     * @return  string
     *
     * @since   1.1.0
     */

    private function allowedValue($value, array $allowed, string $fallback): string
    {
        return \in_array($value, $allowed, true) ? $value : $fallback;
    }

    /**
     * Builds the Bootstrap display classes for a visibility setting.
     *
     * @param   string  $visibility  '' (always show), 'sm', 'md', 'lg' (show from that breakpoint up) or 'none' (always hide).
     *
     * @return  string
     *
     * @since   1.1.0
     */

    private function getVisibilityClass(string $visibility): string
    {
        if ($visibility === '') {
            return '';
        }

        if ($visibility === 'none') {
            return 'd-none';
        }

        return 'd-none d-' . $visibility . '-block';
    }

    /**
     * Makes the button URL safe for use in an href: URLs with a scheme that can run script are dropped
     * (see ButtonurlRule), and internal non-SEF links are routed.
     *
     * @param   string  $url  The button URL as stored in the module params.
     *
     * @return  string  The URL to link to (not HTML-escaped), or an empty string if it is not allowed.
     *
     * @since   1.1.0
     */

    private function getButtonUrl(string $url): string
    {
        $url = trim($url);

        if ($url === '') {
            return '';
        }

        // Also checked here for values saved before the form rule existed
        if (!ButtonurlRule::isAllowed($url)) {
            return '';
        }

        // The url filter saves "index.php?..." as "<site path>/index.php?...", so strip the site path again
        $rootPath = Uri::root(true);

        if (str_starts_with($url, $rootPath . '/index.php')) {
            $url = substr($url, \strlen($rootPath) + 1);
        }

        if (str_starts_with($url, 'index.php')) {
            // Not XHTML-encoded: the layout escapes the href
            $url = Route::_($url, false);
        }

        return $url;
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

    private function updateMastheadArray($updateData, array &$mastheadArray)
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
     * @param   SiteApplication                         $app            The application object.
     * @param   \Joomla\Input\Input                     $input          The input object.
     * @param   string                                  $descSource     The source of the description field ('article', etc.).
     * @param   string                                  $imagePriority  The image priority ('full' or 'intro').
     *
     * @return  \stdClass|false  An object containing article details (title, image, and description), or false if the conditions are not met.
     *
     * @since   V0.3.0
     */

    private function getArticle(SiteApplication $app, $input, $descSource, $imagePriority)
    {
        if ($input->get('option') === 'com_content' && $input->get('view') === 'article') {
            // Save all the data you need to return
            $items = new \stdClass();

            // Get the article ID
            $articleId = $input->getInt('id');

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
                    // Plain text only: the layout escapes it, so decode entities and drop plugin tags like {loadmodule ...}
                    $description        = strip_tags(str_replace('</p>', ' ', $article->introtext));
                    $description        = html_entity_decode($description, ENT_QUOTES, 'UTF-8');
                    $description        = preg_replace('/\{\/?[a-z][^{}]*\}/i', '', $description);
                    $items->description = trim(preg_replace('/\s+/u', ' ', $description));
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
