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

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/institutions_manage_add.php') == false) {

    //Acess denied
    echo "<div class='error'>";
    echo 'You do not have access to this action.';
    echo '</div>';
} else {
    echo "<div class='trail'>";
    echo "<div class='trailHead'><a href='".$_SESSION[$guid]['absoluteURL']."'>Home</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q']).'/'.getModuleEntry($_GET['q'], $connection2, $guid)."'>".getModuleName($_GET['q'])."</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q'])."/institutions_manage.php'>Manage Institutions</a> > </div><div class='trailEnd'>Add Institution</div>";
    echo '</div>';

    $role = staffHigherEducationRole($_SESSION[$guid]['gibbonPersonID'], $connection2);
    if ($role != 'Coordinator') {
        echo "<div class='error'>";
        echo 'You do not have access to this action.';
        echo '</div>';
    } else {
        $returns = array();
        if (isset($_GET['editID'])) {
            $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Higher Education/institutions_manage_edit.php&higherEducationInstitutionID='.$_GET['editID'];
        }
        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], $editLink, $returns);
        }

        ?>
		<form method="post" action="<?php echo $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/institutions_manage_addProcess.php' ?>">
			<table class='smallIntBorder' cellspacing='0' style="width: 100%">
				<tr>
					<td>
						<b>Name *</b><br/>
						<span style="font-size: 90%"><i></i></span>
					</td>
					<td class="right">
						<input name="name" id="uniname" maxlength=150 value="" type="text" style="width: 300px">
						<script type="text/javascript">
							var uniname=new LiveValidation('uniname');
							uniname.add(Validate.Presence);
						 </script>
					</td>
				</tr>
				<tr>
					<td>
						<b>Country *</b><br/>
						<span style="font-size: 90%"><i></i></span>
					</td>
					<td class="right">
						<select name="country" id="country" style="width: 302px">
							<?php
                            echo "<option value='Please select...'>Please select...</option>";
        try {
            $dataSelect = array();
            $sqlSelect = 'SELECT printable_name FROM gibbonCountry ORDER BY printable_name';
            $resultSelect = $connection2->prepare($sqlSelect);
            $resultSelect->execute($dataSelect);
        } catch (PDOException $e) {
        }
        while ($rowSelect = $resultSelect->fetch()) {
            echo "<option value='".$rowSelect['printable_name']."'>".htmlPrep($rowSelect['printable_name']).'</option>';
        }
        ?>
						</select>
						<script type="text/javascript">
							var country=new LiveValidation('country');
							country.add(Validate.Exclusion, { within: ['Please select...'], failureMessage: "Select something!"});
						 </script>
					</td>
				</tr>
				<tr>
					<td>
						<b>Active *</b><br/>
					</td>
					<td class="right">
						<select name="active" id="active" style="width: 302px">
							<option value="Y">Y</option>
							<option value="N">N</option>
						</select>
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
