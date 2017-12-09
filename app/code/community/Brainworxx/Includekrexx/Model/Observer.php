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

use Brainworxx\Krexx\Service\Overwrites;

/**
 * Event observer for kreXX, includes the mainlibrary at an early level.
 */
class Brainworxx_Includekrexx_Model_Observer
{

    /**
     * @var Varien_Io_File
     */
    protected $_ioFile;

    /**
     * Create the io file object on creation.
     */
    public function __construct()
    {

        $this->_ioFile = new Varien_Io_File();
        $this->_ioFile->setAllowCreateFolders(true);

    }

    /**
     * Includes the kreXX mainfile
     *
     * @param Varien_Event_Observer $observer
     *   The event observer of the event we are listening to.
     */
    public function includeKreXX(Varien_Event_Observer $observer)
    {
        // We need to do this only once,
        // the static should save some time.
        static $beenHere = false;

        if (!$beenHere) {
            // We need to check, if the kreXX overwrite class, as well as the
            // main class have been loaded before. If not, load them.
            $libPath = Mage::getModuleDir('', 'Brainworxx_Includekrexx') .
                DIRECTORY_SEPARATOR . 'Libraries' . DIRECTORY_SEPARATOR;

            $pathToKrexx = $libPath . 'krexx/Krexx.php';
            if (!class_exists('\Krexx', false)) {
                include_once $pathToKrexx;
            }

            // Tell kreXX that we want to use some special classes for the getter analysis.
            Overwrites::$classes['Brainworxx\\Krexx\\Analyse\\Callback\\Iterate\\ThroughGetter'] =
                'Brainworxx_Includekrexx_Model_Dynamicgetter';
            Overwrites::$classes['Brainworxx\\Krexx\\Service\\Config\\Config'] =
                'Brainworxx_Includekrexx_Model_Config';

            // We will use the standard folders for cache or logging provided
            // by Magento.
            $cacheDir = Mage::getBaseDir('cache');
            $logDir = Mage::getBaseDir('log');

            // Check if these folders are protected.
            $this->processDir($cacheDir, '/krexx');
            $this->processDir($logDir, '/krexx');

            // Tell kreXX to use the now processed folders.
            Overwrites::$directories['chunks'] = $cacheDir;
            Overwrites::$directories['config'] = $logDir;
            Overwrites::$directories['log'] = $logDir;

            $beenHere = true;
        }
    }

    /**
     * Checks if a directory is protected. If not, create an index.php
     * an a .htaccess file.
     *
     * @param string $path
     *   The path to the directory we want to prectect.
     */
    protected function processDir($path, $newDir)
    {
        $path .= DIRECTORY_SEPARATOR;

        try {
            $this->_ioFile->open();
            $this->_ioFile->cd($path);
            $this->_ioFile->createDestinationDir($path . $newDir);
            $this->_ioFile->cd($path . $newDir);
        } catch (Exception $e) {
            // We have no write access here. Nothing more to do here.
            // kreXX will notice later on that he can not write here
            // and inform the dev.
            return;
        }

        // Empty index.html in case the htacess is not enough.
        if (!$this->_ioFile->fileExists('index.html', true)) {
            $indexHtml = '';
            $this->_ioFile->filePutContent('index.html', $indexHtml);
        }

        // htAccess to prevent a listing
        if (!$this->_ioFile->fileExists('.htaccess', true)) {
            $htAccess = 'order deny,allow' . "\n" . 'deny from all';
            $this->_ioFile->filePutContent('.htaccess', $htAccess);
        }
    }
}
