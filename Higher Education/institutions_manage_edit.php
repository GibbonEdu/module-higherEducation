<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

use Gibbon\Forms\Form;
use Gibbon\Forms\DatabaseFormFactory;

//Module includes
include __DIR__.'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/institutions_manage_edit.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Institutions'), 'institutions_manage.php');
    $page->breadcrumbs->add(__('Edit Institution'));

    $role = staffHigherEducationRole($session->get('gibbonPersonID'), $connection2);
    if ($role != 'Coordinator') {
        $page->addError(__('You do not have access to this action.'));
    } else {
        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }

        //Check if school year specified
        $higherEducationInstitutionID = $_GET['higherEducationInstitutionID'];
        if ($higherEducationInstitutionID == 'Y') {
            $page->addError(__('You have not specified an activity.'));
        } else {
            try {
                $data = array('higherEducationInstitutionID' => $higherEducationInstitutionID);
                $sql = 'SELECT * FROM higherEducationInstitution WHERE higherEducationInstitutionID=:higherEducationInstitutionID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $page->addError(__('The student cannot be edited due to a database error.'));
            }

            if ($result->rowCount() != 1) {
                $page->addError(__('The selected activity does not exist.'));
            } else {
                //Let's go!
                $values = $result->fetch();

                $form = Form::create('institutions', $session->get('absoluteURL').'/modules/'.$session->get('module').'/institutions_manage_editProcess.php?higherEducationInstitutionID='.$higherEducationInstitutionID);
                $form->setFactory(DatabaseFormFactory::create($pdo));
                $form->addHiddenValue('address', $session->get('address'));

                $row = $form->addRow();
                    $row->addLabel('name', __('Name'));
                    $row->addTextField('name')->isRequired()->maxLength(150);

                $row = $form->addRow();
                    $row->addLabel('country', __('Country'));
                    $row->addSelectCountry('country')->isRequired();

                $row = $form->addRow();
                    $row->addLabel('active', __('Active'));
                    $row->addYesNo('active')->isRequired();

                $row = $form->addRow();
                    $row->addFooter();
                    $row->addSubmit();

                $form->loadAllValuesFrom($values);

                echo $form->getOutput();
            }
        }
    }
}
