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

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/references_write_edit.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Write References'), 'references_write.php', [
        'gibbonSchoolYearID' => $_GET['gibbonSchoolYearID'] ?? '',
    ]);
    $page->breadcrumbs->add(__('Edit Reference'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $gibbonSchoolYearID = $_GET['gibbonSchoolYearID'];
    $higherEducationReferenceComponentID = $_GET['higherEducationReferenceComponentID'];
    if ($higherEducationReferenceComponentID == '' or $gibbonSchoolYearID == '') {
        $page->addError(__('You have not specified a reference.'));
    } else {
        try {
            $data = array('higherEducationReferenceComponentID' => $higherEducationReferenceComponentID);
            $sql = 'SELECT higherEducationReference.gibbonPersonID AS gibbonPersonIDStudent, preferredName, surname, higherEducationReference.type as refType, higherEducationReference.notes, higherEducationReferenceComponent.* FROM higherEducationReferenceComponent JOIN higherEducationReference ON (higherEducationReferenceComponent.higherEducationReferenceID=higherEducationReference.higherEducationReferenceID) JOIN gibbonPerson ON (higherEducationReference.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE higherEducationReferenceComponentID=:higherEducationReferenceComponentID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $page->addError($e->getMessage());
        }

        if ($result->rowCount() != 1) {
            $page->addError(__('The selected reference does not exist.'));
        } else {
            //Let's go!
            $row = $result->fetch();
            ?>
            <form method="post" action="<?php echo $session->get('absoluteURL').'/modules/'.$session->get('module')."/references_write_editProcess.php?higherEducationReferenceComponentID=$higherEducationReferenceComponentID&gibbonSchoolYearID=$gibbonSchoolYearID" ?>">
                <table class='smallIntBorder' cellspacing='0' style="width: 100%">
                    <tr class='break'>
                        <td colspan=2>
                            <h3 class='top'>Reference Information</h3>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b>Student *</b><br/>
                            <span style="font-size: 90%"><i>This value cannot be changed.</i></span>
                        </td>
                        <td class="right">
                            <input readonly name="student" id="student" maxlength=255 value="<?php echo formatName('', htmlPrep($row['preferredName']), htmlPrep($row['surname']), 'Student', false, false) ?>" type="text" style="width: 300px">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b>Type *</b><br/>
                            <span style="font-size: 90%"><i>This value cannot be changed.</i></span>
                        </td>
                        <td class="right">
                            <input readonly name="refType" id="refType" maxlength=255 value="<?php echo $row['refType'] ?>" type="text" style="width: 300px">
                        </td>
                    </tr>
                    <tr>
                        <td colspan=2 style='padding-top: 15px;'>
                            <b>Reference Notes</b><br/>
                            <span style="font-size: 90%"><i>Information about this reference shared by the student. This value cannot be changed.</i></span><br/>
                            <textarea readonly name="notes" id="notes" rows=4 style="width:738px; margin: 5px 0px 0px 0px"><?php echo $row['notes'] ?></textarea>
                        </td>
                    </tr>

                    <tr class='break'>
                        <td colspan=2>
                            <h3>Useful Information</h3>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b>Academic</b><br/>
                        </td>
                        <td class="right">
                            <a target='_blank' href='<?php echo $session->get('absoluteURL') ?>/index.php?q=/modules/Students/student_view_details.php&gibbonPersonID=<?php echo $row['gibbonPersonIDStudent'] ?>&subpage=Markbook'>Markbook</a> | <a target='_blank' href='<?php echo $session->get('absoluteURL') ?>/index.php?q=/modules/Students/student_view_details.php&gibbonPersonID=<?php echo $row['gibbonPersonIDStudent'] ?>&subpage=External Assessment'>External Assessment</a>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b>Co-curricular</b><br/>
                        </td>
                        <td class="right">
                            <a target='_blank' href='<?php echo $session->get('absoluteURL') ?>/index.php?q=/modules/Students/student_view_details.php&gibbonPersonID=<?php echo $row['gibbonPersonIDStudent'] ?>&subpage=Activities'>Activities</a>
                            <?php
                                $gibbonModuleID = checkModuleReady('/modules/IB Diploma/index.php', $connection2);
                                if ($gibbonModuleID != false) {
                                    try {
                                        $dataAction = array('gibbonModuleID' => $gibbonModuleID, 'actionName' => 'View CAS in Student Profile', 'gibbonRoleID' => $session->get('gibbonRoleIDCurrent'));
                                        $sqlAction = 'SELECT gibbonAction.name FROM gibbonAction JOIN gibbonPermission ON (gibbonAction.gibbonActionID=gibbonPermission.gibbonActionID) JOIN gibbonRole ON (gibbonPermission.gibbonRoleID=gibbonRole.gibbonRoleID) WHERE (gibbonAction.name=:actionName) AND (gibbonPermission.gibbonRoleID=:gibbonRoleID) AND gibbonAction.gibbonModuleID=:gibbonModuleID';
                                        $resultAction = $connection2->prepare($sqlAction);
                                        $resultAction->execute($dataAction);
                                    } catch (PDOException $e) {
                                    }
                                    if ($resultAction->rowCount() > 0) {
                                        try {
                                            $dataHooks = array();
                                            $sqlHooks = "SELECT * FROM gibbonHook WHERE type='Student Profile' AND name='IB Diploma CAS'";
                                            $resultHooks = $connection2->prepare($sqlHooks);
                                            $resultHooks->execute($dataHooks);
                                        } catch (PDOException $e) {
                                        }
                                        if ($resultHooks->rowCount() == 1) {
                                            $rowHooks = $resultHooks->fetch();
                                            $options = unserialize($rowHooks['options']);
                                            //Check for permission to hook
                                            try {
                                                $dataHook = array('gibbonRoleIDCurrent' => $session->get('gibbonRoleIDCurrent'), 'sourceModuleName' => $options['sourceModuleName']);
                                                $sqlHook = "SELECT gibbonHook.name, gibbonModule.name AS module, gibbonAction.name AS action FROM gibbonHook JOIN gibbonModule ON (gibbonModule.name='".$options['sourceModuleName']."') JOIN gibbonAction ON (gibbonAction.name='".$options['sourceModuleAction']."') JOIN gibbonPermission ON (gibbonPermission.gibbonActionID=gibbonAction.gibbonActionID) WHERE gibbonAction.gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE gibbonPermission.gibbonRoleID=:gibbonRoleIDCurrent AND name=:sourceModuleName) AND gibbonHook.type='Student Profile' ORDER BY name";
                                                $resultHook = $connection2->prepare($sqlHook);
                                                $resultHook->execute($dataHook);
                                            } catch (PDOException $e) {
                                            }
                                            if ($resultHook->rowCount() == 1) {
                                                echo " | <a target='_blank' href='".$session->get('absoluteURL').'/index.php?q=/modules/Students/student_view_details.php&gibbonPersonID='.$row['gibbonPersonIDStudent'].'&hook='.$rowHooks['name'].'&module='.$options['sourceModuleName'].'&action='.$options['sourceModuleAction'].'&gibbonHookID='.$rowHooks['gibbonHookID']."'>".$rowHooks['name'].'</a>';
                                            }
                                        }
                                    }
                                }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b>Miscellaneous</b><br/>
                        </td>
                        <td class="right">
                            <a target='_blank' href='<?php echo $session->get('absoluteURL') ?>/index.php?q=/modules/Students/student_view_details.php&gibbonPersonID=<?php echo $row['gibbonPersonIDStudent'] ?>&subpage=Behaviour'>Behaviour</a> | <a target='_blank' href='<?php echo $session->get('absoluteURL') ?>/index.php?q=/modules/Students/student_view_details.php&gibbonPersonID=<?php echo $row['gibbonPersonIDStudent'] ?>&subpage=School Attendance'>Attendance</a>
                        </td>
                    </tr>
                    <?php
                    try {
                        $dataNotes = array('gibbonPersonID' => $row['gibbonPersonIDStudent']);
                        $sqlNotes = 'SELECT * FROM higherEducationStudent WHERE gibbonPersonID=:gibbonPersonID';
                        $resultNotes = $connection2->prepare($sqlNotes);
                        $resultNotes->execute($dataNotes);
                    } catch (PDOException $e) {
                        echo '<tr>';
                        echo '<td colspan=2>';
                        echo "<div class='warning'>";
                            echo $e->getMessage();
                        echo '</div>';
                        echo '</td>';
                        echo '</tr>';
                    }
                    if ($resultNotes->rowCount() == 1) {
                        $rowNotes = $resultNotes->fetch();
                        ?>
                        <tr>
                            <td colspan=2 style='padding-top: 15px;'>
                                <b>Higher Education Notes</b><br/>
                                <span style="font-size: 90%"><i>Information about higher education in general shared by the student. This value cannot be changed.</i></span><br/>
                                <div style="padding: 1px; background-color: #e2e2e2; border: 1px solid #BFBFBF; min-height: 74px; width:738px; margin: 5px 0px 0px 0px"><?php echo $rowNotes['referenceNotes'] ?></div>
                            </td>
                        </tr>
                        <?php

                        }
                    ?>

                    <tr class='break'>
                        <td colspan=2>
                            <h3>Your Contribution</h3>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b>Type *</b><br/>
                            <span style="font-size: 90%"><i>This value cannot be changed.</i></span>
                        </td>
                        <td class="right">
                            <input readonly name="type" id="type" maxlength=255 value="<?php echo $row['type'] ?>" type="text" style="width: 300px">
                        </td>
                    </tr>
                    <?php
                    if ($row['title'] != '') {
                        ?>
                        <tr>
                            <td>
                                <b>Title *</b><br/>
                                <span style="font-size: 90%"><i>This value cannot be changed.</i></span>
                            </td>
                            <td class="right">
                                <input readonly name="title" id="title" maxlength=255 value="<?php echo $row['title'] ?>" type="text" style="width: 300px">
                            </td>
                        </tr>
                        <?php

                    }
                    ?>
                    <tr>
                        <td colspan=2 style='padding-top: 15px;'>
                            <b>Reference *</b><br/>
                            <span style="font-size: 90%"><i>
                            <?php
                            if ($row['refType'] == 'US Reference') {
                                echo 'Maximum limit of 10,000 characters.';
                            } else {
                                echo 'Maximum limit of 2,000 characters.'; } ?>
                            </i></span><br/>
                            <textarea name="body" id="body" rows=20 style="width:738px; margin: 5px 0px 0px 0px"><?php echo $row['body'] ?></textarea>
                            <script type="text/javascript">
                                var body=new LiveValidation('body');
                                body.add(Validate.Presence);
                                <?php
                                if ($row['refType'] == 'US Reference') {
                                    echo 'body.add( Validate.Length, { maximum: 10000 } );';
                                } else {
                                    echo 'body.add( Validate.Length, { maximum: 2000 } );';
                                }
                                ?>
                             </script>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <b>Status *</b><br/>
                        </td>
                        <td class="right">
                            <select name="status" id="status" style="width: 302px">
                                <option <?php if ($row['status'] == 'In Progress') { echo 'selected'; } ?> value='In Progress'>In Progress</option> ;
                                <option <?php if ($row['status'] == 'Complete') { echo 'selected'; } ?> value='Complete'>Complete</option> ;
                            </select>
                        </td>
                    </tr>

                    <?php
                    try {
                        $dataContributions = array('higherEducationReferenceID' => $row['higherEducationReferenceID'], 'higherEducationReferenceComponentID' => $higherEducationReferenceComponentID);
                        $sqlContributions = 'SELECT higherEducationReferenceComponent.*, preferredName, surname FROM higherEducationReferenceComponent JOIN gibbonPerson ON (higherEducationReferenceComponent.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE higherEducationReferenceID=:higherEducationReferenceID AND NOT higherEducationReferenceComponentID=:higherEducationReferenceComponentID ORDER BY title';
                        $resultContributions = $connection2->prepare($sqlContributions);
                        $resultContributions->execute($dataContributions);
                    } catch (PDOException $e) {
                    }

                    if ($resultContributions->rowCount() > 0) {
                        ?>
                        <tr>
                            <td colspan=2>
                                <h3>Other Contributions</h3>
                                <?php
                                echo "<table cellspacing='0' style='width: 100%'>";
                                echo "<tr class='head'>";
                                echo '<th>';
                                echo 'Name<br/>';
                                echo '</th>';
                                echo '<th colspan=2>';
                                echo 'Status<br/>';
                                echo '</th>';
                                echo '<th>';
                                echo 'Type';
                                echo '</th>';
                                echo '<th>';
                                echo 'Title';
                                echo '</th>';
                                echo '<th>';
                                echo 'Actions';
                                echo '</th>';
                                echo '</tr>';

                                $count = 0;
                                $rowNum = 'odd';
                                while ($rowContributions = $resultContributions->fetch()) {
                                    if ($count % 2 == 0) {
                                        $rowNum = 'even';
                                    } else {
                                        $rowNum = 'odd';
                                    }
                                    ++$count;

                                    echo "<tr class='$rowNum'>";
                                    echo '<td>';
                                    echo formatName('', $rowContributions['preferredName'], $rowContributions['surname'], 'Staff', false, true);
                                    echo '</td>';
                                    echo "<td style='width: 25px'>";
                                    if ($rowContributions['status'] == 'Complete') {
                                        echo "<img style='margin-right: 3px; float: left' title='Complete' src='./themes/".$session->get('gibbonThemeName')."/img/iconTick.png'/> ";
                                    } else {
                                        echo "<img style='margin-right: 3px; float: left' title='In Progress' src='./themes/".$session->get('gibbonThemeName')."/img/iconTick_light.png'/> ";
                                    }
                                    echo '</td>';
                                    echo '<td>';
                                    echo '<b>'.$rowContributions['status'].'</b>';
                                    echo '</td>';
                                    echo '<td>';
                                    echo $rowContributions['type'];
                                    echo '</td>';
                                    echo '<td>';
                                    if ($rowContributions['title'] == '') {
                                        echo '<i>NA</i>';
                                    } else {
                                        echo $rowContributions['title'];
                                    }
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
                                    if ($rowContributions['status'] != 'Pending') {
                                        echo "<a class='show_hide-$count' onclick='false' href='#'><img style='padding-right: 5px' src='".$session->get('absoluteURL')."/themes/Default/img/page_down.png' alt='Show Details' onclick='return false;' /></a>";
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                    if ($rowContributions['status'] != 'Pending') {
                                        echo "<tr class='description-$count' id='fields-$count' style='background-color: #fff; display: none'>";
                                        echo "<td style='border-bottom: 1px solid #333' colspan=6>";
                                        echo $rowContributions['body'];
                                        echo '</td>';
                                        echo '</tr>';
                                    }
                                }
                                echo '</table>';
                                ?>
                            </td>
                        </tr>
                        <?php

                        }
                    ?>
                    <tr>
                        <td>
                            <span style="font-size: 90%"><i>* denotes a required field</i></span>
                        </td>
                        <td class="right">
                            <input type="hidden" name="address" value="<?php echo $session->get('address') ?>">
                            <input type="submit" value="Submit">
                        </td>
                    </tr>
                </table>
            </form>
            <?php

        }
    }
}
?>
