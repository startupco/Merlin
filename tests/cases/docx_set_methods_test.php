<?php

/**
 * Test Docx Set Method
 *
 * PHP version 5.2.0 +
 *
 * LICENSE: Merlin :: merging system (http://primarymodules.com)
 * Copyright 2011, Primary Modules, Inc. (http://primarymodules.com)
 *
 * @category  Docx_Set_Method_Tester
 * @package   Merlin
 * @author    Pooja Pednekar <ppednekar@primarymodules.com>
 * @copyright 2011 Primary Modules Inc.(http://primarymodules.com)
 * @license   http://primarymodules.com/licenses/merlin-prop.php	Merlin Proprietary License
 * @link      http://primarymodules.com/products/merlin
 * @since     Merlin Release 1.0
 */
require_once dirname(__FILE__) . '/../../libs/simpletest/autorun.php';
require_once dirname(__FILE__) . '/../../config/config.inc.php';
require_once dirname(__FILE__) . '/../../libs/primod/utils.php';

/**
 * Test Docx Set Method
 *
 * @category Docx_Set_Method_Tester
 * @package  Merlin
 * @author   Pooja Pednekar <ppednekar@primarymodules.com>
 * @license  http://primarymodules.com/licenses/merlin-prop.php	Merlin Proprietary License
 * @link     http://primarymodules.com/products/merlin
 *
 */
class DocxSetTestCase extends UnitTestCase
{

    /**
     * DocxSetTestCase Constructor
     *
     * @author Pooja Pednekar
     * @access public
     *
     * @return null
     */
    function DocxSetTestCase()
    {
        global $merlin_default_test_input_dir;
        global $merlin_default_output_dir, $rootDirectory;
        $this->source
            = $merlin_default_test_input_dir . 'default_files/Kates_horoscopes.docx';
        $this->file1
            = $merlin_default_test_input_dir . 'Kates_horoscopes.docx';
        $this->file2 = $merlin_default_test_input_dir . 'survey.docx';
        $this->file3
            = $merlin_default_test_input_dir . 'Kates_horoscopes_survey.docx';
        @unlink($this->file1);
        copy($this->source, $this->file1);
        exec("chmod -R 777 {$this->file1}");
        exec("chmod -R 777 {$merlin_default_test_input_dir}");
        include_once  $rootDirectory . "/classes/mergeable_factory.php";
    }

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
        $this->package2 = new ZipArchive();
        if (!$this->package2->open($this->file2)) {
            echo 'Unable to find the DOCX file';
            exit;
        }
        $this->package3 = new ZipArchive();
        if (!$this->package3->open($this->file3)) {
            echo 'Unable to find the DOCX file';
            exit;
        }
    }

    /**
     * Created mergeable object and Calls append method of the mergeable
     *
     * @author Pooja Pednekar
     * @access public
     *
     * @return null
     */
    public function testAppendMethods()
    {
        $objFile1 = MergeableFactory::createMergeableObject($this->file1);
        $objFile2 = MergeableFactory::createMergeableObject($this->file2);
        $objFile1->append($objFile2);
    }

    /**
     * Tests the file contents set by _rels/.rels file and _rels/.rels contents
     *
     * @author Pooja Pednekar
     * @access public
     * @return null
     */
    public function testRelsFunction()
    {
        $this->package = new ZipArchive();
        $this->package->open($this->file1);
        $this->assertEqual(
            strcmp(
                trim($this->package->getFromName('_rels/.rels')),
                trim($this->package3->getFromName('_rels/.rels'))
            ),
            0, 'SetRels did not set the relationships properly'
        );
        $this->assertEqual(
            strcmp(
                trim($this->package->getFromName('docProps/core.xml')),
                trim($this->package3->getFromName('docProps/core.xml'))
            ), 0, 'SetRels did not set the core properly'
        );
        $this->assertEqual(
            strcmp(
                trim($this->package->getFromName('docProps/app.xml')),
                trim($this->package3->getFromName('docProps/app.xml'))
            ),
            0, 'SetRels did not set the app properly'
        );
    }

    /**
     * Tests the file contents of [Content_Types].xml file
     *
     * @author Pooja Pednekar
     * @access public
     * @return null
     */
    public function testContentTypesFunction()
    {
        $rels = simplexml_load_string(
            $this->package->getFromName('[Content_Types].xml')
        );
        $testRels = simplexml_load_string(
            $this->package3->getFromName('[Content_Types].xml')
        );
        $rels = objectsIntoArray($rels);
        $testRels = objectsIntoArray($testRels);
        $diff = array_diff($rels, $testRels);
        $this->assertTrue(
            empty($diff) ? true : false,
            'SetContentTypes is not set correctly'
        );
    }

    /**
     * Tests the file contents set by word/_rels/document.xml.rels file
     * and word/_rels/document.xml.rels file contents
     *
     * @author Pooja Pednekar
     * @access public
     * @return null
     */
    public function testDocRelsFunction()
    {
        $this->assertEqual(
            strcmp(
                trim($this->package->getFromName('word/_rels/document.xml.rels')),
                trim($this->package3->getFromName('word/_rels/document.xml.rels'))
            ),
            0, 'SetDocRels did not set the relations properly'
        );
        $this->assertEqual(
            strcmp(
                trim($this->package->getFromName('word/document.xml')),
                trim($this->package3->getFromName('word/document.xml'))
            ),
            0, 'SetDocRels did not set the document contents properly'
        );
        $this->assertEqual(
            strcmp(
                trim($this->package->getFromName('word/settings.xml')),
                trim($this->package3->getFromName('word/settings.xml'))
            ),
            0, 'SetDocRels did not set the settings properly'
        );
        $this->assertEqual(
            strcmp(
                trim($this->package->getFromName('word/webSettings.xml')),
                trim($this->package3->getFromName('word/webSettings.xml'))
            ),
            0, 'SetDocRels did not set the webSettings properly'
        );
        $this->assertEqual(
            strcmp(
                trim($this->package->getFromName('word/fontTable.xml')),
                trim($this->package3->getFromName('word/fontTable.xml'))
            ), 0,
            'SetDocRels did not set the fontTable properly'
        );
        $this->assertEqual(
            strcmp(
                trim($this->package->getFromName('word/styles.xml')),
                trim($this->package3->getFromName('word/styles.xml'))
            ), 0,
            'SetDocRels did not set the styles properly'
        );
        $this->assertEqual(
            strcmp(
                trim($this->package->getFromName('word/theme/theme1.xml')),
                trim($this->package3->getFromName('word/theme/theme1.xml'))
            ), 0,
            'SetDocRels did not set theme properly'
        );
        $this->assertEqual(
            strcmp(
                trim($this->package->getFromName('word/footnotes.xml')),
                trim($this->package3->getFromName('word/footnotes.xml'))
            ), 0,
            'SetDocRels did not set footnotes properly'
        );
        $this->assertEqual(
            strcmp(
                trim($this->package->getFromName('word/endnotes.xml')),
                trim($this->package3->getFromName('word/endnotes.xml'))
            ), 0,
            'SetDocRels did not set endnotes properly'
        );
    }

}

?>