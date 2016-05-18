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

@session_start();

//Module includes
include './modules/'.$_SESSION[$guid]['module'].'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/references_manage_addMulti.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo 'You do not have access to this action.';
    echo '</div>';
} else {
    //Proceed!
    echo "<div class='trail'>";
    echo "<div class='trailHead'><a href='".$_SESSION[$guid]['absoluteURL']."'>Home</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q']).'/'.getModuleEntry($_GET['q'], $connection2, $guid)."'>".getModuleName($_GET['q'])."</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q'])."/references_manage.php&gibbonSchoolYearID=" . $_GET['gibbonSchoolYearID'] . "&search=" . $_GET['search'] . "'>Manage References</a> > </div><div class='trailEnd'>Add References</div>";
    echo '</div>';

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $role = staffHigherEducationRole($_SESSION[$guid]['gibbonPersonID'], $connection2);
    if ($role == false) {
        //Acess denied
        echo "<div class='error'>";
        echo 'You are not enroled in the Higher Education programme.';
        echo '</div>';
    } else {
        if ($role != 'Coordinator') {
            //Acess denied
            echo "<div class='error'>";
            echo 'You do not have permission to access this page.';
            echo '</div>';
        } else {
            ?>
    		<form method="post" action="<?php echo $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/references_manage_addMultiProcess.php?gibbonSchoolYearID='.$_GET['gibbonSchoolYearID'].'&search='.$_GET['search'] ?>">
    			<table class='smallIntBorder' cellspacing='0' style="width: 100%">
        			<tr>
        				<td style='width: 275px'>
        					<b><?php echo __($guid, 'Students') ?> *</b><br/>
        					<span class="emphasis small"><?php echo __($guid, 'Use Control, Command and/or Shift to select multiple.') ?> </span>
        				</td>
        				<td class="right">
        					<select multiple name="gibbonPersonIDMulti[]" id="gibbonPersonIDMulti[]" style="width: 302px; height:150px">
        						<optgroup label='--<?php echo __($guid, 'Students by Roll Group') ?>--'>
        							<?php
                                    try {
                                        $dataSelect = array('gibbonSchoolYearID' => $_SESSION[$guid]['gibbonSchoolYearID']);
                                        $sqlSelect = "SELECT gibbonPerson.gibbonPersonID, preferredName, surname, gibbonRollGroup.name AS name FROM gibbonPerson JOIN gibbonStudentEnrolment ON (gibbonPerson.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) JOIN gibbonRollGroup ON  (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) JOIN higherEducationStudent ON (higherEducationStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE status='FULL' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND gibbonRollGroup.gibbonSchoolYearID=:gibbonSchoolYearID ORDER BY name, surname, preferredName";
                                        $resultSelect = $connection2->prepare($sqlSelect);
                                        $resultSelect->execute($dataSelect);
                                    } catch (PDOException $e) {
                                    }
        							while ($rowSelect = $resultSelect->fetch()) {
        								echo "<option value='".$rowSelect['gibbonPersonID']."'>".htmlPrep($rowSelect['name']).' - '.formatName('', htmlPrep($rowSelect['preferredName']), htmlPrep($rowSelect['surname']), 'Student', true).'</option>';
        							}
        							?>
        						</optgroup>
        						<optgroup label='--<?php echo __($guid, 'Students by Name') ?>--'>
        							<?php
                                    try {
                                        $dataSelect = array('gibbonSchoolYearID' => $_SESSION[$guid]['gibbonSchoolYearID']);
                                        $sqlSelect = "SELECT gibbonPerson.gibbonPersonID, preferredName, surname, gibbonRollGroup.name AS name FROM gibbonPerson JOIN gibbonStudentEnrolment ON (gibbonPerson.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) JOIN higherEducationStudent ON (higherEducationStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE status='FULL' AND gibbonRollGroup.gibbonSchoolYearID=:gibbonSchoolYearID ORDER BY surname, preferredName";
                                        $resultSelect = $connection2->prepare($sqlSelect);
                                        $resultSelect->execute($dataSelect);
                                    } catch (PDOException $e) {
                                    }
        							while ($rowSelect = $resultSelect->fetch()) {
        								echo "<option value='".$rowSelect['gibbonPersonID']."'>".formatName('', htmlPrep($rowSelect['preferredName']), htmlPrep($rowSelect['surname']), 'Student', true).' ('.htmlPrep($rowSelect['name']).')</option>';
        							}
        							?>
        						</optgroup>
        					</select>
        				</td>
        			</tr>
    				<tr>
    					<td>
    						<b>Type *</b><br/>
    					</td>
    					<td class="right">
    						<select name="type" id="type" style="width: 302px">
    							<option value="Please select...">Please select...</option>
    							<option value="Composite Reference">Composite Reference</option>
    							<option value="US Reference">US Reference</option>
    						</select>
    						<script type="text/javascript">
    							var type=new LiveValidation('type');
    							type.add(Validate.Exclusion, { within: ['Please select...'], failureMessage: "Select something!"});
    						 </script>
    					</td>
    				</tr>
    				<script type="text/javascript">
    					$(document).ready(function(){
    						gibbonPersonIDReferee.disable();
    						$("#type").change(function(){
    							if ($('#type option:selected').val() == "Please select...") {
    								gibbonPersonIDReferee.disable();
    								$("#refereeRow").css("display","none");
    							}
    							else if ($('#type option:selected').val() == "Composite Reference") {
    								gibbonPersonIDReferee.disable();
    								$("#refereeRow").css("display","none");
    							}
    							else {
    								gibbonPersonIDReferee.enable();
    								$("#refereeRow").slideDown("fast", $("#refereeRow").css("display","table-row")); //Slide Down Effect
    							}
    						 });
    					});
    				</script>
    				<tr id="refereeRow" style='display: none'>
    					<td>
    						<b>Referee *</b><br/>
    						<span style="font-size: 90%"><i>The teacher you wish to write your reference.</i></span>
    					</td>
    					<td class="right">
    						<select name="gibbonPersonIDReferee" id="gibbonPersonIDReferee" style="width: 302px">
    							<?php
                                echo "<option value='Please select...'>Please select...</option>";
    							try {
    								$data = array('gibbonSchoolYearID' => $_SESSION[$guid]['gibbonSchoolYearID'], 'gibbonPersonID1' => $gibbonPersonID, 'gibbonPersonID2' => $gibbonPersonID);
    								$sql = "SELECT gibbonPerson.gibbonPersonID, surname, preferredName, title FROM gibbonPerson JOIN gibbonStaff ON (gibbonPerson.gibbonPersonID=gibbonStaff.gibbonPersonID) WHERE type='Teaching' AND gibbonPerson.status='Full' ORDER BY surname, preferredName";
    								$result = $connection2->prepare($sql);
    								$result->execute($data);
    							} catch (PDOException $e) {
    							}
    							while ($row = $result->fetch()) {
    								echo "<option value='".$row['gibbonPersonID']."'>".formatName($row['title'], $row['preferredName'], $row['surname'], 'Staff', true, true).'</option>';
    							}
    							?>
    						</select>
    						<script type="text/javascript">
    							var gibbonPersonIDReferee=new LiveValidation('gibbonPersonIDReferee');
    							gibbonPersonIDReferee.add(Validate.Exclusion, { within: ['Please select...'], failureMessage: "Select something!"});
    						 </script>

    					</td>
    				</tr>
    				<tr>
    					<td colspan=2 style='padding-top: 15px;'>
    						<b>Notes</b><br/>
    						<span style="font-size: 90%"><i>Any information you need to share with the referee(s).</i></span><br/>
    						<textarea name="notes" id="notes" rows=4 style="width:738px; margin: 5px 0px 0px 0px"></textarea>
    					</td>
    				</tr>

    				<tr>
    					<td>
    						<span style="font-size: 90%"><i>* denotes a required field</i></span>
    					</td>
    					<td class="right">
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
