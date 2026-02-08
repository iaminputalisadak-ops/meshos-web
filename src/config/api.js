/**
 * API Configuration
 * Base URL for backend API
 */

// For development - React runs on port 3000, backend on port 80 (XAMPP)
// For production, update this to your production API URL
const API_BASE_URL = process.env.REACT_APP_API_URL || 'http://localhost/backend/api';

export default API_BASE_URL;
