import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { subscriptionPlans } from '../../data/subscriptionPlans';
import { useSubscription } from '../../context/SubscriptionContext';
import './SubscriptionCheckout.css';

const SubscriptionCheckout = () => {
  const { planId } = useParams();
  const navigate = useNavigate();
  const { subscribe } = useSubscription();
  const [plan, setPlan] = useState(null);
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    phone: '',
    address: '',
    paymentMethod: 'card',
  });
  const [errors, setErrors] = useState({});
  const [isProcessing, setIsProcessing] = useState(false);

  useEffect(() => {
    const foundPlan = subscriptionPlans.find((p) => p.id === parseInt(planId));
    if (foundPlan) {
      setPlan(foundPlan);
    } else {
      navigate('/subscription');
    }
  }, [planId, navigate]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
    if (errors[name]) {
      setErrors((prev) => ({ ...prev, [name]: '' }));
    }
  };

  const validateForm = () => {
    const newErrors = {};
    if (!formData.name.trim()) newErrors.name = 'Name is required';
    if (!formData.email.trim()) {
      newErrors.email = 'Email is required';
    } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
      newErrors.email = 'Email is invalid';
    }
    if (!formData.phone.trim()) {
      newErrors.phone = 'Phone is required';
    } else if (!/^\d{10}$/.test(formData.phone)) {
      newErrors.phone = 'Phone must be 10 digits';
    }
    if (!formData.address.trim()) newErrors.address = 'Address is required';
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!validateForm()) return;

    setIsProcessing(true);
    // Simulate payment processing
    setTimeout(() => {
      subscribe(plan);
      setIsProcessing(false);
      navigate('/dashboard?success=true');
    }, 2000);
  };

  if (!plan) {
    return (
      <div className="subscription-checkout">
        <div className="container">
          <div className="loading">Loading...</div>
        </div>
      </div>
    );
  }

  return (
    <div className="subscription-checkout">
      <div className="container">
        <div className="checkout-header">
          <h1>Complete Your Subscription</h1>
          <p>You're one step away from unlocking exclusive benefits</p>
        </div>

        <div className="checkout-content">
          <div className="checkout-form-section">
            <form onSubmit={handleSubmit} className="checkout-form">
              <div className="form-section">
                <h2>Personal Information</h2>
                <div className="form-group">
                  <label>Full Name *</label>
                  <input
                    type="text"
                    name="name"
                    value={formData.name}
                    onChange={handleChange}
                    className={errors.name ? 'error' : ''}
                    placeholder="Enter your full name"
                  />
                  {errors.name && <span className="error-text">{errors.name}</span>}
                </div>

                <div className="form-group">
                  <label>Email Address *</label>
                  <input
                    type="email"
                    name="email"
                    value={formData.email}
                    onChange={handleChange}
                    className={errors.email ? 'error' : ''}
                    placeholder="Enter your email"
                  />
                  {errors.email && <span className="error-text">{errors.email}</span>}
                </div>

                <div className="form-group">
                  <label>Phone Number *</label>
                  <input
                    type="tel"
                    name="phone"
                    value={formData.phone}
                    onChange={handleChange}
                    className={errors.phone ? 'error' : ''}
                    placeholder="Enter your phone number"
                  />
                  {errors.phone && <span className="error-text">{errors.phone}</span>}
                </div>

                <div className="form-group">
                  <label>Address *</label>
                  <textarea
                    name="address"
                    value={formData.address}
                    onChange={handleChange}
                    className={errors.address ? 'error' : ''}
                    placeholder="Enter your address"
                    rows="3"
                  />
                  {errors.address && <span className="error-text">{errors.address}</span>}
                </div>
              </div>

              <div className="form-section">
                <h2>Payment Method</h2>
                <div className="payment-methods">
                  <label className="payment-option">
                    <input
                      type="radio"
                      name="paymentMethod"
                      value="card"
                      checked={formData.paymentMethod === 'card'}
                      onChange={handleChange}
                    />
                    <i className="fas fa-credit-card"></i>
                    <span>Credit/Debit Card</span>
                  </label>
                  <label className="payment-option">
                    <input
                      type="radio"
                      name="paymentMethod"
                      value="upi"
                      checked={formData.paymentMethod === 'upi'}
                      onChange={handleChange}
                    />
                    <i className="fas fa-mobile-alt"></i>
                    <span>UPI</span>
                  </label>
                  <label className="payment-option">
                    <input
                      type="radio"
                      name="paymentMethod"
                      value="wallet"
                      checked={formData.paymentMethod === 'wallet'}
                      onChange={handleChange}
                    />
                    <i className="fas fa-wallet"></i>
                    <span>Wallet</span>
                  </label>
                </div>
              </div>

              <button
                type="submit"
                className="submit-button"
                disabled={isProcessing}
              >
                {isProcessing ? (
                  <>
                    <i className="fas fa-spinner fa-spin"></i>
                    Processing...
                  </>
                ) : (
                  <>
                    <i className="fas fa-lock"></i>
                    Complete Subscription
                  </>
                )}
              </button>
            </form>
          </div>

          <div className="order-summary">
            <div className="summary-card">
              <h2>Order Summary</h2>
              <div className="plan-summary">
                <div className="plan-info">
                  <h3>{plan.name} Plan</h3>
                  <p>Monthly Subscription</p>
                </div>
                <div className="plan-price-summary">
                  <span className="price">₹{plan.price}</span>
                  <span className="period">/month</span>
                </div>
              </div>
              <div className="summary-divider"></div>
              <div className="summary-total">
                <span>Total</span>
                <span className="total-price">₹{plan.price}</span>
              </div>
              <div className="summary-features">
                <h4>You'll get:</h4>
                <ul>
                  {plan.features.slice(0, 5).map((feature, index) => (
                    <li key={index}>
                      <i className="fas fa-check"></i>
                      {feature}
                    </li>
                  ))}
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default SubscriptionCheckout;



