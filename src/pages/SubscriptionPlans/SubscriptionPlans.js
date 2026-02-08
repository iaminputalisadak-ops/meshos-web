import React from 'react';
import { Link } from 'react-router-dom';
import { subscriptionPlans } from '../../data/subscriptionPlans';
import { useSubscription } from '../../context/SubscriptionContext';
import './SubscriptionPlans.css';

const SubscriptionPlans = () => {
  const { isSubscribed } = useSubscription();

  return (
    <div className="subscription-plans-page">
      <div className="container">
        <div className="plans-header">
          <h1>Choose Your Subscription Plan</h1>
          <p>Unlock exclusive benefits and save more on every purchase</p>
        </div>

        {isSubscribed() && (
          <div className="subscription-active-banner">
            <i className="fas fa-check-circle"></i>
            <span>You have an active subscription</span>
            <Link to="/dashboard">Manage Subscription</Link>
          </div>
        )}

        <div className="plans-grid">
          {subscriptionPlans.map((plan) => (
            <div
              key={plan.id}
              className={`plan-card ${plan.popular ? 'popular' : ''}`}
            >
              {plan.popular && (
                <div className="popular-badge">Most Popular</div>
              )}
              <div className="plan-header">
                <h2 className="plan-name">{plan.name}</h2>
                <div className="plan-price">
                  <span className="currency">₹</span>
                  <span className="amount">{plan.price}</span>
                  <span className="period">/month</span>
                </div>
              </div>
              <ul className="plan-features">
                {plan.features.map((feature, index) => (
                  <li key={index}>
                    <i className="fas fa-check"></i>
                    <span>{feature}</span>
                  </li>
                ))}
              </ul>
              <Link
                to={`/subscription/checkout/${plan.id}`}
                className={`plan-button ${plan.popular ? 'popular-btn' : ''}`}
              >
                {isSubscribed() ? 'Upgrade Plan' : 'Subscribe Now'}
              </Link>
            </div>
          ))}
        </div>

        <div className="benefits-section">
          <h2>Why Subscribe?</h2>
          <div className="benefits-grid">
            <div className="benefit-card">
              <i className="fas fa-percent"></i>
              <h3>Exclusive Discounts</h3>
              <p>Get up to 15% off on all products</p>
            </div>
            <div className="benefit-card">
              <i className="fas fa-shipping-fast"></i>
              <h3>Free Delivery</h3>
              <p>Enjoy free delivery on all orders</p>
            </div>
            <div className="benefit-card">
              <i className="fas fa-gift"></i>
              <h3>Monthly Rewards</h3>
              <p>Receive gift vouchers every month</p>
            </div>
            <div className="benefit-card">
              <i className="fas fa-headset"></i>
              <h3>Priority Support</h3>
              <p>24/7 dedicated customer support</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default SubscriptionPlans;



