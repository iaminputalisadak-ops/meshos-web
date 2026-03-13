/**
 * API Configuration
 * Base URL for backend API
 */

// Use /api so the React dev server (proxy in package.json) forwards to http://localhost:8888/api
// You must run the backend first: npm run backend (or double-click backend/start-server.bat)
const API_BASE_URL = process.env.REACT_APP_API_URL || '/api';

export default API_BASE_URL;
