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

namespace Brainworxx\Krexx\Service\Config;

/**
 * Model where we store our configuration.
 *
 * @package Brainworxx\Krexx\Service\Config
 */
class Model
{
    /**
     * The value of this setting.
     *
     * @var string
     */
    protected $_value;

    /**
     * The section of this setting.
     *
     * @var string
     */
    protected $_section;

    /**
     * The type of this setting.
     *
     * @var string
     */
    protected $_type;

    /**
     * Whether or not his setting is editable
     *
     * @var boolean
     */
    protected $_editable;

    /**
     * Source of this setting.
     *
     * @var string
     */
    protected $_source;

    /**
     * Setter for the editable value.
     *
     * @param boolean $_editable
     *
     * @return $this
     *   Return $this for Chaining.
     */
    public function setEditable($_editable)
    {
        $this->_editable = $_editable;
        return $this;
    }

    /**
     * Setter for the type.
     *
     * @param string $_type
     *
     * @return $this
     *   Return $this for Chaining.
     */
    public function setType($_type)
    {
        $this->_type = $_type;
        return $this;
    }

    /**
     * Setter for the value.
     *
     * @param string $_value
     *
     * @return $this
     *   Return $this for Chaining.
     */
    public function setValue($_value)
    {
        if ($_value === 'true') {
            $_value = true;
        }

        if ($_value === 'false') {
            $_value = false;
        }

        $this->_value = $_value;
        return $this;
    }

    /**
     * Getter for the editable value.
     *
     * @return boolean
     */
    public function getEditable()
    {
        return $this->_editable;
    }

    /**
     * Getter for the section.
     *
     * @return string
     */
    public function getSection()
    {
        return $this->_section;
    }

    /**
     * Getter for the type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Getter for the value.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * Setter for the section.
     *
     * @param string $_section
     *
     * @return $this
     *   Return $this for Chaining.
     */
    public function setSection($_section)
    {
        $this->_section = $_section;
        return $this;
    }

    /**
     * Getter for the source value.
     *
     * @return string
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * Setter for the source value.
     *
     * @param string $_source
     */
    public function setSource($_source)
    {
        $this->_source = $_source;
    }
}
