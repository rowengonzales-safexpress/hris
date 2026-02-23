<?php

namespace App\Http\Controllers\FRMS;

use Inertia\Inertia;
use Inertia\Response;
use App\Models\FRMS\Form;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\FRMS\FrmsDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\FRMS\FinanceDisbursement;
use App\Services\NotificationService;
use App\Models\Core\CoreBranch;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailNotification;
use App\Models\Core\User;

class DocumentController extends Controller
{
    /**
     * Display the document upload page for a specific FRMS form
     */
    public function index(Request $request, $frmId): Response
    {
        $form = Form::with(['documents' => function($query) {
            $query->where('is_active', true)->orderBy('created_at', 'desc');
        }])->findOrFail($frmId);

        return Inertia::render('FRMS/Form/Document', [
            'form' => $form,
            'documents' => $form->documents,
        ]);
    }

    /**
     * Upload and store documents
     */
    public function upload(Request $request, $frmId): JsonResponse
    {
       $request->validate([
            'files' => 'required|array',
            'files.*' => 'required|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx',
            'description' => 'nullable|string|max:500',
        ]);

        $form = Form::findOrFail($frmId);
        $uploadedDocuments = [];
        $uploadedDocumentObjects = []; // Store document objects for notifications

        foreach ($request->file('files') as $file) {
            try {
                // Generate unique filename
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $filename = Str::uuid() . '.' . $extension;

                // Create directory structure: frms_documents/form_id/year/month/
                $directory = 'frms_documents/' . $frmId . '/' . date('Y') . '/' . date('m');

                // Store the file
                $filePath = $file->storeAs($directory, $filename, 'public');

                // Create database record
                $document = FrmsDocument::create([
                    'frm_id' => $frmId,
                    'document_name' => pathinfo($originalName, PATHINFO_FILENAME),
                    'original_filename' => $originalName,
                    'file_path' => $filePath,
                    'file_extension' => $extension,
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'description' => $request->description,
                    'uploaded_by' => auth()->id(),
                ]);

                $uploadedDocuments[] = [
                    'id' => $document->id,
                    'document_name' => $document->document_name,
                    'original_filename' => $document->original_filename,
                    'file_size_human' => $document->file_size_human,
                    'file_url' => $document->file_url,
                    'uploaded_at' => $document->uploaded_at ? $document->uploaded_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
                ];

                // Store document object for later notification
                $uploadedDocumentObjects[] = $document;

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to upload file: ' . $originalName,
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        // Update FinanceDisbursement status and handle notifications (only once after all uploads)
        if (!empty($uploadedDocuments)) {
            $disbursement = FinanceDisbursement::where('form_id', $frmId)->first();
            $oldStatus = $disbursement ? $disbursement->status : null;

            // Update disbursement status
            FinanceDisbursement::where('form_id', $frmId)->update([
                'status' => 'O',
            ]);

            // Send notifications for all uploaded documents (wrapped in try-catch to prevent errors from affecting upload response)
            try {
                foreach ($uploadedDocumentObjects as $document) {
                    NotificationService::notifyDocumentUploaded($document, $form, 4, $form->user_id);
                }

                // Notify about disbursement status change if status actually changed
                if ($disbursement && $oldStatus !== 'O') {
                    NotificationService::notifyFormStatusChanged($form, $oldStatus, 'O', 4, $form->user_id);
                }
            } catch (\Exception $e) {
                // Log the notification error but don't let it affect the upload response
                \Log::error('Notification error during document upload: ' . $e->getMessage(), [
                    'form_id' => $frmId,
                    'document_count' => count($uploadedDocumentObjects),
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($uploadedDocuments) . ' document(s) uploaded successfully',
            'documents' => $uploadedDocuments
        ]);
    }

    /**
     * Download a document
     */
    public function download($documentId)
    {
        try {
            $document = FrmsDocument::where('is_active', true)->findOrFail($documentId);

            $filePath = storage_path('app/public/' . $document->file_path);

            // Log download attempt for debugging
            Log::info('Document download attempt', [
                'document_id' => $documentId,
                'file_path' => $document->file_path,
                'full_path' => $filePath,
                'file_exists' => file_exists($filePath),
                'original_filename' => $document->original_filename
            ]);

            if (!file_exists($filePath)) {
                \Log::error('File not found for download', [
                    'document_id' => $documentId,
                    'file_path' => $filePath,
                    'document_file_path' => $document->file_path
                ]);
                abort(404, 'File not found');
            }

            return response()->download($filePath, $document->original_filename);

        } catch (\Exception $e) {
            \Log::error('Document download failed', [
                'document_id' => $documentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                abort(404, 'Document not found');
            }

            abort(500, 'Download failed: ' . $e->getMessage());
        }
    }

    /**
     * Delete a document
     */
    public function destroy($documentId): JsonResponse
    {
        $document = FrmsDocument::where('is_active', true)->findOrFail($documentId);

        try {
            // Delete file from storage
            if (Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
            FrmsDocument::where('id', $documentId)->delete();



            // Soft delete by setting is_active to false
            $updated = $document->update(['is_active' => false]);



            if (!$updated) {
                throw new \Exception('Failed to update document status');
            }

            try {
                $form = Form::find($document->frm_id);
                $creator = $form ? User::find($form->user_id) : null;
                $creatorEmail = $creator?->email;
                $siteheadId = $creator?->sitehead_user_id;
                $siteheadEmail = $siteheadId ? User::where('id', $siteheadId)->value('email') : null;
                $uploaderEmail = $document->uploaded_by ? User::where('id', $document->uploaded_by)->value('email') : null;

                $subject = 'Document Deleted - ' . ($document->original_filename ?? '');
                $emailRequest = [
                    'subject' => $subject,
                    'title' => 'Document Deleted',
                    'intro' => 'A document was deleted for a fund request.',
                    'frm_no' => $form?->frm_no,
                    'filename' => $document->original_filename,
                    'deleted_by' => trim((auth()->user()->first_name ?? '') . ' ' . (auth()->user()->last_name ?? '')),
                    'action_url' => $form ? "/frls/form/{$form->id}/documents" : null,
                    'branch_name' => $form ? CoreBranch::where('id', $form->branch_id)->value('branch_name') : null,
                ];

                $to = [];
                if ($creatorEmail) { $to[] = $creatorEmail; }
                if (empty($to) && $siteheadEmail) { $to[] = $siteheadEmail; }
                $cc = [];
                if ($uploaderEmail && !in_array($uploaderEmail, $to)) { $cc[] = $uploaderEmail; }
                if ($siteheadEmail && !in_array($siteheadEmail, $to)) { $cc[] = $siteheadEmail; }

                if (!empty($to) || !empty($cc)) {
                    Mail::to($to ?: $cc)
                        ->cc(!empty($to) ? $cc : [])
                        ->send(new MailNotification($emailRequest, $subject, 'emails.generic'));
                }
            } catch (\Throwable $e) {
            }

            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Document deletion failed', [
                'document_id' => $documentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get documents for a specific form (API endpoint)
     */
    public function getDocuments($frmId): JsonResponse
    {
        $documents = FrmsDocument::where('frm_id', $frmId)
            ->where('is_active', true)
            ->with('uploader:id,name')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($document) {
                return [
                    'id' => $document->id,
                    'document_name' => $document->document_name,
                    'original_filename' => $document->original_filename,
                    'file_size_human' => $document->file_size_human,
                    'file_url' => $document->file_url,
                    'uploaded_at' => $document->uploaded_at ? $document->uploaded_at->format('Y-m-d H:i:s') : null,
                    'uploader' => $document->uploader->name ?? 'Unknown',
                ];
            });

        return response()->json([
            'success' => true,
            'documents' => $documents
        ]);
    }
}
