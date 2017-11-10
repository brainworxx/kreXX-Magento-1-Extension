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
 * Block for the backend lgfile access.
 */
class Brainworxx_Includekrexx_Block_Adminhtml_Log extends Mage_Adminhtml_Block_Template
{
    /**
     * Assign the values to the template file.
     *
     * @see Mage_Core_Block_Template::_construct()
     */
    public function _construct()
    {
        parent::_construct();

        // 1. Get the log folder.
        $dir = \Krexx::$pool->config->getLogDir();

        // 2. Get the file list and sort it.
        $ioFile = new Varien_Io_File();
        $ioFile->open(array('path' => $dir));
        $files = $ioFile->ls();

        // Filter them.
        foreach ($files as $key => $file) {
            if ($file['filetype'] !== 'html' ||
                strpos($file['text'], 'Krexx') === false
            ) {
                unset($files[$key]);
            }
        }

        // When we have no files, we stop right here.
        if (empty($files)) {
            $this->assign('files', array());
            return;
        }

        // The function filemtime gets cached by php btw.
        usort(
            $files,
            function ($a, $b) {
                return strtotime($b['mod_date']) - strtotime($a['mod_date']);
            }
        );

        // 3. Get the file info.
        $fileList = array();
        foreach ($files as $file) {
            $fileinfo = array();
            $fileObject = new Varien_File_Object($dir . $file['text']);

            // Getting the basic info.
            $fileinfo['name'] = $fileObject->getBasename();
            $fileinfo['size'] = $this->fileSizeConvert($fileObject->getSize());
            /** @var Mage_Core_Model_Date $dateTime */
            $dateTime = Mage::getSingleton('core/date');
            $fileinfo['time'] = $dateTime->date("d.m.y H:i:s", $fileObject->getMTime());
            $fileinfo['id'] = str_replace('.Krexx.html', '', $fileinfo['name']);

            // Parsing a potentialls 80MB file for it's content is not a good idea.
            // That is why the kreXX lib provides some meta data. We will open
            // this file and add it's content to the template.

            $fileinfo['meta'] = json_decode($ioFile->read($dir . $file['text'] . '.json'), true);
            foreach ($fileinfo['meta'] as &$meta) {
                $array = explode(DIRECTORY_SEPARATOR, $meta['file']);
                $array = array_values(array_slice($array, -1));
                $meta['filename'] = $array[0];
            }

            $fileList[] = $fileinfo;
        }

        // 4. Assign the flile list.
        $this->assign('files', $fileList);
    }

    /**
     * Converts bytes into human readable file size.
     *
     * @author Mogilev Arseny
     *
     * @param string $bytes
     *   The bytes value we want to make readable.
     *
     * @return string
     *   Human readable file size.
     */
    protected function fileSizeConvert($bytes)
    {
        $bytes = floatval($bytes);
        $arBytes = array(
            0 => array(
                "UNIT" => "TB",
                "VALUE" => pow(1024, 4),
            ),
            1 => array(
                "UNIT" => "GB",
                "VALUE" => pow(1024, 3),
            ),
            2 => array(
                "UNIT" => "MB",
                "VALUE" => pow(1024, 2),
            ),
            3 => array(
                "UNIT" => "KB",
                "VALUE" => 1024,
            ),
            4 => array(
                "UNIT" => "B",
                "VALUE" => 1,
            ),
        );

        $result = '';
        foreach ($arBytes as $aritem) {
            if ($bytes >= $aritem["VALUE"]) {
                $result = $bytes / $aritem["VALUE"];
                $result = str_replace(".", ",", (string)(round($result, 2))) . " " . $aritem["UNIT"];
                break;
            }
        }

        return $result;
    }
}
