import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import API_BASE_URL from '../../config/api';
import './CreatorAuth.css';

const CreatorRegister = () => {
  const navigate = useNavigate();
  const [form, setForm] = useState({ name: '', email: '', phone: '', password: '', payment_reference: '' });
  const [message, setMessage] = useState('');
  const [isError, setIsError] = useState(false);
  const [loading, setLoading] = useState(false);
  const [step, setStep] = useState(1);

  const handleChange = (e) => setForm({ ...form, [e.target.name]: e.target.value });

  const handleSubmit = async (e) => {
    e.preventDefault();
    setMessage('');
    setIsError(false);
    setLoading(true);
    try {
      const res = await fetch(`${API_BASE_URL}/creator/register.php`, {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(form),
      });
      const text = await res.text();
      let data;
      try { data = text ? JSON.parse(text) : {}; } catch {
        setIsError(true);
        setMessage('Invalid response from server.');
        return;
      }
      if (data.success) {
        setIsError(false);
        setMessage(data.message || 'Registration successful!');
        setTimeout(() => navigate('/creator/login'), 4000);
      } else {
        setIsError(true);
        setMessage(data.message || 'Registration failed');
      }
    } catch {
      setIsError(true);
      setMessage('Cannot reach backend. Make sure the server is running.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="creator-auth-page">
      <div className="creator-auth-box">
        <div className="creator-brand">
          <i className="fas fa-crown"></i>
          <h1>Creator Partner Program</h1>
          <p className="creator-subtitle">Join our exclusive paid promotion network</p>
        </div>

        <div className="creator-steps">
          <div className={`creator-step ${step >= 1 ? 'active' : ''}`}>
            <span className="step-num">1</span>
            <span>Register</span>
          </div>
          <div className="step-line"></div>
          <div className={`creator-step ${step >= 2 ? 'active' : ''}`}>
            <span className="step-num">2</span>
            <span>Pay ₹500</span>
          </div>
          <div className="step-line"></div>
          <div className="creator-step">
            <span className="step-num">3</span>
            <span>Get Approved</span>
          </div>
        </div>

        <div className="creator-benefits">
          <div className="benefit"><i className="fas fa-check-circle"></i> 28-day active promotion period</div>
          <div className="benefit"><i className="fas fa-check-circle"></i> Custom commission rate set by admin</div>
          <div className="benefit"><i className="fas fa-check-circle"></i> Exclusive partner product links</div>
          <div className="benefit"><i className="fas fa-check-circle"></i> Wallet-based earning system</div>
        </div>

        {step === 1 && (
          <form onSubmit={(e) => { e.preventDefault(); setStep(2); }}>
            <input type="text" name="name" placeholder="Full Name *" value={form.name} onChange={handleChange} required />
            <input type="email" name="email" placeholder="Email *" value={form.email} onChange={handleChange} required />
            <input type="tel" name="phone" placeholder="Phone" value={form.phone} onChange={handleChange} />
            <input type="password" name="password" placeholder="Password (min 6) *" value={form.password} onChange={handleChange} minLength={6} required />
            <button type="submit" className="creator-btn">Next: Payment</button>
          </form>
        )}

        {step === 2 && (
          <form onSubmit={handleSubmit}>
            <div className="payment-info">
              <div className="payment-amount">
                <span className="amount-label">Monthly Membership Fee</span>
                <span className="amount-value">₹500</span>
              </div>
              <p className="payment-note">
                Transfer ₹500 via UPI/Bank and enter the transaction reference below.
                Admin will verify your payment and approve your account.
              </p>
              <input
                type="text"
                name="payment_reference"
                placeholder="Payment Reference / UPI Transaction ID"
                value={form.payment_reference}
                onChange={handleChange}
              />
            </div>
            {message && <div className={isError ? 'creator-msg creator-msg-err' : 'creator-msg'}>{message}</div>}
            <button type="submit" className="creator-btn" disabled={loading}>
              {loading ? 'Submitting...' : 'Submit Registration'}
            </button>
            <button type="button" className="creator-btn-secondary" onClick={() => setStep(1)}>Back</button>
          </form>
        )}

        <p className="creator-link"><Link to="/creator/login">Already a Creator Partner? Login</Link></p>
      </div>
    </div>
  );
};

export default CreatorRegister;
