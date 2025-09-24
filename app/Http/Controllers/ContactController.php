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
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240'
        ]);

        $file = $request->file('file');
        $path = $file->store('imports');

        // Read CSV headers
        $csv = Reader::createFromPath($file->path(), 'r');
        $csv->setHeaderOffset(0);
        $headers = $csv->getHeader();

        // Reset import summary
        Cache::put('import_summary', [
            'totalRows' => 0,
            'importedRows' => 0,
            'duplicateRows' => 0,
            'invalidRows' => 0
        ]);

        // Dispatch job
        ProcessCsvImport::dispatch($path, $headers);

        return back()->with('message', 'CSV import started. You will be notified when it completes.');
    }
}
