import React, { useState, useEffect, useCallback } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import API_BASE_URL from '../../config/api';
import './CreatorDashboard.css';

const CreatorDashboard = () => {
  const navigate = useNavigate();
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [copyId, setCopyId] = useState(null);
  const [renewLoading, setRenewLoading] = useState(false);
  const [renewMsg, setRenewMsg] = useState('');
  const [tab, setTab] = useState('overview');

  const loadDashboard = useCallback(() => {
    fetch(`${API_BASE_URL}/creator/dashboard.php`, { credentials: 'include' })
      .then((r) => r.json())
      .then((d) => {
        if (d.success) setData(d.data);
        else navigate('/creator/login');
      })
      .catch(() => navigate('/creator/login'))
      .finally(() => setLoading(false));
  }, [navigate]);

  useEffect(() => { loadDashboard(); }, [loadDashboard]);

  const copyLink = (url, id) => {
    navigator.clipboard.writeText(url);
    setCopyId(id);
    setTimeout(() => setCopyId(null), 2000);
  };

  const handleRenew = async () => {
    setRenewLoading(true);
    setRenewMsg('');
    try {
      const res = await fetch(`${API_BASE_URL}/creator/renew.php`, {
        method: 'POST', credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({}),
      });
      const d = await res.json();
      setRenewMsg(d.message || (d.success ? 'Renewal submitted' : 'Failed'));
      if (d.success) loadDashboard();
    } catch { setRenewMsg('Network error'); }
    finally { setRenewLoading(false); }
  };

  const handleLogout = async () => {
    await fetch(`${API_BASE_URL}/creator/logout.php`, { credentials: 'include' }).catch(() => {});
    navigate('/creator/login');
  };

  if (loading) return <div className="container" style={{ padding: 40, textAlign: 'center' }}>Loading...</div>;
  if (!data) return null;

  const { membership, stats, commissions, products, wallet_history, pending_membership } = data;

  return (
    <div className="cd-page">
      <div className="container">
        <div className="cd-header">
          <div className="cd-header-left">
            <i className="fas fa-crown cd-crown"></i>
            <div>
              <h1>Creator Partner Dashboard</h1>
              <span className="cd-code">Partner Code: <strong>{data.creator_code}</strong></span>
            </div>
          </div>
          <div className="cd-header-right">
            <Link to="/" className="cd-link">Store</Link>
            <button onClick={handleLogout} className="cd-logout">Logout</button>
          </div>
        </div>

        {/* Membership Status Card */}
        <div className={`cd-membership-card ${membership.is_active ? 'active' : 'expired'}`}>
          <div className="cd-mem-left">
            <div className="cd-mem-status">
              <i className={`fas ${membership.is_active ? 'fa-check-circle' : 'fa-exclamation-circle'}`}></i>
              <span>{membership.is_active ? 'Active Membership' : 'Membership Expired'}</span>
            </div>
            {membership.is_active ? (
              <div className="cd-mem-details">
                <span><strong>{membership.days_left}</strong> days remaining</span>
                <span>Expires: {new Date(membership.expires_at).toLocaleDateString()}</span>
                <span>Renewal #{membership.renewal_number}</span>
              </div>
            ) : (
              <div className="cd-mem-details">
                <span>Renew your membership to continue earning</span>
                {pending_membership && <span className="cd-pending-tag">Renewal pending approval</span>}
              </div>
            )}
          </div>
          <div className="cd-mem-right">
            <div className="cd-commission-badge">
              <span className="cd-comm-value">{data.commission_rate}%</span>
              <span className="cd-comm-label">Commission Rate</span>
            </div>
            {!membership.is_active && !pending_membership && (
              <button className="cd-renew-btn" onClick={handleRenew} disabled={renewLoading}>
                {renewLoading ? 'Submitting...' : 'Renew ₹500'}
              </button>
            )}
            {renewMsg && <div className="cd-renew-msg">{renewMsg}</div>}
          </div>
        </div>

        {/* Stats Grid */}
        <div className="cd-stats">
          <div className="cd-stat"><span className="cd-stat-value">{stats.total_clicks}</span><span className="cd-stat-label">Total Clicks</span></div>
          <div className="cd-stat"><span className="cd-stat-value">{stats.total_orders}</span><span className="cd-stat-label">Total Orders</span></div>
          <div className="cd-stat"><span className="cd-stat-value">₹{Number(stats.total_sales).toFixed(0)}</span><span className="cd-stat-label">Total Sales</span></div>
          <div className="cd-stat highlight"><span className="cd-stat-value">₹{Number(stats.wallet_balance).toFixed(0)}</span><span className="cd-stat-label">Wallet Balance</span></div>
          <div className="cd-stat"><span className="cd-stat-value">₹{Number(stats.pending_commission).toFixed(0)}</span><span className="cd-stat-label">Pending</span></div>
          <div className="cd-stat"><span className="cd-stat-value">₹{Number(stats.approved_commission).toFixed(0)}</span><span className="cd-stat-label">Approved</span></div>
          <div className="cd-stat"><span className="cd-stat-value">₹{Number(stats.total_earned).toFixed(0)}</span><span className="cd-stat-label">Total Earned</span></div>
          <div className="cd-stat"><span className="cd-stat-value">₹{Number(stats.total_paid).toFixed(0)}</span><span className="cd-stat-label">Paid Out</span></div>
        </div>

        {/* Tabs */}
        <div className="cd-tabs">
          <button className={`cd-tab ${tab === 'overview' ? 'active' : ''}`} onClick={() => setTab('overview')}>
            <i className="fas fa-link"></i> Product Links
          </button>
          <button className={`cd-tab ${tab === 'commissions' ? 'active' : ''}`} onClick={() => setTab('commissions')}>
            <i className="fas fa-rupee-sign"></i> Commissions
          </button>
          <button className={`cd-tab ${tab === 'wallet' ? 'active' : ''}`} onClick={() => setTab('wallet')}>
            <i className="fas fa-wallet"></i> Wallet
          </button>
        </div>

        {/* Product Links */}
        {tab === 'overview' && (
          <section className="cd-section">
            {membership.is_active ? (
              <>
                <p className="cd-section-note">Share these partner links. You earn {data.commission_rate}% on each sale.</p>
                <div className="cd-products">
                  {(products || []).slice(0, 24).map((p) => (
                    <div key={p.id} className="cd-product">
                      <img src={p.image || 'https://via.placeholder.com/80'} alt={p.name} />
                      <div className="cd-product-info">
                        <div className="cd-product-name">{p.name}</div>
                        <div className="cd-product-price">₹{p.price}</div>
                        <button className="cd-copy-btn" onClick={() => copyLink(p.partner_link, p.id)}>
                          {copyId === p.id ? '✓ Copied!' : 'Copy Partner Link'}
                        </button>
                      </div>
                    </div>
                  ))}
                </div>
              </>
            ) : (
              <div className="cd-inactive-notice">
                <i className="fas fa-lock"></i>
                <h3>Partner Links Locked</h3>
                <p>Your membership has expired. Renew to access partner links and continue earning.</p>
              </div>
            )}
          </section>
        )}

        {/* Commissions */}
        {tab === 'commissions' && (
          <section className="cd-section">
            <table className="cd-table">
              <thead><tr><th>Order</th><th>Sale</th><th>Rate</th><th>Commission</th><th>Status</th><th>Date</th></tr></thead>
              <tbody>
                {(commissions || []).map((c) => (
                  <tr key={c.id}>
                    <td>#{c.order_id}</td>
                    <td>₹{Number(c.sale_amount).toFixed(2)}</td>
                    <td>{c.commission_rate}%</td>
                    <td>₹{Number(c.commission_amount).toFixed(2)}</td>
                    <td><span className={`cd-badge cd-badge-${c.status}`}>{c.status}</span></td>
                    <td>{new Date(c.created_at).toLocaleDateString()}</td>
                  </tr>
                ))}
              </tbody>
            </table>
            {(!commissions || commissions.length === 0) && <p className="cd-empty">No commissions yet. Share your partner links to start earning!</p>}
          </section>
        )}

        {/* Wallet */}
        {tab === 'wallet' && (
          <section className="cd-section">
            <div className="cd-wallet-header">
              <div className="cd-wallet-balance">
                <span className="cd-wallet-amount">₹{Number(stats.wallet_balance).toFixed(2)}</span>
                <span>Available Balance</span>
              </div>
            </div>
            <table className="cd-table">
              <thead><tr><th>Type</th><th>Source</th><th>Amount</th><th>Balance After</th><th>Description</th><th>Date</th></tr></thead>
              <tbody>
                {(wallet_history || []).map((w) => (
                  <tr key={w.id}>
                    <td><span className={`cd-badge cd-badge-${w.type}`}>{w.type}</span></td>
                    <td>{w.source}</td>
                    <td className={w.type === 'credit' ? 'cd-green' : 'cd-red'}>
                      {w.type === 'credit' ? '+' : '-'}₹{Number(w.amount).toFixed(2)}
                    </td>
                    <td>₹{Number(w.balance_after).toFixed(2)}</td>
                    <td>{w.description || '-'}</td>
                    <td>{new Date(w.created_at).toLocaleDateString()}</td>
                  </tr>
                ))}
              </tbody>
            </table>
            {(!wallet_history || wallet_history.length === 0) && <p className="cd-empty">No wallet transactions yet.</p>}
          </section>
        )}
      </div>
    </div>
  );
};

export default CreatorDashboard;
