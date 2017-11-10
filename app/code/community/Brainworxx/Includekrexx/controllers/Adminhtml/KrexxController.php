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
 * Magento backend controller for kreXX
 */
class Brainworxx_Includekrexx_Adminhtml_KrexxController extends Mage_Adminhtml_Controller_Action
{

    /**
     * List of all setting-manes for which we are accepting values.
     *
     * @var array
     */
    protected $_allowedSettingsNames = array(
        'skin',
        'maxfiles',
        'destination',
        'maxCall',
        'disabled',
        'detectAjax',
        'analyseProtected',
        'analysePrivate',
        'analyseTraversable',
        'debugMethods',
        'level',
        'analyseProtectedMethods',
        'analysePrivateMethods',
        'registerAutomatically',
        'backtraceAnalysis',
        'analyseConstants',
        'iprange',
        'memoryLeft',
        'maxRuntime',
        'useScopeAnalysis',
        'analyseGetter',
        'maxStepNumber',
    );

    /**
     * List of all sections for which we are accepting values
     *
     * @var array
     */
    protected $_allowedSections = array(
        'runtime',
        'output',
        'properties',
        'methods',
        'backtraceAndError',
    );

    /**
     * Internal security call, to show if the current backenduser is allowed here.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        $actionName = $this->getFullActionName();

        if ($actionName == 'adminhtml_krexx_config') {
            return Mage::getSingleton('admin/session')->isAllowed('system/krexx/edit');
        }

        if ($actionName == 'adminhtml_krexx_feconfig') {
            return Mage::getSingleton('admin/session')->isAllowed('system/krexx/editfe');
        }

        if ($actionName == 'adminhtml_krexx_editlocalconfig') {
            return Mage::getSingleton('admin/session')->isAllowed('system/krexx/editlocalconfig');
        }

        if ($actionName == 'adminhtml_krexx_docu') {
            return Mage::getSingleton('admin/session')->isAllowed('system/krexx/docu');
        }

        // Still here?
        return parent::_isAllowed();
    }

    /**
     * Standard initilaizing actions.
     *
     * @return Brainworxx_Includekrexx_Adminhtml_KrexxController
     *   Return $this for chaining.
     */
    protected function init()
    {
        Mage::helper('includekrexx')->relayMessages();

        $this->loadLayout();
        $this->_setActiveMenu('system/krexxdocu');
        $this->_addBreadcrumb(
            Mage::helper('adminhtml')->__('System'),
            Mage::helper('includekrexx')->__('kreXX quick docu')
        );
        return $this;
    }

    /**
     * The docu action only displays the help text.
     */
    public function docuAction()
    {
        $this->init();
        $this->getLayout()->getBlock('head')->setTitle(Mage::helper('includekrexx')->__('kreXX quick docu'));
        $this->renderLayout();
    }

    /**
     * The edit action displays configuration editor.
     */
    public function configAction()
    {
        $this->init();
        $this->getLayout()->getBlock('head')->setTitle(Mage::helper('includekrexx')->__('Edit kreXX Config File'));
        $this->renderLayout();
    }

    /**
     * Displays the fe editing config form.
     */
    public function feconfigAction()
    {
        $this->init();
        $this->getLayout()->getBlock('head')->setTitle(Mage::helper('includekrexx')->__('Administer krexX FE editing'));
        $this->renderLayout();
    }

    /**
     * Displays the krexx::editSettings() as well as some help text.
     */
    public function editlocalconfigAction()
    {
        $this->init();
        $this->getLayout()->getBlock('head')->setTitle(Mage::helper('includekrexx')->__('Edit local browser settings'));
        $this->renderLayout();

    }

    /**
     * Saves the form data.
     */
    public function saveconfigAction()
    {
        $arguments = $this->getRequest()->getPost();
        $allOk = true;
        $pool = \Krexx::$pool;

        $filepath = $pool->config->getPathToIniFile();
        // We must preserve the section 'feEditing'.
        // Everything else will be overwritten.
        $ioFile = new Varien_Io_File();
        if ($ioFile->fileExists($filepath)) {
            $iniParser = New Zend_Config_Ini($filepath);
            $values = $iniParser->toArray();
        } else {
            $values = array();
        }

        $values = array('feEditing' => $values['feEditing']);

        // Iterating through the form.
        foreach ($arguments as $section => $data) {
            if (is_array($data) && in_array($section, $this->_allowedSections)) {
                // We've got a section key.
                foreach ($data as $settingName => $setting) {
                    if (in_array($settingName, $this->_allowedSettingsNames)) {
                        // We escape the value, just in case, since we can not whitelist it.
                        $setting = htmlspecialchars(preg_replace('/\s+/', '', $setting));
                        // Evaluate the setting!
                        if ($pool->config->security->evaluateSetting($section, $settingName, $setting)) {
                            $values[$section][$settingName] = $setting;
                        } else {
                            // Validation failed! kreXX will generate a message, which we will
                            // display at the buttom.
                            $allOk = false;
                        }
                    }
                }
            }
        }

        $this->finalizeIni($filepath, $values, $allOk);
        $this->_redirect('*/*/config');

    }

    /**
     * Saves the fe editing config from the backendform.
     */
    public function savefeconfigAction()
    {
        $arguments = $this->getRequest()->getPost();
        $allOk = true;
        $pool = \Krexx::$pool;
        $filepath = $pool->config->getPathToIniFile();

        // Whitelist of the vales we are accepting.
        $allowedValues = array('full', 'display', 'none');

        // Get the old values . . .
        $ioFile = new Varien_Io_File();
        if ($ioFile->fileExists($filepath)) {
            $iniParser = New Zend_Config_Ini($filepath);
            $values = $iniParser->toArray();
            unset($values['feEditing']);
        } else {
            $values = array();
        }

        // We need to correct the allowed settings, since we do not allow anything.
        unset($this->_allowedSettingsNames['destination']);
        unset($this->_allowedSettingsNames['maxfiles']);
        unset($this->_allowedSettingsNames['debugMethods']);

        // Iterating through the form.
        foreach ($arguments as $key => $data) {
            if (is_array($data)) {
                foreach ($data as $settingName => $setting) {
                    if (in_array($setting, $allowedValues) && in_array($settingName, $this->_allowedSettingsNames)) {
                        // Whitelisted values are ok.
                        $values['feEditing'][$settingName] = $setting;
                    } else {
                        // Validation failed!
                        $allOk = false;
                        $pool->messages->addMessage(htmlentities($setting) . ' is not an allowed value!');
                    }
                }
            }
        }

        $this->finalizeIni($filepath, $values, $allOk);
        $this->_redirect('*/*/feconfig');
    }

    /**
     * Either write the file, or give feedback of what went wrong.
     *
     * @param string $filepath
     *   The path to the ini file.
     * @param array $values
     *   The generated content of the ini file.
     * @param bool $allOk
     *   Did we encounter any errors during the $ini generation?
     */
    protected function finalizeIni($filepath, $values, $allOk)
    {
        $ini = '';
        $pool = \Krexx::$pool;
        foreach ($values as $key => $setting) {
            $ini .= '[' . $key . ']' . PHP_EOL;
            foreach ($setting as $settingName => $value) {
                $ini .= $settingName . ' = "' . $value . '"' . PHP_EOL;
            }
        }

        // Now we should write the file!
        if ($allOk) {
            $file = new Varien_Io_File();
            if ($file->write($filepath, $ini) === false) {
                $allOk = false;
                $pool->messages->addMessage('Configuration file ' . $filepath . ' is not writeable!');
            }
        }

        // Something went wrong, we need to tell the user.
        if (!$allOk) {
            Mage::getSingleton('core/session')->addError(
                strip_tags($pool->messages->outputMessages()),
                "The settings were NOT saved."
            );
        } else {
            Mage::getSingleton('core/session')->addSuccess(
                "The settings were saved to: <br /> " . $filepath,
                "The data was saved."
            );
        }
    }
}
