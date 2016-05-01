<?php
//USE ;end TO SEPERATE SQL STATEMENTS. DON'T USE ;end IN ANY OTHER PLACES!

$sql = array();
$count = 0;

//v1.0.00
$sql[$count][0] = '1.0.00';
$sql[$count][1] = "
INSERT INTO `gibbonAction` (`gibbonModuleID` ,`name` ,`precedence` ,`category` ,`description` ,`URLList` ,`entryURL` ,`defaultPermissionAdmin` ,`defaultPermissionTeacher` ,`defaultPermissionStudent` ,`defaultPermissionParent` ,`defaultPermissionSupport` ,`categoryPermissionStaff` ,`categoryPermissionStudent` ,`categoryPermissionParent` ,`categoryPermissionOther`) VALUES ((SELECT gibbonModuleID FROM gibbonModule WHERE name='Higher Education'), 'Edit My Reference Notes', 0, 'References', 'Allows students to share some notes with referees, outlining their achievements.', 'references_myNotes.php', 'references_myNotes.php', 'N', 'N', 'Y', 'N', 'N', 'N', 'Y', 'N', 'N');end
INSERT INTO `gibbonPermission` (`permissionID` ,`gibbonRoleID` ,`gibbonActionID`) VALUES (NULL , '3', (SELECT gibbonActionID FROM gibbonAction JOIN gibbonModule ON (gibbonAction.gibbonModuleID=gibbonModule.gibbonModuleID) WHERE gibbonModule.name='Higher Education' AND gibbonAction.name='Edit My Reference Notes'));end
INSERT INTO `gibbonAction` (`gibbonModuleID` ,`name` ,`precedence` ,`category` ,`description` ,`URLList` ,`entryURL` ,`defaultPermissionAdmin` ,`defaultPermissionTeacher` ,`defaultPermissionStudent` ,`defaultPermissionParent` ,`defaultPermissionSupport` ,`categoryPermissionStaff` ,`categoryPermissionStudent` ,`categoryPermissionParent` ,`categoryPermissionOther`) VALUES ((SELECT gibbonModuleID FROM gibbonModule WHERE name='Higher Education'), 'Request References', 0, 'References', 'Allows students to request that a reference be written for them.', 'references_request.php, references_request_add.php', 'references_request.php', 'N', 'N', 'Y', 'N', 'N', 'N', 'Y', 'N', 'N');end
INSERT INTO `gibbonPermission` (`permissionID` ,`gibbonRoleID` ,`gibbonActionID`) VALUES (NULL , '3', (SELECT gibbonActionID FROM gibbonAction JOIN gibbonModule ON (gibbonAction.gibbonModuleID=gibbonModule.gibbonModuleID) WHERE gibbonModule.name='Higher Education' AND gibbonAction.name='Request References'));end
ALTER TABLE `higherEducationStudent` ADD `referenceNotes` TEXT NOT NULL ;end
CREATE TABLE `higherEducationReference` (  `higherEducationReferenceID` int(12) unsigned zerofill NOT NULL AUTO_INCREMENT,  `gibbonPersonID` int(10) unsigned zerofill NOT NULL,  `gibbonSchoolYearID` int(3) unsigned zerofill NOT NULL,  `type` enum('Composite Reference','US Reference','') NOT NULL,  `status` enum('Pending','In Progress','Complete','Cancelled') NOT NULL,  `statusNotes` varchar(255) NOT NULL,  `notes` text NOT NULL,  `alertsSent` enum('N','Y') NOT NULL DEFAULT 'N',  `timestamp` timestamp NULL DEFAULT NULL,  PRIMARY KEY (`higherEducationReferenceID`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;end
INSERT INTO `gibbonAction` (`gibbonModuleID` ,`name` ,`precedence` ,`category` ,`description` ,`URLList` ,`entryURL` ,`defaultPermissionAdmin` ,`defaultPermissionTeacher` ,`defaultPermissionStudent` ,`defaultPermissionParent` ,`defaultPermissionSupport` ,`categoryPermissionStaff` ,`categoryPermissionStudent` ,`categoryPermissionParent` ,`categoryPermissionOther`) VALUES ((SELECT gibbonModuleID FROM gibbonModule WHERE name='Higher Education'), 'Manage References', 0, 'References', 'Allows coordinators to see, approve and edit all references.', 'references_manage.php, references_manage_edit.php, references_manage_delete.php', 'references_manage.php', 'Y', 'N', 'N', 'N', 'N', 'Y', 'N', 'N', 'N');end
INSERT INTO `gibbonPermission` (`permissionID` ,`gibbonRoleID` ,`gibbonActionID`) VALUES (NULL , '1', (SELECT gibbonActionID FROM gibbonAction JOIN gibbonModule ON (gibbonAction.gibbonModuleID=gibbonModule.gibbonModuleID) WHERE gibbonModule.name='Higher Education' AND gibbonAction.name='Manage References'));end
INSERT INTO `gibbonAction` (`gibbonModuleID` ,`name` ,`precedence` ,`category` ,`description` ,`URLList` ,`entryURL` ,`defaultPermissionAdmin` ,`defaultPermissionTeacher` ,`defaultPermissionStudent` ,`defaultPermissionParent` ,`defaultPermissionSupport` ,`categoryPermissionStaff` ,`categoryPermissionStudent` ,`categoryPermissionParent` ,`categoryPermissionOther`) VALUES ((SELECT gibbonModuleID FROM gibbonModule WHERE name='Higher Education'), 'Write References', 0, 'References', 'Allows teachers to contribute to those references that have been assigned to them.', 'references_write.php, references_write_edit.php', 'references_write.php', 'Y', 'Y', 'N', 'N', 'N', 'Y', 'N', 'N', 'N');end
INSERT INTO `gibbonPermission` (`permissionID` ,`gibbonRoleID` ,`gibbonActionID`) VALUES (NULL , '1', (SELECT gibbonActionID FROM gibbonAction JOIN gibbonModule ON (gibbonAction.gibbonModuleID=gibbonModule.gibbonModuleID) WHERE gibbonModule.name='Higher Education' AND gibbonAction.name='Write References'));end
INSERT INTO `gibbonPermission` (`permissionID` ,`gibbonRoleID` ,`gibbonActionID`) VALUES (NULL , '2', (SELECT gibbonActionID FROM gibbonAction JOIN gibbonModule ON (gibbonAction.gibbonModuleID=gibbonModule.gibbonModuleID) WHERE gibbonModule.name='Higher Education' AND gibbonAction.name='Write References'));end
CREATE TABLE `higherEducationReferenceComponent` (  `higherEducationReferenceComponentID` int(14) unsigned zerofill NOT NULL AUTO_INCREMENT,  `higherEducationReferenceID` int(12) unsigned zerofill NOT NULL,  `gibbonPersonID` int(10) unsigned zerofill NOT NULL COMMENT 'Referee',  `status` enum('Pending','In Progress','Complete') NOT NULL,  `type` enum('General','Academic','Pastoral','Other') NOT NULL,  `title` varchar(100) NOT NULL,  `body` text NOT NULL,  PRIMARY KEY (`higherEducationReferenceComponentID`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;end
";

//v1.0.01
++$count;
$sql[$count][0] = '1.0.01';
$sql[$count][1] = '';

//v1.0.02
++$count;
$sql[$count][0] = '1.0.02';
$sql[$count][1] = '';

//v1.0.03
++$count;
$sql[$count][0] = '1.0.03';
$sql[$count][1] = '';

//v1.0.04
++$count;
$sql[$count][0] = '1.0.04';
$sql[$count][1] = '';

//v1.0.05
++$count;
$sql[$count][0] = '1.0.05';
$sql[$count][1] = '';

//v1.0.06
++$count;
$sql[$count][0] = '1.0.06';
$sql[$count][1] = '';

//v1.0.07
++$count;
$sql[$count][0] = '1.0.07';
$sql[$count][1] = '';

//v1.0.08
++$count;
$sql[$count][0] = '1.0.08';
$sql[$count][1] = '';

//v1.0.09
++$count;
$sql[$count][0] = '1.0.09';
$sql[$count][1] = '';

//v1.0.10
++$count;
$sql[$count][0] = '1.0.10';
$sql[$count][1] = '';

//v1.0.11
++$count;
$sql[$count][0] = '1.0.11';
$sql[$count][1] = '';

//v1.0.12
++$count;
$sql[$count][0] = '1.0.12';
$sql[$count][1] = '';

//v1.0.13
++$count;
$sql[$count][0] = '1.0.13';
$sql[$count][1] = '';

//v1.0.14
++$count;
$sql[$count][0] = '1.0.14';
$sql[$count][1] = '';
