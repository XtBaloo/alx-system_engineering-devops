<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function store(Request $request, Student $student)
    {
        $this->authorize('create', Document::class);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:birth_certificate,admission,previous_school_record,certificate,other'],
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
        ]);

        $path = $request->file('file')->store('documents/'.$student->id, 'local');

        Document::create([
            'student_id' => $student->id,
            'title' => $data['title'],
            'type' => $data['type'],
            'file_path' => $path,
            'original_filename' => $request->file('file')->getClientOriginalName(),
            'uploaded_by' => auth()->id(),
        ]);

        return back()->with('success', 'Document uploaded successfully.');
    }

    public function download(Document $document)
    {
        $this->authorize('view', $document);

        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->download($document->file_path, $document->original_filename ?? $document->title);
    }

    public function destroy(Document $document)
    {
        $this->authorize('delete', $document);

        Storage::disk('local')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Document removed.');
    }
}
