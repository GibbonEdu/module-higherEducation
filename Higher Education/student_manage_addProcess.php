<?php
/*
Gibbon: the flexible, open school platform
Founded by Ross Parker at ICHK Secondary. Built by Ross Parker, Sandra Kuipers and the Gibbon community (https://gibbonedu.org/about/)
Copyright © 2010, Gibbon Foundation
Gibbon™, Gibbon Education Ltd. (Hong Kong)

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

$URL = $session->get('absoluteURL').'/index.php?q=/modules/'.getModuleName($_POST['address']).'/student_manage_add.php';

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/student_manage_add.php') == false) {

    //Fail 0
    $URL = $URL.'&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $gibbonPersonIDAdvisor = $_POST['gibbonPersonIDAdvisor'] ?? null;
    $update = true;
    $choices = $_POST['Members'] ?? [];

    if (count($choices) < 1) {
        //Fail 2
        $URL = $URL.'&return=error1';
        header("Location: {$URL}");
    } else {
        foreach ($choices as $t) {
            //Check to see if student is already registered in this class
            try {
                $data = array();
                $sql = "SELECT * FROM higherEducationStudent WHERE gibbonPersonID=$t";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                //Fail 2
                $URL = $URL.'&return=error1';
                header("Location: {$URL}");
                $update = false;
            }

            //If student not in course, add them
            if ($result->rowCount() == 0) {
                try {
                    $data = array('gibbonPersonID' => $t, 'gibbonPersonIDAdvisor' => $gibbonPersonIDAdvisor);
                    $sql = 'INSERT INTO higherEducationStudent SET gibbonPersonID=:gibbonPersonID, gibbonPersonIDAdvisor=:gibbonPersonIDAdvisor, referenceNotes=\'\'';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $update = false;
                }
            }
        }
        //Write to database
        if ($update == false) {
            //Fail 2
            $URL = $URL.'&return=error2';
            header("Location: {$URL}");
        } else {
            //Success 0
            $URL = $URL.'&return=success0';
            header("Location: {$URL}");
        }
    }
}
