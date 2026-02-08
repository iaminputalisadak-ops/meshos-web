import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import './ProductCard.css';

const ProductCard = ({ product }) => {
  // Get the actual image URL - prioritize real images
  const imageUrl = product.image || (product.images && product.images.length > 0 ? product.images[0] : null);
  
  const [imgSrc, setImgSrc] = useState(imageUrl);
  const [hasError, setHasError] = useState(false);

  // Reset when product changes
  useEffect(() => {
    const newUrl = product.image || (product.images && product.images.length > 0 ? product.images[0] : null);
    if (newUrl) {
      setImgSrc(newUrl);
      setHasError(false);
    }
  }, [product.id, product.image]);

  const handleError = (e) => {
    if (!hasError) {
      setHasError(true);
      // Try alternative source from images array
      if (product.images && product.images.length > 0) {
        const altImage = product.images.find(img => img !== e.target.src);
        if (altImage) {
          setImgSrc(altImage);
          setHasError(false);
          return;
        }
      }
      // Try product.image if different
      if (product.image && product.image !== e.target.src) {
        setImgSrc(product.image);
        setHasError(false);
        return;
      }
      // Last resort - placeholder
      const placeholder = `https://via.placeholder.com/400x400/f5f5f5/999999?text=${encodeURIComponent(product.name.substring(0, 20))}`;
      setImgSrc(placeholder);
    }
  };

  const handleLoad = () => {
    setHasError(false);
  };

  // Always use actual image if available, only fallback to placeholder if none exist
  const finalSrc = imgSrc || imageUrl || 
    `https://via.placeholder.com/400x400/f5f5f5/999999?text=${encodeURIComponent(product.name.substring(0, 20))}`;

  return (
    <Link to={`/product/${product.id}`} className="product-card">
      <div className="product-image-container">
        <img 
          src={finalSrc}
          alt={product.name} 
          className="product-image"
          onError={handleError}
          onLoad={handleLoad}
          loading="eager"
          style={{ display: 'block', visibility: 'visible', opacity: 1 }}
        />
        {product.discount > 0 && (
          <span className="discount-badge">{product.discount}% OFF</span>
        )}
      </div>
      <div className="product-info">
        <h3 className="product-name">{product.name}</h3>
        <div className="product-rating">
          <span className="rating-stars">
            {Array.from({ length: 5 }).map((_, i) => (
              <i
                key={i}
                className={`fas fa-star ${i < Math.floor(product.rating) ? 'filled' : ''}`}
              ></i>
            ))}
          </span>
          <span className="rating-value">({product.reviews})</span>
        </div>
        <div className="product-price">
          <span className="current-price">₹{product.price}</span>
          {product.originalPrice > product.price && (
            <span className="original-price">₹{product.originalPrice}</span>
          )}
        </div>
        <div className="product-delivery">Free Delivery</div>
        <div className="product-reviews">
          {product.rating} ({product.reviews > 1000 ? `${(product.reviews / 1000).toFixed(1)}K` : product.reviews} {product.reviews === 1 ? 'Review' : 'Reviews'})
        </div>
      </div>
    </Link>
  );
};

export default ProductCard;
