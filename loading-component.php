<!-- Loading Screen Component -->
<style>
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .animate-spin-slow {
        animation: spin 2s linear infinite;
    }
    
    .animate-pulse-slow {
        animation: pulse 2s ease-in-out infinite;
    }
    
    .animate-fade-in {
        animation: fadeIn 0.8s ease-out;
    }
</style>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="fixed inset-0 bg-white/95 backdrop-blur-sm z-50 flex items-center justify-center hidden">
                <div class="text-center animate-fade-in">
            <!-- Simple Spinner -->
            <div class="mb-8 flex justify-center">
                <div class="w-16 h-16 border-4 border-gray-200 border-t-blue-900 rounded-full animate-spin-slow"></div>
            </div>
            
            <!-- Loading Text -->
            <p class="text-gray-600 font-medium animate-pulse-slow text-lg">Loading...</p>
        </div>
</div>

<script>
class SimpleLoadingManager {
    constructor() {
        this.overlay = document.getElementById('loadingOverlay');
    }
    
    show() {
        this.overlay.classList.remove('hidden');
    }
    
    hide() {
        this.overlay.classList.add('hidden');
    }
}

const loadingManager = new SimpleLoadingManager();

function showLoading() {
    loadingManager.show();
}

function hideLoading() {
    loadingManager.hide();
}

// Make functions globally available
window.showLoading = showLoading;
window.hideLoading = hideLoading;
window.loadingManager = loadingManager;
</script> 