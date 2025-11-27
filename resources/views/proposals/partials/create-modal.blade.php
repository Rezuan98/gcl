<!-- Modal for Add Proposal -->
<div id="create-modal" class="fixed inset-0 z-50 hidden">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>

    <!-- Modal Container -->
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Add New Proposal</h3>
                    <p class="text-sm text-gray-500 mt-1">Upload proposal details and PDF document</p>
                </div>
                <button id="btn-close-create" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <form action="{{ route('proposals.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col max-h-[calc(90vh-80px)]">
                @csrf
                <div class="flex-1 overflow-y-auto p-6 space-y-6">
                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Proposal Title *</label>
                        <input name="title" required placeholder="Enter proposal title"
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            value="{{ old('title') }}">
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Company Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Company Name *</label>
                        <input name="company_name" required placeholder="Enter company or organization name"
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            value="{{ old('company_name') }}">
                        @error('company_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Contact Phone -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Contact Phone (for OTP) *</label>
                        <input name="client_phone" required placeholder="+880XXXXXXXXX"
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            value="{{ old('client_phone') }}">
                        <p class="mt-1 text-xs text-gray-500">This number will receive OTP for verification</p>
                        @error('client_phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- PDF Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Proposal PDF *</label>
                        <div class="relative">
                            <input type="file" name="pdf_file" id="pdf_file" required accept=".pdf"
                                class="hidden"
                                onchange="updateFileName(this)">
                            <label for="pdf_file" 
                                class="flex items-center justify-center w-full px-4 py-6 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-emerald-500 transition-colors">
                                <div class="text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                    <p class="mt-2 text-sm text-gray-600">
                                        <span class="font-medium text-emerald-600">Click to upload</span> or drag and drop
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">PDF file (max 10MB)</p>
                                    <p id="file-name" class="mt-2 text-sm font-medium text-emerald-600"></p>
                                </div>
                            </label>
                        </div>
                        @error('pdf_file')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Notes (Optional) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Additional Notes (Optional)</label>
                        <textarea name="notes" rows="3" placeholder="Add any additional information..."
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Draft Option -->
                    <div class="flex items-center p-4 bg-amber-50 rounded-lg border border-amber-200">
                        <input type="checkbox" name="save_as_draft" value="1" id="draft-checkbox"
                            class="h-4 w-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500"
                            {{ old('save_as_draft') ? 'checked' : '' }}>
                        <label for="draft-checkbox" class="ml-3 text-sm text-amber-800">
                            Save as <strong>Draft</strong> (won't generate verification URL yet)
                        </label>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-between p-6 border-t border-gray-200 bg-gray-50">
                    <button type="button" id="btn-cancel"
                        class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <div class="flex gap-3">
                        <button type="submit" name="save_as_draft" value="1"
                            class="px-4 py-2 rounded-lg border border-emerald-300 text-emerald-700 hover:bg-emerald-50 transition-colors">
                            Save Draft
                        </button>
                        <button type="submit"
                            class="px-6 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-medium transition-colors">
                            Create Proposal
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Update file name display
    function updateFileName(input) {
        const fileName = document.getElementById('file-name');
        if (input.files && input.files[0]) {
            fileName.textContent = '📄 ' + input.files[0].name;
        }
    }

    // Modal functionality
    const openBtn = document.getElementById('btn-open-create');
    const closeBtn = document.getElementById('btn-close-create');
    const cancelBtn = document.getElementById('btn-cancel');
    const modal = document.getElementById('create-modal');

    function openModal() {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        
        // Reset form
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
            document.getElementById('file-name').textContent = '';
        }
    }

    openBtn?.addEventListener('click', openModal);
    closeBtn?.addEventListener('click', closeModal);
    cancelBtn?.addEventListener('click', closeModal);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeModal();
    });

    modal?.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });
</script>
@endpush