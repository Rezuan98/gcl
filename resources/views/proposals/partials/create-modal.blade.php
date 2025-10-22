<!-- Modal for Add Proposal -->
<div id="create-modal" class="fixed inset-0 z-50 hidden">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>

    <!-- Modal Container -->
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl lg:max-w-5xl max-h-[90vh] overflow-hidden">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Add New Proposal</h3>
                </div>
                <button id="btn-close-create" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <form action="{{ route('proposals.store') }}" method="POST" class="flex flex-col max-h-[calc(90vh-80px)]">
                @csrf
                <div class="flex-1 overflow-y-auto p-6 space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
                            <input name="title" required placeholder="Enter proposal title"
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                value="{{ old('title') }}">
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Amount</label>
                            <input name="amount" type="number" step="0.01" placeholder="0.00"
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                value="{{ old('amount') }}">
                            @error('amount')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Currency</label>
                            <input name="currency_code" value="{{ old('currency_code', 'BDT') }}" placeholder="BDT"
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            @error('currency_code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Client / Organization</label>
                        <input name="client_org" placeholder="Enter client or organization name"
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            value="{{ old('client_org') }}">
                        @error('client_org')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    </div>

                   <div class="grid grid-cols-2 gap-4">
 

                    
                   </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Contact Email</label>
                            <input name="client_email" type="email" placeholder="client@example.com"
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                value="{{ old('client_email') }}">
                            @error('client_email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Contact Phone</label>
                            <input name="client_phone" placeholder="+880XXXXXXXXX"
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                value="{{ old('client_phone') }}">
                            @error('client_phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    

                    <!-- Proposal Details -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Proposal Details</label>
                        <div class="tinymce-container">
                            <textarea name="notes" id="proposal-notes" class="w-full">{{ old('notes') }}</textarea>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">Use the toolbar to format your proposal details with headings, lists, and emphasis.</p>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center p-4 bg-amber-50 rounded-lg border border-amber-200">
                        <input type="checkbox" name="save_as_draft" value="1" id="draft-checkbox"
                            class="h-4 w-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500"
                            {{ old('save_as_draft') ? 'checked' : '' }}>
                        <label for="draft-checkbox" class="ml-3 text-sm text-amber-800">
                            Save as <strong>Draft</strong> (hidden from public verification)
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
                            Submit Proposal
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* TinyMCE Custom Styles */
    .tinymce-container {
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        overflow: hidden;
        transition: border-color 0.2s, box-shadow 0.2s;
        background: white;
    }

    .tinymce-container:focus-within {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
    }

    .tox-tinymce {
        border: none !important;
        border-radius: 0.5rem !important;
    }

    .tox-editor-header {
        background: #f9fafb !important;
        border-bottom: 1px solid #e5e7eb !important;
        padding: 0.5rem !important;
    }

    .tox-toolbar__primary {
        background: transparent !important;
        border: none !important;
    }

    #proposal-notes {
        padding: 1rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        line-height: 1.6;
        resize: vertical;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    #proposal-notes:focus {
        outline: none;
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.tiny.cloud/1/px1itsitpyrt4u7zf2clqulug5bkfrq4r8uqnvxpwoje65nf/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    // TinyMCE initialization
    let tinymceEditor = null;
    
    function initializeTinyMCE() {
        if (tinymceEditor) {
            return;
        }

        tinymce.init({
            selector: '#proposal-notes',
            menubar: false,
            branding: false,
            promotion: false,
            statusbar: false,
            plugins: [
                'lists', 'link', 'autolink', 'autoresize',
                'searchreplace', 'wordcount', 'fullscreen'
            ],
            toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | bullist numlist | link | removeformat | fullscreen',
            block_formats: 'Paragraph=p; Heading 1=h1; Heading 2=h2; Heading 3=h3',
            min_height: 240,
            max_height: 700,
            autoresize_on_init: true,
            autoresize_bottom_margin: 16,
            content_style: `
                body { font-family: 'Inter', system-ui, -apple-system, sans-serif; font-size:14px; line-height:1.6; color:#374151; margin:1rem; }
                h1 { color:#111827; font-size:1.5rem; font-weight:700; margin:1rem 0 .5rem; }
                h2 { color:#111827; font-size:1.25rem; font-weight:600; margin:.75rem 0 .5rem; }
                h3 { color:#111827; font-size:1.125rem; font-weight:600; margin:.5rem 0 .25rem; }
                p { margin:.5rem 0; } ul,ol { margin:.5rem 0; padding-left:1.5rem; } li { margin:.25rem 0; }
                a { color:#10b981; text-decoration:underline; }
            `,
            skin: 'oxide',
            content_css: false,
            setup(editor) { 
                window.tinymceEditor = editor; 
            }
        });
    }

    // Modal functionality
    const openBtn = document.getElementById('btn-open-create');
    const closeBtn = document.getElementById('btn-close-create');
    const cancelBtn = document.getElementById('btn-cancel');
    const modal = document.getElementById('create-modal');

    function openModal() {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        setTimeout(() => {
            initializeTinyMCE();
        }, 100);
    }

    function closeModal() {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        
        if (tinymceEditor) {
            tinymceEditor.setContent('');
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

    // Form submission
    document.querySelector('form')?.addEventListener('submit', function(e) {
        if (tinymceEditor) {
            tinymceEditor.save();
        }
    });

    window.addEventListener('beforeunload', function() {
        if (tinymceEditor) {
            tinymce.remove('#proposal-notes');
        }
    });
</script>
@endpush