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

use Gibbon\Services\Format;

//Module includes
include __DIR__.'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/references_request.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {

    //Proceed!
    $page->breadcrumbs->add(__('Request References'));
    
    if (studentEnrolment($session->get('gibbonPersonID'), $connection2) == false) {
        $page->addError(__('You have not been enrolled for higher education applications.'));
    } else {
        echo '<p>';
        echo 'Use the form below to request references for particular purposes, and then track the writing and completion of the reference. Please remember that your reference is a complex document written by several people, and so make take some time to create.';
        echo '</p>';

        try {
            $data = array('gibbonPersonID' => $session->get('gibbonPersonID'));
            $sql = 'SELECT higherEducationReference.* FROM higherEducationReference WHERE higherEducationReference.gibbonPersonID=:gibbonPersonID ORDER BY timestamp';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='warning'>";
                echo $e->getMessage();
            echo '</div>';
        }

        echo "<div class='linkTop'>";
        echo "<a href='".$session->get('absoluteURL').'/index.php?q=/modules/'.$session->get('module')."/references_request_add.php'><img title='New' src='./themes/".$session->get('gibbonThemeName')."/img/page_new.png'/></a>";
        echo '</div>';

        if ($result->rowCount() < 1) {
            echo "<div class='warning'>";
                echo __('There are no reference requests to display.');
            echo '</div>';
        } else {
            echo "<table cellspacing='0' style='width: 100%'>";
            echo "<tr class='head'>";
            echo '<th>';
            echo 'Date<br/>';
            echo "<span style='font-size: 75%; font-style: italic'>Time</span>";
            echo '</th>';
            echo '<th>';
            echo 'Type';
            echo '</th>';
            echo '<th colspan=2>';
            echo 'Status<br/>';
            echo "<span style='font-size: 75%; font-style: italic'>Notes</span>";
            echo '</th>';
            echo '<th>';
            echo 'Referees';
            echo '</th>';
            echo '</tr>';

            $count = 0;
            $rowNum = 'odd';
            while ($row = $result->fetch()) {
                if ($count % 2 == 0) {
                    $rowNum = 'even';
                } else {
                    $rowNum = 'odd';
                }

                    //COLOR ROW BY STATUS!
                    echo "<tr class=$rowNum>";
                echo '<td>';
                echo '<b>'.Format::date(substr($row['timestamp'], 0, 10)).'</b><br/>';
                echo "<span style='font-size: 75%; font-style: italic'>".substr($row['timestamp'], 11, 5).'</span>';
                echo '</td>';
                echo '<td>';
                echo $row['type'];
                echo '</td>';
                echo "<td style='width: 25px'>";
                if ($row['status'] == 'Cancelled') {
                    echo "<img style='margin-right: 3px; float: left' title='Cancelled' src='./themes/".$session->get('gibbonThemeName')."/img/iconCross.png'/> ";
                } elseif ($row['status'] == 'Complete') {
                    echo "<img style='margin-right: 3px; float: left' title='Complete' src='./themes/".$session->get('gibbonThemeName')."/img/iconTick.png'/> ";
                } else {
                    echo "<img style='padding-bottom: 3px; margin-right: 3px; float: left' title='In Progress' src='./themes/".$session->get('gibbonThemeName')."/img/iconTick_light.png'/> ";
                }
                echo '</td>';
                echo '<td>';
                echo '<b>'.$row['status'].'</b>';
                if ($row['statusNotes'] != '') {
                    echo "<br/><span style='font-size: 75%; font-style: italic'>".$row['statusNotes'].'</span>';
                }
                echo '</td>';
                echo '<td>';
                try {
                    $dataReferee = array('higherEducationReferenceID' => $row['higherEducationReferenceID']);
                    $sqlReferee = 'SELECT DISTINCT gibbonPerson.title, surname, preferredName FROM higherEducationReferenceComponent JOIN gibbonPerson ON (higherEducationReferenceComponent.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE higherEducationReferenceID=:higherEducationReferenceID ORDER BY surname, preferredName';
                    $resultReferee = $connection2->prepare($sqlReferee);
                    $resultReferee->execute($dataReferee);
                } catch (PDOException $e) {
                    echo "<div class='warning'>";
                        echo $e->getMessage();
                    echo '</div>';
                }
                while ($rowReferee = $resultReferee->fetch()) {
                    echo Format::name(htmlPrep($rowReferee['title']), htmlPrep($rowReferee['preferredName']), htmlPrep($rowReferee['surname']), 'Staff', false).'<br/>';
                }
                echo '</td>';
                echo '</tr>';

                ++$count;
            }
            echo '</table>';
        }
    }
}
