<?php

/**
 * Index for Mergeables
 *
 * PHP version 5.2.0 +
 *
 * LICENSE: Merlin :: merging system (http://primarymodules.com)
 * Copyright 2011, Primary Modules, Inc. (http://primarymodules.com)
 *
 * @category  Index
 * @package   Merlin
 * @author    Pooja Pednekar <ppednekar@primarymodules.com>
 * @copyright 2011 Primary Modules Inc.(http://primarymodules.com)
 * @license   http://primarymodules.com/licenses/merlin-prop.php	Merlin Proprietary License
 * @link      http://primarymodules.com/products/merlin
 * @since     Merlin Release 1.0
 */

require_once dirname(__FILE__) . '/config/config.inc.php';
global $merlin_default_input_dir, $merlin_default_output_dir, $rootDirectory;
require_once $rootDirectory . '/classes/mergeable_factory.php';

// Mail Merge
/*
 * Mail Merge For Mergeables
 * merge word :: $data[account][name]
 */

/*$obj = MergeableFactory::createMergeableObject(
    $merlin_default_input_dir . 'test_merge.pptx'
);
$data = array();
$data['pooja']['pednekar'] = 'pooja pednekar is a software engineer';
$data['pooja']['address'] = 'Raya Smriti,Khorlim Mapusa Goa.';
$data['pooja']['phone'] = 9860830054;
$obj->mergePhpVariables($data);
*/


//PPTX Merging
/*
require_once $rootDirectory . '/classes/mergeables/pptx_mergeables.php';
$dest = $merlin_default_output_dir . 'test.pptx';
@unlink($dest);
$obj1 = new PptxMergeables($dest);
$obj2 = new PptxMergeables($merlin_default_input_dir . 'Chapter3Assignment.pptx');
$obj3 = new PptxMergeables($merlin_default_input_dir . 'V_People.pptx');

$obj1->append($obj2);
$obj1->append($obj3);
*/



//DOCX Merging

/*$dest = $merlin_default_output_dir . '2_docx.docx';
@unlink($dest);
require_once $rootDirectory . '/classes/mergeables/docx_mergeables.php';
$obj1 = new DocxMergeables($dest);
$obj2 = new DocxMergeables($merlin_default_input_dir . 'test2.docx');
$obj3 = new DocxMergeables($merlin_default_input_dir . 'survey.docx');

$obj1->append($obj2);
$obj1->append($obj3);*/


// PDF File Merging
$dest = $merlin_default_output_dir . 'pooja.pdf';
@unlink($dest);
$obj1 = MergeableFactory::createMergeableObject($dest);
$obj2 = MergeableFactory::createMergeableObject(
    $merlin_default_input_dir . 'OpenXML_White_Paper.pdf'
);
$obj3 = MergeableFactory::createMergeableObject(
    $merlin_default_input_dir . 'vpuml_quickstart.pdf'
);


$obj1->append($obj2);
$obj1->append($obj3);


if (!empty(Mergeables::$errorArray) && is_array(Mergeables::$errorArray)) {
    echo "Following errors occured while merging:<br />";
    foreach (Mergeables::$errorArray as $error) {
        echo $error . "<br />";
    }
} else {
    echo "Files merged successfully.";
}


