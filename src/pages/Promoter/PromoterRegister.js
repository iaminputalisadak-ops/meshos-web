import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import API_BASE_URL from '../../config/api';
import './PromoterAuth.css';

const PromoterRegister = () => {
  const navigate = useNavigate();
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [phone, setPhone] = useState('');
  const [password, setPassword] = useState('');
  const [message, setMessage] = useState('');
  const [isError, setIsError] = useState(false);
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setMessage('');
    setIsError(false);
    setLoading(true);
    try {
      const res = await fetch(`${API_BASE_URL}/promoter/register.php`, {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name, email, phone, password }),
      });
      const data = await res.json().catch(() => ({ success: false, message: 'Invalid response from server' }));
      if (data.success) {
        setIsError(false);
        setMessage(data.message || 'Registration successful! Wait for admin approval.');
        setTimeout(() => navigate('/promoter/login'), 3000);
      } else {
        setIsError(true);
        setMessage(data.message || 'Registration failed');
      }
    } catch (err) {
      setIsError(true);
      setMessage('Cannot reach backend. 1) Open XAMPP and start Apache. 2) Confirm this link works in your browser: http://localhost/Shopping/backend/api/promoter/register.php');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="promoter-auth-page">
      <div className="promoter-auth-box">
        <h1>Become a Promoter</h1>
        <p>Sign up to earn 10% commission on every sale you refer.</p>
        <form onSubmit={handleSubmit}>
          <input type="text" placeholder="Name *" value={name} onChange={(e) => setName(e.target.value)} required />
          <input type="email" placeholder="Email *" value={email} onChange={(e) => setEmail(e.target.value)} required />
          <input type="tel" placeholder="Phone" value={phone} onChange={(e) => setPhone(e.target.value)} />
          <input type="password" placeholder="Password (min 6) *" value={password} onChange={(e) => setPassword(e.target.value)} minLength={6} required />
          {message && <div className={isError ? 'promoter-msg promoter-msg-err' : 'promoter-msg'}>{message}</div>}
          <button type="submit" disabled={loading}>{loading ? 'Submitting...' : 'Register'}</button>
        </form>
        <p><Link to="/promoter/login">Already registered? Login</Link></p>
      </div>
    </div>
  );
};

export default PromoterRegister;
