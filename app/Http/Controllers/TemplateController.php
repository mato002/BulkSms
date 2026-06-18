<?php

namespace App\Http\Controllers;

use App\Support\CsvExporter;
use App\Support\ListFilters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TemplateController extends Controller
{
    public function index(Request $request)
    {
        $clientId = session('client_id', 1);
        $query = DB::table('templates')->where('client_id', $clientId);

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                    ->orWhere('body', 'like', $search)
                    ->orWhere('subject', 'like', $search);
            });
        }

        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        $templates = ListFilters::paginate(
            $query->orderBy('id', 'desc'),
            $request,
            25
        );

        return view('templates.index', compact('templates'));
    }

    public function export(Request $request)
    {
        $clientId = session('client_id', 1);
        $query = DB::table('templates')->where('client_id', $clientId);

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                    ->orWhere('body', 'like', $search)
                    ->orWhere('subject', 'like', $search);
            });
        }

        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        $rows = $query->orderBy('id', 'desc')->get();

        return CsvExporter::download(
            'templates_' . date('Y-m-d') . '.csv',
            ['ID', 'Name', 'Channel', 'Subject', 'Body', 'Created At'],
            function () use ($rows) {
                foreach ($rows as $template) {
                    yield [
                        $template->id,
                        $template->name,
                        $template->channel,
                        $template->subject ?? '',
                        $template->body,
                        $template->created_at,
                    ];
                }
            }
        );
    }

    public function create()
    {
        return view('templates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'channel' => 'required|in:sms,whatsapp,email',
            'subject' => 'nullable|string|max:255',
            'body' => 'required|string',
        ]);

        $clientId = session('client_id', 1);

        DB::table('templates')->insert([
            'client_id' => $clientId,
            'name' => $validated['name'],
            'channel' => $validated['channel'],
            'subject' => $validated['subject'] ?? null,
            'body' => $validated['body'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('templates.index')->with('success', 'Template created successfully');
    }

    public function show(string $id)
    {
        $clientId = session('client_id', 1);
        $template = DB::table('templates')
            ->where('client_id', $clientId)
            ->where('id', $id)
            ->first();

        if (!$template) {
            abort(404);
        }

        return view('templates.show', compact('template'));
    }

    public function edit(string $id)
    {
        $clientId = session('client_id', 1);
        $template = DB::table('templates')
            ->where('client_id', $clientId)
            ->where('id', $id)
            ->first();

        if (!$template) {
            abort(404);
        }

        return view('templates.edit', compact('template'));
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'channel' => 'required|in:sms,whatsapp,email',
            'subject' => 'nullable|string|max:255',
            'body' => 'required|string',
        ]);

        $clientId = session('client_id', 1);

        DB::table('templates')
            ->where('client_id', $clientId)
            ->where('id', $id)
            ->update([
                'name' => $validated['name'],
                'channel' => $validated['channel'],
                'subject' => $validated['subject'] ?? null,
                'body' => $validated['body'],
                'updated_at' => now(),
            ]);

        return redirect()->route('templates.index')->with('success', 'Template updated successfully');
    }

    public function destroy(string $id)
    {
        $clientId = session('client_id', 1);

        DB::table('templates')
            ->where('client_id', $clientId)
            ->where('id', $id)
            ->delete();

        return redirect()->route('templates.index')->with('success', 'Template deleted successfully');
    }
}
