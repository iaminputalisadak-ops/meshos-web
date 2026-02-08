import React, { useEffect, useState } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import { useSubscription } from '../../context/SubscriptionContext';
import { subscriptionPlans } from '../../data/subscriptionPlans';
import './Dashboard.css';

const Dashboard = () => {
  const { subscription, cancelSubscription, isSubscribed, getDaysRemaining } = useSubscription();
  const [searchParams] = useSearchParams();
  const [showSuccess, setShowSuccess] = useState(false);

  useEffect(() => {
    if (searchParams.get('success') === 'true') {
      setShowSuccess(true);
      setTimeout(() => setShowSuccess(false), 5000);
    }
  }, [searchParams]);

  if (!subscription) {
    return (
      <div className="dashboard-page">
        <div className="container">
          <div className="no-subscription">
            <i className="fas fa-user-circle"></i>
            <h2>No Active Subscription</h2>
            <p>Subscribe to unlock exclusive benefits and save on every purchase</p>
            <Link to="/subscription" className="subscribe-btn">
              View Subscription Plans
            </Link>
          </div>
        </div>
      </div>
    );
  }

  const currentPlan = subscriptionPlans.find((p) => p.id === subscription.planId);
  const daysRemaining = getDaysRemaining();
  const isActive = isSubscribed();

  return (
    <div className="dashboard-page">
      <div className="container">
        {showSuccess && (
          <div className="success-banner">
            <i className="fas fa-check-circle"></i>
            <span>Subscription activated successfully!</span>
          </div>
        )}

        <div className="dashboard-header">
          <h1>My Dashboard</h1>
          <Link to="/" className="back-link">
            <i className="fas fa-arrow-left"></i>
            Back to Shopping
          </Link>
        </div>

        <div className="dashboard-content">
          <div className="subscription-card">
            <div className="subscription-status">
              <div className="status-header">
                <h2>Current Subscription</h2>
                <span className={`status-badge ${isActive ? 'active' : 'inactive'}`}>
                  {isActive ? 'Active' : 'Expired'}
                </span>
              </div>
              <div className="plan-details">
                <div className="plan-name-large">{subscription.planName} Plan</div>
                <div className="plan-price-large">₹{subscription.price}/month</div>
              </div>
              {isActive && (
                <div className="subscription-info">
                  <div className="info-item">
                    <i className="fas fa-calendar-check"></i>
                    <div>
                      <span className="info-label">Started</span>
                      <span className="info-value">
                        {new Date(subscription.startDate).toLocaleDateString()}
                      </span>
                    </div>
                  </div>
                  <div className="info-item">
                    <i className="fas fa-calendar-times"></i>
                    <div>
                      <span className="info-label">Expires</span>
                      <span className="info-value">
                        {new Date(subscription.endDate).toLocaleDateString()}
                      </span>
                    </div>
                  </div>
                  <div className="info-item">
                    <i className="fas fa-clock"></i>
                    <div>
                      <span className="info-label">Days Remaining</span>
                      <span className="info-value highlight">{daysRemaining} days</span>
                    </div>
                  </div>
                </div>
              )}
            </div>

            <div className="subscription-features">
              <h3>Your Benefits</h3>
              <ul>
                {subscription.features.map((feature, index) => (
                  <li key={index}>
                    <i className="fas fa-check"></i>
                    <span>{feature}</span>
                  </li>
                ))}
              </ul>
            </div>

            <div className="subscription-actions">
              {isActive ? (
                <>
                  <Link
                    to={`/subscription/checkout/${subscription.planId}`}
                    className="action-btn renew-btn"
                  >
                    <i className="fas fa-sync"></i>
                    Renew Subscription
                  </Link>
                  <button
                    onClick={cancelSubscription}
                    className="action-btn cancel-btn"
                  >
                    <i className="fas fa-times"></i>
                    Cancel Subscription
                  </button>
                </>
              ) : (
                <Link to="/subscription" className="action-btn subscribe-btn">
                  <i className="fas fa-plus"></i>
                  Subscribe Again
                </Link>
              )}
            </div>
          </div>

          <div className="benefits-section">
            <h2>Subscription Benefits</h2>
            <div className="benefits-grid">
              <div className="benefit-item">
                <i className="fas fa-percent"></i>
                <h3>Exclusive Discounts</h3>
                <p>Get {currentPlan?.name === 'Basic' ? '5%' : currentPlan?.name === 'Premium' ? '10%' : '15%'} off on all products</p>
              </div>
              <div className="benefit-item">
                <i className="fas fa-shipping-fast"></i>
                <h3>Free Delivery</h3>
                <p>Enjoy free delivery on all orders</p>
              </div>
              <div className="benefit-item">
                <i className="fas fa-headset"></i>
                <h3>Priority Support</h3>
                <p>24/7 dedicated customer support</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Dashboard;



