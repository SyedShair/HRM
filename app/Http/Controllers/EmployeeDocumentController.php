<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use PDF;

class EmployeeDocumentController extends Controller
{
    /**
     * Legacy keyword map — only used as a fallback to guess a type
     * for documents that were uploaded before document_type_id existed.
     */
    protected function legacyKeywordMap()
    {
        return [
            'Passport / Photo ID'              => ['passport', 'photo id', 'national id', 'id card'],
            'Right to Work (Share Code)'       => ['right to work', 'share code', 'sharecode', 'rtw'],
            'Visa / BRP'                       => ['visa', 'brp', 'biometric residence'],
            'Certificate of Sponsorship (COS)' => ['cos', 'certificate of sponsorship', 'sponsorship'],
            'National Insurance'               => ['national insurance', 'ni number', 'ni proof', ' ni '],
            'Proof of Address'                 => ['proof of address', 'utility bill', 'bank statement', 'council tax'],
            'P45 / Starter Checklist'          => ['p45', 'starter checklist', 'hmrc starter'],
            'Bank Details'                     => ['bank detail','bank statement' ,'bank account', 'sort code'],
            'Signed Employment Contract'       => ['contract', 'employment contract', 'offer letter'],
            'Emergency Contact / Next of Kin'  => ['emergency contact', 'next of kin', 'kin'],
        ];
    }

    /**
     * Items that are already captured as plain text fields on the
     * employee's profile during registration. For these, the checklist
     * ticks based on whether the field has a value — no document
     * upload is required. Each entry returns [is_filled, display_value].
     */
    protected function profileFieldCheck($label, $employee, $company)
    {
        switch ($label) {

            case 'Passport / Photo ID':
                $value = $employee->nationalid ?? null;
                return [!empty($value), $value];

            case 'Right to Work (Share Code)':
                $value = $employee->sharecode ?? null;
                return [!empty($value), $value];

            case 'Visa / BRP':
                $value = $company->visastatus ?? null;
                return [!empty($value), $value];

            case 'Certificate of Sponsorship (COS)':
                $value = $company->COSCertificateNo ?? null;
                return [!empty($value), $value];

            case 'National Insurance':
                $value = $employee->NI ?? null;
                return [!empty($value), $value];

            case 'Proof of Address':
                $value = $employee->homeaddress ?? null;
                return [!empty($value), $value];

            case 'Emergency Contact / Next of Kin':
                $name = $company->kinname ?? null;
                $number = $company->kinno ?? null;
                $filled = !empty($name) && !empty($number);
                $display = $filled ? "{$name} ({$number})" : null;
                return [$filled, $display];

            default:
                // Not a profile field — must be satisfied by an uploaded document
                return null;
        }
    }

    /**
     * Builds the compliance checklist for one employee. Items with a
     * matching profile field (NI, Passport No, Share Code, etc.) are
     * ticked based on that field's value. Everything else (P45, Bank
     * Details, Signed Contract) still requires an uploaded document,
     * matched by document_type_id with a legacy keyword fallback.
     */
//     protected function buildRequiredDocumentsChecklist($employee, $company, $documents, $documentTypes)
// {
//     $legacyMap = $this->legacyKeywordMap();
//     $checklist = [];

//     foreach ($documentTypes as $type) {

//         // 1. First check: is there an uploaded document explicitly
//         //    tagged with this type's document_type_id?
//         $match = $documents->first(function ($doc) use ($type) {
//             return (int) ($doc->document_type_id ?? 0) === (int) $type->id;
//         });

//         // 2. Fallback: for documents with no type set at all (old
//         //    uploads, or "Other/Not Listed"), try to match by keyword
//         //    in the file name.
//         if (!$match && isset($legacyMap[$type->label])) {
//             $keywords = $legacyMap[$type->label];

//             $match = $documents->first(function ($doc) use ($keywords) {
//                 if (!empty($doc->document_type_id)) {
//                     return false;
//                 }
//                 $name = strtolower($doc->file_name ?? '');
//                 foreach ($keywords as $keyword) {
//                     if ($name !== '' && strpos($name, $keyword) !== false) {
//                         return true;
//                     }
//                 }
//                 return false;
//             });
//         }

//         // 3. Also check the profile field, if this type has one.
//         $profileCheck = $this->profileFieldCheck($type->label, $employee, $company);
//         $profileFilled = false;
//         $profileValue  = null;

//         if ($profileCheck !== null) {
//             [$profileFilled, $profileValue] = $profileCheck;
//         }

//         // 4. Found if EITHER an uploaded doc matches OR the profile
//         //    field is filled. Prefer the document as evidence when
//         //    both exist, since it's more specific/verifiable.
//         $found = $match !== null || $profileFilled;

//         if ($match !== null) {
//             $matchedFile = $match->file_name;
//             $source = 'document';
//         } elseif ($profileFilled) {
//             $matchedFile = $profileValue;
//             $source = 'profile';
//         } else {
//             $matchedFile = null;
//             $source = $profileCheck !== null ? 'profile' : 'document';
//         }

//         $checklist[] = [
//             'label'        => $type->label,
//             'required'     => (bool) $type->is_required,
//             'found'        => $found,
//             'matched_file' => $matchedFile,
//             'source'       => $source,
//         ];
//     }

//     return $checklist;
// }
protected function buildRequiredDocumentsChecklist($employee, $company, $documents, $documentTypes)
{
    $legacyMap = $this->legacyKeywordMap();
    $checklist = [];

    foreach ($documentTypes as $type) {

        // 1. Exact type match: a document explicitly tagged with this
        //    type's document_type_id.
        $match = $documents->first(function ($doc) use ($type) {
            return (int) ($doc->document_type_id ?? 0) === (int) $type->id;
        });

        // 2. Legacy keyword fallback: untyped documents, matched by
        //    keyword against the known keyword map.
        if (!$match && isset($legacyMap[$type->label])) {
            $keywords = $legacyMap[$type->label];

            $match = $documents->first(function ($doc) use ($keywords) {
                if (!empty($doc->document_type_id)) {
                    return false;
                }
                $name = strtolower($doc->file_name ?? '');
                foreach ($keywords as $keyword) {
                    if ($name !== '' && strpos($name, $keyword) !== false) {
                        return true;
                    }
                }
                return false;
            });
        }

        // 3. NEW: filename-vs-label fallback. Catches documents whose
        //    file name clearly names this type directly (e.g. a file
        //    named "Bank Statement.pdf" satisfying the "Bank Statement"
        //    type), even if it was uploaded under a different/blank
        //    document_type_id. Runs regardless of whether the doc has
        //    a document_type_id set, since the file name is explicit
        //    evidence on its own.
        if (!$match) {
            $labelLower = strtolower($type->label);
            // strip bracketed qualifiers like "(Share Code)" so
            // "Right to Work" still matches "Right to Work.pdf"
            $labelCore = trim(preg_replace('/\s*\(.*?\)\s*/', ' ', $labelLower));

            $match = $documents->first(function ($doc) use ($labelLower, $labelCore) {
                $name = strtolower($doc->file_name ?? '');
                if ($name === '') {
                    return false;
                }
                return strpos($name, $labelLower) !== false
                    || ($labelCore !== '' && strpos($name, $labelCore) !== false);
            });
        }

        // 4. Profile field check, if this type has one.
        $profileCheck = $this->profileFieldCheck($type->label, $employee, $company);
        $profileFilled = false;
        $profileValue  = null;

        if ($profileCheck !== null) {
            [$profileFilled, $profileValue] = $profileCheck;
        }

        $found = $match !== null || $profileFilled;

        if ($match !== null) {
            $matchedFile = $match->file_name;
            $source = 'document';
        } elseif ($profileFilled) {
            $matchedFile = $profileValue;
            $source = 'profile';
        } else {
            $matchedFile = null;
            $source = $profileCheck !== null ? 'profile' : 'document';
        }

        $checklist[] = [
            'label'        => $type->label,
            'required'     => (bool) $type->is_required,
            'found'        => $found,
            'matched_file' => $matchedFile,
            'source'       => $source,
        ];
    }

    return $checklist;
}

    /**
     * FIX: file_path values are now tagged so every downstream consumer
     * (blade views, destroy()) can tell how a file was stored without
     * guessing:
     *   - Storage-disk uploads are saved under 'employee_documents/...'
     *     (relative to the 'public' disk root, i.e. storage/app/public)
     *   - Legacy raw-move uploads are saved under 'uploads/employee_documents/...'
     *     (relative to the public/ web root)
     * These two prefixes never collide, so a single string prefix check
     * is enough to route both display URLs and deletion correctly.
     */
    public function index($id)
    {
        $employee = DB::table('tbl_people')->where('id', $id)->first();

        if (!$employee) {
            abort(404, 'Employee not found');
        }

        $company = DB::table('tbl_company_data')
            ->where('reference', $id)
            ->first();

        $documents = DB::table('employee_documents')
            ->where('people_id', $id)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($doc) {
                $doc->file_url = $this->resolveDocumentUrl($doc->file_path);
                return $doc;
            });

        $documentTypes = DB::table('document_types')
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->get();

        $requiredDocuments = $this->buildRequiredDocumentsChecklist($employee, $company, $documents, $documentTypes);

        return view('employees.documents', compact('employee', 'company', 'documents', 'requiredDocuments', 'documentTypes'));
    }

    /**
     * Resolves the correct public URL for a stored file_path, regardless
     * of which of the two storage methods was used to save it.
     */
    protected function resolveDocumentUrl(?string $filePath): ?string
    {
        if (empty($filePath)) {
            return null;
        }

        // Legacy raw-move uploads live directly under public/, so a plain
        // asset() URL already resolves correctly.
        if (Str::startsWith($filePath, 'uploads/')) {
            return asset($filePath);
        }

        // Storage-disk uploads live under storage/app/public. Storage::url()
        // builds the correct public URL from the disk's own configured
        // 'url' (config/filesystems.php) rather than hardcoding the
        // 'storage/' prefix by hand, so it stays correct even if that
        // disk's URL config changes later (custom domain, CDN, etc).
        return Storage::disk('public')->url($filePath);
    }

    public function store(Request $request)
    {
        $request->validate([
            'people_id' => 'required',
            'file_name' => 'required|string|max:255',
            'storage_method' => 'nullable|in:storage,legacy',
           // 'document'  => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
        ]);

        $employee = DB::table('tbl_people')
            ->where('id', $request->people_id)
            ->first();

        if (!$employee) {
            return back()->with('error', 'Employee not found');
        }

        $file = $request->file('document');

        $folderName = preg_replace(
            '/[^A-Za-z0-9_\-]/',
            '_',
            $employee->firstname . '_' . $employee->lastname
        );

        $original  = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $original);
        $newFileName = time() . '_' . $cleanName . '.' . $extension;

        // FIX: two explicit, selectable storage methods.
        //
        // 'storage' (recommended, default): goes through Laravel's
        // Storage facade onto the 'public' disk - the same mechanism
        // destroy() already assumed was in use. This is the standard,
        // portable way to handle uploads in Laravel (works the same
        // whether the disk is local, S3, etc. later on).
        //
        // 'legacy': preserves the exact original raw file_move()
        // behavior for anyone relying on the old folder layout, with
        // one correction - the original used
        // base_path('../uploads/employee_documents/...'), which resolves
        // to a directory OUTSIDE the entire Laravel project root (one
        // level above it), not inside public/. Files saved there were
        // never actually reachable at the asset() URL the blade
        // generated for them. Swapping to public_path(...) keeps the
        // legacy folder structure/filenames identical but actually
        // lands the file where the web server can serve it.
        $storageMethod = $request->input('storage_method', 'storage');

        if ($storageMethod === 'legacy') {
            $uploadPath = public_path('uploads/employee_documents/' . $folderName);

            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $file->move($uploadPath, $newFileName);

            $filePath = 'uploads/employee_documents/' . $folderName . '/' . $newFileName;
        } else {
            $relativeFolder = 'employee_documents/' . $folderName;

            $storedPath = $file->storeAs($relativeFolder, $newFileName, 'public');

            $filePath = $storedPath;
        }

        if ($extension === 'pdf') {
            $fileType = 'pdf';
        } elseif (in_array($extension, ['doc', 'docx'])) {
            $fileType = 'doc';
        } else {
            $fileType = 'image';
        }

        DB::table('employee_documents')->insert([
            'people_id'        => $request->people_id,
            'document_type_id' => $request->document_type_id ?: null,
            'file_name'        => $request->file_name,
            'file_path'        => $filePath,
            'file_type'        => $fileType,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        return back()->with('success', 'Document uploaded successfully');
    }

    /**
     * Uploads a "Proof of Address" document for an employee, using the
     * Storage disk directly (Storage::disk('public')->storeAs(...)) into
     * a flat 'address-documents' folder shared by every employee.
     *
     * Because this folder is flat (not split per-employee like
     * store()'s 'employee_documents/{folder}' does), the filename alone
     * has to guarantee no collisions between different employees'
     * uploads - a UUID prefix does that reliably, same pattern already
     * used for the branding logo upload in SettingsController@update.
     *
     * The resulting file_path ('address-documents/...', no 'uploads/'
     * prefix) is automatically handled correctly by the existing
     * resolveDocumentUrl() and destroy() prefix checks above, since both
     * already route anything without a literal 'uploads/' prefix through
     * the Storage disk.
     */
    public function storeAddressDocument(Request $request)
    {
        $request->validate([
            'people_id' => 'required',
            'document'  => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $employee = DB::table('tbl_people')
            ->where('id', $request->people_id)
            ->first();

        if (!$employee) {
            return back()->with('error', 'Employee not found');
        }

        $file = $request->file('document');
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid()->toString() . '.' . $extension;

        $file->storeAs('address-documents', $filename, 'public');

        $addressDocType = DB::table('document_types')
            ->where('label', 'Proof of Address')
            ->first();

        if ($extension === 'pdf') {
            $fileType = 'pdf';
        } else {
            $fileType = 'image';
        }

        DB::table('employee_documents')->insert([
            'people_id'        => $request->people_id,
            'document_type_id' => $addressDocType->id ?? null,
            'file_name'        => $request->input('file_name', $file->getClientOriginalName()),
            'file_path'        => 'address-documents/' . $filename,
            'file_type'        => $fileType,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        return back()->with('success', 'Proof of address uploaded successfully');
    }

    public function destroy($id)
    {
        $doc = DB::table('employee_documents')->where('id', $id)->first();

        if ($doc) {
            // FIX: route deletion to whichever backend actually holds the
            // file, based on the same prefix used to resolve its URL.
            // Previously this always called Storage::disk('public')->delete(),
            // which silently no-ops for any file that was actually saved
            // by the raw move() path (outside that disk entirely) - the
            // DB row would be removed but the file left orphaned on disk.
            if (Str::startsWith($doc->file_path, 'uploads/')) {
                $fullPath = public_path($doc->file_path);
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                }
            } else {
                Storage::disk('public')->delete($doc->file_path);
            }

            DB::table('employee_documents')->where('id', $id)->delete();
        }

        return back()->with('success', 'Document deleted.');
    }

    public function printPdf($id)
    {
        $employee = DB::table('tbl_people')->where('id', $id)->first();

        if (!$employee) {
            abort(404, 'Employee not found');
        }

        $company = DB::table('tbl_company_data')
            ->where('reference', $employee->id)
            ->first();

        $schedule = DB::table('tbl_people_schedules')
            ->where('reference', $employee->id)
            ->first();

        $attendanceRaw = DB::table('tbl_people_attendance')
            ->where('reference', $employee->id)
            ->whereNotNull('date')
            ->orderBy('date')
            ->get();

        $attendance = $attendanceRaw->groupBy(function ($item) {
            return Carbon::parse($item->date)->format('Y-m');
        });

        $leaves = DB::table('tbl_people_leaves')
            ->where('reference', $employee->id)
            ->get();

        $documents = DB::table('employee_documents')
            ->where('people_id', $employee->id)
            ->get()
            ->map(function ($doc) {
                $doc->file_url = $this->resolveDocumentUrl($doc->file_path);
                return $doc;
            });

        return view(
            'employees.full-pdf',
            compact('employee', 'company', 'schedule', 'attendance', 'leaves', 'documents')
        );
    }
}