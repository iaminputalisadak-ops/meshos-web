# Meesho Clone - Online Shopping Platform

A modern, responsive e-commerce platform built with React.js that mimics the Meesho online shopping experience.

## Features

- 🏠 **Home Page** - Hero section, category browsing, and featured products
- 🛍️ **Product Catalog** - Browse products by category
- 📱 **Product Details** - Detailed product view with image gallery
- 🛒 **Shopping Cart** - Add, remove, and manage cart items
- 🔍 **Search Functionality** - Search products across the platform
- 📲 **Social Sharing** - Share products on WhatsApp, Facebook, and Twitter (key Meesho feature)
- 💳 **Cart Management** - Quantity controls, price calculations, and delivery charges
- 📱 **Responsive Design** - Works seamlessly on desktop, tablet, and mobile devices

## Tech Stack

- **React.js** - Frontend framework
- **React Router** - Navigation and routing
- **Context API** - State management for cart
- **CSS3** - Modern styling with responsive design
- **Font Awesome** - Icons

## Project Structure

```
src/
├── components/
│   ├── Header/
│   │   ├── Header.js
│   │   └── Header.css
│   └── ProductCard/
│       ├── ProductCard.js
│       └── ProductCard.css
├── pages/
│   ├── Home/
│   │   ├── Home.js
│   │   └── Home.css
│   ├── Category/
│   │   ├── Category.js
│   │   └── Category.css
│   ├── ProductDetail/
│   │   ├── ProductDetail.js
│   │   └── ProductDetail.css
│   └── Cart/
│       ├── Cart.js
│       └── Cart.css
├── context/
│   └── CartContext.js
├── data/
│   └── products.js
├── App.js
├── App.css
├── index.js
└── index.css
```

## Installation

1. Install dependencies:
```bash
npm install
```

2. Start the development server:
```bash
npm start
```

3. Open [http://localhost:3000](http://localhost:3000) to view it in the browser.

## Build for Production

```bash
npm run build
```

This creates an optimized production build in the `build` folder.

## Key Features Implementation

### Shopping Cart
- Persistent cart using localStorage
- Add/remove items
- Update quantities
- Real-time price calculations
- Free delivery on orders above ₹499

### Social Sharing
- Share products on WhatsApp
- Share on Facebook
- Share on Twitter
- Pre-filled messages with product details

### Responsive Design
- Mobile-first approach
- Adaptive layouts for all screen sizes
- Touch-friendly interface

## Future Enhancements

- User authentication
- Payment integration
- Order tracking
- Product reviews and ratings
- Wishlist functionality
- Advanced search and filters
- Product recommendations

## License

This project is for educational purposes.



