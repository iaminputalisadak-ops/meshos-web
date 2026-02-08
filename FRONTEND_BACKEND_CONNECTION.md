# 🔗 Frontend-Backend Connection Guide

## ✅ What Was Done

### 1. Created API Service Layer
- **`src/config/api.js`** - API base URL configuration
- **`src/services/api.js`** - Complete API service with all endpoints
- **`src/hooks/useProducts.js`** - Custom hook for fetching products
- **`src/hooks/useCategories.js`** - Custom hook for fetching categories

### 2. Updated Components
- **`src/pages/Home/Home.js`** - Now fetches from API with fallback to local data
- **`src/pages/Category/Category.js`** - Now fetches from API with fallback
- **`src/pages/ProductDetail/ProductDetail.js`** - Now fetches from API with fallback

### 3. Features
- ✅ Automatic fallback to local data if API fails
- ✅ Loading states while fetching
- ✅ Error handling
- ✅ CORS properly configured in backend

---

## 🚀 How It Works

### API Endpoints Used

1. **Products API**
   - `GET /backend/api/products.php` - Get all products
   - `GET /backend/api/products.php?id={id}` - Get single product
   - `GET /backend/api/products.php?category={category}` - Get by category

2. **Categories API**
   - `GET /backend/api/categories.php` - Get all categories

3. **Cart API** (Ready for use)
   - `GET /backend/api/cart.php` - Get cart items
   - `POST /backend/api/cart.php` - Add to cart
   - `PUT /backend/api/cart.php` - Update cart
   - `DELETE /backend/api/cart.php` - Remove from cart

---

## 📝 Configuration

### API Base URL
The API base URL is configured in `src/config/api.js`:

```javascript
const API_BASE_URL = process.env.REACT_APP_API_URL || 'http://localhost/backend/api';
```

**To change the API URL:**
1. Create a `.env` file in the root directory
2. Add: `REACT_APP_API_URL=http://your-api-url/backend/api`
3. Restart the React development server

---

## 🔧 Setup Instructions

### Step 1: Start Backend
1. Make sure XAMPP is running (MySQL and Apache)
2. Run database setup: `http://localhost/backend/setup.php`
3. Verify API works: `http://localhost/backend/api/products.php`

### Step 2: Start Frontend
```bash
npm start
```

The React app will run on `http://localhost:3000`

### Step 3: Test Connection
1. Open browser console (F12)
2. Navigate to the home page
3. Check Network tab - you should see API calls to `/backend/api/`

---

## 🐛 Troubleshooting

### Issue: API calls failing
**Solution:**
1. Check if backend is running: `http://localhost/backend/api/products.php`
2. Check CORS settings in `backend/config/cors.php`
3. Check browser console for CORS errors

### Issue: No data showing
**Solution:**
1. Check if database has products: `http://localhost/phpmyadmin`
2. Run seed data script if needed
3. Check API response in browser Network tab

### Issue: CORS errors
**Solution:**
1. Make sure `backend/config/cors.php` includes your frontend URL
2. Check that Apache is running in XAMPP
3. Verify API base URL in `src/config/api.js`

---

## 📊 Data Flow

```
Frontend Component
    ↓
useProducts() / useCategories() Hook
    ↓
API Service (src/services/api.js)
    ↓
Backend API (backend/api/*.php)
    ↓
MySQL Database
```

---

## 🎯 Next Steps

1. **Populate Database**
   - Run seed data script to add sample products
   - Or use admin panel to add products

2. **Connect Cart**
   - Update `CartContext.js` to use `cartAPI` from services
   - Implement cart persistence

3. **Add Search**
   - Update search functionality to use API
   - Add search endpoint if needed

4. **Add Authentication**
   - Create user login/register
   - Connect to backend user API

---

## 📁 File Structure

```
src/
├── config/
│   └── api.js              # API configuration
├── services/
│   └── api.js              # API service layer
├── hooks/
│   ├── useProducts.js      # Products hook
│   └── useCategories.js    # Categories hook
└── pages/
    ├── Home/
    │   └── Home.js         # ✅ Updated to use API
    ├── Category/
    │   └── Category.js     # ✅ Updated to use API
    └── ProductDetail/
        └── ProductDetail.js # ✅ Updated to use API
```

---

## ✅ Status

- ✅ API Service Layer Created
- ✅ Products API Connected
- ✅ Categories API Connected
- ✅ Fallback to Local Data Working
- ✅ Loading States Added
- ✅ Error Handling Added
- ⏳ Cart API (Ready, not yet connected)
- ⏳ Search API (Ready, not yet connected)

---

**The frontend and backend are now connected!** 🎉

