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
 * Block for the frontend configuration in the backend.
 */
class Brainworxx_Includekrexx_Block_Adminhtml_Edit_Feconfig extends Mage_Adminhtml_Block_Template
{
    
    /**
     * Return save url for edit form
     *
     * @return string
     *   The url, where the form is saved.
     */
    public function getSaveUrl()
    {
        return $this->getUrl(
            '*/*/savefeconfig', array(
            '_current' => true,
            'back' => null
            )
        );
    }

    /**
     * Assign the values to the template file.
     *
     * @see Mage_Core_Block_Template::_construct()
     */
    public function _construct()
    {
        parent::_construct();

        // Generate the values for the select elements.
        $data = array();
        $settings = array();
        $factory = array();
        $pool = \Krexx::$pool;

        // Setting possible form values.
        $data['settings'] = array(
            'full' => 'full edit',
            'display' => 'display only',
            'none' => 'do not display',
        );

        // See, if we have any values in the configuration file.
        foreach ($pool->config->configFallback as $sectionName => $sectionSettings) {
            foreach ($sectionSettings as $settingName) {
                $settings[$sectionName][$settingName] = $pool->config->iniConfig->getFeConfigFromFile($settingName);
                if ($settings[$sectionName][$settingName] === null) {
                    $factory[$settingName] = ' checked="checked" ';
                    // We need to fill these values with the stuff from the
                    // factory settings!
                    $settings[$sectionName][$settingName] = $this->convertKrexxFeSetting(
                        $pool->config->feConfigFallback[$settingName][Fallback::RENDER]
                    );
                } else {
                    $factory[$settingName] = '';
                }
            }
        }

        $this->assign('data', $data);
        $this->assign('settings', $settings);
        $this->assign('factory', $factory);
    }

    /**
     * Converts the kreXX FE config setting.
     *
     * Letting people choose what kind of form element will
     * be used does not really make sense. We will convert the
     * original kreXX settings to a more useable form for the editor.
     *
     * @param array $values
     *   The values we want to convert.
     *
     * @return string|null
     *   The converted value.
     */
    protected function convertKrexxFeSetting($values)
    {
        $result = null;
        if (is_array($values)) {
            // The values are:
            // full    -> is editable and values will be accepted
            // display -> we will only display the settings
            // The original values include the name of a template partial
            // with the form element.
            if ($values[Fallback::RENDER_TYPE] == Fallback::RENDER_TYPE_NONE) {
                // It's not visible, thus we do not accept any values from it.
                $result = Fallback::RENDER_TYPE_NONE;
            }

            if ($values[Fallback::RENDER_EDITABLE] == 'true' && $values[Fallback::RENDER_TYPE] != Fallback::RENDER_TYPE_NONE) {
                // It's editable and visible.
                $result = 'full';
            }

            if ($values[Fallback::RENDER_EDITABLE] == 'false' && $values[Fallback::RENDER_TYPE] != Fallback::RENDER_TYPE_NONE) {
                // It's only visible.
                $result = 'display';
            }
        }

        return $result;
    }
}
