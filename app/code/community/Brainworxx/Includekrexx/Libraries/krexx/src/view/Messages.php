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

namespace Brainworxx\Krexx\View;

use Brainworxx\Krexx\Service\Factory\Pool;

/**
 * Messaging system.
 *
 * @package Brainworxx\Krexx\View
 */
class Messages
{

    /**
     * Here we store all messages, which gets send to the output.
     *
     * @var array
     */
    protected $_messages = array();

    /**
     * The translatable keys for backend integration.
     *
     * @var array
     */
    protected $_keys = array();

    /**
     * A simple array to hold the values.
     *
     * @var array
     */
    protected $_helpArray = array();

    /**
     * Here we store all relevant data.
     *
     * @var Pool
     */
    protected $_pool;

    /**
     * Injects the pool and reads the language file.
     *
     * @param Pool $pool
     *   The pool, where we store the classes we need.
     */
    public function __construct(Pool $pool)
    {
        $this->_pool = $pool;
        $file = KREXX_DIR . 'resources/language/Help.ini';
        $this->_helpArray = (array)parse_ini_string(
            $this->_pool->fileService->getFileContents($file)
        );
    }

    /**
     * The message we want to add. It will be displayed in the output.
     *
     * @param string $key
     *   The message itself.
     * @param array $args
     *   The parameters for vsprintf().
     */
    public function addMessage($key, array $args = array())
    {
        // Add it to the keys, so the CMS can display it.
        $this->_keys[] = array('key' => $key, 'params' => $args);
        $this->_messages[] = $this->getHelp($key, $args);
    }

    /**
     * Removes a key from the key array.
     *
     * @param string $key
     *   The key we want to remove
     */
    public function removeKey($key)
    {
        unset($this->_keys[$key]);
    }

    /**
     * Getter for the language key array.
     *
     * @return array
     *   The language keys we added beforehand.
     */
    public function getKeys()
    {
        return $this->_keys;
    }

    /**
     * Renders the output of the messages.
     *
     * @return string
     *   The rendered html output of the messages.
     */
    public function outputMessages()
    {
        // Return the rendered messages.
        return $this->_pool->render->renderMessages($this->_messages);
    }

    /**
     * Returns the help text when found, otherwise returns an empty string.
     *
     * @param string $key
     *   The help ID from the array above.
     * @param  array $args
     *   THe replacement arguments for vsprintf().
     *
     * @return string
     *   The help text.
     */
    public function getHelp($key, array $args = array())
    {
        // Check if we can get a value, at all.
        if (empty($this->_helpArray[$key])) {
            return '';
        }

        // Return the value
        return vsprintf($this->_helpArray[$key], $args);
    }
}
