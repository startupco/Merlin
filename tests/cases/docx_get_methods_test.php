<?php

/**
 * Test Docx Get Method
 *
 * PHP version 5.2.0 +
 *
 * LICENSE: Merlin :: merging system (http://primarymodules.com)
 * Copyright 2011, Primary Modules, Inc. (http://primarymodules.com)
 *
 * @category  Docx_Get_Method_Tester
 * @package   Merlin
 * @author    Pooja Pednekar <ppednekar@primarymodules.com>
 * @copyright 2011 Primary Modules Inc.(http://primarymodules.com)
 * @license   http://primarymodules.com/licenses/merlin-prop.php	Merlin Proprietary License
 * @link      http://primarymodules.com/products/merlin
 * @since     Merlin Release 1.0
 */
require_once dirname(__FILE__)  . '/../../libs/simpletest/autorun.php';
require_once dirname(__FILE__)  . '/../../config/config.inc.php';
require_once dirname(__FILE__)  . '/../../libs/primod/utils.php';

/**
 * Test Docx Get Method
 *
 * @category Docx_Get_Method_Tester
 * @package  Merlin
 * @author   Pooja Pednekar <ppednekar@primarymodules.com>
 * @license  http://primarymodules.com/licenses/merlin-prop.php	Merlin Proprietary License
 * @link     http://primarymodules.com/products/merlin
 *
 */
class DocxGetTestCase extends UnitTestCase
{
    /**
     * Setup Method
     *
     * @author Pooja Pednekar
     * @access public
     *
     * @return null
     */
    function setUp()
    {
        global $merlin_default_test_input_dir;
        global $merlin_default_output_dir, $rootDirectory;
        include_once $rootDirectory . "/classes/mergeable_factory.php";
        $this->DocxMergeable = MergeableFactory::createMergeableObject(
            $merlin_default_test_input_dir . 'survey.docx'
        );
        $this->file1 = $merlin_default_test_input_dir . 'survey.docx';
        $this->package = new ZipArchive();
        if (!$this->package->open($this->file1)) {
            echo 'Unable to find the DOCX file';
        }
    }

    /**
     * Tests docx get method
     *
     * @author Pooja Pednekar
     * @access public
     *
     * @return null
     */

    public function testGetFileContents()
    {
        if (is_array($this->DocxMergeable->mergableParts)
            && !empty($this->DocxMergeable->mergableParts)
        ) {
            foreach ($this->DocxMergeable->mergableParts
            as $mergeableFile => $mergeableDetails
            ) {
                extract($mergeableDetails);
                $sourceXML = $this->DocxMergeable->get($FilePath, $obj_xml);
                if ($mergeableFile != 'docx') {
                    $sourceXmlTest = simplexml_load_string(
                        $this->DocxMergeable->package->getFromName($FilePath)
                    );
                    $sourceXML = objectsIntoArray($sourceXML);
                    $sourceXmlTest = objectsIntoArray($sourceXmlTest);
                    $diff = array_diff($sourceXML, $sourceXmlTest);
                    $this->assertTrue(
                        empty($diff) ? true : false,
                        "File contents of {$FilePath} file is not set correctly"
                    );
                } else {
                    $this->assertEqual(
                        strcmp(
                            trim($sourceXML),
                            trim(
                                $this->DocxMergeable->package->getFromName(
                                    $FilePath
                                )
                            )
                        ),
                        0, "File contents of {$FilePath} file is not set correctly"
                    );
                }
            }
        }
    }

}

?>