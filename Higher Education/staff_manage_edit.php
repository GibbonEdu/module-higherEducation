<?php
/*
Gibbon: the flexible, open school platform
Founded by Ross Parker at ICHK Secondary. Built by Ross Parker, Sandra Kuipers and the Gibbon community (https://gibbonedu.org/about/)
Copyright © 2010, Gibbon Foundation
Gibbon™, Gibbon Education Ltd. (Hong Kong)

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
use Gibbon\Services\Format;

//Module includes
include __DIR__.'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/staff_manage_edit.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Staff'), 'staff_manage.php');
    $page->breadcrumbs->add(__('Edit Staff'));

    //Check if school year specified
    $higherEducationStaffID = $_GET['higherEducationStaffID'];
    if ($higherEducationStaffID == 'Y') {
        $page->addError(__('You have not specified an activity.'));
    } else {
        try {
            $data = array('higherEducationStaffID' => $higherEducationStaffID);
            $sql = "SELECT higherEducationStaffID, higherEducationStaff.role, surname, preferredName FROM higherEducationStaff JOIN gibbonPerson ON (higherEducationStaff.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE status='Full' AND higherEducationStaffID=:higherEducationStaffID";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
        }

        if ($result->rowCount() != 1) {
            $page->addError(__('The selected activity does not exist.'));
        } else {
            //Let's go!
            $values = $result->fetch();

            $form = Form::create('staff', $session->get('absoluteURL').'/modules/'.$session->get('module').'/staff_manage_editProcess.php?higherEducationStaffID='.$higherEducationStaffID);
            $form->addHiddenValue('address', $session->get('address'));

            $row = $form->addRow();
                $row->addLabel('name', __('Staff'));
                $row->addTextField('name')->readonly()->setValue(Format::name('', $values['preferredName'], $values['surname'], 'Staff', true, true));

            $roles = array(
                'Coordinator' => __('Coordinator'),
                'Advisor' => __('Advisor'),
            );
            $row = $form->addRow();
                $row->addLabel('role', __('Role'));
                $row->addSelect('role')->fromArray($roles)->isRequired()->placeholder();

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            $form->loadAllValuesFrom($values);

            echo $form->getOutput();
        }
    }
}
