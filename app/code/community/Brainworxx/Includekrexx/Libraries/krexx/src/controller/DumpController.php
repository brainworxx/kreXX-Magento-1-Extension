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

namespace Brainworxx\Krexx\Controller;

/**
 * "Controller" for the dump (aka analysis) "action" ad the timer "actions".
 *
 * @package Brainworxx\Krexx\Controller
 */
class DumpController extends AbstractController
{
    /**
     * Dump information about a variable.
     *
     * Here everything starts and ends (well, unless we are only outputting
     * the settings editor).
     *
     * @param mixed $data
     *   The variable we want to analyse.
     * @param string $headline
     *   The headline of the markup we want to produce. Only used by the timer.
     *
     * @return $this;
     *   Return $this for chaining.
     */
    public function dumpAction($data, $headline = '')
    {
        if ($this->_pool->emergencyHandler->checkMaxCall()) {
            // Called too often, we might get into trouble here!
            return $this;
        }

        $this->_pool->reset();

        // Find caller.
        $caller = $this->_callerFinder->findCaller();

        // Set the headline, if it's not set already.
        if (empty($headline)) {
            if (is_object($data)) {
                $headline = get_class($data);
            }

            // We are analysing stuff here.
            $caller['type'] = 'Analysis';
        } else {
            // Caller type is most likely the timer.
            $caller['type'] = $headline;
        }

        // We need to get the footer before the generating of the header,
        // because we need to display messages in the header from the configuration.
        $footer = $this->outputFooter($caller);
        $this->_pool->scope->setScope($caller['varname']);

        // Enable code generation only if we were able to determine the varname.
        if ($caller['varname'] !== '. . .') {
            // We were able to determine the variable name and can generate some
            // sourcecode.
            $headline = $caller['varname'];
        }

        // Start the magic.
        $analysis = $this->_pool->routing->analysisHub(
            $this->_pool->createClass('Brainworxx\\Krexx\\Analyse\\Model')
                ->setData($data)
                ->setName($caller['varname'])
        );

        // Detect the encoding on the start-chunk-string of the analysis
        // for a complete encoding picture.
        $this->_pool->chunks->detectEncoding($analysis);

        // Now that our analysis is done, we must check if there was an emergency
        // break.
        if ($this->_pool->emergencyHandler->checkEmergencyBreak()) {
            return $this;
        }

        // Add the caller as metadata to the chunks class. It will be saved as
        // additional info, in case we are logging to a file.
        $this->_pool->chunks->addMetadata($caller);

        $this->_outputService->addChunkString($this->outputHeader($headline));
        $this->_outputService->addChunkString($analysis);
        $this->_outputService->addChunkString($footer);

        return $this;
    }

    /**
     * Takes a "moment" for the benchmark test.
     *
     * @param string $string
     *   Defines a "moment" during a benchmark test.
     *   The string should be something meaningful, like "Model invoice db call".
     *
     * @return $this
     *   Return $this for chaining
     */
    public function timerAction($string)
    {
        // Did we use this one before?
        if (isset(static::$_counterCache[$string])) {
            // Add another to the counter.
            ++static::$_counterCache[$string];
            static::$_timekeeping['[' . static::$_counterCache[$string] . ']' . $string] = microtime(true);
        } else {
            // First time counter, set it to 1.
            static::$_counterCache[$string] = 1;
            static::$_timekeeping[$string] = microtime(true);
        }

        return $this;
    }

    /**
     * Outputs the timer
     *
     * @return $this
     *   Return $this for chaining
     */
    public function timerEndAction()
    {
        $this->timerAction('end');
        // And we are done. Feedback to the user.
        $this->dumpAction($this->miniBenchTo(static::$_timekeeping), 'kreXX timer');
        // Reset the timer vars.
        static::$_timekeeping = array();
        static::$_counterCache = array();

        return $this;
    }
}
