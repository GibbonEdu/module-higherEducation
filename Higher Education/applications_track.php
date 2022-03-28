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
use Gibbon\Services\Format;
use Gibbon\Tables\DataTable;
use Gibbon\Module\HigherEducation\Domain\ApplicationInstitutionGateway;

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/applications_track.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {

    //Proceed!
    $page->breadcrumbs->add(__m('Track Application'));

    if (studentEnrolment($session->get('gibbonPersonID'), $connection2) == false) {
        $page->addError(__m('You have not been enrolled for higher education applications.'));
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
            $page->addMessage(__m('It appears that you are new to application tracking via the Higher Education module. Please enter your details below, and press the Submit button once you are done. You can reenter details into this page at any time.'));
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

            $applicationInstitutionGateway = $container->get(ApplicationInstitutionGateway::class);

            // QUERY
            $criteria = $applicationInstitutionGateway->newQueryCriteria(true)
                ->sortBy(['rank', 'institution', 'major'])
                ->pageSize(50)
                ->fromPOST();

            $applications = $applicationInstitutionGateway->queryApplicationInstitutions($criteria, $higherEducationApplicationID);

            // TABLE
            $table = DataTable::createPaginated('applications', $criteria);
            $table->setTitle(__('View'));

            $table->addHeaderAction('add', __('Add'))
                ->setURL('/modules/Higher Education/applications_track_add.php')
                ->displayLabel();

            $table->addColumn('institution', __m('Institution'));

            $table->addColumn('major', __m('Major'));

            $table->addColumn('ranking', __m('Ranking'))
                ->format(function ($values) {
                    return $values["rank"]."<br/>".Format::small(__($values['rating']));
                });

            $actions = $table->addActionColumn()
                ->addParam('higherEducationApplicationInstitutionID')
                ->format(function ($resource, $actions) {
                    $actions->addAction('edit', __('Edit'))
                        ->setURL('/modules/Higher Education/applications_track_edit.php');
                    $actions->addAction('delete', __('Delete'))
                        ->setURL('/modules/Higher Education/applications_track_delete.php');
                });

            echo $table->render($applications);
        }
    }
}
?>
