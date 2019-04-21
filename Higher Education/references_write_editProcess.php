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

//Module includes
include __DIR__.'/moduleFunctions.php';

$gibbonSchoolYearID = $_GET['gibbonSchoolYearID'];
$higherEducationReferenceComponentID = $_GET['higherEducationReferenceComponentID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/references_write_edit.php&higherEducationReferenceComponentID=$higherEducationReferenceComponentID&gibbonSchoolYearID=$gibbonSchoolYearID";

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/references_write_edit.php') == false) {
    //Fail 0
    $URL = $URL.'&return=error0';
    header("Location: {$URL}");
} else {
    if ($higherEducationReferenceComponentID == '' or $gibbonSchoolYearID == '') {
        //Fail1
        $URL = $URL.'&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('higherEducationReferenceComponentID' => $higherEducationReferenceComponentID, 'gibbonPersonID' => $_SESSION[$guid]['gibbonPersonID']);
            $sql = 'SELECT * FROM higherEducationReferenceComponent WHERE higherEducationReferenceComponentID=:higherEducationReferenceComponentID AND gibbonPersonID=:gibbonPersonID';
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
            //Validate Inputs
            $status = $_POST['status'];
            $body = $_POST['body'];

            if ($status == '' or $body == '') {
                //Fail 3
                $URL = $URL.'&return=error3';
                header("Location: {$URL}");
            } else {
                //Write to database
                try {
                    $data = array('status' => $status, 'body' => $body, 'higherEducationReferenceComponentID' => $higherEducationReferenceComponentID);
                    $sql = 'UPDATE higherEducationReferenceComponent SET status=:status, body=:body WHERE higherEducationReferenceComponentID=:higherEducationReferenceComponentID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    //Fail 2
                    $URL = $URL.'&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                //Success 0
                $URL = $URL.'&return=success0';
                header("Location: {$URL}");
            }
        }
    }
}
