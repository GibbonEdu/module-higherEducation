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

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/references_request_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo 'You do not have access to this action.';
    echo '</div>';
} else {
    //Proceed!
    echo "<div class='trail'>";
    echo "<div class='trailHead'><a href='".$_SESSION[$guid]['absoluteURL']."'>Home</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q']).'/'.getModuleEntry($_GET['q'], $connection2, $guid)."'>".getModuleName($_GET['q'])."</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q'])."/references_request.php'>Request References</a> > </div><div class='trailEnd'>Request A Reference</div>";
    echo '</div>';

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check for student enrolment
    if (studentEnrolment($_SESSION[$guid]['gibbonPersonID'], $connection2) == false) { echo "<div class='error'>";
        echo 'You have not been enrolled for higher education applications.';
        echo '</div>';
    } else {
        ?>
		<form method="post" action="<?php echo $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/references_request_addProcess.php' ?>">
			<table class='smallIntBorder' cellspacing='0' style="width: 100%">
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
						<span style="font-size: 90%"><i>Any information you need to share with your referee(s), that is not already in your <a href='<?php echo $_SESSION[$guid]['absoluteURL'] ?>/index.php?q=/modules/Higher Education/references_myNotes.php'>general reference notes</a>.</i></span><br/>
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
?>
