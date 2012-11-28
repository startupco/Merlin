<?php

/**
 * Configuration for Mergeables
 *
 * PHP version 5.2.0 +
 *
 * LICENSE: Merlin :: merging system (http://primarymodules.com)
 * Copyright 2011, Primary Modules, Inc. (http://primarymodules.com)
 *
 * @category  Config
 * @package   Merlin
 * @author    Pooja Pednekar <ppednekar@primarymodules.com>
 * @copyright 2011 Primary Modules Inc.(http://primarymodules.com)
 * @license   http://primarymodules.com/licenses/merlin-prop.php	Merlin Proprietary License
 * @link      http://primarymodules.com/products/merlin
 * @since     Merlin Release 1.0
 */

/**
 * Merlin configuration defaults
 */
$rootDirectory = dirname(dirname(__FILE__));
$merlin_default_input_dir = $rootDirectory . '/files/';
$merlin_default_output_dir = $rootDirectory . '/files/';
$merlin_default_test_input_dir = $rootDirectory . '/tests/files/';
$merlin_default_test_output_dir = $rootDirectory . '/tests/files/';
/*debug = 0 no debug (default)
 *        1 debug messages
 */
$debug=0;
?>