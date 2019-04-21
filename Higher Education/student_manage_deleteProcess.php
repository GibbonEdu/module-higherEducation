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

include __DIR__.'/../../gibbon.php';

$higherEducationStudentID = $_POST['higherEducationStudentID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/student_manage_delete.php&higherEducationStudentID=$higherEducationStudentID";
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/student_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/student_manage_delete.php') == false) {

    //Fail 0
    $URL = $URL.'&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    if ($higherEducationStudentID == '') {
        //Fail1
        $URL = $URL.'&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('higherEducationStudentID' => $higherEducationStudentID);
            $sql = 'SELECT * FROM higherEducationStudent WHERE higherEducationStudentID=:higherEducationStudentID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            //Fail2
            $URL = $URL.'&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() != 1) {
            //Fail 2
            $URL = $URL.'&return=error2';
            header("Location: {$URL}");
        } else {
            //Write to database
            try {
                $data = array('higherEducationStudentID' => $higherEducationStudentID);
                $sql = 'DELETE FROM higherEducationStudent WHERE higherEducationStudentID=:higherEducationStudentID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                //Fail2
                $URL = $URL.'&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Success 0
            $URLDelete = $URLDelete.'&return=success0';
            header("Location: {$URLDelete}");
        }
    }
}
