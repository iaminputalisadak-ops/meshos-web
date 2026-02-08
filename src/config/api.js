/**
 * API Configuration
 * Centralized API endpoint configuration
 */

// Backend API Base URL
// Change this if your backend is on a different URL
const API_BASE_URL = 'http://localhost/backend/api';

// Alternative: If using PHP built-in server on port 8000
// const API_BASE_URL = 'http://localhost:8000/api';

export const API_ENDPOINTS = {
  // Products
  PRODUCTS: `${API_BASE_URL}/products.php`,
  PRODUCT_BY_ID: (id) => `${API_BASE_URL}/products.php?id=${id}`,
  PRODUCTS_BY_CATEGORY: (category) => `${API_BASE_URL}/products.php?category=${category}`,
  
  // Categories
  CATEGORIES: `${API_BASE_URL}/categories.php`,
  
  // Cart
  CART: `${API_BASE_URL}/cart.php`,
  CART_ITEM: (id) => `${API_BASE_URL}/cart.php?id=${id}`,
  
  // Subscriptions
  SUBSCRIPTIONS: `${API_BASE_URL}/subscriptions.php`,
  SUBSCRIPTION_BY_USER: (userId) => `${API_BASE_URL}/subscriptions.php?user_id=${userId}`,
  
  // Admin
  ADMIN_LOGIN: `${API_BASE_URL}/admin/login.php`,
  ADMIN_AUTH: `${API_BASE_URL}/admin/auth.php`,
  ADMIN_PRODUCTS: `${API_BASE_URL}/admin/products.php`,
};

/**
 * Fetch wrapper with error handling
 */
export const apiRequest = async (url, options = {}) => {
  try {
    const response = await fetch(url, {
      ...options,
      headers: {
        'Content-Type': 'application/json',
        ...options.headers,
      },
      credentials: 'include', // Important for session-based auth
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();
    return data;
  } catch (error) {
    console.error('API Request Error:', error);
    throw error;
  }
};

export default API_ENDPOINTS;


