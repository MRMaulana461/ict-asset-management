<?php

namespace App\Http\Controllers;

use App\Imports\EmployeesImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeImportController extends Controller
{
    /**
     * Show import form
     */
    public function showForm()
    {
        return view('employees.import');
    }

    /**
     * Process Excel import
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240' // Max 10MB
        ]);

        try {
            $import = new EmployeesImport;
            Excel::import($import, $request->file('file'));

            // Get statistics
            $created = $import->getCreatedCount();
            $updated = $import->getUpdatedCount();
            $skipped = $import->getSkippedCount();

            // Build success message
            $details = [];
            if ($created > 0) $details[] = "{$created} employees created";
            if ($updated > 0) $details[] = "{$updated} employees updated";
            if ($skipped > 0) $details[] = "{$skipped} rows skipped";
            
            if (empty($details)) {
                return redirect()->route('employees.index')
                    ->with('warning', 'No data was imported. Please check your Excel file format and content.');
            }

            $message = "✅ Employee import completed successfully! " . implode(', ', $details);

            return redirect()->route('employees.index')->with('success', $message);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errorMessages = [];
            
            foreach ($failures as $failure) {
                $errorMessages[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
            }
            
            return back()->with('error', 'Import validation failed: ' . implode(' | ', $errorMessages));
            
        } catch (\Exception $e) {
            \Log::error('Employee import exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}