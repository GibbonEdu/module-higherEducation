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

include __DIR__.'/../../gibbon.php';

//Module includes
include __DIR__.'/moduleFunctions.php';


$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/references_request_add.php';

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/references_request_add.php') == false) {
    //Fail 0
    $URL = $URL.'&return=error0';
    header("Location: {$URL}");
} else {
    //Check for student enrolment
    if (studentEnrolment($_SESSION[$guid]['gibbonPersonID'], $connection2) == false) {
        //Fail 0
        $URL = $URL.'&return=error0';
        header("Location: {$URL}");
    } else {
        //Validate Inputs
        $gibbonPersonID = $_SESSION[$guid]['gibbonPersonID'];
        $gibbonSchoolYearID = $_SESSION[$guid]['gibbonSchoolYearID'];
        $type = $_POST['type'];
        $gibbonPersonIDReferee = null;
        if (isset($_POST['gibbonPersonIDReferee'])) {
            $gibbonPersonIDReferee = $_POST['gibbonPersonIDReferee'];
        }
        $status = 'Pending';
        $statusNotes = '';
        $notes = $_POST['notes'];
        $timestamp = date('Y-m-d H:i:s');

        if ($type == '' or ($type == 'US References' and $gibbonPersonIDReferee == '')) {
            //Fail 3
            $URL = $URL.'&return=error3';
            header("Location: {$URL}");
        } else {
            //Write to database
            try {
                $data = array('gibbonPersonID' => $gibbonPersonID, 'gibbonSchoolYearID' => $gibbonSchoolYearID, 'type' => $type, 'status' => $status, 'statusNotes' => $statusNotes, 'notes' => $notes, 'timestamp' => $timestamp);
                $sql = 'INSERT INTO higherEducationReference SET gibbonPersonID=:gibbonPersonID, gibbonSchoolYearID=:gibbonSchoolYearID, type=:type, status=:status, statusNotes=:statusNotes, notes=:notes, timestamp=:timestamp';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                //Fail 2
                $URL = $URL.'&return=error2';
                header("Location: {$URL}");
                exit();
            }

            $higherEducationReferenceID = $connection2->lastInsertID();

            //Set referees based on type of reference
            $partialFail = false;
            //Get new unit ID
            $higherEducationReferenceIDNew = $connection2->lastInsertID();
            if ($type == 'Composite Reference') {
                //Get subject teachers
                try {
                    $dataClass = array('gibbonSchoolYearID' => $gibbonSchoolYearID, 'gibbonPersonID' => $gibbonPersonID);
                    $sqlClass = "SELECT gibbonCourseClass.gibbonCourseClassID, gibbonCourseClass.nameShort AS class, gibbonCourse.nameShort AS course
                        FROM gibbonCourse
                            JOIN gibbonCourseClass ON (gibbonCourse.gibbonCourseID=gibbonCourseClass.gibbonCourseID)
                            JOIN gibbonCourseClassPerson ON (gibbonCourseClassPerson.gibbonCourseClassID=gibbonCourseClass.gibbonCourseClassID)
                        WHERE gibbonSchoolYearID=:gibbonSchoolYearID
                            AND gibbonPersonID=:gibbonPersonID
                            AND NOT role LIKE '%left'
                            AND gibbonCourseClass.reportable='Y'
                            AND gibbonCourseClassPerson.reportable='Y'
                        ORDER BY course, class";
                    $resultClass = $connection2->prepare($sqlClass);
                    $resultClass->execute($dataClass);
                } catch (PDOException $e) {
                    $partialFail = true;
                }
                while ($rowClass = $resultClass->fetch()) {
                    try {
                        $dataTeacher = array('gibbonCourseClassID' => $rowClass['gibbonCourseClassID']);
                        $sqlTeacher = "SELECT gibbonCourseClassPerson.gibbonPersonID
                            FROM gibbonCourseClassPerson
                                JOIN gibbonPerson ON (gibbonCourseClassPerson.gibbonPersonID=gibbonPerson.gibbonPersonID)
                            WHERE gibbonCourseClassID=:gibbonCourseClassID
                                AND role='Teacher'
                                AND gibbonPerson.status='Full'";
                        $resultTeacher = $connection2->prepare($sqlTeacher);
                        $resultTeacher->execute($dataTeacher);
                    } catch (PDOException $e) {
                        $partialFail = true;
                    }
                    while ($rowTeacher = $resultTeacher->fetch()) {
                        try {
                            $dataInsert = array('higherEducationReferenceID' => $higherEducationReferenceIDNew, 'gibbonPersonID' => $rowTeacher['gibbonPersonID'], 'title' => $rowClass['course'].'.'.$rowClass['class']);
                            $sqlInsert = "INSERT INTO higherEducationReferenceComponent SET higherEducationReferenceID=:higherEducationReferenceID, gibbonPersonID=:gibbonPersonID, status='Pending', type='Academic', title=:title";
                            $resultInsert = $connection2->prepare($sqlInsert);
                            $resultInsert->execute($dataInsert);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                    }
                }

                //Get tutors
                try {
                    $dataForm = array('gibbonSchoolYearID' => $gibbonSchoolYearID, 'gibbonPersonID' => $gibbonPersonID);
                    $sqlForm = 'SELECT gibbonFormGroup.* FROM gibbonFormGroup JOIN gibbonStudentEnrolment ON (gibbonStudentEnrolment.gibbonFormGroupID=gibbonFormGroup.gibbonFormGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPersonID=:gibbonPersonID';
                    $resultForm = $connection2->prepare($sqlForm);
                    $resultForm->execute($dataForm);
                } catch (PDOException $e) {
                    $partialFail = true;
                }
                if ($resultForm->rowCount() == 1) {
                    $rowForm = $resultForm->fetch();
                    if ($rowForm['gibbonPersonIDTutor'] != '') {
                        try {
                            $dataInsert = array('higherEducationReferenceID' => $higherEducationReferenceIDNew, 'gibbonPersonID' => $rowForm['gibbonPersonIDTutor'], 'title' => $rowForm['nameShort']);
                            $sqlInsert = "INSERT INTO higherEducationReferenceComponent SET higherEducationReferenceID=:higherEducationReferenceID, gibbonPersonID=:gibbonPersonID, status='Pending', type='Pastoral', title=:title";
                            $resultInsert = $connection2->prepare($sqlInsert);
                            $resultInsert->execute($dataInsert);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                    }
                    if ($rowForm['gibbonPersonIDTutor2'] != '') {
                        try {
                            $dataInsert = array('higherEducationReferenceID' => $higherEducationReferenceIDNew, 'gibbonPersonID' => $rowForm['gibbonPersonIDTutor2'], 'title' => $rowForm['nameShort']);
                            $sqlInsert = "INSERT INTO higherEducationReferenceComponent SET higherEducationReferenceID=:higherEducationReferenceID, gibbonPersonID=:gibbonPersonID, status='Pending', type='Pastoral', title=:title";
                            $resultInsert = $connection2->prepare($sqlInsert);
                            $resultInsert->execute($dataInsert);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                    }
                    if ($rowForm['gibbonPersonIDTutor3'] != '') {
                        try {
                            $dataInsert = array('higherEducationReferenceID' => $higherEducationReferenceIDNew, 'gibbonPersonID' => $rowForm['gibbonPersonIDTutor3'], 'title' => $rowForm['nameShort']);
                            $sqlInsert = "INSERT INTO higherEducationReferenceComponent SET higherEducationReferenceID=:higherEducationReferenceID, gibbonPersonID=:gibbonPersonID, status='Pending', type='Pastoral', title=:title";
                            $resultInsert = $connection2->prepare($sqlInsert);
                            $resultInsert->execute($dataInsert);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                    }
                }
            }
            if ($type == 'US Reference') {
                if ($gibbonPersonIDReferee != '') {
                    try {
                        $dataInsert = array('higherEducationReferenceID' => $higherEducationReferenceIDNew, 'gibbonPersonID' => $gibbonPersonIDReferee);
                        $sqlInsert = "INSERT INTO higherEducationReferenceComponent SET higherEducationReferenceID=:higherEducationReferenceID, gibbonPersonID=:gibbonPersonID, status='Pending', type='General', title=''";
                        $resultInsert = $connection2->prepare($sqlInsert);
                        $resultInsert->execute($dataInsert);
                    } catch (PDOException $e) {
                        $partialFail = true;
                    }
                }
            }

            //Attempt to notify coordinators
            try {
                $dataNotify = array();
                $sqlNotify = "SELECT gibbonPerson.gibbonPersonID FROM higherEducationStaff JOIN gibbonPerson ON (higherEducationStaff.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE status='Full' AND role='Coordinator'";
                $resultNotify = $connection2->prepare($sqlNotify);
                $resultNotify->execute($dataNotify);
            } catch (PDOException $e) {
            }
            while ($rowNotify = $resultNotify->fetch()) {
                $notificationText = sprintf(__('Someone has created a new Higher Education reference request.'));
                setNotification($connection2, $guid, $rowNotify['gibbonPersonID'], $notificationText, 'Higher Education', '/index.php?q=/modules/Higher Education/references_manage.php');
            }

            if ($partialFail == true) {
                //Fail 5
                $URL = $URL.'&return=error5';
                header("Location: {$URL}");
            } else {
                //Success 0
                $URL = $URL.'&return=success0';
                header("Location: {$URL}");
            }
        }
    }
}
