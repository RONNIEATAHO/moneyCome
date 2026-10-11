// Suvan Ventures - Common JavaScript Functions
const API_BASE_URL = '../backend/api';

// API Helper Functions
async function apiCall(endpoint, action, method = 'GET', data = null) {
    try {
        const url = `${API_BASE_URL}/${endpoint}.php?action=${action}`;
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            }
        };

        if (data) {
            options.body = JSON.stringify({ action, ...data });
        }

        const response = await fetch(url, options);
        const result = await response.json();
        return result;
    } catch (error) {
        console.error('API Error:', error);
        return { success: false, message: 'Network error occurred' };
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
    return await apiCall('auth', 'logout', 'POST');
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
    
    if (auth.data && auth.data.logged_in) {
        const userRole = auth.data.user_role;
        const userName = auth.data.user_name;
        const adminHref = window.location.pathname.includes('/public/')
            ? '../backend/admin/admin-dashboard.html'
            : 'admin-dashboard.html';
        
        let authHTML = `
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
        authContainer.innerHTML = `
            <a href="login.html" style="color: var(--primary-light);">Login</a>
            <a href="login.html" style="color: var(--primary-light);">Sign Up</a>
        `;
    }
}

async function logoutAndRedirect() {
    await logoutUser();
    const isAdminPage = window.location.pathname.includes('/backend/admin/');
    window.location.href = isAdminPage ? '../../public/index.html' : 'index.html';
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', initializeNavbar);
