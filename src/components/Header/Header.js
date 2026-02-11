import React, { useState } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { useCart } from '../../context/CartContext';
// import { useSubscription } from '../../context/SubscriptionContext'; // Reserved for future use
import './Header.css';

const Header = () => {
  const [searchQuery, setSearchQuery] = useState('');
  const navigate = useNavigate();
  const location = useLocation();
  const { getCartItemsCount } = useCart();
  // const { isSubscribed, subscription } = useSubscription(); // Reserved for future use
  
  // Get current category from URL
  const currentCategory = location.pathname.startsWith('/category/') 
    ? decodeURIComponent(location.pathname.split('/category/')[1])
    : null;

  const handleSearch = (e) => {
    e.preventDefault();
    if (searchQuery.trim()) {
      // Navigate to search results or filter products
      navigate(`/search?q=${encodeURIComponent(searchQuery)}`);
    }
  };

  return (
    <header className="header">
      <div className="header-top">
        <div className="container">
          <div className="header-top-content">
            <div className="logo">
              <Link to="/">
                <span className="logo-text">Meesho</span>
              </Link>
            </div>
            <form className="search-form" onSubmit={handleSearch}>
              <input
                type="text"
                className="search-input"
                placeholder="Try Saree, Kurti or Search by Product Code"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
              />
              <button type="submit" className="search-button">
                <i className="fas fa-search"></i>
              </button>
            </form>
            <div className="header-actions">
              <Link to="/supplier" className="header-link">Become a Supplier</Link>
              <Link to="/investor" className="header-link">Investor Relations</Link>
              <Link to="/dashboard" className="user-icon">
                <i className="fas fa-user"></i>
                <span className="icon-label">Profile</span>
              </Link>
              <Link to="/cart" className="cart-icon">
                <i className="fas fa-shopping-cart"></i>
                {getCartItemsCount() > 0 && (
                  <span className="cart-badge">{getCartItemsCount()}</span>
                )}
                <span className="icon-label">Cart</span>
              </Link>
            </div>
          </div>
        </div>
      </div>
      <div className="header-bottom">
        <div className="container">
          <nav className="nav-menu">
            <Link 
              to="/category/Popular" 
              className={`nav-item ${currentCategory === 'Popular' ? 'active' : ''}`}
            >
              Popular
            </Link>
            <Link 
              to="/category/Kurti, Saree & Lehenga" 
              className={`nav-item ${currentCategory === 'Kurti, Saree & Lehenga' ? 'active' : ''}`}
            >
              Kurti, Saree & Lehenga
            </Link>
            <Link 
              to="/category/Women Western" 
              className={`nav-item ${currentCategory === 'Women Western' ? 'active' : ''}`}
            >
              Women Western
            </Link>
            <Link 
              to="/category/Lingerie" 
              className={`nav-item ${currentCategory === 'Lingerie' ? 'active' : ''}`}
            >
              Lingerie
            </Link>
            <Link 
              to="/category/Men" 
              className={`nav-item ${currentCategory === 'Men' ? 'active' : ''}`}
            >
              Men
            </Link>
            <Link 
              to="/category/Kids & Toys" 
              className={`nav-item ${currentCategory === 'Kids & Toys' ? 'active' : ''}`}
            >
              Kids & Toys
            </Link>
            <Link 
              to="/category/Home & Kitchen" 
              className={`nav-item ${currentCategory === 'Home & Kitchen' ? 'active' : ''}`}
            >
              Home & Kitchen
            </Link>
            <Link 
              to="/category/Beauty & Health" 
              className={`nav-item ${currentCategory === 'Beauty & Health' ? 'active' : ''}`}
            >
              Beauty & Health
            </Link>
            <Link 
              to="/category/Jewellery & Accessories" 
              className={`nav-item ${currentCategory === 'Jewellery & Accessories' ? 'active' : ''}`}
            >
              Jewellery & Accessories
            </Link>
            <Link 
              to="/category/Bags & Footwear" 
              className={`nav-item ${currentCategory === 'Bags & Footwear' ? 'active' : ''}`}
            >
              Bags & Footwear
            </Link>
          </nav>
        </div>
      </div>
    </header>
  );
};

export default Header;

