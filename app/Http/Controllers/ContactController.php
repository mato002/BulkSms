<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Contact;

class ContactController extends Controller
{
    /**
     * Format phone number with country code
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // If already has country code, return as is
        if (str_starts_with($phone, '+')) {
            return $phone;
        }
        
        // If starts with 0, remove it and add Kenya country code
        if (str_starts_with($phone, '0')) {
            return '+254' . substr($phone, 1);
        }
        
        // If starts with 254, add +
        if (str_starts_with($phone, '254')) {
            return '+' . $phone;
        }
        
        // If 9 digits (Kenya mobile without leading 0), add +254
        if (strlen($phone) === 9) {
            return '+254' . $phone;
        }
        
        // Default: assume it needs Kenya country code
        return '+254' . $phone;
    }

    public function index()
    {
        $clientId = session('client_id', 1);
        $contacts = DB::table('contacts')
            ->where('client_id', $clientId)
            ->orderBy('id', 'desc')
            ->paginate(50);

        return view('contacts.index', compact('contacts'));
    }

    public function create()
    {
        return view('contacts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
        ]);

        $clientId = session('client_id', 1);

        // Format phone number with country code
        $formattedContact = $this->formatPhoneNumber($validated['contact']);

        DB::table('contacts')->insert([
            'client_id' => $clientId,
            'name' => $validated['name'],
            'contact' => $formattedContact,
            'department' => $validated['department'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('contacts.index')->with('success', 'Contact created successfully');
    }

    public function show(string $id)
    {
        $clientId = session('client_id', 1);
        $contact = DB::table('contacts')
            ->where('client_id', $clientId)
            ->where('id', $id)
            ->first();

        if (!$contact) {
            abort(404);
        }

        return view('contacts.show', compact('contact'));
    }

    public function edit(string $id)
    {
        $clientId = session('client_id', 1);
        $contact = DB::table('contacts')
            ->where('client_id', $clientId)
            ->where('id', $id)
            ->first();

        if (!$contact) {
            abort(404);
        }

        return view('contacts.edit', compact('contact'));
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
        ]);

        $clientId = session('client_id', 1);

        // Format phone number with country code
        $formattedContact = $this->formatPhoneNumber($validated['contact']);

        DB::table('contacts')
            ->where('client_id', $clientId)
            ->where('id', $id)
            ->update([
                'name' => $validated['name'],
                'contact' => $formattedContact,
                'department' => $validated['department'] ?? null,
                'updated_at' => now(),
            ]);

        return redirect()->route('contacts.index')->with('success', 'Contact updated successfully');
    }

    public function destroy(string $id)
    {
        $clientId = session('client_id', 1);

        DB::table('contacts')
            ->where('client_id', $clientId)
            ->where('id', $id)
            ->delete();

        return redirect()->route('contacts.index')->with('success', 'Contact deleted successfully');
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $clientId = session('client_id', 1);
        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        
        $header = fgetcsv($handle);
        $imported = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) >= 2) {
                // Format phone number with country code
                $formattedContact = $this->formatPhoneNumber($row[1] ?? '');
                
                DB::table('contacts')->insert([
                    'client_id' => $clientId,
                    'name' => $row[0] ?? 'Unknown',
                    'contact' => $formattedContact,
                    'department' => $row[2] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $imported++;
            }
        }

        fclose($handle);

        return redirect()->route('contacts.index')->with('success', "Imported {$imported} contacts successfully");
    }
}
