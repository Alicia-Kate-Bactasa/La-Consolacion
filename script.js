// Script loaded
document.addEventListener("DOMContentLoaded", function () {
  let menu = document.querySelector("#menu-icon");
  let navlist = document.querySelector(".nav-links");

  if (menu && navlist) {
    menu.onclick = () => {
      menu.classList.toggle("bx-x");
      navlist.classList.toggle("active");
    };
  }
});

let cart = [];

// --- Connect PHP product cards to modal ---
document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".shop-product-card").forEach(function (card) {
    card.addEventListener("click", function () {
      const product = {
        id: card.dataset.id,
        image: card.dataset.image,
        name: card.dataset.name,
        price: card.dataset.price,
        type: card.dataset.type,
        stock: card.dataset.stock,
        material: card.dataset.material,
        description: card.dataset.description,
      };
      openProductModal(product);
    });
  });
});

function addToCart(product) {
  // Show loading state
  const btn = document.getElementById("modalAddToCartBtn");
  if (btn) {
    const originalText = btn.textContent;
    btn.textContent = "Adding...";
    btn.disabled = true;

    fetch("add-to-cart.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `product_id=${product.id}&quantity=1`,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          // Success - close modal and show cart
          document.getElementById("productModal").style.display = "none";
          openCartPanel();
        } else {
          // Error - show message and restore button
          alert(data.error || "Error adding to cart");
          btn.textContent = originalText;
          btn.disabled = false;
        }
      })
      .catch((error) => {
        console.error("Error adding to cart:", error);
        alert("Error adding to cart");
        btn.textContent = originalText;
        btn.disabled = false;
      });
  }
}

function openCartModal() {
  renderCart();
  document.getElementById("cartModal").style.display = "flex";
}

// Cart modal event handlers (guarded)
const cartModalClose = document.getElementById("cartModalClose");
if (cartModalClose) {
  cartModalClose.onclick = function () {
    document.getElementById("cartModal").style.display = "none";
  };
}
const cartModal = document.getElementById("cartModal");
if (cartModal) {
  cartModal.onclick = function (e) {
    if (e.target === this) {
      this.style.display = "none";
    }
  };
}

// Cart icon click
const cartIcon = document.querySelector(".bx-cart");
if (cartIcon) {
  cartIcon.parentElement.addEventListener("click", function (e) {
    e.preventDefault();
    if (typeof openCartModal === "function") openCartModal();
  });
}

function renderCart() {
  const cartItems = document.getElementById("cartItems");
  const cartCount = document.getElementById("cartCount");
  const cartCountPlural = document.getElementById("cartCountPlural");
  const cartSubtotal = document.getElementById("cartSubtotal");
  if (!cartItems || !cartCount || !cartCountPlural || !cartSubtotal) return;
  cartItems.innerHTML = "";
  let subtotal = 0;
  let count = 0;
  cart.forEach((item, i) => {
    subtotal += item.qty * parseFloat(item.price);
    count += item.qty;
    const div = document.createElement("div");
    div.className = "cart-modal-item";
    div.innerHTML = `
      <div class="cart-modal-item-image"><img src="${item.image}" alt="${
      item.name
    }" /></div>
      <div class="cart-modal-item-info">
        <div class="cart-modal-item-name">${item.name}</div>
        <div class="cart-modal-item-price">₱${parseFloat(
          item.price
        ).toLocaleString()}</div>
        <div class="cart-modal-item-qty">
          <button class="cart-qty-minus">-</button>
          <span>${item.qty}</span>
          <button class="cart-qty-plus">+</button>
          <button class="cart-modal-item-remove">&times;</button>
        </div>
      </div>
    `;
    // Quantity controls
    div.querySelector(".cart-qty-minus").onclick = () => {
      if (item.qty > 1) {
        item.qty--;
        renderCart();
        updateCartIcon();
      }
    };
    div.querySelector(".cart-qty-plus").onclick = () => {
      item.qty++;
      renderCart();
      updateCartIcon();
    };
    div.querySelector(".cart-modal-item-remove").onclick = () => {
      cart.splice(i, 1);
      renderCart();
      updateCartIcon();
    };
    cartItems.appendChild(div);
  });
  cartCount.textContent = count;
  cartCountPlural.style.display = count === 1 ? "none" : "";
  cartSubtotal.textContent = "₱" + subtotal.toLocaleString();
  // Suggestion carousel (show up to 3 random products not in cart)
  const suggest = document.getElementById("cartSuggest");
  suggest.innerHTML = "";
  // You can implement suggestions if you want, or leave blank
}

function updateCartIcon() {
  // Optionally, you can add a badge to the cart icon
}

function openProductModal(product) {
  if (!document.getElementById("modalImage")) return;
  document.getElementById("modalImage").src = product.image;
  document.getElementById("modalImage").alt = product.name;
  document.getElementById("modalName").textContent = product.name;
  document.getElementById("modalPrice").textContent = `₱${parseFloat(
    product.price
  ).toLocaleString()}`;
  document.getElementById("modalCategory").textContent = product.type
    ? product.type
    : "";
  document.getElementById("modalMaterial").textContent = product.material
    ? `Material: ${product.material}`
    : "";
  document.getElementById("modalStock").textContent = product.stock
    ? `Stock: ${product.stock}`
    : "";
  document.getElementById("modalDescription").textContent = product.description
    ? product.description
    : "";

  // Check current cart quantity and update button accordingly
  checkCartQuantityAndUpdateButton(product);

  document.getElementById("productModal").style.display = "flex";
}

function checkCartQuantityAndUpdateButton(product) {
  // Get current cart quantity for this product
  fetch(`get-cart-quantity.php?product_id=${product.id}`)
    .then((response) => response.json())
    .then((data) => {
      let modalInfo = document.querySelector(".product-modal-info");
      let existingBtn = document.getElementById("modalAddToCartBtn");
      if (existingBtn) existingBtn.remove();

      let btn = document.createElement("button");
      btn.id = "modalAddToCartBtn";

      if (data.success) {
        const currentQuantity = data.quantity;
        const maxStock = parseInt(product.stock);

        if (maxStock <= 0) {
          // Out of stock - disable button
          btn.className = "modal-add-to-cart-btn disabled";
          btn.textContent = "Out of Stock";
          btn.disabled = true;
          btn.style.backgroundColor = "#9ca3af";
          btn.style.cursor = "not-allowed";
          btn.style.opacity = "0.6";
        } else if (currentQuantity >= maxStock) {
          // At max stock - disable button
          btn.className = "modal-add-to-cart-btn disabled";
          btn.textContent = `Max Stock Reached (${currentQuantity}/${maxStock})`;
          btn.disabled = true;
          btn.style.backgroundColor = "#9ca3af";
          btn.style.cursor = "not-allowed";
          btn.style.opacity = "0.6";
        } else {
          // Can add more - enable button
          btn.className = "modal-add-to-cart-btn";
          btn.textContent =
            currentQuantity > 0
              ? `Add to Cart (${currentQuantity}/${maxStock})`
              : `Add to Cart (0/${maxStock})`;
          btn.disabled = false;
          btn.style.backgroundColor = "";
          btn.style.cursor = "";
          btn.style.opacity = "";
          btn.onclick = function () {
            addToCart(product);
            document.getElementById("productModal").style.display = "none";
          };
        }
      } else {
        // Error or not logged in - show default button
        btn.className = "modal-add-to-cart-btn";
        btn.textContent = "Add to Cart";
        btn.onclick = function () {
          addToCart(product);
          document.getElementById("productModal").style.display = "none";
        };
      }

      modalInfo.appendChild(btn);
    })
    .catch((error) => {
      console.error("Error checking cart quantity:", error);
      // Fallback to default button
      let modalInfo = document.querySelector(".product-modal-info");
      let existingBtn = document.getElementById("modalAddToCartBtn");
      if (existingBtn) existingBtn.remove();

      let btn = document.createElement("button");
      btn.id = "modalAddToCartBtn";
      btn.className = "modal-add-to-cart-btn";
      btn.textContent = "Add to Cart";
      btn.onclick = function () {
        addToCart(product);
        document.getElementById("productModal").style.display = "none";
      };
      modalInfo.appendChild(btn);
    });
}

if (document.getElementById("productModalClose")) {
  document.getElementById("productModalClose").onclick = function () {
    document.getElementById("productModal").style.display = "none";
  };
}
if (document.getElementById("productModal")) {
  document.getElementById("productModal").onclick = function (e) {
    if (e.target === this) {
      this.style.display = "none";
    }
  };
}

// Cart Panel Functions
function openCartPanel() {
  const cartPanel = document.getElementById("cartPanel");
  const cartOverlay = document.getElementById("cartOverlay");

  if (cartPanel && cartOverlay) {
    // Show cart panel (for shop.php)
    cartOverlay.classList.remove("hidden");
    cartPanel.classList.remove("translate-x-full");

    // Load cart items
    loadCartItems();
  } else {
    // Redirect to shop page with cart parameter to open cart panel (for index.php)
    window.location.href = "shop.php?openCart=true";
  }
}

function closeCartPanel() {
  const cartPanel = document.getElementById("cartPanel");
  const cartOverlay = document.getElementById("cartOverlay");

  if (cartPanel && cartOverlay) {
    // Hide cart panel
    cartPanel.classList.add("translate-x-full");
    cartOverlay.classList.add("hidden");
  }
}

function loadCartItems() {
  fetch("get-cart-items.php")
    .then((response) => response.json())
    .then((data) => {
      const cartItems = document.getElementById("cartItems");
      const emptyCart = document.getElementById("emptyCart");
      const cartCount = document.getElementById("cartCount");
      const cartSubtotal = document.getElementById("cartSubtotal");
      const cartTotal = document.getElementById("cartTotal");

      if (data.items && data.items.length > 0) {
        // Show cart items
        cartItems.innerHTML = "";
        emptyCart.classList.add("hidden");

        let total = 0;
        data.items.forEach((item) => {
          const itemTotal = parseFloat(item.price) * parseInt(item.quantity);
          total += itemTotal;

          const itemDiv = document.createElement("div");
          itemDiv.className =
            "flex items-center space-x-4 p-4 bg-white rounded-xl border border-gray-200";
          itemDiv.innerHTML = `
            <img src="${item.image}" alt="${
            item.name
          }" class="w-16 h-16 object-cover rounded-lg">
            <div class="flex-1">
              <h4 class="font-semibold text-gray-900">${item.name}</h4>
              <p class="text-sm text-gray-600">₱${parseFloat(
                item.price
              ).toLocaleString()}</p>
              <div class="flex items-center space-x-2 mt-2">
                <button onclick="updateCartQuantity(${
                  item.id
                }, -1)" class="w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center hover:bg-gray-200">-</button>
                <span class="text-sm font-medium">${item.quantity}</span>
                <button onclick="updateCartQuantity(${
                  item.id
                }, 1)" class="w-6 h-6 rounded-full flex items-center justify-center ${
            item.quantity >= item.stock
              ? "bg-gray-300 cursor-not-allowed opacity-50"
              : "bg-gray-100 hover:bg-gray-200"
          }" ${item.quantity >= item.stock ? "disabled" : ""}>+</button>
              </div>
              ${
                item.quantity >= item.stock
                  ? '<p class="text-xs text-red-500 mt-1">Max stock reached</p>'
                  : ""
              }
            </div>
            <button onclick="removeCartItem(${
              item.id
            })" class="text-red-500 hover:text-red-700">
              <i class="bx bx-trash"></i>
            </button>
          `;
          cartItems.appendChild(itemDiv);
        });

        cartCount.textContent = `${data.items.length} item${
          data.items.length !== 1 ? "s" : ""
        }`;
        cartSubtotal.textContent = `₱${total.toLocaleString()}`;
        cartTotal.textContent = `₱${total.toLocaleString()}`;
      } else {
        // Show empty cart
        cartItems.innerHTML = "";
        emptyCart.classList.remove("hidden");
        cartCount.textContent = "0 items";
        cartSubtotal.textContent = "₱0.00";
        cartTotal.textContent = "₱0.00";
      }
    })
    .catch((error) => {
      console.error("Error loading cart items:", error);
    });
}

function updateCartQuantity(productId, change) {
  fetch("update-cart.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `product_id=${productId}&change=${change}`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        loadCartItems(); // Reload cart items
      } else {
        console.error("Error updating cart quantity:", data.message);
        // Show error message to user
        if (
          data.error &&
          data.error.includes("Cannot exceed available stock")
        ) {
          alert(data.error);
        }
      }
    })
    .catch((error) => {
      console.error("Error updating cart quantity:", error);
    });
}

function removeCartItem(productId) {
  fetch("update-cart.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `product_id=${productId}&action=remove`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        loadCartItems(); // Reload cart items
      } else {
        console.error("Error removing cart item:", data.message);
      }
    })
    .catch((error) => {
      console.error("Error removing cart item:", error);
    });
}

function updateCartTotal(total) {
  const cartTotal = document.getElementById("cartTotal");
  if (cartTotal) {
    cartTotal.textContent = `₱${parseFloat(total).toLocaleString()}`;
  }
}

function updateCartBadge(count) {
  // Update cart badge in header if it exists
  const cartBadge = document.querySelector(".cart-badge");
  if (cartBadge) {
    cartBadge.textContent = count;
    cartBadge.style.display = count > 0 ? "block" : "none";
  }
}

function updateCartStockTotal(items) {
  // This function can be used for additional cart calculations if needed
  console.log("Cart stock total updated:", items);
}

function updateCartCount(count) {
  const cartCount = document.getElementById("cartCount");
  if (cartCount) {
    cartCount.textContent = `${count} item${count !== 1 ? "s" : ""}`;
  }
}

function loadCartCount() {
  fetch("get-cart-count.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        updateCartBadge(data.count);
        updateCartCount(data.count);
      }
    })
    .catch((error) => {
      console.error("Error loading cart count:", error);
    });
}

// Profile check function for index.php
function checkProfileCompletion() {
  return new Promise((resolve, reject) => {
    fetch("check-profile-completion.php")
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          if (!data.hasPhoneNumber) {
            // Show profile update reminder modal
            showProfileUpdateModal();
            resolve(false);
          } else {
            resolve(true);
          }
        } else {
          reject(new Error("Failed to check profile"));
        }
      })
      .catch((error) => {
        console.error("Error checking profile:", error);
        reject(error);
      });
  });
}

// Profile update reminder modal for index.php
function showProfileUpdateModal() {
  const modal = document.createElement("div");
  modal.className =
    "fixed inset-0 z-[9999] flex items-center justify-center backdrop-blur-lg";
  modal.style.background = "rgba(0, 0, 0, 0.5)";
  modal.innerHTML = `
        <div class="bg-white/95 backdrop-blur-xl rounded-3xl p-8 shadow-2xl border border-gray-200/60 max-w-md w-full mx-4 relative overflow-hidden">
            <div class="flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                    <i class="bx bx-user-plus text-2xl text-blue-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-3">Complete Your Profile</h3>
                <p class="text-gray-600 mb-6 leading-relaxed">
                    Please update your profile and add your phone number to continue with your purchase. 
                    This helps us contact you regarding your order.
                </p>
                <div class="flex space-x-3 w-full">
                    <button onclick="closeProfileModal()" class="flex-1 py-3 px-4 border-2 border-gray-300 text-gray-700 font-medium rounded-xl hover:border-gray-400 hover:bg-gray-50 transition-all duration-300">
                        Continue Shopping
                    </button>
                    <button onclick="goToProfile()" class="flex-1 py-3 px-4 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 transition-all duration-300">
                        Update Profile
                    </button>
                </div>
            </div>
        </div>
    `;
  document.body.appendChild(modal);
}

function closeProfileModal() {
  const modal = document.querySelector(".fixed.inset-0.z-\\[9999\\]");
  if (modal) {
    modal.remove();
  }
}

function goToProfile() {
  closeProfileModal();
  window.location.href = "profile.php";
}

function proceedToCheckout() {
  // Check profile completion first
  checkProfileCompletion()
    .then((profileComplete) => {
      if (profileComplete) {
        // Since index.php doesn't have a cart panel, redirect to shop page
        window.location.href = "shop.php";
      }
    })
    .catch((error) => {
      console.error("Error checking profile:", error);
      alert("Error checking profile completion");
    });
}
