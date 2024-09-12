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
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

namespace Gibbon\Module\HigherEducation\Domain;

use Gibbon\Domain\Traits\TableAware;
use Gibbon\Domain\QueryCriteria;
use Gibbon\Domain\QueryableGateway;

class StudentGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'higherEducationStudent';
    private static $primaryKey = 'higherEducationStudentID';

    public function queryStudents($criteria, $gibbonSchoolYearID)
    {
        $query = $this
            ->newQuery()
            ->cols(['higherEducationStudentID', 'student.surname', 'student.preferredName', 'gibbonYearGroup.nameShort AS yearGroup', 'gibbonFormGroup.nameShort AS formGroup', 'advisor.surname AS advisorsurname', 'advisor.preferredName AS advisorpreferredName', 'gibbonSchoolYear.name AS schoolYear'])
            ->from($this->getTableName())
            ->innerJoin('gibbonPerson AS student','higherEducationStudent.gibbonPersonID=student.gibbonPersonID')
            ->innerJoin('gibbonStudentEnrolment','higherEducationStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID')
            ->leftJoin('gibbonPerson AS advisor','higherEducationStudent.gibbonPersonIDAdvisor=advisor.gibbonPersonID')
            ->leftJoin('gibbonSchoolYear','gibbonSchoolYear.gibbonSchoolYearID=student.gibbonSchoolYearIDClassOf')
            ->leftJoin('gibbonYearGroup','gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID')
            ->leftJoin('gibbonFormGroup','gibbonStudentEnrolment.gibbonFormGroupID=gibbonFormGroup.gibbonFormGroupID')
            ->where('gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID')
            ->bindValue('gibbonSchoolYearID', $gibbonSchoolYearID)
            ->where('student.status=\'Full\'');

        return $this->runQuery($query, $criteria);
    }

}
