
function showNotification(message, type = 'info', duration = 5000) {
    const toastContainer = document.getElementById('notificationContainer');
    if (!toastContainer) return;
    
    const toastId = 'toast-' + Date.now();
    const bgClass = type === 'error' ? 'bg-danger' : 
                    type === 'success' ? 'bg-success' : 
                    type === 'warning' ? 'bg-warning' : 'bg-info';
    
    const toast = document.createElement('div');
    toast.className = `toast ${bgClass} text-white`;
    toast.setAttribute('id', toastId);
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    toast.innerHTML = `
        <div class="toast-header">
            <strong class="me-auto">${type.charAt(0).toUpperCase() + type.slice(1)}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            ${message}
        </div>
    `;
    
    toastContainer.appendChild(toast);
    
    const toastInstance = new bootstrap.Toast(toast, {
        delay: duration
    });
    
    toastInstance.show();
    
    
    toast.addEventListener('hidden.bs.toast', function () {
        toast.remove();
    });
}


function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', { 
        day: '2-digit', 
        month: '2-digit', 
        year: 'numeric' 
    });
}


function formatMoney(amount) {
    if (amount === null || amount === undefined) return '0,00 €';
    return new Intl.NumberFormat('fr-FR', { 
        style: 'currency', 
        currency: 'EUR' 
    }).format(amount);
}


function getAuthToken() {
    const cookies = document.cookie.split('; ');
    const tokenCookie = cookies.find(row => row.startsWith('prestataire_token'));
    return tokenCookie ? tokenCookie.split('=')[1] : null;
}


async function callApi(endpoint, method = 'GET', data = null) {
    const token = getAuthToken();
    
    const options = {
        method,
        headers: {
            'Content-Type': 'application/json',
            'Authorization': token ? `Bearer ${token}` : ''
        }
    };
    
    if (data && (method === 'POST' || method === 'PUT' || method === 'PATCH')) {
        options.body = JSON.stringify(data);
    }
    
    try {
        const response = await fetch(endpoint, options);
        
        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.error || 'Une erreur est survenue');
        }
        
        return await response.json();
    } catch (error) {
        console.error('API call error:', error);
        showNotification(error.message, 'error');
        throw error;
    }
}


document.addEventListener('DOMContentLoaded', function() {
    console.log('Espace prestataire chargé');
});
