<?php

namespace App\Imports;

use App\Models\Asset;
use App\Models\AssetType;
use App\Models\Employee;
use App\Models\AssetHistory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Carbon\Carbon;

class AssetsImport implements ToModel, WithHeadingRow, SkipsOnError, SkipsOnFailure
{
    use SkipsErrors, SkipsFailures;

    private $createdCount = 0;
    private $updatedCount = 0;
    private $skippedCount = 0;
    private $employeesCreatedCount = 0;
    
    /**
     * Column mapping - Excel column variants to exact database columns
     */
    private $columnMapping = [
        // ASSETS table columns (exact match with DB)
        'asset_tag' => ['asset_tag', 'assettag', 'tag', 'asset tag', 'no_asset', 'no asset', 'asset no'],
        'serial_number' => ['serial_number', 'serialnumber', 'serial', 'sn', 'serial no'],
        'asset_type_id' => ['asset_type_id', 'assettypeid', 'type_id', 'typeid'],
        'asset_type_name' => ['asset_type', 'assettype', 'type', 'type name', 'typename', 'tipe', 'jenis'],
        'assigned_to' => ['assigned_to', 'assignedto', 'assigned to'],
        'status' => ['status', 'condition', 'kondisi', 'asset status'],
        'assignment_date' => ['assignment_date', 'assignmentdate', 'assign_date', 'assigndate', 'date assigned'],
        'last_status_date' => ['last_status_date', 'laststatusdate', 'status_date', 'statusdate', 'last status date'],
        'notes' => ['notes', 'note', 'catatan', 'keterangan', 'remarks', 'remark', 'description'],
        
        // EMPLOYEES table columns (exact match with DB)
        'employee_id' => ['employee_id', 'employeeid', 'emp_id', 'empid', 'nik', 'nip', 'staff id', 'staffid'],
        'sam_account_name' => ['sam_account_name', 'samaccountname', 'sam', 'username', 'user_name', 'account name'],
        'user_id' => ['user_id', 'userid'],
        'employee_name' => ['name', 'employee_name', 'employeename', 'emp_name', 'empname', 'nama', 'full name', 'fullname'],
        'email' => ['email', 'email_address', 'emailaddress', 'e_mail', 'mail'],
        'department' => ['department', 'dept', 'departemen', 'divisi', 'division'],
        'cost_center' => ['cost_center', 'costcenter', 'cost center', 'cc', 'cost centre'],
        'is_active' => ['is_active', 'isactive', 'active', 'enabled', 'status karyawan', 'employee status'],
    ];

    public function model(array $row)
    {
        // Normalize and map columns
        $normalizedRow = $this->normalizeRow($row);
        $mappedData = $this->mapColumns($normalizedRow);
        
        // Skip if no asset_tag
        if (empty($mappedData['asset_tag'])) {
            $this->skippedCount++;
            return null;
        }

        try {
            // 1. GET ASSET TYPE ID
            $assetTypeId = $this->getAssetTypeId($mappedData);
            if (!$assetTypeId) {
                $this->skippedCount++;
                \Log::warning('Asset import skipped - Asset type not found', [
                    'asset_tag' => $mappedData['asset_tag'],
                    'asset_type' => $mappedData['asset_type_name'] ?? $mappedData['asset_type_id'] ?? 'N/A'
                ]);
                return null;
            }

            // 2. HANDLE EMPLOYEE (assigned_to)
            $assignedToId = $this->handleEmployee($mappedData);

            // 3. CHECK IF ASSET EXISTS
            $existingAsset = Asset::where('asset_tag', $mappedData['asset_tag'])->first();
            
            // 4. PREPARE ASSET DATA - only non-null values
            $assetData = $this->prepareAssetData($mappedData, $assetTypeId, $assignedToId);

            // 5. CREATE or UPDATE
            if ($existingAsset) {
                // UPDATE existing asset
                $oldAssignedTo = $existingAsset->assigned_to;
                $existingAsset->update($assetData);
                
                // Handle assignment history
                $this->handleAssetHistory($existingAsset, $oldAssignedTo, $assignedToId, 'Updated via Excel import');
                
                $this->updatedCount++;
            } else {
                // CREATE new asset
                
                // Check serial_number duplicate
                if (!empty($mappedData['serial_number'])) {
                    $existingSerial = Asset::where('serial_number', $mappedData['serial_number'])->first();
                    if ($existingSerial) {
                        $this->skippedCount++;
                        \Log::warning('Asset import skipped - Duplicate serial number', [
                            'asset_tag' => $mappedData['asset_tag'],
                            'serial_number' => $mappedData['serial_number']
                        ]);
                        return null;
                    }
                }

                $asset = Asset::create($assetData);

                // Create history if assigned
                if ($assignedToId) {
                    AssetHistory::create([
                        'asset_id' => $asset->id,
                        'employee_id' => $assignedToId,
                        'assignment_date' => $asset->assignment_date ?? now(),
                        'notes' => 'Created via Excel import'
                    ]);
                }

                $this->createdCount++;
            }

            return null;

        } catch (\Exception $e) {
            $this->skippedCount++;
            \Log::error('Asset import error', [
                'asset_tag' => $mappedData['asset_tag'] ?? 'N/A',
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
            if ($key === null || $value === null) continue;
            
            $normalizedKey = strtolower(trim($key));
            $normalizedKey = preg_replace('/[^a-z0-9_]/', '', str_replace(' ', '_', $normalizedKey));
            $normalized[$normalizedKey] = trim($value);
        }
        return $normalized;
    }

    /**
     * Map Excel columns to exact database columns
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
     * Get asset_type_id from name or direct ID
     */
    private function getAssetTypeId(array $data): ?int
    {
        // Direct asset_type_id
        if (!empty($data['asset_type_id']) && is_numeric($data['asset_type_id'])) {
            $exists = AssetType::find($data['asset_type_id']);
            if ($exists) return (int)$data['asset_type_id'];
        }
        
        // From asset_type name
        if (!empty($data['asset_type_name'])) {
            $assetType = AssetType::where('name', 'LIKE', '%' . $data['asset_type_name'] . '%')->first();
            if ($assetType) return $assetType->id;
            
            // Exact match
            $assetType = AssetType::where('name', $data['asset_type_name'])->first();
            if ($assetType) return $assetType->id;
        }
        
        return null;
    }

    /**
     * Handle employee: find or auto-create
     */
    private function handleEmployee(array $data): ?int
    {
        // Check if employee specified
        $employeeIdentifier = $data['employee_id'] ?? $data['assigned_to'] ?? null;
        
        if (empty($employeeIdentifier)) {
            return null;
        }

        // Find employee by employee_id or id
        $employee = Employee::where('employee_id', $employeeIdentifier)
            ->orWhere('id', $employeeIdentifier)
            ->first();
        
        // Auto-create employee if not exists and has name
        if (!$employee && !empty($data['employee_name'])) {
            $employee = Employee::create([
                'employee_id' => $employeeIdentifier,
                'sam_account_name' => $data['sam_account_name'] ?? null,
                'user_id' => !empty($data['user_id']) ? $data['user_id'] : null,
                'name' => $data['employee_name'],
                'email' => $data['email'] ?? $employeeIdentifier . '@temp.local',
                'department' => $data['department'] ?? 'Not Available',
                'cost_center' => $data['cost_center'] ?? null,
                'is_active' => isset($data['is_active']) ? filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN) : true
            ]);
            
            $this->employeesCreatedCount++;
            
            \Log::info('Auto-created employee during import', [
                'employee_id' => $employeeIdentifier,
                'name' => $data['employee_name']
            ]);
        }
        
        return $employee ? $employee->id : null;
    }

    /**
     * Prepare asset data for create/update - exact DB columns only
     */
    private function prepareAssetData(array $data, int $assetTypeId, ?int $assignedToId): array
    {
        $assetData = [
            'asset_tag' => $data['asset_tag'],
            'asset_type_id' => $assetTypeId,
            'last_status_date' => $this->parseDate($data['last_status_date'] ?? null) ?? now(),
        ];

        // Optional fields - only add if not empty
        if (!empty($data['serial_number'])) {
            $assetData['serial_number'] = $data['serial_number'];
        }
        
        if ($assignedToId !== null) {
            $assetData['assigned_to'] = $assignedToId;
        }
        
        if (!empty($data['status'])) {
            $assetData['status'] = $this->validateStatus($data['status']);
        }
        
        if (!empty($data['assignment_date'])) {
            $assetData['assignment_date'] = $this->parseDate($data['assignment_date']);
        }
        
        if (!empty($data['notes'])) {
            $assetData['notes'] = $data['notes'];
        }

        return $assetData;
    }

    /**
     * Validate status against DB enum values
     */
    private function validateStatus(string $status): string
    {
        // Exact DB enum values
        $validStatuses = ['In Stock', 'In Use', 'Broken', 'Retired', 'Taken'];
        
        // Exact match (case-insensitive)
        foreach ($validStatuses as $valid) {
            if (strcasecmp($valid, $status) === 0) {
                return $valid;
            }
        }
        
        // Fuzzy match
        foreach ($validStatuses as $valid) {
            if (stripos($valid, $status) !== false || stripos($status, $valid) !== false) {
                return $valid;
            }
        }
        
        return 'In Stock'; // Default
    }

    /**
     * Parse date with multiple formats
     */
    private function parseDate($date)
    {
        if (empty($date)) return null;
        
        try {
            // Excel serial date number
            if (is_numeric($date)) {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date));
            }
            
            // Parse various date formats
            return Carbon::parse($date);
        } catch (\Exception $e) {
            \Log::warning('Date parse failed', ['date' => $date, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Handle asset history when assignment changes
     */
    private function handleAssetHistory($asset, $oldEmployeeId, $newEmployeeId, $notes = null)
    {
        if ($oldEmployeeId != $newEmployeeId) {
            // Close old history
            if ($oldEmployeeId) {
                $lastHistory = AssetHistory::where('asset_id', $asset->id)
                    ->where('employee_id', $oldEmployeeId)
                    ->whereNull('return_date')
                    ->first();
                
                if ($lastHistory) {
                    $lastHistory->update(['return_date' => now()]);
                }
            }

            // Create new history
            if ($newEmployeeId) {
                AssetHistory::create([
                    'asset_id' => $asset->id,
                    'employee_id' => $newEmployeeId,
                    'assignment_date' => now(),
                    'notes' => $notes
                ]);
            }
        }
    }

    // Getters for statistics
    public function getCreatedCount(): int { return $this->createdCount; }
    public function getUpdatedCount(): int { return $this->updatedCount; }
    public function getSkippedCount(): int { return $this->skippedCount; }
    public function getEmployeesCreatedCount(): int { return $this->employeesCreatedCount; }
}