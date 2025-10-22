<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

   
    
   public function store(Request $request)
    {
        // Debug: Log the incoming request
        \Log::info('Proposal Store Request', $request->all());

        try {
            $validated = $request->validate([
                
                'title'            => ['required','string','max:255'],
                'amount'           => ['nullable','numeric','min:0'],
                'currency_code'    => ['nullable','string','max:3'],
                'client_org'       => ['nullable','string','max:255'],
                
                'client_email'     => ['nullable','email','max:255'],
                'client_phone'     => ['required','string','max:50'], // Required for OTP
                'notes'            => ['nullable','string'],
                'save_as_draft'    => ['nullable','boolean'],
            ]);

            \Log::info('Validation passed', $validated);

            // Generate proposal number if not provided
            $proposalNo = $validated['proposal_no'] ?? $this->generateProposalNo();
            
            \Log::info('Generated proposal number', ['proposal_no' => $proposalNo]);

            $proposalData = [
                'proposal_no'      => $proposalNo,
                'title'            => $validated['title'],
                'amount'           => $validated['amount'] ?? null,
                'currency_code'    => strtoupper($validated['currency_code'] ?? 'BDT'),
                'client_org'       => $validated['client_org'] ?? null,
               
                'client_email'     => $validated['client_email'] ?? null,
                'client_phone'     => $validated['client_phone'],
                'notes'            => $validated['notes'] ?? null,
                'status'           => $request->boolean('save_as_draft') ? 'draft' : 'pending',
            ];

            \Log::info('About to create proposal with data', $proposalData);

            $proposal = Proposal::create($proposalData);

            \Log::info('Proposal created successfully', ['id' => $proposal->id]);

            $message = $proposal->status === 'draft' 
                ? 'Draft saved successfully!' 
                : 'Proposal submitted successfully!';

            return back()->with('success', $message);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', ['errors' => $e->errors()]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Failed to create proposal', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Failed to create proposal: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Update proposal status (pending to verified or back to pending)
     */
    public function updateStatus(Request $request, Proposal $proposal)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,verified']
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
     * Delete a proposal
     */
    public function destroy(Proposal $proposal)
    {
        $proposal->delete();
        return back()->with('success', 'Proposal deleted successfully!');
    }

    /**
     * Generate a unique proposal number
     */
    private function generateProposalNo(): string
    {
        $year = now()->format('Y');
        
        do {
            $seq = str_pad((string)random_int(1, 99999), 5, '0', STR_PAD_LEFT);
            $proposalNo = "P{$year}{$seq}";
        } while (Proposal::where('proposal_no', $proposalNo)->exists());

        return $proposalNo;
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