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

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/majors_manage_add.php';

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/majors_manage_add.php') == false) {
    //Fail 0
    $URL = $URL.'&return=error0';
    header("Location: {$URL}");
} else {
    $role = staffHigherEducationRole($_SESSION[$guid]['gibbonPersonID'], $connection2);
    if ($role != 'Coordinator') {
        //Fail 0
        $URL = $URL.'&return=error0';
        header("Location: {$URL}");
    } else {
        //Validate Inputs
        $name = $_POST['name'];
        $active = $_POST['active'];

        if ($name == '' or $active == '') {
            //Fail 3
            $URL = $URL.'&return=error3';
            header("Location: {$URL}");
        } else {
            //Write to database
            try {
                $data = array('name' => $name, 'active' => $active);
                $sql = 'INSERT INTO higherEducationMajor SET name=:name, active=:active';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                //Fail 2
                $URL = $URL.'&return=error2';
                header("Location: {$URL}");
                exit();
            }

            $AI = str_pad($connection2->lastInsertID(), 6, '0', STR_PAD_LEFT);

            //Success 0
            $URL = $URL.'&return=success0&editID='.$AI;
            header("Location: {$URL}");
        }
    }
}
