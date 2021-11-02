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


if (isActionAccessible($guid, $connection2, '/modules/Higher Education/applications_track_edit.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Track Applications'), 'applications_track.php')
        ->add(__('Edit Application'));

    //Check for student enrolment
    if (studentEnrolment($session->get('gibbonPersonID'), $connection2) == false) {
        $page->addError(__('You have not been enrolled for higher education applications.'));
    } else {
        //Check for application record
        try {
            $data = array('gibbonPersonID' => $session->get('gibbonPersonID'));
            $sql = 'SELECT * FROM  higherEducationApplication WHERE gibbonPersonID=:gibbonPersonID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $page->addError($e->getMessage());
        }

        if ($result->rowCount() != 1) {
            $page->addError(__('You have not saved your application process yet.'));
        } else {
            $row = $result->fetch();

            //Check if school year specified
            $higherEducationApplicationInstitutionID = $_GET['higherEducationApplicationInstitutionID'];
            if ($higherEducationApplicationInstitutionID == '') {
                $page->addError(__('You have not specified an application.'));
            } else {
                try {
                    $data = array('higherEducationApplicationInstitutionID' => $higherEducationApplicationInstitutionID);
                    $sql = 'SELECT * FROM higherEducationApplicationInstitution JOIN higherEducationInstitution ON (higherEducationApplicationInstitution.higherEducationInstitutionID=higherEducationInstitution.higherEducationInstitutionID) WHERE higherEducationApplicationInstitutionID=:higherEducationApplicationInstitutionID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $page->addError($e->getMessage());
                }

                if ($result->rowCount() != 1) {
                    $page->addError(__('The specified application cannot be found.'));
                } else {
                    //Let's go!
                    $values = $result->fetch(); 

                    $form = Form::create('applicationsTrackEdit', $session->get('absoluteURL').'/modules/'.$session->get('module')."/applications_track_editProcess.php?higherEducationApplicationInstitutionID=$higherEducationApplicationInstitutionID");
                    $form->addHiddenValue('address', $session->get('address'));

                    $form->addRow()->addHeading(__('Application Information'));
            
                    $data0 = array();
                    $sql0 = "SELECT higherEducationInstitutionID as value, concat(name, ' (', country, ')') as name FROM higherEducationInstitution WHERE active='Y' ORDER BY name";
                    $row = $form->addRow();
                        $row->addLabel('higherEducationInstitutionID', __('Institution'));
                        $row->addSelect('higherEducationInstitutionID')->fromQuery($pdo, $sql0, $data0)->placeholder()->required();

                    $data1 = array();
                    $sql1 = "SELECT higherEducationMajorID as value, name as name FROM higherEducationMajor WHERE active='Y' ORDER BY name";
            
                    $row = $form->addRow();
                        $row->addLabel('higherEducationMajorID', __('Major/Course'));
                        $row->addSelect('higherEducationMajorID')->fromQuery($pdo, $sql1, $data1)->placeholder()->required();

                    $row = $form->addRow();
                        $row->addLabel('applicationNumber', __('Application Number'))->description(__('Official number for your application (given by institution, UCAS, etc).'));
                        $row->addTextField('applicationNumber')->maxLength(50);

                    $row = $form->addRow();
                        $row->addLabel('rank', __('Rank'))->description(__('Order all your applications. 1 should be your most favoured application.'));
                        $row->addSelect('rank')->fromArray(range(1, 10))->placeholder();
                
                    $row = $form->addRow();
                        $row->addLabel('rating', __('Rating'))->description(__('How likely is it that you will get into this institution?'));
                        $row->addSelect('rating')->fromArray(array('High Reach' =>__('High Reach'), 'Reach' => __('Reach'), 'Mid' => __('Mid'), 'Safe' => __('Safe')))->placeholder();
            
                    $row = $form->addRow();
                        $column = $row->addColumn();
                        $column->addLabel('question', __('Application Question'))->description(__('If the application form has a question, enter it here.'));
                        $column->addTextArea('question')->setRows(4)->setClass('fullWidth');
            
                     $row = $form->addRow();
                        $column = $row->addColumn();
                        $column->addLabel('answer', __('Application Answer'))->description(__('Answer the above question here.'));
                        $column->addTextArea('answer')->setRows(14)->setClass('fullWidth');
                
                    $row = $form->addRow();
                        $column = $row->addColumn();
                        $column->addLabel('scholarship', __('Scholarship Details'))->description(__('Have you applied for a scholarship? If so, list the details below.'));
                        $column->addTextArea('scholarship')->setRows(4)->setClass('fullWidth');
                
                    $form->addRow()->addHeading(__('Status & Offers'));   
             
                    $row = $form->addRow();
                        $row->addLabel('status', __('Status'))->description(__('Where are you in the application process'));
                        $row->addSelect('status')->fromArray(array('Not Yet Started' =>__('Not Yet Started'), 'Researching' => __('Researching'), 'Started' => __('Started'), 'Passed To Careers Office' => __('Passed To Careers Office'), 'Completed' => __('Completed'), 'Application Sent' => __('Application Sent'), 'Offer/Acceptance Received' => __('Offer/Acceptance Received'), 'Rejection Received' => __('Rejection Received')))->placeholder();
            
                    $row = $form->addRow();
                        $row->addLabel('offer', __('Offer'))->description(__('If you have received an offer or rejection, select relevant option below:'));
                        $row->addSelect('offer')->fromArray(array('First Choice' =>__('Yes - First Choice'), 'Backup' => __('Yes - Backup Choice'), 'Y' => __('Yes - Other'), 'N' => __('No')))->placeholder();
        
                    $row = $form->addRow();
                        $column = $row->addColumn();
                        $column->addLabel('offerDetails', __('Offer Details'))->description(__('If you have received an offer, enter details here.'));
                        $column->addTextArea('offerDetails')->setRows(4)->setClass('fullWidth');
            
                    $row = $form->addRow();
                        $row->addFooter();
                        $row->addSubmit();
                        
                    $form->loadAllValuesFrom($values);

                    echo $form->getOutput();

                }
            }
        }
    }
}
?>
