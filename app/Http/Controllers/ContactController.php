<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessCsvImport;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use League\Csv\Reader;

class ContactController extends Controller
{
    public function index()
    {
        $contacts = Contact::latest()->paginate(10);
        $importSummary = Cache::get('import_summary', [
            'totalRows' => 0,
            'importedRows' => 0,
            'duplicateRows' => 0,
            'invalidRows' => 0
        ]);

        return Inertia::render('Contacts/Index', [
            'contacts' => $contacts,
            'importSummary' => $importSummary
        ]);
    }

    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:csv,txt|max:10240'
            ]);

            $file = $request->file('file');
            $path = $file->store('imports');

            $csv = Reader::createFromPath($file->path(), 'r');
            $csv->setHeaderOffset(0);
            $headers = $csv->getHeader();

            Cache::put('import_summary', [
                'totalRows' => 0,
                'importedRows' => 0,
                'duplicateRows' => 0,
                'invalidRows' => 0
            ]);

            ProcessCsvImport::dispatch($path, $headers);

            return back()->with('success', 'CSV import started successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Error uploading file: ' . $e->getMessage());
        }
    }
}
