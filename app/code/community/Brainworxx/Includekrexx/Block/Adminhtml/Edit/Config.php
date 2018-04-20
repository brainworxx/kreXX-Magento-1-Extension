<?php
/**
 * kreXX: Krumo eXXtended
 *
 * kreXX is a debugging tool, which displays structured information
 * about any PHP object. It is a nice replacement for print_r() or var_dump()
 * which are used by a lot of PHP developers.
 *
 * kreXX is a fork of Krumo, which was originally written by:
 * Kaloyan K. Tsvetkov <kaloyan@kaloyan.info>
 *
 * @author
 *   brainworXX GmbH <info@brainworxx.de>
 *
 * @license
 *   http://opensource.org/licenses/LGPL-2.1
 *
 *   GNU Lesser General Public License Version 2.1
 *
 *   kreXX Copyright (C) 2014-2017 Brainworxx GmbH
 *
 *   This library is free software; you can redistribute it and/or modify it
 *   under the terms of the GNU Lesser General Public License as published by
 *   the Free Software Foundation; either version 2.1 of the License, or (at
 *   your option) any later version.
 *   This library is distributed in the hope that it will be useful, but WITHOUT
 *   ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 *   FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License
 *   for more details.
 *   You should have received a copy of the GNU Lesser General Public License
 *   along with this library; if not, write to the Free Software Foundation,
 *   Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 */

use \Brainworxx\Krexx\Service\Config\Fallback;

/**
 * Block for the configuration file editor in the backend.
 */
class Brainworxx_Includekrexx_Block_Adminhtml_Edit_Config extends Mage_Adminhtml_Block_Template
{

    /**
     * Assign the values to the template file.
     *
     * @see Mage_Core_Block_Template::_construct()
     */
    public function _construct()
    {
        parent::_construct();

        $help = array();
        $settings = array();
        $factory = array();
        $pool = \Krexx::$pool;

        // Initializing the select data for the template.
        $this->setSelectDestination(
            array(
                'browser' => 'browser',
                'file' => 'file'
            )
        );

        $this->setSelectBool(array('true' => 'true', 'false' => 'false'));
        $this->setSelectBacktrace(
            array(
                'normal' => 'normal',
                'deep' => 'deep'
            )
        );
        $skins = array();

        foreach ($pool->render->getSkinList() as $skin) {
            $skins[$skin] = $skin;
        }

        foreach ($pool->config->configFallback as $sectionName => $sectionSettings) {
            foreach ($sectionSettings as $settingName) {
                $settings[$sectionName][$settingName] = $pool
                    ->config->iniConfig->getConfigFromFile($sectionName, $settingName);

                $help[$settingName] = htmlspecialchars(strip_tags($pool->messages->getHelp($settingName . 'Help')));

                // Are these actually set?
                if ($settings[$sectionName][$settingName] === null) {
                    $factory[$settingName] = ' checked="checked" ';
                    $settings[$sectionName][$settingName] = $pool
                        ->config->feConfigFallback[$settingName][Fallback::VALUE];
                } else {
                    $factory[$settingName] = '';
                }
            }
        }


        // Add them to the template.
        $this->assign('help', $help);
        $this->assign('skins', $skins);
        $this->assign('settings', $settings);
        $this->assign('factory', $factory);
    }

    /**
     * Return save url for edit form
     *
     * @return string
     *   The url where the form is saved.
     */
    public function getSaveUrl()
    {
        return $this->getUrl(
            '*/*/saveconfig',
            array('_current' => true, 'back' => null)
        );
    }
}
