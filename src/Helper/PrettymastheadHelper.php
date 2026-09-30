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

        // Find the masthead configured for the current menu item
        $menuMasthead = null;

        if (!empty($itemId) && (\is_object($mastheads) || \is_array($mastheads))) {
            foreach ($mastheads as $m) {
                if (\is_object($m) && (int) ($m->mastheadmenuitem ?? 0) === (int) $itemId) {
                    $menuMasthead = $m;
                    break;
                }
            }
        }

        // Without a menu masthead, an article being viewed overrides the default masthead.
        // With one, only when the menu item is a category (the article was opened from that category).
        $useArticle = true;

        if ($menuMasthead !== null) {
            $this->updateMastheadArray($menuMasthead, $mastheadArray);

            $useArticle = ($app->getMenu()->getActive()?->query['view'] ?? '') === 'category';
        }

        if ($useArticle) {
            $this->updateMastheadArray($this->getArticle($app, $input, $descSource, $imagePriority), $mastheadArray);
        }

        $mastheadArray['image'] = $this->getImage((string) $mastheadArray['image']);

        // Truncate description if descLength is set
        if (isset($descLength) && !empty($descLength)) {
            $mastheadArray['description'] = StringHelper::truncate(
                (string) $mastheadArray['description'],
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
        if ($input->getCmd('option') !== 'com_content' || $input->getCmd('view') !== 'article') {
            return false;
        }

        // The article model
        $model = $app->bootComponent('com_content')
            ->getMVCFactory()->createModel('Article', 'Site', ['ignore_request' => true]);

        $model->setState('params', $app->getParams());
        $model->setState('filter.published', 1);
        $model->setState('article.id', $input->getInt('id'));

        try {
            $article = $model->getItem();
        } catch (\Throwable $e) {
            // Article not found or not published: keep the menu or default masthead
            return false;
        }

        // Do not show the title and image of an article the visitor may not view
        if (!\is_object($article) || !$article->params instanceof Registry || !$article->params->get('access-view')) {
            return false;
        }

        $images = json_decode((string) ($article->images ?? ''));

        if (!\is_object($images)) {
            $images = new \stdClass();
        }

        $intro = [
            'image'   => (string) ($images->image_intro ?? ''),
            'alt'     => (string) ($images->image_intro_alt ?? ''),
            'caption' => (string) ($images->image_intro_caption ?? ''),
        ];
        $full = [
            'image'   => (string) ($images->image_fulltext ?? ''),
            'alt'     => (string) ($images->image_fulltext_alt ?? ''),
            'caption' => (string) ($images->image_fulltext_caption ?? ''),
        ];

        [$first, $second] = $imagePriority === 'full' ? [$full, $intro] : [$intro, $full];

        $items        = new \stdClass();
        $items->title = (string) $article->title;
        $items->image = $first['image'] ?: $second['image'];

        switch ($descSource) {
            case "article":
                // Plain text only: the layout escapes it, so decode entities and drop plugin tags like {loadmodule ...}
                $description        = strip_tags(str_replace('</p>', ' ', (string) $article->introtext));
                $description        = html_entity_decode($description, ENT_QUOTES, 'UTF-8');
                $description        = preg_replace('/\{\/?[a-z][^{}]*\}/i', '', $description);
                $items->description = trim(preg_replace('/\s+/u', ' ', $description));
                break;
            case "imagealt":
                $items->description = $first['alt'] ?: $second['alt'];
                break;
            case "imagecaption":
                $items->description = $first['caption'] ?: $second['caption'];
                break;
            case "pagetitle":
                // The browser page title is an article option, merged into the params by the site model
                $items->description = (string) $article->params->get('article_page_title', '');
                break;
            case "metadesc":
                $items->description = (string) ($article->metadesc ?? '');
                break;
        }

        return $items;
    }

    /**
     * Builds the image object for the layout from a media field value.
     *
     * Only local images are measured, and only when the media value has no size;
     * remote images are never fetched, the layout then falls back to its default ratio.
     *
     * @param   string  $image  The media field value, e.g. "images/a.jpg#joomlaImage://local-images/a.jpg?width=800&height=600".
     *
     * @return  \stdClass|string  Object with url and attributes (width, height), or an empty string when there is no image.
     *
     * @since   1.2.0
     */

    private function getImage(string $image)
    {
        if ($image === '') {
            return '';
        }

        $image = HTMLHelper::_('cleanImageURL', $image);

        // Drop any media fragment (#joomlaImage://...) that cleanImageURL leaves on values without a size
        $image->url = explode('#', (string) $image->url, 2)[0];

        if ($image->url === '') {
            return '';
        }

        $isRemote = (bool) preg_match('~^([a-z][a-z0-9+.-]*:)?//~i', $image->url);

        if (!$isRemote && (empty($image->attributes['width']) || empty($image->attributes['height']))) {
            [$image->attributes['width'], $image->attributes['height']] = $this->getLocalImageSize($image->url);
        }

        if (!$isRemote && !str_starts_with($image->url, '/')) {
            $image->url = Uri::root() . $image->url;
        }

        return $image;
    }

    /**
     * Reads the size of an image file inside the site root.
     *
     * @param   string  $url  The relative image URL.
     *
     * @return  int[]  Width and height, or [0, 0] when the file cannot be read.
     *
     * @since   1.2.0
     */

    private function getLocalImageSize(string $url): array
    {
        $root = realpath(JPATH_ROOT);
        $path = realpath(JPATH_ROOT . '/' . ltrim(rawurldecode((string) parse_url($url, PHP_URL_PATH)), '/'));

        if ($root === false || $path === false || !str_starts_with($path, $root . DIRECTORY_SEPARATOR) || !is_file($path)) {
            return [0, 0];
        }

        $size = getimagesize($path);

        return $size ? [(int) $size[0], (int) $size[1]] : [0, 0];
    }
}
