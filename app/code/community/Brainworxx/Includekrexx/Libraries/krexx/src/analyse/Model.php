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

namespace Brainworxx\Krexx\Analyse;

/**
 * Model for the view rendering
 *
 * @package Brainworxx\Krexx\Analyse
 */
class Model extends AbstractModel
{
    /**
     * The object/string/array/whatever we are analysing right now
     *
     * @var mixed
     */
    protected $_data;

    /**
     * The name/key of it.
     *
     * @var string|int
     */
    protected $_name = '';

    /**
     * The short result of the analysis.
     *
     * @var string
     */
    protected $_normal = '';

    /**
     * Additional data that gets added to the type. Normally something like
     * 'protected static final'
     *
     * @var string
     */
    protected $_additional = '';

    /**
     * The type of the variable we are analysing, in a string.
     *
     * @var string
     */
    protected $_type = '';

    /**
     * A unique ID for the dom. We use this one for recursion resolving via JS.
     *
     * @var string
     */
    protected $_domid = '';

    /**
     * Info, if we have "extra" data to render.
     *
     * @see render->renderSingleChild()
     *
     * @var bool
     */
    protected $_hasExtra = false;

    /**
     * Are we dealing with multiline code generation?
     *
     * @var integer
     */
    protected $_multiLineCodeGen = 0;

    /**
     * Defines if the content of the variable qualifies as a callback.
     *
     * @var bool
     */
    protected $_isCallback = false;

    /**
     * We need to know, if we are rendering the expandable child for the
     * constants. The code generation does special stuff there.
     *
     * @var bool
     */
    protected $_isMetaConstants = false;

    /**
     * Setter for the data.
     *
     * @param mixed $_data
     *   The current variable we are rendering.
     *
     * @return $this
     *   $this, for chaining.
     */
    public function setData(&$_data)
    {
        $this->_data = $_data;
        return $this;
    }

    /**
     * Getter for the data.
     *
     * @return mixed
     *   The variable, we are currently analysing.
     */
    public function &getData()
    {
        return $this->_data;
    }

    /**
     * Setter for the name.
     *
     * @param int|string $_name
     *   The name/key we are analysing.
     *
     * @return $this
     *   $this, for chaining.
     */
    public function setName($_name)
    {
        $this->_name = $_name;
        return $this;
    }

    /**
     * Getter for the name.
     *
     * @return int|string
     *   The name/key we are analysing.
     */
    public function &getName()
    {
        return $this->_name;
    }

    /**
     * Setter for normal.
     *
     * @param string $_normal
     *   The short result of the analysis.
     *
     * @return $this
     *   $this, for chaining.
     */
    public function setNormal($_normal)
    {
        $this->_normal = $_normal;
        return $this;
    }

    /**
     * Getter for normal.
     *
     * @return string
     *   The short result of the analysis.
     */
    public function getNormal()
    {
        return $this->_normal;
    }

    /**
     * Setter for additional.
     *
     * @param string $_additional
     *   The long result of the analysis.
     *
     * @return $this
     *   $this, for chaining.
     */
    public function setAdditional($_additional)
    {
        $this->_additional = $_additional;
        return $this;
    }

    /**
     * Getter for additional
     *
     * @return string
     *   The long result of the analysis.
     */
    public function getAdditional()
    {
        return $this->_additional;
    }

    /**
     * Setter for the type.
     *
     * @param string $_type
     *   The type of the variable we are analysing.
     *
     * @return $this
     *   $this, for chaining.
     */
    public function setType($_type)
    {
        $this->_type = $_type;
        return $this;
    }

    /**
     * Getter for the type.
     *
     * @return string
     *   The type of the variable we are analysing
     */
    public function getType()
    {
        return $this->_additional . $this->_type;
    }

    /**
     * Getter got connectorLeft.
     *
     * @return string
     *   The first connector.
     */
    public function getConnectorLeft()
    {
        return $this->_connectorService->getConnectorLeft();
    }

    /**
     * Getter for connectorRight.
     *
     * @param integer $cap
     *   Maximum length of all parameters. 0 means no cap.
     *
     * @return string
     *   The second connector.
     */
    public function getConnectorRight($cap = 0)
    {
        return $this->_connectorService->getConnectorRight($cap);
    }

    /**
     * Setter for domid.
     *
     * @param string $_domid
     *   The dom id, of cause.
     *
     * @return $this
     *   $this, for chaining.
     */
    public function setDomid($_domid)
    {
        $this->_domid = $_domid;
        return $this;
    }

    /**
     * Getter for domid.
     *
     * @return string
     *   The dom id, of cause.
     */
    public function getDomid()
    {
        return $this->_domid;
    }

    /**
     * Getter for the hasExtras property.
     *
     * @return bool
     *   Info for the render class, if we need to render the extras part.
     */
    public function getHasExtras()
    {
        return $this->_hasExtra;
    }

    /**
     * "Setter" for the hasExtras property.
     *
     * @return $this
     *   $this, for chaining.
     */
    public function hasExtras()
    {
        $this->_hasExtra = true;
        return $this;
    }

    /**
     * Getter for the multiline code generation.
     *
     * @return string
     */
    public function getMultiLineCodeGen()
    {
        return $this->_multiLineCodeGen;
    }

    /**
     * Setter for the multiline code generation.
     *
     * @param string $_multiLineCodeGen
     *   The constant from the Codegen class.
     *
     * @return $this
     *   $this, for chaining.
     */
    public function setMultiLineCodeGen($_multiLineCodeGen)
    {
        $this->_multiLineCodeGen = $_multiLineCodeGen;
        return $this;
    }

    /**
     * Getter for the $isCallback.
     *
     * @return boolean
     */
    public function getIsCallback()
    {
        return $this->_isCallback;
    }

    /**
     * Setter for the $isCallback.
     *
     * @param boolean $_isCallback
     */
    public function setIsCallback($_isCallback)
    {
        $this->_isCallback = $_isCallback;
    }

     /**
     * Setter for the $params. It is used in case we are connection a method or
     * closure.
     *
     * @param string $params
     *   The parameters as a sting.
     *
     * @return $this
     *   $this for chaining.
     */
    public function setConnectorParameters($params)
    {
        $this->_connectorService->setParameters($params);
        return $this;
    }

    /**
     * Getter for the connection parameters.
     *
     * @return string
     *   The connection parameters.
     */
    public function getConnectorParameters()
    {
        return $this->_connectorService->getParameters();
    }

    /**
     * Setter for the type we are rendering, using the class constants.
     *
     * @param string $type
     *
     * @return $this
     *   Return $this, for chaining.
     */
    public function setConnectorType($type)
    {
        $this->_connectorService->setType($type);
        return $this;
    }

    /**
     * Sets a special and custom connectorLeft. Only used for constants code
     * generation.
     *
     * @param string $string
     *
     * @return $this
     *   Return $this for chaining.
     */
    public function setCustomConnectorLeft($string)
    {
        $this->_connectorService->setCustomConnectorLeft($string);
        return $this;
    }

    /**
     * Getter for the Language of the connector service.
     *
     * @return string
     */
    public function getConnectorLanguage()
    {
        return $this->_connectorService->getLanguage();
    }

    /**
     * Getter for all parameters for the internal callback.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->_parameters;
    }

    /**
     * Getter for the isMetaConstants.
     *
     * @return bool
     *   True means that we are currently rendering the expandable child for
     *   the constants.
     */
    public function getIsMetaConstants()
    {
        return $this->_isMetaConstants;
    }

    /**
     * Setter for the isMetaConstants.
     *
     * @param bool $bool
     *   The value we want to set.
     * @return $this
     *   Return $this for chaining.
     */
    public function setIsMetaConstants($bool)
    {
        $this->_isMetaConstants = $bool;
        return $this;
    }
}
