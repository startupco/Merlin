<?php

/**
 * Mergeable Functionality
 *
 * Handles merging of mergeable documents
 *
 * PHP version 5.2.0 +
 *
 * LICENSE: Merlin :: merging system (http://primarymodules.com)
 * Copyright 2011, Primary Modules, Inc. (http://primarymodules.com)
 *
 * @category   Merging_Tool
 * @package    Merlin
 * @subpackage Merlin.mergeables
 * @author     Pooja Pednekar <ppednekar@primarymodules.com>
 * @copyright  2011 Primary Modules Inc.(http://primarymodules.com)
 * @license    http://primarymodules.com/licenses/merlin-prop.php	Merlin Proprietary License
 * @link       http://primarymodules.com/products/merlin
 * @since      Merlin Release 1.0
 */
require_once dirname(dirname(__FILE__)) . '/config/config.inc.php';
require_once dirname(dirname(__FILE__)) . '/libs/primod/utils.php';
require_once dirname(dirname(__FILE__)) . '/libs/primod/string.php';
global $debug;
if ($debug == 1) {
    include_once dirname(dirname(__FILE__)) . '/libs/primod/debug_utils.php';
}
/**
 * Mergeables merges the mergeable documents
 *
 * @category Merging_Tool
 * @package  Merlin
 * @author   Pooja Pednekar <ppednekar@primarymodules.com>
 * @license  http://primarymodules.com/licenses/merlin-prop.php	Merlin Proprietary License
 * @link     http://primarymodules.com/products/merlin
 *
 */
abstract class Mergeables
{

    /**
     * File type of the mergeable file.
     * @var string
     * @access public
     */
    var $type = '';
    /**
     * Name of the input mergeable file
     * @var string
     * @access public
     */
    var $file = '';
    /**
     * valid input types
     * @var array
     * @access private
     */
    private $_validInputTypes = array('docx', 'pptx', 'pdf');
    /**
     * valid output types
     * @var array
     * @access private
     */
    private $_validOutputTypes = array('docx', 'pptx', 'pdf');
    /**
     * Flag idicating if errors occured while merging
     * @var boolean
     * @access public
     */
    public static $errors;
    /**
     * array holding all the erros that ocuured while merging
     * @var array
     * @access public
     */
    public static $errorArray;

    /**
     * Initialises params for source/destination files
     *
     * @param string $fileName Name of the mergeable file
     * @param string $type     File type of the mergeable
     *
     * @return null
     *
     */
    public function __construct($fileName='', $type='')
    {
        $this->file = $fileName;
        if (empty($type)) {
            $fileParts = explode('.', $fileName);
            $this->type = $fileParts[count($fileParts) - 1];
        } else {
            $this->type = $type;
        }
    }

    /**
     * validates source/destination documents
     * 
     * @param object $mergeObj validates mergeable object
     *
     * @return boolean
     *
     */
    protected function validate($mergeObj)
    {
        /*
         * 10. Checks if the output file exists.
         *  10.1 Validates output file types.
         *  10.2 Validates if the input file exists.
         *  10.3 Validates input file types.
         *  10.4 Checks if input and output types match.
         */
        if (self::$errors !== true) {
            //10. validates if the file exists.
            if (file_exists($this->file)) {
                // 10.1 Validates output file types.
                if (in_array($this->type, $this->_validOutputTypes)) {
                    // 10.2 Validates if the input file exists.
                    if (is_object($mergeObj) && file_exists($mergeObj->file)) {
                        //10.3 Validates input file types.
                        if (in_array(
                            $mergeObj->type, $mergeObj->_validInputTypes
                        )) {
                            //10.4 Checks if input and output types match.
                            if (trim($mergeObj->type) == trim($this->type)) {
                                return true;
                            } else {
                                self::$errors = true;
                                self::$errorArray[] = "Output file type
                                      {$mergeObj->type} does not match input
                                      file type {$this->type}.";
                                return false;
                            }
                        } else {
                            self::$errors = true;
                            self::$errorArray[] = "File type {$mergeObj->type} of
                                  {$mergeObj->file} is not supported.";
                            return false;
                        }
                    } else {
                        self::$errors = true;
                        self::$errorArray[] = "File {$mergeObj->file} doesn't
                              exist.Check if the file exits in the specified
                              location with proper permissions(r/w/x).";
                        return false;
                    }
                } else {
                    self::$errors = true;
                    self::$errorArray[] = "File type {$this->type} of
                                        {$this->file} is not supported.";
                    return false;
                }
            } else {
                if (file_exists($mergeObj->file)) {
                    return true;
                } else {
                    self::$errors = true;
                    self::$errorArray[] = "File {$mergeObj->file} doesn't exist.
                          Check if the file exits in the specified location with
                          proper permissions(r/w/x).";
                    return false;
                }
            }
        }
    }

    /**
     * Creates a file by appending $source file to $destination file
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
         * 20. Calls set method for each of the mergeable parts.
         */
        //10. Validates source and destination files.
        if ($this->validate($source)) {
            if (!file_exists($this->file) && file_exists($source->file)) {
                copy($source->file, $this->file);
            } else {
                if (!$this->package->open(
                    $this->file, ZIPARCHIVE::CHECKCONS | ZIPARCHIVE::CREATE
                )) {
                    self::$errors = true;
                    self::$errorArray[] = "Unable to find the {$this->type}
                          file {$this->file}";
                }
                global $merlin_default_output_dir;
                $tempFolder = tempnam(
                    $merlin_default_output_dir . 'temp/',
                    basename($this->file, '.' . $this->type)
                );
                @exec("chmod -R 0777 {$tempFolder}");
                deleteDirectory($tempFolder);
                $this->package->tempFolder = $tempFolder;
                $this->package->extractTo($tempFolder);
                @exec("chmod -R 0777 {$tempFolder}");
                //20. Calls set method for each of the mergeable parts.
                if (is_array($this->mergableParts) && !empty($this->mergableParts)) {
                    foreach ($this->mergableParts as
                    $mergeableFile => $mergeablePartDetails) {
                        if (!isset($this->InitialMergableParts) || in_array(
                            $mergeableFile, $this->InitialMergableParts
                        )) {
                            $this->appendPart($source, $mergeablePartDetails);
                        }
                    }
                }
                $source->package->close();
                if ($this->package->close() === false) {
                    self::$errors = true;
                    self::$errorArray[] = "Errors occured while generating
                          {$this->file} file.If the destination file if open,
                          close it and re-merge the files.";
                }
                exec("chmod -R 777 {$this->file}");
                @exec("chmod -R 0777 {$tempFolder}");
                deleteDirectory($tempFolder);
            }
        }
    }

    /**
     * Appends source contents to destination contents
     * 
     * @param object $source               source object
     * @param array  $mergeablePartDetails array containing mergeable part details
     *
     * @return null
     *
     */
    function appendPart($source, $mergeablePartDetails)
    {
        /*
         * 10. gets the simplexml object of  file of source and destination document
         * 20. calls respective method to merge xml of these files
         * 30. Writes the  merged xml to the destination
         */
        //10. gets the simplexml object of file of source and destination document
        extract($mergeablePartDetails);
        $destinationXML = $this->get($FilePath, $obj_xml);
        $sourceXML = $source->get($FilePath, $obj_xml);
        //20. calls respective method to merge xml of these files
        switch ($method) {
        case 'noDuplicateMerge':
                $x = noDuplicateMerge(
                    $destinationXML, $sourceXML, $FileTag, $this, $source, $flag
                );
            break;
        case 'mergePresentationPart':
                $x = mergePresentationPart(
                    $destinationXML, $sourceXML, $this, $source, $namespace
                );
            break;
        case 'contentMerge':
                $x = contentMerge($destinationXML, $sourceXML, $this, $source);
            break;
        case 'noDuplicateMergeXML':
                $x = noDuplicateMergeXML(
                    $destinationXML, $sourceXML, $namespace, $flag
                );
            break;
        case 'duplicateMergeXML':
                $x = duplicateMergeXML(
                    $destinationXML, $sourceXML, $namespace, $tags
                );
            break;
        case 'mergeThemes':
                $x = mergeThemes($destinationXML, $sourceXML);
            break;
        }
        //30. Writes the  merged xml to the destination
        $this->package->addFromString($FilePath, $x);
    }

    /**
     * returns simple xml object to rels file
     * 
     * @param string  $FilePath filepath inside the archive
     * @param boolean $obj_xml  simplexml object or XML
     * 
     * @return - returns simple xml object/XML
     *
     */
    function get($FilePath, $obj_xml)
    {
        /*
         * 10. returns simplexml object/XML for $FilePath file
         */
        if (isset($FilePath)) {
            if ($obj_xml) {
                return simplexml_load_string($this->package->getFromName($FilePath));
            } else {
                return $this->package->getFromName($FilePath);
            }
        }
    }
    /**
     * replaces the merge words with their values in the $file.
     * calls splitPhpVariables method to get all merge words
     * calls joinPhpVariables method join all file parts with merge data 
     * and writting it back to the source
     *
     * @param array $data merge data
     * @param array $file filename $flag
     * @param array $flag flag 
     *
     * @return null
     */
    function mergePhpVariablesUtil($data, $file= 'word/document.xml', $flag=false)
    {
        $w = preg_quote('$');
        $this->splitPhpVariables($file, "/\{{$w}[^\}]*\}/");
        $this->joinPhpVariables($data, $file);
        if ($flag) {
            if ($this->package->close() === false) {
                self::$errors = true;
                self::$errorArray[] = "Errors occured while generating
                              {$this->file} file.";
            }
            exec("chmod -R 777 {$this->file}");
        }
    }

    /**
     * join all file parts with merge data and writting it back to the source
     *
     * @param array $file    filename
     * @param array $pattern patern for merge words
     *
     * @return null
     */
    protected function splitPhpVariables($file, $pattern)
    {
        $fileContents = $this->package->getFromName($file);
        $fileContentsArray = string::split(
            $fileContents, array("merge_words" => $pattern)
        );
        if (is_array($fileContentsArray)) {
            if (isset($fileContentsArray[0])) {
                $this->contentParts = $fileContentsArray[0];
            }
            if (isset($fileContentsArray[1])) {
                $this->mergeWords = $fileContentsArray[1];
            }
        }
        $this->mergeWords["merge_words"] = string::cleanWords(
            $this->mergeWords["merge_words"]
        );
    }

    /**
     * join all file parts with merge data and writting it back to the source
     *
     * @param array $data merge data 
     * @param array $file filename 
     *
     * @return null
     */
    protected function joinPhpVariables($data, $file)
    {
        global $merlin_default_output_dir;
        $mergeWords = array();
        if (is_array($this->mergeWords["merge_words"])) {
            foreach ($this->mergeWords["merge_words"] as $k => $v) {
                $v = strip_tags($v);
                $keys = preg_match_all("/\[[^\]]*\]/i", $v, $matches);
                if (isset($matches[0]) && is_array($matches[0])) {
                    $matches = string::cleanWords(
                        $matches[0], array("[", "]")
                    );
                    $val = string::extract($data, $matches);
                    
                    if (!empty($val) && !is_array($val)) {
                        $v = str_replace(array('[', ']'), array('["', '"]'), $v);
                        eval('$this->mergeWords["merge_words"][$k]=' . $v . ';');
                    } else {
                        self::$errors = true;
                        self::$errorArray[] = "Merge Word {$v} did not have any value.";
                    }
                }
            }
        }
        $mergedFileContents = string::merge(
            $this->contentParts, $this->mergeWords
        );
        $this->package->addFromString(
            $file, $mergedFileContents
        );
    }

}

?>
