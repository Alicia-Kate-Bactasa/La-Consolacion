<?php
require_once 'database/db.php';

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

// Fetch product data (custom orders are stored in products table with type='custom')
$stmt = $pdo->prepare('SELECT * FROM products WHERE id = ? AND type = "custom"');
$stmt->execute([$order_id]);
$product = $stmt->fetch();

if (!$product) {
    die('Custom order not found.');
}

// Fetch custom order details
$stmt = $pdo->prepare('SELECT * FROM custom_orders WHERE id = ?');
$stmt->execute([$order_id]);
$customOrder = $stmt->fetch();

if (!$customOrder) {
    die('Custom order details not found.');
}

// Calculate payment amount (30% downpayment)
$totalAmount = $product['price'];
$downpayment = $totalAmount * 0.3;
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Custom Order Payment</title>
    <script src="https://cdn.tailwindcss.com?v=<?php echo time(); ?>"></script>
    <style>
      .accent-blue {
        color: #2d5979;
      }
      .bg-accent-blue {
        background-color: #2d5979;
      }
      .border-accent-blue {
        border-color: #2d5979;
      }
      .hover-accent-blue:hover {
        background-color: #234a66;
      }
      .focus-accent-blue:focus {
        border-color: #2d5979;
        box-shadow: 0 0 0 3px rgba(45, 89, 121, 0.1);
      }

      .file-upload-area {
        border: 2px dashed #2d5979;
        transition: all 0.3s ease;
        background: #fafbfc;
      }

      .file-upload-area:hover {
        background-color: rgba(45, 89, 121, 0.05);
        border-color: #234a66;
      }

      .file-upload-area.dragover {
        background-color: rgba(45, 89, 121, 0.1);
        border-color: #234a66;
      }

      .image-preview {
        position: relative;
        display: inline-block;
      }

      .remove-btn {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #ef4444;
        color: white;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 14px;
        line-height: 1;
        transition: background-color 0.2s;
      }

      .remove-btn:hover {
        background: #dc2626;
      }

      .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        z-index: 1000;
        align-items: center;
        justify-content: center;
      }

      .modal.active {
        display: flex;
      }

      .modal img {
        max-width: 90%;
        max-height: 90%;
        object-fit: contain;
      }

      #loadingOverlay {
        transition: opacity 0.3s ease;
      }

      /* Loading animations */
      @keyframes spin {
        to {
          transform: rotate(360deg);
        }
      }

      @keyframes pulse {
        0%,
        100% {
          opacity: 1;
        }
        50% {
          opacity: 0.6;
        }
      }

      @keyframes fadeIn {
        from {
          opacity: 0;
          transform: translateY(10px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
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
      
      /* Additional polish for redesigned step 2 */
      .modern-card {
        background: #fff;
        border-radius: 1.25rem;
        box-shadow: 0 4px 24px 0 rgba(34, 42, 69, 0.08);
        border: 1px solid #f3f4f6;
      }
      #imagePreview img {
        max-width: 120px;
        max-height: 120px;
        margin: 0 auto;
        display: block;
        border-radius: 0.5rem;
        margin-top: 0.5rem;
      }
      /* Ensure both cards are the same height on desktop */
      @media (min-width: 768px) {
        #step2 {
          align-items: stretch;
          min-height: 520px;
        }
        .modern-card {
          height: 100%;
        }
      }
    </style>
  </head>
  <body class="bg-white min-h-screen">
    <div class="max-w-7xl mx-auto px-8 py-16">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">
          Custom Order Payment
        </h1>
        <p class="text-gray-600">
          Complete your custom order with secure e-wallet payment
        </p>
      </div>

      <!-- Step 1: E-wallet & Order Summary -->
      <div id="step1" class="flex flex-col xl:flex-row gap-8">
        <!-- E-wallet selection (left) -->
        <div class="xl:w-2/5 w-full">
          <div
            class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100"
          >
            <h3 class="text-xl font-bold mb-6 text-gray-800">
              Choose E-wallet
            </h3>
            <div class="space-y-4">
              <div
                class="ewallet-option flex items-center gap-4 cursor-pointer p-4 rounded-xl border border-gray-200 hover:border-blue-400 hover:bg-blue-50 transition-all duration-200"
                data-value="gcash"
              >
                <img
                  src="payment/gcash-logo.png"
                  alt="GCash"
                  class="w-10 h-10 object-contain"
                />
                <span class="font-medium text-gray-700">GCash</span>
              </div>
              <div
                class="ewallet-option flex items-center gap-4 cursor-pointer p-4 rounded-xl border border-gray-200 hover:border-blue-400 hover:bg-blue-50 transition-all duration-200"
                data-value="paymaya"
              >
                <img
                  src="payment/maya-logo.png"
                  alt="PayMaya"
                  class="w-10 h-10 object-contain"
                />
                <span class="font-medium text-gray-700">PayMaya</span>
              </div>
              <div
                class="ewallet-option flex items-center gap-4 cursor-pointer p-4 rounded-xl border border-gray-200 hover:border-blue-400 hover:bg-blue-50 transition-all duration-200"
                data-value="gotyme"
              >
                <img
                  src="payment/gotyme-logo.png"
                  alt="GoTyme"
                  class="w-10 h-10 object-contain"
                />
                <span class="font-medium text-gray-700">GoTyme</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Order Summary (right) -->
        <div class="xl:w-3/5 w-full">
          <div
            class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100"
          >
            <h3 class="text-xl font-bold mb-6 text-gray-800">Custom Order Summary</h3>
            
            <!-- Custom Order Details -->
            <div class="mb-6 space-y-4">
              <div class="flex items-center gap-3 mb-4">
                <?php if (!empty($product['image'])): ?>
                  <img src="Image/product-add/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-16 h-16 object-cover rounded border" />
                <?php else: ?>
                  <div class="w-16 h-16 bg-gray-100 rounded border flex items-center justify-center">
                    <span class="text-gray-400 text-xs">No Image</span>
                  </div>
                <?php endif; ?>
                <div class="flex-1">
                  <div class="font-semibold text-gray-800"><?php echo htmlspecialchars($product['name']); ?></div>
                  <div class="text-sm text-gray-500">Custom Order</div>
                </div>
                <div class="font-semibold text-gray-700">₱<?php echo number_format($product['price'], 2); ?></div>
              </div>
              
              <!-- Customer Details -->
              <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="font-semibold text-gray-700 mb-2">Customer Details</h4>
                <div class="text-sm text-gray-600">
                  <div><strong>Name:</strong> <?php echo htmlspecialchars($customOrder['customer_name']); ?></div>
                  <div><strong>Email:</strong> <?php echo htmlspecialchars($customOrder['email']); ?></div>
                </div>
              </div>
              
              <!-- Custom Description -->
              <div class="bg-blue-50 p-4 rounded-lg">
                <h4 class="font-semibold text-blue-700 mb-2">Custom Description</h4>
                <div class="text-sm text-blue-600">
                  <?php echo nl2br(htmlspecialchars($customOrder['description'])); ?>
                </div>
              </div>
            </div>

            <div class="space-y-4 pt-6 border-t border-gray-200">
              <div class="flex justify-between items-center">
                <span class="font-medium text-gray-700">Total Amount:</span>
                <span class="font-semibold text-gray-800">₱<?php echo number_format($totalAmount, 2); ?></span>
              </div>
              <div class="flex justify-between items-center">
                <span class="font-medium text-gray-700">Downpayment:</span>
                <span class="font-semibold text-blue-600">30%</span>
              </div>
            </div>

            <div
              class="flex justify-between items-center pt-4 border-t border-gray-200 mt-4"
            >
              <div>
                <span class="text-lg font-bold text-gray-800">Amount to Pay</span>
                <p class="text-sm text-gray-500">(30% downpayment)</p>
              </div>
              <span class="text-2xl font-bold text-blue-600">₱<?php echo number_format($downpayment, 2); ?></span>
            </div>

            <button
              id="continueBtn"
              class="w-full mt-8 bg-blue-600 text-white py-3 px-6 rounded-xl font-semibold hover:bg-blue-700 transition-colors duration-200"
            >
              Continue to Payment
            </button>
          </div>
        </div>
      </div>
      
      <!-- Step 2: Payment Confirmation -->
      <div
        id="step2"
        class="hidden flex flex-col md:flex-row gap-8 items-stretch justify-center py-8"
      >
        <!-- QR Code and instructions (left) -->
        <div
          class="md:w-1/2 w-full flex-1 flex flex-col modern-card p-8 h-full"
        >
          <h2 class="text-2xl font-bold accent-blue mb-2 text-center">
            Scan to Pay
          </h2>
          <p class="text-gray-600 mb-4 text-center">
            Use your selected e-wallet app to scan the QR code and complete your
            payment.
          </p>
          <div
            class="w-56 h-56 mx-auto mb-4 flex items-center justify-center bg-gray-50 rounded-xl border-2 border-dashed border-accent-blue"
          >
            <img
              src="payment/qr-code.png"
              alt="QR Code"
              class="w-48 h-48 object-contain"
            />
          </div>
          <div class="flex flex-col items-center mt-2">
            <span class="text-sm text-gray-500">Amount to pay</span>
            <span class="text-2xl font-bold text-blue-600 mt-1">₱<?php echo number_format($downpayment, 2); ?></span>
          </div>
        </div>
        
        <!-- Payment form (right) -->
        <div class="md:w-1/2 w-full flex-1 flex flex-col h-full">
          <div class="modern-card p-8 h-full flex flex-col justify-between">
            <form
              id="paymentForm"
              method="POST"
              enctype="multipart/form-data"
              action="payment/payment.php"
              class="space-y-6"
            >
              <div>
                <label
                  for="mobile"
                  class="block text-sm font-medium text-gray-700 mb-1"
                  >Your Mobile No.</label
                >
                <input
                  type="tel"
                  id="mobile"
                  name="mobile"
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-200 transition"
                  placeholder="09XXXXXXXXX"
                />
                <div
                  id="mobileError"
                  class="text-red-600 text-xs mt-1 hidden text-right"
                >
                  Mobile number must be exactly 11 digits.
                </div>
              </div>
              <div>
                <label
                  for="reference"
                  class="block text-sm font-medium text-gray-700 mb-1"
                  >Reference Number</label
                >
                <input
                  type="text"
                  id="reference"
                  name="reference"
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-200 transition"
                  placeholder="Enter reference number"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Upload Payment Screenshot
                </label>
                <div
                  class="file-upload-area rounded-lg p-6 text-center cursor-pointer"
                  id="fileUpload"
                >
                  <input
                    type="file"
                    id="fileInput"
                    name="payment_screenshot"
                    accept="image/*"
                    class="hidden"
                    required
                  />
                  <svg
                    class="w-12 h-12 text-gray-400 mx-auto mb-3"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                    />
                  </svg>
                  <p class="text-sm text-gray-600 mb-1">
                    Click to upload or drag and drop
                  </p>
                  <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                </div>
                <div id="filePreview" class="mt-3 hidden">
                  <div class="image-preview">
                    <img
                      id="previewImage"
                      class="w-32 h-32 object-cover rounded-lg border cursor-pointer hover:opacity-90 transition-opacity"
                    />
                    <div class="remove-btn" id="removeBtn">×</div>
                  </div>
                  <p id="fileName" class="text-sm text-gray-600 mt-1"></p>
                </div>
              </div>
              
              <!-- Hidden inputs for custom order -->
              <input type="hidden" id="hiddenService" name="service" value="gcash" />
              <input type="hidden" id="hiddenAmount" name="amount" value="<?php echo $downpayment; ?>" />
              <input type="hidden" name="product_id[]" value="<?php echo $order_id; ?>" />
              <input type="hidden" name="quantity[]" value="1" />
              
              <button
                type="submit"
                class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition mt-4 text-lg"
              >
                Confirm Payment
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading Overlay -->
    <div
      id="loadingOverlay"
      class="fixed inset-0 bg-white/95 backdrop-blur-sm z-50 flex items-center justify-center hidden"
    >
      <div class="text-center animate-fade-in">
        <!-- Simple Spinner -->
        <div class="mb-8 flex justify-center">
          <div
            class="w-16 h-16 border-4 border-gray-200 border-t-blue-900 rounded-full animate-spin-slow"
          ></div>
        </div>

        <!-- Loading Text -->
        <p class="text-gray-600 font-medium animate-pulse-slow text-lg">
          Processing Payment...
        </p>
      </div>
    </div>

    <script>
      // E-wallet selection with highlight
      let selectedEwallet = "gcash"; // Default selection

      // Set initial selection
      document
        .querySelector('[data-value="gcash"]')
        .classList.add("border-blue-400", "bg-blue-50");

      document.querySelectorAll(".ewallet-option").forEach((option) => {
        option.addEventListener("click", function () {
          // Remove selection from all options
          document.querySelectorAll(".ewallet-option").forEach((opt) => {
            opt.classList.remove("border-blue-400", "bg-blue-50");
          });

          // Add selection to clicked option
          this.classList.add("border-blue-400", "bg-blue-50");

          // Update selected value
          selectedEwallet = this.getAttribute("data-value");
          document.getElementById("hiddenService").value = selectedEwallet;
        });
      });

      // Step transition
      document
        .getElementById("continueBtn")
        .addEventListener("click", function (e) {
          e.preventDefault();
          document.getElementById("step1").classList.add("hidden");
          document.getElementById("step2").classList.remove("hidden");
        });

      // Mobile number validation
      const mobileInput = document.getElementById("mobile");
      const mobileError = document.getElementById("mobileError");

      mobileInput.addEventListener("input", function (e) {
        let value = e.target.value.replace(/\D/g, ""); // Remove non-digits
        if (value.length > 11) value = value.slice(0, 11);
        e.target.value = value;

        if (value.length === 11) {
          mobileError.classList.add("hidden");
          mobileInput.classList.remove("border-red-500");
        } else if (value.length > 0) {
          mobileError.classList.remove("hidden");
          mobileInput.classList.add("border-red-500");
        } else {
          mobileError.classList.add("hidden");
          mobileInput.classList.remove("border-red-500");
        }
      });

      // Custom drag-and-drop upload with preview and remove
      const fileUpload = document.getElementById("fileUpload");
      const fileInput = document.getElementById("fileInput");
      const filePreview = document.getElementById("filePreview");
      const previewImage = document.getElementById("previewImage");
      const fileName = document.getElementById("fileName");
      const removeBtn = document.getElementById("removeBtn");
      
      // Click to open file dialog
      fileUpload.addEventListener("click", () => fileInput.click());
      
      // Drag & drop events
      fileUpload.addEventListener("dragover", (e) => {
        e.preventDefault();
        fileUpload.classList.add("dragover");
      });
      fileUpload.addEventListener("dragleave", () => {
        fileUpload.classList.remove("dragover");
      });
      fileUpload.addEventListener("drop", (e) => {
        e.preventDefault();
        fileUpload.classList.remove("dragover");
        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
          fileInput.files = e.dataTransfer.files;
          showFilePreview(fileInput.files[0]);
        }
      });
      fileInput.addEventListener("change", () => {
        if (fileInput.files && fileInput.files[0]) {
          showFilePreview(fileInput.files[0]);
        }
      });
      
      function showFilePreview(file) {
        if (!file.type.startsWith("image/")) return;
        const reader = new FileReader();
        reader.onload = (e) => {
          previewImage.src = e.target.result;
          filePreview.classList.remove("hidden");
          fileName.textContent = file.name;
        };
        reader.readAsDataURL(file);
      }
      
      removeBtn.addEventListener("click", () => {
        fileInput.value = "";
        filePreview.classList.add("hidden");
        previewImage.src = "";
        fileName.textContent = "";
      });

      // Form submission with loading overlay
      document
        .getElementById("paymentForm")
        .addEventListener("submit", function (e) {
          e.preventDefault(); // Prevent immediate submission

          // Show loading overlay
          document.getElementById("loadingOverlay").classList.remove("hidden");

          // Submit form after 3 seconds
          setTimeout(() => {
            document.getElementById("paymentForm").submit();
          }, 3000);
        });
    </script>
  </body>
</html> 