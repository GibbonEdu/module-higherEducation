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

namespace Gibbon\Module\HigherEducation\Domain;

use Gibbon\Domain\Traits\TableAware;
use Gibbon\Domain\QueryCriteria;
use Gibbon\Domain\QueryableGateway;

class ApplicationGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'higherEducationApplication';
    private static $primaryKey = 'higherEducationApplicationID';

    public function queryApplications($criteria, $role, $gibbonSchoolYearID, $gibbonPersonIDAdvisor)
    {
        $query = $this
            ->newQuery()
            ->cols(['gibbonPerson.gibbonPersonID', 'higherEducationStudentID', 'surname', 'preferredName', 'gibbonYearGroup.nameShort AS yearGroup', 'gibbonFormGroup.nameShort AS formGroup', 'gibbonFormGroup.gibbonFormGroupID', 'applying', 'gibbonPersonIDAdvisor', 'gibbonSchoolYear.name AS schoolYear', 'count(*) AS applications'])
            ->from('higherEducationStudent')
            ->innerJoin('gibbonPerson', 'higherEducationStudent.gibbonPersonID=gibbonPerson.gibbonPersonID')
            ->innerJoin('gibbonStudentEnrolment', 'higherEducationStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID')
            ->leftJoin('gibbonSchoolYear', 'gibbonSchoolYear.gibbonSchoolYearID=gibbonPerson.gibbonSchoolYearIDClassOf')
            ->leftJoin('gibbonYearGroup', 'gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID')
            ->leftJoin('gibbonFormGroup', 'gibbonStudentEnrolment.gibbonFormGroupID=gibbonFormGroup.gibbonFormGroupID')
            ->leftJoin('higherEducationApplication', 'higherEducationApplication.gibbonPersonID=gibbonPerson.gibbonPersonID')
            ->leftJoin('higherEducationApplicationInstitution', 'higherEducationApplicationInstitution.higherEducationApplicationID=higherEducationApplication.higherEducationApplicationID')
            ->where('gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID')
            ->bindValue('gibbonSchoolYearID', $gibbonSchoolYearID)
            ->where("gibbonPerson.status='Full'")
            ->groupBy(['gibbonPerson.gibbonPersonID']);

        if ($role == 'Advisor') {
            $query->where('gibbonPersonIDAdvisor=:gibbonPersonIDAdvisor')
                ->bindValue('gibbonPersonIDAdvisor', $gibbonPersonIDAdvisor);
        }

        return $this->runQuery($query, $criteria);
    }

}
