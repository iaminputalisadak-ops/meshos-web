/**
 * API Configuration
 * Base URL for backend API
 */

// Use relative URL so React dev server (package.json "proxy") forwards to Apache on port 80.
// Backend must be at http://localhost/Shopping/backend/api (XAMPP). Override with REACT_APP_API_URL in .env if needed.
const API_BASE_URL = process.env.REACT_APP_API_URL || '/Shopping/backend/api';

export default API_BASE_URL;
