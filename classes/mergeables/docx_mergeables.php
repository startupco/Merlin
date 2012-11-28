<?php

/**
 * Docx Mergeable Functionality
 *
 * Handles merging of pptx documents
 *
 * PHP version 5.2.0 +
 *
 * LICENSE: Merlin :: merging system (http://primarymodules.com)
 * Copyright 2011, Primary Modules, Inc. (http://primarymodules.com)
 *
 * @category   Docx_Merging_Tool
 * @package    Merlin
 * @subpackage Merlin.DocxMergeables
 * @author     Pooja Pednekar <ppednekar@primarymodules.com>
 * @copyright  2011 Primary Modules Inc.(http://primarymodules.com)
 * @license    http://primarymodules.com/licenses/merlin-prop.php	Merlin Proprietary License
 * @link       http://primarymodules.com/products/merlin
 * @since      Merlin Release 1.0
 */
require_once dirname(dirname(__FILE__)) . '/mergeables.php';

/**
 * Docx Mergeables merges the docx documents
 *
 * @category Docx_Merging_Tool
 * @package  Merlin
 * @author   Pooja Pednekar <ppednekar@primarymodules.com>
 * @license  http://primarymodules.com/licenses/merlin-prop.php	Merlin Proprietary License
 * @link     http://primarymodules.com/products/merlin
 *
 */
class DocxMergeables extends Mergeables
{

    /**
     * List of files to be merged.
     *
     * @var array
     * @access private
     */
    public $InitialMergableParts = array('rels', 'docRels', 'contentTypes');
    public $mergableParts = array(
        'rels' => array(
            'FilePath' => '_rels/.rels', 'method' => 'noDuplicateMerge',
            'FileTag' => '<Relationships 
                xmlns="http://schemas.openxmlformats.org/package/2006/relationships"
                ></Relationships>',
            'namespace' => null, 'obj_xml' => true, 'tags' => array(),
            'flag' => false
        ),
        'docRels' => array(
            'FilePath' => 'word/_rels/document.xml.rels',
            'method' => 'noDuplicateMerge',
            'FileTag' => '<Relationships 
                xmlns="http://schemas.openxmlformats.org/package/2006/relationships"
                ></Relationships>',
            'namespace' => null, 'obj_xml' => true, 'tags' => array(),
            'flag' => false
        ),
        'contentTypes' => array(
            'FilePath' => '[Content_Types].xml', 'method' => 'noDuplicateMerge',
            'FileTag' => '<Types 
                xmlns="http://schemas.openxmlformats.org/package/2006/content-types"
                ></Types>',
            'namespace' => null, 'obj_xml' => true, 'tags' => array(),
            'flag' => false
        ),
        'docx' => array('FilePath' => 'word/document.xml',
            'method' => 'contentMerge', 'FileTag' => null,
            'namespace' => null, 'obj_xml' => false, 'tags' => array(),
            'flag' => false
        ),
        'settings' => array('FilePath' => 'word/settings.xml',
            'method' => 'noDuplicateMergeXML', 'FileTag' => null,
            'namespace' => 'w', 'obj_xml' => true, 'tags' => array(),
            'flag' => false
        ),
        'webSettings' => array(
            'FilePath' => 'word/webSettings.xml',
            'method' => 'noDuplicateMergeXML', 'FileTag' => null,
            'namespace' => 'w', 'obj_xml' => true, 'tags' => array(),
            'flag' => false
        ),
        'fontTable' => array(
            'FilePath' => 'word/fontTable.xml',
            'method' => 'noDuplicateMergeXML', 'FileTag' => null,
            'namespace' => 'w', 'obj_xml' => true,
            'tags' => array(), 'flag' => true
        ),
        'core' => array(
            'FilePath' => 'docProps/core.xml',
            'method' => 'noDuplicateMergeXML', 'FileTag' => null,
            'namespace' => 'dc', 'obj_xml' => true, 'tags' => array(),
            'flag' => false
        ),
        'app' => array(
            'FilePath' => 'docProps/app.xml', 'method' => 'noDuplicateMergeXML',
            'FileTag' => null, 'namespace' => null, 'obj_xml' => true,
            'tags' => array(), 'flag' => false
        ),
        'styles' => array(
            'FilePath' => 'word/styles.xml', 'method' => 'duplicateMergeXML',
            'FileTag' => null, 'namespace' => 'w', 'obj_xml' => true,
            'tags' => array('style'), 'flag' => false
        ),
        'theme1' => array(
            'FilePath' => 'word/theme/theme1.xml',
            'method' => 'mergeThemes', 'FileTag' => null, 'namespace' => null,
            'obj_xml' => true, 'tags' => array(), 'flag' => false
        ),
        'footnotes' => array(
            'FilePath' => 'word/footnotes.xml',
            'method' => 'noDuplicateMergeXML', 'FileTag' => null,
            'namespace' => 'w', 'obj_xml' => true, 'tags' => array(),
            'flag' => false
        ),
        'endnotes' => array(
            'FilePath' => 'word/endnotes.xml',
            'method' => 'noDuplicateMergeXML', 'FileTag' => null,
            'namespace' => 'w', 'obj_xml' => true, 'tags' => array(),
            'flag' => false
        ),
        'numbering' => array(
            'FilePath' => 'word/numbering.xml', 'method' => 'duplicateMergeXML',
            'FileTag' => null, 'namespace' => 'w', 'obj_xml' => true,
            'tags' => array(), 'flag' => false
        ),
    );
    /**
     * Object pointing to the zipped folder
     * @var object
     * @access public
     */
    var $package = '';

    /**
     * Array holding content parts of the document(after extracting merge words)
     * @var array
     * @access public
     */
    var $contentParts = array();

    /**
     * Array holding mail merge words of the document
     * @var array
     * @access public
     */
    var $mergeWords = array();

    /**
     * Initialises the ZipArchive for source/destination files
     *
     * @param string $filename name of the docx file
     * @param string $type     file type 
     *
     * @return null
     * 
     */
    public function __construct($filename, $type='')
    {
        parent::__construct($filename, $type);
        $this->package = new ZipArchive();
        if (!empty($this->file) && file_exists($this->file)) {
            if (!$this->package->open(
                $this->file, ZIPARCHIVE::CHECKCONS | ZIPARCHIVE::CREATE
            )) {
                self::$errors = true;
                self::$errorArray[] = "Unable to find the {$this->file}
                                                            file {$this->file}";
            }
        }
    }
    
    /**
     * replaces the merge words with their values in the main document part.
     *
     * @param array $data merge data
     *
     * @return null
     */
    public function mergePhpVariables($data)
    {

        $this->mergePhpVariablesUtil($data, 'word/document.xml');

        if ($this->package->close() === false) {
            self::$errors = true;
            self::$errorArray[] = "Errors occured while generating
                          {$this->file} file.";
        }
        exec("chmod -R 777 {$this->file}");
    }

}

?>
