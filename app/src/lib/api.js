const BASE_URL = import.meta.env.DEV 
    ? 'http://localhost:10076/wp-json' 
    : window.location.origin + '/wp-json';

let nonce = window.wpSettings?.nonce;
let refreshInterval;

async function refreshNonce() {
    try {
        const data = await apiFetch('/rk/refresh-nonce', {
         method: 'GET',
         headers: { 'Content-Type': 'application/json' }
     });
        if (data.nonce) {
            nonce = data.nonce;
        }
    } catch (error) {
        console.error('Failed to refresh nonce:', error);
    }
}

export function startNonceRefresh() {
    if (refreshInterval) return;
    
    refreshInterval = setInterval(refreshNonce, 10 * 60 * 1000);
}

async function apiFetch(endpoint, options = {}) {    
    const defaultHeaders = {
        'Accept': 'application/json',
        'X-WP-Nonce': nonce
    };

    const response = await fetch(`${BASE_URL}${endpoint}`, {
        ...options,
        credentials: 'include',
        headers: {
            ...defaultHeaders,
            ...options.headers
        }
    });

    // If we're expecting JSON, parse it and check for errors
    if (!options.headers?.Accept || options.headers.Accept === 'application/json') {
        const data = await response.json();
        if (!response.ok) {
            throw new Error(data.message || 'API call failed');
        }
        return data;
    }

    // For non-JSON responses (like PDF), return the response directly
    if (!response.ok) {
        throw new Error('API call failed');
    }
    return response;
}

export async function fetchData() {
    return apiFetch('/rk/data');
}

export async function saveProfile(profileData) {
    return apiFetch('/rk/profile', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(profileData)
    });
}

export async function createCustomer(customerData) {
    return apiFetch('/rk/customer', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(customerData)
    });
}

export async function updateCustomer(id, customerData) {
    return apiFetch(`/rk/customer/${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(customerData)
    });
}

export async function deleteCustomer(id) {
    return apiFetch(`/rk/customer/${id}`, {
        method: 'DELETE'
    });
}

export async function toggleInvoicePaid(id) {
    return apiFetch(`/rk/invoice/${id}/toggle-paid`, {
        method: 'POST'
    });
}

export async function createInvoice(invoiceData) {
    return apiFetch('/rk/invoice', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(invoiceData)
    });
}

export async function updateInvoice(id, invoiceData) {
    return apiFetch(`/rk/invoice/${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(invoiceData)
    });
}

export async function deleteInvoice(id) {
    return apiFetch(`/rk/invoice/${id}`, {
        method: 'DELETE'
    });
}

export async function downloadInvoicePdf(id) {
    const response = await apiFetch(`/rk/invoice/${id}/pdf`, {
        method: 'GET',
        headers: { 'Accept': 'application/pdf' }
    });

    const filename = response.headers.get('Content-Disposition')?.split('filename=')[1] || `invoice-${id}.pdf`;
    const blob = await response.blob();
    
    const objectUrl = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = objectUrl;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    setTimeout(() => window.URL.revokeObjectURL(objectUrl), 1000);
}

// Clean up on page unload
window.addEventListener('unload', () => {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});
