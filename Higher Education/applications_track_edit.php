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

//Module includes
include __DIR__.'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/applications_track_edit.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Track Applications'), 'applications_track.php');
    $page->breadcrumbs->add(__('Edit Application'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check for student enrolment
    if (studentEnrolment($_SESSION[$guid]['gibbonPersonID'], $connection2) == false) {
        $page->addError(__('You have not been enrolled for higher education applications.'));
    } else {
        //Check for application record
        try {
            $data = array('gibbonPersonID' => $_SESSION[$guid]['gibbonPersonID']);
            $sql = 'SELECT * FROM  higherEducationApplication WHERE gibbonPersonID=:gibbonPersonID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $page->addError($e->getMessage());
        }

        if ($result->rowCount() != 1) {
            $page->addError(__('You have not saved your application process yet.'));
        } else {
            $row = $result->fetch();

            //Check if school year specified
            $higherEducationApplicationInstitutionID = $_GET['higherEducationApplicationInstitutionID'];
            if ($higherEducationApplicationInstitutionID == '') {
                $page->addError(__('You have not specified an application.'));
            } else {
                try {
                    $data = array('higherEducationApplicationInstitutionID' => $higherEducationApplicationInstitutionID);
                    $sql = 'SELECT * FROM higherEducationApplicationInstitution JOIN higherEducationInstitution ON (higherEducationApplicationInstitution.higherEducationInstitutionID=higherEducationInstitution.higherEducationInstitutionID) WHERE higherEducationApplicationInstitutionID=:higherEducationApplicationInstitutionID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $page->addError($e->getMessage());
                }

                if ($result->rowCount() != 1) {
                    $page->addError(__('The specified application cannot be found.'));
                } else {
                    //Let's go!
                    $row = $result->fetch(); ?>
                    <form method="post" action="<?php echo $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/applications_track_editProcess.php?higherEducationApplicationInstitutionID=$higherEducationApplicationInstitutionID" ?>">
                        <table class='smallIntBorder' cellspacing='0' style="width: 100%">
                            <tr class='break'>
                                <td colspan=2>
                                    <h3 class='top'>Application Information</h3>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <b>Institution *</b><br/>
                                </td>
                                <td class="right">
                                    <select name="higherEducationInstitutionID" id="higherEducationInstitutionID" style="width: 302px">
                                        <?php
                                        echo "<option value='Please select...'>Please select...</option>";
                                        try {
                                            $dataSelect = array();
                                            $sqlSelect = "SELECT * FROM higherEducationInstitution WHERE active='Y' ORDER BY name";
                                            $resultSelect = $connection2->prepare($sqlSelect);
                                            $resultSelect->execute($dataSelect);
                                        } catch (PDOException $e) {
                                        }
                                        while ($rowSelect = $resultSelect->fetch()) {
                                            $selected = '';
                                            if ($rowSelect['higherEducationInstitutionID'] == $row['higherEducationInstitutionID']) {
                                                $selected = 'selected';
                                            }
                                            echo "<option $selected value='".$rowSelect['higherEducationInstitutionID']."'>".htmlPrep($rowSelect['name']).' ('.htmlPrep($rowSelect['country']).')</option>';
                                        }
                                        ?>
                                    </select>
                                    <script type="text/javascript">
                                        var higherEducationInstitutionID=new LiveValidation('higherEducationInstitutionID');
                                        higherEducationInstitutionID.add(Validate.Exclusion, { within: ['Please select...'], failureMessage: "Select something!"});
                                     </script>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <b>Major/Course *</b><br/>
                                </td>
                                <td class="right">
                                    <select name="higherEducationMajorID" id="higherEducationMajorID" style="width: 302px">
                                        <?php
                                        echo "<option value='Please select...'>Please select...</option>";
                                        try {
                                            $dataSelect = array();
                                            $sqlSelect = "SELECT * FROM higherEducationMajor WHERE active='Y' ORDER BY name";
                                            $resultSelect = $connection2->prepare($sqlSelect);
                                            $resultSelect->execute($dataSelect);
                                        } catch (PDOException $e) {
                                        }
                                        while ($rowSelect = $resultSelect->fetch()) {
                                            $selected = '';
                                            if ($rowSelect['higherEducationMajorID'] == $row['higherEducationMajorID']) {
                                                $selected = 'selected';
                                            }
                                            echo "<option $selected value='".$rowSelect['higherEducationMajorID']."'>".htmlPrep($rowSelect['name']).'</option>';
                                        }
                                        ?>
                                    </select>
                                    <script type="text/javascript">
                                        var higherEducationMajorID=new LiveValidation('higherEducationMajorID');
                                        higherEducationMajorID.add(Validate.Exclusion, { within: ['Please select...'], failureMessage: "Select something!"});
                                     </script>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <b>Application Number</b><br/>
                                    <span style="font-size: 90%"><i>Official number for your application (given by institution, UCAS, etc).</i></span>
                                </td>
                                <td class="right">
                                    <input name="applicationNumber" id="applicationNumber" maxlength=50 value="<?php echo $row['applicationNumber'] ?>" type="text" style="width: 300px">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <b>Rank</b><br/>
                                    <span style="font-size: 90%"><i>Order all your applications. 1 should be your most favoured application.</i></span>
                                </td>
                                <td class="right">
                                    <select name="rank" id="rank" style="width: 302px">
                                        <?php
                                        echo "<option value=''></option>";
                                        for ($i = 1; $i < 11; ++$i) {
                                            $selected = '';
                                            if ($i == $row['rank']) {
                                                $selected = 'selected';
                                            }
                                            echo "<option $selected value='$i'>$i</option>";
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <b>Rating</b><br/>
                                    <span style="font-size: 90%"><i>How likely is it that you will get into this institution?</i></span>
                                </td>
                                <td class="right">
                                    <select name="rating" id="rating" style="width: 302px">
                                        <option <?php if ($row['rating'] == '') { echo 'selected'; } ?> value=""></option>
                                        <option <?php if ($row['rating'] == 'High Reach') { echo 'selected'; } ?> value="High Reach">High Reach</option>
                                        <option <?php if ($row['rating'] == 'Reach') { echo 'selected'; } ?> value="Reach">Reach</option>
                                        <option <?php if ($row['rating'] == 'Mid') { echo 'selected'; } ?> value="Mid">Mid</option>
                                        <option <?php if ($row['rating'] == 'Safe') { echo 'selected'; } ?> value="Safe">Safe</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td colspan=2 style='padding-top: 15px;'>
                                    <b>Application Question</b><br/>
                                    <span style="font-size: 90%"><i>If the application form has a question, enter it here.</i></span><br/>
                                    <textarea name="question" id="question" rows=4 style="width:738px; margin: 5px 0px 0px 0px"><?php echo htmlPrep($row['question']) ?></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td colspan=2 style='padding-top: 15px;'>
                                    <b>Application Answer</b><br/>
                                    <span style="font-size: 90%"><i>Answer the above question here.</i></span><br/>
                                    <textarea name="answer" id="answer" rows=14 style="width:738px; margin: 5px 0px 0px 0px"><?php echo htmlPrep($row['answer']) ?></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td colspan=2 style='padding-top: 15px;'>
                                    <b>Scholarship Details</b><br/>
                                    <span style="font-size: 90%"><i>Have you applied for a scholarship? If so, list the details below.</i></span><br/>
                                    <textarea name="scholarship" id="scholarship" rows=4 style="width:738px; margin: 5px 0px 0px 0px"><?php echo htmlPrep($row['scholarship']) ?></textarea>
                                </td>
                            </tr>

                            <tr class='break'>
                                <td colspan=2>
                                    <h3>Status & Offers</h3>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <b>Status</b><br/>
                                    <span style="font-size: 90%"><i>Where are you in the application process?</i></span>
                                </td>
                                <td class="right">
                                    <select name="status" id="status" style="width: 302px">
                                        <option <?php if ($row['status'] == '') { echo 'selected'; } ?> value=""></option>
                                        <option <?php if ($row['status'] == 'Not Yet Started') { echo 'selected'; } ?> value="Not Yet Started">Not Yet Started</option>
                                        <option <?php if ($row['status'] == 'Researching') { echo 'selected'; } ?> value="Researching">Researching</option>
                                        <option <?php if ($row['status'] == 'Started') { echo 'selected'; } ?> value="Started">Started</option>
                                        <option <?php if ($row['status'] == 'Passed To Careers Office') { echo 'selected'; } ?> value="Passed To Careers Office">Passed To Careers Office</option>
                                        <option <?php if ($row['status'] == 'Completed') { echo 'selected'; } ?> value="Completed">Completed</option>
                                        <option <?php if ($row['status'] == 'Application Sent') { echo 'selected'; } ?> value="Application Sent">Application Sent</option>
                                        <option <?php if ($row['status'] == 'Offer/Acceptance Received') { echo 'selected'; } ?> value="Offer/Acceptance Received">Offer/Acceptance Received</option>
                                        <option <?php if ($row['status'] == 'Rejection Received') { echo 'selected'; } ?> value="Rejection Received">Rejection Received</option>
                                        <option <?php if ($row['status'] == 'Offer Denied') { echo 'selected'; } ?> value="Offer Denied">Offer Denied</option>
                                        <option <?php if ($row['status'] == 'Deposit Paid/Offer Accepted') { echo 'selected'; } ?> value="Deposit Paid/Offer Accepted">Deposit Paid/Offer Accepted</option>
                                        <option <?php if ($row['status'] == 'Enrolling') { echo 'selected'; } ?> value="Enrolling">Enrolling</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <b>Offer</b><br/>
                                    <span style="font-size: 90%"><i>If you have received an offer or rejection, select relevant option below:</i></span>
                                </td>
                                <td class="right">
                                    <select name="offer" id="offer" style="width: 302px">
                                        <option <?php if ($row['offer'] == '') { echo 'selected'; } ?> value=""></option>
                                        <option <?php if ($row['offer'] == 'First Choice') { echo 'selected'; } ?> value="First Choice">Yes - First Choice</option>
                                        <option <?php if ($row['offer'] == 'Backup') { echo 'selected'; } ?> value="Backup">Yes - Backup Choice</option>
                                        <option <?php if ($row['offer'] == 'Y') { echo 'selected'; } ?> value="Y">Yes - Other</option>
                                        <option <?php if ($row['offer'] == 'N') { echo 'selected'; } ?> value="N">No</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td colspan=2 style='padding-top: 15px;'>
                                    <b>Offer Details</b><br/>
                                    <span style="font-size: 90%"><i>If you have received an offer, enter details here.</i></span><br/>
                                    <textarea name="offerDetails" id="offerDetails" rows=4 style="width:738px; margin: 5px 0px 0px 0px"><?php echo htmlPrep($row['offerDetails']) ?></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <span style="font-size: 90%"><i>* denotes a required field</i></span>
                                </td>
                                <td class="right">
                                    <input name="gibbonCourseID" id="gibbonCourseID" value="<?php echo $gibbonCourseID ?>" type="hidden">
                                    <input name="gibbonSchoolYearID" id="gibbonSchoolYearID" value="<?php echo $gibbonSchoolYearID ?>" type="hidden">
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
}
?>
