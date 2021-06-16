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
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

//Module includes
include __DIR__.'/moduleFunctions.php';
use Gibbon\Forms\Form;
use Gibbon\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/references_manage_addMulti.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage References'), 'references_manage.php', [
        'gibbonSchoolYearID' => $_GET['gibbonSchoolYearID'] ?? '',
        'search' => $_GET['search'] ?? '',
    ]);
    $page->breadcrumbs->add(__('Add References'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $role = staffHigherEducationRole($session->get('gibbonPersonID'), $connection2);
    if ($role == false) {
        //Acess denied
        $page->addError(__('You are not enroled in the Higher Education programme.'));
    } else {
        if ($role != 'Coordinator') {
            //Acess denied
            $page->addError(__('You do not have permission to access this page.'));
        } else {
            $form = Form::create('referencesManageAddMulti',$session->get('absoluteURL').'/modules/'.$session->get('module').'/references_manage_addMultiProcess.php?gibbonSchoolYearID='.$_GET['gibbonSchoolYearID'].'&search='.$_GET['search']);
            $form->setFactory(DatabaseFormFactory::create($pdo));
            $form->addHiddenValue('address', $session->get('address'));
        
                $data = array('gibbonSchoolYearID' => $session->get('gibbonSchoolYearID'));
                $sql = "SELECT gibbonPerson.gibbonPersonID as value, concat(gibbonFormGroup.nameShort, ' - ', gibbonPerson.surname,', ',gibbonPerson.firstName) as name FROM gibbonPerson JOIN gibbonStudentEnrolment ON (gibbonPerson.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) JOIN gibbonFormGroup ON  (gibbonStudentEnrolment.gibbonFormGroupID=gibbonFormGroup.gibbonFormGroupID) JOIN higherEducationStudent ON (higherEducationStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE status='FULL' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND gibbonFormGroup.gibbonSchoolYearID=:gibbonSchoolYearID ORDER BY nameShort, surname, preferredName";
            
             $row = $form->addRow();
                    $row->addLabel('gibbonPersonIDMulti[]', __('Students'));
                    $row->addSelect('gibbonPersonIDMulti[]')->fromQuery($pdo, $sql, $data)->selectMultiple()->placeholder()->isRequired();
            
            $types = [
                'Composite Reference' =>__('Composite Reference'),
                'US Reference' => __('US Reference'),
            ];

            $row = $form->addRow();
                $row->addLabel('type', __('Type'));
                $row->addSelect('type')->fromArray($types)->placeholder()->isRequired();

            $form->toggleVisibilityByCLass('gibbonPersonIDReferee')->onSelect('type')->when('US Reference');
            $row = $form->addRow()->addClass('gibbonPersonIDReferee');
                $row->addLabel('gibbonPersonIDReferee', __('Referee'))->description(__('The teacher you wish to write the reference.'));
                $row->addSelectStaff('gibbonPersonIDReferee')->placeholder()->isRequired();
                
            $row = $form->addRow();
            $column = $row->addColumn();
                $column->addLabel('notes', __('Notes'))->description(__('Any information you need to share with the referee(s)'));
                $column->addTextArea('notes')->setRows(4)->setClass('w-full');
                    
            $row = $form->addRow();
                    $row->addFooter();
                    $row->addSubmit();
                echo $form->getOutput();
        }
    }
}
?>
