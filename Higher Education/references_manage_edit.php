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

@session_start();

//Module includes
include './modules/'.$_SESSION[$guid]['module'].'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/references_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo 'You do not have access to this action.';
    echo '</div>';
} else {
    //Proceed!
    echo "<div class='trail'>";
    echo "<div class='trailHead'><a href='".$_SESSION[$guid]['absoluteURL']."'>Home</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q']).'/'.getModuleEntry($_GET['q'], $connection2, $guid)."'>".getModuleName($_GET['q'])."</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q']).'/references_manage.php&gibbonSchoolYearID='.$_GET['gibbonSchoolYearID'].'&higherEducationReferenceID='.$_GET['higherEducationReferenceID']."'>Manage References</a> > </div><div class='trailEnd'>Edit Reference</div>";
    echo '</div>';

    $role = staffHigherEducationRole($_SESSION[$guid]['gibbonPersonID'], $connection2);
    if ($role != 'Coordinator') { echo "<div class='error'>";
        echo 'You do not have access to this action.';
        echo '</div>';
    } else {
        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }

        //Check if school year specified
        $gibbonSchoolYearID = $_GET['gibbonSchoolYearID'];
        $higherEducationReferenceID = $_GET['higherEducationReferenceID'];
        if ($higherEducationReferenceID == '' or $gibbonSchoolYearID == '') {
            echo "<div class='error'>";
            echo 'You have not specified a reference.';
            echo '</div>';
        } else {
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
                echo "<a target='_blank' href='".$_SESSION[$guid]['absoluteURL'].'/report.php?q=/modules/'.$_SESSION[$guid]['module']."/references_manage_edit_print.php&higherEducationReferenceID=$higherEducationReferenceID'><img title='Print' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/print.png'/></a>";
                echo '</div>'; ?>

				<form method="post" action="<?php echo $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/references_manage_editProcess.php?higherEducationReferenceID=$higherEducationReferenceID&gibbonSchoolYearID=$gibbonSchoolYearID" ?>">
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
								<input readonly name="type" id="type" maxlength=255 value="<?php echo $row['type'] ?>" type="text" style="width: 300px">
							</td>
						</tr>
						<tr>
							<td>
								<b>Status *</b><br/>
							</td>
							<td class="right">
								<select name="status" id="status" style="width: 302px">
									<?php
                                    if ($row['status'] == 'Pending') {
                                        ?>
										<option <?php if ($row['status'] == 'Pending') { echo 'selected'; } ?> value='Pending'>Pending</option> ;
										<option <?php if ($row['status'] == 'In Progress') { echo 'selected'; } ?> value='In Progress'>In Progress</option> ;
										<option <?php if ($row['status'] == 'Complete') { echo 'selected'; } ?> value='Complete'>Complete</option> ;
										<option <?php if ($row['status'] == 'Cancelled') { echo 'selected'; } ?> value='Cancelled'>Cancelled</option> ;
										<?php

                                    } elseif ($row['status'] == 'In Progress') {
                                        ?>
										<option <?php if ($row['status'] == 'In Progress') { echo 'selected'; } ?> value='In Progress'>In Progress</option> ;
										<option <?php if ($row['status'] == 'Complete') { echo 'selected'; } ?> value='Complete'>Complete</option> ;
										<option <?php if ($row['status'] == 'Cancelled') { echo 'selected'; } ?> value='Cancelled'>Cancelled</option> ;
										<?php

                                    } elseif ($row['status'] == 'Complete') {
                                        ?>
										<option <?php if ($row['status'] == 'Complete') { echo 'selected'; } ?> value='Complete'>Complete</option> ;
										<?php

                                    } elseif ($row['status'] == 'Cancelled') {
                                        ?>
										<option <?php if ($row['status'] == 'Cancelled') { echo 'selected'; } ?> value='Cancelled'>Cancelled</option> ;
										<?php
                                    }
                					?>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								<b>Status Notes</b><br/>
								<?php
                                if ($row['status'] == 'Pending' or $row['status'] == 'In Progress') {
                                    echo '<span style="font-size: 90%"><i>Brief comment on current status.</i></span>';
                                } elseif ($row['status'] == 'Complete' or $row['status'] == 'Cancelled') {
                                    echo '<span style="font-size: 90%"><i>Brief comment on current status. This value cannot be changed.</i></span>';
                                }
                				?>
							</td>
							<td class="right">
								<?php
                                $readonly = '';
								if ($row['status'] == 'Complete' or $row['status'] == 'Cancelled') {
									$readonly = 'readonly';
								}
								echo "<input $readonly name=\"statusNotes\" id=\"statusNotes\" maxlength=255 value=\"".$row['statusNotes'].'" type="text" style="width: 300px">';
								?>
							</td>
						</tr>

						<tr>
							<td colspan=2 style='padding-top: 15px;'>
								<b>Reference Notes</b><br/>
								<span style="font-size: 90%"><i>Information about this reference shared by the student. This value cannot be changed.</i></span><br/>
								<textarea readonly name="notes" id="notes" rows=4 style="width:738px; margin: 5px 0px 0px 0px"><?php echo $row['notes'] ?></textarea>
							</td>
						</tr>


						<script type="text/javascript">
							$(document).ready(function(){
								$("#status").change(function(){
									if ($('#status option:selected').val() == "In Progress") {
										$("#contributionsRow").slideDown("fast", $("#contributionsRow").css("display","table-row")); //Slide Down Effect
									}
									else {
										$("#contributionsRow").css("display","none");
									}
								 });
							});
						</script>
						<tr class='break'>
							<td colspan=2>
								<h3>Contributions</h3>
							</td>
						</tr>
						<tr>
							<td colspan=2>
								<?php
                                //Check alert status
                                echo '<input type="hidden" name="alertsSent" value="'.$row['alertsSent'].'">';
								$style = '';
								if ($row['status'] != 'In Progress') {
									$style = "style='display: none'";
								}
								echo "<div id='contributionsRow' $style>";
								if ($row['alertsSent'] == 'N') {
									echo "<div class='warning'>";
									echo 'The user(s) listed below will be notified that their input is required for this reference. This will take place the next time you press the Submit beutton below.';
									echo '</div>';
								} else {
									echo "<div class='success'>";
									echo 'The user(s) listed below have already been notified by email that their input is required for this reference, and will not be alerted again.';
									echo '</div>';
								}
								echo '</div>';

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

								try {
									$dataContributions = array('higherEducationReferenceID' => $row['higherEducationReferenceID']);
									$sqlContributions = 'SELECT higherEducationReferenceComponent.*, preferredName, surname FROM higherEducationReferenceComponent JOIN gibbonPerson ON (higherEducationReferenceComponent.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE higherEducationReferenceID=:higherEducationReferenceID ORDER BY title';
									$resultContributions = $connection2->prepare($sqlContributions);
									$resultContributions->execute($dataContributions);
								} catch (PDOException $e) {
								}

								if ($resultContributions->rowCount() < 1) {
									echo "<tr class='even'>";
									echo '<td colspan=2>';
									echo '<i>Error: no referee requested, or a system error.</i>';
									echo '</td>';
									echo '</tr>';
								} else {
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
											echo "<img style='margin-right: 3px; float: left' title='Complete' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/iconTick.png'/> ";
										} else {
											echo "<img style='margin-right: 3px; float: left' title='In Progress' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/iconTick_light.png'/> ";
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
										echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/references_manage_edit_contribution_edit.php&higherEducationReferenceComponentID='.$rowContributions['higherEducationReferenceComponentID']."&higherEducationReferenceID=$higherEducationReferenceID&gibbonSchoolYearID=$gibbonSchoolYearID'><img title='Edit' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/config.png'/></a> ";
										echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/references_manage_edit_contribution_delete.php&higherEducationReferenceComponentID='.$rowContributions['higherEducationReferenceComponentID']."&higherEducationReferenceID=$higherEducationReferenceID&gibbonSchoolYearID=$gibbonSchoolYearID'><img title='Delete' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/garbage.png'/></a> ";
										if ($rowContributions['status'] != 'Pending') {
											echo "<a class='show_hide-$count' onclick='false' href='#'><img style='padding-right: 5px' src='".$_SESSION[$guid]['absoluteURL']."/themes/Default/img/page_down.png' alt='Show Details' onclick='return false;' /></a>";
										}
										echo '</td>';
										echo '</tr>';
										if ($rowContributions['status'] != 'Pending') {
											echo "<tr class='description-$count' id='fields-$count' style='background-color: #fff; display: none'>";
											echo '<td colspan=6>';
											echo $rowContributions['body'];
											echo '</td>';
											echo '</tr>';
										}
									}
								}
								echo '</table>';
								?>
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
}
?>
