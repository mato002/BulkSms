<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Support\PhoneNumber;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContactController extends Controller
{
    public function index(Request $request): View
    {
        $clientId = session('client_id', 1);
        $query = DB::table('contacts')
            ->where('client_id', $clientId);

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact', 'like', "%{$search}%");
            });
        }

        // Apply department filter
        if ($request->filled('department')) {
            $query->where('department', 'like', "%{$request->get('department')}%");
        }

        $contacts = $query->orderBy('id', 'desc')->paginate(50);

        $availableTags = DB::table('tags')
            ->where('client_id', $clientId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('contacts.index', [
            'contacts' => $contacts,
            'availableTags' => $availableTags,
        ]);
    }

    public function create(): View
    {
        return view('contacts.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
        ]);

        $clientId = session('client_id', 1);

        // Format phone number with country code
        $formattedContact = PhoneNumber::e164($validated['contact']);

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

    public function show(string $id): View
    {
        $clientId = session('client_id', 1);
        $contact = DB::table('contacts')
            ->where('client_id', $clientId)
            ->where('id', $id)
            ->first();

        if (!$contact) {
            abort(404);
        }

        // Get contact tags
        $contactTags = DB::table('contact_tag')
            ->join('tags', 'contact_tag.tag_id', '=', 'tags.id')
            ->where('contact_tag.contact_id', $contact->id)
            ->where('tags.client_id', $clientId)
            ->select('tags.id', 'tags.name', 'tags.color')
            ->get();

        // Get recent messages
        $recentMessages = DB::table('messages')
            ->where('client_id', $clientId)
            ->where(function($q) use ($contact) {
                $q->where('recipient', $contact->contact)
                  ->orWhere('sender', $contact->contact);
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('contacts.show', compact('contact', 'contactTags', 'recentMessages'));
    }

    public function edit(string $id): View
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

    public function update(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
        ]);

        $clientId = session('client_id', 1);

        // Format phone number with country code
        $formattedContact = PhoneNumber::e164($validated['contact']);

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

    public function destroy(string $id): RedirectResponse
    {
        $clientId = session('client_id', 1);

        DB::table('contacts')
            ->where('client_id', $clientId)
            ->where('id', $id)
            ->delete();

        return redirect()->route('contacts.index')->with('success', 'Contact deleted successfully');
    }

    public function import(Request $request): RedirectResponse
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
                $formattedContact = PhoneNumber::e164($row[1] ?? '');
                
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

    /**
     * Handle bulk actions (delete, tag) on selected contacts.
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $clientId = session('client_id', 1);

        $validated = $request->validate([
            'selected_contacts' => 'required|array|min:1',
            'selected_contacts.*' => 'integer',
            'bulk_action' => 'required|in:delete,tag',
            'tag_id' => 'nullable|integer',
        ]);

        $contactIds = DB::table('contacts')
            ->where('client_id', $clientId)
            ->whereIn('id', $validated['selected_contacts'])
            ->pluck('id')
            ->all();

        if (empty($contactIds)) {
            return redirect()
                ->route('contacts.index')
                ->with('error', 'None of the selected contacts are available for the chosen action.');
        }

        if ($validated['bulk_action'] === 'delete') {
            DB::table('contacts')
                ->where('client_id', $clientId)
                ->whereIn('id', $contactIds)
                ->delete();

            // Clean up pivot table entries
            DB::table('contact_tag')->whereIn('contact_id', $contactIds)->delete();

            $message = count($contactIds) . ' contact(s) deleted successfully.';

            return redirect()
                ->route('contacts.index')
                ->with('success', $message);
        }

        // Bulk tag application
        if (!$request->filled('tag_id')) {
            return redirect()
                ->route('contacts.index')
                ->with('error', 'Select a tag before applying it to contacts.');
        }

        $tag = DB::table('tags')
            ->where('client_id', $clientId)
            ->where('id', $request->integer('tag_id'))
            ->first();

        if (!$tag) {
            return redirect()
                ->route('contacts.index')
                ->with('error', 'The selected tag is no longer available.');
        }

        $timestamp = now();
        $rows = collect($contactIds)->map(function ($contactId) use ($request, $timestamp) {
            return [
                'contact_id' => $contactId,
                'tag_id' => $request->integer('tag_id'),
                'tagged_at' => $timestamp,
            ];
        })->toArray();

        DB::table('contact_tag')->upsert(
            $rows,
            ['contact_id', 'tag_id'],
            ['tagged_at']
        );

        return redirect()
            ->route('contacts.index')
            ->with('success', 'Tag "' . $tag->name . '" applied to ' . count($contactIds) . ' contact(s).');
    }
}
