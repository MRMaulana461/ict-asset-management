<?php

namespace App\Imports;

use App\Models\Asset;
use App\Models\AssetType;
use App\Models\Employee;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssetsImport implements 
    ToCollection,
    WithHeadingRow,
    WithChunkReading
{
    private $createdCount = 0;
    private $updatedCount = 0;
    private $skippedCount = 0;
    private $assetTypesCreatedCount = 0;
    private $skipReasons = []; // ✅ Track skip reasons
    
    private $assetTypesCache = [];
    private $employeesCache = [];
    private $existingAssetsCache = [];
    private static $processedTags = [];
    private static $tagCounter = 1;
    private static $isFirstBatch = true;
    
    private $assetBatch = [];
    private $batchSize = 500;
    
    private $columnMapping = [
        // Excel columns mapping
        'code' => ['code', 'no', 'no.'],
        'pr_ref' => ['pr_ref', 'prref', 'pr'],
        'po_ref' => ['po_ref', 'poref', 'po', 'po ref'],
        'asset_tag' => ['asset_tag', 'assettag', 'tag'],
        'status' => ['status', 'condition'],
        'assignment_date' => ['assignment_date', 'assignmentdate', 'assignment date'],
        'assignment_status' => ['assignment_status', 'assignmentstatus', 'assignment status'],
        'serial_number' => ['serial_number', 'serialnumber', 'serial number'],
        'user_id' => ['user_id', 'userid', 'user id'],
        'email_address' => ['email_address', 'emailaddress', 'email address'],
        'employee_id' => ['employee_id', 'employeeid', 'employee id'], // ✅ Ini = GHRS ID
        'department' => ['department', 'dept'],
        'cost_center' => ['cost_center', 'costcenter', 'cost center'],
        'location_site' => ['location_site', 'locationsite', 'location site'],
        'location' => ['location', 'lokasi'],
        'type' => ['type', 'item_type'],
        'device_model' => ['device_model', 'devicemodel', 'device model'],
        'memory' => ['memory', 'ram'],
        'remarks' => ['remarks', 'remark', 'notes'],
        'soc_compliant' => ['soc_compliant', 'soccompliant', 'soc compliant'],
        'ref' => ['ref', 'reference'],
        'dept_cost_center' => ['dept___cost_center', 'dept_cost_center', 'dept - cost center'],
        
        // Legacy columns (tetap support format lama)
        'item_name' => ['item', 'item_name', 'itemname'],
        'brand' => ['brand', 'merk'],
        'service_tag' => ['service_tag', 'servicetag'],
        'serial_clean' => ['serial_clean', 'serialclean'],
        'ghrs_id' => ['ghrs_id', 'ghrsid'],
        'badge_id' => ['badge_id', 'badgeid'],
        'username' => ['username', 'user_name'],
        'delivery_date' => ['delivery_date', 'deliverydate'],
        'dept_project' => ['deptproject', 'dept_project', 'dept/project'],
        'device_name' => ['device_name', 'devicename'],
        'asset_type_name' => ['asset_type', 'assettype'],
    ];

    private $assetTypeKeywords = [
        'Laptop' => ['laptop', 'notebook', 'macbook', 'thinkpad', 'latitude', 'elitebook'],
        'Desktop' => ['desktop', 'pc', 'prodesk', 'optiplex'],
        'Monitor' => ['monitor', 'display', 'screen'],
        'Mouse' => ['mouse', 'mice'],
        'Keyboard' => ['keyboard', 'keypad'],
        'Printer' => ['printer', 'laserjet'],
        'Headset' => ['headset', 'headphone'],
        'Earphone' => ['earphone', 'earbud'],
        'Docking' => ['docking', 'dock', 'wd22', 'ud22'],
        'SSD' => ['ssd', 'solid state'],
        'VGA Card' => ['vga', 'graphics', 'nvidia', 'geforce'],
        'USB Hub' => ['usb hub', 'hub'],
        'Webcam' => ['webcam', 'camera'],
        'Cable' => ['cable', 'kabel', 'hdmi'],
        'UPS' => ['ups', 'battery backup'],
        'Router' => ['router', 'switch', 'access point'],
        'Adapter' => ['adapter', 'charger'],
    ];

    public function __construct()
    {
        set_time_limit(600);
        ini_set('memory_limit', '1024M');
        
        // Pre-load caches
        $this->assetTypesCache = AssetType::all()->keyBy(function($item) {
            return strtolower($item->name);
        })->toArray();
        
        $employees = Employee::select('id', 'ghrs_id', 'badge_id', 'name')->get();
        $this->employeesCache = [
            'ghrs' => $employees->whereNotNull('ghrs_id')->pluck('id', 'ghrs_id')->toArray(),
            'badge' => $employees->whereNotNull('badge_id')->pluck('id', 'badge_id')->toArray(),
            'name' => $employees->pluck('id', 'name')->toArray(),
        ];
        
        $this->existingAssetsCache = Asset::pluck('id', 'asset_tag')->toArray();
        
        Log::info('🚀 Asset import initialized', [
            'asset_types' => count($this->assetTypesCache),
            'employees' => array_sum(array_map('count', $this->employeesCache)),
            'existing_assets' => count($this->existingAssetsCache)
        ]);
    }

    public function collection(Collection $rows)
    {
        $startTime = microtime(true);
        
        // ✅ STEP 3B: Hapus semua Laptop & Desktop (hanya sekali di batch pertama)
        if (self::$isFirstBatch) {
            DB::transaction(function() {
                $deletedCount = Asset::whereIn('type', ['Laptop', 'Desktop'])->delete();
                
                Log::info('🗑️ Deleted old Laptop & Desktop assets', [
                    'count' => $deletedCount
                ]);
            });
            
            // Refresh cache setelah delete
            $this->existingAssetsCache = Asset::pluck('id', 'asset_tag')->toArray();
            
            self::$isFirstBatch = false;
        }
        
        $this->assetBatch = [];
        
        Log::info('📥 Processing asset rows', ['total' => $rows->count()]);
        
        foreach ($rows as $index => $row) {
            try {
                $normalizedRow = $this->normalizeRow($row->toArray());
                $mappedData = $this->mapColumns($normalizedRow);
                
                // Debug first 3 rows
                if ($index < 3) {
                    Log::info("Asset Row " . ($index + 2), [
                        'normalized_keys' => array_keys($normalizedRow),
                        'mapped_data' => array_keys($mappedData)
                    ]);
                }
                
                // SKIP jika benar-benar kosong
                if ($this->isCompletelyEmpty($mappedData)) {
                    $this->skippedCount++;
                    $this->skipReasons['empty_row'] = ($this->skipReasons['empty_row'] ?? 0) + 1; // ✅ Track
                    
                    // Log first 10 skipped rows for debugging
                    if ($this->skippedCount <= 10) {
                        Log::warning("Row " . ($index + 2) . " - Skipped (empty)", [
                            'raw_row' => array_filter($normalizedRow),
                            'mapped_data' => $mappedData
                        ]);
                    }
                    continue;
                }
                
                // Generate asset_tag jika kosong
                if (empty($mappedData['asset_tag'])) {
                    $mappedData['asset_tag'] = $this->generateUniqueAssetTag($mappedData, $index);
                }
                
                // Check existing
                $existingAssetId = $this->existingAssetsCache[$mappedData['asset_tag']] ?? null;
                
                // Prepare data
                $assetData = $this->prepareAssetData($mappedData);
                
                if ($existingAssetId) {
                    // UPDATE (jarang terjadi karena sudah di-delete)
                    Asset::where('id', $existingAssetId)->update($assetData);
                    $this->updatedCount++;
                } else {
                    // INSERT
                    $this->assetBatch[] = $assetData;
                    
                    if (count($this->assetBatch) >= $this->batchSize) {
                        $this->flushBatch();
                    }
                }
                
            } catch (\Exception $e) {
                $this->skippedCount++;
                $this->skipReasons['error'] = ($this->skipReasons['error'] ?? 0) + 1; // ✅ Track
                
                // Log error details
                Log::error('Asset row ' . ($index + 2) . ' error', [
                    'error' => $e->getMessage(),
                    'file' => basename($e->getFile()),
                    'line' => $e->getLine(),
                    'asset_tag' => $mappedData['asset_tag'] ?? 'N/A',
                    'serial' => $mappedData['serial_number'] ?? 'N/A',
                    'employee_id' => $mappedData['employee_id'] ?? 'N/A'
                ]);
            }
        }
        
        // Flush remaining
        if (!empty($this->assetBatch)) {
            $this->flushBatch();
        }
        
        $duration = round(microtime(true) - $startTime, 2);
        
        Log::info('✅ Asset import completed', [
            'created' => $this->createdCount,
            'updated' => $this->updatedCount,
            'skipped' => $this->skippedCount,
            'skip_reasons' => $this->skipReasons, // ✅ Show reasons
            'asset_types_created' => $this->assetTypesCreatedCount,
            'duration' => $duration . 's'
        ]);
    }

    private function flushBatch()
    {
        if (empty($this->assetBatch)) return;
        
        try {
            DB::transaction(function() {
                $chunks = array_chunk($this->assetBatch, 100);
                
                foreach ($chunks as $chunk) {
                    Asset::insert($chunk);
                }
                
                $this->createdCount += count($this->assetBatch);
                
                foreach ($this->assetBatch as $asset) {
                    $this->existingAssetsCache[$asset['asset_tag']] = true;
                }
            });
            
            Log::info('✓ Batch inserted', ['count' => count($this->assetBatch)]);
            
        } catch (\Exception $e) {
            Log::error('Batch insert failed', [
                'error' => $e->getMessage(),
                'batch_size' => count($this->assetBatch)
            ]);
            
            // Fallback: insert one by one
            foreach ($this->assetBatch as $asset) {
                try {
                    Asset::create($asset);
                    $this->createdCount++;
                } catch (\Exception $e2) {
                    Log::error('Single insert failed', [
                        'asset_tag' => $asset['asset_tag'],
                        'error' => $e2->getMessage()
                    ]);
                    $this->skippedCount++;
                    $this->skipReasons['insert_failed'] = ($this->skipReasons['insert_failed'] ?? 0) + 1; // ✅ Track
                }
            }
        }
        
        $this->assetBatch = [];
    }

    private function prepareAssetData(array $data): array
    {
        $now = now();
        
        // ✅ ALWAYS include ALL columns dengan default NULL untuk konsistensi
        $assetData = [
            'asset_tag' => $data['asset_tag'],
            'asset_type_id' => null,
            'assigned_to' => null,
            'ghrs_id' => null,
            'badge_id' => null,
            'status' => 'In Stock',
            'last_status_date' => $now,
            'serial_number' => null,
            'serial_clean' => null,
            'service_tag' => null,
            'assignment_date' => null,
            'delivery_date' => null,
            'pr_ref' => null,
            'po_ref' => null,
            'ref' => null,
            'item_name' => null,
            'brand' => null,
            'type' => null,
            'code' => null,
            'email_address' => null,
            'cost_center' => null,
            'location_site' => null,
            'assignment_status' => null,
            'soc_compliant' => null,
            'dept_cost_center' => null,
            'memory' => null,
            'username' => null,
            'location' => null,
            'dept_project' => null,
            'device_name' => null,
            'remarks' => null,
            'specifications' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ];
        
        // Detect asset type
        $assetTypeId = $this->detectAssetType($data);
        if ($assetTypeId) {
            $assetData['asset_type_id'] = $assetTypeId;
        }
        
        // ✅ NEW: Link employee via EMPLOYEE ID (yang = GHRS ID)
        $assignedToId = null;
        $ghrsId = $data['employee_id'] ?? null; // EMPLOYEE ID dari Excel
        
        if ($ghrsId) {
            $employee = Employee::where('ghrs_id', $ghrsId)->first();
            if ($employee) {
                $assignedToId = $employee->id;
                $assetData['ghrs_id'] = $ghrsId;
                $assetData['badge_id'] = $employee->badge_id;
            } else {
                // Log jika tidak ditemukan
                Log::warning('Employee not found', ['ghrs_id' => $ghrsId]);
                $assetData['ghrs_id'] = $ghrsId; // Tetap simpan meski employee tidak ada
            }
        }
        
        if ($assignedToId) {
            $assetData['assigned_to'] = $assignedToId;
        }
        
        // Status
        $assetData['status'] = $this->determineStatus($data, $assignedToId);
        $assetData['last_status_date'] = $this->parseDate($data['last_status_date'] ?? null) ?? $now;
        
        // Serial numbers
        if (!empty($data['serial_number'])) {
            $cleaned = $this->cleanSerialNumber($data['serial_number']);
            $assetData['serial_number'] = $cleaned;
            $assetData['serial_clean'] = preg_replace('/[^A-Z0-9]/', '', strtoupper($cleaned));
        }
        
        if (!empty($data['service_tag'])) {
            $assetData['service_tag'] = $data['service_tag'];
        }
        
        // Dates
        if (!empty($data['assignment_date'])) {
            $assetData['assignment_date'] = $this->parseDate($data['assignment_date']);
        }
        
        if (!empty($data['delivery_date'])) {
            $assetData['delivery_date'] = $this->parseDate($data['delivery_date']);
        }
        
        // Purchase info
        if (!empty($data['pr_ref'])) {
            $assetData['pr_ref'] = substr($data['pr_ref'], 0, 50);
        }
        if (!empty($data['po_ref'])) {
            $assetData['po_ref'] = substr($data['po_ref'], 0, 50);
        }
        
        // Item details
        if (!empty($data['item_name'])) {
            $assetData['item_name'] = substr($data['item_name'], 0, 100);
        }
        if (!empty($data['brand'])) {
            $assetData['brand'] = substr($data['brand'], 0, 50);
        }
        if (!empty($data['type'])) {
            $assetData['type'] = substr($data['type'], 0, 100);
        }
        
        // ✅ NEW: Excel columns
        if (!empty($data['code'])) {
            $assetData['code'] = substr($data['code'], 0, 50);
        }
        if (!empty($data['email_address'])) {
            $assetData['email_address'] = substr($data['email_address'], 0, 100);
        }
        if (!empty($data['cost_center'])) {
            $assetData['cost_center'] = substr($data['cost_center'], 0, 50);
        }
        if (!empty($data['location_site'])) {
            $assetData['location_site'] = substr($data['location_site'], 0, 150);
        }
        if (!empty($data['assignment_status'])) {
            $assetData['assignment_status'] = substr($data['assignment_status'], 0, 50);
        }
        if (!empty($data['soc_compliant'])) {
            $assetData['soc_compliant'] = substr($data['soc_compliant'], 0, 10);
        }
        if (!empty($data['ref'])) {
            $assetData['ref'] = substr($data['ref'], 0, 50);
        }
        if (!empty($data['dept_cost_center'])) {
            $assetData['dept_cost_center'] = substr($data['dept_cost_center'], 0, 150);
        }
        if (!empty($data['memory'])) {
            $assetData['memory'] = substr($data['memory'], 0, 50);
        }
        
        // User info
        if (!empty($data['user_id'])) {
            $assetData['username'] = substr($data['user_id'], 0, 100);
        }
        
        // Location
        if (!empty($data['location'])) {
            $assetData['location'] = substr($data['location'], 0, 150);
        }
        if (!empty($data['dept_project']) || !empty($data['department'])) {
            $assetData['dept_project'] = substr($data['dept_project'] ?? $data['department'], 0, 100);
        }
        
        // Device info
        if (!empty($data['device_model']) || !empty($data['device_name'])) {
            $assetData['device_name'] = substr($data['device_model'] ?? $data['device_name'], 0, 100);
        }
        
        // Remarks
        if (!empty($data['remarks'])) {
            $assetData['remarks'] = $data['remarks'];
        }
        
        // Specifications (combine memory if available)
        $specs = [];
        if (!empty($data['device_model'])) {
            $specs[] = "Model: " . $data['device_model'];
        }
        if (!empty($data['memory'])) {
            $specs[] = "Memory: " . $data['memory'];
        }
        if (!empty($specs)) {
            $assetData['specifications'] = implode(' | ', $specs);
        }
        
        return $assetData;
    }

    private function isCompletelyEmpty(array $data): bool
    {
        $importantFields = ['asset_tag', 'serial_number', 'item_name', 'type', 'device_model', 'employee_id'];
        
        foreach ($importantFields as $field) {
            if (!empty($data[$field])) {
                return false;
            }
        }
        
        return true;
    }

    private function generateUniqueAssetTag(array $data, int $index): string
    {
        if (!empty($data['serial_number'])) {
            $serial = preg_replace('/[^A-Z0-9]/', '', strtoupper($data['serial_number']));
            if (strlen($serial) >= 6) {
                $tag = 'AST-' . substr($serial, -8);
                if (!isset($this->existingAssetsCache[$tag]) && !in_array($tag, self::$processedTags)) {
                    self::$processedTags[] = $tag;
                    return $tag;
                }
            }
        }
        
        if (!empty($data['item_name']) || !empty($data['type'])) {
            $prefix = $this->extractPrefix($data['item_name'] ?? $data['type']);
            do {
                $tag = $prefix . str_pad(self::$tagCounter, 4, '0', STR_PAD_LEFT);
                self::$tagCounter++;
            } while (isset($this->existingAssetsCache[$tag]) || in_array($tag, self::$processedTags));
            
            self::$processedTags[] = $tag;
            return $tag;
        }
        
        do {
            $tag = 'AST-' . date('ymd') . '-' . str_pad(self::$tagCounter, 4, '0', STR_PAD_LEFT);
            self::$tagCounter++;
        } while (isset($this->existingAssetsCache[$tag]) || in_array($tag, self::$processedTags));
        
        self::$processedTags[] = $tag;
        return $tag;
    }

    private function extractPrefix(string $itemName): string
    {
        $itemUpper = strtoupper($itemName);
        
        $prefixes = [
            'LAP-' => ['LAPTOP', 'NOTEBOOK'],
            'DES-' => ['DESKTOP', 'PC'],
            'MON-' => ['MONITOR'],
            'MOU-' => ['MOUSE'],
            'KEY-' => ['KEYBOARD'],
            'HDS-' => ['HEADSET'],
            'EAR-' => ['EARPHONE'],
            'DOC-' => ['DOCKING'],
            'SSD-' => ['SSD'],
            'VGA-' => ['VGA'],
            'CAB-' => ['CABLE'],
        ];
        
        foreach ($prefixes as $prefix => $keywords) {
            foreach ($keywords as $keyword) {
                if (Str::contains($itemUpper, $keyword)) {
                    return $prefix;
                }
            }
        }
        
        return 'AST-';
    }

    private function cleanSerialNumber(string $serial): string
    {
        if (preg_match('/S\/N:\s*([A-Z0-9]+)/i', $serial, $matches)) {
            return strtoupper($matches[1]);
        }
        
        $serial = preg_replace('/^(SN|S\/N|SERIAL)[:.\s]*/i', '', $serial);
        return trim($serial);
    }

    private function determineStatus(array $data, ?int $assignedToId): string
    {
        if (!empty($data['status'])) {
            return $this->validateStatus($data['status']);
        }
        
        return $assignedToId !== null ? 'In Use' : 'In Stock';
    }

    private function validateStatus(string $status): string
    {
        $statusMap = [
            'in stock' => 'In Stock',
            'in use' => 'In Use',
            'broken' => 'Broken',
            'retired' => 'Retired',
            'taken' => 'Taken',
        ];
        
        return $statusMap[strtolower(trim($status))] ?? 'In Stock';
    }

    private function detectAssetType(array $data): ?int
    {
        $searchText = strtolower(trim(
            ($data['asset_type_name'] ?? '') . ' ' . 
            ($data['item_name'] ?? '') . ' ' . 
            ($data['brand'] ?? '') . ' ' .
            ($data['type'] ?? '') . ' ' .
            ($data['device_model'] ?? '')
        ));
        
        if (empty($searchText)) {
            return null;
        }
        
        foreach ($this->assetTypesCache as $name => $type) {
            if (Str::contains($searchText, $name)) {
                return $type['id'];
            }
        }
        
        foreach ($this->assetTypeKeywords as $typeName => $keywords) {
            foreach ($keywords as $keyword) {
                if (Str::contains($searchText, $keyword)) {
                    $typeNameLower = strtolower($typeName);
                    if (isset($this->assetTypesCache[$typeNameLower])) {
                        return $this->assetTypesCache[$typeNameLower]['id'];
                    }
                    return $this->createAssetType($typeName);
                }
            }
        }
        
        if (!empty($data['asset_type_name'])) {
            return $this->createAssetType(ucwords(strtolower(trim($data['asset_type_name']))));
        }
        
        return null;
    }

    private function createAssetType(string $typeName): int
    {
        $typeNameLower = strtolower($typeName);
        
        if (isset($this->assetTypesCache[$typeNameLower])) {
            return $this->assetTypesCache[$typeNameLower]['id'];
        }
        
        $assetType = AssetType::create([
            'name' => $typeName,
            'category' => $this->guessCategory($typeName),
            'description' => 'Auto-created during import'
        ]);
        
        $this->assetTypesCache[$typeNameLower] = [
            'id' => $assetType->id,
            'name' => $assetType->name
        ];
        
        $this->assetTypesCreatedCount++;
        
        return $assetType->id;
    }

    private function guessCategory(string $typeName): string
    {
        $typeLower = strtolower($typeName);
        
        if (Str::contains($typeLower, ['laptop', 'desktop', 'monitor', 'keyboard', 'mouse', 'printer'])) {
            return 'Hardware';
        }
        
        return 'Peripheral';
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

    private function normalizeRow(array $row): array
    {
        $normalized = [];
        foreach ($row as $key => $value) {
            if ($key === null) continue;
            
            $normalizedKey = strtolower(trim($key));
            $normalizedKey = preg_replace('/[^a-z0-9_]/', '', str_replace([' ', '-', '.', '/', '#'], '_', $normalizedKey));
            
            $normalized[$normalizedKey] = $value !== null && is_string($value) ? trim($value) : $value;
        }
        
        return $normalized;
    }

    private function mapColumns(array $normalizedRow): array
    {
        $mapped = [];
        
        foreach ($this->columnMapping as $dbField => $excelVariants) {
            foreach ($excelVariants as $variant) {
                $normalizedVariant = strtolower(preg_replace('/[^a-z0-9_]/', '', str_replace([' ', '-', '.', '/', '#'], '_', $variant)));
                
                if (isset($normalizedRow[$normalizedVariant]) && $normalizedRow[$normalizedVariant] !== '') {
                    $mapped[$dbField] = $normalizedRow[$normalizedVariant];
                    break;
                }
            }
        }
        
        return $mapped;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function getCreatedCount(): int { return $this->createdCount; }
    public function getUpdatedCount(): int { return $this->updatedCount; }
    public function getSkippedCount(): int { return $this->skippedCount; }
    public function getAssetTypesCreatedCount(): int { return $this->assetTypesCreatedCount; }
    public function getSkipReasons(): array { return $this->skipReasons; } // ✅ NEW
}