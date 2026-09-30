<?php

/**
 * @package     TLWebdesign.Module
 * @subpackage  mod_prettymasthead
 *
 * @copyright   Copyright (C) 2022 TLWebdesign. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace TlwebNamespace\Module\Prettymasthead\Site\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\CMS\Helper\ModuleHelper;

\defined('_JEXEC') or die;

/**
 * Dispatcher class for mod_prettymasthead
 *
 * @since  1.2.0
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Returns the layout data.
     *
     * @return  array|false
     *
     * @since   1.2.0
     */
    protected function getLayoutData()
    {
        $data   = parent::getLayoutData();
        $params = $data['params'];

        // The masthead depends on the menu item and the article being viewed, so cache it per page
        $cacheParams               = new \stdClass();
        $cacheParams->cachemode    = 'safeuri';
        $cacheParams->class        = $this->getHelperFactory()->getHelper('PrettymastheadHelper');
        $cacheParams->method       = 'getMasthead';
        $cacheParams->methodparams = [$params, $data['app']];
        $cacheParams->modeparams   = ['option' => 'cmd', 'view' => 'cmd', 'id' => 'int', 'Itemid' => 'int'];

        $data['masthead']     = ModuleHelper::moduleCache($this->module, $params, $cacheParams);
        $data['mainDivClass'] = (string) $params->get('maindivclass', '');
        $data['minHeight']    = (int) $params->get('minheight', 0);
        $data['maxHeight']    = (int) $params->get('maxheight', 0);

        return $data;
    }
}
