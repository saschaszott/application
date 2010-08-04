<?php

/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the Cooperative Library Network Berlin-Brandenburg,
 * the Saarland University and State Library, the Saxon State Library -
 * Dresden State and University Library, the Bielefeld University Library and
 * the University Library of Hamburg University of Technology with funding from
 * the German Research Foundation and the European Regional Development Fund.
 *
 * LICENCE
 * OPUS is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or any later version.
 * OPUS is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details. You should have received a copy of the GNU General Public License
 * along with OPUS; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * @category    Application
 * @package     Module_Search
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
// Configure include path.
set_include_path('.' . PATH_SEPARATOR
        . PATH_SEPARATOR . dirname(__FILE__)
        . PATH_SEPARATOR . dirname(dirname(__FILE__)) . '/library'
        . PATH_SEPARATOR . get_include_path());

// Zend_Loader is'nt available yet. We have to do a require_once
// in order to find the bootstrap class.
require_once 'Application/Bootstrap.php';

/**
 * Bootstraps and runs an import from Opus3
 *
 * @category Search
 */
class SolrIndexBuilder extends Application_Bootstrap {
    private $start = 0;
    private $end = -1;
    private $deleteAllDocs = false;

    /**
     * Prints a help message to the console.
     */
    private function printHelpMessage($argv) {
        echo "\nThis program can be used to build up an initial Solr index (e.g., useful when migrating instances)\n\n";
        echo "Usage: " . $argv[0] . " [starting with ID] [ending with ID]\n";
        echo "\n";
        echo "[starting with ID] If system aborted indexing at some ID, you can restart this command by supplying this parameter.\n";
        echo "It should be the ID where the program stopped before.\n";
        echo "Default start value is 0.\n";
        echo "\n";
        echo "[ending with ID] You can also supply a second ID where the indexer should stop indexing.\n";
        echo "If you omit this parameter or set it to -1, the indexer will index all remaining documents.\n";
        echo "\n";
        echo "In case both parameters are not specified the currently used index is deleted before insertion of new documents begins.\n";
        echo "\n";
    }

    /**
     * Evaluates command line arguments.
     */
    private function evaluateArguments($argc, $argv) {
        if ($argc >= 2) {
            $this->start = $argv[1];
        }
        if ($argc >= 3) {
            $this->end = $argv[2];
        }
        if ($this->start === 0 && $this->end === -1) {
            // TODO gesondertes Argument für Indexdeletion einführen
            $this->deleteAllDocs = true;
        }
    }

    /**
     * Starts an Opus console.     
     */
    public function _run() {
        global $argv, $argc;
        if (true === in_array('--help', $argv) || true === in_array('-h', $argv)) {
            $this->printHelpMessage($argv);
            exit;
        }
        $this->evaluateArguments($argc, $argv);
        $docIds = Opus_Document::getAllPublishedIds($this->start, $this->end);
        $indexer = new Opus_Search_Index_Solr_Indexer($this->deleteAllDocs);
        //$indexer = new Opus_Search_Index_Solr_Indexer();
        echo date('Y-m-d H:i:s') . " Start indexing of " . count($docIds) . " documents.\n";
        foreach ($docIds as $docId) {
            if ($docId < $this->start || ($this->end !== -1 && $docId > $this->end)) {
                continue;
            }
            $indexer->addDocumentToEntryIndex(new Opus_Document((int) $docId));
        }
        echo date('Y-m-d H:i:s') . " Finished indexing!\n";
        $indexer->finalize();
    }
}

$index = new SolrIndexBuilder;
try {
    $index->run(dirname(dirname(__FILE__)), Opus_Bootstrap_Base::CONFIG_TEST, dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'config');
    echo "\nOperation completed successfully.\n";
}
catch (Exception $e) {
    echo "\nAn error occurred while indexing.";
    echo "\nError Message: " . $e->getMessage();
    echo "\nStack Trace: " . $e->getTraceAsString();
    echo "\n\n";
}