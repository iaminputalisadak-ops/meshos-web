/**
 * API Service
 * Handles all API calls to backend
 */

import { API_ENDPOINTS, apiRequest } from '../config/api';

export const productService = {
  // Get all products
  getAll: async (params = {}) => {
    const queryString = new URLSearchParams(params).toString();
    const url = queryString 
      ? `${API_ENDPOINTS.PRODUCTS}?${queryString}`
      : API_ENDPOINTS.PRODUCTS;
    return await apiRequest(url);
  },

  // Get product by ID
  getById: async (id) => {
    return await apiRequest(API_ENDPOINTS.PRODUCT_BY_ID(id));
  },

  // Get products by category
  getByCategory: async (category, params = {}) => {
    const queryParams = { category, ...params };
    const queryString = new URLSearchParams(queryParams).toString();
    return await apiRequest(`${API_ENDPOINTS.PRODUCTS}?${queryString}`);
  },

  // Search products
  search: async (searchTerm, params = {}) => {
    const queryParams = { search: searchTerm, ...params };
    const queryString = new URLSearchParams(queryParams).toString();
    return await apiRequest(`${API_ENDPOINTS.PRODUCTS}?${queryString}`);
  },
};

export const categoryService = {
  // Get all categories
  getAll: async () => {
    return await apiRequest(API_ENDPOINTS.CATEGORIES);
  },
};

export const cartService = {
  // Get cart items
  getCart: async (userId = null) => {
    const url = userId 
      ? `${API_ENDPOINTS.CART}?user_id=${userId}`
      : API_ENDPOINTS.CART;
    return await apiRequest(url);
  },

  // Add to cart
  addItem: async (productId, quantity = 1, userId = null) => {
    return await apiRequest(API_ENDPOINTS.CART, {
      method: 'POST',
      body: JSON.stringify({
        product_id: productId,
        quantity,
        user_id: userId,
      }),
    });
  },

  // Update cart item
  updateItem: async (cartId, quantity) => {
    return await apiRequest(API_ENDPOINTS.CART, {
      method: 'PUT',
      body: JSON.stringify({
        cart_id: cartId,
        quantity,
      }),
    });
  },

  // Remove from cart
  removeItem: async (cartId) => {
    return await apiRequest(`${API_ENDPOINTS.CART}?id=${cartId}`, {
      method: 'DELETE',
    });
  },
};

export const subscriptionService = {
  // Get user subscription
  getSubscription: async (userId) => {
    return await apiRequest(API_ENDPOINTS.SUBSCRIPTION_BY_USER(userId));
  },

  // Create subscription
  create: async (userId, planType, price, discountPercentage = 0) => {
    return await apiRequest(API_ENDPOINTS.SUBSCRIPTIONS, {
      method: 'POST',
      body: JSON.stringify({
        user_id: userId,
        plan_type: planType,
        price,
        discount_percentage: discountPercentage,
      }),
    });
  },

  // Cancel subscription
  cancel: async (subscriptionId) => {
    return await apiRequest(API_ENDPOINTS.SUBSCRIPTIONS, {
      method: 'PUT',
      body: JSON.stringify({
        subscription_id: subscriptionId,
        status: 'cancelled',
      }),
    });
  },
};

export const adminService = {
  // Admin login
  login: async (username, password) => {
    return await apiRequest(API_ENDPOINTS.ADMIN_LOGIN, {
      method: 'POST',
      body: JSON.stringify({ username, password }),
    });
  },

  // Check admin auth
  checkAuth: async () => {
    return await apiRequest(API_ENDPOINTS.ADMIN_AUTH);
  },

  // Get products (admin)
  getProducts: async () => {
    return await apiRequest(API_ENDPOINTS.ADMIN_PRODUCTS);
  },

  // Create product (admin)
  createProduct: async (productData) => {
    return await apiRequest(API_ENDPOINTS.ADMIN_PRODUCTS, {
      method: 'POST',
      body: JSON.stringify(productData),
    });
  },

  // Update product (admin)
  updateProduct: async (productData) => {
    return await apiRequest(API_ENDPOINTS.ADMIN_PRODUCTS, {
      method: 'PUT',
      body: JSON.stringify(productData),
    });
  },

  // Delete product (admin)
  deleteProduct: async (productId) => {
    return await apiRequest(`${API_ENDPOINTS.ADMIN_PRODUCTS}?id=${productId}`, {
      method: 'DELETE',
    });
  },
};


