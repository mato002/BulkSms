<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagController extends Controller
{
    /**
     * Display a listing of tags
     */
    public function index()
    {
        $clientId = session('client_id', 1);
        
        $tags = Tag::where('client_id', $clientId)
            ->orderBy('name')
            ->get();

        return view('tags.index', compact('tags'));
    }

    /**
     * Store a newly created tag
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string|max:500',
        ]);

        $clientId = session('client_id', 1);

        Tag::create([
            'client_id' => $clientId,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'color' => $validated['color'] ?? $this->randomColor(),
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('tags.index')->with('success', 'Tag created successfully');
    }

    /**
     * Update the specified tag
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string|max:500',
        ]);

        $clientId = session('client_id', 1);
        
        $tag = Tag::where('client_id', $clientId)->findOrFail($id);

        $tag->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'color' => $validated['color'] ?? $tag->color,
            'description' => $validated['description'],
        ]);

        return redirect()->route('tags.index')->with('success', 'Tag updated successfully');
    }

    /**
     * Remove the specified tag
     */
    public function destroy($id)
    {
        $clientId = session('client_id', 1);
        
        $tag = Tag::where('client_id', $clientId)->findOrFail($id);
        $tag->delete();

        return redirect()->route('tags.index')->with('success', 'Tag deleted successfully');
    }

    /**
     * Get contacts by tag (AJAX)
     */
    public function getContacts($id)
    {
        $clientId = session('client_id', 1);
        
        $tag = Tag::where('client_id', $clientId)->findOrFail($id);
        $contacts = $tag->contacts()->get(['id', 'name', 'contact']);

        return response()->json([
            'success' => true,
            'tag' => $tag->name,
            'contacts' => $contacts,
        ]);
    }

    /**
     * Add tag to contact
     */
    public function addToContact(Request $request, $contactId)
    {
        $validated = $request->validate([
            'tag_id' => 'required|exists:tags,id',
        ]);

        $clientId = session('client_id', 1);
        
        $contact = Contact::where('client_id', $clientId)->findOrFail($contactId);
        $contact->addTag($validated['tag_id']);

        return response()->json([
            'success' => true,
            'message' => 'Tag added successfully',
        ]);
    }

    /**
     * Remove tag from contact
     */
    public function removeFromContact($contactId, $tagId)
    {
        $clientId = session('client_id', 1);
        
        $contact = Contact::where('client_id', $clientId)->findOrFail($contactId);
        $contact->removeTag($tagId);

        return response()->json([
            'success' => true,
            'message' => 'Tag removed successfully',
        ]);
    }

    /**
     * Generate a random color for new tags
     */
    private function randomColor()
    {
        $colors = [
            '#3490dc', '#38c172', '#ffed4e', '#e3342f', '#f66d9b',
            '#6574cd', '#9561e2', '#f6993f', '#4dc0b5', '#6cb2eb'
        ];
        
        return $colors[array_rand($colors)];
    }
}


