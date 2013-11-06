<?
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

session_start() ;

//Module includes
include "./modules/" . $_SESSION[$guid]["module"] . "/moduleFunctions.php" ;

if (isActionAccessible($guid, $connection2, "/modules/Higher Education/majors_manage_add.php")==FALSE) {

	//Acess denied
	print "<div class='error'>" ;
		print "You do not have access to this action." ;
	print "</div>" ;
}
else {
	print "<div class='trail'>" ;
	print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>Home</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . getModuleName($_GET["q"]) . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/majors_manage.php'>Manage Majors</a> > </div><div class='trailEnd'>Add Major</div>" ;
	print "</div>" ;
	
	$role=staffHigherEducationRole($_SESSION[$guid]["gibbonPersonID"], $connection2) ;
	if ($role!="Coordinator") {
		print "<div class='error'>" ;
			print "You do not have access to this action." ;
		print "</div>" ;
	}
	else {
		$addReturn = $_GET["addReturn"] ;
		$addReturnMessage ="" ;
		$class="error" ;
		if (!($addReturn=="")) {
			if ($addReturn=="fail0") {
				$addReturnMessage ="Add failed because you do not have access to this action." ;	
			}
			else if ($addReturn=="fail2") {
				$addReturnMessage ="Add failed because no students were selected." ;	
			}
			else if ($addReturn=="fail2") {
				$addReturnMessage ="Add failed due to a database error." ;	
			}
			else if ($addReturn=="fail3") {
				$addReturnMessage ="Add failed because your inputs were invalid." ;	
			}
			else if ($addReturn=="fail4") {
				$addReturnMessage ="Add failed because the selected person is already registered." ;	
			}
			else if ($addReturn=="fail5") {
				$addReturnMessage ="Add succeeded, but there were problems uploading one or more attachments." ;	
			}
			else if ($addReturn=="success0") {
				$addReturnMessage ="Add was successful. You can add another record if you wish." ;	
				$class="success" ;
			}
			print "<div class='$class'>" ;
				print $addReturnMessage;
			print "</div>" ;
		} 
		
		?>
		<form method="post" action="<? print $_SESSION[$guid]["absoluteURL"] . "/modules/" . $_SESSION[$guid]["module"] . "/majors_manage_addProcess.php" ?>">
			<table class='smallIntBorder' cellspacing='0' style="width: 100%">	
				<tr>
					<td> 
						<b>Name *</b><br/>
						<span style="font-size: 90%"><i></i></span>
					</td>
					<td class="right">
						<input name="name" id="uniname" maxlength=150 value="" type="text" style="width: 300px">
						<script type="text/javascript">
							var uniname = new LiveValidation('uniname');
							uniname.add(Validate.Presence);
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
						<input type="hidden" name="address" value="<? print $_SESSION[$guid]["address"] ?>">
						<input type="submit" value="Submit">
					</td>
				</tr>
			</table>
		</form>
		<?
	}
}
?>