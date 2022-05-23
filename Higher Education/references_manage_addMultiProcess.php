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


$gibbonSchoolYearID = $_GET['gibbonSchoolYearID'] ?? '';
$search = $_GET['search'] ?? '';
$URL = $session->get('absoluteURL').'/index.php?q=/modules/'.getModuleName($_POST['address'])."/references_manage_addMulti.php&gibbonSchoolYearID=$gibbonSchoolYearID&search=$search";

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/references_manage_addMulti.php') == false) {
    //Fail 0
    $URL = $URL.'&return=error0';
    header("Location: {$URL}");
} else {
    $role = staffHigherEducationRole($session->get('gibbonPersonID'), $connection2);
    if ($role != 'Coordinator') {
        //Fail 0
        $URL = $URL.'&return=error0';
        header("Location: {$URL}");
    } else {
        //Proceed!
        if ($gibbonSchoolYearID == '') {
            //Fail1
            $URL = $URL.'&return=error1';
            header("Location: {$URL}");
        } else {
            //Validate Inputs
            $gibbonPersonIDMulti = $_POST['gibbonPersonIDMulti'] ?? '';
            $type = $_POST['type'] ?? '';
            $gibbonPersonIDReferee = $_POST['gibbonPersonIDReferee'] ?? '';
            $status = 'Pending';
            $statusNotes = '';
            $notes = $_POST['notes'] ?? '';
            $timestamp = date('Y-m-d H:i:s');

            if ($gibbonPersonIDMulti == null or $type == '' or ($type == 'US References' and $gibbonPersonIDReferee == '')) {
                //Fail 3
                $URL = $URL.'&return=error3';
                header("Location: {$URL}");
            } else {
                $partialFail = false ;
                foreach ($gibbonPersonIDMulti AS $gibbonPersonID) {
                    $writeFail = false ;

                    //Write to database
                    try {
                        $data = array('gibbonPersonID' => $gibbonPersonID, 'gibbonSchoolYearID' => $gibbonSchoolYearID, 'type' => $type, 'status' => $status, 'statusNotes' => $statusNotes, 'notes' => $notes, 'timestamp' => $timestamp);
                        $sql = 'INSERT INTO higherEducationReference SET gibbonPersonID=:gibbonPersonID, gibbonSchoolYearID=:gibbonSchoolYearID, type=:type, status=:status, statusNotes=:statusNotes, notes=:notes, timestamp=:timestamp';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $partialFail = true;
                        $writeFail = true ;
                    }

                    if (!$writeFail) {
                        $AI = $connection2->lastInsertID();
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
                                        $dataInsert = array('higherEducationReferenceID' => $AI, 'gibbonPersonID' => $rowTeacher['gibbonPersonID'], 'title' => $rowClass['course'].'.'.$rowClass['class']);
                                        $sqlInsert = "INSERT INTO higherEducationReferenceComponent SET higherEducationReferenceID=:higherEducationReferenceID, gibbonPersonID=:gibbonPersonID, status='Pending', type='Academic', title=:title,body=''";
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
                                        $dataInsert = array('higherEducationReferenceID' => $AI, 'gibbonPersonID' => $rowForm['gibbonPersonIDTutor'], 'title' => $rowForm['nameShort']);
                                        $sqlInsert = "INSERT INTO higherEducationReferenceComponent SET higherEducationReferenceID=:higherEducationReferenceID, gibbonPersonID=:gibbonPersonID, status='Pending', type='Pastoral', title=:title,body=''";
                                        $resultInsert = $connection2->prepare($sqlInsert);
                                        $resultInsert->execute($dataInsert);
                                    } catch (PDOException $e) {
                                        $partialFail = true;
                                    }
                                }
                                if ($rowForm['gibbonPersonIDTutor2'] != '') {
                                    try {
                                        $dataInsert = array('higherEducationReferenceID' => $AI, 'gibbonPersonID' => $rowForm['gibbonPersonIDTutor2'], 'title' => $rowForm['nameShort']);
                                        $sqlInsert = "INSERT INTO higherEducationReferenceComponent SET higherEducationReferenceID=:higherEducationReferenceID, gibbonPersonID=:gibbonPersonID, status='Pending', type='Pastoral', title=:title,body=''";
                                        $resultInsert = $connection2->prepare($sqlInsert);
                                        $resultInsert->execute($dataInsert);
                                    } catch (PDOException $e) {
                                        $partialFail = true;
                                    }
                                }
                                if ($rowForm['gibbonPersonIDTutor3'] != '') {
                                    try {
                                        $dataInsert = array('higherEducationReferenceID' => $AI, 'gibbonPersonID' => $rowForm['gibbonPersonIDTutor3'], 'title' => $rowForm['nameShort']);
                                        $sqlInsert = "INSERT INTO higherEducationReferenceComponent SET higherEducationReferenceID=:higherEducationReferenceID, gibbonPersonID=:gibbonPersonID, status='Pending', type='Pastoral', title=:title,body=''";
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
                                    $dataInsert = array('higherEducationReferenceID' => $AI, 'gibbonPersonID' => $gibbonPersonIDReferee);
                                    $sqlInsert = "INSERT INTO higherEducationReferenceComponent SET higherEducationReferenceID=:higherEducationReferenceID, gibbonPersonID=:gibbonPersonID, status='Pending', type='General', title='',body=''";
                                    $resultInsert = $connection2->prepare($sqlInsert);
                                    $resultInsert->execute($dataInsert);
                                } catch (PDOException $e) {
                                    $partialFail = true;
                                }
                            }
                        }
                    }
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
}
