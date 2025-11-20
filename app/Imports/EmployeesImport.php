<?php

namespace App\Imports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EmployeesImport implements 
    ToModel, 
    WithHeadingRow, 
    SkipsOnError, 
    SkipsOnFailure,
    WithBatchInserts,
    WithChunkReading
{
    use SkipsErrors, SkipsFailures;

    private $createdCount = 0;
    private $updatedCount = 0;
    private $skippedCount = 0;
    private $skipReasons = [];
    
    private $existingEmployeesCache = [];
    private $cacheInitialized = false;
    private static $isFirstBatch = true;
    
    /**
     * Column mapping - Excel column variants to exact database columns
     */
    private $columnMapping = [
        // Primary identifiers
        'ghrs_id' => ['ghrs_id', 'ghrsid', 'ghrs', 'employee_id'],
        'comp_empl_id' => ['comp_empl_id', 'compemplid', 'comp empl id', 'company_employee_id'],
        'empl_rcd' => ['empl_rcd', 'emplrcd', 'empl rcd #', 'empl_rcd_', 'employee_record'],
        'badge_id' => ['badge_id', 'badgeid', 'badge', 'badge number'],
        'user_id' => ['samaccountname', 'sam_account_name', 'username', 'user_id', 'userid'],
        
        // Basic info
        'first_name' => ['first_name', 'firstname', 'first name'],
        'last_name' => ['lastname', 'last_name', 'last name'],
        'name' => ['name', 'employee_name', 'full_name', 'fullname'],
        'email' => ['emailaddress', 'email_address', 'email', 'e_mail'],
        
        // Organizational
        'company' => ['company', 'role_company', 'role company'],
        'org_context' => ['org_context', 'org. context', 'org context', 'organization'],
        'dept_id' => ['deptid', 'dept_id', 'department_id'],
        'department' => ['department', 'dept', 'cost_center_descr', 'cost center descr'],
        'org_relation' => ['org_relation', 'org relation'],
        'agency' => ['agency'],
        'boc' => ['boc'],
        'cost_center' => ['cost_center', 'cost center', 'costcenter'],
        'cost_center_descr' => ['cost_center_descr', 'cost center descr', 'cost center description'],
        'role_company' => ['role_company', 'role company'],
        'employee_class' => ['empl_class', 'empl class', 'employee_class', 'class'],
        'tipo_terzi' => ['tipo_terzi', 'tipo terzi'],
        'contractual_position' => ['contractual_position', 'contractual position', 'position', 'job_title'],
        
        // NEW: Additional columns from migration
        'job_code' => ['job_code', 'jobcode', 'job code'],
        'job_title' => ['job_code_description', 'job code description', 'job_title', 'jobtitle', 'title'],
        'supervisor_name' => ['supervisor_name', 'supervisor name', 'supv_name'],
        'supervisor_id' => ['supv_id', 'supervisor_id', 'supervisor id'],
        'contract_type' => ['contract_type', 'contract type'],
        'contract_number' => ['contract_', 'contract_number', 'contract #'],
        'hire_date' => ['hire_rehire_date', 'hire/rehire date', 'hire_date', 'hiredate'],
        'expiry_date' => ['expiry_date', 'expiry date', 'contract_expiry'],
        'first_start_date' => ['first_start_date', 'first start date'],
        'location' => ['location', 'lokasi', 'site'],
        'project_id' => ['project_id', 'project id'],
        'project_description' => ['descr', 'project_description', 'project description'],
        
        // Status
        'is_active' => ['is_active', 'isactive', 'active', 'status'],
    ];

    public function batchSize(): int
    {
        return 500;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    private function initializeCache()
    {
        if (!$this->cacheInitialized) {
            // ✅ STEP 3A: Set semua employee inactive (hanya sekali di batch pertama)
            if (self::$isFirstBatch) {
                DB::table('employees')->update(['is_active' => false]);
                self::$isFirstBatch = false;
                
                Log::info('🔄 All employees set to inactive before import');
            }
            
            $this->existingEmployeesCache = Employee::pluck('id', 'ghrs_id')->toArray();
            $this->cacheInitialized = true;
            
            Log::info('Employee cache initialized', [
                'total' => count($this->existingEmployeesCache)
            ]);
        }
    }

    public function model(array $row)
    {
        $this->initializeCache();
        
        $normalizedRow = $this->normalizeRow($row);
        $mappedData = $this->mapColumns($normalizedRow);
        
        if (empty($mappedData['ghrs_id'])) {
            $this->skippedCount++;
            $this->skipReasons['no_ghrs_id'] = ($this->skipReasons['no_ghrs_id'] ?? 0) + 1;
            
            if (($this->skipReasons['no_ghrs_id'] ?? 0) <= 5) {
                Log::warning('SKIPPED - No GHRS ID', [
                    'raw_keys' => array_keys($row),
                    'mapped_keys' => array_keys($mappedData)
                ]);
            }
            return null;
        }

        try {
            // Normalize email - set empty email to NULL
            if (isset($mappedData['email'])) {
                $mappedData['email'] = !empty($mappedData['email']) ? $mappedData['email'] : null;
            }
            
            // Extract name from email if name is NULL/empty but email exists
            $mappedData = $this->extractNameFromEmail($mappedData);
            
            // Map badge_id dari Comp Empl ID
            if (!empty($mappedData['comp_empl_id']) && empty($mappedData['badge_id'])) {
                $mappedData['badge_id'] = $mappedData['comp_empl_id'];
            }
            
            // Prepare name untuk kolom 'name'
            $name = $this->prepareName($mappedData);
            
            if (empty($name)) {
                $this->skippedCount++;
                $this->skipReasons['no_name'] = ($this->skipReasons['no_name'] ?? 0) + 1;
                
                if (($this->skipReasons['no_name'] ?? 0) <= 5) {
                    Log::warning('SKIPPED - No name', [
                        'ghrs_id' => $mappedData['ghrs_id'],
                        'first_name' => $mappedData['first_name'] ?? 'null',
                        'last_name' => $mappedData['last_name'] ?? 'null',
                        'name' => $mappedData['name'] ?? 'null',
                        'email' => $mappedData['email'] ?? 'null'
                    ]);
                }
                return null;
            }

            $existingEmployeeId = $this->existingEmployeesCache[$mappedData['ghrs_id']] ?? null;
            
            $employeeData = $this->prepareEmployeeData($mappedData, $name);

            if ($existingEmployeeId) {
                $existingEmployee = Employee::find($existingEmployeeId);
                $existingEmployee->update($employeeData);
                $this->updatedCount++;
                
                Log::info('Employee updated', [
                    'ghrs_id' => $mappedData['ghrs_id'],
                    'name' => $name
                ]);
            } else {
                $newEmployee = Employee::create($employeeData);
                $this->existingEmployeesCache[$mappedData['ghrs_id']] = $newEmployee->id;
                $this->createdCount++;
                
                Log::info('Employee created', [
                    'ghrs_id' => $mappedData['ghrs_id'],
                    'name' => $name
                ]);
            }

            return null;

        } catch (\Exception $e) {
            $this->skippedCount++;
            $this->skipReasons['error'] = ($this->skipReasons['error'] ?? 0) + 1;
            
            Log::error('Employee import error', [
                'ghrs_id' => $mappedData['ghrs_id'] ?? 'N/A',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile())
            ]);
            return null;
        }
    }

    private function normalizeRow(array $row): array
    {
        $normalized = [];
        foreach ($row as $key => $value) {
            if ($key === null) continue;
            
            $normalizedKey = strtolower(trim($key));
            $normalizedKey = str_replace([' ', '.', '#', '/', '-'], '_', $normalizedKey);
            
            $normalized[$normalizedKey] = $value !== null ? trim($value) : null;
        }
        return $normalized;
    }

    private function mapColumns(array $normalizedRow): array
    {
        $mapped = [];
        
        foreach ($this->columnMapping as $dbField => $excelVariants) {
            foreach ($excelVariants as $variant) {
                $normalizedVariant = strtolower(preg_replace('/[^a-z0-9_]/', '', str_replace([' ', '.', '#', '/', '-'], '_', $variant)));
                
                if (isset($normalizedRow[$normalizedVariant]) && $normalizedRow[$normalizedVariant] !== '' && $normalizedRow[$normalizedVariant] !== null) {
                    $mapped[$dbField] = $normalizedRow[$normalizedVariant];
                    break;
                }
            }
        }
        
        return $mapped;
    }

    private function extractNameFromEmail(array $data): array
    {
        if (!empty($data['first_name']) || !empty($data['last_name'])) {
            return $data;
        }
        
        if (empty($data['email'])) {
            return $data;
        }
        
        $emailParts = explode('@', $data['email']);
        $localPart = $emailParts[0] ?? '';
        
        if (empty($localPart)) {
            return $data;
        }
        
        $nameParts = explode('.', $localPart);
        
        if (count($nameParts) >= 2) {
            $data['first_name'] = $nameParts[0];
            $data['last_name'] = $nameParts[1];
        } else {
            $data['first_name'] = $nameParts[0];
            $data['last_name'] = null;
        }
        
        return $data;
    }

    private function prepareName(array $data): ?string
    {
        if (!empty($data['name'])) {
            return $data['name'];
        }
        
        $firstName = $data['first_name'] ?? '';
        $lastName = $data['last_name'] ?? '';
        
        if (!empty($firstName) || !empty($lastName)) {
            return trim($firstName . ' ' . $lastName);
        }
        
        if (!empty($data['ghrs_id'])) {
            return 'Employee ' . $data['ghrs_id'];
        }
        
        return null;
    }

    private function prepareEmployeeData(array $data, string $name): array
    {
        $employeeData = [
            'ghrs_id' => $data['ghrs_id'],
            'name' => $name,
            'is_active' => true, // ✅ Yang di Excel jadi ACTIVE
        ];

        // Basic info
        if (!empty($data['first_name'])) {
            $employeeData['first_name'] = $data['first_name'];
        }
        
        if (!empty($data['last_name'])) {
            $employeeData['last_name'] = $data['last_name'];
        }

        if (!empty($data['empl_rcd'])) {
            $employeeData['empl_rcd'] = (int)$data['empl_rcd'];
        }
        
        if (!empty($data['badge_id'])) {
            $employeeData['badge_id'] = $data['badge_id'];
        }
        
        if (!empty($data['user_id'])) {
            $employeeData['user_id'] = $data['user_id'];
        }
        
        if (isset($data['email'])) {
            $employeeData['email'] = $data['email'];
        }
        
        // Organizational
        if (!empty($data['company'])) {
            $employeeData['company'] = $data['company'];
        }
        
        if (!empty($data['org_context'])) {
            $employeeData['org_context'] = $data['org_context'];
        }
        
        if (!empty($data['dept_id'])) {
            $employeeData['dept_id'] = $data['dept_id'];
        }
        
        if (!empty($data['department'])) {
            $employeeData['department'] = $data['department'];
        }
        
        if (!empty($data['org_relation'])) {
            $employeeData['org_relation'] = $data['org_relation'];
        }
        
        if (!empty($data['agency'])) {
            $employeeData['agency'] = $data['agency'];
        }
        
        if (!empty($data['boc'])) {
            $employeeData['boc'] = $data['boc'];
        }
        
        if (!empty($data['cost_center'])) {
            $employeeData['cost_center'] = $data['cost_center'];
        }
        
        if (!empty($data['cost_center_descr'])) {
            $employeeData['cost_center_descr'] = $data['cost_center_descr'];
        }
        
        if (!empty($data['role_company'])) {
            $employeeData['role_company'] = $data['role_company'];
        }
        
        if (!empty($data['employee_class'])) {
            $employeeData['employee_class'] = $this->validateEmployeeClass($data['employee_class']);
        }
        
        if (!empty($data['tipo_terzi'])) {
            $employeeData['tipo_terzi'] = $data['tipo_terzi'];
        }
        
        if (!empty($data['contractual_position'])) {
            $employeeData['contractual_position'] = $data['contractual_position'];
        }

        // ✅ NEW: Additional columns
        if (!empty($data['job_code'])) {
            $employeeData['job_code'] = $data['job_code'];
        }
        
        if (!empty($data['job_title'])) {
            $employeeData['job_title'] = $data['job_title'];
        }
        
        if (!empty($data['supervisor_name'])) {
            $employeeData['supervisor_name'] = $data['supervisor_name'];
        }
        
        if (!empty($data['supervisor_id'])) {
            $employeeData['supervisor_id'] = $data['supervisor_id'];
        }
        
        if (!empty($data['contract_type'])) {
            $employeeData['contract_type'] = $data['contract_type'];
        }
        
        if (!empty($data['contract_number'])) {
            $employeeData['contract_number'] = $data['contract_number'];
        }
        
        if (!empty($data['hire_date'])) {
            $employeeData['hire_date'] = $this->parseDate($data['hire_date']);
        }
        
        if (!empty($data['expiry_date'])) {
            $employeeData['expiry_date'] = $this->parseDate($data['expiry_date']);
        }
        
        if (!empty($data['first_start_date'])) {
            $employeeData['first_start_date'] = $this->parseDate($data['first_start_date']);
        }
        
        if (!empty($data['location'])) {
            $employeeData['location'] = $data['location'];
        }
        
        if (!empty($data['project_id'])) {
            $employeeData['project_id'] = $data['project_id'];
        }
        
        if (!empty($data['project_description'])) {
            $employeeData['project_description'] = $data['project_description'];
        }

        return $employeeData;
    }

    private function parseDate($date)
    {
        if (empty($date)) return null;
        
        try {
            if (is_numeric($date)) {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date))->format('Y-m-d');
            }
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function validateEmployeeClass(string $class): ?string
    {
        $class = strtoupper(trim($class));
        
        if ($class === 'W' || $class === 'WHITE' || $class === 'WHITE COLLAR') {
            return 'W';
        }
        
        if ($class === 'B' || $class === 'BLUE' || $class === 'BLUE COLLAR') {
            return 'B';
        }
        
        return null;
    }

    public function getCreatedCount(): int 
    { 
        return $this->createdCount; 
    }
    
    public function getUpdatedCount(): int 
    { 
        return $this->updatedCount; 
    }
    
    public function getSkippedCount(): int 
    { 
        return $this->skippedCount; 
    }
    
    public function getSkipReasons(): array
    {
        return $this->skipReasons;
    }
}