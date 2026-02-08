import React from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useCart } from '../../context/CartContext';
import { useSubscription } from '../../context/SubscriptionContext';
import { subscriptionBenefits } from '../../data/subscriptionPlans';
import './Cart.css';

const Cart = () => {
  const {
    cartItems,
    removeFromCart,
    updateQuantity,
    getCartTotal,
    clearCart,
  } = useCart();
  const { isSubscribed, subscription } = useSubscription();
  const navigate = useNavigate();

  if (cartItems.length === 0) {
    return (
      <div className="cart-page">
        <div className="container">
          <div className="empty-cart">
            <i className="fas fa-shopping-cart"></i>
            <h2>Your cart is empty</h2>
            <p>Add some products to your cart to continue shopping</p>
            <Link to="/" className="continue-shopping-btn">
              Continue Shopping
            </Link>
          </div>
        </div>
      </div>
    );
  }

  const subtotal = getCartTotal();
  const subscribed = isSubscribed();
  const discountPercent = subscribed
    ? subscription?.planName === 'Basic'
      ? subscriptionBenefits.basic.discount
      : subscription?.planName === 'Premium'
      ? subscriptionBenefits.premium.discount
      : subscriptionBenefits.gold.discount
    : 0;
  const discountAmount = (subtotal * discountPercent) / 100;
  const totalAfterDiscount = subtotal - discountAmount;
  const freeDeliveryThreshold = subscribed
    ? subscription?.planName === 'Basic'
      ? subscriptionBenefits.basic.freeDelivery
      : 0
    : 499;
  const deliveryCharge = totalAfterDiscount < freeDeliveryThreshold ? 40 : 0;
  const finalTotal = totalAfterDiscount + deliveryCharge;

  return (
    <div className="cart-page">
      <div className="container">
        <div className="cart-header">
          <h1>Shopping Cart</h1>
          <button className="clear-cart-btn" onClick={clearCart}>
            Clear Cart
          </button>
        </div>

        <div className="cart-content">
          <div className="cart-items">
            {cartItems.map((item) => (
              <div key={item.id} className="cart-item">
                <div className="item-image">
                  <img 
                    src={item.image || item.images?.[0] || 'https://via.placeholder.com/120x120/f5f5f5/999999?text=Product'} 
                    alt={item.name}
                    onError={(e) => {
                      if (!e.target.src.includes('via.placeholder.com')) {
                        e.target.src = `https://via.placeholder.com/120x120/f5f5f5/999999?text=${encodeURIComponent(item.name.substring(0, 15))}`;
                      }
                    }}
                  />
                </div>
                <div className="item-details">
                  <Link to={`/product/${item.id}`} className="item-name">
                    {item.name}
                  </Link>
                  <div className="item-price">
                    <span className="current-price">₹{item.price}</span>
                    {item.originalPrice > item.price && (
                      <span className="original-price">₹{item.originalPrice}</span>
                    )}
                  </div>
                  <div className="item-actions">
                    <div className="quantity-controls">
                      <button
                        onClick={() => updateQuantity(item.id, item.quantity - 1)}
                        className="quantity-btn"
                      >
                        -
                      </button>
                      <span className="quantity-value">{item.quantity}</span>
                      <button
                        onClick={() => updateQuantity(item.id, item.quantity + 1)}
                        className="quantity-btn"
                      >
                        +
                      </button>
                    </div>
                    <button
                      className="remove-btn"
                      onClick={() => removeFromCart(item.id)}
                    >
                      <i className="fas fa-trash"></i>
                      Remove
                    </button>
                  </div>
                </div>
                <div className="item-total">
                  <span className="total-price">
                    ₹{subscribed
                      ? ((item.price * item.quantity * (100 - discountPercent)) / 100).toFixed(2)
                      : item.price * item.quantity}
                  </span>
                  {subscribed && discountPercent > 0 && (
                    <span className="original-item-price">₹{item.price * item.quantity}</span>
                  )}
                </div>
              </div>
            ))}
          </div>

          <div className="cart-summary">
            <div className="summary-card">
              <h2>Order Summary</h2>
              <div className="summary-row">
                <span>Subtotal</span>
                <span>₹{subtotal}</span>
              </div>
              {subscribed && discountAmount > 0 && (
                <div className="summary-row discount-row">
                  <span>
                    <i className="fas fa-tag"></i> Subscription Discount ({discountPercent}%)
                  </span>
                  <span className="discount-amount">-₹{discountAmount.toFixed(2)}</span>
                </div>
              )}
              <div className="summary-row">
                <span>Delivery Charges</span>
                <span>
                  {deliveryCharge > 0 ? `₹${deliveryCharge}` : 'FREE'}
                </span>
              </div>
              {deliveryCharge > 0 && (
                <div className="delivery-info">
                  <i className="fas fa-info-circle"></i>
                  Add ₹{(freeDeliveryThreshold - totalAfterDiscount).toFixed(0)} more for free delivery
                </div>
              )}
              {!subscribed && (
                <div className="subscription-promo">
                  <i className="fas fa-crown"></i>
                  <div>
                    <strong>Subscribe now</strong> to get up to 15% off and free delivery!
                  </div>
                  <Link to="/subscription" className="subscribe-link-btn">Subscribe</Link>
                </div>
              )}
              <div className="summary-divider"></div>
              <div className="summary-row total-row">
                <span>Total</span>
                <span>₹{finalTotal.toFixed(2)}</span>
              </div>
              <button className="checkout-btn" onClick={() => navigate('/checkout')}>
                <i className="fas fa-lock"></i>
                Proceed to Checkout
              </button>
              <Link to="/" className="continue-shopping-link">
                Continue Shopping
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Cart;

