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

//Module includes
include __DIR__.'/moduleFunctions.php';

use Gibbon\Forms\Form;
use Gibbon\Services\Format;

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

                echo "<table class='smallIntBorder' cellspacing='0' style='width: 100%'>";
                echo '<tr>';
                echo "<td style='width: 34%; vertical-align: top'>";
                echo "<span style='font-size: 115%; font-weight: bold'>Name</span><br/>";
                echo Format::name('', $values['preferredName'], $values['surname'], 'Student', true, true);
                echo '</td>';
                echo "<td style='width: 34%; vertical-align: top'>";
                echo "<span style='font-size: 115%; font-weight: bold'>Form Group</span><br/>";
                try {
                    $dataDetail = array('gibbonFormGroupID' => $values['gibbonFormGroupID']);
                    $sqlDetail = 'SELECT * FROM gibbonFormGroup WHERE gibbonFormGroupID=:gibbonFormGroupID';
                    $resultDetail = $connection2->prepare($sqlDetail);
                    $resultDetail->execute($dataDetail);
                } catch (PDOException $e) {
                    echo "<div class='warning'>";
                        echo $e->getMessage();
                    echo '</div>';
                }
                if ($resultDetail->rowCount() == 1) {
                    $valuesDetail = $resultDetail->fetch();
                    echo '<i>'.$valuesDetail['name'].'</i>';
                }
                echo '</td>';
                echo "<td style='width: 34%; vertical-align: top'>";

                echo '</td>';
                echo '</tr>';
                echo '</table>';

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

                        $style = '';
                        if ($values['applying'] == 'N' or $values['applying'] == '') {
                            $style = 'display: none;';
                        }
                        echo "<div id='applicationsDiv' style='$style'>";
                        echo '<h2>';
                        echo 'Application To Institutions';
                        echo '</h2>';

                        if ($values['higherEducationApplicationID'] == '') {
                            echo "<div class='warning'>";
                            echo 'You need to save the information above (press the Submit button) before you can start adding applications.';
                            echo '</div>';
                        } else {
                            try {
                                $dataApps = array('higherEducationApplicationID' => $values['higherEducationApplicationID']);
                                $sqlApps = 'SELECT higherEducationApplicationInstitution.higherEducationApplicationInstitutionID, higherEducationInstitution.name as institution, higherEducationMajor.name as major, higherEducationApplicationInstitution.* FROM higherEducationApplicationInstitution JOIN higherEducationInstitution ON (higherEducationApplicationInstitution.higherEducationInstitutionID=higherEducationInstitution.higherEducationInstitutionID) JOIN higherEducationMajor ON (higherEducationApplicationInstitution.higherEducationMajorID=higherEducationMajor.higherEducationMajorID) WHERE higherEducationApplicationID=:higherEducationApplicationID ORDER BY rank, institution, major';
                                $resultApps = $connection2->prepare($sqlApps);
                                $resultApps->execute($dataApps);
                            } catch (PDOException $e) {
                                echo "<div class='warning'>";
                                    echo $e->getMessage();
                                echo '</div>';
                            }

                            if ($resultApps->rowCount() < 1) {
                                echo "<div class='warning'>";
                                    echo __('There are no applications to display.');
                                echo '</div>';
                            } else {
                                echo "<table cellspacing='0' style='width: 100%'>";
                                echo "<tr class='head'>";
                                echo '<th>';
                                echo 'Institution';
                                echo '</th>';
                                echo '<th>';
                                echo 'Major';
                                echo '</th>';
                                echo '<th>';
                                echo 'Ranking<br/>';
                                echo "<span style='font-size: 75%; font-style: italic'>Rating</span>";
                                echo '</th>';
                                echo '<th>';
                                echo 'Status';
                                echo '</th>';
                                echo '<th>';
                                echo 'Actions';
                                echo '</th>';
                                echo '</tr>';

                                $count = 0;
                                $valuesNum = 'odd';
                                while ($valuesApps = $resultApps->fetch()) {
                                    if ($count % 2 == 0) {
                                        $valuesNum = 'even';
                                    } else {
                                        $valuesNum = 'odd';
                                    }

                                    //COLOR ROW BY STATUS!
                                    echo "<tr class=$valuesNum>";
                                    echo '<td>';
                                    echo $valuesApps['institution'];
                                    echo '</td>';
                                    echo '<td>';
                                    echo $valuesApps['major'];
                                    echo '</td>';
                                    echo '<td>';
                                    echo $valuesApps['rank'].'<br/>';
                                    echo "<span style='font-size: 75%; font-style: italic'>".$valuesApps['rating'].'</span>';
                                    echo '</td>';
                                    echo '<td>';
                                    echo $valuesApps['status'];
                                    echo '</td>';
                                    echo '<td>';
                                    echo "<script type='text/javascript'>";
                                    echo '$(document).ready(function(){';
                                    echo "\$(\".description-$count\").hide();";
                                    echo "\$(\".show_hide-$count\").fadeIn(1000);";
                                    echo "\$(\".show_hide-$count\").click(function(){";
                                    echo "\$(\".description-$count\").fadeToggle(1000);";
                                    echo '});';
                                    echo '});';
                                    echo '</script>';
                                    echo "<a class='show_hide-$count' onclick='false' href='#'><img style='padding-right: 5px' src='".$session->get('absoluteURL')."/themes/Default/img/page_down.png' alt='Show Details' onclick='return false;' /></a>";
                                    echo '</td>';
                                    echo '</tr>';
                                    echo "<tr class='description-$count' id='fields-$count' style='background-color: #fff; display: none'>";
                                    echo '<td colspan=5>';
                                    echo "<table class='mini' cellspacing='0' style='width: 100%'>";
                                    echo '<tr>';
                                    echo "<td style='vertical-align: top'>";
                                    echo '<b>Application Number</b>';
                                    echo '</td>';
                                    echo "<td style='vertical-align: top'>";
                                    if ($valuesApps['applicationNumber'] == '') {
                                        echo 'NA';
                                    } else {
                                        echo $valuesApps['applicationNumber'];
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                    echo '<tr>';
                                    echo "<td style='vertical-align: top'>";
                                    echo '<b>Scholarship Details</b>';
                                    echo '</td>';
                                    echo "<td style='vertical-align: top'>";
                                    if ($valuesApps['scholarship'] == '') {
                                        echo 'NA';
                                    } else {
                                        echo $valuesApps['scholarship'];
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                    echo '<tr>';
                                    echo "<td style='vertical-align: top'>";
                                    echo '<b>Offer</b>';
                                    echo '</td>';
                                    echo "<td style='vertical-align: top'>";
                                    if ($valuesApps['offer'] == '') {
                                        echo 'NA';
                                    } else {
                                        echo $valuesApps['offer'].'</br>';
                                        echo '<i>'.$valuesApps['offerDetails'].'</i></br>';
                                    }

                                    echo '</td>';
                                    echo '</tr>';
                                    echo '<tr>';
                                    echo "<td style='vertical-align: top'>";
                                    echo '<b>Application Question</b>';
                                    echo '</td>';
                                    echo "<td style='vertical-align: top'>";
                                    if ($valuesApps['question'] == '') {
                                        echo 'NA';
                                    } else {
                                        echo $valuesApps['question'];
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                    echo '<tr>';
                                    echo "<td style='vertical-align: top'>";
                                    echo '<b>Application Answer</b>';
                                    echo '</td>';
                                    echo "<td style='vertical-align: top'>";
                                    if ($valuesApps['answer'] == '') {
                                        echo 'NA';
                                    } else {
                                        echo $valuesApps['answer'];
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                    echo '</table>';
                                    echo '</td>';
                                    echo '</tr>';

                                    ++$count;
                                }
                                echo '</table>';
                            }
                        }
                        echo '</div>';
                    }
                }

                //Set sidebar
                $session->set('sidebarExtra', Format::userPhoto($image_240, 240));
            }
        }
    }
}
?>
