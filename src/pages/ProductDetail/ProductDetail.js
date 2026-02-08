import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { products } from '../../data/products';
import { useCart } from '../../context/CartContext';
import './ProductDetail.css';

const ProductDetail = () => {
  const { productId } = useParams();
  const navigate = useNavigate();
  const { addToCart } = useCart();
  const [product, setProduct] = useState(null);
  const [selectedImage, setSelectedImage] = useState(0);
  const [quantity, setQuantity] = useState(1);
  const [imageSrc, setImageSrc] = useState('');
  const [imageError, setImageError] = useState(false);
  const [imageLoading, setImageLoading] = useState(true);

  useEffect(() => {
    const foundProduct = products.find((p) => p.id === parseInt(productId));
    setProduct(foundProduct);
    if (foundProduct) {
      setSelectedImage(0);
      // Set initial image source
      const initialImage = foundProduct.images?.[0] || foundProduct.image || 
        `https://via.placeholder.com/500x500/f5f5f5/999999?text=${encodeURIComponent(foundProduct.name.substring(0, 20))}`;
      setImageSrc(initialImage);
      setImageError(false);
    }
  }, [productId]);

  useEffect(() => {
    if (product) {
      const currentImage = product.images?.[selectedImage] || product.image || 
        `https://via.placeholder.com/500x500/f5f5f5/999999?text=${encodeURIComponent(product.name.substring(0, 20))}`;
      setImageSrc(currentImage);
      setImageError(false);
    }
  }, [selectedImage, product]);

  if (!product) {
    return (
      <div className="product-detail">
        <div className="container">
          <div className="not-found">
            <i className="fas fa-exclamation-circle"></i>
            <p>Product not found</p>
          </div>
        </div>
      </div>
    );
  }

  const handleAddToCart = () => {
    for (let i = 0; i < quantity; i++) {
      addToCart(product);
    }
    navigate('/cart');
  };

  const handleShare = (platform) => {
    const productUrl = window.location.href;
    const message = `Check out this amazing product: ${product.name} - ${productUrl}`;

    if (platform === 'whatsapp') {
      window.open(
        `https://wa.me/?text=${encodeURIComponent(message)}`,
        '_blank'
      );
    } else if (platform === 'facebook') {
      window.open(
        `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(productUrl)}`,
        '_blank'
      );
    } else if (platform === 'twitter') {
      window.open(
        `https://twitter.com/intent/tweet?text=${encodeURIComponent(message)}&url=${encodeURIComponent(productUrl)}`,
        '_blank'
      );
    }
  };

  return (
    <div className="product-detail">
      <div className="container">
        <div className="product-detail-content">
          <div className="product-images">
            <div className="main-image">
              {imageLoading && !imageError && (
                <div className="image-loading-placeholder">
                  <i className="fas fa-spinner fa-spin"></i>
                </div>
              )}
              {imageError ? (
                <div className="image-error-placeholder">
                  <i className="fas fa-image"></i>
                  <span>{product.name}</span>
                </div>
              ) : (
                <img
                  src={imageSrc || product.images?.[selectedImage] || product.image || 
                    `https://via.placeholder.com/500x500/f5f5f5/999999?text=${encodeURIComponent(product.name.substring(0, 20))}`}
                  alt={product.name}
                  loading="eager"
                  style={{ 
                    width: '100%', 
                    height: '100%', 
                    objectFit: 'cover',
                    display: 'block',
                    visibility: 'visible',
                    opacity: imageLoading ? 0.3 : 1,
                    transition: 'opacity 0.3s'
                  }}
                  onError={(e) => {
                    setImageLoading(false);
                    // Try fallback sources
                    if (e.target.src === product.image && product.images && product.images.length > 0) {
                      e.target.src = product.images[0];
                      setImageSrc(product.images[0]);
                      setImageLoading(true);
                      return;
                    }
                    // Use placeholder
                    const placeholderUrl = `https://via.placeholder.com/500x500/f5f5f5/999999?text=${encodeURIComponent(product.name.substring(0, 20))}`;
                    if (e.target.src !== placeholderUrl) {
                      e.target.src = placeholderUrl;
                      setImageSrc(placeholderUrl);
                      setImageLoading(true);
                    } else {
                      setImageError(true);
                    }
                  }}
                  onLoad={() => {
                    setImageError(false);
                    setImageLoading(false);
                  }}
                  onLoadStart={() => setImageLoading(true)}
                />
              )}
            </div>
            {product.images && product.images.length > 1 && (
              <div className="image-thumbnails">
                {product.images.map((img, index) => (
                  <img
                    key={index}
                    src={img || `https://via.placeholder.com/80x80/f5f5f5/999999?text=${index + 1}`}
                    alt={`${product.name} ${index + 1}`}
                    className={selectedImage === index ? 'active' : ''}
                    onClick={() => setSelectedImage(index)}
                    onError={(e) => {
                      if (!e.target.src.includes('via.placeholder.com')) {
                        e.target.src = `https://via.placeholder.com/80x80/f5f5f5/999999?text=${index + 1}`;
                      }
                    }}
                    style={{ display: 'block', visibility: 'visible' }}
                  />
                ))}
              </div>
            )}
          </div>

          <div className="product-info">
            <h1 className="product-title">{product.name}</h1>
            <div className="product-rating">
              <span className="rating-stars">
                {Array.from({ length: 5 }).map((_, i) => (
                  <i
                    key={i}
                    className={`fas fa-star ${
                      i < Math.floor(product.rating) ? 'filled' : ''
                    }`}
                  ></i>
                ))}
              </span>
              <span className="rating-value">
                {product.rating} ({product.reviews} reviews)
              </span>
            </div>

            <div className="product-pricing">
              <div className="price-row">
                <span className="current-price">₹{product.price}</span>
                {product.originalPrice > product.price && (
                  <>
                    <span className="original-price">₹{product.originalPrice}</span>
                    <span className="discount-badge">
                      {product.discount}% OFF
                    </span>
                  </>
                )}
              </div>
              {product.discount > 0 && (
                <div className="savings">
                  You save ₹{product.originalPrice - product.price}
                </div>
              )}
            </div>

            <div className="product-description">
              <h3>Description</h3>
              <p>{product.description}</p>
            </div>

            <div className="product-actions">
              <div className="quantity-selector">
                <label>Quantity:</label>
                <div className="quantity-controls">
                  <button
                    onClick={() => setQuantity(Math.max(1, quantity - 1))}
                    className="quantity-btn"
                  >
                    -
                  </button>
                  <span className="quantity-value">{quantity}</span>
                  <button
                    onClick={() => setQuantity(quantity + 1)}
                    className="quantity-btn"
                  >
                    +
                  </button>
                </div>
              </div>

              <button className="add-to-cart-btn" onClick={handleAddToCart}>
                <i className="fas fa-shopping-cart"></i>
                Add to Cart
              </button>

              <button 
                className="buy-now-btn"
                onClick={() => {
                  for (let i = 0; i < quantity; i++) {
                    addToCart(product);
                  }
                  navigate('/cart');
                }}
              >
                <i className="fas fa-bolt"></i>
                Buy Now
              </button>
            </div>

            <div className="social-share">
              <h3>Share this product</h3>
              <div className="share-buttons">
                <button
                  className="share-btn whatsapp"
                  onClick={() => handleShare('whatsapp')}
                  title="Share on WhatsApp"
                >
                  <i className="fab fa-whatsapp"></i>
                  WhatsApp
                </button>
                <button
                  className="share-btn facebook"
                  onClick={() => handleShare('facebook')}
                  title="Share on Facebook"
                >
                  <i className="fab fa-facebook"></i>
                  Facebook
                </button>
                <button
                  className="share-btn twitter"
                  onClick={() => handleShare('twitter')}
                  title="Share on Twitter"
                >
                  <i className="fab fa-twitter"></i>
                  Twitter
                </button>
              </div>
            </div>

            <div className="product-features">
              <div className="feature-item">
                <i className="fas fa-shipping-fast"></i>
                <span>Free delivery on orders above ₹499</span>
              </div>
              <div className="feature-item">
                <i className="fas fa-undo"></i>
                <span>7-day easy return policy</span>
              </div>
              <div className="feature-item">
                <i className="fas fa-shield-alt"></i>
                <span>100% secure payment</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default ProductDetail;

