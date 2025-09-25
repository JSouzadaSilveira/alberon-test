<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Services\ContactService;
use Inertia\Inertia;

class ContactController extends Controller
{
    public function index()
    {
        try {
            $contacts = ContactService::getContacts();
            return Inertia::render('Contacts/Index', $contacts);
        } catch (\Exception $e) {
            return back()->with('error', 'Error getting contacts: ' . $e->getMessage());
        }
    }

    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:csv,txt|max:10240'
            ]);

            ContactService::import($request);

            return Inertia::render('Contacts/Index', [
                'message' => 'CSV import started successfully'
            ]);

        } catch (\Exception $e) {
            return Inertia::render('Contacts/Index', [
                'message' => 'Error uploading file: ' . $e->getMessage()
            ]);
        }
    }
}
