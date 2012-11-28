<?php

/**
 * Test Pptx Set Method
 *
 * PHP version 5.2.0 +
 *
 * LICENSE: Merlin :: merging system (http://primarymodules.com)
 * Copyright 2011, Primary Modules, Inc. (http://primarymodules.com)
 *
 * @category  Pptx_Set_Method_Tester
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
 * Test Pptx Set Method
 *
 * @category Pptx_Set_Method_Tester
 * @package  Merlin
 * @author   Pooja Pednekar <ppednekar@primarymodules.com>
 * @license  http://primarymodules.com/licenses/merlin-prop.php	Merlin Proprietary License
 * @link     http://primarymodules.com/products/merlin
 *
 */
class PptxSetTestCase extends UnitTestCase
{

    /**
     * PptxSetTestCase Constructor
     *
     * @author Pooja Pednekar
     * @access public
     *
     * @return null
     */
    function PptxSetTestCase()
    {
        global $merlin_default_test_input_dir;
        global $merlin_default_output_dir, $rootDirectory;
        $this->source
            = $merlin_default_test_input_dir . 'default_files/horoscopes.pptx';
        $this->file1
            = $merlin_default_test_input_dir . 'horoscopes.pptx';
        $this->file2
            = $merlin_default_test_input_dir . 'V_People.pptx';
        $this->file3
            = $merlin_default_test_input_dir . 'horoscopes_V_People.pptx';
        @unlink($this->file1);
        copy($this->source, $this->file1);
        exec("chmod -R 777 {$this->file1}");
        exec("chmod -R 777 {$merlin_default_test_input_dir}");
        $this->tempFolder3
            = $merlin_default_test_input_dir . 'temp/' .
            basename($this->file3, '.pptx');
        @exec("chmod -R 0777 {$this->tempFolder3}");
        deleteDirectory($this->tempFolder3);
        $this->tempFolder1
            = $merlin_default_test_input_dir . 'temp/' .
            basename($this->file1, '.pptx');
        @exec("chmod -R 0777 {$this->tempFolder1}");
        deleteDirectory($this->tempFolder1);
        include_once $rootDirectory . "/classes/mergeable_factory.php";
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
            echo 'Unable to find the PPTX file';
            exit;
        }
        $this->package3 = new ZipArchive();
        if (!$this->package3->open($this->file3)) {
            echo 'Unable to find the PPTX file';
            exit;
        }
        $this->package3->tempFolder = $this->tempFolder3;
        $this->package3->extractTo($this->tempFolder3);
        @exec("chmod -R 0777 {$this->tempFolder3}");
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
     * Tests the _rels/.rels contents
     *
     * @author Pooja Pednekar
     * @access public
     * @return null
     */
    public function testAppendRelsFunction()
    {
        $this->package = new ZipArchive();
        $this->package->open($this->file1);
        $this->package->tempFolder = $this->tempFolder1;
        $this->package->extractTo($this->tempFolder1);
        @exec("chmod -R 0777 {$this->tempFolder1}");
        $this->assertEqual(
            strcmp(
                trim($this->package->getFromName('_rels/.rels')),
                trim($this->package3->getFromName('_rels/.rels'))
            ), 0, 'SetRels did not set the relationships properly'
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
            empty($diff) ? true : false, 'SetContentTypes is not set correctly'
        );
    }

    /**
     * Tests the file contents of ppt/_rels/presentation.xml.rels file
     *
     * @author Pooja Pednekar
     * @access public
     * @return null
     */
    public function testPptRelsFunction()
    {
        $rels = simplexml_load_string(
            $this->package->getFromName('ppt/_rels/presentation.xml.rels')
        );
        $testRels = simplexml_load_string(
            $this->package3->getFromName('ppt/_rels/presentation.xml.rels')
        );
        $rels = objectsIntoArray($rels);
        $testRels = objectsIntoArray($testRels);
        $diff = array_diff($rels, $testRels);
        $this->assertTrue(
            empty($diff) ? true : false, 'SetPptRels is not set relations correctly'
        );
    }

    /**
     * Tests the file contents of ppt/presentation.xml file
     *
     * @author Pooja Pednekar
     * @access public
     * @return null
     */
    public function testAppendPresentationFunction()
    {
        $this->assertEqual(
            strcmp(
                trim($this->package->getFromName('ppt/presentation.xml')),
                trim($this->package3->getFromName('ppt/presentation.xml'))
            ),
            0, 'SetPresentation did not set the presentation contents properly'
        );
    }

    /**
     * Tests the file contents of all slides
     *
     * @author Pooja Pednekar
     * @access public
     * @return null
     */
    public function testSlides()
    {
        $file3Slides = getMaxFilesInDir($this->package3, 'ppt/slides', '.xml');
        $mergedSlides = getMaxFilesInDir($this->package, 'ppt/slides', '.xml');
        $this->assertEqual(
            $file3Slides, $mergedSlides,
            'Slides in two files did not get merged properly'
        );

        $file3SlidesRels = getMaxFilesInDir(
            $this->package3, 'ppt/slides/_rels', '.xml.rels'
        );
        $mergedSlidesRels = getMaxFilesInDir(
            $this->package, 'ppt/slides/_rels', '.xml.rels'
        );
        $this->assertEqual(
            $file3Slides, $mergedSlides,
            'Slide relations in two files did not get merged properly'
        );

        if ($file3SlidesRels == $mergedSlidesRels) {
            for ($i = 1; $i < $file3SlidesRels; $i++) {
                $this->assertEqual(
                    strcmp(
                        trim(
                            $this->package->getFromName("ppt/slides/slide{$i}.xml")
                        ),
                        trim(
                            $this->package3->getFromName("ppt/slides/slide{$i}.xml")
                        )
                    ), 0,
                    "Contents of slide{$i} was not set properly"
                );
                $this->assertEqual(
                    strcmp(
                        trim(
                            $this->package->getFromName(
                                "ppt/slides/_rels/slide{$i}.xml.rels"
                            )
                        ),
                        trim(
                            $this->package3->getFromName(
                                "ppt/slides/_rels/slide{$i}.xml.rels"
                            )
                        )
                    ),
                    0, "Contents of slide{$i} rel was not set properly"
                );
            }
        }
    }

    /**
     * Tests the file contents of all themes
     *
     * @author Pooja Pednekar
     * @access public
     * @return null
     */
    public function testThemes()
    {
        $file3Themes = getMaxFilesInDir($this->package3, 'ppt/theme', '.xml');
        $mergedThemes = getMaxFilesInDir($this->package, 'ppt/theme', '.xml');
        $this->assertEqual(
            $file3Themes, $mergedThemes,
            'Themes in two files did not get merged properly'
        );

        if ($file3Themes == $mergedThemes) {
            for ($i = 1; $i < $file3Themes; $i++) {
                $this->assertEqual(
                    strcmp(
                        trim(
                            $this->package->getFromName("ppt/theme/theme{$i}.xml")
                        ),
                        trim(
                            $this->package3->getFromName("ppt/theme/theme{$i}.xml")
                        )
                    ),
                    0, "Contents of theme{$i} was not set properly"
                );
            }
        }
        $file3ThemesRels = getMaxFilesInDir(
            $this->package3, 'ppt/theme/_rels', '.xml.rels'
        );
        $mergedThemesRels = getMaxFilesInDir(
            $this->package, 'ppt/theme/_rels', '.xml.rels'
        );
        $this->assertEqual(
            $file3Themes, $mergedThemes,
            'Theme relations in two files did not get merged properly'
        );

        if ($file3ThemesRels == $mergedThemesRels) {
            for ($i = 1; $i < $file3ThemesRels; $i++) {
                $this->assertEqual(
                    strcmp(
                        trim(
                            $this->package->getFromName(
                                "ppt/theme/_rels/theme{$i}.xml.rels"
                            )
                        ),
                        trim(
                            $this->package3->getFromName(
                                "ppt/theme/_rels/theme{$i}.xml.rels"
                            )
                        )
                    ),
                    0, "Contents of theme{$i} rel was not set properly"
                );
            }
        }
    }

    /**
     * Tests the file contents of all slide masters
     *
     * @author Pooja Pednekar
     * @access public
     * @return null
     */
    public function testSlideMasters()
    {
        $file3slideMasters = getMaxFilesInDir(
            $this->package3, 'ppt/slideMasters', '.xml'
        );
        $mergedslideMasters = getMaxFilesInDir(
            $this->package, 'ppt/slideMasters', '.xml'
        );
        $this->assertEqual(
            $file3slideMasters, $mergedslideMasters,
            'slideMasters in two files did not get merged properly'
        );

        if ($file3slideMasters == $mergedslideMasters) {
            for ($i = 1; $i < $file3slideMasters; $i++) {
                $this->assertEqual(
                    strcmp(
                        trim(
                            $this->package->getFromName(
                                "ppt/slideMasters/slideMaster{$i}.xml"
                            )
                        ),
                        trim(
                            $this->package3->getFromName(
                                "ppt/slideMasters/slideMaster{$i}.xml"
                            )
                        )
                    ),
                    0, "Contents of slideMaster{$i} was not set properly"
                );
            }
        }
        $file3slideMastersRels = getMaxFilesInDir(
            $this->package3, 'ppt/slideMasters/_rels', '.xml.rels'
        );
        $mergedslideMastersRels = getMaxFilesInDir(
            $this->package, 'ppt/slideMasters/_rels', '.xml.rels'
        );
        $this->assertEqual(
            $file3slideMasters, $mergedslideMasters,
            'slideMasters relations in two files did not get merged properly'
        );

        if ($file3slideMastersRels == $mergedslideMastersRels) {
            for ($i = 1; $i < $file3slideMastersRels; $i++) {
                $this->assertEqual(
                    strcmp(
                        trim(
                            $this->package->getFromName(
                                "ppt/slideMasters/_rels/slideMaster{$i}.xml.rels"
                            )
                        ),
                        trim(
                            $this->package3->getFromName(
                                "ppt/slideMasters/_rels/slideMaster{$i}.xml.rels"
                            )
                        )
                    ),
                    0, "Contents of slideMaster{$i} rel was not set properly"
                );
            }
        }
    }

    /**
     * Tests the file contents of all slide layouts
     *
     * @author Pooja Pednekar
     * @access public
     * @return null
     */
    public function testSlideLayouts()
    {
        $file3SlideLayouts = getMaxFilesInDir(
            $this->package3, 'ppt/slideLayouts', '.xml'
        );
        $mergedSlideLayouts = getMaxFilesInDir(
            $this->package, 'ppt/slideLayouts', '.xml'
        );
        $this->assertEqual(
            $file3SlideLayouts, $mergedSlideLayouts,
            'SlideLayouts in two files did not get merged properly'
        );

        if ($file3SlideLayouts == $mergedSlideLayouts) {
            for ($i = 1; $i < $file3SlideLayouts; $i++) {
                $this->assertEqual(
                    strcmp(
                        trim(
                            $this->package->getFromName(
                                "ppt/slideLayouts/slideLayout{$i}.xml"
                            )
                        ),
                        trim(
                            $this->package3->getFromName(
                                "ppt/slideLayouts/slideLayout{$i}.xml"
                            )
                        )
                    ), 0, "Contents of slideLayout{$i} was not set properly"
                );
            }
        }
        $file3SlideLayoutsRels = getMaxFilesInDir(
            $this->package3, 'ppt/slideLayouts/_rels', '.xml.rels'
        );
        $mergedSlideLayoutsRels = getMaxFilesInDir(
            $this->package, 'ppt/slideLayouts/_rels', '.xml.rels'
        );
        $this->assertEqual(
            $file3SlideLayouts, $mergedSlideLayouts,
            'Slide Layout relations in two files did not get merged properly'
        );

        if ($file3SlideLayoutsRels == $mergedSlideLayoutsRels) {
            for ($i = 1; $i < $file3SlideLayoutsRels; $i++) {
                $this->assertEqual(
                    strcmp(
                        trim(
                            $this->package->getFromName(
                                "ppt/slideLayouts/_rels/slideLayout{$i}.xml.rels"
                            )
                        ),
                        trim(
                            $this->package3->getFromName(
                                "ppt/slideLayouts/_rels/slideLayout{$i}.xml.rels"
                            )
                        )
                    ),
                    0, "Contents of slideLayout{$i} rel was not set properly"
                );
            }
        }
    }

    /**
     * Tests the file contents of notes slides
     *
     * @author Pooja Pednekar
     * @access public
     * @return null
     */
    public function testNotesSlides()
    {
        $file3NotesSlides = getMaxFilesInDir(
            $this->package3, 'ppt/notesSlides', '.xml'
        );
        $mergedNotesSlides = getMaxFilesInDir(
            $this->package, 'ppt/notesSlides', '.xml'
        );
        $this->assertEqual(
            $file3NotesSlides, $mergedNotesSlides,
            'NotesSlides in two files did not get merged properly'
        );

        if ($file3NotesSlides == $mergedNotesSlides) {
            for ($i = 1; $i < $file3NotesSlides; $i++) {
                $this->assertEqual(
                    strcmp(
                        trim(
                            $this->package->getFromName(
                                "ppt/notesSlides/notesSlide{$i}.xml"
                            )
                        ),
                        trim(
                            $this->package3->getFromName(
                                "ppt/notesSlides/notesSlide{$i}.xml"
                            )
                        )
                    ),
                    0, "Contents of notesSlide{$i} was not set properly"
                );
            }
        }
        $file3NotesSlidesRels = getMaxFilesInDir(
            $this->package3, 'ppt/notesSlides/_rels', '.xml.rels'
        );
        $mergedNotesSlidesRels = getMaxFilesInDir(
            $this->package, 'ppt/notesSlides/_rels', '.xml.rels'
        );
        $this->assertEqual(
            $file3NotesSlides, $mergedNotesSlides,
            'Note Slide relations in two files did not get merged properly'
        );

        if ($file3NotesSlidesRels == $mergedNotesSlidesRels) {
            for ($i = 1; $i < $file3NotesSlidesRels; $i++) {
                $this->assertEqual(
                    strcmp(
                        trim(
                            $this->package->getFromName(
                                "ppt/notesSlides/_rels/notesSlide{$i}.xml.rels"
                            )
                        ),
                        trim(
                            $this->package3->getFromName(
                                "ppt/notesSlides/_rels/notesSlide{$i}.xml.rels"
                            )
                        )
                    ), 0, "Contents of notesSlide{$i} rel was not set properly"
                );
            }
        }
    }

    /**
     * Tests the file contents of all note masters
     *
     * @author Pooja Pednekar
     * @access public
     * @return null
     */
    public function testNotesMasters()
    {
        $file3notesMasters = getMaxFilesInDir(
            $this->package3, 'ppt/notesMasters', '.xml'
        );
        $mergednotesMasters = getMaxFilesInDir(
            $this->package, 'ppt/notesMasters', '.xml'
        );
        $this->assertEqual(
            $file3notesMasters, $mergednotesMasters,
            'notesMasters in two files did not get merged properly'
        );

        if ($file3notesMasters == $mergednotesMasters) {
            for ($i = 1; $i < $file3notesMasters; $i++) {
                $this->assertEqual(
                    strcmp(
                        trim(
                            $this->package->getFromName(
                                "ppt/notesMasters/notesMaster{$i}.xml"
                            )
                        ),
                        trim(
                            $this->package3->getFromName(
                                "ppt/notesMasters/notesMaster{$i}.xml"
                            )
                        )
                    ), 0,
                    "Contents of notesMaster{$i} was not set properly"
                );
            }
        }
        $file3notesMastersRels = getMaxFilesInDir(
            $this->package3, 'ppt/notesMasters/_rels', '.xml.rels'
        );
        $mergednotesMastersRels = getMaxFilesInDir(
            $this->package, 'ppt/notesMasters/_rels', '.xml.rels'
        );
        $this->assertEqual(
            $file3notesMasters, $mergednotesMasters,
            'Note Master relations in two files did not get merged properly'
        );

        if ($file3notesMastersRels == $mergednotesMastersRels) {
            for ($i = 1; $i < $file3notesMastersRels; $i++) {
                $this->assertEqual(
                    strcmp(
                        trim(
                            $this->package->getFromName(
                                "ppt/notesMasters/_rels/notesMaster{$i}.xml.rels"
                            )
                        ),
                        trim(
                            $this->package3->getFromName(
                                "ppt/notesMasters/_rels/notesMaster{$i}.xml.rels"
                            )
                        )
                    ),
                    0, "Contents of notesMaster{$i} rel was not set properly"
                );
            }
        }
    }

    /**
     * Tests the pptx media
     *
     * @author Pooja Pednekar
     * @access public
     * @return null
     */
    public function testMedia()
    {
        $file3Media = getMaxFilesInDir($this->package3, 'ppt/media');
        $mergedMedia = getMaxFilesInDir($this->package, 'ppt/media');
        $this->assertEqual(
            $file3Media, $mergedMedia,
            'Media in two files did not get merged properly'
        );
    }

}

?>