import React, { useState, useRef } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { categories, products, brands } from '../../data/products';
import { useSubscription } from '../../context/SubscriptionContext';
import ProductCard from '../../components/ProductCard/ProductCard';
import './Home.css';

const Home = () => {
  const navigate = useNavigate();
  const featuredProducts = products.slice(0, 12);
  const trendingProducts = products.slice(4, 16);
  // Original Brands with proper category mapping
  const originalBrandsData = [
    {
      id: 1,
      name: 'NIVEA Body Milk',
      category: 'Personal Care',
      image: 'https://images.unsplash.com/photo-1556229010-6c3f2c9ca5f8?w=400&q=80',
      product: products.find(p => p.name.includes('NIVEA')) || products[3]
    },
    {
      id: 2,
      name: 'JBL Headphones',
      category: 'Electronics',
      image: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&q=80',
      product: products.find(p => p.name.includes('JBL')) || products[4]
    },
    {
      id: 3,
      name: 'Lipstick Set',
      category: 'Makeup',
      image: 'https://images.unsplash.com/photo-1586495777744-4413f21062fa?w=400&q=80',
      product: products.find(p => p.name.includes('Lipstick') || p.name.includes('Lip')) || products[1]
    },
    {
      id: 4,
      name: 'OPPO A59 5G',
      category: 'Smart Phones',
      image: 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=400&q=80',
      product: products.find(p => p.name.includes('OPPO')) || products[5]
    },
    {
      id: 5,
      name: 'DENVER HAMILTON',
      category: 'Men Perfume',
      image: 'https://images.unsplash.com/photo-1541643600914-78b084683601?w=400&q=80',
      product: products.find(p => p.name.includes('DENVER')) || products[22]
    },
  ];
  const { isSubscribed, subscription } = useSubscription();
  const [currentBanner, setCurrentBanner] = useState(0);
  const brandsScrollRef = useRef(null);
  const categoriesScrollRef = useRef(null);
  const brandsLogosScrollRef = useRef(null);
  const originalBrandsScrollRef = useRef(null);
  const [isCategoriesHovered, setIsCategoriesHovered] = useState(false);
  const [isBrandsHovered, setIsBrandsHovered] = useState(false);
  const [isOriginalBrandsHovered, setIsOriginalBrandsHovered] = useState(false);

  const banners = [
    {
      id: 1,
      title: 'Upto 35% OFF on your first order',
      subtitle: '*Only on App',
      image: 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=1200',
      cta: 'Shop Now',
      showQR: true,
      gradient: 'linear-gradient(135deg, #6a1b9a 0%, #8e24aa 100%)',
    },
    {
      id: 2,
      title: 'Start Your Online Business',
      subtitle: 'Zero Investment | Zero Risk | Earn from Home',
      image: 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=1200',
      cta: 'Start Selling Now',
      showQR: false,
      gradient: 'linear-gradient(135deg, #f43397 0%, #e02885 100%)',
    },
    {
      id: 3,
      title: 'Biggest Sale of the Year',
      subtitle: 'Up to 70% OFF on Fashion & Lifestyle',
      image: 'https://images.unsplash.com/photo-1445205170230-053b83016050?w=1200',
      cta: 'Shop Now',
      showQR: false,
      gradient: 'linear-gradient(135deg, #4a148c 0%, #6a1b9a 100%)',
    },
    {
      id: 4,
      title: 'New Arrivals - Latest Fashion',
      subtitle: 'Trending Styles | Free Delivery',
      image: 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=1200',
      cta: 'Explore Now',
      showQR: true,
      gradient: 'linear-gradient(135deg, #8e24aa 0%, #ab47bc 100%)',
    },
    {
      id: 5,
      title: 'Premium Brands Collection',
      subtitle: 'Authentic Products | Best Prices',
      image: 'https://images.unsplash.com/photo-1445205170230-053b83016050?w=1200',
      cta: 'Shop Now',
      showQR: false,
      gradient: 'linear-gradient(135deg, #7b1fa2 0%, #9c27b0 100%)',
    },
  ];

  React.useEffect(() => {
    const timer = setInterval(() => {
      setCurrentBanner((prev) => (prev + 1) % banners.length);
    }, 5000);
    return () => clearInterval(timer);
  }, [banners.length]);

  // Auto-scroll categories
  React.useEffect(() => {
    if (!categoriesScrollRef.current || isCategoriesHovered) return;

    const scrollContainer = categoriesScrollRef.current;
    let scrollPosition = 0;
    const scrollSpeed = 0.5; // pixels per frame
    const maxScroll = scrollContainer.scrollWidth - scrollContainer.clientWidth;

    const autoScroll = () => {
      if (scrollPosition < maxScroll) {
        scrollPosition += scrollSpeed;
        scrollContainer.scrollLeft = scrollPosition;
      } else {
        // Reset to start for continuous loop
        scrollPosition = 0;
        scrollContainer.scrollLeft = 0;
      }
    };

    const interval = setInterval(autoScroll, 20); // Smooth scrolling

    return () => clearInterval(interval);
  }, [isCategoriesHovered]);

  // Auto-scroll brand logos
  React.useEffect(() => {
    if (!brandsLogosScrollRef.current || isBrandsHovered) return;

    const scrollContainer = brandsLogosScrollRef.current;
    let scrollPosition = 0;
    const scrollSpeed = 0.3; // pixels per frame (slower than categories)
    const maxScroll = scrollContainer.scrollWidth - scrollContainer.clientWidth;

    const autoScroll = () => {
      if (scrollPosition < maxScroll) {
        scrollPosition += scrollSpeed;
        scrollContainer.scrollLeft = scrollPosition;
      } else {
        // Reset to start for continuous loop
        scrollPosition = 0;
        scrollContainer.scrollLeft = 0;
      }
    };

    const interval = setInterval(autoScroll, 20); // Smooth scrolling

    return () => clearInterval(interval);
  }, [isBrandsHovered]);

  // Auto-scroll Original Brands
  React.useEffect(() => {
    if (!originalBrandsScrollRef.current || isOriginalBrandsHovered) return;

    const scrollContainer = originalBrandsScrollRef.current;
    let scrollPosition = 0;
    const scrollSpeed = 0.4; // pixels per frame
    const maxScroll = scrollContainer.scrollWidth - scrollContainer.clientWidth;

    const autoScroll = () => {
      if (scrollPosition < maxScroll) {
        scrollPosition += scrollSpeed;
        scrollContainer.scrollLeft = scrollPosition;
      } else {
        // Reset to start for continuous loop
        scrollPosition = 0;
        scrollContainer.scrollLeft = 0;
      }
    };

    const interval = setInterval(autoScroll, 20); // Smooth scrolling

    return () => clearInterval(interval);
  }, [isOriginalBrandsHovered]);

  const currentBannerData = banners[currentBanner];

  return (
    <div className="home">
      {/* Main Hero Banner with QR Code - Auto-sliding */}
      <section 
        className="hero-banner-section"
        style={{ background: currentBannerData.gradient }}
      >
        <div className="hero-banner-container">
          <div className="hero-banner-content">
            <div className="hero-banner-left">
              <h1 className="hero-banner-title">{currentBannerData.title}</h1>
              <p className="hero-banner-subtitle">{currentBannerData.subtitle}</p>
              {currentBannerData.showQR && (
                <div className="qr-code-container">
                  <div className="qr-code-placeholder">
                    <i className="fas fa-qrcode"></i>
                  </div>
                  <p className="qr-code-text">Scan now to install</p>
                </div>
              )}
            </div>
            <div className="hero-banner-right">
              <div className="hero-banner-slogan">
                <h2>Smart Shopping</h2>
                <h3>Trusted by Millions</h3>
                <button 
                  className="hero-shop-btn"
                  onClick={() => navigate('/category/Popular')}
                >
                  {currentBannerData.cta}
                </button>
              </div>
            </div>
          </div>
        </div>
        {/* Banner Indicators */}
        <div className="banner-indicators">
          {banners.map((banner, index) => (
            <button
              key={banner.id}
              className={`banner-indicator ${index === currentBanner ? 'active' : ''}`}
              onClick={() => setCurrentBanner(index)}
              aria-label={`Go to banner ${index + 1}`}
            />
          ))}
        </div>
      </section>

      {/* Features Strip */}
      <section className="features-strip">
        <div className="container">
          <div className="features-strip-content">
            <div className="feature-strip-item">
              <div className="feature-icon-wrapper">
                <i className="fas fa-gift"></i>
              </div>
              <span>7 Days Easy Return</span>
            </div>
            <div className="feature-strip-item">
              <div className="feature-icon-wrapper">
                <i className="fas fa-money-bill-wave"></i>
              </div>
              <span>Cash on Delivery</span>
            </div>
            <div className="feature-strip-item">
              <div className="feature-icon-wrapper">
                <i className="fas fa-tag"></i>
              </div>
              <span>Lowest Prices</span>
            </div>
          </div>
        </div>
      </section>

      {/* Categories Section - Horizontal Scroll with Auto-scroll */}
      <section className="categories-section">
        <div className="container">
          <div 
            className="categories-scroll"
            ref={categoriesScrollRef}
            onMouseEnter={() => setIsCategoriesHovered(true)}
            onMouseLeave={() => setIsCategoriesHovered(false)}
          >
            {categories.map((category) => (
              <Link
                key={category.id}
                to={`/category/${category.name}`}
                className="category-item"
              >
                <div className="category-icon-wrapper">
                  {category.image ? (
                    <img 
                      src={category.image} 
                      alt={category.name}
                      className="category-image"
                      onError={(e) => {
                        e.target.style.display = 'none';
                        const iconSpan = e.target.parentElement.querySelector('.category-icon');
                        if (iconSpan) iconSpan.style.display = 'block';
                      }}
                    />
                  ) : null}
                  <span 
                    className="category-icon" 
                    style={{display: category.image ? 'none' : 'block'}}
                  >
                    {category.icon}
                  </span>
                </div>
                <span className="category-label">{category.name}</span>
              </Link>
            ))}
            {/* Duplicate categories for seamless loop */}
            {categories.map((category) => (
              <Link
                key={`duplicate-${category.id}`}
                to={`/category/${category.name}`}
                className="category-item"
              >
                <div className="category-icon-wrapper">
                  {category.image ? (
                    <img 
                      src={category.image} 
                      alt={category.name}
                      className="category-image"
                      onError={(e) => {
                        e.target.style.display = 'none';
                        const iconSpan = e.target.parentElement.querySelector('.category-icon');
                        if (iconSpan) iconSpan.style.display = 'block';
                      }}
                    />
                  ) : null}
                  <span 
                    className="category-icon" 
                    style={{display: category.image ? 'none' : 'block'}}
                  >
                    {category.icon}
                  </span>
                </div>
                <span className="category-label">{category.name}</span>
              </Link>
            ))}
          </div>
        </div>
      </section>

      {/* Top Deals Section */}
      <section className="products-section">
        <div className="container">
          <div className="section-header">
            <h2 className="section-title">Top Deals</h2>
            <Link to="/category/Popular" className="view-all-link">
              View All <i className="fas fa-chevron-right"></i>
            </Link>
          </div>
          <div className="products-grid">
            {featuredProducts.slice(0, 8).map((product) => (
              <ProductCard key={product.id} product={product} />
            ))}
          </div>
        </div>
      </section>

      {/* Original Brands Section - With Product Images and Auto-scroll */}
      <section className="original-brands-section">
        <div className="container">
          <div className="section-header">
            <h2 className="section-title">
              Original Brands
              <i className="fas fa-check-circle brand-verified"></i>
            </h2>
            <Link to="/category/Electronics" className="view-all-link">
              VIEW ALL <i className="fas fa-chevron-right"></i>
            </Link>
          </div>
          <div 
            className="brands-products-scroll"
            ref={originalBrandsScrollRef}
            onMouseEnter={() => setIsOriginalBrandsHovered(true)}
            onMouseLeave={() => setIsOriginalBrandsHovered(false)}
          >
            {originalBrandsData.map((brand) => (
              <Link
                key={brand.id}
                to={brand.product ? `/product/${brand.product.id}` : '#'}
                className="original-brand-card"
              >
                <div className="original-brand-image-wrapper">
                  <img 
                    src={brand.image || brand.product?.image || 'https://via.placeholder.com/200'} 
                    alt={brand.name}
                    className="original-brand-image"
                    onError={(e) => {
                      if (e.target.src !== brand.product?.image && brand.product?.image) {
                        e.target.src = brand.product.image;
                      } else {
                        e.target.src = 'https://via.placeholder.com/200x200/f5f5f5/999999?text=' + encodeURIComponent(brand.name);
                      }
                    }}
                    loading="lazy"
                    crossOrigin="anonymous"
                  />
                </div>
                <div className="original-brand-category-bar">
                  {brand.category}
                </div>
              </Link>
            ))}
            {/* Duplicate for seamless loop */}
            {originalBrandsData.map((brand) => (
              <Link
                key={`duplicate-${brand.id}`}
                to={brand.product ? `/product/${brand.product.id}` : '#'}
                className="original-brand-card"
              >
                <div className="original-brand-image-wrapper">
                  <img 
                    src={brand.image || brand.product?.image || 'https://via.placeholder.com/200'} 
                    alt={brand.name}
                    className="original-brand-image"
                    onError={(e) => {
                      if (e.target.src !== brand.product?.image && brand.product?.image) {
                        e.target.src = brand.product.image;
                      } else {
                        e.target.src = 'https://via.placeholder.com/200x200/f5f5f5/999999?text=' + encodeURIComponent(brand.name);
                      }
                    }}
                    loading="lazy"
                    crossOrigin="anonymous"
                  />
                </div>
                <div className="original-brand-category-bar">
                  {brand.category}
                </div>
              </Link>
            ))}
          </div>
        </div>
      </section>

      {/* Brand Logos Section - With Product Images and Auto-scroll */}
      <section className="brands-section">
        <div className="container">
          <div 
            className="brands-logos"
            ref={brandsLogosScrollRef}
            onMouseEnter={() => setIsBrandsHovered(true)}
            onMouseLeave={() => setIsBrandsHovered(false)}
          >
            {brands.map((brand) => (
              <div key={brand.id} className="brand-logo-item">
                {brand.productImage ? (
                  <img 
                    src={brand.productImage} 
                    alt={brand.name}
                    className="brand-product-image"
                    onError={(e) => {
                      // Try to show text fallback
                      const textDiv = e.target.parentElement.querySelector('.brand-logo-text');
                      if (textDiv) {
                        textDiv.style.display = 'block';
                        e.target.style.display = 'none';
                      } else {
                        // If no text div, use placeholder
                        e.target.src = `https://via.placeholder.com/150x150/f5f5f5/999999?text=${encodeURIComponent(brand.name)}`;
                      }
                    }}
                    loading="lazy"
                  />
                ) : null}
                <div 
                  className="brand-logo-text"
                  style={{display: brand.productImage ? 'none' : 'block'}}
                >
                  {brand.name}
                </div>
              </div>
            ))}
            {/* Duplicate brands for seamless loop */}
            {brands.map((brand) => (
              <div key={`duplicate-${brand.id}`} className="brand-logo-item">
                {brand.productImage ? (
                  <img 
                    src={brand.productImage} 
                    alt={brand.name}
                    className="brand-product-image"
                    onError={(e) => {
                      // Try to show text fallback
                      const textDiv = e.target.parentElement.querySelector('.brand-logo-text');
                      if (textDiv) {
                        textDiv.style.display = 'block';
                        e.target.style.display = 'none';
                      } else {
                        // If no text div, use placeholder
                        e.target.src = `https://via.placeholder.com/150x150/f5f5f5/999999?text=${encodeURIComponent(brand.name)}`;
                      }
                    }}
                    loading="lazy"
                  />
                ) : null}
                <div 
                  className="brand-logo-text"
                  style={{display: brand.productImage ? 'none' : 'block'}}
                >
                  {brand.name}
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Trending Now Section */}
      <section className="products-section trending-section">
        <div className="container">
          <div className="section-header">
            <h2 className="section-title">Trending Now</h2>
            <Link to="/category/Popular" className="view-all-link">
              View All <i className="fas fa-chevron-right"></i>
            </Link>
          </div>
          <div className="products-grid">
            {trendingProducts.map((product) => (
              <ProductCard key={product.id} product={product} />
            ))}
          </div>
        </div>
      </section>
    </div>
  );
};

export default Home;
