// Suvan Ventures - Common JavaScript Functions
const API_BASE_URL = window.location.pathname.includes('/backend/admin/')
    ? '../api'
    : '../backend/api';

// API Helper Functions
async function apiCall(endpoint, action, method = 'GET', data = null) {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 30000);

    try {
        const url = `${API_BASE_URL}/${endpoint}.php?action=${action}`;
        const options = {
            method: method,
            headers: {}
        };

        if (method !== 'GET') {
            if (data instanceof FormData) {
                data.set('action', action);
                options.body = data;
            } else {
                options.headers['Content-Type'] = 'application/json';
                options.body = JSON.stringify({ action, ...(data || {}) });
            }
        }

        const response = await fetch(url, {
            ...options,
            cache: 'no-store',
            signal: controller.signal
        });
        const responseText = await response.text();
        let result;
        try {
            result = JSON.parse(responseText);
        } catch (parseError) {
            throw new Error(`API ${endpoint} returned HTTP ${response.status} with an invalid response.`);
        }
        if (!response.ok && result.success) {
            result.success = false;
            result.message = `API request failed with HTTP ${response.status}.`;
        }
        return result;
    } catch (error) {
        console.error('API Error:', error);
        const message = error.name === 'AbortError'
            ? `API request to ${endpoint} timed out.`
            : error.message || 'Network error occurred';
        return { success: false, message };
    } finally {
        clearTimeout(timeoutId);
    }
}

// Auth Functions
async function loginUser(email, password) {
    return await apiCall('auth', 'login', 'POST', { email, password });
}

async function registerUser(email, password, first_name, last_name, phone) {
    return await apiCall('auth', 'register', 'POST', { 
        email, password, first_name, last_name, phone 
    });
}

async function logoutUser() {
    return await apiCall('auth', 'logout', 'POST', {});
}

async function checkAuth() {
    return await apiCall('auth', 'check_auth', 'GET');
}

async function getUserProfile() {
    return await apiCall('auth', 'get_user_profile', 'GET');
}

async function updateProfile(data) {
    return await apiCall('auth', 'update_profile', 'POST', data);
}

// Products Functions
async function getAllProducts() {
    return await apiCall('products', 'get_all', 'GET');
}

async function getAllProductsForAdmin() {
    return await apiCall('products', 'get_all_admin', 'GET');
}

function getProductImageUrl(product) {
    if (product.image_url) {
        return product.image_url;
    }

    const fallbackImages = [
        'images/WhatsApp Image 2026-10-08 at 16.40.51 (1).jpeg',
        'images/WhatsApp Image 2026-10-08 at 16.40.49 (1).jpeg',
        'images/WhatsApp Image 2026-10-08 at 16.40.58 (1).jpeg'
    ];
    const productId = Number(product.id) || 0;
    return fallbackImages[productId % fallbackImages.length];
}

async function getProduct(id) {
    return await apiCall('products', 'get_single', 'GET', null, `&id=${id}`);
}

async function addProduct(data) {
    return await apiCall('products', 'add_product', 'POST', data);
}

async function updateProduct(data) {
    return await apiCall('products', 'update_product', 'POST', data);
}

async function deleteProduct(id) {
    return await apiCall('products', 'delete_product', 'POST', { id });
}

// Services Functions
async function getAllServices() {
    return await apiCall('services', 'get_all', 'GET');
}

async function getAllServicesForAdmin() {
    return await apiCall('services', 'get_all_admin', 'GET');
}

function getServiceImageUrl(service) {
    if (service.image_url) {
        return service.image_url;
    }

    const fallbackImages = [
        'images/web.png',
        'images/networkingSolutios.jpeg',
        'images/securityCamera.jpeg'
    ];
    const serviceId = Number(service.id) || 0;
    return fallbackImages[serviceId % fallbackImages.length];
}

async function getService(id) {
    return await apiCall('services', 'get_single', 'GET', null, `&id=${id}`);
}

async function addService(data) {
    return await apiCall('services', 'add_service', 'POST', data);
}

async function updateService(data) {
    return await apiCall('services', 'update_service', 'POST', data);
}

async function deleteService(id) {
    return await apiCall('services', 'delete_service', 'POST', { id });
}

// Cart Functions
async function addToCart(type, itemId, quantity, price) {
    const data = { quantity, price, type };
    if (type === 'product') {
        data.product_id = itemId;
    } else {
        data.service_id = itemId;
    }
    return await apiCall('cart', 'add_to_cart', 'POST', data);
}

async function getCart() {
    return await apiCall('cart', 'get_cart', 'GET');
}

async function removeFromCart(cartId) {
    return await apiCall('cart', 'remove_from_cart', 'POST', { cart_id: cartId });
}

async function updateCartQuantity(cartId, quantity) {
    return await apiCall('cart', 'update_cart_quantity', 'POST', { cart_id: cartId, quantity });
}

async function clearCart() {
    return await apiCall('cart', 'clear_cart', 'POST');
}

// Orders Functions
async function checkout() {
    return await apiCall('orders', 'checkout', 'POST');
}

async function getOrders() {
    return await apiCall('orders', 'get_orders', 'GET');
}

async function getOrderDetails(orderId) {
    const url = `${API_BASE_URL}/orders.php?action=get_order_details&order_id=${orderId}`;
    try {
        const response = await fetch(url);
        return await response.json();
    } catch (error) {
        console.error('Error fetching order details:', error);
        return { success: false, message: 'Error fetching order details' };
    }
}

// Payments Functions
async function processPayment(orderId, amount, paymentMethod, transactionId = '') {
    return await apiCall('payments', 'process_payment', 'POST', { 
        order_id: orderId, 
        amount, 
        payment_method: paymentMethod, 
        transaction_id: transactionId 
    });
}

async function getPaymentStatus(orderId) {
    const url = `${API_BASE_URL}/payments.php?action=get_payment_status&order_id=${orderId}`;
    try {
        const response = await fetch(url);
        return await response.json();
    } catch (error) {
        console.error('Error fetching payment status:', error);
        return { success: false };
    }
}

// Appointments Functions
async function bookAppointment(serviceId, appointmentDate, appointmentTime, notes = '') {
    return await apiCall('appointments', 'book_appointment', 'POST', { 
        service_id: serviceId, 
        appointment_date: appointmentDate, 
        appointment_time: appointmentTime, 
        notes 
    });
}

async function getAppointments() {
    return await apiCall('appointments', 'get_appointments', 'GET');
}

async function cancelAppointment(appointmentId) {
    return await apiCall('appointments', 'cancel_appointment', 'POST', { appointment_id: appointmentId });
}

async function getAllAppointments() {
    return await apiCall('appointments', 'get_all_appointments', 'GET');
}

async function updateAppointmentStatus(appointmentId, status) {
    return await apiCall('appointments', 'update_appointment_status', 'POST', { 
        appointment_id: appointmentId, 
        status 
    });
}

// Inquiries Functions
async function submitInquiry(name, email, phone, subject, message, serviceType = '') {
    return await apiCall('inquiries', 'submit_inquiry', 'POST', { 
        name, email, phone, subject, message, service_type: serviceType 
    });
}

async function getAllInquiries() {
    return await apiCall('inquiries', 'get_all_inquiries', 'GET');
}

async function getInquiry(id) {
    const url = `${API_BASE_URL}/inquiries.php?action=get_inquiry&id=${id}`;
    try {
        const response = await fetch(url);
        return await response.json();
    } catch (error) {
        console.error('Error fetching inquiry:', error);
        return { success: false };
    }
}

async function updateInquiryStatus(id, status) {
    return await apiCall('inquiries', 'update_inquiry_status', 'POST', { id, status });
}

// Admin Dashboard Functions
async function getDashboardStats() {
    return await apiCall('admin', 'get_dashboard_stats', 'GET');
}

async function getAllUsers() {
    return await apiCall('admin', 'get_all_users', 'GET');
}

// DOM Helper Functions
function showAlert(message, type = 'success') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    document.body.insertBefore(alertDiv, document.body.firstChild);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('show');
    }
}

function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
    }
}

function showLoading(containerId) {
    const container = document.getElementById(containerId);
    if (container) {
        container.innerHTML = '<div class="spinner"></div>';
    }
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

function formatServiceCurrency(amount) {
    return new Intl.NumberFormat('en-UG', {
        style: 'currency',
        currency: 'UGX',
        maximumFractionDigits: 0
    }).format(amount);
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function formatDateTime(dateString) {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Initialize Navbar
async function initializeNavbar() {
    const auth = await checkAuth();
    const authContainer = document.querySelector('.navbar-auth');
    if (!authContainer) return;

    const isAdminPage = window.location.pathname.includes('/backend/admin/');
    const isHomePage = /\/public\/(?:index\.html)?$/.test(window.location.pathname);
    const cartLink = isAdminPage || isHomePage || !auth.data || !auth.data.logged_in ? '' : `
        <a href="cart.html" class="navbar-cart-link" aria-label="View cart">
            <span aria-hidden="true">🛒</span> Cart
            <span class="navbar-cart-count" hidden>0</span>
        </a>
    `;
    
    if (auth.data && auth.data.logged_in) {
        const userRole = auth.data.user_role;
        const userName = auth.data.user_name;
        const adminHref = window.location.pathname.includes('/public/')
            ? '../backend/admin/admin-dashboard.html'
            : 'admin-dashboard.html';
        
        let authHTML = `${cartLink}
            <span class="text-muted">${userName}</span>
        `;
        
        if (userRole === 'admin' || userRole === 'system_admin') {
            authHTML += `
                <a href="${adminHref}" class="navbar-menu" style="display: inline;">Admin Panel</a>
            `;
        }
        
        authHTML += `
            <a href="#" onclick="logoutAndRedirect()" style="color: var(--primary-light);">Logout</a>
        `;
        
        authContainer.innerHTML = authHTML;
    } else {
        const homeHref = window.location.pathname.includes('/backend/admin/')
            ? '../../public/index.html'
            : 'index.html';
        authContainer.innerHTML = `
            ${cartLink}
            <a href="${homeHref}?auth=login" style="color: var(--primary-light);">Login</a>
            <a href="${homeHref}?auth=signup" style="color: var(--primary-light);">Sign Up</a>
        `;
    }

    if (auth.data && auth.data.logged_in) {
        await refreshNavbarCartCount();
    }
}

async function refreshNavbarCartCount() {
    const countBadge = document.querySelector('.navbar-cart-count');
    if (!countBadge) return;

    const result = await getCart();
    if (!result.success || !result.data || !Array.isArray(result.data.items)) {
        console.error('Unable to refresh cart count:', result.message || 'Invalid cart response');
        return;
    }

    const itemCount = result.data.items.reduce((count, item) => count + Number(item.quantity || 0), 0);
    countBadge.textContent = itemCount;
    countBadge.hidden = itemCount === 0;
}

async function logoutAndRedirect() {
    const result = await logoutUser();
    if (!result.success) {
        showAlert(result.message || 'Unable to sign out. Please try again.', 'danger');
        return;
    }

    sessionStorage.clear();
    const isAdminPage = window.location.pathname.includes('/backend/admin/');
    const homeHref = isAdminPage ? '../../public/index.html' : 'index.html';
    window.location.replace(homeHref);
}

window.addEventListener('pageshow', (event) => {
    if (event.persisted) {
        window.location.reload();
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', initializeNavbar);
