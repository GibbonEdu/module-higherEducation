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

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/references_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo 'You do not have access to this action.';
    echo '</div>';
} else {
    //Check if school year specified
    $gibbonSchoolYearID = $_GET['gibbonSchoolYearID'];
    $higherEducationReferenceComponentID = $_GET['higherEducationReferenceComponentID'];
    $higherEducationReferenceID = $_GET['higherEducationReferenceID'];
    if ($higherEducationReferenceComponentID == '' or $higherEducationReferenceID == '' or $gibbonSchoolYearID == '') { echo "<div class='error'>";
        echo 'You have not specified a grade scale or grade.';
        echo '</div>';
    } else {
        try {
            $data = array('higherEducationReferenceID' => $higherEducationReferenceID, 'higherEducationReferenceComponentID' => $higherEducationReferenceComponentID);
            $sql = 'SELECT higherEducationReferenceComponent.*, higherEducationReference.type AS refType FROM higherEducationReference JOIN higherEducationReferenceComponent ON (higherEducationReference.higherEducationReferenceID=higherEducationReferenceComponent.higherEducationReferenceID) WHERE higherEducationReferenceComponent.higherEducationReferenceID=:higherEducationReferenceID AND higherEducationReferenceComponent.higherEducationReferenceComponentID=:higherEducationReferenceComponentID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='error'>";
            echo 'The specified class cannot be found.';
            echo '</div>';
        } else {
            //Let's go!
            $row = $result->fetch();

            $page->breadcrumbs->add(__('Manage References'), 'references_manage.php', ['gibbonSchoolYearID' => $gibbonSchoolYearID]);
            $page->breadcrumbs->add(__('Edit Reference'), 'references_manage_edit.php', [
                'higherEducationReferenceID' => $higherEducationReferenceID,
                'gibbonSchoolYearID' => $gibbonSchoolYearID,
            ]);
            $page->breadcrumbs->add(__('Edit Contribution'));

            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, null);
            }
            ?>
            <form method="post" action="<?php echo $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/references_manage_edit_contribution_editProcess.php?higherEducationReferenceComponentID=$higherEducationReferenceComponentID&higherEducationReferenceID=$higherEducationReferenceID&gibbonSchoolYearID=$gibbonSchoolYearID" ?>">
                <table class='smallIntBorder' cellspacing='0' style="width: 100%">
                    <tr>
                        <td>
                            <b>Contribution Type *</b><br/>
                            <span style="font-size: 90%"><i>This value cannot be changed.</i></span>
                        </td>
                        <td class="right">
                            <input readonly name="type" id="type" maxlength=255 value="<?php echo $row['type'] ?>" type="text" style="width: 300px">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b>Title *</b><br/>
                        </td>
                        <td class="right">
                            <input name="title" id="title" maxlength=10 value="<?php echo $row['title'] ?>" type="text" style="width: 300px">
                            <script type="text/javascript">
                                var title=new LiveValidation('title');
                                title.add(Validate.Presence);
                             </script>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b><?php echo __('Author') ?></b><br/>
                            <span class="emphasis small"></span>
                        </td>
                        <td class="right">
                            <select class="standardWidth" name="gibbonPersonID" id="gibbonPersonID">
                                <?php
                                echo "<option $selected value='Please select...'>".__('Please select...').'</option>';
                                try {
                                    $dataSelect = array('gibbonPersonID' => $row['gibbonPersonID']);
                                    $sqlSelect = "SELECT gibbonPerson.gibbonPersonID, surname, preferredName FROM gibbonPerson JOIN gibbonStaff ON (gibbonPerson.gibbonPersonID=gibbonStaff.gibbonPersonID) WHERE (status='Full' or gibbonPerson.gibbonPersonID=:gibbonPersonID) ORDER BY surname, preferredName";
                                    $resultSelect = $connection2->prepare($sqlSelect);
                                    $resultSelect->execute($dataSelect);
                                } catch (PDOException $e) {}
                                while ($rowSelect = $resultSelect->fetch()) {
                                    if ($row['gibbonPersonID'] == $rowSelect['gibbonPersonID']) {
                                        echo "<option selected value='".$rowSelect['gibbonPersonID']."'>".formatName('', htmlPrep($rowSelect['preferredName']), htmlPrep($rowSelect['surname']), 'Staff', true, true).'</option>';
                                    } else {
                                        echo "<option value='".$rowSelect['gibbonPersonID']."'>".formatName('', htmlPrep($rowSelect['preferredName']), htmlPrep($rowSelect['surname']), 'Staff', true, true).'</option>';
                                    }
                                }
                                ?>
                            </select>
                            <script type="text/javascript">
                                var gibbonPersonID=new LiveValidation('gibbonPersonID');
                                gibbonPersonID.add(Validate.Exclusion, { within: ['Please select...'], failureMessage: "<?php echo __('Select something!') ?>"});
                            </script>
                        </td>
                    </tr>

                    <tr>
                        <td colspan=2 style='padding-top: 15px;'>
                            <b>Reference</b><br/>
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
                    <tr>
                        <td>
                            <span style="font-size: 90%"><i>* denotes a required field</i></span>
                        </td>
                        <td class="right">
                            <input name="higherEducationReferenceID" id="higherEducationReferenceID" value="<?php echo $higherEducationReferenceID ?>" type="hidden">
                            <input type="hidden" name="address" value="<?php echo $_SESSION[$guid]['address'] ?>">
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
