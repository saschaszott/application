<?php
/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the North Rhine-Westphalian Library Service Center,
 * the Cooperative Library Network Berlin-Brandenburg, the Saarland University
 * and State Library, the Saxon State Library - Dresden State and University
 * Library, the Bielefeld University Library and the University Library of
 * Hamburg University of Technology with funding from the German Research
 * Foundation and the European Regional Development Fund.
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
 * @package     Module_Oai
 * @author      Ralf Claußnitzer <ralf.claussnitzer@slub-dresden.de>
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id: Configuration.php 4695 2009-11-06 10:12:59Z claussni $
 */

/**
 * Configuration model holding OAI module config options
 * gathered from Zend_Registry and application configuration.
 *
 * @category    Application
 * @package     Module_Oai
 */
class Oai_Model_Configuration {

    /**
     * Holds ddb contact id
     *
     * @var string
     */
    private $_ddb_contact_id = '';

    /**
     * Hold URL prefix for document frontdoor. OAI module will replace 
     * $docid with the document id.
     *
     * E.g. "http://www.example.com/frontdoor?document=$docid"
     *
     * @var string
     */
    private $_frontdoor_url = '';
    
    /**
     * Hold URL prefix for file download. OAI module will replace
     * $docid with the document id and $filename with a real filename.
     *
     * E.g. "http://www.example.com/fileadmin/$docid/$filename"
     *
     * @var string
     */
    private $_file_url = '';
    
    
    /**
     * Hold path where to store temporary resumption token files.
     *
     * @var string
     */
    private $_path_tokens = '';
    
    
    /**
     * Holds email address of repository contact person.
     *
     * @var string
     */
    private $_email_contact = '';
    
    /**
     * Holds repository name.
     *
     * @var string
     */
    private $_repo_name = '';
    
    /**
     * Holds repository identifier.
     *
     * @var string
     */
    private $_repo_id = '';
    
    /**
     * Holds sample identifier.
     *
     * @var string
     */
    private $_sample_id = '';
    
    /**
     * Holds maximum number of identifiers to list per request.
     *
     * @var int
     */
    private $_max_list_ids = 10;
    
    /**
     * Holds maximum number of records to list per request.
     *
     * @var int
     */
    private $_max_list_recs = 10;

    /**
     * Holds oai base url. If not given, local server name will be used.
     *
     * @var string
     */
    private $_oai_baseurl = '';

    /**
     * Collect configuration information from Zend_Config instance.
     *
     * @throws Exception Thrown if no oai section is set.
     */
    public function __construct(Zend_Config $config) {
        if (false === isset($config->oai)) {
            throw new Exception('No configuration for module oai.');
        }
    
        if (true === isset($config->oai->ddb->contactid)) {
            $this->_ddb_contact_id = $config->oai->ddb->contactid;
        }
        if (true === isset($config->oai->repository->frontdoorurl)) {
            $this->_frontdoor_url = $config->oai->repository->frontdoorurl;
        }
        if (true === isset($config->oai->repository->fileurl)) {
            $this->_file_url = $config->oai->repository->fileurl;
        }
        if (true === isset($config->oai->repository->name)) {
            $this->_repo_name = $config->oai->repository->name;
        }
        if (true === isset($config->oai->repository->identifier)) {
            $this->_repo_id = $config->oai->repository->identifier;
        }
        if (true === isset($config->oai->sample->identifier)) {
            $this->_sample_id = $config->oai->sample->identifier;
        }
        if (true === isset($config->oai->max->listidentifiers)) {
            $this->_max_list_ids = $config->oai->max->listidentifiers;
        }
        if (true === isset($config->oai->max->listrecords)) {
            $this->_max_list_recs = $config->oai->max->listrecords;
        }
        if (true === isset($config->oai->baseurl)) {
            $this->_oai_baseurl = $config->oai->baseurl;
        }

        if (true === isset($config->workspacePath)) {
            $this->_path_tokens = $config->workspacePath
                . DIRECTORY_SEPARATOR .'tmp' 
                . DIRECTORY_SEPARATOR . 'resumption';
        }
        
        if (true === isset($config->mail->opus->address)) {
            $this->_email_contact = $config->mail->opus->address;
        }
    }

    /**
     * Return configured ddb contact id.
     *
     * @return string Ddb ontact id.
     */
    public function getDdbContactId() {
        return $this->_ddb_contact_id;
    }
 
    /**
     * Return configured frontdoor URL pattern.
     *
     * @return string Frontdoor URL pattern.
     */
    public function getFrontdoorUrl() {
        return $this->_frontdoor_url;
    }
    
    
    /**
     * Return configured file URL pattern.
     *
     * @return string File URL pattern.
     */
    public function getFileUrl() {
        return $this->_file_url;
    }
    
    /**
     * Return temporary path for resumption tokens.
     *
     * @return string Path.
     */
    public function getResumptionTokenPath() {
        return $this->_path_tokens;
    }
    
    /**
     * Return contact email address.
     *
     * @return string Email address.
     */
    public function getEmailContact() {
        return $this->_email_contact;
    }

    /**
     * Return OAI base url.
     *
     * @return string Oai base url.
     */
    public function getOaiBaseUrl() {
        return $this->_oai_baseurl;
    }

    /**
     * Return repository name.
     *
     * @return string Repository name.
     */
    public function getRepositoryName() {
        return $this->_repo_name;
    }
    
    /**
     * Return repository identifier.
     *
     * @return string Repository identifier.
     */
    public function getRepositoryIdentifier() {
        return $this->_repo_id;
    }

    /**
     * Return sample identifier.
     *
     * @return string Sample identifier.
     */
    public function getSampleIdentifier() {
        return $this->_sample_id;
    }
    
    /**
     * Return maximum number of listable identifiers per request.
     *
     * @return int Maximum number of listable identifiers per request.
     */
    public function getMaxListIdentifiers() {
        return $this->_max_list_ids;
    }

    /**
     * Return maximum number of listable records per request.
     *
     * @return int Maximum number of listable records per request.
     */
    public function getMaxListRecords() {
        return $this->_max_list_recs;
    }

}

