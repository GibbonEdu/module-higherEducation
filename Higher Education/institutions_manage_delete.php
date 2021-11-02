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

use Gibbon\Forms\Prefab\DeleteForm;

//Module includes
include __DIR__.'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/institutions_manage_delete.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Institutions'), 'institutions_manage.php');
    $page->breadcrumbs->add(__('Delete Institution'));

    $role = staffHigherEducationRole($session->get('gibbonPersonID'), $connection2);
    if ($role != 'Coordinator') {
        $page->addError(__('You do not have access to this action.'));
    } else {
        //Check if school year specified
        $higherEducationInstitutionID = $_GET['higherEducationInstitutionID'];
        if ($higherEducationInstitutionID == '') {
            $page->addError(__('You have not specified a student member.'));
        } else {
            try {
                $data = array('higherEducationInstitutionID' => $higherEducationInstitutionID);
                $sql = 'SELECT * FROM higherEducationInstitution WHERE higherEducationInstitutionID=:higherEducationInstitutionID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $page->addError($e->getMessage());
            }

            if ($result->rowCount() != 1) {
                $page->addError(__('The selected student member does not exist.'));
            } else {
                //Let's go!
                $form = DeleteForm::createForm($session->get('absoluteURL').'/modules/'.$session->get('module')."/institutions_manage_deleteProcess.php?higherEducationInstitutionID=$higherEducationInstitutionID", true);
                $form->addHiddenValue('higherEducationInstitutionID', $higherEducationInstitutionID);
                echo $form->getOutput();
            }
        }
    }
}
