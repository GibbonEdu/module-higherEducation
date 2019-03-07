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

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/references_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo 'You do not have access to this action.';
    echo '</div>';
} else {
    $role = staffHigherEducationRole($_SESSION[$guid]['gibbonPersonID'], $connection2);
    if ($role != 'Coordinator') { echo "<div class='error'>";
        echo 'You do not have access to this action.';
        echo '</div>';
    } else {
        $higherEducationReferenceID = $_GET['higherEducationReferenceID'];

        //Proceed!
        echo "<h2 class='top'>";
        echo 'Higher Education Reference';
        echo '</h2>';

        if ($higherEducationReferenceID != '') {
            try {
                $data = array('higherEducationReferenceID' => $higherEducationReferenceID);
                $sql = "SELECT preferredName, surname, higherEducationReference.* FROM higherEducationReference JOIN gibbonPerson ON (higherEducationReference.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE higherEducationReferenceID=:higherEducationReferenceID AND gibbonPerson.status='Full'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='error'>".$e->getMessage().'</div>';
            }

            if ($result->rowCount() != 1) {
                echo "<div class='error'>";
                echo 'The selected reference does not exist.';
                echo '</div>';
            } else {
                //Let's go!
                $row = $result->fetch();

                echo "<div class='linkTop'>";
                echo "<a href='javascript:window.print()'><img title='Print' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/print.png'/></a>";
                echo '</div>'; ?>
                <table class='smallIntBorder' cellspacing='0' style="width: 100%">    
                    <tr>
                        <td> 
                            <b>Student</b><br/>
                        </td>
                        <td class="right">
                            <input readonly name="student" id="student" maxlength=255 value="<?php echo formatName('', htmlPrep($row['preferredName']), htmlPrep($row['surname']), 'Student', false, false) ?>" type="text" style="width: 300px">
                        </td>
                    </tr>
                    <tr>
                        <td> 
                            <b>Reference Type</b><br/>
                        </td>
                        <td class="right">
                            <input readonly name="type" id="type" maxlength=255 value="<?php echo $row['type'] ?>" type="text" style="width: 300px">
                        </td>
                    </tr>
                    <?php
                    try {
                        $dataContributions = array('higherEducationReferenceID' => $row['higherEducationReferenceID']);
                        $sqlContributions = 'SELECT higherEducationReferenceComponent.*, preferredName, surname FROM higherEducationReferenceComponent JOIN gibbonPerson ON (higherEducationReferenceComponent.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE higherEducationReferenceID=:higherEducationReferenceID ORDER BY title';
                        $resultContributions = $connection2->prepare($sqlContributions);
                        $resultContributions->execute($dataContributions);
                    } catch (PDOException $e) {
                    }
                    if ($resultContributions->rowCount() < 1) {
                        echo '<tr>';
                        echo '<td colspan=2>';
                        echo '<i>Error: no referees requested, or a system error.</i>';
                        echo '</td>';
                        echo '</tr>';
                    } else {
                        while ($rowContributions = $resultContributions->fetch()) {
                            echo '<tr>';
                            echo '<td colspan=2>';
                            echo '<h4>';
                            if ($rowContributions['title'] == '') {
                                echo $rowContributions['type'].' Comment';
                                echo "<span style='font-size: 75%; font-style: italic'>";
                                echo ' . by '.formatName('', $rowContributions['preferredName'], $rowContributions['surname'], 'Staff', false, true);
                                echo '</span>';
                            } else {
                                echo $rowContributions['title'];
                                echo "<span style='font-size: 75%; font-style: italic'>";
                                echo ' . '.$rowContributions['type'].' comment by '.formatName('', $rowContributions['preferredName'], $rowContributions['surname'], 'Staff', false, true);
                                echo '</span>';
                            }
                            echo '</h4>';
                            echo '<p>';
                            echo $rowContributions['body'];
                            echo '</p>';
                            echo '</td>';
                            echo '</tr>';
                        }
                    }
                echo '</table>';
            }
        }
    }
}
?>