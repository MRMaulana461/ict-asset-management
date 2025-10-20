<?php

namespace App\Imports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class EmployeesImport implements ToModel, WithHeadingRow, SkipsOnError, SkipsOnFailure
{
    use SkipsErrors, SkipsFailures;

    private $createdCount = 0;
    private $updatedCount = 0;
    private $skippedCount = 0;
    
    /**
     * Column mapping - Excel columns to database fields
     * Supports Active Directory export format
     */
    private $columnMapping = [
        // EMPLOYEES table columns - exact DB field names
        'employee_id' => ['employee_id', 'employeeid', 'emp_id', 'empid', 'nik', 'nip', 'staff id', 'staffid'],
        'user_id' => ['user_id', 'userid', 'sam_account_name', 'samaccountname', 'sam', 'username', 'user_name', 'account name'],
        'name' => ['name', 'employee_name', 'employeename', 'emp_name', 'empname', 'nama', 'full name', 'fullname', 'title', 'display name', 'displayname'],
        'email' => ['email', 'email_address', 'emailaddress', 'e_mail', 'mail'],
        'department' => ['department', 'dept', 'departemen', 'divisi', 'division'],
        'cost_center' => ['cost_center', 'costcenter', 'cost center', 'cc', 'cost centre'],
        'is_active' => ['is_active', 'isactive', 'active', 'enabled', 'status', 'employee status', 'status karyawan'],
    ];

    public function model(array $row)
    {
        // Normalize and map columns
        $normalizedRow = $this->normalizeRow($row);
        $mappedData = $this->mapColumns($normalizedRow);
        
        // Skip if no employee_id (required)
        if (empty($mappedData['employee_id'])) {
            $this->skippedCount++;
            \Log::warning('Employee import skipped - No employee_id', ['row' => $normalizedRow]);
            return null;
        }

        try {
            // Prepare employee data
            $employeeData = $this->prepareEmployeeData($mappedData);

            // Check if employee exists
            $existingEmployee = Employee::where('employee_id', $mappedData['employee_id'])->first();

            if ($existingEmployee) {
                // UPDATE existing employee
                $existingEmployee->update($employeeData);
                $this->updatedCount++;
                
                \Log::info('Employee updated', [
                    'employee_id' => $mappedData['employee_id'],
                    'name' => $employeeData['name'] ?? $existingEmployee->name
                ]);
            } else {
                // CREATE new employee
                Employee::create($employeeData);
                $this->createdCount++;
                
                \Log::info('Employee created', [
                    'employee_id' => $mappedData['employee_id'],
                    'name' => $employeeData['name'] ?? 'N/A'
                ]);
            }

            return null;

        } catch (\Exception $e) {
            $this->skippedCount++;
            \Log::error('Employee import error', [
                'employee_id' => $mappedData['employee_id'] ?? 'N/A',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Normalize row keys: lowercase, remove spaces and special chars
     */
    private function normalizeRow(array $row): array
    {
        $normalized = [];
        foreach ($row as $key => $value) {
            if ($key === null || $value === null || $value === '') continue;
            
            $normalizedKey = strtolower(trim($key));
            $normalizedKey = preg_replace('/[^a-z0-9_]/', '', str_replace(' ', '_', $normalizedKey));
            $normalized[$normalizedKey] = trim($value);
        }
        return $normalized;
    }

    /**
     * Map Excel columns to database fields
     */
    private function mapColumns(array $normalizedRow): array
    {
        $mapped = [];
        
        foreach ($this->columnMapping as $dbField => $excelVariants) {
            foreach ($excelVariants as $variant) {
                $normalizedVariant = strtolower(preg_replace('/[^a-z0-9_]/', '', str_replace(' ', '_', $variant)));
                
                if (isset($normalizedRow[$normalizedVariant]) && $normalizedRow[$normalizedVariant] !== '') {
                    $mapped[$dbField] = $normalizedRow[$normalizedVariant];
                    break;
                }
            }
        }
        
        return $mapped;
    }

    /**
     * Prepare employee data for create/update
     */
    private function prepareEmployeeData(array $data): array
    {
        $employeeData = [
            'employee_id' => $data['employee_id'],
        ];

        // Optional fields - only add if not empty
        if (!empty($data['user_id'])) {
            $employeeData['user_id'] = $data['user_id'];
        }

        // === 🧠 AUTO-GENERATE NAME ===
        if (!empty($data['name'])) {
            $employeeData['name'] = $data['name'];
        } elseif (!empty($data['email'])) {
            // Ambil bagian sebelum @
            $nameFromEmail = explode('@', $data['email'])[0];

            // Hilangkan titik dan underscore, ganti jadi spasi
            $nameFromEmail = str_replace(['.', '_'], ' ', $nameFromEmail);

            // Hilangkan spasi ganda
            $nameFromEmail = preg_replace('/\s+/', ' ', $nameFromEmail);

            // Ubah ke Title Case (huruf besar di awal kata)
            $nameFromEmail = ucwords(strtolower(trim($nameFromEmail)));

            $employeeData['name'] = $nameFromEmail;
        } else {
            // Jika dua-duanya kosong
            $employeeData['name'] = 'Unknown';
        }

        // === EMAIL ===
        if (!empty($data['email'])) {
            $employeeData['email'] = $data['email'];
        } else {
            $employeeData['email'] = $data['employee_id'] . '@temp.local';
        }

        if (!empty($data['department'])) {
            $employeeData['department'] = $data['department'];
        }

        if (!empty($data['cost_center'])) {
            $employeeData['cost_center'] = $data['cost_center'];
        }

        // Handle is_active
        $employeeData['is_active'] = isset($data['is_active'])
            ? $this->parseBoolean($data['is_active'])
            : true;

        return $employeeData;
    }


    /**
     * Parse boolean from various formats (TRUE, true, 1, yes, enabled, etc)
     */
    private function parseBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        
        $value = strtolower(trim($value));
        
        // TRUE values
        if (in_array($value, ['true', '1', 'yes', 'y', 'enabled', 'active', 'on'])) {
            return true;
        }
        
        // FALSE values
        if (in_array($value, ['false', '0', 'no', 'n', 'disabled', 'inactive', 'off'])) {
            return false;
        }
        
        // Default
        return true;
    }

    // Getters for statistics
    public function getCreatedCount(): int { return $this->createdCount; }
    public function getUpdatedCount(): int { return $this->updatedCount; }
    public function getSkippedCount(): int { return $this->skippedCount; }
}