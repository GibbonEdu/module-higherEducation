<?php
use Gibbon\Forms\Form;
use Gibbon\Forms\DatabaseFormFactory;
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/references_manage_edit.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Check if school year specified
    $gibbonSchoolYearID = $_GET['gibbonSchoolYearID'];
    $higherEducationReferenceComponentID = $_GET['higherEducationReferenceComponentID'];
    $higherEducationReferenceID = $_GET['higherEducationReferenceID'];
    if ($higherEducationReferenceComponentID == '' or $higherEducationReferenceID == '' or $gibbonSchoolYearID == '') {
        $page->addError(__('You have not specified a grade scale or grade.'));
    } else {
        try {
            $data = array('higherEducationReferenceID' => $higherEducationReferenceID, 'higherEducationReferenceComponentID' => $higherEducationReferenceComponentID);
            $sql = 'SELECT higherEducationReferenceComponent.*, higherEducationReference.type AS refType FROM higherEducationReference JOIN higherEducationReferenceComponent ON (higherEducationReference.higherEducationReferenceID=higherEducationReferenceComponent.higherEducationReferenceID) WHERE higherEducationReferenceComponent.higherEducationReferenceID=:higherEducationReferenceID AND higherEducationReferenceComponent.higherEducationReferenceComponentID=:higherEducationReferenceComponentID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $page->addError($e->getMessage());
        }

        if ($result->rowCount() != 1) {
            $page->addError(__('The specified class cannot be found.'));
        } else {
            //Let's go!
            $values = $result->fetch();

            $urlParams = [
                'higherEducationReferenceID' => $higherEducationReferenceID,
                'gibbonSchoolYearID' => $gibbonSchoolYearID,
            ];

            $page->breadcrumbs
                ->add(__('Manage References'), 'references_manage.php', $urlParams)
                ->add(__('Edit Reference'), 'references_manage_edit.php', $urlParams)
                ->add(__('Edit Contribution'));

            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, null);
            }
            $form = Form::create('editContribution', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/references_manage_edit_contribution_editProcess.php?higherEducationReferenceComponentID=$higherEducationReferenceComponentID&higherEducationReferenceID=$higherEducationReferenceID&gibbonSchoolYearID=$gibbonSchoolYearID");
            $form->setFactory(DatabaseFormFactory::create($pdo));
            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('higherEducationReferenceID', $higherEducationReferenceID);

            $row = $form->addRow();
                $row->addLabel('type', __('Contribution Type'));
                $row->addTextField('type')->required()->readOnly()->maxLength(255);

            $row = $form->addRow();
                $row->addLabel('title', __('Title'));
                $row->addTextField('title')->required()->maxLength(255);

            $row = $form->addRow();
                  $row->addLabel('gibbonPersonID', __('Author'));
                  $row->addSelectStaff('gibbonPersonID')->placeholder();

            $col = $form->addRow()->addColumn();
            if ($values['refType'] == 'US Reference') {
                $col->addLabel('body', __('Reference'))->description(__('Maximum limit of 10,000 Characters'));
                $col->addTextArea('body')->setRows(20)->maxLength(10000)->setClass('w-full');
            } else {
                    $col->addLabel('body', __('Reference'))->description(__('Maximum limit of 2,000 Characters'));
                    $col->addTextArea('body')->setRows(20)->maxLength(2000)->setClass('w-full');
            }
            
            $row = $form->addRow();
                $row->addLabel('status', __('Status'));
                $row->addSelect('status')->fromArray(['In Progress' =>__('In Progress'), 'Complete' => __('Complete')])->isRequired();

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            $form->loadAllValuesFrom($values);

            echo $form->getOutput();
        }
    }
}
