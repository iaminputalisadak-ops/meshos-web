import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import API_BASE_URL from '../../config/api';
import './PromoterAuth.css';

const PromoterLogin = () => {
  const navigate = useNavigate();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [message, setMessage] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setMessage('');
    setLoading(true);
    try {
      const res = await fetch(`${API_BASE_URL}/promoter/login.php`, {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password }),
      });
      const data = await res.json();
      if (data.success) {
        navigate('/promoter/dashboard');
      } else {
        setMessage(data.message || 'Login failed');
      }
    } catch {
      setMessage('Network error');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="promoter-auth-page">
      <div className="promoter-auth-box">
        <h1>Promoter Login</h1>
        <form onSubmit={handleSubmit}>
          <input type="email" placeholder="Email *" value={email} onChange={(e) => setEmail(e.target.value)} required />
          <input type="password" placeholder="Password *" value={password} onChange={(e) => setPassword(e.target.value)} required />
          {message && <div className="promoter-msg promoter-msg-err">{message}</div>}
          <button type="submit" disabled={loading}>{loading ? 'Logging in...' : 'Login'}</button>
        </form>
        <p><Link to="/promoter/register">Become a Promoter</Link></p>
      </div>
    </div>
  );
};

export default PromoterLogin;
