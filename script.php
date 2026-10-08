<?php

/**
 * @package     TLWebdesign.Module
 * @subpackage  mod_prettymasthead
 *
 * @copyright   Copyright (C) 2022 TLWebdesign. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Filesystem\File;

/**
 * Installer script for mod_prettymasthead
 *
 * @since  1.2.0
 */
return new class () implements InstallerScriptInterface {
    /**
     * Minimum Joomla version required to install the module.
     *
     * @since  1.2.0
     */
    private const MINIMUM_JOOMLA = '5.4.0';

    /**
     * Minimum PHP version required to install the module.
     *
     * @since  1.2.0
     */
    private const MINIMUM_PHP = '8.1.0';

    /**
     * Function called after the extension is installed.
     *
     * @param   InstallerAdapter  $adapter  The adapter calling this method
     *
     * @return  boolean  True on success
     *
     * @since   1.2.0
     */
    public function install(InstallerAdapter $adapter): bool
    {
        echo Text::_('MOD_PRETTYMASTHEAD_INSTALLERSCRIPT_INSTALL');

        return true;
    }

    /**
     * Function called after the extension is updated.
     *
     * @param   InstallerAdapter  $adapter  The adapter calling this method
     *
     * @return  boolean  True on success
     *
     * @since   1.2.0
     */
    public function update(InstallerAdapter $adapter): bool
    {
        echo Text::_('MOD_PRETTYMASTHEAD_INSTALLERSCRIPT_UPDATE');

        $this->removeLegacyEntryFile($adapter);
        $this->migrateParams();
        $this->cleanCache();

        return true;
    }

    /**
     * Function called after the extension is uninstalled.
     *
     * @param   InstallerAdapter  $adapter  The adapter calling this method
     *
     * @return  boolean  True on success
     *
     * @since   1.2.0
     */
    public function uninstall(InstallerAdapter $adapter): bool
    {
        echo Text::_('MOD_PRETTYMASTHEAD_INSTALLERSCRIPT_UNINSTALL');

        return true;
    }

    /**
     * Function called before the extension is installed, updated or uninstalled.
     *
     * @param   string            $type     The type of change (install, update, discover_install or uninstall)
     * @param   InstallerAdapter  $adapter  The adapter calling this method
     *
     * @return  boolean  True to continue, false to abort
     *
     * @since   1.2.0
     */
    public function preflight(string $type, InstallerAdapter $adapter): bool
    {
        if ($type === 'uninstall') {
            return true;
        }

        if (version_compare(PHP_VERSION, self::MINIMUM_PHP, '<')) {
            Log::add(Text::sprintf('JLIB_INSTALLER_MINIMUM_PHP', self::MINIMUM_PHP), Log::WARNING, 'jerror');

            return false;
        }

        if (version_compare(JVERSION, self::MINIMUM_JOOMLA, '<')) {
            Log::add(Text::sprintf('JLIB_INSTALLER_MINIMUM_JOOMLA', self::MINIMUM_JOOMLA), Log::WARNING, 'jerror');

            return false;
        }

        return true;
    }

    /**
     * Function called after the extension is installed, updated or uninstalled.
     *
     * @param   string            $type     The type of change (install, update, discover_install or uninstall)
     * @param   InstallerAdapter  $adapter  The adapter calling this method
     *
     * @return  boolean  True on success
     *
     * @since   1.2.0
     */
    public function postflight(string $type, InstallerAdapter $adapter): bool
    {
        if ($type === 'update') {
            echo Text::_('MOD_PRETTYMASTHEAD_INSTALLERSCRIPT_RESAVE_MODULE');
        }

        return true;
    }

    /**
     * Remove the entry file used before the module had a service provider.
     *
     * Joomla 6.1 removes files that are no longer in the manifest, Joomla 5 does not.
     *
     * @param   InstallerAdapter  $adapter  The adapter calling this method
     *
     * @return  void
     *
     * @since   1.2.0
     */
    private function removeLegacyEntryFile(InstallerAdapter $adapter): void
    {
        $file = $adapter->getParent()->getPath('extension_root') . '/mod_prettymasthead.php';

        if (!is_file($file)) {
            return;
        }

        try {
            File::delete($file);
        } catch (\Throwable $e) {
            // The file is no longer loaded, so leaving it behind is harmless
        }
    }

    /**
     * Clear cached module output and the cached module list.
     *
     * The module list cache holds the module params, including the old cache mode,
     * so without this the old mode and its cached output stay in use until they expire.
     *
     * @return  void
     *
     * @since   1.2.0
     */
    private function cleanCache(): void
    {
        $app = Factory::getApplication();

        foreach (['com_modules', 'mod_prettymasthead'] as $group) {
            try {
                Factory::getContainer()->get(CacheControllerFactoryInterface::class)
                    ->createCacheController('callback', ['defaultgroup' => $group, 'cachebase' => $app->get('cache_path', JPATH_CACHE)])
                    ->clean();
            } catch (\Throwable $e) {
                Log::add(Text::sprintf('MOD_PRETTYMASTHEAD_INSTALLERSCRIPT_CACHEMODE_FAILED', $e->getMessage()), Log::WARNING, 'jerror');
            }
        }
    }

    /**
     * Bring the params of existing module instances up to date.
     *
     * - The cache mode becomes the page-aware "safeuri": the hidden cachemode field posts its stored value back,
     *   so re-saving a module would keep the old "static" mode, which served one masthead on every page.
     * - The single button of the default masthead and of each menu masthead becomes the first row of its buttons list.
     *
     * @return  void
     *
     * @since   1.2.0
     */
    private function migrateParams(): void
    {
        try {
            $db     = Factory::getContainer()->get(DatabaseInterface::class);
            $module = 'mod_prettymasthead';

            $query = $db->createQuery()
                ->select($db->quoteName(['id', 'params']))
                ->from($db->quoteName('#__modules'))
                ->where($db->quoteName('module') . ' = :module')
                ->bind(':module', $module);

            $rows = $db->setQuery($query)->loadObjectList();

            foreach ($rows as $row) {
                $params = json_decode((string) $row->params);

                if (!$params instanceof \stdClass) {
                    continue;
                }

                $changed = $this->migrateButton($params, 'default');

                foreach ((array) ($params->mastheads ?? []) as $masthead) {
                    if ($masthead instanceof \stdClass && $this->migrateButton($masthead, 'masthead')) {
                        $changed = true;
                    }
                }

                if (($params->cachemode ?? '') !== 'safeuri') {
                    $params->cachemode = 'safeuri';
                    $changed           = true;
                }

                if (!$changed) {
                    continue;
                }

                $json = json_encode($params);
                $id   = (int) $row->id;

                if ($json === false) {
                    continue;
                }

                $update = $db->createQuery()
                    ->update($db->quoteName('#__modules'))
                    ->set($db->quoteName('params') . ' = :params')
                    ->where($db->quoteName('id') . ' = :id')
                    ->bind(':params', $json)
                    ->bind(':id', $id, ParameterType::INTEGER);

                $db->setQuery($update)->execute();
            }
        } catch (\Throwable $e) {
            // Never block the update over this; affected modules keep their old params until they are fixed by hand
            Log::add(Text::sprintf('MOD_PRETTYMASTHEAD_INSTALLERSCRIPT_CACHEMODE_FAILED', $e->getMessage()), Log::WARNING, 'jerror');
        }
    }

    /**
     * Move a single button (<prefix>buttontext, <prefix>buttonurl, <prefix>buttonclass) into the <prefix>buttons list.
     *
     * @param   \stdClass  $holder  The module params, or one menu masthead row.
     * @param   string     $prefix  "default" or "masthead".
     *
     * @return  boolean  True when $holder was changed.
     *
     * @since   1.2.0
     */
    private function migrateButton(\stdClass $holder, string $prefix): bool
    {
        $list    = $prefix . 'buttons';
        $changed = false;
        $button  = [];

        foreach (['buttontext', 'buttonurl', 'buttonclass'] as $key) {
            if (property_exists($holder, $prefix . $key)) {
                $button[$key] = (string) $holder->{$prefix . $key};
                $changed      = true;

                unset($holder->{$prefix . $key});
            }
        }

        $existing = $holder->{$list} ?? null;
        $hasList  = (\is_object($existing) || \is_array($existing)) && (array) $existing !== [];

        // A button without text was never shown
        if (trim($button['buttontext'] ?? '') === '' || $hasList) {
            return $changed;
        }

        // Subform rows are stored as <field name><index>
        $holder->{$list} = (object) [
            $list . '0' => (object) [
                'buttontext'  => $button['buttontext'],
                'buttonurl'   => $button['buttonurl'] ?? '',
                'buttonclass' => $button['buttonclass'] ?? 'btn-primary',
            ],
        ];

        return true;
    }
};
