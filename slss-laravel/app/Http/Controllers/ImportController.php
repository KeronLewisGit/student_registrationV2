<?php

namespace App\Http\Controllers;

use App\Services\CsvImportService;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function __construct(
        protected CsvImportService $importService
    ) {
        $this->middleware('can:import-students');
    }

    public function index()
    {
        return view('students.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240'
        ]);

        try {
            $result = $this->importService->import($request->file('csv_file'));

            return back()->with('success',
                "Import completed successfully. Imported: {$result['imported']}, Skipped: {$result['skipped']}"
            );

        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
