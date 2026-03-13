import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import API_BASE_URL from '../../config/api';
import './CreatorAuth.css';

const CreatorLogin = () => {
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
      const res = await fetch(`${API_BASE_URL}/creator/login.php`, {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password }),
      });
      const data = await res.json();
      if (data.success) {
        navigate('/creator/dashboard');
      } else {
        setMessage(data.message || 'Login failed');
      }
    } catch {
      setMessage('Cannot reach server. Make sure the backend is running.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="creator-auth-page">
      <div className="creator-auth-box">
        <div className="creator-brand">
          <i className="fas fa-crown"></i>
          <h1>Creator Partner Login</h1>
        </div>
        <form onSubmit={handleSubmit}>
          <input type="email" placeholder="Email *" value={email} onChange={(e) => setEmail(e.target.value)} required />
          <input type="password" placeholder="Password *" value={password} onChange={(e) => setPassword(e.target.value)} required />
          {message && <div className="creator-msg creator-msg-err">{message}</div>}
          <button type="submit" className="creator-btn" disabled={loading}>{loading ? 'Logging in...' : 'Login'}</button>
        </form>
        <p className="creator-link"><Link to="/creator/register">Join the Creator Partner Program</Link></p>
      </div>
    </div>
  );
};

export default CreatorLogin;
