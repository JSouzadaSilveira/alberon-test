<?php

namespace App\Http\Services;

use App\Models\Contact;
use Illuminate\Support\Facades\Cache;
use League\Csv\Reader;
use App\Jobs\ProcessCsvImport;

class ContactService
{
    public static function getContacts()
    {
        $contacts = Contact::latest()->paginate(12);
        $importSummary = Cache::get('import_summary', [
            'totalRows' => 0,
            'importedRows' => 0,
            'duplicateRows' => 0,
            'invalidRows' => 0
        ]);

        return [
            'contacts' => $contacts,
            'importSummary' => $importSummary
        ];
    }

    public static function import($request)
    {

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
    }
}
