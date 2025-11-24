<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    /**
     * Get all contacts for the client
     */
    public function index(Request $request): JsonResponse
    {
        $client = $request->user();
        $perPage = $request->get('per_page', 50);
        $department = $request->get('department');
        $search = $request->get('search');

        $query = Contact::where('client_id', $client->id)
            ->with('client'); // Eager load client relationship

        if ($department) {
            $query->where('department', $department);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact', 'like', "%{$search}%");
            });
        }

        $contacts = $query->orderBy('name', 'asc')
                          ->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $contacts
        ]);
    }

    /**
     * Store a new contact
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'contact' => 'required|string|regex:/^254[0-9]{9}$/',
            'department' => 'nullable|string|max:255',
            'custom_fields' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $client = $request->user();
        
        $contact = Contact::create([
            'client_id' => $client->id,
            'name' => $request->name,
            'contact' => $request->contact,
            'department' => $request->department,
            'custom_fields' => $request->custom_fields
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Contact created successfully',
            'data' => $contact
        ], 201);
    }

    /**
     * Update a contact
     */
    public function update(Request $request, $id): JsonResponse
    {
        $client = $request->user();
        $contact = Contact::where('id', $id)
                         ->where('client_id', $client->id)
                         ->first();

        if (!$contact) {
            return response()->json([
                'status' => 'error',
                'message' => 'Contact not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'contact' => 'sometimes|string|regex:/^254[0-9]{9}$/',
            'department' => 'nullable|string|max:255',
            'custom_fields' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $contact->update($request->only(['name', 'contact', 'department', 'custom_fields']));

        return response()->json([
            'status' => 'success',
            'message' => 'Contact updated successfully',
            'data' => $contact
        ]);
    }

    /**
     * Delete a contact
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $client = $request->user();
        $contact = Contact::where('id', $id)
                         ->where('client_id', $client->id)
                         ->first();

        if (!$contact) {
            return response()->json([
                'status' => 'error',
                'message' => 'Contact not found'
            ], 404);
        }

        $contact->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Contact deleted successfully'
        ]);
    }

    /**
     * Bulk import contacts
     */
    public function bulkImport(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'contacts' => 'required|array|max:1000',
            'contacts.*.name' => 'required|string|max:255',
            'contacts.*.contact' => 'required|string|regex:/^254[0-9]{9}$/',
            'contacts.*.department' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $client = $request->user();
        $imported = 0;
        $errors = [];

        foreach ($request->contacts as $index => $contactData) {
            try {
                Contact::create([
                    'client_id' => $client->id,
                    'name' => $contactData['name'],
                    'contact' => $contactData['contact'],
                    'department' => $contactData['department'] ?? null
                ]);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row {$index}: " . $e->getMessage();
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => "Imported {$imported} contacts successfully",
            'data' => [
                'imported' => $imported,
                'errors' => $errors
            ]
        ]);
    }
}
