<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ConsentForm;
use App\Models\Patient;
use App\Models\Treatment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ConsentFormController extends Controller
{
    public function index(Request $request)
    {
        $query = ConsentForm::with(['patient', 'dentist'])->latest('signed_at');

        if ($request->filled('consent_type')) {
            $query->where('consent_type', $request->consent_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('consent_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhereHas('patient', function ($pq) use ($search) {
                      $pq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('patient_number', 'like', "%{$search}%");
                  });
            });
        }

        $consentForms = $query->paginate(15)->withQueryString();

        $consentTypes = [
            'tooth_extraction' => 'Tooth Extraction Consent',
            'root_canal' => 'Endodontic (Root Canal) Consent',
            'implant_surgery' => 'Dental Implant Surgery Consent',
            'orthodontic_treatment' => 'Orthodontic Treatment Consent',
            'anesthesia' => 'Local / Sedation Anesthesia Consent',
            'general_treatment' => 'General Dental Treatment Consent',
            'xray' => 'Dental Radiography / X-Ray Consent',
        ];

        return view('consent_forms.index', compact('consentForms', 'consentTypes'));
    }

    public function create(Request $request)
    {
        $selectedPatient = null;
        if ($request->filled('patient_id')) {
            $selectedPatient = Patient::find($request->patient_id);
        }

        $patients = Patient::orderBy('last_name')->get();
        $dentists = User::where('role', 'dentist')->orderBy('name')->get();
        $treatments = Treatment::with('patient')->latest()->take(50)->get();

        $templates = [
            'tooth_extraction' => [
                'title' => 'Informed Consent for Tooth Extraction & Oral Surgery',
                'description' => "I hereby authorize the attending dentist and clinical staff to perform the recommended tooth extraction(s). The nature and purpose of the procedure, possible alternative treatments, and the substantial risks have been explained to me. I understand that potential risks include, but are not limited to: postoperative bleeding, swelling, pain, infection, dry socket (alveolar osteitis), bruising, damage to adjacent teeth or restorations, temporary or permanent numbness of the lip, tongue, chin, or gums (nerve paresthesia), and sinus involvement for upper molar extractions. I have had an opportunity to ask questions and discuss all concerns."
            ],
            'root_canal' => [
                'title' => 'Informed Consent for Endodontic Therapy (Root Canal Treatment)',
                'description' => "I understand that root canal therapy is performed to retain a tooth which might otherwise require extraction. Although endodontic therapy has a very high degree of clinical success, it is a biological procedure and results cannot be guaranteed. Potential risks include: instrument separation within root canals, canal calcification/perforation, postoperative discomfort or flare-up requiring antibiotics or analgesics, and the necessity of a permanent restoration (such as a dental crown) following completion to protect against tooth fracture."
            ],
            'implant_surgery' => [
                'title' => 'Informed Consent for Dental Implant Placement & Bone Augmentation',
                'description' => "I authorize the surgical placement of dental implants into my jawbone. I understand that smoking, uncontrolled diabetes, poor oral hygiene, or osteoporosis can increase the risk of implant failure. I acknowledge that risks include bleeding, infection, nerve injury causing altered sensation, sinus perforation, and potential implant failure to osseointegrate, which may require removal."
            ],
            'anesthesia' => [
                'title' => 'Informed Consent for Local Anesthesia Administration',
                'description' => "I authorize the administration of local dental anesthesia. Common and rare complications have been explained to me, including localized hematoma, prolonged numbness or tingling (paresthesia), tachycardia or palpitations, dizziness, and rare allergic reactions."
            ],
            'general_treatment' => [
                'title' => 'General Dental Treatment Informed Consent',
                'description' => "I consent to the dental examinations, cleanings, diagnostic procedures, dental restorations (fillings), and preventive care recommended by the dentist. I confirm that I have provided an accurate and complete medical and dental history."
            ]
        ];

        return view('consent_forms.create', compact('patients', 'dentists', 'treatments', 'selectedPatient', 'templates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'dentist_id' => 'required|exists:users,id',
            'treatment_id' => 'nullable|exists:treatments,id',
            'consent_type' => 'required|in:tooth_extraction,root_canal,implant_surgery,orthodontic_treatment,anesthesia,general_treatment,xray,other',
            'title' => 'required|string|max:255',
            'description_and_risks' => 'required|string',
            'patient_signature' => 'required|string', // base64 canvas data
            'witness_name' => 'nullable|string|max:255',
            'witness_signature' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $consentNumber = 'CNS-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        $consent = ConsentForm::create([
            'consent_number' => $consentNumber,
            'patient_id' => $validated['patient_id'],
            'dentist_id' => $validated['dentist_id'],
            'treatment_id' => $validated['treatment_id'] ?? null,
            'consent_type' => $validated['consent_type'],
            'title' => $validated['title'],
            'description_and_risks' => $validated['description_and_risks'],
            'patient_signature' => $validated['patient_signature'],
            'signed_at' => Carbon::now(),
            'witness_name' => $validated['witness_name'] ?? null,
            'witness_signature' => $validated['witness_signature'] ?? null,
            'status' => 'signed',
            'notes' => $validated['notes'] ?? null,
            'created_by_id' => auth()->id(),
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'consent_form_signed',
            'model_type' => ConsentForm::class,
            'model_id' => $consent->id,
            'description' => "Digital consent form executed: {$consent->title} for patient #{$consent->patient->patient_number} (RA 10173 compliant signature)",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('consent-forms.show', $consent)->with('success', "Digital consent form signed and securely recorded.");
    }

    public function show(ConsentForm $consentForm)
    {
        $consentForm->load(['patient', 'dentist', 'treatment', 'createdBy']);
        return view('consent_forms.show', compact('consentForm'));
    }
}
