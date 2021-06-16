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

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/majors_manage_delete.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Majors'), 'majors_manage.php');
    $page->breadcrumbs->add(__('Delete Major'));

    $role = staffHigherEducationRole($session->get('gibbonPersonID'), $connection2);
    if ($role != 'Coordinator') {
        $page->addError(__('You do not have access to this action.'));
    } else {
        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }

        //Check if school year specified
        $higherEducationMajorID = $_GET['higherEducationMajorID'];
        if ($higherEducationMajorID == '') {
            $page->addError(__('You have not specified a student member.'));
        } else {
            try {
                $data = array('higherEducationMajorID' => $higherEducationMajorID);
                $sql = 'SELECT * FROM higherEducationMajor WHERE higherEducationMajorID=:higherEducationMajorID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
            }

            if ($result->rowCount() != 1) {
                $page->addError(__('The selected student member does not exist.'));
            } else {
                //Let's go!
                $form = DeleteForm::createForm($session->get('absoluteURL').'/modules/'.$session->get('module')."/majors_manage_deleteProcess.php?higherEducationMajorID=$higherEducationMajorID");
                $form->addHiddenValue('higherEducationMajorID', $higherEducationMajorID);
                echo $form->getOutput();
            }
        }
    }
}
