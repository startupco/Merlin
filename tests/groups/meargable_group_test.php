<?php

/**
 * Group Test Method
 *
 * PHP version 5.2.0 +
 *
 * LICENSE: Merlin :: merging system (http://primarymodules.com)
 * Copyright 2011, Primary Modules, Inc. (http://primarymodules.com)
 *
 * @category  Group_Test_Method
 * @package   Merlin
 * @author    Pooja Pednekar <ppednekar@primarymodules.com>
 * @copyright 2011 Primary Modules Inc.(http://primarymodules.com)
 * @license   http://primarymodules.com/licenses/merlin-prop.php	Merlin Proprietary License
 * @link      http://primarymodules.com/products/merlin
 * @since     Merlin Release 1.0
 */
require_once dirname(__FILE__) . '/../../libs/simpletest/autorun.php';
require_once dirname(__FILE__) . '/../../config/config.inc.php';

/**
 * Group Test Method
 *
 * @category Group_Test_Method
 * @package  Merlin
 * @author   Pooja Pednekar <ppednekar@primarymodules.com>
 * @license  http://primarymodules.com/licenses/merlin-prop.php	Merlin Proprietary License
 * @link     http://primarymodules.com/products/merlin
 *
 */
class AllMergeableTests extends TestSuite
{

    /**
     * AllMergeableTests calls the test cases
     *
     * @author Pooja Pednekar
     * @access public
     *
     * @return null
     */
    function AllMergeableTests()
    {
        $this->TestSuite('All Mergeable Tests');
        TestSuite::addFile(
            dirname(__FILE__) . '/../cases/docx_get_methods_test.php'
        );
        TestSuite::addFile(
            dirname(__FILE__) . '/../cases/docx_set_methods_test.php'
        );
        TestSuite::addFile(
            dirname(__FILE__) . '/../cases/pptx_get_methods_test.php'
        );
        TestSuite::addFile(
            dirname(__FILE__) . '/../cases/pptx_set_methods_test.php'
        );
    }

}

?>