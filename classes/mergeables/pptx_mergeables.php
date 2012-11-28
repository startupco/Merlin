<?php

/**
 * Pptx Mergeable Functionality
 *
 * Handles merging of pptx documents
 *
 * PHP version 5.2.0 +
 *
 * LICENSE: Merlin :: merging system (http://primarymodules.com)
 * Copyright 2011, Primary Modules, Inc. (http://primarymodules.com)
 *
 * @category   Pptx_Merging_Tool
 * @package    Merlin
 * @subpackage Merlin.PptxMergeables
 * @author     Pooja Pednekar <ppednekar@primarymodules.com>
 * @copyright  2011 Primary Modules Inc.(http://primarymodules.com)
 * @license    http://primarymodules.com/licenses/merlin-prop.php	Merlin Proprietary License
 * @link       http://primarymodules.com/products/merlin
 * @since      Merlin Release 1.0
 */
require_once dirname(dirname(__FILE__)) . '/mergeables.php';

/**
 * Pptx Mergeables merges the pptx documents
 *
 * @category Pptx_Merging_Tool
 * @package  Merlin
 * @author   Pooja Pednekar <ppednekar@primarymodules.com>
 * @license  http://primarymodules.com/licenses/merlin-prop.php	Merlin Proprietary License
 * @link     http://primarymodules.com/products/merlin
 *
 */
class PptxMergeables extends Mergeables
{

    /**
     * List of files to be merged.
     * @var array
     * @access private
     */
    public $mergableParts = array(
        'rels' => array(
            'FilePath' => '_rels/.rels', 'method' => 'noDuplicateMerge',
            'FileTag' => '<Relationships 
                xmlns="http://schemas.openxmlformats.org/package/2006/relationships"
                ></Relationships>',
            'namespace' => null, 'obj_xml' => true, 'tags' => array(),
            'flag' => true
        ),
        'pptRels' => array(
            'FilePath' => 'ppt/_rels/presentation.xml.rels',
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
        'presentation' => array('FilePath' => 'ppt/presentation.xml',
            'method' => 'mergePresentationPart', 'FileTag' => null,
            'namespace' => 'p', 'obj_xml' => true, 'tags' => array(),
            'flag' => false
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
     * @param string $filename name of the pptx file
     * @param string $type     file type
     *
     * @return null
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
                self::$errorArray[] = "Unable to find the
                                            {$this->file} file {$this->file}";
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
        global $merlin_default_output_dir;
        $tempFolder = tempnam(
            $merlin_default_output_dir . 'temp/', basename(
                $this->file, '.' . $this->type
            )
        );
        @exec("chmod -R 0777 {$tempFolder}");
        $this->package->tempFolder = $tempFolder;
        deleteDirectory($tempFolder);
        $this->package->extractTo($tempFolder);
        $noOfSlides = getMaxFilesInDir($this->package, 'ppt/slides', ".xml");
        for ($i = 1; $i < $noOfSlides; $i++) {
            $this->mergePhpVariablesUtil($data, "ppt/slides/slide{$i}.xml");
        }
        if ($this->package->close() === false) {
            self::$errors = true;
            self::$errorArray[] = "Errors occured while generating
                          {$this->file} file.";
        }
        exec("chmod -R 777 {$this->file}");
        @exec("chmod -R 0777 {$this->package->tempFolder}");
        deleteDirectory($this->package->tempFolder);
    }
}

?>
