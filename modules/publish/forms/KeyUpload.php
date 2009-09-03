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
 * @author      Oliver Marahrens <o.marahrens@tu-harburg.de>
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id: KeyUploadForm.php 2281 2009-03-26 13:53:34Z marahrens $
 */

/**
 * form to show the search mask
 */
class KeyUpload extends Zend_Form
{
    /**
     * Build easy upload form
     *
     * @return void
     */
    public function init() {
        // FIXME: Make hard coded path configurable.
        $keyupload = new Zend_Form_Element_File('keyupload');
        $keyupload->setLabel('keyFileToUpload')
            ->setDestination('../workspace/tmp/')
            ->addValidator('Count', false, 1)     // ensure only 1 file
            ->addValidator('Size', false, 102400); // limit to 100K

        $key_upload_token = new Zend_Form_Element_Hidden('gpg_key_upload');
        $key_upload_token->setValue('true');

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('pkm_process');

        $this->addElements(array($keyupload, $key_upload_token, $submit));
        $this->setAttrib('enctype', Zend_Form::ENCTYPE_MULTIPART);
    }
}