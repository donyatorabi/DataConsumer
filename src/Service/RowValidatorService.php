<?php

namespace App\Service;

class RowValidatorService
{
    public function validate(array $row, int $rowNumber): ?string
    {
        if (count($row) < 4) {
            return "Row $rowNumber: Not enough columns.";
        }

        [$name, $email, $position, $salary] = array_map('trim', $row);

        if (empty($name)) {
            return "Row $rowNumber: Name is missing.";
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Row $rowNumber: Invalid email '$email'.";
        }

        if (empty($position)) {
            return "Row $rowNumber: Position is missing.";
        }

        if (!is_numeric($salary)) {
            return "Row $rowNumber: Salary must be numeric.";
        }

        return null;
    }
}
