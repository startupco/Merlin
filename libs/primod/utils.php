<?php

/**
 * Util functions for Mergeable Functionality
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
 * Translates a camel case string into a string with underscores
 *
 * @param string $str String in camel case format
 *
 * @return string $str Translated into underscore format
 *
 */
function fromCamelCase($str)
{
    $str[0] = strtolower($str[0]);
    $func = create_function('$c', 'return "_" . strtolower($c[1]);');
    return preg_replace_callback('/([A-Z])/', $func, $str);
}

/**
 * Translates a string with underscores into camel case
 *
 * @param string $str                   String in underscore format
 * @param bool   $capitalise_first_char If true, capitalise the first char
 *
 * @return   string   $str translated into camel caps
 *
 */
function toCamelCase($str, $capitalise_first_char = false)
{
    if ($capitalise_first_char) {
        $str[0] = strtoupper($str[0]);
    }
    $func = create_function('$c', 'return strtoupper($c[1]);');
    return preg_replace_callback('/_([a-z])/', $func, $str);
}

/**
 * Add child nodes to $schild from $style with all its attrs
 *
 * @param object &$schild    destination xml object
 * @param object $style      source xml object
 * @param array  $namespaces array of namespaces
 * @param string $ns         namespace prefix
 *
 * @return null
 *
 */
function addXmlChildNodes(&$schild, $style, $namespaces, $ns='w')
{
    $prepend = '';
    $nameSpace = null;
    if (isset($ns)) {
        if (isset($namespaces[$ns])) {
            $prepend = "{$ns}:";
            $nameSpace = $namespaces[$ns];
        }
    }
    foreach ($style->children($nameSpace) as $grandkey => $grandChild) {
        $gChild = $schild->addChild((string) $grandkey, '', $nameSpace);
        foreach ($grandChild->attributes($nameSpace) as $attrKey => $attr) {
            $gChild->addAttribute(
                $prepend . (string) $attrKey, (string) $attr, $nameSpace
            );
        }
        addXmlChildNodes($gChild, $grandChild, $namespaces, $ns);
    }
}

/**
 * function defination to convert array to xml
 *
 * @param array  $xml1Array  array to be converted
 * @param object &$xml_info  simplexml
 * @param string $parentNode parent xml node
 *
 * @return null
 *
 */
function arrayToXml($xml1Array, &$xml_info, $parentNode=null)
{
    foreach ($xml1Array as $key => $value) {
        if (is_array($value)) {
            if (!is_numeric($key)) {
                if ($key == '@attributes') {
                    foreach ($value as $attrIndex => $attrVal) {
                        $xml_info->addAttribute("$attrIndex", "$attrVal");
                    }
                    arrayToXml($value, $xml_info, $key);
                } elseif (count($value) == 1) {
                    $subnode = $xml_info->addChild($key);
                    if (isset($value['@attributes']) && is_array(
                        $value['@attributes']
                    )) {
                        foreach ($value['@attributes'] as $attrIndex => $attrVal) {
                            $subnode->addAttribute("$attrIndex", "$attrVal");
                        }
                    }
                } else {
                    arrayToXml($value, $xml_info, $key);
                }
            } else {
                $subnode = $xml_info->addChild("$parentNode");
                if (isset($value['@attributes']) && is_array(
                    $value['@attributes']
                )) {
                    foreach ($value['@attributes'] as $attrIndex => $attrVal) {
                        $subnode->addAttribute("$attrIndex", "$attrVal");
                    }
                }
            }
        }
    }
}

/**
 * Converts objects into array
 *
 * @param array $arrObjData     Object to be converted to array
 * @param array $arrSkipIndices indexes to be skipped
 *
 * @return array
 *
 */
function objectsIntoArray($arrObjData, $arrSkipIndices = array())
{
    $arrData = array();

    // if input is object, convert into array
    if (is_object($arrObjData)) {
        $arrObjData = get_object_vars($arrObjData);
    }

    if (is_array($arrObjData)) {
        foreach ($arrObjData as $index => $value) {
            if (is_object($value) || is_array($value)) {
                $value = objectsIntoArray($value, $arrSkipIndices); // recursive call
            }
            if (in_array($index, $arrSkipIndices)) {
                continue;
            }
            $arrData[$index] = $value;
        }
    }
    return $arrData;
}

/**
 * Gets absolute path in a Zip Archieve
 *
 * @param string $path path to be converted
 *
 * @return absolute path
 *
 */
function absoluteZipPath($path)
{
    $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
    $parts = array_filter(
        explode(DIRECTORY_SEPARATOR, $path), 'strlen'
    );
    $arrAbsolutes = array();
    foreach ($parts as $datParts) {
        if ('.' == $datParts) {
            continue;
        }
        if ('..' == $datParts) {
            array_pop($arrAbsolutes);
        } else {
            $arrAbsolutes[] = $datParts;
        }
    }
    return implode('/', $arrAbsolutes);
}

/**
 * Returns maximum relation ID
 *
 * @param object $simpleXMlObj simple xml object
 * @param string $attr         attribute to be acessed
 * @param string $nameSpace    name space
 * @param int    $max          max attribute value
 *
 * @return int
 *
 */
function getMaxIdFromChild($simpleXMlObj, $attr, $nameSpace=null, $max = 1)
{
    foreach ($simpleXMlObj->children($nameSpace) as $child) {
        $id = $child->attributes()->$attr;
        $id1 = preg_replace("/[^0-9]/", "", $id);
        if (((float) $id1) > $max) {
            $max = ((float) $id1);
        }
    }
    return $max;
}

/**
 * recursively deletes a directory
 *
 * @param string $dir directory name
 *
 * @return true/false
 *
 */
function deleteDirectory($dir)
{
    if (!file_exists($dir)) {
        return true;
    }
    if (!is_dir($dir) || is_link($dir)) {
        return unlink($dir);
    }
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        if (!deleteDirectory($dir . "/" . $item)) {
            chmod($dir . "/" . $item, 0777);
            if (!deleteDirectory($dir . "/" . $item)) {
                return false;
            }
        };
    }
    return rmdir($dir);
}

/**
 * Gets maximum files in a directory
 *
 * @param object $package ZipArchieve Object
 * @param string $dir     Directory name
 * @param string $ext     extension
 *
 * @return Maxmimum ID
 *
 */
function getMaxFilesInDir($package, $dir, $ext=".xml")
{
    $numFiles = 0;
    if (isset($package->tempFolder)) {
        if (glob($package->tempFolder . '/' . $dir) != false) {
            $numFiles = count(glob($package->tempFolder . '/' . $dir . "/*{$ext}"));
            if (strpos($dir, 'media') !== false) {
                $n1 = count(glob($package->tempFolder . '/' . $dir . "/*.jpeg"));
                $n2 = count(glob($package->tempFolder . '/' . $dir . "/*.png"));
                $n3 = count(glob($package->tempFolder . '/' . $dir . "/*.gif"));
                $n4 = count(glob($package->tempFolder . '/' . $dir . "/*.wav"));
                $n5 = count(glob($package->tempFolder . '/' . $dir . "/*.emf"));
                $n6 = count(glob($package->tempFolder . '/' . $dir . "/*.wmf"));
                $numFiles = $n1 + $n2 + $n3 + $n4 + $n5 + $n6;
            }
        }
    }
    $numFiles++;
    return $numFiles;
}

/**
 * merges xml file contents such that there are no duplicate nodes
 *
 * @param object  $destinationXML destination file Simple XMl Object
 * @param object  $sourceXML      source file Simple XMl Object
 * @param string  $fileTags       xml tags
 * @param object  $destination    detination object
 * @param object  $source         source object
 * @param boolean $baseRelFlag    flag
 *
 * @return string
 *
 */
function noDuplicateMerge($destinationXML, $sourceXML,
    $fileTags, $destination, $source, $baseRelFlag=false
) {
    /*
     * 10. Gets maximum relationship ID from $destinationXML
     * 20. Converts $destinationXML and $sourceXML into arrays
     * 30. Calls comparativeMerge for merging the two arrays
     * 40. Converts the merged array into Simple XML Object
     *
     */
    if (is_object($destinationXML)) {
        $destinationArray = array();
        $sourceArray = array();
        //10. Gets maximum relationship ID from $destinationXML
        $maxID = getMaxIdFromChild($destinationXML, 'Id');
        //20. Converts $destinationXML and $sourceXML into arrays
        $destinationArray = objectsIntoArray($destinationXML);
        $sourceArray = objectsIntoArray($sourceXML);
        //30. Calls comparativeMerge for merging the two arrays
        $function = "{$destination->type}ComparativeMerge";
        $destinationArray = $function(
                $destinationArray, $sourceArray, $destination,
                $source, $maxID + 1, $baseRelFlag
        );
        // creating object of SimpleXMLElement
        $param = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>" .
            $fileTags;
        //40. Converts the merged array into Simple XML Object
        $xml_info = new SimpleXMLElement($param);
        // function call to convert array to xml
        arrayToXml($destinationArray, $xml_info);
        //saving generated xml file
        return $xml_info->asXML();
    } else {
        return $sourceXML->asXML();
    }
}

/**
 * called by noDuplicateMergeXML
 *
 * @param object  &$destinationXML destination file Simple XMl Object
 * @param object  $sourceXML       source file Simple XMl Object
 * @param string  $ns              namespace
 * @param boolean $nodeAttr        merge based on attr names/parent nodes
 *
 * @return null
 *
 */
function noDuplicateMergeXML(&$destinationXML, $sourceXML, $ns=null, $nodeAttr=false)
{
    $parentNodes = array();
    $prepend = '';
    $nameSpace = null;
    $namespaces = $sourceXML->getNameSpaces(true);
    if (isset($ns)) {
        if (isset($namespaces[$ns])) {
            $prepend = "{$ns}:";
            $nameSpace = $namespaces[$ns];
        }
    }
    if (is_object($destinationXML)) {
        foreach ($destinationXML->children($nameSpace) as $desNodes => $destchild) {
            if (!$nodeAttr) {
                $parentNodes[] = (string) $desNodes;
            } else {
                $parentNodes[] = (string) $destchild->attributes($nameSpace)->name;
            }
        }

        foreach ($sourceXML->children($nameSpace) as $k => $child) {
            if ($nodeAttr) {
                $attrName = $child->attributes($nameSpace)->name;
                if (isset($attrName) && !empty($attrName)) {
                    if (!in_array((string) $attrName, $parentNodes)) {
                        $fchild = $destinationXML->addChild($k, '', $nameSpace);
                        $fchild->addAttribute(
                            $prepend . 'name', $attrName, $nameSpace
                        );
                        foreach (
                        $child->children($nameSpace) as $grandkey => $grandChild
                        ) {
                            $gChild = $fchild->addChild(
                                (string) $grandkey, '', $nameSpace
                            );
                            foreach (
                            $grandChild->attributes($nameSpace) as $attrKey => $attr
                            ) {
                                $gChild->addAttribute(
                                    $prepend . (string) $attrKey,
                                    (string) $attr, $nameSpace
                                );
                            }
                        }
                    }
                }
            } elseif (!is_numeric($k)) {
                if (!in_array((string) $k, $parentNodes)) {
                    $schild = $destinationXML->addChild($k, '', $nameSpace);
                    foreach ($child->attributes($nameSpace) as $attrKey => $attr) {
                        $schild->addAttribute(
                            $prepend . (string) $attrKey,
                            (string) $attr, $nameSpace
                        );
                    }
                    addXmlChildNodes($schild, $child, $namespaces, $ns);
                } else {
                    $destNodeAttrs
                        = $destinationXML->children($nameSpace)->$k->attributes(
                            $nameSpace
                        );
                    $destNodeAttrsArray
                        = array_values(objectsIntoArray($destNodeAttrs));
                    if (isset($destNodeAttrsArray[0]) && is_array(
                        $destNodeAttrsArray[0]
                    )) {
                        $destAtt = array_keys($destNodeAttrsArray[0]);
                    }
                    foreach ($child->attributes($nameSpace) as $attrKey => $attr) {
                        if (!in_array((string) $attrKey, $destAtt)) {
                            $destinationXML->children($nameSpace)->$k->addAttribute(
                                $prepend . (string) $attrKey,
                                (string) $attr, $nameSpace
                            );
                        }
                    }
                    noDuplicateMergeXML(
                        $destinationXML->children($nameSpace)->$k,
                        $child, $ns, $nodeAttr
                    );
                }
            }
        }
    } else {
        $destinationXML = $sourceXML;
    }
    return $destinationXML->asXML();
}

/**
 * merges xml files to have duplicate nodes
 *
 * @param obj    $destinationXML destination file Simple XMl Object
 * @param obj    $sourceXML      source file Simple XMl Object
 * @param string $ns             name space of the xml file
 * @param array  $nodes          array containing nodes to be appended
 *
 * @return null
 *
 */
function duplicateMergeXML($destinationXML, $sourceXML, $ns=null, $nodes=array())
{
    $prepend = '';
    $nameSpace = null;
    $namespaces = $sourceXML->getNameSpaces(true);
    if (isset($ns)) {
        if (isset($namespaces[$ns])) {
            $prepend = "{$ns}:";
            $nameSpace = $namespaces[$ns];
        }
    }
    if (is_object($destinationXML)) {
        foreach ($sourceXML->children($nameSpace) as $k => $child) {
            if (!empty($nodes)) {
                foreach ($nodes as $node) {
                    if ((string) $k == $node) {
                        $schild = $destinationXML->addChild(
                            (string) $k, '', $nameSpace
                        );
                        foreach (
                            $child->attributes($nameSpace) as $attrKey => $attr
                            ) {
                            $schild->addAttribute(
                                $prepend . (string) $attrKey,
                                (string) $attr, $nameSpace
                            );
                        }
                        addXmlChildNodes($schild, $child, $namespaces, $ns);
                    }
                }
                if ((string) $k == 'latentStyles') {
                    $destNodeAttrs
                        = $destinationXML->children($nameSpace)->$k->attributes(
                            $nameSpace
                        );
                    $destNodeAttrsArray
                        = array_values(objectsIntoArray($destNodeAttrs));
                    if (isset($destNodeAttrsArray[0]) && is_array(
                        $destNodeAttrsArray[0]
                    )) {
                        $destAtt = array_keys($destNodeAttrsArray[0]);
                    }
                    foreach ($child->attributes($nameSpace) as $attrKey => $attr) {
                        if (!in_array((string) $attrKey, $destAtt)) {
                            $destinationXML->children($nameSpace)->$k->addAttribute(
                                $prepend . (string) $attrKey,
                                (string) $attr, $nameSpace
                            );
                        }
                    }
                    addXmlChildNodes(
                        $destinationXML->children($nameSpace)->$k,
                        $child, $namespaces, $ns
                    );
                }
                if ((string) $k == 'docDefaults') {
                    noDuplicateMergeXML(
                        $destinationXML->children($nameSpace)->$k, $child, $ns
                    );
                }
            } else {
                $schild = $destinationXML->addChild((string) $k, '', $nameSpace);
                foreach ($child->attributes($nameSpace) as $attrKey => $attr) {
                    $schild->addAttribute(
                        $prepend . (string) $attrKey, (string) $attr, $nameSpace
                    );
                }
                addXmlChildNodes($schild, $child, $namespaces, $ns);
            }
        }
    } else {
        $destinationXML = $sourceXML;
    }
    return $destinationXML->asXML();
}

/**
 * merges presentation.xml of mergeable files
 *
 * @param object $destinationXML destination file Simple XMl Object
 * @param object $sourceXML      source file Simple XMl Object
 * @param object $destination    detination object
 * @param object $source         source object
 * @param string $ns             name space of the xml file
 *
 * @return string
 *
 * @return null
 *
 */
function mergePresentationPart(
    $destinationXML, $sourceXML, $destination, $source, $ns=null
) {
    /*
     * 10. Adds recursively the nodes from the source to the destination
     * 20. Adds refrence to the slides from the source to the destination
     *     in same order as they in destination.
     *  20.1 Gets the max slide ID from the detination and increments the ID
     *       as slides are added from source.
     * 30. Adds refrence to the slideMasters from the source to the destination
     *     in same order as they in destination.
     *  30.1 Gets the max slideMaster ID from the detination and increments
     *       the ID as slideMasters are added from source
     *  30.2 Calls fixSlideLayoutIds to fix Layout IDs in slideMasters such that
     *       are unique,different from slideMaster IDs and have
     *       value greater then 2147483648.
     */
    $parentNodes = array();
    $prepend = '';
    $nameSpace = null;
    $namespaces = $sourceXML->getNameSpaces(true);
    if (isset($ns)) {
        if (isset($namespaces[$ns])) {
            $prepend = "{$ns}:";
            $nameSpace = $namespaces[$ns];
        }
    }
    if (is_object($destinationXML)) {
        foreach ($destinationXML->children($nameSpace) as $desNodes => $destchild) {
            $parentNodes[] = (string) $desNodes;
        }
        foreach ($sourceXML->children($nameSpace) as $k => $child) {
            if (!is_numeric($k)) {
                //10. Adds recursively the nodes from the source to the destination
                if (!in_array((string) $k, $parentNodes)) {
                    $schild = $destinationXML->addChild($k, '', $nameSpace);
                    foreach ($child->attributes($nameSpace) as $attrKey => $attr) {
                        $schild->addAttribute(
                            $prepend . (string) $attrKey, (string) $attr, $nameSpace
                        );
                    }
                    addXmlChildNodes($schild, $child, $namespaces, $ns);
                }
                if (!in_array(
                    $k,
                    array(
                            'sldIdLst', 'sldMasterIdLst',
                            'notesSz', 'sldSz', 'defaultTextStyle'
                        )
                )) {
                    unset($destinationXML->children($nameSpace)->$k);
                } elseif (in_array($k, array('sldIdLst', 'sldMasterIdLst'))) {
                    if ($k == 'sldIdLst') {
                        $minId = 256;
                        /* 20.1 Gets the max slide ID from the detination and
                         *      increments the ID as slides are added from source.
                         */
                        $maxId = getMaxIdFromChild(
                            $destinationXML->children($nameSpace)->$k,
                            'id', $nameSpace, $minId
                        );
                    } else {
                        $minId = 2147483648;
                        /* 30.1 Gets the max slideMaster ID from the detination
                         *      and increments the ID as slideMasters are
                         *      added from source
                         */
                        $maxId = getMaxIdForSlideMaster(
                            $destinationXML->children($nameSpace)->$k, 'id',
                            $destination->package, $nameSpace, $minId
                        );
                    }
                    /*20. Adds refrence to the slides from the source to the
                     *    destination in same order as they in destination.
                     */
                    /*30. Adds refrence to the slideMasters from the source to the
                     *    destination in same order as they in destination.
                     */
                    foreach ($child->children($nameSpace) as $k1 => $gChild) {
                        if (!is_numeric($k1)) {
                            $oldId
                                = (string)
                                $gChild->attributes($namespaces['r'])->id[0];
                            $newKey
                                = isset(
                                    $destination->fileRelations[$source->file]
                                    [$k][$oldId]['newKey']
                                ) ? true : false;
                            if ($newKey) {
                                $newID = $destination->fileRelations
                                    [$source->file][$k][$oldId]['newKey'];
                                $maxId++;
                                $gGChild = $destinationXML->children(
                                    $nameSpace
                                )->$k->addChild($k1, '', $nameSpace);
                                $gGChild->addAttribute('id', $maxId);
                                $gGChild->addAttribute(
                                    "r:" . 'id', $newID, $namespaces['r']
                                );
                                if ($k == 'sldMasterIdLst') {
                                    /*30.2 Calls fixSlideLayoutIds to fix Layout IDs
                                     *     in slideMasters such that are unique,
                                     *     different from slideMaster IDs and
                                     *     have value greater then 2147483648.
                                     */

                                    fixSlideLayoutIds(
                                        $destination->package, $source->package,
                                        $destination->fileRelations[$source->file]
                                        [$k][$oldId],
                                        $nameSpace, $maxId
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }
    } else {
        $destinationXML = $sourceXML;
    }
    return $destinationXML->asXML();
}

/**
 * Fixes slideLayout IDs
 *
 * @param obj    $package     destination ZipArchieve
 * @param obj    $package2    source ZipArchieve
 * @param array  $masterSlide Master Slide Details
 * @param string $nameSpace   name space of the xml file
 * @param int    &$maxId      maximum id
 *
 * @return null
 *
 */
function fixSlideLayoutIds($package, $package2, $masterSlide, $nameSpace, &$maxId)
{
    /*
     *  10. Updates Slide Layout IDs such that they are unique,
     *      different from slideMaster IDs.
     */
    $master = simplexml_load_string(
        $package2->getFromName($masterSlide['oldFile'])
    );
    //10. Updates Slide Layout IDs such that they are unique,
    //    different from slideMaster IDs.
    foreach ($master->children($nameSpace) as $k => $child) {
        if ($k == 'sldLayoutIdLst') {
            foreach ($child->children($nameSpace) as $k1 => $gChild) {
                if ($k1 == 'sldLayoutId') {
                    $maxId++;
                    unset($gChild->attributes()->id[0]);
                    $gChild->addAttribute('id', $maxId);
                }
            }
        }
    }
    $package->addFromString($masterSlide['newFile'], $master->asXML());
}

/**
 * Returns maximum relation ID of slide Master
 *
 * @param obj    $simpleXMlObj simple xml object
 * @param srting $attr         Attribute name
 * @param obj    $package      ZipArchieve
 * @param string $nameSpace    name space of the xml file
 * @param int    $max          maximum id
 *
 * @return int $max
 *
 */
function getMaxIdForSlideMaster(
    $simpleXMlObj, $attr, $package, $nameSpace=null, $max = 1
) {
    /*
     * 10.  Gets the max slideMaster ID from the detination from destination
     *      presenation part.
     *     10.1 Gets the maximum slide Layout ID from SlideMasters Part
     */
    //10.  Gets the max slideMaster ID from the detination from destination
    //     presenation part.
    foreach ($simpleXMlObj->children($nameSpace) as $child) {
        $id = $child->attributes()->$attr;
        $id1 = preg_replace("/[^0-9]/", "", $id);
        if (((float) $id1) > $max) {
            $max = ((float) $id1);
        }
    }
    $masterSlidesCount = getMaxFilesInDir($package, 'ppt/slideMasters', ".xml");
    //10.1 Gets the maximum slide Layout ID from SlideMasters Part
    for ($i = 1; $i < $masterSlidesCount; $i++) {
        $master = simplexml_load_string(
            $package->getFromName("ppt/slideMasters/slideMaster{$i}.xml")
        );
        foreach ($master->children($nameSpace) as $k => $child) {
            if ($k == 'sldLayoutIdLst') {
                foreach ($child->children($nameSpace) as $k1 => $gChild) {
                    if ($k1 == 'sldLayoutId') {
                        $id = $gChild->attributes()->$attr;
                        $id1 = preg_replace("/[^0-9]/", "", $id);
                        if (((float) $id1) > $max) {
                            $max = ((float) $id1);
                        }
                    }
                }
            }
        }
    }
    return $max;
}

/**
 * Adds files recursively from source to destination
 *
 * @param array  $RelationshipsArray relationship array
 * @param object $destination        detisnation object
 * @param object $source             source object
 *
 * @return null
 *
 */
function addPptxFiles($RelationshipsArray, $destination, $source)
{
    /*
     * 10. Each of the target in $RelationshipsArray that are not
     *     present in destination is added from source to deatination.
     * 20. $RelationshipsArray array is updated with new target name
     * 30. addPptxFiles is called recursively for .rels of each
     *     of the copied mergeable part.
     */
    $filesCopied = array_keys(
        $destination->fileRelations["{$source->file}"]['filesCopied']
    );
    $PresentationParts=array('slideMasters', 'notesMasters', 'handoutMasters');
    $xlnsRel="http://schemas.openxmlformats.org/package/2006/relationships";
    if (isset($RelationshipsArray['Relationship'])
        && is_array($RelationshipsArray['Relationship'])
    ) {
        foreach ($RelationshipsArray['Relationship'] as $key => $relInfo) {
            if ($key == '@attributes' && !is_numeric($key)) {
                $relInfo = array($key => $relInfo);
            }
            if (isset($relInfo['@attributes']['Target'])) {
                $targetRel = $relInfo['@attributes']['Target'];
                $targetRel = str_replace('../', '', $targetRel);
                $targetRel = trim($targetRel);
                if (strpos($targetRel, "ppt/") === false
                    && strpos($targetRel, "docProps/") === false
                ) {
                    $targetRel = "ppt/" . $targetRel;
                }
                $dir = dirname("{$targetRel}");
                if (!isset($relInfo['@attributes']['TargetMode'])
                    || $relInfo['@attributes']['TargetMode'] != 'External'
                ) {

                    $file = basename($targetRel);
                    $extParts = explode('.', $file);
                    $ext = $extParts[count($extParts) - 1];
                    $dirParts = explode('/', $dir);
                    $dirFolder = $dirParts[count($dirParts) - 1];
                    if (!isset($destination->$dirFolder)) {
                        $destination->$dirFolder = getMaxFilesInDir(
                            $destination->package, $dir, '.' . $ext
                        );
                    }
                    $dir = (!empty($dir) ? ($dir . '/') : '');
                    $filename
                        = basename(preg_replace("/[0-9]/", "", $file), '.' . $ext) .
                        $destination->$dirFolder . '.' . $ext;
                    $destinationTarget = $dir . $filename;
                    //20. $RelationshipsArray array is updated with new target name
                    if (in_array($targetRel, $filesCopied)) {
                        if ($key == '@attributes' && !is_numeric($key)) {
                            $RelationshipsArray['Relationship']['@attributes']
                                ['Target'] = str_replace(
                                    'ppt/', '../',
                                    $destination->fileRelations["{$source->file}"]
                                    ['filesCopied'][$targetRel]
                                );
                        } else {
                            $RelationshipsArray['Relationship'][$key]
                                ['@attributes']['Target'] = str_replace(
                                    'ppt/', '../',
                                    $destination->fileRelations["{$source->file}"]
                                    ['filesCopied'][$targetRel]
                                );
                        }
                    } else {
                        if ($key == '@attributes' && !is_numeric($key)) {
                            $RelationshipsArray['Relationship']['@attributes']
                                ['Target'] = '../' . str_replace('ppt/', '', $dir) .
                                $filename;
                        } else {
                            $RelationshipsArray['Relationship'][$key]
                                ['@attributes']['Target'] = '../' .
                                str_replace('ppt/', '', $dir) . $filename;
                        }
                        $destinationTargetFlag = ($destination->package->locateName(
                            "{$destinationTarget}"
                        ) === false)
                        ?true:false;
                        if (($destinationTargetFlag )
                            && !in_array($dirFolder, $PresentationParts)
                        ) {
                            $folder = substr(
                                $destination->package->tempFolder . '/' . $dir,
                                0, -1
                            );

                            $destination->fileRelations["{$source->file}"]
                                [$targetRel]
                                    = array('newFile' => $destinationTarget);
                            $dirFolderCreated = $dirFolder . 'Created';
                            if (!is_dir($folder)
                                && !isset($destination->$dirFolderCreated)
                            ) {
                                $destination->$dirFolderCreated = true;
                                $dirAdded = substr($dir, 0, -1);
                                $destination->package->addEmptyDir($dirAdded);
                            }
                            //10. Each of the target in $RelationshipsArray that
                            //    are not present in destination is added
                            //    from source to deatination.
                            $destination->package->addFromString(
                                "{$destinationTarget}",
                                $source->package->getFromName("{$targetRel}")
                            );
                            $destination->fileRelations["{$source->file}"]
                                ['filesCopied'][$targetRel] = $destinationTarget;
                            $destinationRels = $dir . '_rels/' . $filename . '.rels';
                            $sourceRels = $dir . '_rels/' . $file . '.rels';
                            $dir = str_replace('ppt/', '', $dir);
                            $sourceRelsFlag = ($source->package->locateName(
                                $sourceRels
                            ) !== false) ? true : false;
                            if ($sourceRelsFlag) {
                                $SlideRel = objectsIntoArray(
                                    simplexml_load_string(
                                        $source->package->getFromName(
                                            "{$sourceRels}"
                                        )
                                    )
                                );
                                if (!empty($SlideRel)) {
                                    //30. addPptxFiles is called recursively
                                    //    for .rels of each of the
                                    //    copied mergeable part.
                                    $SlideRel = addPptxFiles(
                                        $SlideRel, $destination, $source
                                    );

                                    $param
                                        = "<?xml version=\"1.0\"
                                    encoding=\"UTF-8\"standalone=\"yes\"?>
                                            <Relationships xmlns='{$xlnsRel}'
                                            ></Relationships>";
                                    $xml_info = new SimpleXMLElement($param);
                                    foreach ($SlideRel["Relationship"]
                                    as $key => $rels
                                    ) {
                                        if ($key == '@attributes'
                                            && !is_numeric($key)
                                        ) {
                                            $rels = array($key => $rels);
                                        }
                                        $child = $xml_info->addChild(
                                            "Relationship"
                                        );
                                        if (isset($rels['@attributes'])
                                            && is_array($rels['@attributes'])
                                        ) {
                                            foreach ($rels['@attributes']
                                            as $attrKey => $attrs
                                            ) {
                                                $child->addAttribute(
                                                    $attrKey, $attrs
                                                );
                                            }
                                        }
                                    }
                                    $destination->package->addFromString(
                                        "{$destinationRels}", $xml_info->asXML()
                                    );
                                    
                                } else {
                                     $destination->package->addFromString(
                                         "{$destinationRels}",
                                         $source->package->getFromName(
                                             "{$sourceRels}"
                                         )
                                     );
                                }
                                $destination->fileRelations["{$source->file}"]
                                        [$sourceRels] = array(
                                        'oldKey' => '', 'newKey' => '',
                                        'newFile' => $destinationRels
                                    );
                                
                            }
                            $destination->$dirFolder++;
                        }
                    }
                }
            }
        }
    }
    return $RelationshipsArray;
}

/**
 * Compares two arrays and returns array1 which is a merge of $array1 and $array2
 *
 * @param array   $array1      destination
 * @param array   $array2      source array
 * @param array   $destination destination object
 * @param array   $source      source object
 * @param array   $maxID       maximum id
 * @param boolean $baseRelFlag flag
 *
 * @return array $array1
 *
 */
function pptxComparativeMerge(
    $array1, $array2, $destination, $source, $maxID=1, $baseRelFlag=false
) {
    /*
     * 10. Compares each node in array2 with nodes in array1
     *     10.1 If node doesn't exists in array1 it copies the node in array1
     * 20. For relationships files (presentation.xml.rels file)
     *     each of the target file in source is copied into the destination.
     *     20.1. For each of the file copied its rels file is copied and
     *           addPptxFiles is called,which inturn adds the source file to
     *           destination and updates rels
     *     20.2. array1 is updated with a new node entry
     */
    if (!isset($destination->fpackaileRelations["{$source->file}"]['filesCopied'])) {
        $destination->fileRelations["{$source->file}"]['filesCopied'] = array();
    }
    $arr = $array1;
    $keys = array_keys($array1);
    $relsFlag = false;
    $xlnsRel = "http://schemas.openxmlformats.org/package/2006/relationships";
    foreach ($keys as $key) {
        $mID = ((int) $maxID);
        if (!empty($array1[$key]) && is_array($array1[$key])) {
            if (!empty($array2[$key]) && is_array($array2[$key])) {
                $count = count($array1[$key]);
                if ($key == 'Relationship') {
                    $relsFlag = true;
                    foreach ($arr[$key] as $k => $value) {
                        if ($k == '@attributes' && !is_numeric($k)) {
                            if (count($array2[$key]) > 1) {
                                unset($array1[$key][$k]);
                                $array1[$key][] = array($k => $value);
                            }
                            unset($arr[$key]['@attributes']['Id']);
                            $arr[$key] = array($key => $arr[$key]);
                        } else {
                            unset($arr[$key][$k]['@attributes']['Id']);
                        }
                    }
                }
                foreach ($array2[$key] as $k11 => $a2val) {
                    $notCopied = false;
                    if ($k11 == '@attributes' && !is_numeric($k11)) {
                        $a2val = array($k11 => $a2val);
                    }
                    if ($relsFlag) {
                        if (isset($a2val['@attributes']['Id'])) {
                            $id = $a2val['@attributes']['Id'];
                            unset($a2val['@attributes']['Id']);
                        }
                        if (isset($a2val['@attributes']['Target'])) {
                            $target = $a2val['@attributes']['Target'];
                            if (strpos($target, "ppt/") === false
                                && strpos($target, "docProps/") === false
                            ) {
                                $target = "ppt/" . $target;
                            }
                        }
                    }
                    //10. Compares each node in array2 with nodes in array1
                    $result = array_search($a2val, $arr[$key]);
                    if ($result === false && !$relsFlag) {
                        // 10.1 If node doesn't exists in array1 it copies
                        //      the node in array1
                        $array1[$key][$count] = $a2val;
                    } elseif ($result === false && $baseRelFlag) {
                        $dir = dirname("{$target}");
                        $newId = 'rId' . $mID;
                        $destination->fileRelations["{$source->file}"][$target]
                            = array(
                            'oldKey' => $id, 'newKey' => $newId,
                            'newFile' => $target
                            );
                        if (strpos($dir, 'slides') !== false) {
                            $destination->fileRelations["{$source->file}"]
                                ['sldIdLst'][$id] = array(
                                'newKey' => $newId, 'newFile' => $target,
                                'oldFile' => $target
                            );
                        }
                        if (strpos($dir, 'notesMasters') !== false) {
                            $destination->fileRelations["{$source->file}"]
                                ['notesMasterIdLst'][$id] = array(
                                'newKey' => $newId, 'newFile' => $target,
                                'oldFile' => $target
                            );
                        }
                        if (strpos($dir, 'slideMasters') !== false) {
                            $destination->fileRelations["{$source->file}"]
                                ['sldMasterIdLst'][$id] = array(
                                'newKey' => $newId, 'newFile' => $target,
                                'oldFile' => $target
                            );
                        }
                        $a2val['@attributes']['Id'] = $newId;
                        $array1[$key][$count] = $a2val;
                        $targetFlag = ($destination->package->locateName(
                            "{$target}"
                        ) === false) ? true : false;
                        if ($targetFlag) {
                            if (!isset($a2val['@attributes']['TargetMode'])
                                || $a2val['@attributes']['TargetMode'] != 'External'
                            ) {
                                $destination->package->addFromString(
                                    "{$target}", $source->package->getFromName(
                                        "{$target}"
                                    )
                                );
                            }
                        }
                    }
                    $filesCopied = array_keys(
                        $destination->fileRelations
                        ["{$source->file}"]['filesCopied']
                    );
                    if ($relsFlag && !in_array($target, $filesCopied)
                        && !$baseRelFlag
                    ) {
                        $file = basename($target);
                        $extParts = explode('.', $file);
                        $ext = $extParts[count($extParts) - 1];
                        $dir = dirname("{$target}");
                        $dirParts = explode('/', $dir);
                        $dirFol = $dirParts[count($dirParts) - 1];
                        if (!isset($destination->$dirFol)) {
                            $destination->$dirFol = getMaxFilesInDir(
                                $destination->package, $dir, '.' . $ext
                            );
                        }
                        if ((strpos($file, 'slide') !== false
                            || strpos($file, 'note') !== false
                            || strpos($dir, 'media') !== false
                            || strpos($dir, 'theme') !== false
                            || strpos($dir, 'customXml') !== false
                            || strpos($dir, 'handout') !== false)
                        ) {
                            $targetFlag = ($source->package->locateName(
                                "{$target}"
                            ) !== false) ? true : false;
                            if (($targetFlag)) {
                                $file = basename($target, '.' . $ext);
                                $base = str_replace(
                                    ' ', '_', basename(
                                        "{$destination->file}",
                                        ".pptx"
                                    )
                                );
                                $filename = preg_replace("/[0-9]/", "", $file) .
                                    $destination->$dirFol . '.' . $ext;
                                $destinationTarget
                                    = (!empty($dir) ? ($dir . '/') : '') . $filename;
                                //20. For relationships files
                                //    (presentation.xml.rels file) each of the target
                                //    file in source is copied into the destination.
                                $destination->package->addFromString(
                                    "{$destinationTarget}",
                                    $source->package->getFromName("{$target}")
                                );
                                $destination->fileRelations["{$source->file}"]
                                    ['filesCopied'][$target] = $destinationTarget;

                                if (strpos($file, 'slide') !== false
                                    || strpos($file, 'note') !== false
                                    || strpos($file, 'theme') !== false
                                ) {
                                    $sourceRels
                                        = $dir . "/_rels/" . $file . ".xml.rels";
                                    $destinationRels
                                        = $dir . "/_rels/" . $filename . ".rels";
                                    $sourceRelsFlag = ($source->package->locateName(
                                        $sourceRels
                                    ) !== false) ? true : false;
                                    if ($sourceRelsFlag) {
                                        $SlideRel = array();
                                        $SlideRel = objectsIntoArray(
                                            simplexml_load_string(
                                                $source->package->getFromName(
                                                    "{$sourceRels}"
                                                )
                                            )
                                        );
                                        //20.1. For each of the file copied its
                                        //      rels file is copied and
                                        //      addPptxFilesis called,which
                                        //      inturn adds the source file to
                                        //      destination and updates rels
                                        $SlideRel = addPptxFiles(
                                            $SlideRel, $destination, $source
                                        );
                                        $param
                                            = "<?xml version=\"1.0\"
                                        encoding=\"UTF-8\"standalone=\"yes\"?>
                                                <Relationships
                                                xmlns='{$xlnsRel}'>
                                                </Relationships>";
                                        $xml_info = new SimpleXMLElement($param);
                                        foreach ($SlideRel["Relationship"]
                                        as $key1 => $attrs
                                        ) {
                                            if ($key1 == '@attributes'
                                                && !is_numeric($key1)
                                            ) {
                                                $attrs = array($key1 => $attrs);
                                            }
                                            $child = $xml_info->addChild(
                                                "Relationship"
                                            );
                                            if (isset($attrs['@attributes'])
                                                && is_array($attrs['@attributes'])
                                            ) {
                                                foreach ($attrs['@attributes']
                                                as $akey => $aval
                                                ) {
                                                    $child->addAttribute(
                                                        $akey, $aval
                                                    );
                                                }
                                            }
                                        }
                                        $destination->package->addFromString(
                                            "{$destinationRels}",
                                            $xml_info->asXML()
                                        );
                                        $destination->fileRelations
                                            ["{$source->file}"][$sourceRels]
                                                = array(
                                                'oldKey' => '',
                                                'newKey' => '',
                                                'newFile' => $destinationRels
                                        );
                                    }
                                }

                                $newId = 'rId' . $mID;
                                $destination->fileRelations["{$source->file}"]
                                    [$target] = array(
                                    'oldKey' => $id, 'newKey' => $newId,
                                    'newFile' => $destinationTarget,
                                    'oldFile' => $target
                                );
                                if (strpos($dir, 'slides') !== false) {
                                    $destination->fileRelations["{$source->file}"]
                                        ['sldIdLst'][$id] = array(
                                        'newKey' => $newId,
                                        'newFile' => $destinationTarget,
                                        'oldFile' => $target
                                    );
                                }
                                if (strpos($dir, 'notesMasters') !== false) {
                                    $destination->fileRelations["{$source->file}"]
                                        ['notesMasterIdLst'][$id] = array(
                                        'newKey' => $newId,
                                        'newFile' => $destinationTarget,
                                        'oldFile' => $target
                                    );
                                }
                                if (strpos($dir, 'slideMasters') !== false) {
                                    $destination->fileRelations["{$source->file}"]
                                        ['sldMasterIdLst'][$id] = array(
                                        'newKey' => $newId,
                                        'newFile' => $destinationTarget,
                                        'oldFile' => $target
                                    );
                                }
                                $a2val['@attributes']['Id'] = $newId;
                                $dir = str_replace('ppt/', '', $dir);
                                $a2val['@attributes']['Target']
                                    = (!empty($dir) ? ($dir . '/') : '') . $filename;
                                $array1[$key][$count] = $a2val;
                                $destination->$dirFol++;
                            }
                        } elseif ($result === false) {
                            $target = str_replace('../', '', $target);
                            $dir = dirname("{$target}");
                            $newId = 'rId' . $mID;
                            $destination->fileRelations["{$source->file}"]
                                [$target] = array(
                                'oldKey' => $id, 'newKey' => $newId,
                                'newFile' => $target
                            );
                            if (strpos($dir, 'slides') !== false) {
                                $destination->fileRelations["{$source->file}"]
                                    ['sldIdLst'][$id] = array(
                                    'newKey' => $newId, 'newFile' => $target,
                                    'oldFile' => $target
                                );
                            }
                            if (strpos($dir, 'notesMasters') !== false) {
                                $destination->fileRelations["{$source->file}"]
                                    ['notesMasterIdLst'][$id] = array(
                                    'newKey' => $newId, 'newFile' => $target,
                                    'oldFile' => $target
                                );
                            }
                            if (strpos($dir, 'slideMasters') !== false) {
                                $destination->fileRelations["{$source->file}"]
                                    ['sldMasterIdLst'][$id] = array(
                                    'newKey' => $newId, 'newFile' => $target,
                                    'oldFile' => $target
                                );
                            }
                            $a2val['@attributes']['Id'] = $newId;
                            //20.2. array1 is updated with a new node entry
                            $array1[$key][$count] = $a2val;
                            $targetFlag = ($destination->package->locateName(
                                "{$target}"
                            ) === false) ? true : false;
                                $targetModeExternal = (!isset(
                                    $a2val['@attributes']['TargetMode']
                                )
                                || $a2val['@attributes']
                                ['TargetMode'] != 'External') ? true : false;
                            if ($targetFlag) {
                                if ( $targetModeExternal ) {
                                    $destination->package->addFromString(
                                        "{$target}",
                                        $source->package->getFromName("{$target}")
                                    );
                                }
                            }
                        }
                    }
                    if (!$relsFlag && isset($a2val['@attributes']['PartName'])
                        && $key == 'Override'
                    ) {
                        $fileRelFlag = is_array(
                            $destination->fileRelations["{$source->file}"]
                        ) ? true : false;
                        if ($fileRelFlag) {
                            foreach ($destination->fileRelations["{$source->file}"]
                            as $target => $targetValues
                            ) {
                                $partNameTargetFlag = (trim(
                                    $a2val['@attributes']['PartName']
                                ) == trim('/' . $target)) ? true : false;
                                if ($partNameTargetFlag) {
                                    $a2val['@attributes']['PartName']
                                        = trim('/' . $targetValues['newFile']);
                                    $array1[$key][$count] = $a2val;
                                    break;
                                }
                            }
                        }
                    }
                    $count++;
                    $mID++;
                }
            }
        } elseif (!empty($array2[$key]) && is_array($array2[$key])) {
            $array1[$key] = $array2[$key];
        }
    }
    return $array1;
}

/**
 * merges xml theme files
 *
 * @param obj $destinationXML destination file Simple XML Object
 * @param obj $sourceXML      source file Simple XML Object
 *
 * @return null
 *
 */
function mergeThemes($destinationXML, $sourceXML)
{
    $parentNodes = array();
    $namespaces = $sourceXML->getNameSpaces(true);
    if (is_object($destinationXML)) {
        foreach ($destinationXML->children($namespaces['a'])
        as $desNodes => $destchild
        ) {
            $parentNodes[] = (string) $desNodes;
        }
        foreach ($sourceXML->children($namespaces['a']) as $k => $child) {
            if (!is_numeric($k)) {
                if (!in_array((string) $k, $parentNodes)) {
                    $schild = $destinationXML->addChild($k, '', $namespaces['a']);
                    foreach ($child->attributes() as $attrKey => $attr) {
                        $schild->addAttribute((string) $attrKey, (string) $attr);
                    }
                    addXmlChildNodes($schild, $child, $namespaces);
                } else {
                    foreach ($child->children($namespaces['a']) as $k1 => $gChild) {
                        foreach ($gChild->children($namespaces['a'])
                        as $k11 => $gGChild
                        ) {
                            foreach ($gGChild->children($namespaces['a'])
                            as $k111 => $gGGChild
                            ) {
                                $objectExistsFlag = (is_object(
                                    $destinationXML->children(
                                        $namespaces['a']
                                    )->$k->$k1->$k11
                                )
                                ) ? true : false;
                                if ($objectExistsFlag) {
                                    if ($k111 == 'font') {
                                        $innerChild = $destinationXML->children(
                                            $namespaces['a']
                                        )->$k->$k1->$k11->addChild(
                                            $k111, '', $namespaces['a']
                                        );
                                        foreach ($gGGChild->attributes()
                                        as $attrKey => $attr
                                        ) {
                                            $innerChild->addAttribute(
                                                (string) $attrKey, (string) $attr
                                            );
                                        }
                                        addXmlChildNodes(
                                            $innerChild, $gGGChild,
                                            $namespaces, 'a'
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    } else {
        $destinationXML = $sourceXML;
    }
    return $destinationXML->asXML();
}

/**
 * merges the contents of two xml files
 *
 * @param obj $destinationXML destination file Simple XML Object
 * @param obj $sourceXML      source file Simple XML Object
 * @param obj $destination    destination object
 * @param obj $source         source object
 *
 * @return null
 *
 */
function contentMerge($destinationXML, $sourceXML, $destination, $source)
{
    /*
     * 10. Updates destination document.xml with new relationships Ids
     * 20. Appends source xml to destination xml.
     */
    $destinationReplace = '</w:body></w:document>';
    $sourceReplace = '(.*)(<w:body>)';
    if (!empty($destinationXML)) {
        $destinationXML = str_replace($destinationReplace, '', $destinationXML);
        $destinationXML .='<w:br w:type="page"/>';
        $sourceXML = str_replace(
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>',
            '', $sourceXML
        );
        /*$sourceXML= nl2br($sourceXML);
        $replace = '<w:document 
            xmlns:ve="http://schemas.openxmlformats.org/markup-compatibility/2006"
            xmlns:o="urn:schemas-microsoft-com:office:office"
            xmlns:r="http://schemas.openxmlformats.org
         * /officeDocument/2006/relationships"
            xmlns:m="http://schemas.openxmlformats.org/officeDocument/2006/math"
            xmlns:v="urn:schemas-microsoft-com:vml"
            xmlns:wp="http://schemas.openxmlformats.org
         * /drawingml/2006/wordprocessingDrawing"
            xmlns:w10="urn:schemas-microsoft-com:office:word"
            xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"
            xmlns:wne="http://schemas.microsoft.com/office/word/2006/wordml">
            <w:body>';
         $sourceXML = str_replace(trim($replace), '', $sourceXML);*/
        $sourceXML = preg_replace("/(.*)(<w:body>)/", '', $sourceXML);
        if (isset($destination->fileRelations["{$source->file}"])
            && is_array($destination->fileRelations["{$source->file}"])
        ) {
            //10. Updates destination document.xml with new relationships Ids
            foreach ($destination->fileRelations["{$source->file}"] as $ids) {
                $old = 'r:id="' . $ids['oldKey'] . '"';
                $new = 'r:id="' . $ids['newKey'] . '"';
                $sourceXML = str_replace($old, $new, $sourceXML);
                $old = 'r:embed="' . $ids['oldKey'] . '"';
                $new = 'r:embed="' . $ids['newKey'] . '"';
                $sourceXML = str_replace($old, $new, $sourceXML);
            }
        }
        //20. Appends source xml to destination xml.
        $destinationXML .=$sourceXML;
    } else {
        $destinationXML = $sourceXML;
    }
    return $destinationXML;
}

/**
 * Adds header/footer/image files
 *
 * @param array  &$headerFooterRel relationship array to be merged recursively
 * @param object $destination      destination file object
 * @param object $package2         source file ZipArchieve
 *
 * @return null
 *
 */
function addDocxFiles(&$headerFooterRel, $destination, $package2)
{
    /*
     * 10. Each of the target in $headerFooterRel that are not present in destination
     *     is added from source to deatination.
     * 20. $headerFooterRel array is updated with new target name
     */
    if (isset($headerFooterRel['Relationship'])
        && is_array($headerFooterRel['Relationship'])
    ) {
        foreach ($headerFooterRel['Relationship'] as $key => $relInfo) {
            if (isset($relInfo['Target'])) {
                $targetRel = $relInfo['Target'];
                if (strpos($targetRel, "word/") === false
                    && strpos($targetRel, "docProps/") === false
                ) {
                    $targetRel = "word/" . $targetRel;
                }
                if ($destination->package->locateName("{$targetRel}") === false) {
                    $base = dirname("{$targetRel}");
                    if (!isset($relInfo['TargetMode'])
                        || $relInfo['TargetMode'] != 'External'
                    ) {
                        $destination->package->addEmptyDir($base);
                        //10. Each of the target in $headerFooterRel that are not
                        //    present in destination is added from source to
                        //    destination.
                        $destination->package->addFromString(
                            "{$targetRel}",
                            $package2->getFromName("{$targetRel}")
                        );
                    }
                } else {
                    $dir = dirname($targetRel);
                    if (strpos($targetRel, 'media') !== false) {
                        $base = str_replace(
                            ' ', '_', basename("{$destination->file}", ".docx")
                        );
                        $filename = $base . '_' . basename($targetRel);
                        $destinationTarget
                            = (!empty($dir) ? ($dir . '/') : '') . $filename;
                        $destination->package->addFromString(
                            "{$destinationTarget}",
                            $package2->getFromName("{$targetRel}")
                        );
                        $dir = str_replace('word/', '', $dir);
                        //20. $headerFooterRel array is updated with new target name
                        $headerFooterRel['Relationship'][$key]['Target']
                            = (!empty($dir) ? ($dir . '/') : '') . $filename;
                    }
                }
            }
        }
    }
}

/**
 * Compares two arrays and returns array1 which is a merge of $array1 and $array2
 *
 * @param array  $array1      destination object
 * @param array  $array2      source object
 * @param object $destination destination object
 * @param object $source      source object
 * @param int    $maxID       maximum id
 *
 * @return array $array1
 *
 */
function docxComparativeMerge($array1, $array2, $destination, $source, $maxID=1)
{
    /*
     * 10. Compares each node in array2 with nodes in array1
     *     10.1 If node doesn't exists in array1 it copies the node in array1
     * 20. For relationships files (documnet.xml.rels and _rels/.rels file)
     *     each of the target file in source is copied into the destination.
     *     20.1. For each of the file copied its rels file is copied and
     *           addFiles is called,which inturn adds the source file to
     *           destination and updates rels
     */
    $arr = $array1;
    $keys = array_keys($array1);
    $relsFlag = false;
    $docxFlag = true;
    $xmlnsForRelationships="http://schemas.openxmlformats.org/
        package/2006/relationships";
    if (!isset($destination->fileRelations["{$source->file}"])) {
        $destination->fileRelations["{$source->file}"] = array();
    }
    foreach ($keys as $key) {
        $mID = ((int) $maxID);
        if (!empty($array1[$key]) && is_array($array1[$key])) {
            if (!empty($array2[$key]) && is_array($array2[$key])) {
                $count = count($array1[$key]);
                if ($key == 'Relationship') {
                    $relsFlag = true;
                    foreach ($arr[$key] as $k => $value) {
                        unset($arr[$key][$k]['@attributes']['Id']);
                    }
                }
                foreach ($array2[$key] as $k11 => $a2val) {
                    $notCopied = false;
                    if ($relsFlag) {
                        if (isset($a2val['@attributes']['Id'])) {
                            $id = $a2val['@attributes']['Id'];
                            unset($a2val['@attributes']['Id']);
                        }
                        if (isset($a2val['@attributes']['Target'])) {
                            $target = $a2val['@attributes']['Target'];
                            if (strpos($target, "word/") === false
                                && strpos($target, "docProps/") === false
                            ) {
                                $target = "word/" . $target;
                            }
                        }
                    }
                    //10. Compares each node in array2 with nodes in array1
                    $result = array_search($a2val, $arr[$key]);
                    if ($result === false) {
                        if ($relsFlag) {
                            $newId = 'rId' . $mID;
                            $targetKey = $target;
                            $keyExists
                                = array_key_exists(
                                    $target,
                                    $destination->fileRelations["{$source->file}"]
                                );
                            if ($keyExists) {
                                $targetKey = $target . '_' . $count;
                            }
                            $destination->fileRelations["{$source->file}"]
                                [$targetKey] = array(
                                'oldKey' => $id, 'newKey' => $newId, 'newFile' => ''
                            );
                            $a2val['@attributes']['Id'] = $newId;
                        }
                        //10.1 If node doesn't exists in array1 it copies the
                        //     node in array1
                        $array1[$key][$count] = $a2val;
                        if ($relsFlag) {
                            $targetFlag = ($destination->package->locateName(
                                "{$target}"
                            ) === false) ? true : false;
                            if ($targetFlag) {
                                $base = dirname("{$target}");
                                $targetModeExternalFlag = (!isset(
                                        $a2val['@attributes']['TargetMode']
                                    )
                                    ||
                                    $a2val['@attributes']['TargetMode'] != 'External'
                                    ) ? true : false;
                                if ( $targetModeExternalFlag ) {
                                    $destination->package->addEmptyDir($base);
                                    //20. For relationships files
                                    //    (documnet.xml.rels and _rels/.rels file)
                                    //    each of the target file in source is
                                    //    copied into the destination.
                                    $destination->package->addFromString(
                                        "{$target}",
                                        $source->package->getFromName("{$target}")
                                    );
                                    $file = basename($target);
                                    $destinationRels = $base .
                                        "/_rels/" . $file . ".rels";
                                    $destinationRelsFlag = ($source->package
                                        ->locateName($destinationRels)
                                        !== false) ? true : false;
                                    if ($destinationRelsFlag) {
                                        $headerFooterRel = objectsIntoArray(
                                            simplexml_load_string(
                                                $source->package->getFromName(
                                                    "{$destinationRels}"
                                                )
                                            )
                                        );
                                        //20.1. For each of the file copied its
                                        //      rels file is copied and addFiles
                                        //      is called,which inturn adds the
                                        //      source file to destination and
                                        //      updates rels
                                        addDocxFiles(
                                            $headerFooterRel,
                                            $destination, $source->package
                                        );
                                        $param = "<?xml version=\"1.0\"
                                        encoding=\"UTF-8\" standalone=\"yes\"?>
                                            <Relationships
                                            xmlns='{$xmlnsForRelationships}'
                                            ></Relationships>";
                                        $xml_info = new SimpleXMLElement($param);
                                        // function call to convert array to xml
                                        $child = $xml_info->addChild("Relationship");
                                        foreach ($headerFooterRel["Relationship"]
                                        ['@attributes'] as $attrKey => $attrs
                                        ) {
                                            $child->addAttribute($attrKey, $attrs);
                                        }
                                        $destination->package->addFromString(
                                            "{$destinationRels}",
                                            $xml_info->asXML()
                                        );
                                    }
                                } else {
                                    $notCopied = true;
                                }
                            } else {
                                $notCopied = true;
                                unset($array1[$key][$count]);
                            }
                        }
                    }
                    if (($result !== false && $relsFlag) || $notCopied) {
                        $dir = dirname("{$target}");
                        if (($destination->package->locateName("{$target}") !== false
                            && $source->package->locateName("{$target}") !== false)
                            || $notCopied
                        ) {
                            $file = basename($target, ".xml");
                            if (strpos($file, 'footer') !== false
                                || strpos($file, 'header') !== false
                                || strpos($dir, 'media') !== false
                            ) {
                                $base = str_replace(
                                    ' ', '_', basename(
                                        "{$destination->file}", ".docx"
                                    )
                                );
                                $filename = $base . '_' . $file;
                                if (strpos($file, 'footer') !== false
                                    || strpos($file, 'header') !== false
                                ) {
                                    $filename = $filename . '.xml';
                                    $sourceRels = "word/_rels/" .
                                        $file . ".xml.rels";
                                    $destinationRels = "word/_rels/" .
                                        $filename . ".rels";
                                    $sourceRelsFlag = ($source->package->locateName(
                                        $sourceRels
                                    ) !== false) ? true : false;
                                    if ($sourceRelsFlag) {
                                        $headerFooterRel = objectsIntoArray(
                                            simplexml_load_string(
                                                $source->package->getFromName(
                                                    "{$sourceRels}"
                                                )
                                            )
                                        );
                                        addDocxFiles(
                                            $headerFooterRel, $destination,
                                            $source->package
                                        );
                                        $param = "<?xml version=\"1.0\"
                                            encoding=\"UTF-8\"
                                            standalone=\"yes\"?>
                                            <Relationships
                                            xmlns='{$xmlnsForRelationships}'
                                            ></Relationships>";
                                        $xml_info = new SimpleXMLElement($param);
                                        // function call to convert array to xml
                                        $child = $xml_info->addChild("Relationship");
                                        foreach ($headerFooterRel["Relationship"]
                                        ['@attributes'] as $attrKey => $attrs
                                        ) {
                                            $child->addAttribute($attrKey, $attrs);
                                        }
                                        $destination->package->addFromString(
                                            "{$destinationRels}",
                                            $xml_info->asXML()
                                        );
                                    }
                                }
                                $destinationTarget
                                    = (!empty($dir) ? ($dir . '/') : '') . $filename;
                                $destination->package->addFromString(
                                    "{$destinationTarget}",
                                    $source->package->getFromName("{$target}")
                                );
                                $newId = 'rId' . $mID;
                                $destination->fileRelations["{$source->file}"]
                                [$target] = array(
                                    'oldKey' => $id,
                                    'newKey' => $newId,
                                    'newFile' => $filename
                                );
                                $a2val['@attributes']['Id'] = $newId;
                                $dir = str_replace('word/', '', $dir);
                                $a2val['@attributes']['Target']
                                    = (!empty($dir) ? ($dir . '/') : '') . $filename;
                                $array1[$key][$count] = $a2val;
                            } else {
                                if ($file != 'document') {
                                    $keyExists
                                        = array_key_exists(
                                            $file,
                                            $destination->mergableParts
                                        );
                                    if ($keyExists) {
                                        $destination->appendPart(
                                            $source,
                                            $destination->mergableParts[$file]
                                        );
                                    }
                                }
                                if ($file == 'document') {
                                    $docxFlag = false;
                                }
                            }
                        }
                    }
                    if (!$relsFlag) {
                        $headerFooterFlag = (isset(
                            $a2val['@attributes']['PartName']
                            )
                            && (strpos(
                                $a2val['@attributes']['PartName'], 'header'
                            ) !== false
                            || strpos(
                                $a2val['@attributes']['PartName'], 'footer'
                            )) !== false) ? true : false;
                        if ( $headerFooterFlag ) {
                            $fileRelFlag = is_array(
                                $destination->fileRelations["{$source->file}"]
                            ) ? true : false;
                            if ($fileRelFlag) {
                                foreach ($destination->fileRelations
                                ["{$source->file}"] as $target => $targetValues
                                ) {
                                    $partNameTargetFlag = (trim(
                                        $a2val['@attributes']['PartName']
                                    ) == trim('/' . $target)) ? true : false;
                                    if ($partNameTargetFlag) {
                                        $a2val['@attributes']['PartName']
                                            = !empty($targetValues['newFile']) ?
                                            $targetValues['newFile'] :
                                            $a2val['@attributes']['PartName'];
                                        $array1[$key][$k11] = $a2val;
                                    }
                                }
                            }
                        }
                    }
                    $mID++;
                    $count++;
                }
            }
        } elseif (!empty($array2[$key]) && is_array($array2[$key])) {
            $array1[$key] = $array2[$key];
        }
    }
    if ($relsFlag && $docxFlag) {
        if (array_key_exists('docx', $destination->mergableParts)) {
            $destination->appendPart($source, $destination->mergableParts['docx']);
        }
    }
    return $array1;
}

?>
