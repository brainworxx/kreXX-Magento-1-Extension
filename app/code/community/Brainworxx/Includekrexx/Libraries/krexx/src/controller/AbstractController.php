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

use Brainworxx\Krexx\Analyse\Caller\AbstractCaller;
use Brainworxx\Krexx\Service\Factory\Pool;
use Brainworxx\Krexx\View\Output\AbstractOutput;

/**
 * Methods for the "controller" that are not directly "actions".
 *
 * @package Brainworxx\Krexx\Controller
 */
abstract class AbstractController
{
    /**
     * Here we remember, if we are currently running a analysis.
     * The debug methods may trigger another run, and we may get into
     * trouble, memory or runtime wise.
     *
     * @var bool
     */
    public static $analysisInProgress = false;

    /**
     * Sends the output to the browser during shutdown phase.
     *
     * @var AbstractOutput
     */
    protected $outputService;

    /**
     * Have we already send the CSS and JS?
     *
     * @var bool
     */
    protected static $headerSend = false;

    /**
     * Here we store the fatal error handler.
     *
     * @var \Brainworxx\Krexx\Errorhandler\Fatal
     */
    protected $krexxFatal;

    /**
     * Stores whether out fatal error handler should be active.
     *
     * During a kreXX analysis, we deactivate it to improve performance.
     * Here we save, whether we should reactivate it.
     *
     * @var boolean
     */
    protected $fatalShouldActive = false;

    /**
     * Here we save all timekeeping stuff.
     *
     * @var array
     */
    protected static $timekeeping = array();

    /**
     * More timekeeping stuff.
     *
     * @var array
     */
    protected static $counterCache = array();

    /**
     * Our pool where we keep all relevant classes.
     *
     * @var Pool
     */
    protected $pool;

    /**
     * Finds our caller.
     *
     * @var AbstractCaller
     */
    protected $callerFinder;

    /**
     * Injects the pool.
     *
     * @param Pool $pool
     *   The pool, where we store the classes we need.
     */
    public function __construct(Pool $pool)
    {
        $this->pool = $pool;
        $this->callerFinder = $pool->createClass('Brainworxx\\Krexx\\Analyse\\Caller\\CallerFinder');

        // Register our output service.
        $this->outputService = $pool->createClass('Brainworxx\\Krexx\\View\\Output\\File');
    }

    /**
     * Simply outputs the Header of kreXX.
     *
     * @param string $headline
     *   The headline, displayed in the header.
     *
     * @return string
     *   The generated markup
     */
    protected function outputHeader($headline)
    {
        // Do we do an output as file?
        if (static::$headerSend) {
            return $this->pool->render->renderHeader('', $headline, '');
        }

        // Send doctype and css/js only once.
        static::$headerSend = true;
        return $this->pool->render->renderHeader('<!DOCTYPE html>', $headline, $this->outputCssAndJs());
    }

    /**
     * Simply renders the footer and output current settings.
     *
     * @param array $caller
     *   Where was kreXX initially invoked from.
     * @param bool $isExpanded
     *   Are we rendering an expanded footer?
     *   TRUE when we render the settings menu only.
     *
     * @return string
     *   The generated markup.
     */
    protected function outputFooter(array $caller, $isExpanded = false)
    {
        // Now we need to stitch together the content of the ini file
        // as well as it's path.
        $pathToIni = $this->pool->config->getPathToIniFile();
        if ($this->pool->fileService->fileIsReadable($pathToIni)) {
            $path = $this->pool->messages->getHelp('currentConfig');
        } else {
            // Project settings are not accessible
            // tell the user, that we are using fallback settings.
            $path = $this->pool->messages->getHelp('iniNotFound');
        }

        $model = $this->pool->createClass('Brainworxx\\Krexx\\Analyse\\Model')
            ->setName($path)
            ->setType($this->pool->fileService->filterFilePath($pathToIni))
            ->setHelpid('currentSettings')
            ->injectCallback(
                $this->pool->createClass('Brainworxx\\Krexx\\Analyse\\Callback\\Iterate\\ThroughConfig')
            );

        $configOutput = $this->pool->render->renderExpandableChild($model, $isExpanded);
        return $this->pool->render->renderFooter($caller, $configOutput, $isExpanded);
    }

    /**
     * Outputs the CSS and JS.
     *
     * @return string
     *   The generated markup.
     */
    protected function outputCssAndJs()
    {
        // Get the css file.
        $css = $this->pool->fileService->getFileContents(
            KREXX_DIR .
            'resources/skins/' .
            $this->pool->config->getSetting('skin') .
            '/skin.css'
        );
        // Remove whitespace.
        $css = preg_replace('/\s+/', ' ', $css);

        // Adding our DOM tools to the js.
        if ($this->pool->fileService->fileIsReadable(KREXX_DIR . 'resources/jsLibs/kdt.min.js')) {
            $jsFile = KREXX_DIR . 'resources/jsLibs/kdt.min.js';
        } else {
            $jsFile = KREXX_DIR . 'resources/jsLibs/kdt.js';
        }

        $jsCode = $this->pool->fileService->getFileContents($jsFile);

        // Krexx.js is comes directly form the template.
        $path = KREXX_DIR . 'resources/skins/' . $this->pool->config->getSetting('skin');
        if ($this->pool->fileService->fileIsReadable($path . '/krexx.min.js')) {
            $jsFile = $path . '/krexx.min.js';
        } else {
            $jsFile = $path . '/krexx.js';
        }

        $jsCode .= $this->pool->fileService->getFileContents($jsFile);

        return $this->pool->render->renderCssJs($css, $jsCode);
    }

    /**
     * Disables the fatal handler and the tick callback.
     *
     * We disable the tick callback and the error handler during
     * a analysis, to generate faster output. We also disable
     * other kreXX calls, which may be caused by the debug callbacks
     * to prevent kreXX from starting other kreXX calls.
     *
     * @return $this
     *   Return $this for chaining.
     */
    public function noFatalForKrexx()
    {
        if ($this->fatalShouldActive) {
            $this->krexxFatal->setIsActive(false);
            unregister_tick_function(array($this->krexxFatal, 'tickCallback'));
        }

        return $this;
    }

    /**
     * Re-enable the fatal handler and the tick callback.
     *
     * We disable the tick callback and the error handler during
     * a analysis, to generate faster output. We re-enable kreXX
     * afterwards, so the dev can use it again.
     *
     * @return $this
     *   Return $this for chaining.
     */
    public function reFatalAfterKrexx()
    {
        if ($this->fatalShouldActive) {
            $this->krexxFatal->setIsActive(true);
            register_tick_function(array($this->krexxFatal, 'tickCallback'));
        }

        return $this;
    }

    /**
     * The benchmark main function.
     *
     * @param array $timeKeeping
     *   The timekeeping array.
     *
     * @return array
     *   The benchmark array.
     *
     * @see http://php.net/manual/de/function.microtime.php
     * @author gomodo at free dot fr
     */
    protected function miniBenchTo(array $timeKeeping)
    {
        // Get the very first key.
        $start = key($timeKeeping);
        $totalTime = round((end($timeKeeping) - $timeKeeping[$start]) * 1000, 4);
        $result['url'] = $this->getCurrentUrl();
        $result['total_time'] = $totalTime;
        $prevMomentName = $start;
        $prevMomentStart = $timeKeeping[$start];

        foreach ($timeKeeping as $moment => $time) {
            if ($moment !== $start) {
                // Calculate the time.
                $percentageTime = round(((round(($time - $prevMomentStart) * 1000, 4) / $totalTime) * 100), 1);
                $result[$prevMomentName . '->' . $moment] = $percentageTime . '%';
                $prevMomentStart = $time;
                $prevMomentName = $moment;
            }
        }

        return $result;
    }

    /**
     * Return the current URL.
     *
     * @see http://stackoverflow.com/questions/6768793/get-the-full-url-in-php
     * @author Timo Huovinen
     *
     * @return string
     *   The current URL.
     */
    protected function getCurrentUrl()
    {
        return \Mage::helper('core/url')->getCurrentUrl();
    }
}
