<?php

/**
 * PDF Mergeable Functionality
 *
 * Handles merging of pdf documents
 *
 * PHP version 5.2.0 +
 *
 * LICENSE: Merlin :: merging system (http://primarymodules.com)
 * Copyright 2011, Primary Modules, Inc. (http://primarymodules.com)
 *
 * @category   Pdf_Merging_Tool
 * @package    Merlin
 * @subpackage Merlin.PdfMergeables
 * @author     Pooja Pednekar <ppednekar@primarymodules.com>
 * @copyright  2011 Primary Modules Inc.(http://primarymodules.com)
 * @license    http://primarymodules.com/licenses/merlin-prop.php	Merlin Proprietary License
 * @link       http://primarymodules.com/products/merlin
 * @since      Merlin Release 1.0
 */
require_once dirname(dirname(__FILE__)) . '/mergeables.php';
require_once dirname(dirname(dirname(__FILE__))) . '/libs/Zend/Pdf.php';

/**
 * Pptx Mergeables merges the pptx documents
 *
 * @category Pdf_Merging_Tool
 * @package  Merlin
 * @author   Pooja Pednekar <ppednekar@primarymodules.com>
 * @license  http://primarymodules.com/licenses/merlin-prop.php	Merlin Proprietary License
 * @link     http://primarymodules.com/products/merlin
 *
 */
class PdfMergeables extends Mergeables
{

    /**
     * Instance of Zend_Pdf
     * @var object
     * @access private
     */
    private static $_instance;

    /**
     * Initialises file name and type
     *
     * @param string $filename name of the pptx file
     * @param string $type     file type
     *
     * @return null
     */
    public function __construct($filename, $type='')
    {
        parent::__construct($filename, $type);
    }

    /**
     * Creates a pdf by appending $source file to $destination file
     *
     * @param object $source - mergeable source obj
     *
     * @return null
     *
     */
    public function append($source)
    {
        /*
         * 10. Validates source and destination files.
         */
        //10. Validates source and destination files.
        if ($this->validate($source)) {
            if (!file_exists($this->file) && file_exists($source->file)) {
                copy($source->file, $this->file);
            } else {
                if (!isset(self::$_instance)) {
                    $_instance = new Zend_Pdf();
                }
                $filesToBeMerged = array($this->file, $source->file);
                foreach ($filesToBeMerged as $file) {
                    $pdf = Zend_Pdf::load($file);
                    $extractor = new Zend_Pdf_Resource_Extractor();
                    foreach ($pdf->pages as $page) {
                        $_instance->pages[] = $extractor->clonePage($page);
                    }
                }
                $_instance->save($this->file);
                exec("chmod -R 777 {$this->file}");
            }
        }
    }

}
?>

