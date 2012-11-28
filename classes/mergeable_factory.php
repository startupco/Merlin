<?php

/**
 * Creates Object of the Mergeable
 *
 * PHP version 5.2.0 +
 *
 * LICENSE: Merlin :: merging system (http://primarymodules.com)
 * Copyright 2011, Primary Modules, Inc. (http://primarymodules.com)
 *
 * @category   Merging_Factory
 * @package    Merlin
 * @subpackage Merlin.MergeableFactory
 * @author     Pooja Pednekar <ppednekar@primarymodules.com>
 * @copyright  2011 Primary Modules Inc.(http://primarymodules.com)
 * @license    http://primarymodules.com/licenses/merlin-prop.php	Merlin Proprietary License
 * @link       http://primarymodules.com/products/merlin
 * @since      Merlin Release 1.0
 */
require_once dirname(dirname(__FILE__)) . '/config/config.inc.php';
require_once dirname(dirname(__FILE__)) . '/libs/primod/utils.php';

/**
 * Mergeables merges the mergeable documents
 *
 * @category Merging_Factory
 * @package  Merlin
 * @author   Pooja Pednekar <ppednekar@primarymodules.com>
 * @license  http://primarymodules.com/licenses/merlin-prop.php	Merlin Proprietary License
 * @link     http://primarymodules.com/products/merlin
 *
 */
class MergeableFactory
{

     /**
     * valid input types
     * @var array
     * @access private
     */
    static private $_validInputTypes = array('docx', 'pptx', 'pdf');
    
    /**
     * Creates child mergeable object based on the file type
     *
     * @param string $filename name of the pptx file
     * @param string $type     file type
     *
     * @return Child Mergeable Object
     *
     */
    static public function createMergeableObject($filename, $type='')
    {
        global $rootDirectory;
        if (empty($type)) {
            $fileParts = explode('.', $filename);
            $type = $fileParts[count($fileParts) - 1];
        }
        if (in_array($type, self::$_validInputTypes)) {
            $mergeableClass = toCamelCase($type) . 'Mergeables';
            include_once $rootDirectory .
              "/classes/mergeables/{$type}_mergeables.php";
            $instance = new $mergeableClass($filename, $type);
            return $instance;
        }
    }

}

?>
