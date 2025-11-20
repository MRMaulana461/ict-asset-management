<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        echo "\n🚀 Starting migration: Complete Asset & Employee Structure\n";
        
        // =====================================================
        // STEP 1: Rename employee_id → ghrs_id
        // =====================================================
        echo "📝 Step 1: Renaming employee_id to ghrs_id...\n";
        
        if (!Schema::hasColumn('employees', 'ghrs_id') && Schema::hasColumn('employees', 'employee_id')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->renameColumn('employee_id', 'ghrs_id');
            });
            echo "✅ Renamed employee_id → ghrs_id\n";
        } else {
            echo "⏭️  Skipped (ghrs_id already exists or employee_id not found)\n";
        }

        // Update comment
        try {
            DB::statement("ALTER TABLE employees MODIFY ghrs_id VARCHAR(50) NOT NULL COMMENT 'GHRS ID - Primary system identifier'");
        } catch (\Exception $e) {
            echo "⚠️  Could not update comment: " . $e->getMessage() . "\n";
        }

        // =====================================================
        // STEP 2: Add new identifier columns to employees
        // =====================================================
        echo "\n📝 Step 2: Adding identifier columns to employees...\n";
        
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'comp_empl_id')) {
                $table->string('comp_empl_id', 50)->nullable()->after('ghrs_id')
                    ->comment('Company Employee ID');
                echo "✅ Added comp_empl_id\n";
            }
            
            if (!Schema::hasColumn('employees', 'empl_rcd')) {
                $table->integer('empl_rcd')->nullable()->after('comp_empl_id')
                    ->comment('Employee Record Number');
                echo "✅ Added empl_rcd\n";
            }
            
            if (!Schema::hasColumn('employees', 'badge_id')) {
                $table->string('badge_id', 50)->nullable()->after('empl_rcd')
                    ->comment('Badge ID');
                echo "✅ Added badge_id\n";
            }
        });

        // Make user_id nullable
        if (Schema::hasColumn('employees', 'user_id')) {
            DB::statement('ALTER TABLE employees MODIFY user_id VARCHAR(50) NULL COMMENT "SamAccountName"');
            echo "✅ Updated user_id to nullable\n";
        }

        // =====================================================
        // STEP 3: Add name columns (first_name, last_name)
        // =====================================================
        echo "\n📝 Step 3: Adding name columns to employees...\n";
        
        Schema::table('employees', function (Blueprint $table) {
            // Add first_name and last_name before 'name' column
            if (!Schema::hasColumn('employees', 'first_name')) {
                $table->string('first_name', 100)->nullable()->after('badge_id')
                    ->comment('First Name');
                echo "✅ Added first_name\n";
            }
            
            if (!Schema::hasColumn('employees', 'last_name')) {
                $table->string('last_name', 100)->nullable()->after('first_name')
                    ->comment('Last Name');
                echo "✅ Added last_name\n";
            }
        });

        // Make 'name' nullable (in case it's derived from first_name + last_name)
        if (Schema::hasColumn('employees', 'name')) {
            try {
                DB::statement('ALTER TABLE employees MODIFY name VARCHAR(255) NULL COMMENT "Full Name"');
                echo "✅ Updated name to nullable\n";
            } catch (\Exception $e) {
                echo "⚠️  Could not update name: " . $e->getMessage() . "\n";
            }
        }

        // =====================================================
        // STEP 4: Add organizational columns to employees
        // =====================================================
        echo "\n📝 Step 4: Adding organizational columns to employees...\n";
        
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'company')) {
                $table->string('company', 150)->nullable()->after('email')
                    ->comment('Company name');
                echo "✅ Added company\n";
            }
            
            if (!Schema::hasColumn('employees', 'org_context')) {
                $table->string('org_context', 150)->nullable()->after('company')
                    ->comment('Org. Context');
                echo "✅ Added org_context\n";
            }
            
            if (!Schema::hasColumn('employees', 'dept_id')) {
                $table->string('dept_id', 20)->nullable()->after('department')
                    ->comment('DeptID');
                echo "✅ Added dept_id\n";
            }
            
            if (!Schema::hasColumn('employees', 'org_relation')) {
                $table->string('org_relation', 100)->nullable()->after('dept_id');
                echo "✅ Added org_relation\n";
            }
            
            if (!Schema::hasColumn('employees', 'agency')) {
                $table->string('agency', 100)->nullable()->after('org_relation');
                echo "✅ Added agency\n";
            }
            
            if (!Schema::hasColumn('employees', 'boc')) {
                $table->string('boc', 50)->nullable()->after('agency');
                echo "✅ Added boc\n";
            }
            
            if (!Schema::hasColumn('employees', 'cost_center_descr')) {
                $table->string('cost_center_descr', 200)->nullable()->after('cost_center')
                    ->comment('Cost Center Description');
                echo "✅ Added cost_center_descr\n";
            }
            
            if (!Schema::hasColumn('employees', 'role_company')) {
                $table->string('role_company', 100)->nullable()->after('cost_center_descr');
                echo "✅ Added role_company\n";
            }
            
            if (!Schema::hasColumn('employees', 'employee_class')) {
                $table->enum('employee_class', ['W', 'B'])->nullable()->after('role_company')
                    ->comment('W=White Collar, B=Blue Collar');
                echo "✅ Added employee_class\n";
            }
            
            if (!Schema::hasColumn('employees', 'tipo_terzi')) {
                $table->string('tipo_terzi', 50)->nullable()->after('employee_class');
                echo "✅ Added tipo_terzi\n";
            }
            
            if (!Schema::hasColumn('employees', 'contractual_position')) {
                $table->string('contractual_position', 150)->nullable()->after('tipo_terzi')
                    ->comment('Job title/position');
                echo "✅ Added contractual_position\n";
            }
        });

        // Make existing columns nullable
        if (Schema::hasColumn('employees', 'cost_center')) {
            DB::statement('ALTER TABLE employees MODIFY cost_center VARCHAR(50) NULL');
            echo "✅ Updated cost_center to nullable\n";
        }
        
        if (Schema::hasColumn('employees', 'department')) {
            DB::statement('ALTER TABLE employees MODIFY department VARCHAR(100) NULL');
            echo "✅ Updated department to nullable\n";
        }
        
        if (Schema::hasColumn('employees', 'email')) {
            DB::statement('ALTER TABLE employees MODIFY email VARCHAR(100) NULL');
            echo "✅ Updated email to nullable\n";
        }

        // =====================================================
        // STEP 5: Add ALL required columns to assets table
        // =====================================================
        echo "\n📝 Step 5: Adding ALL required columns to assets...\n";
        
        Schema::table('assets', function (Blueprint $table) {
            // PR/PO References
            if (!Schema::hasColumn('assets', 'pr_ref')) {
                $table->string('pr_ref', 50)->nullable()->comment('Purchase Request Reference');
                echo "✅ Added pr_ref\n";
            }
            if (!Schema::hasColumn('assets', 'po_ref')) {
                $table->string('po_ref', 50)->nullable()->comment('Purchase Order Reference');
                echo "✅ Added po_ref\n";
            }
            
            // Item Details
            if (!Schema::hasColumn('assets', 'item_name')) {
                $table->string('item_name', 100)->nullable()->comment('Item Name/Description');
                echo "✅ Added item_name\n";
            }
            if (!Schema::hasColumn('assets', 'brand')) {
                $table->string('brand', 50)->nullable()->comment('Brand/Manufacturer');
                echo "✅ Added brand\n";
            }
            if (!Schema::hasColumn('assets', 'type')) {
                $table->string('type', 100)->nullable()->comment('Item Type/Category');
                echo "✅ Added type\n";
            }
            
            // Serial Numbers
            if (!Schema::hasColumn('assets', 'serial_number')) {
                $table->string('serial_number', 100)->nullable()->comment('Serial Number');
                echo "✅ Added serial_number\n";
            }
            if (!Schema::hasColumn('assets', 'serial_clean')) {
                $table->string('serial_clean', 100)->nullable()->comment('Cleaned Serial Number (for matching)');
                echo "✅ Added serial_clean\n";
            }
            if (!Schema::hasColumn('assets', 'service_tag')) {
                $table->string('service_tag', 100)->nullable()->comment('Service Tag');
                echo "✅ Added service_tag\n";
            }
            
            // Employee Assignment
            if (!Schema::hasColumn('assets', 'ghrs_id')) {
                $table->string('ghrs_id', 50)->nullable()->comment('Assigned Employee GHRS ID');
                echo "✅ Added ghrs_id\n";
            }
            if (!Schema::hasColumn('assets', 'badge_id')) {
                $table->string('badge_id', 50)->nullable()->comment('Assigned Employee Badge ID');
                echo "✅ Added badge_id\n";
            }
            if (!Schema::hasColumn('assets', 'assignment_date')) {
                $table->date('assignment_date')->nullable()->comment('Date assigned to employee');
                echo "✅ Added assignment_date\n";
            }
            
            // Location & Department
            if (!Schema::hasColumn('assets', 'location')) {
                $table->string('location', 150)->nullable()->comment('Physical Location');
                echo "✅ Added location\n";
            }
            if (!Schema::hasColumn('assets', 'dept_project')) {
                $table->string('dept_project', 100)->nullable()->comment('Department/Project');
                echo "✅ Added dept_project\n";
            }
            
            // Additional Info
            if (!Schema::hasColumn('assets', 'remarks')) {
                $table->text('remarks')->nullable()->comment('Remarks/Notes');
                echo "✅ Added remarks\n";
            }
            if (!Schema::hasColumn('assets', 'delivery_date')) {
                $table->date('delivery_date')->nullable()->comment('Delivery Date');
                echo "✅ Added delivery_date\n";
            }
            
            // Status (if not exists)
            if (!Schema::hasColumn('assets', 'status')) {
                $table->string('status', 50)->default('In Stock')->comment('Asset Status');
                echo "✅ Added status\n";
            }
            
            // Computer-specific fields
            if (!Schema::hasColumn('assets', 'username')) {
                $table->string('username', 100)->nullable()->comment('Computer Username');
                echo "✅ Added username\n";
            }
            if (!Schema::hasColumn('assets', 'device_name')) {
                $table->string('device_name', 100)->nullable()->comment('Computer Device Name');
                echo "✅ Added device_name\n";
            }
            
            // Technical Specifications (if not exists)
            if (!Schema::hasColumn('assets', 'specifications')) {
                $table->text('specifications')->nullable()->comment('Technical Specifications');
                echo "✅ Added specifications\n";
            }
        });

        // =====================================================
        // STEP 6: Ensure asset_types has required columns
        // =====================================================
        echo "\n📝 Step 6: Checking asset_types table...\n";
        
        Schema::table('asset_types', function (Blueprint $table) {
            // Ensure basic columns exist
            if (!Schema::hasColumn('asset_types', 'name')) {
                $table->string('name', 100)->after('id')->comment('Asset Type Name');
                echo "✅ Added name to asset_types\n";
            }
            if (!Schema::hasColumn('asset_types', 'category')) {
                $table->enum('category', ['Hardware', 'Peripheral'])->default('Peripheral')->after('name');
                echo "✅ Added category to asset_types\n";
            }
            if (!Schema::hasColumn('asset_types', 'description')) {
                $table->text('description')->nullable()->after('category');
                echo "✅ Added description to asset_types\n";
            }
        });

        // =====================================================
        // STEP 7: Add indexes for performance
        // =====================================================
        echo "\n📝 Step 7: Adding indexes...\n";
        
        // Employee indexes
        $this->addIndexSafely('employees', 'ghrs_id', 'employees_ghrs_id_index');
        $this->addIndexSafely('employees', 'badge_id', 'employees_badge_id_index');
        $this->addIndexSafely('employees', 'comp_empl_id', 'employees_comp_empl_id_index');
        $this->addIndexSafely('employees', 'user_id', 'employees_user_id_index');
        $this->addIndexSafely('employees', 'company', 'employees_company_index');
        $this->addIndexSafely('employees', 'dept_id', 'employees_dept_id_index');
        $this->addIndexSafely('employees', 'first_name', 'employees_first_name_index');
        $this->addIndexSafely('employees', 'last_name', 'employees_last_name_index');
        
        // Asset indexes
        $this->addIndexSafely('assets', 'ghrs_id', 'assets_ghrs_id_index');
        $this->addIndexSafely('assets', 'badge_id', 'assets_badge_id_index');
        $this->addIndexSafely('assets', 'serial_number', 'assets_serial_number_index');
        $this->addIndexSafely('assets', 'serial_clean', 'assets_serial_clean_index');
        $this->addIndexSafely('assets', 'service_tag', 'assets_service_tag_index');
        $this->addIndexSafely('assets', 'username', 'assets_username_index');
        $this->addIndexSafely('assets', 'device_name', 'assets_device_name_index');
        $this->addIndexSafely('assets', 'location', 'assets_location_index');
        $this->addIndexSafely('assets', 'dept_project', 'assets_dept_project_index');
        $this->addIndexSafely('assets', 'status', 'assets_status_index');
        $this->addIndexSafely('assets', 'pr_ref', 'assets_pr_ref_index');
        $this->addIndexSafely('assets', 'po_ref', 'assets_po_ref_index');

        echo "\n✅ Migration completed successfully!\n\n";
    }

    /**
     * Add index safely
     */
    private function addIndexSafely(string $table, string $column, string $indexName): void
    {
        try {
            if (!$this->indexExists($table, $indexName)) {
                Schema::table($table, function (Blueprint $table) use ($column, $indexName) {
                    $table->index($column, $indexName);
                });
                echo "✅ Added index: {$indexName}\n";
            } else {
                echo "⏭️  Index already exists: {$indexName}\n";
            }
        } catch (\Exception $e) {
            echo "⚠️  Could not add index {$indexName}: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Check if index exists
     */
    private function indexExists(string $table, string $index): bool
    {
        try {
            $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$index]);
            return count($indexes) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        echo "\n🔄 Rolling back migration...\n";

        // Rename ghrs_id back to employee_id
        if (Schema::hasColumn('employees', 'ghrs_id') && !Schema::hasColumn('employees', 'employee_id')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->renameColumn('ghrs_id', 'employee_id');
            });
            echo "✅ Renamed ghrs_id → employee_id\n";
        }

        // Drop indexes
        echo "📝 Dropping indexes...\n";
        $this->dropIndexSafely('employees', 'employees_ghrs_id_index');
        $this->dropIndexSafely('employees', 'employees_badge_id_index');
        $this->dropIndexSafely('employees', 'employees_comp_empl_id_index');
        $this->dropIndexSafely('employees', 'employees_company_index');
        $this->dropIndexSafely('employees', 'employees_dept_id_index');
        $this->dropIndexSafely('employees', 'employees_first_name_index');
        $this->dropIndexSafely('employees', 'employees_last_name_index');
        
        $this->dropIndexSafely('assets', 'assets_ghrs_id_index');
        $this->dropIndexSafely('assets', 'assets_badge_id_index');
        $this->dropIndexSafely('assets', 'assets_serial_number_index');
        $this->dropIndexSafely('assets', 'assets_serial_clean_index');
        $this->dropIndexSafely('assets', 'assets_service_tag_index');
        $this->dropIndexSafely('assets', 'assets_username_index');
        $this->dropIndexSafely('assets', 'assets_device_name_index');
        $this->dropIndexSafely('assets', 'assets_location_index');
        $this->dropIndexSafely('assets', 'assets_dept_project_index');
        $this->dropIndexSafely('assets', 'assets_status_index');
        $this->dropIndexSafely('assets', 'assets_pr_ref_index');
        $this->dropIndexSafely('assets', 'assets_po_ref_index');

        // Drop columns from employees
        echo "📝 Dropping columns from employees...\n";
        Schema::table('employees', function (Blueprint $table) {
            $columns = [
                'comp_empl_id', 'empl_rcd', 'badge_id', 
                'first_name', 'last_name',
                'company', 'org_context', 'dept_id', 'org_relation', 
                'agency', 'boc', 'cost_center_descr', 'role_company', 
                'employee_class', 'tipo_terzi', 'contractual_position'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('employees', $column)) {
                    $table->dropColumn($column);
                    echo "✅ Dropped: {$column}\n";
                }
            }
        });

        // Drop columns from assets
        echo "📝 Dropping columns from assets...\n";
        Schema::table('assets', function (Blueprint $table) {
            $columns = [
                'pr_ref', 'po_ref', 'item_name', 'brand', 'type',
                'serial_number', 'serial_clean', 'specifications',
                'location', 'dept_project', 'delivery_date', 'remarks',
                'service_tag', 'username', 'device_name', 
                'ghrs_id', 'badge_id', 'assignment_date'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('assets', $column)) {
                    $table->dropColumn($column);
                    echo "✅ Dropped: {$column}\n";
                }
            }
        });

        echo "\n✅ Rollback completed!\n\n";
    }

    /**
     * Drop index safely
     */
    private function dropIndexSafely(string $table, string $indexName): void
    {
        try {
            Schema::table($table, function (Blueprint $table) use ($indexName) {
                $table->dropIndex($indexName);
            });
            echo "✅ Dropped index: {$indexName}\n";
        } catch (\Exception $e) {
            echo "⏭️  Index not found: {$indexName}\n";
        }
    }
};