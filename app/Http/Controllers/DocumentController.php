<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\PatientDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function store(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'document_file' => 'required|file|max:15360|mimes:jpeg,png,jpg,pdf,dicom,webp', // up to 15MB
            'category' => 'required|in:xray,panoramic,photo,scan,report,consent,other',
            'tooth_number' => 'nullable|string|max:50',
            'treatment_id' => 'nullable|exists:treatments,id',
            'description' => 'nullable|string|max:500',
        ]);

        $file = $request->file('document_file');
        $fileName = $file->getClientOriginalName();
        $fileType = $file->getClientOriginalExtension();
        $fileSize = $file->getSize();
        $filePath = $file->store('patient_documents/' . $patient->id, 'public');

        $doc = PatientDocument::create([
            'patient_id' => $patient->id,
            'treatment_id' => $validated['treatment_id'] ?? null,
            'tooth_number' => $validated['tooth_number'] ?? null,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_type' => $fileType,
            'file_size' => $fileSize,
            'category' => $validated['category'],
            'description' => $validated['description'] ?? null,
            'uploaded_by_id' => Auth::id(),
        ]);

        AuditLog::log('document_uploaded', "Uploaded {$validated['category']} ({$fileName}) for patient {$patient->full_name}.", PatientDocument::class, $doc->id);

        return redirect()->route('patients.show', ['patient' => $patient, 'tab' => 'documents'])->with('success', "File {$fileName} uploaded successfully.");
    }

    public function destroy(PatientDocument $document)
    {
        $patientId = $document->patient_id;
        $fileName = $document->file_name;

        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        AuditLog::log('document_deleted', "Deleted document {$fileName}.", PatientDocument::class, $document->id);

        return redirect()->route('patients.show', ['patient' => $patientId, 'tab' => 'documents'])->with('success', "Document deleted.");
    }
}
