<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Profile Picture') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Update your account's profile picture") }}
        </p>
    </header>

    <!-- Success Messages -->
    @if (session('status') === 'picture-uploaded')
        <div class="mt-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="text-sm text-green-800 dark:text-green-300">{{ __('Profile picture uploaded successfully!') }}</p>
            </div>
        </div>
    @endif

    @if (session('status') === 'picture-removed')
        <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <p class="text-sm text-blue-800 dark:text-blue-300">{{ __('Profile picture removed successfully!') }}</p>
            </div>
        </div>
    @endif

    <div class="mt-6">
        <!-- Profile Picture Display with Actions -->
        <div class="flex items-center space-x-6">
            <!-- Profile Picture Preview -->
            <div class="relative group cursor-pointer" onclick="openUploadModal()">
                <img
                    id="currentProfilePicture"
                    src="{{ $user->profile_picture_url }}"
                    alt="{{ $user->full_name }}"
                    class="w-20 h-20 rounded-full object-cover border-3 border-gray-300 dark:border-gray-600 shadow-md transition-all duration-200 group-hover:border-indigo-400"
                >
                <!-- Hover Overlay -->
                <div class="absolute inset-0 rounded-full bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all duration-200 flex items-center justify-center">
                    <svg class="w-6 h-6 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex-1">
                <div class="flex items-center gap-3">
                    <!-- Upload Button -->
                    <button
                        type="button"
                        onclick="openUploadModal()"
                        class="btn-primary btn-sm"
                        aria-label="Upload profile picture">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Upload an Image
                    </button>

                    <!-- Remove Button - Only if user has a profile picture -->
                    @if($user->profile_picture)
                        <button
                            type="button"
                            onclick="if(confirm('Are you sure you want to remove your profile picture?')) { document.getElementById('remove-picture-form').submit(); }"
                            class="btn-secondary btn-sm"
                            aria-label="Remove profile picture">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Remove
                        </button>

                        <!-- Hidden Remove Form -->
                        <form id="remove-picture-form" method="POST" action="{{ route('profile.picture.remove') }}" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endif
                </div>
                <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                    JPG, PNG, or GIF. Maximum file size 2MB.
                </p>
            </div>
        </div>

        @error('profile_picture')
            <div class="mt-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm text-red-800 dark:text-red-400">{{ $message }}</p>
                </div>
            </div>
        @enderror
    </div>
</section><!-- Upload Modal with Professional Design -->
<div id="uploadModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-70 backdrop-blur-sm z-50 flex items-center justify-center p-4 overflow-y-auto transition-all duration-300" onclick="closeModalOnOutsideClick(event)">
    <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-xl w-full my-8 transform transition-all duration-300" onclick="event.stopPropagation()">
        <!-- Modal Header with Gradient -->
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-indigo-50 to-blue-50 dark:from-gray-800 dark:to-gray-700 rounded-t-2xl">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-indigo-600 rounded-lg">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                        Upload Profile Picture
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Choose a photo that represents you</p>
                </div>
            </div>
            <button
                type="button"
                onclick="closeUploadModal()"
                class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-all duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Modal Body -->
        <form id="uploadForm" method="POST" action="{{ route('profile.picture.upload') }}" enctype="multipart/form-data" onsubmit="handleFormSubmit(event)">
            @csrf
            <div class="px-6 py-6">
                <!-- Image Preview Area -->
                <div class="mb-4">
                    <div
                        id="dropZone"
                        class="border-3 border-dashed border-indigo-300 dark:border-indigo-600 rounded-xl p-10 text-center hover:border-indigo-500 dark:hover:border-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-all duration-300 cursor-pointer relative bg-gradient-to-br from-gray-50 to-indigo-50 dark:from-gray-900 dark:to-indigo-950"
                        onclick="document.getElementById('profilePictureInput').click()">

                        <!-- Preview Image (Hidden initially) -->
                        <div id="imagePreviewContainer" class="hidden">
                            <div class="flex flex-col items-center">
                                <!-- Circular Image Container with Enhanced Styling -->
                                <div class="w-48 h-48 rounded-full overflow-hidden border-4 border-indigo-200 dark:border-indigo-700 shadow-2xl bg-white dark:bg-gray-800 mb-4 ring-4 ring-indigo-100 dark:ring-indigo-900/50">
                                    <img
                                        id="imagePreview"
                                        src=""
                                        alt="Preview"
                                        class="w-full h-full object-cover"
                                    >
                                </div>

                                <!-- File Info with Icon -->
                                <div class="text-center">
                                    <div class="flex items-center justify-center space-x-2 mb-2">
                                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <p class="text-sm font-semibold text-green-700 dark:text-green-400">Image Selected</p>
                                    </div>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">
                                        Click to change or drop a new image
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Upload Icon (Shown initially) with Enhanced Design -->
                        <div id="uploadPlaceholder">
                            <div class="mb-4">
                                <div class="mx-auto w-20 h-20 bg-indigo-100 dark:bg-indigo-900/50 rounded-full flex items-center justify-center mb-4">
                                    <svg class="h-10 w-10 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                </div>
                            </div>
                            <h4 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Drop your image here
                            </h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">
                                or click to browse
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-500">
                                JPG, PNG or GIF • Max 2MB
                            </p>
                        </div>

                        <input
                            type="file"
                            name="profile_picture"
                            id="profilePictureInput"
                            accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                            class="hidden"
                            onchange="handleFileSelect(this)"
                        >
                    </div>

                    <!-- Filename Display with Icon -->
                    <div class="text-center mt-4">
                        <p id="fileName" class="text-sm text-gray-800 dark:text-gray-200 font-semibold"></p>
                    </div>

                    <!-- File Size Warning with Better Design -->
                    <div id="fileSizeWarning" class="hidden mt-4 p-4 bg-red-50 dark:bg-red-900/20 border-2 border-red-300 dark:border-red-800 rounded-lg">
                        <div class="flex items-start space-x-3">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <p class="text-sm font-semibold text-red-800 dark:text-red-300">File too large</p>
                                <p class="text-xs text-red-700 dark:text-red-400 mt-1">Please choose an image smaller than 2MB or try compressing it first.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Divider -->
                <div class="relative mb-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
                    </div>
                    <div class="relative flex justify-center text-xs uppercase">
                        <span class="bg-white dark:bg-gray-800 px-2 text-gray-500 dark:text-gray-400">or</span>
                    </div>
                </div>

                <!-- Choose File Button with Better Design -->
                <div class="text-center">
                    <button
                        type="button"
                        onclick="document.getElementById('profilePictureInput').click()"
                        class="inline-flex items-center px-6 py-3 bg-white dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 hover:border-gray-400 dark:hover:border-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all duration-200 shadow-sm hover:shadow">
                        <svg class="w-5 h-5 mr-2 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        Browse Files
                    </button>
                </div>
            </div>

            <!-- Modal Footer with Professional Styling -->
            <div class="flex items-center justify-between px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 rounded-b-lg">
                <button
                    type="button"
                    onclick="closeUploadModal()"
                    class="inline-flex items-center px-6 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 hover:border-gray-400 dark:hover:border-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all duration-200 shadow-sm hover:shadow">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Cancel
                </button>
                <button
                    type="submit"
                    id="saveButton"
                    disabled
                    class="inline-flex items-center px-6 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-400 dark:disabled:bg-gray-600 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    <span id="saveButtonText">Upload Picture</span>
                    <svg id="saveButtonSpinner" class="hidden animate-spin w-4 h-4 ml-2 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let selectedFile = null;
const maxFileSize = 2 * 1024 * 1024; // 2MB in bytes

function openUploadModal() {
    const modal = document.getElementById('uploadModal');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    // Add entrance animation
    setTimeout(() => {
        modal.querySelector('.relative').classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closeUploadModal() {
    const modal = document.getElementById('uploadModal');
    const modalContent = modal.querySelector('.relative');

    // Add exit animation
    modalContent.classList.remove('scale-100', 'opacity-100');
    modalContent.classList.add('scale-95', 'opacity-0');

    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        modalContent.classList.remove('scale-95', 'opacity-0');
        resetUploadForm();
    }, 200);
}

function closeModalOnOutsideClick(event) {
    if (event.target.id === 'uploadModal') {
        closeUploadModal();
    }
}

function resetUploadForm() {
    const saveButton = document.getElementById('saveButton');

    selectedFile = null;
    document.getElementById('profilePictureInput').value = '';
    document.getElementById('imagePreviewContainer').classList.add('hidden');
    document.getElementById('uploadPlaceholder').classList.remove('hidden');
    document.getElementById('fileSizeWarning').classList.add('hidden');
    document.getElementById('fileName').textContent = '';

    // Reset button state
    saveButton.disabled = true;
    saveButton.classList.add('bg-gray-400', 'cursor-not-allowed');
    saveButton.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
}

function clearSelectedImage() {
    resetUploadForm();
}

function handleFileSelect(input) {
    const file = input.files[0];
    const saveButton = document.getElementById('saveButton');

    if (!file) {
        resetUploadForm();
        return;
    }

    // Check file size
    if (file.size > maxFileSize) {
        document.getElementById('fileSizeWarning').classList.remove('hidden');
        saveButton.disabled = true;
        saveButton.classList.add('bg-gray-400', 'cursor-not-allowed');
        saveButton.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
        return;
    }

    document.getElementById('fileSizeWarning').classList.add('hidden');
    selectedFile = file;

    // Show preview
    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('imagePreview').src = e.target.result;
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('uploadPlaceholder').classList.add('hidden');
        document.getElementById('imagePreviewContainer').classList.remove('hidden');

        // Enable the save button with visual feedback
        saveButton.disabled = false;
        saveButton.classList.remove('bg-gray-400', 'cursor-not-allowed');
        saveButton.classList.add('bg-indigo-600', 'hover:bg-indigo-700');
    }
    reader.readAsDataURL(file);
}

// Drag and drop functionality
const dropZone = document.getElementById('dropZone');

['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, function(e) {
        e.preventDefault();
        e.stopPropagation();
    }, false);
});

['dragenter', 'dragover'].forEach(eventName => {
    dropZone.addEventListener(eventName, function() {
        dropZone.classList.add('border-indigo-500', 'bg-indigo-50', 'dark:bg-indigo-900/20', 'dark:border-indigo-400');
    }, false);
});

['dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, function() {
        dropZone.classList.remove('border-indigo-500', 'bg-indigo-50', 'dark:bg-indigo-900/20', 'dark:border-indigo-400');
    }, false);
});

dropZone.addEventListener('drop', function(e) {
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        document.getElementById('profilePictureInput').files = files;
        handleFileSelect(document.getElementById('profilePictureInput'));
    }
}, false);

// Close modal on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeUploadModal();
    }
});

// Handle form submission
function handleFormSubmit(event) {
    const saveButton = document.getElementById('saveButton');
    const buttonText = document.getElementById('saveButtonText');
    const buttonSpinner = document.getElementById('saveButtonSpinner');

    // Disable button and show loading state
    saveButton.disabled = true;
    buttonText.textContent = 'Uploading...';
    buttonSpinner.classList.remove('hidden');

    // Form will submit normally
    return true;
}
</script>
</section>
