<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Payment Successful</title>
    <script src="https://cdn.tailwindcss.com?v=<?php echo time(); ?>"></script>
    <style>
      .accent-blue {
        color: #2d5979;
      }
      .bg-accent-blue {
        background-color: #2d5979;
      }
      .hover-accent-blue:hover {
        background-color: #234a66;
      }

      .success-icon {
        animation: scaleIn 0.8s ease-out;
      }

      #successMessage {
        background: white !important;
        color: #111827 !important; /* dark gray */
        opacity: 1 !important;
        filter: none !important;
        transform: none !important;
      }

      @keyframes scaleIn {
        0% {
          transform: scale(0);
          opacity: 0;
        }
        50% {
          transform: scale(1.1);
          opacity: 0.8;
        }
        100% {
          transform: scale(1);
          opacity: 1;
        }
      }

      .countdown-circle {
        animation: countdownPulse 1s ease-in-out infinite;
      }

      @keyframes countdownPulse {
        0%,
        100% {
          transform: scale(1);
        }
        50% {
          transform: scale(1.05);
        }
      }

      .fade-in {
        animation: fadeIn 0.6s ease-out;
      }

      @keyframes fadeIn {
        from {
          opacity: 0;
          transform: translateY(20px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }

      .progress-bar {
        width: 100%;
        height: 4px;
        background-color: #e5e7eb;
        border-radius: 2px;
        overflow: hidden;
        margin-top: 1rem;
      }

      .progress-fill {
        height: 100%;
        background-color: #2d5979;
        transition: width 1s linear;
      }
      
      /* Loading animations */
      @keyframes spin {
          to { transform: rotate(360deg); }
      }
      
      @keyframes pulse {
          0%, 100% { opacity: 1; }
          50% { opacity: 0.6; }
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
  </head>
  <body class="bg-white min-h-screen flex items-center justify-center">
    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="fixed inset-0 bg-white/95 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="text-center animate-fade-in">
            <!-- Simple Spinner -->
            <div class="mb-8 flex justify-center">
                <div class="w-16 h-16 border-4 border-gray-200 border-t-blue-900 rounded-full animate-spin-slow"></div>
            </div>
            
            <!-- Loading Text -->
            <p class="text-gray-600 font-medium animate-pulse-slow text-lg">Loading...</p>
        </div>
    </div>

    <div class="max-w-md mx-auto px-4 text-center">
      <!-- Success Icon -->
      <div class="success-icon mb-8">
        <div
          class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4"
        >
          <svg
            class="w-12 h-12 text-green-600"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M5 13l4 4L19 7"
            />
          </svg>
        </div>
      </div>

      <!-- Success Message -->
      <div id="receiptArea">
        <div
          id="successMessage"
          class="bg-white text-gray-900 p-6 rounded-xl shadow-lg fade-in mb-8 text-center border border-gray-300"
        >
          <h1 class="text-3xl font-bold text-gray-900 mb-4">
            Payment Successful!
          </h1>
          <p class="text-lg text-gray-800 mb-2">
            Thank you for paying
            <span class="font-semibold accent-blue"
              >₱<span id="paidAmount">0.00</span></span
            >
          </p>
          <p class="text-sm text-gray-600 mb-1">
            <strong>Mobile No:</strong>
            <span id="paidMobile">N/A</span>
          </p>
          <p class="text-sm text-gray-600 mb-4">
            <strong>Reference No:</strong>
            <span id="paidReference">N/A</span>
          </p>

          <p class="text-sm text-gray-600 mb-4">
            Your payment has been received and is being processed.
          </p>

          <!-- Screenshot Notice -->
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
            <p class="text-blue-700 text-sm font-medium">
              Please take a screenshot of this receipt for your reference.
            </p>
          </div>

          <!-- Screenshot Button -->
          <button
            onclick="takeScreenshot()"
            class="screenshot-exclude bg-accent-blue text-white py-2 px-4 rounded-lg hover-accent-blue transition"
          >
            Download Screenshot
          </button>
        </div>
      </div>
      <!-- Countdown Section -->
      <div class="bg-gray-50 rounded-lg p-6 mb-6">
        <div class="flex items-center justify-center mb-4">
          <div
            class="countdown-circle w-16 h-16 bg-accent-blue rounded-full flex items-center justify-center"
          >
            <span id="countdown" class="text-2xl font-bold text-white">8</span>
          </div>
        </div>
        <p class="text-gray-600 mb-2">
          You will be redirected to the shop's main page in
          <span id="countdownText" class="font-semibold accent-blue">8</span>
          seconds.
        </p>

        <!-- Progress Bar -->
        <div class="progress-bar">
          <div id="progressBar" class="progress-fill" style="width: 100%"></div>
        </div>
      </div>

      <!-- Fallback Link -->
      <div class="text-center">
        <p class="text-sm text-gray-500 mb-3">
          If you're not redirected automatically,
        </p>
        <a
          href="../index.php"
          id="manualRedirect"
          class="inline-flex items-center px-4 py-2 bg-accent-blue hover-accent-blue text-white font-medium rounded-lg transition duration-200"
        >
          <svg
            class="w-4 h-4 mr-2"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M13 7l5 5m0 0l-5 5m5-5H6"
            />
          </svg>
          Click here to continue
        </a>
      </div>

      <!-- Additional Info -->
      <div class="mt-8 pt-6 border-t border-gray-200">
        <p class="text-xs text-gray-500">Need help? Contact our support team</p>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <script>
      // DEBUG: Add debugging console logs
      console.log("🔍 PAYMENT SUCCESS DEBUG: Page loaded");
      console.log("🔍 PAYMENT SUCCESS DEBUG: Loading overlay element:", document.getElementById("loadingOverlay"));
      console.log("🔍 PAYMENT SUCCESS DEBUG: Loading overlay HTML:", document.getElementById("loadingOverlay").outerHTML);
      
      // DEBUG: Monitor loading overlay changes
      const loadingOverlay = document.getElementById("loadingOverlay");
      const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
          console.log("🔍 PAYMENT SUCCESS DEBUG: Loading overlay changed:", mutation.type);
          console.log("🔍 PAYMENT SUCCESS DEBUG: Current HTML:", loadingOverlay.outerHTML);
        });
      });
      
      observer.observe(loadingOverlay, {
        attributes: true,
        childList: true,
        subtree: true
      });

      // Hide loading screen after 3 seconds
      setTimeout(() => {
        console.log("🔍 PAYMENT SUCCESS DEBUG: Hiding loading overlay");
        document.getElementById('loadingOverlay').classList.add('hidden');
        console.log("🔍 PAYMENT SUCCESS DEBUG: Loading overlay hidden");
      }, 3000);

      // Set the amount
      function getUrlParameter(name) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
      }

      const amount = getUrlParameter("amount") || "0.00";
      document.getElementById("paidAmount").textContent =
        parseFloat(amount).toFixed(2);

      const mobile = getUrlParameter("mobile") || "N/A";
      const reference = getUrlParameter("reference") || "N/A";

      document.getElementById("paidMobile").textContent = mobile;
      document.getElementById("paidReference").textContent = reference;

      // Unhide the success message
      document.getElementById("successMessage").classList.remove("hidden");

      // Countdown logic
      let countdownTime = 20;
      const countdownElement = document.getElementById("countdown");
      const countdownTextElement = document.getElementById("countdownText");
      const progressBar = document.getElementById("progressBar");
      const redirectUrl = "../index.php";

      function updateCountdown() {
        countdownElement.textContent = countdownTime;
        countdownTextElement.textContent = countdownTime;

        const progressPercent = (countdownTime / 20) * 100;
        progressBar.style.width = progressPercent + "%";

        if (countdownTime <= 0) {
          window.location.href = redirectUrl;
        } else {
          countdownTime--;
        }
      }

      updateCountdown();
      const countdownInterval = setInterval(updateCountdown, 1000);

      document
        .getElementById("manualRedirect")
        .addEventListener("click", function (e) {
          e.preventDefault();
          clearInterval(countdownInterval);
          window.location.href = redirectUrl;
        });

      document.addEventListener("keydown", function (e) {
        if (e.key === "Enter") {
          clearInterval(countdownInterval);
          window.location.href = redirectUrl;
        }
      });

      setInterval(() => {
        if (countdownTime <= 3 && countdownTime > 0) {
          countdownElement.parentElement.classList.add("bg-orange-500");
          countdownElement.parentElement.classList.remove("bg-accent-blue");
        } else if (countdownTime <= 1) {
          countdownElement.parentElement.classList.add("bg-red-500");
          countdownElement.parentElement.classList.remove("bg-orange-500");
        }
      }, 1000);

      // Screenshot
      function takeScreenshot() {
        const captureTarget = document.getElementById("receiptArea");

        // Find and hide all elements marked for exclusion
        const toExclude = captureTarget.querySelectorAll(".screenshot-exclude");
        toExclude.forEach((el) => (el.style.display = "none"));

        html2canvas(captureTarget, {
          backgroundColor: "#ffffff",
          scale: 2,
          useCORS: true,
        }).then((canvas) => {
          // Show excluded elements again
          toExclude.forEach((el) => (el.style.display = ""));

          const link = document.createElement("a");
          link.download = "payment_receipt.png";
          link.href = canvas.toDataURL("image/png");
          link.click();
        });
      }
    </script>
  </body>
</html>
