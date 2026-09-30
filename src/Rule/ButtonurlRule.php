<?php

/**
 * @package     TLWebdesign.Module
 * @subpackage  mod_prettymasthead
 *
 * @copyright   Copyright (C) 2022 TLWebdesign. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace TlwebNamespace\Module\Prettymasthead\Site\Rule;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;

\defined('_JEXEC') or die;

/**
 * Form rule for the button URL: web, site-relative, mailto:, tel: and app links are allowed,
 * schemes that can run script are not.
 *
 * @since  1.2.0
 */
class ButtonurlRule extends FormRule
{
    /**
     * Schemes that can execute script when used in an href.
     *
     * @since  1.2.0
     */
    public const BLOCKED_SCHEMES = ['javascript', 'vbscript', 'data'];

    /**
     * Method to test the button URL.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   ?string            $group    The field name group control value.
     * @param   ?Registry          $input    An optional Registry object with the entire data set to validate against the entire form.
     * @param   ?Form              $form     The form object for which the field is being tested.
     *
     * @return  boolean  True if the value is valid, false otherwise.
     *
     * @since   1.2.0
     */
    public function test(\SimpleXMLElement $element, $value, $group = null, ?Registry $input = null, ?Form $form = null)
    {
        $required = ((string) $element['required'] === 'true' || (string) $element['required'] === 'required');

        if (!$required && ($value === null || $value === '')) {
            return true;
        }

        return self::isAllowed((string) $value);
    }

    /**
     * Checks that a URL does not use a blocked scheme.
     *
     * @param   string  $url  The URL to check.
     *
     * @return  boolean
     *
     * @since   1.2.0
     */
    public static function isAllowed(string $url): bool
    {
        // Browsers ignore whitespace and control characters inside a scheme ("java\tscript:"), so ignore them here too.
        // A scheme is whatever comes before a ":" that appears before any "/", "?" or "#".
        $compact = preg_replace('/[\x00-\x20]+/', '', $url);

        if (!preg_match('~^([^/?#]*?):~', $compact, $matches)) {
            return true;
        }

        return !\in_array(strtolower($matches[1]), self::BLOCKED_SCHEMES, true);
    }
}
