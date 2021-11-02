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

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/applications_track.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {

    //Proceed!
    $page->breadcrumbs->add(__('Track Application'));

    if (studentEnrolment($session->get('gibbonPersonID'), $connection2) == false) {
        $page->addError(__('You have not been enrolled for higher education applications.'));
    } else {
        echo '<p>';
        echo __m('Use this page to provide relevant information about your higher education application intentions and progress. This information will be used to guide you through this process.');
        echo '</p>';

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
            echo "<div class='warning'>";
            echo __m('It appears that you are new to application tracking via the Higher Education module. Please enter your details below, and press the Submit button once you are done. You can reenter details into this page at any time.');
            echo '</div>';
        } else {
            $values = $result->fetch();
        }

        $higherEducationApplicationID = null;
        if (isset($values['higherEducationApplicationID'])) {
            $higherEducationApplicationID = $values['higherEducationApplicationID'];
        }

        $form = Form::create('applicationStatus', $session->get('absoluteURL').'/modules/'.$session->get('module').'/applications_trackProcess.php?higherEducationApplicationID='.$higherEducationApplicationID);

        $form->addHiddenValue('address', $session->get('address'));

        $row = $form->addRow();
            $row->addLabel('applying', __('Applying?'))->description(__('Are you intending on applying for entry to higher education?'));
            $row->addYesNo('applying');

        $form->toggleVisibilityByClass('visibility')->onSelect('applying')->when('Y');

        $row = $form->addRow();
            $column = $row->addColumn()->addClass('visibility');
                $column->addLabel('careerInterests', __('Career Interests'))->description(__('What areas of work are you interested in? What are your ambitions?'));
                $column->addTextArea('careerInterests')->setRows(8)->setClass('w-full');

        $row = $form->addRow();
            $column = $row->addColumn()->addClass('visibility');
                $column->addLabel('coursesMajors', __('Courses/Majors'))->description(__('What areas of study are you interested in? How do these relate to your career interests?'));
                $column->addTextArea('coursesMajors')->setRows(8)->setClass('w-full');

        $row = $form->addRow();
            $column = $row->addColumn()->addClass('visibility');
                $column->addLabel('otherScores', __('Scores'))->description(__('Do you have any non-'.$session->get('organisationNameShort').' exam scores?'));
                $column->addTextArea('otherScores')->setRows(8)->setClass('w-full');

        $row = $form->addRow();
            $column = $row->addColumn()->addClass('visibility');
                $column->addLabel('personalStatement', __('Personal Statement'))->description(__('Draft out ideas for your personal statement.'));
                $column->addTextArea('personalStatement')->setRows(8)->setClass('w-full');

        $row = $form->addRow();
            $column = $row->addColumn()->addClass('visibility');
                $column->addLabel('meetingNotes', __('Meeting Notes'))->description(__('Take notes on any meetings you have regarding your application process'));
                $column->addTextArea('meetingNotes')->setRows(8)->setClass('w-full');

        $form->loadAllValuesFrom($values);

        $row = $form->addRow();
            $row->addFooter();
            $row->addSubmit();

        echo $form->getOutput();

        $style = '';
        if (isset($values['applying'])) {
            if ($values['applying'] == 'N' or $values['applying'] == '') {
                $style = 'display: none;';
            }
        }
        echo "<div id='applicationsDiv' style='$style'>";
        echo '<h2>';
        echo 'Application To Institutions';
        echo '</h2>';

        if (isset($values['higherEducationApplicationID']) == false) {
            echo "<div class='warning'>";
            echo 'You need to save the information above (press the Submit button) before you can start adding applications.';
            echo '</div>';
        } else {
            try {
                $dataApps = array('higherEducationApplicationID' => $values['higherEducationApplicationID']);
                $sqlApps = 'SELECT higherEducationApplicationInstitution.higherEducationApplicationInstitutionID, higherEducationInstitution.name as institution, higherEducationMajor.name as major, rank, rating FROM higherEducationApplicationInstitution JOIN higherEducationInstitution ON (higherEducationApplicationInstitution.higherEducationInstitutionID=higherEducationInstitution.higherEducationInstitutionID) JOIN higherEducationMajor ON (higherEducationApplicationInstitution.higherEducationMajorID=higherEducationMajor.higherEducationMajorID) WHERE higherEducationApplicationID=:higherEducationApplicationID ORDER BY rank, institution, major';
                $resultApps = $connection2->prepare($sqlApps);
                $resultApps->execute($dataApps);
            } catch (PDOException $e) {
                echo "<div class='warning'>";
                    echo $e->getMessage();
                echo '</div>';
            }

            echo "<div class='linkTop'>";
            echo "<a href='".$session->get('absoluteURL').'/index.php?q=/modules/'.$session->get('module')."/applications_track_add.php'><img title='New' src='./themes/".$session->get('gibbonThemeName')."/img/page_new.png'/></a>";
            echo '</div>';

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
                echo 'Actions';
                echo '</th>';
                echo '</tr>';

                $count = 0;
                $rowNum = 'odd';
                while ($rowApps = $resultApps->fetch()) {
                    if ($count % 2 == 0) {
                        $rowNum = 'even';
                    } else {
                        $rowNum = 'odd';
                    }

                    //COLOR ROW BY STATUS!
                    echo "<tr class=$rowNum>";
                    echo '<td>';
                    echo $rowApps['institution'];
                    echo '</td>';
                    echo '<td>';
                    echo $rowApps['major'];
                    echo '</td>';
                    echo '<td>';
                    echo $rowApps['rank'].'<br/>';
                    echo "<span style='font-size: 75%; font-style: italic'>".$rowApps['rating'].'</span>';
                    echo '</td>';
                    echo '<td>';
                    echo "<a href='".$session->get('absoluteURL').'/index.php?q=/modules/'.$session->get('module').'/applications_track_edit.php&higherEducationApplicationInstitutionID='.$rowApps['higherEducationApplicationInstitutionID']."'><img title='Edit' src='./themes/".$session->get('gibbonThemeName')."/img/config.png'/></a> ";
                    echo "<a class='thickbox' href='".$session->get('absoluteURL').'/fullscreen.php?q=/modules/'.$session->get('module').'/applications_track_delete.php&higherEducationApplicationInstitutionID='.$rowApps['higherEducationApplicationInstitutionID']."&width=650&height=135'><img title='Delete' src='./themes/".$session->get('gibbonThemeName')."/img/garbage.png'/></a> ";
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
?>
