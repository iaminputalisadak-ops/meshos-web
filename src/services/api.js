/**
 * API Service
 * Handles all API calls to the backend
 */

import API_BASE_URL from '../config/api';

/**
 * Generic fetch function with error handling
 */
async function fetchAPI(endpoint, options = {}) {
  const url = `${API_BASE_URL}/${endpoint}`;
  
  const defaultOptions = {
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
    ...options,
  };

  try {
    const response = await fetch(url, defaultOptions);
    
    // Check if response is ok
    if (!response.ok) {
      const errorData = await response.json().catch(() => ({ message: 'Network error' }));
      throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
    }
    
    const data = await response.json();
    
    if (!data.success) {
      throw new Error(data.message || 'API request failed');
    }
    
    return data;
  } catch (error) {
    console.error('API Error:', error);
    throw error;
  }
}

/**
 * Products API
 */
export const productsAPI = {
  /**
   * Get all products
   */
  getAll: async (search = null, limit = 50, offset = 0) => {
    let endpoint = `products.php?limit=${limit}&offset=${offset}`;
    if (search) {
      endpoint += `&search=${encodeURIComponent(search)}`;
    }
    const response = await fetchAPI(endpoint);
    return response.data || [];
  },

  /**
   * Get product by ID
   */
  getById: async (id) => {
    const response = await fetchAPI(`products.php?id=${id}`);
    return response.data;
  },

  /**
   * Get products by category
   */
  getByCategory: async (category, search = null, limit = 50, offset = 0) => {
    let endpoint = `products.php?category=${encodeURIComponent(category)}&limit=${limit}&offset=${offset}`;
    if (search) {
      endpoint += `&search=${encodeURIComponent(search)}`;
    }
    const response = await fetchAPI(endpoint);
    return response.data || [];
  },
};

/**
 * Categories API
 */
export const categoriesAPI = {
  /**
   * Get all categories
   */
  getAll: async () => {
    const response = await fetchAPI('categories.php');
    return response.data || [];
  },
};

/**
 * Cart API
 */
export const cartAPI = {
  /**
   * Get cart items
   */
  get: async (sessionId) => {
    const response = await fetchAPI(`cart.php?session_id=${sessionId}`);
    return response.data || [];
  },

  /**
   * Add item to cart
   */
  add: async (productId, quantity = 1, sessionId = null) => {
    const response = await fetchAPI('cart.php', {
      method: 'POST',
      body: JSON.stringify({
        product_id: productId,
        quantity: quantity,
        session_id: sessionId,
      }),
    });
    return response.data;
  },

  /**
   * Update cart item
   */
  update: async (cartId, quantity) => {
    const response = await fetchAPI('cart.php', {
      method: 'PUT',
      body: JSON.stringify({
        id: cartId,
        quantity: quantity,
      }),
    });
    return response.data;
  },

  /**
   * Remove item from cart
   */
  remove: async (cartId) => {
    const response = await fetchAPI(`cart.php?id=${cartId}`, {
      method: 'DELETE',
    });
    return response.data;
  },
};

/**
 * Subscriptions API
 */
export const subscriptionsAPI = {
  /**
   * Get all subscription plans
   */
  getPlans: async () => {
    const response = await fetchAPI('subscriptions.php');
    return response.data || [];
  },

  /**
   * Create subscription
   */
  create: async (planData) => {
    const response = await fetchAPI('subscriptions.php', {
      method: 'POST',
      body: JSON.stringify(planData),
    });
    return response.data;
  },
};

export default {
  products: productsAPI,
  categories: categoriesAPI,
  cart: cartAPI,
  subscriptions: subscriptionsAPI,
};
