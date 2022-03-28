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
use Gibbon\Services\Format;
use Gibbon\Tables\DataTable;
use Gibbon\Module\HigherEducation\Domain\ApplicationInstitutionGateway;

//Module includes
include __DIR__.'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/applications_view_details.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $role = staffHigherEducationRole($session->get('gibbonPersonID'), $connection2);
    if ($role == false) {
        //Acess denied
        $page->addError(__('You are not enroled in the Higher Education programme.'));
    } else {
        $gibbonPersonID = $_GET['gibbonPersonID'];
        if ($gibbonPersonID == '') {
            $page->addError(__('You have not specified a student.'));
        } else {
            try {
                if ($role == 'Coordinator') {
                    $data = array('gibbonSchoolYearID' => $session->get('gibbonSchoolYearID'), 'gibbonPersonID' => $gibbonPersonID);
                    $sql = "SELECT gibbonPerson.gibbonPersonID, higherEducationStudentID, surname, preferredName, image_240, gibbonYearGroup.nameShort AS yearGroup, gibbonFormGroup.nameShort AS formGroup, gibbonFormGroup.gibbonFormGroupID, gibbonPersonIDAdvisor, gibbonSchoolYear.name AS schoolYear FROM higherEducationStudent JOIN gibbonPerson ON (higherEducationStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (higherEducationStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonSchoolYear ON (gibbonSchoolYear.gibbonSchoolYearID=gibbonPerson.gibbonSchoolYearIDClassOf) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonFormGroup ON (gibbonStudentEnrolment.gibbonFormGroupID=gibbonFormGroup.gibbonFormGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND gibbonPerson.gibbonPersonID=:gibbonPersonID ORDER BY gibbonSchoolYear.sequenceNumber DESC, surname, preferredName";
                } else {
                    $data = array('gibbonSchoolYearID' => $session->get('gibbonSchoolYearID'), 'advisor' => $session->get('gibbonPersonID'), 'gibbonPersonID' => $gibbonPersonID);
                    $sql = "SELECT gibbonPerson.gibbonPersonID, higherEducationStudentID, surname, preferredName, image_240, , gibbonYearGroup.nameShort AS yearGroup, gibbonFormGroup.nameShort AS formGroup, gibbonFormGroup.gibbonFormGroupID, gibbonPersonIDAdvisor, gibbonSchoolYear.name AS schoolYear FROM higherEducationStudent JOIN gibbonPerson ON (higherEducationStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (higherEducationStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonSchoolYear ON (gibbonSchoolYear.gibbonSchoolYearID=gibbonPerson.gibbonSchoolYearIDClassOf) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonFormGroup ON (gibbonStudentEnrolment.gibbonFormGroupID=gibbonFormGroup.gibbonFormGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND gibbonPersonIDAdvisor=:advisor AND gibbonPerson.gibbonPersonID=:gibbonPersonID ORDER BY gibbonSchoolYear.sequenceNumber DESC, surname, preferredName";
                }
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $page->addError($e->getMessage());
            }

            if ($result->rowCount() != 1) {
                $page->addError(__('The specified student does not exist, or you do not have access to them.'));
            } else {
                $values = $result->fetch();
                $image_240 = $values['image_240'];

                $page->breadcrumbs->add(__('View Applications'), 'applications_view.php');
                $page->breadcrumbs->add(__('Application Details'));

                // Details table
                $table = DataTable::createDetails('reference');

                $table->addColumn('name', __('Student'))->format(Format::using('name', ['', 'preferredName', 'surname', 'Student', 'true']));
                $table->addColumn('formGroup', __m('Form Group'));

                echo $table->render([$values]);

                //Check for application record
                try {
                    $data = array('gibbonPersonID' => $gibbonPersonID);
                    $sql = 'SELECT * FROM  higherEducationApplication WHERE gibbonPersonID=:gibbonPersonID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    echo "<div class='warning'>";
                        echo $e->getMessage();
                    echo '</div>';
                }

                if ($result->rowCount() != 1) {
                    echo "<div class='warning'>";
                    echo 'The selected student has not initiated the higher education application process.';
                    echo '</div>';
                } else {
                    $values = $result->fetch();

                    if ($values['applying'] != 'Y') {
                        echo "<div class='warning'>";
                        echo 'The selected student is not applying for higher education.';
                        echo '</div>';
                    } else {

                        //Create application record
                        $form = Form::create('applicationStatus', $session->get('absoluteURL').'/modules/'.$session->get('module').'/applications_trackProcess.php?higherEducationApplicationID='.$values['higherEducationApplicationID']);

                        $row = $form->addRow();
                            $row->addLabel('applying', __('Applying?'))->description(__('<b>Student asked</b>: Are you intending on applying for entry to higher education?'));
                            $row->addYesNo('applying')->selected($values['applying'])->readOnly();

                        $form->toggleVisibilityByClass('visibility')->onSelect('applying')->when('Y');

                        $row = $form->addRow();
                            $column = $row->addColumn()->addClass('visibility');
                                $column->addLabel('careerInterests', __('Career Interests'))->description(__('<b>Student asked</b>: What areas of work are you interested in? What are your ambitions?'));
                                $column->addTextArea('careerInterests')->setRows(8)->setClass('w-full')->readOnly();

                        $row = $form->addRow();
                            $column = $row->addColumn()->addClass('visibility');
                                $column->addLabel('coursesMajors', __('Courses/Majors'))->description(__('<b>Student asked</b>: What areas of study are you interested in? How do these relate to your career interests?'));
                                $column->addTextArea('coursesMajors')->setRows(8)->setClass('w-full')->readOnly();

                        $row = $form->addRow();
                            $column = $row->addColumn()->addClass('visibility');
                                $column->addLabel('otherScores', __('Scores'))->description(__('<b>Student asked</b>: Do you have any non-'.$session->get('organisationNameShort').' exam scores?'));
                                $column->addTextArea('otherScores')->setRows(8)->setClass('w-full')->readOnly();

                        $row = $form->addRow();
                            $column = $row->addColumn()->addClass('visibility');
                                $column->addLabel('personalStatement', __('Personal Statement'))->description(__('<b>Student asked</b>: Draft out ideas for your personal statement.'));
                                $column->addTextArea('personalStatement')->setRows(8)->setClass('w-full')->readOnly();

                        $row = $form->addRow();
                            $column = $row->addColumn()->addClass('visibility');
                                $column->addLabel('meetingNotes', __('Meeting Notes'))->description(__('<b>Student asked</b>: Take notes on any meetings you have regarding your application process'));
                                $column->addTextArea('meetingNotes')->setRows(8)->setClass('w-full')->readOnly();

                        $form->loadAllValuesFrom($values);

                        echo $form->getOutput();

                        if ($values['applying'] == 'Y') {
                            $applicationInstitutionGateway = $container->get(ApplicationInstitutionGateway::class);

                            // QUERY
                            $criteria = $applicationInstitutionGateway->newQueryCriteria(true)
                                ->sortBy(['rank', 'institution', 'major'])
                                ->pageSize(50)
                                ->fromPOST();

                            $applications = $applicationInstitutionGateway->queryApplicationInstitutions($criteria, $values["higherEducationApplicationID"]);

                            // TABLE
                            $table = DataTable::createPaginated('applications', $criteria);
                            $table->setTitle(__('Application To Institutions'));

                            $table->addExpandableColumn('details')
                                ->format(function($values) {
                                    $output = '';
                                    if (!empty($values['applicationNumber'])) {
                                        $output .= Format::bold(__m('Application Number')).'<br/>';
                                        $output .= nl2brr($values['applicationNumber']).'<br/><br/>';
                                    }
                                    if (!empty($values['scholarship'])) {
                                        $output .= Format::bold(__m('Scholarship')).'<br/>';
                                        $output .= nl2brr($values['scholarship']).'<br/><br/>';
                                    }
                                    if (!empty($values['offer'])) {
                                        $output .= Format::bold(__m('Offer')).'<br/>';
                                        $output .= nl2brr($values['offer']);
                                        if (!empty($values['offerDetails'])) {
                                            $output .= ' - '.$values['offerDetails'];
                                        }
                                        $output .= '<br/><br/>';
                                    }
                                    if (!empty($values['question'])) {
                                        $output .= Format::bold(__m('Application Question')).'<br/>';
                                        $output .= nl2brr($values['question']).'<br/><br/>';
                                    }
                                    if (!empty($values['answer'])) {
                                        $output .= Format::bold(__m('Application Answer')).'<br/>';
                                        $output .= nl2brr($values['answer']).'<br/><br/>';
                                    }
                                    return $output;
                                });

                            $table->addColumn('institution', __m('Institution'));

                            $table->addColumn('major', __m('Major'));

                            $table->addColumn('ranking', __m('Ranking'))
                                ->format(function ($values) {
                                    return $values["rank"]."<br/>".Format::small(__($values['rating']));
                                });

                            $table->addColumn('status', __m('Status'));

                            echo $table->render($applications);
                        }
                    }
                }

                //Set sidebar
                $session->set('sidebarExtra', Format::userPhoto($image_240, 240));
            }
        }
    }
}
?>
