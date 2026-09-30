<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_prettymasthead
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/**
 * Script file of Prettymasthead module
 */
class mod_prettymastheadInstallerScript
{
    /**
     * Extension script constructor.
     *
     * @return  void
     */
    public function __construct()
    {
        $this->minimumJoomla = '4.0';
        $this->minimumPhp    = JOOMLA_MINIMUM_PHP;
    }

    /**
     * Method to install the extension
     *
     * @param   InstallerAdapter  $parent  The class calling this method
     *
     * @return  boolean  True on success
     */
    function install($parent)
    {
        echo Text::_('MOD_PRETTYMASTHEAD_INSTALLERSCRIPT_INSTALL');

        return true;
    }

    /**
     * Method to uninstall the extension
     *
     * @param   InstallerAdapter  $parent  The class calling this method
     *
     * @return  boolean  True on success
     */
    function uninstall($parent)
    {
        echo Text::_('MOD_PRETTYMASTHEAD_INSTALLERSCRIPT_UNINSTALL');

        return true;
    }

    /**
     * Method to update the extension
     *
     * @param   InstallerAdapter  $parent  The class calling this method
     *
     * @return  boolean  True on success
     */
    function update($parent)
    {
        echo Text::_('MOD_PRETTYMASTHEAD_INSTALLERSCRIPT_UPDATE');

        $this->migrateCacheMode();

        return true;
    }

    /**
     * Switch existing module instances to the page-aware "safeuri" cache mode.
     *
     * The hidden cachemode field posts its stored value back, so re-saving a module
     * would keep the old "static" mode, which served one masthead on every page.
     *
     * @return  void
     *
     * @since   1.1.0
     */
    private function migrateCacheMode(): void
    {
        try {
            $db     = Factory::getContainer()->get(DatabaseInterface::class);
            $module = 'mod_prettymasthead';

            // createQuery() exists from Joomla 5, getQuery(true) is deprecated there
            $query = method_exists($db, 'createQuery') ? $db->createQuery() : $db->getQuery(true);
            $query->select($db->quoteName(['id', 'params']))
                ->from($db->quoteName('#__modules'))
                ->where($db->quoteName('module') . ' = :module')
                ->bind(':module', $module);

            $rows = $db->setQuery($query)->loadObjectList();

            foreach ($rows as $row) {
                $params = json_decode((string) $row->params);

                if (!$params instanceof \stdClass || ($params->cachemode ?? '') === 'safeuri') {
                    continue;
                }

                $params->cachemode = 'safeuri';
                $json              = json_encode($params);
                $id                = (int) $row->id;

                if ($json === false) {
                    continue;
                }

                $update = method_exists($db, 'createQuery') ? $db->createQuery() : $db->getQuery(true);
                $update->update($db->quoteName('#__modules'))
                    ->set($db->quoteName('params') . ' = :params')
                    ->where($db->quoteName('id') . ' = :id')
                    ->bind(':params', $json)
                    ->bind(':id', $id, ParameterType::INTEGER);

                $db->setQuery($update)->execute();
            }
        } catch (\Throwable $e) {
            // Never block the update over this; affected modules keep the old cache mode until it is fixed by hand
            Log::add(Text::sprintf('MOD_PRETTYMASTHEAD_INSTALLERSCRIPT_CACHEMODE_FAILED', $e->getMessage()), Log::WARNING, 'jerror');
        }
    }

    /**
     * Function called before extension installation/update/removal procedure commences
     *
     * @param   string            $type    The type of change (install, update or discover_install, not uninstall)
     * @param   InstallerAdapter  $parent  The class calling this method
     *
     * @return  boolean  True on success
     */
    function preflight($type, $parent)
    {
        // Check for the minimum PHP version before continuing
        if (!empty($this->minimumPhp) && version_compare(PHP_VERSION, $this->minimumPhp, '<')) {
            Log::add(Text::sprintf('JLIB_INSTALLER_MINIMUM_PHP', $this->minimumPhp), Log::WARNING, 'jerror');

            return false;
        }

        // Check for the minimum Joomla version before continuing
        if (!empty($this->minimumJoomla) && version_compare(JVERSION, $this->minimumJoomla, '<')) {
            Log::add(Text::sprintf('JLIB_INSTALLER_MINIMUM_JOOMLA', $this->minimumJoomla), Log::WARNING, 'jerror');

            return false;
        }

        echo Text::_('MOD_PRETTYMASTHEAD_INSTALLERSCRIPT_PREFLIGHT');

        return true;
    }

    /**
     * Function called after extension installation/update/removal procedure commences
     *
     * @param   string            $type    The type of change (install, update or discover_install, not uninstall)
     * @param   InstallerAdapter  $parent  The class calling this method
     *
     * @return  boolean  True on success
     */
    function postflight($type, $parent)
    {
        if ($type == "update") {
            echo Text::_('MOD_PRETTYMASTHEAD_INSTALLERSCRIPT_RESAVE_MODULE');
        }
        echo Text::_('MOD_PRETTYMASTHEAD_INSTALLERSCRIPT_POSTFLIGHT');

        return true;
    }
}
