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
 * @package     Module_Mobile
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2016, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Controller for mobile app interface.
 *
 * TODO figure out JSON format for documents and search results
 * TODO place preprocessing and such in separate classes
 */
class Mobile_IndexController extends Application_Controller_Action {

    public function init() {
        parent::init();

        $this->_helper->layout()->disableLayout();
    }

    /**
     * Loads mobile web application.
     */
    public function indexAction() {
    }

    /**
     * Returns suggestions for search box.
     *
     * TODO get dynamically from index
     */
    public function autocompleteAction() {
        $suggestions = array(
            'Application',
            'Big',
            'Computer',
            'Development',
            'Environment',
            'Failure',
            'Green',
            'Hope',
            'Injection',
            'Java',
            'Kilo'
        );

        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();

        echo json_encode($suggestions);
    }

    /**
     * Returns search results as json.
     */
    public function searchAction() {
        $search = $this->getParam('search');


        $queryBuilder = new Application_Util_QueryBuilder($this->getLogger());

        $queryBuilderInput = array(
            'searchtype' => 'simple',
            'start' => '0',
            'rows' => '10',
            'sortOrder' => 'desc',
            'sortField'=> 'score',
            'docId' => null,
            'query' => $search,
            'author' => '',
            'modifier' => 'contains_all',
            'title' => '',
            'titlemodifier' => 'contains_all',
            'persons' => '',
            'personsmodifier' => 'contains_all',
            'referee' => '',
            'refereemodifier' => 'contains_all',
            'abstract' => '',
            'abstractmodifier' => 'contains_all',
            'fulltext' => '',
            'fulltextmodifier' => 'contains_all',
            'year' => '',
            'yearmodifier' => 'contains_all',
            'author_facetfq' => '',
            'languagefq' => '',
            'yearfq' => '',
            'doctypefq' => '',
            'has_fulltextfq' => '',
            'belongs_to_bibliographyfq' => '',
            'subjectfq' => '',
            'institutefq' => ''
        );


        $query = $queryBuilder->createSearchQuery($queryBuilderInput, $this->getLogger());

        $result = array();

        $searcher = new Opus_SolrSearch_Searcher();
        try {
            $result = $searcher->search($query);
        }
        catch (Opus_SolrSearch_Exception $ex) {
            var_dump($ex);
        }

        $matches = $result->getReturnedMatches();

        $this->view->total = $result->getAllMatchesCount();
        $this->view->matches = $matches;
    }

    public function doctypesAction() {
        $facetname = 'doctype';
        $query = new Opus_SolrSearch_Query(Opus_SolrSearch_Query::FACET_ONLY);
        $query->setFacetField($facetname);
        $facets = array();

        try {
            $searcher = new Opus_SolrSearch_Searcher();
            $facets = $searcher->search($query)->getFacets();
        }
        catch (Opus_SolrSearch_Exception $e) {
            $this->getLogger()->err(__METHOD__ . ' : ' . $e);
            throw new Application_SearchException($e);
        }

        $docTypesTranslated = array();
        foreach ($facets[$facetname] as $facetitem) {
            $translation = $this->view->translate($facetitem->getText());
            $docTypesTranslated[$translation] = $facetitem;
        }
        uksort($docTypesTranslated, "strnatcasecmp");
        $this->view->facetitems = $docTypesTranslated;
        $this->view->title = $this->view->translate('solrsearch_browse_doctypes');
    }

    /**
     * Returns document information in json.
     *
     * The standard array representation of a document contains all the metadata, but it makes it necessary to do a lot
     * of processing on the client side, for instance to assemble dates. Some preprocessing makes sense.
     *
     * TODO simplify metadata to reduce processing on client side
     */
    public function showAction() {
        $docId = $this->getParam('id');

        // TODO verify parameter

        $document = new Opus_Document($docId);

        $this->_helper->layout()->disableLayout();

        $this->view->doc = $document;
        $this->view->document = new Application_Util_DocumentAdapter($this->view, $document);
    }

    public function aboutAction() {
        $this->_helper->layout()->disableLayout();
    }

    /**
     * Returns document as JSON.
     *
     * This function is necessary in order to reduce the complexity of processing necessary on the client side. Dates
     * are returned in a specific format.
     *
     * TODO simplify return of main title of document
     *
     * @param $document
     * @return string
     */
    public function convertDocumentToJson($document) {
        $result = array();

        $fieldNames = $document->describe();

        foreach ($fieldNames as $fieldname) {
            $field = $document->getField($fieldname);
            $fieldvalue = $field->getValue();

            if (!$field->hasMultipleValues()) {
                $fieldvalue = array($fieldvalue);
            }

            $fieldvalues = array();
            foreach ($fieldvalue as $value) {
                if ($value instanceof Opus_Date) {
                    $fieldvalues[] = $value->getZendDate()->get('yyyy/MM/dd');
                }
                else if ($value instanceof Opus_Model_Abstract) {
                    $fieldvalues[] = $value->toArray();
                }
                else if ($value instanceOf Zend_Date) {
                    $fieldvalues[] = $value->toArray();
                }
                else {
                    $fieldvalues[] = $value;
                }
            }

            if (!$field->hasMultipleValues()) {
                $fieldvalues = $fieldvalues[0];
            }

            $result[$fieldname] = $fieldvalues;
        }
        return json_encode($result);
    }

}
