<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProposalController extends Controller
{
    /**
     * Display a listing of published proposals (excluding drafts)
     */
    public function index(Request $request)
    {
        $query = Proposal::where('status', '!=', 'draft')
            ->orderBy('created_at', 'desc');

        // Search functionality
        if ($search = $request->get('search')) {
            $query->search($search);
        }

        // Status filter
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $proposals = $query->paginate(15)->withQueryString();
          
       
        return view('proposals.index', compact('proposals'));
    }

    /**
     * Display draft proposals
     */
    public function drafts(Request $request)
    {
        $query = Proposal::where('status', 'draft')
            ->orderBy('created_at', 'desc');

        // Search functionality
        if ($search = $request->get('search')) {
            $query->search($search);
        }

        $proposals = $query->paginate(15)->withQueryString();

        return view('proposals.draft', compact('proposals'));
    }

    /**
     * Store a new proposal
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title'            => ['required', 'string', 'max:255'],
                'company_name'     => ['required', 'string', 'max:255'],
                'client_phone'     => ['required', 'string', 'max:50'],
                'pdf_file'         => ['required', 'file', 'mimes:pdf'], // Max 10MB
                'notes'            => ['nullable', 'string'],
                'save_as_draft'    => ['nullable', 'boolean'],
            ]);

            Log::info('Proposal Store Request', $validated);

            // Handle PDF upload
            $pdfPath = null;
            if ($request->hasFile('pdf_file')) {
                $file = $request->file('pdf_file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $pdfPath = $file->storeAs('proposals', $filename, 'public');
                
                Log::info('PDF uploaded', ['path' => $pdfPath]);
            }

            // Generate unique verification token
            $uniqueToken = Proposal::generateUniqueToken();

            $proposalData = [
                'unique_token'     => $uniqueToken,
                'title'            => $validated['title'],
                'company_name'     => $validated['company_name'],
                'client_phone'     => $validated['client_phone'],
                'pdf_path'         => $pdfPath,
                'notes'            => $validated['notes'] ?? null,
                'status'           => $request->boolean('save_as_draft') ? 'draft' : 'pending',
            ];

            $proposal = Proposal::create($proposalData);

            Log::info('Proposal created successfully', [
                'id' => $proposal->id,
                'token' => $proposal->unique_token
            ]);

            $message = $proposal->status === 'draft' 
                ? 'Draft saved successfully!' 
                : 'Proposal submitted successfully!';

            return redirect()->route('proposals.index', $proposal)
                ->with('success', $message);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', ['errors' => $e->errors()]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Failed to create proposal', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Failed to create proposal: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display a single proposal
     */
    public function show(Proposal $proposal)
    {
        return view('proposals.show', compact('proposal'));
    }

    /**
     * Update proposal status
     */
    public function updateStatus(Request $request, Proposal $proposal)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,verified,draft']
        ]);

        $proposal->update(['status' => $validated['status']]);

        return back()->with('success', 'Proposal status updated successfully!');
    }

    /**
     * Publish a draft proposal
     */
    public function publish(Proposal $proposal)
    {
        if ($proposal->status !== 'draft') {
            return back()->with('error', 'Only draft proposals can be published.');
        }

        $proposal->update(['status' => 'pending']);

        return back()->with('success', 'Proposal published successfully!');
    }

    /**
     * Copy verification URL
     */
    public function copyUrl(Proposal $proposal)
    {
        return response()->json([
            'success' => true,
            'url' => $proposal->verification_url
        ]);
    }

    /**
     * Delete a proposal
     */
    public function destroy(Proposal $proposal)
    {
        $proposal->delete();
        return redirect()->route('proposals.index')
            ->with('success', 'Proposal deleted successfully!');
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats()
    {
        return [
            'total' => Proposal::where('status', '!=', 'draft')->count(),
            'verified' => Proposal::where('status', 'verified')->count(),
            'pending' => Proposal::where('status', 'pending')->count(),
            'drafts' => Proposal::where('status', 'draft')->count(),
        ];
    }
}