import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import Header from './components/Header/Header';
import Footer from './components/Footer/Footer';
import Home from './pages/Home/Home';
import Category from './pages/Category/Category';
import ProductDetail from './pages/ProductDetail/ProductDetail';
import Cart from './pages/Cart/Cart';
import Checkout from './pages/Checkout/Checkout';
import OrderSuccess from './pages/OrderSuccess/OrderSuccess';
import SubscriptionPlans from './pages/SubscriptionPlans/SubscriptionPlans';
import SubscriptionCheckout from './pages/SubscriptionCheckout/SubscriptionCheckout';
import Dashboard from './pages/Dashboard/Dashboard';
import PromoterRegister from './pages/Promoter/PromoterRegister';
import PromoterLogin from './pages/Promoter/PromoterLogin';
import PromoterDashboard from './pages/Promoter/PromoterDashboard';
import { CartProvider } from './context/CartContext';
import { SubscriptionProvider } from './context/SubscriptionContext';
import ErrorBoundary from './components/ErrorBoundary/ErrorBoundary';
import './App.css';

function App() {
  return (
    <ErrorBoundary>
      <SubscriptionProvider>
        <CartProvider>
          <Router>
            <div className="App">
              <Header />
              <Routes>
                <Route path="/" element={<Home />} />
                <Route path="/category/:categoryName" element={<Category />} />
                <Route path="/product/:productId" element={<ProductDetail />} />
                <Route path="/cart" element={<Cart />} />
                <Route path="/checkout" element={<Checkout />} />
                <Route path="/order-success" element={<OrderSuccess />} />
                <Route path="/subscription" element={<SubscriptionPlans />} />
                <Route path="/subscription/checkout/:planId" element={<SubscriptionCheckout />} />
                <Route path="/dashboard" element={<Dashboard />} />
                <Route path="/promoter/register" element={<PromoterRegister />} />
                <Route path="/promoter/login" element={<PromoterLogin />} />
                <Route path="/promoter/dashboard" element={<PromoterDashboard />} />
                <Route path="/supplier" element={<div className="container" style={{padding: '40px 20px', textAlign: 'center'}}><h1>Become a Supplier</h1><p>Join Meesho as a supplier and grow your business!</p></div>} />
                <Route path="/investor" element={<div className="container" style={{padding: '40px 20px', textAlign: 'center'}}><h1>Investor Relations</h1><p>Learn about Meesho's investor relations and financial information.</p></div>} />
              </Routes>
              <Footer />
            </div>
          </Router>
        </CartProvider>
      </SubscriptionProvider>
    </ErrorBoundary>
  );
}

export default App;

