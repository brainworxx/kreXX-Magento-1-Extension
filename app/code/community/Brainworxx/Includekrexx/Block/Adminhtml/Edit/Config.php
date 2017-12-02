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

        // Initialzing help data for the template.
        $help['skin'] = htmlspecialchars(
            strip_tags(
                $pool->messages->getHelp(
                    'skinHelp'
                )
            )
        );
        $help['iprange'] = 'List of IPs that can trigger kreXX. Wildcards can be used.';
        $help['maxfiles'] = htmlspecialchars(
            strip_tags(
                $pool->messages->getHelp(
                    'maxfilesHelp'
                )
            )
        );
        $help['destination'] = htmlspecialchars(
            strip_tags(
                $pool->messages->getHelp(
                    'destinationHelp'
                )
            )
        );
        $help['maxCall'] = htmlspecialchars(
            strip_tags(
                $pool->messages->getHelp(
                    'maxCallHelp'
                )
            )
        );
        $help['disabled'] = 'Here you can disable kreXX without uninstalling the whole module.';
        $help['detectAjax'] = htmlspecialchars(
            strip_tags(
                $pool->messages->getHelp(
                    'detectAjaxHelp'
                )
            )
        );
        $help['analyseProtected'] = htmlspecialchars(
            strip_tags(
                $pool->messages->getHelp(
                    'analyseProtectedHelp'
                )
            )
        );
        $help['analysePrivate'] = htmlspecialchars(
            strip_tags(
                $pool->messages->getHelp(
                    'analysePrivateHelp'
                )
            )
        );
        $help['analyseTraversable'] = htmlspecialchars(
            strip_tags(
                $pool->messages->getHelp(
                    'analyseTraversableHelp'
                )
            )
        );
        $help['debugMethods'] = 'Comma-separated list of used debug callback functions. kreXX will try to call them,' .
            "if they are available and display their provided data.\nWe Recommend for Magento: '__toArray,toString'";
        $help['level'] = htmlspecialchars(
            strip_tags(
                $pool->messages->getHelp(
                    'levelHelp'
                )
            )
        );
        $help['analyseProtectedMethods'] = htmlspecialchars(
            strip_tags(
                $pool->messages->getHelp(
                    'analyseProtectedMethodsHelp'
                )
            )
        );
        $help['analysePrivateMethods'] = htmlspecialchars(
            strip_tags(
                $pool->messages->getHelp(
                    'analysePrivateMethodsHelp'
                )
            )
        );
        $help['analyseConstants'] = htmlspecialchars(
            strip_tags(
                $pool->messages->getHelp(
                    'analyseConstantsHelp'
                )
            )
        );
        $help['analyseGetter'] = htmlspecialchars(
            strip_tags(
                $pool->messages->getHelp(
                    'analyseGetterHelp'
                )
            )
        );
        $help['useScopeAnalysis'] = htmlspecialchars(
            strip_tags(
                $pool->messages->getHelp(
                    'useScopeAnalysisHelp'
                )
            )
        );
        $help['memoryLeft'] = htmlspecialchars(
            strip_tags(
                $pool->messages->getHelp(
                    'memoryLeftHelp'
                )
            )
        );
        $help['maxRuntime'] = htmlspecialchars(
            strip_tags(
                $pool->messages->getHelp(
                    'maxRuntimeHelp'
                )
            )
        );
        $help['maxStepNumber'] = htmlspecialchars(
            strip_tags(
                $pool->messages->getHelp(
                    'maxStepNumberHelp'
                )
            )
        );

        $this->assign('help', $help);

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

        // Get all values from the configuration file.
        $settings['output']['skin'] = $pool->config->iniConfig->getConfigFromFile(
            'output',
            'skin'
        );
        $settings['output']['maxfiles'] = $pool->config->iniConfig->getConfigFromFile(
            'output',
            'maxfiles'
        );
        $settings['output']['destination'] = $pool->config->iniConfig->getConfigFromFile(
            'output',
            'destination'
        );
        $settings['runtime']['maxCall'] = $pool->config->iniConfig->getConfigFromFile(
            'runtime',
            'maxCall'
        );
        $settings['output']['disabled'] = $pool->config->iniConfig->getConfigFromFile(
            'output',
            'disabled'
        );
        $settings['output']['iprange'] = $pool->config->iniConfig->getConfigFromFile(
            'output',
            'iprange'
        );
        $settings['runtime']['detectAjax'] = $pool->config->iniConfig->getConfigFromFile(
            'runtime',
            'detectAjax'
        );
        $settings['properties']['analyseProtected'] = $pool->config->iniConfig->getConfigFromFile(
            'properties',
            'analyseProtected'
        );
        $settings['properties']['analysePrivate'] = $pool->config->iniConfig->getConfigFromFile(
            'properties',
            'analysePrivate'
        );
        $settings['properties']['analyseConstants'] = $pool->config->iniConfig->getConfigFromFile(
            'properties',
            'analyseConstants'
        );
        $settings['properties']['analyseTraversable'] = $pool->config->iniConfig->getConfigFromFile(
            'properties',
            'analyseTraversable'
        );
        $settings['methods']['debugMethods'] = $pool->config->iniConfig->getConfigFromFile(
            'methods',
            'debugMethods'
        );
        $settings['runtime']['level'] = $pool->config->iniConfig->getConfigFromFile(
            'runtime',
            'level'
        );
        $settings['methods']['analyseProtectedMethods'] = $pool->config->iniConfig->getConfigFromFile(
            'methods',
            'analyseProtectedMethods'
        );
        $settings['methods']['analysePrivateMethods'] = $pool->config->iniConfig->getConfigFromFile(
            'methods',
            'analysePrivateMethods'
        );
        $settings['backtrace']['maxStepNumber'] = $pool->config->iniConfig->getConfigFromFile(
            'backtrace',
            'maxStepNumber'
        );
        $settings['methods']['analyseGetter'] = $pool->config->iniConfig->getConfigFromFile(
            'methods',
            'analyseGetter'
        );
        $settings['runtime']['useScopeAnalysis'] = $pool->config->iniConfig->getConfigFromFile(
            'runtime',
            'useScopeAnalysis'
        );
        $settings['runtime']['memoryLeft'] = $pool->config->iniConfig->getConfigFromFile(
            'runtime',
            'memoryLeft'
        );
        $settings['runtime']['maxRuntime'] = $pool->config->iniConfig->getConfigFromFile(
            'runtime',
            'maxRuntime'
        );

        // Are these actually set?
        foreach ($settings as $mainkey => $setting) {
            foreach ($setting as $attribute => $config) {
                if ($config === null) {
                    $factory[$attribute] = ' checked="checked" ';
                    // We need to fill these values with the stuff from the factory settings!
                    $settings[$mainkey][$attribute] = $pool->config->configFallback[$mainkey][$attribute];
                } else {
                    $factory[$attribute] = '';
                }
            }
        }

        // Add them to the template.
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
            '*/*/saveconfig', array(
                '_current' => true,
                'back' => null
            )
        );
    }
}
