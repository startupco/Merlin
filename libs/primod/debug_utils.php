<?php

/**
 * Debug Util functions for Mergeable Functionality
 *
 * PHP version 5.2.0 +
 *
 * LICENSE: Merlin :: merging system (http://primarymodules.com)
 * Copyright 2011, Primary Modules, Inc. (http://primarymodules.com)
 *
 * @category  Utils
 * @package   Merlin
 * @author    Pooja Pednekar <ppednekar@primarymodules.com>
 * @copyright 2011 Primary Modules Inc.(http://primarymodules.com)
 * @license   http://primarymodules.com/licenses/merlin-prop.php	Merlin Proprietary License
 * @link      http://primarymodules.com/products/merlin
 * @since     Merlin Release 1.0
 */

/**
 * formats array for output - don't use except for debugging
 *
 * @param array $data data to be printed
 *
 * @return null
 *
 */
function pr($data)
{
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}

?>
