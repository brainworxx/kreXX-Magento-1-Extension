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

use Brainworxx\Krexx\Service\Factory\Pool;
use Brainworxx\Krexx\Controller\AbstractController;
use Brainworxx\Krexx\Service\Overwrites;

// Include some files and set some internal values.
\Krexx::bootstrapKrexx();

/**
 * Public functions, allowing access to the kreXX debug features.
 *
 * @package Krexx
 */
class Krexx
{

    /**
     * Our pool where we keep all relevant classes.
     *
     * @internal
     *
     * @var Pool
     */
    public static $pool;

    /**
     * Create the pool, but only if it is not alredy there.
     *
     * @internal
     */
    public static function createPool()
    {
        if (static::$pool !== null) {
            // The ppol is there, do nothing.
            return;
        }

        // Create a new pool where we store all our classes.
        // We also need to check if we have an overwrite for the pool.
        if (empty(Overwrites::$classes['Brainworxx\\Krexx\\Service\\Factory\\Pool'])) {
            static::$pool = new Pool();
        } else {
            $classname = Overwrites::$classes['Brainworxx\\Krexx\\Service\\Factory\\Pool'];
            static::$pool = new $classname();
        }
    }

    /**
     * Includes all needed files and sets some internal values.
     *
     * @internal
     */
    public static function bootstrapKrexx()
    {
        // @todo Plesae read me
        // Hi everyone.
        //
        // First, I would like to thank you for your time.
        // kreXX itself is a php debugger lib, tailored to be used in any
        // environment, with minimized interference / interaction with the
        // hosting cms / shop / framework. At least it tries to.
        //
        // At this point I have fixed some of the most glaring EQP issues, but
        // as you can plainly see, there are at lot more around here.
        // So far, here are my main concernes from the code inspection:
        //
        // 1.) Autolaoding the kreXX sourcefile manually.
        // Loading these files manually is just revolting. Using an autoloader
        // here will still trigger zhe magento autoloader, causing a warning.
        // To be honest, I don't think we have the manpower to refactor kreXX
        // into a class structure which can be loaded by the native magento
        // autoloader.
        //
        // 2.) Using the register_shutdown_function.
        // Originally, kreXX was used with a direct output on the frontend,
        // doing the output in the shutdown phase. The frontend output was
        // removed, but the internal structure remains, and it's still used for
        // the writing of the logging file. Again, I'm not sure if we have the
        // manpower to fix this.
        //
        // 3.) File Handling all over the place.
        // The last big heap of issues is with file handling. I've tried to use
        // Varien_File_Io wherever I could. Any kind of feedback about this is
        // extremely welcome. I also tried to displace glob() with the glob
        // iterator, only to notice that it has problems during the shutdown
        // phase of PHP, forcing a rollback commit, a few weeks ago.
        //
        // I am truly sorry, if I have just wasted everyone's time with this.
        //
        // Tobi

        // There may or may not be an active autoloader, which may or may not
        // be able to autolaod the krexx files. There amy or may not be an
        // unwanted interaction with the rest of the system when registering
        // another autoloader. This leaves us with loading every single file
        // via include_once.
        define('KREXX_DIR', __DIR__ . '/');
        include_once 'src/Analyse/Callback/AbstractCallback.php';

        include_once 'src/Analyse/Callback/Analyse/Objects/AbstractObjectAnalysis.php';
        include_once 'src/Analyse/Callback/Analyse/Objects/Constants.php';
        include_once 'src/Analyse/Callback/Analyse/Objects/DebugMethods.php';
        include_once 'src/Analyse/Callback/Analyse/Objects/Getter.php';
        include_once 'src/Analyse/Callback/Analyse/Objects/Methods.php';
        include_once 'src/Analyse/Callback/Analyse/Objects/PrivateProperties.php';
        include_once 'src/Analyse/Callback/Analyse/Objects/ProtectedProperties.php';
        include_once 'src/Analyse/Callback/Analyse/Objects/PublicProperties.php';
        include_once 'src/Analyse/Callback/Analyse/Objects/Traversable.php';

        include_once 'src/Analyse/Callback/Analyse/BacktraceStep.php';
        include_once 'src/Analyse/Callback/Analyse/ConfigSection.php';
        include_once 'src/Analyse/Callback/Analyse/Debug.php';
        include_once 'src/Analyse/Callback/Analyse/Objects.php';

        include_once 'src/Analyse/Callback/Iterate/ThroughArray.php';
        include_once 'src/Analyse/Callback/Iterate/ThroughConfig.php';
        include_once 'src/Analyse/Callback/Iterate/ThroughConstants.php';
        include_once 'src/Analyse/Callback/Iterate/ThroughGetter.php';
        include_once 'src/Analyse/Callback/Iterate/ThroughLargeArray.php';
        include_once 'src/Analyse/Callback/Iterate/ThroughMethodAnalysis.php';
        include_once 'src/Analyse/Callback/Iterate/ThroughMethods.php';
        include_once 'src/Analyse/Callback/Iterate/ThroughProperties.php';

        include_once 'src/Analyse/Caller/AbstractCaller.php';
        include_once 'src/Analyse/Caller/CallerFinder.php';

        include_once 'src/Analyse/Code/Codegen.php';
        include_once 'src/Analyse/Code/Connectors.php';
        include_once 'src/Analyse/Code/Scope.php';

        include_once 'src/Analyse/Comment/AbstractComment.php';
        include_once 'src/Analyse/Comment/Functions.php';
        include_once 'src/Analyse/Comment/Methods.php';
        include_once 'src/Analyse/Comment/Properties.php';

        include_once 'src/Analyse/Routing/AbstractRouting.php';
        include_once 'src/Analyse/Routing/Routing.php';

        include_once 'src/Analyse/Routing/Process/AbstractProcess.php';
        include_once 'src/Analyse/Routing/Process/ProcessArray.php';
        include_once 'src/Analyse/Routing/Process/ProcessBacktrace.php';
        include_once 'src/Analyse/Routing/Process/ProcessBoolean.php';
        include_once 'src/Analyse/Routing/Process/ProcessClosure.php';
        include_once 'src/Analyse/Routing/Process/ProcessFloat.php';
        include_once 'src/Analyse/Routing/Process/ProcessInteger.php';
        include_once 'src/Analyse/Routing/Process/ProcessNull.php';
        include_once 'src/Analyse/Routing/Process/ProcessObject.php';
        include_once 'src/Analyse/Routing/Process/ProcessResource.php';
        include_once 'src/Analyse/Routing/Process/ProcessString.php';

        include_once 'src/Analyse/AbstractModel.php';
        include_once 'src/Analyse/Model.php';

        include_once 'src/Controller/AbstractController.php';
        include_once 'src/Controller/BacktraceController.php';
        include_once 'src/Controller/DumpController.php';
        include_once 'src/Controller/EditSettingsController.php';
        include_once 'src/Controller/ErrorController.php';

        include_once 'src/Errorhandler/AbstractError.php';
        include_once 'src/Errorhandler/Fatal.php';

        include_once 'src/Service/Config/Fallback.php';
        include_once 'src/Service/Config/Config.php';
        include_once 'src/Service/Config/Model.php';
        include_once 'src/Service/Config/Security.php';

        include_once 'src/Service/Config/From/Cookie.php';
        include_once 'src/Service/Config/From/Ini.php';

        include_once 'src/Service/Factory/Factory.php';
        include_once 'src/Service/Factory/Pool.php';

        include_once 'src/Service/Flow/Emergency.php';
        include_once 'src/Service/Flow/Recursion.php';

        include_once 'src/Service/Misc/Encoding.php';
        include_once 'src/Service/Misc/File.php';
        include_once 'src/Service/Misc/Registry.php';

        include_once 'src/Service/Overwrites.php';

        include_once 'src/View/Output/AbstractOutput.php';
        include_once 'src/View/Output/Chunks.php';
        include_once 'src/View/Output/File.php';

        include_once 'src/View/RenderInterface.php';
        include_once 'src/View/AbstractRender.php';
        include_once 'src/View/Messages.php';
        include_once 'src/View/Render.php';

        if (!function_exists('krexx')) {
            /**
             * Alias function for object analysis.
             *
             * Register an alias function for object analysis,
             * so you will not have to type \Krexx::open($data);
             * all the time.
             *
             * @param mixed $data
             *   The variable we want to analyse.
             * @param string $handle
             *   The developer handle.
             */
            function krexx($data = null, $handle = '')
            {
                if (empty($handle)) {
                    \Krexx::open($data);
                } else {
                    \Krexx::$handle($data);
                }
            }
        }
    }

    /**
     * Handles the developer handle.
     *
     * @api
     *
     * @param string $name
     *   The name of the static function which was called.
     * @param array $arguments
     *   The arguments of said function.
     */
    public static function __callStatic($name, array $arguments)
    {
        static::createPool();

        // Do we gave a handle?
        if ($name === static::$pool->config->getDevHandler()) {
            // We do a standard-open.
            if (isset($arguments[0])) {
                static::open($arguments[0]);
            } else {
                static::open();
            }
        }
    }

    /**
     * Takes a "moment".
     *
     * @api
     *
     * @param string $string
     *   Defines a "moment" during a benchmark test.
     *   The string should be something meaningful, like "Model invoice db call".
     */
    public static function timerMoment($string)
    {
        static::createPool();

        // Disabled?
        if (static::$pool->config->getSetting('disabled') || AbstractController::$analysisInProgress) {
            return;
        }

        AbstractController::$analysisInProgress = true;

        static::$pool->createClass('Brainworxx\\Krexx\\Controller\\DumpController')
            ->noFatalForKrexx()
            ->timerAction($string)
            ->reFatalAfterKrexx();

        AbstractController::$analysisInProgress = false;
    }

    /**
     * Takes a "moment" and outputs the timer.
     *
     * @api
     */
    public static function timerEnd()
    {
        static::createPool();

        // Disabled ?
        if (static::$pool->config->getSetting('disabled') || AbstractController::$analysisInProgress) {
            return;
        }

        AbstractController::$analysisInProgress = true;

        static::$pool->createClass('Brainworxx\\Krexx\\Controller\\DumpController')
            ->noFatalForKrexx()
            ->timerEndAction()
            ->reFatalAfterKrexx();

        AbstractController::$analysisInProgress = false;
    }

    /**
     * Starts the analysis of a variable.
     *
     * @api
     *
     * @param mixed $data
     *   The variable we want to analyse.
     */
    public static function open($data = null)
    {
        static::createPool();

        // Disabled?
        if (static::$pool->config->getSetting('disabled') || AbstractController::$analysisInProgress) {
            return;
        }

        AbstractController::$analysisInProgress = true;

        static::$pool->createClass('Brainworxx\\Krexx\\Controller\\DumpController')
            ->noFatalForKrexx()
            ->dumpAction($data)
            ->reFatalAfterKrexx();

        AbstractController::$analysisInProgress = false;
    }

    /**
     * Prints a debug backtrace.
     *
     * When there are classes found inside the backtrace,
     * they will be analysed.
     *
     * @api
     *
     */
    public static function backtrace()
    {
        static::createPool();

        // Disabled?
        if (static::$pool->config->getSetting('disabled') || AbstractController::$analysisInProgress) {
            return;
        }

        AbstractController::$analysisInProgress = true;

        static::$pool->createClass('Brainworxx\\Krexx\\Controller\\BacktraceController')
            ->noFatalForKrexx()
            ->backtraceAction()
            ->reFatalAfterKrexx();

        AbstractController::$analysisInProgress = false;
    }

    /**
     * Disable kreXX.
     *
     * @api
     */
    public static function disable()
    {
        static::createPool();

        static::$pool->config->setDisabled(true);
        static::$pool->createClass('Brainworxx\\Krexx\\Controller\\DumpController')
            ->noFatalForKrexx();
        // We will not re-enable it afterwards, because kreXX
        // is disabled and the handler would not show up anyway.
    }

    /**
     * Displays the edit settings part, no analysis.
     *
     * Ignores the 'disabled' settings in the cookie.
     *
     * @api
     */
    public static function editSettings()
    {
        static::createPool();

        // Disabled?
        // We are ignoring local settings here.
        if (static::$pool->config->getSetting('disabled')) {
            return;
        }

         static::$pool->createClass('Brainworxx\\Krexx\\Controller\\EditSettingsController')
            ->noFatalForKrexx()
            ->editSettingsAction()
            ->reFatalAfterKrexx();
    }

    /**
     * Registers a shutdown function.
     *
     * Our fatal errorhandler is located there.
     *
     * @api
     */
    public static function registerFatal()
    {
        static::createPool();

        // Disabled?
        if (static::$pool->config->getSetting('disabled')) {
            return;
        }

        // Wrong PHP version?
        if (version_compare(phpversion(), '7.0.0', '>=')) {
            static::$pool->messages->addMessage('php7');
            // In case that there is no other kreXX output, we show the configuration
            // with the message.
            static::editSettings();
            return;
        }

        static::$pool->createClass('Brainworxx\\Krexx\\Controller\\ErrorController')
            ->registerFatalAction();
    }

    /**
     * Tells the registered shutdown function to do nothing.
     *
     * We can not unregister a once declared shutdown function,
     * so we need to tell our errorhandler to do nothing, in case
     * there is a fatal.
     *
     * @api
     */
    public static function unregisterFatal()
    {
        static::createPool();

        // Disabled?
        if (static::$pool->config->getSetting('disabled')) {
            return;
        }

        static::$pool->createClass('Brainworxx\\Krexx\\Controller\\ErrorController')
            ->unregisterFatalAction();
    }
}
