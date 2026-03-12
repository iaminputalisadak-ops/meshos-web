import React, { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import API_BASE_URL from '../../config/api';
import './PromoterDashboard.css';

const PromoterDashboard = () => {
  const navigate = useNavigate();
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [copyId, setCopyId] = useState(null);

  useEffect(() => {
    fetch(`${API_BASE_URL}/promoter/dashboard.php`, { credentials: 'include' })
      .then((r) => r.json())
      .then((d) => {
        if (d.success) setData(d.data);
        else navigate('/promoter/login');
      })
      .catch(() => navigate('/promoter/login'))
      .finally(() => setLoading(false));
  }, [navigate]);

  const copyLink = (url, id) => {
    navigator.clipboard.writeText(url);
    setCopyId(id);
    setTimeout(() => setCopyId(null), 2000);
  };

  if (loading) return <div className="container" style={{ padding: 40, textAlign: 'center' }}>Loading...</div>;
  if (!data) return null;

  const { stats, promoter_code, commissions, products, base_url } = data;

  return (
    <div className="promoter-dashboard">
      <div className="container">
        <div className="pd-header">
          <h1>Promoter Dashboard</h1>
          <span>Code: <strong>{promoter_code}</strong></span>
          <Link to="/" className="pd-back">Back to Store</Link>
        </div>
        <div className="pd-stats">
          <div className="pd-stat"><span className="pd-stat-value">{stats?.total_clicks ?? 0}</span><span>Clicks</span></div>
          <div className="pd-stat"><span className="pd-stat-value">{stats?.total_orders ?? 0}</span><span>Orders</span></div>
          <div className="pd-stat"><span className="pd-stat-value">₹{Number(stats?.total_sales ?? 0).toFixed(0)}</span><span>Sales</span></div>
          <div className="pd-stat"><span className="pd-stat-value">₹{Number(stats?.pending_commission ?? 0).toFixed(0)}</span><span>Pending</span></div>
          <div className="pd-stat"><span className="pd-stat-value">₹{Number(stats?.approved_commission ?? 0).toFixed(0)}</span><span>Approved</span></div>
          <div className="pd-stat"><span className="pd-stat-value">₹{Number(stats?.paid_commission ?? 0).toFixed(0)}</span><span>Paid</span></div>
        </div>
        <section className="pd-section">
          <h2>Product referral links</h2>
          <p>Share these links. You earn 10% when someone buys within 30 days.</p>
          <div className="pd-products">
            {(products || []).slice(0, 20).map((p) => (
              <div key={p.id} className="pd-product">
                <img src={p.image || 'https://via.placeholder.com/80'} alt={p.name} />
                <div>
                  <div className="pd-product-name">{p.name}</div>
                  <div className="pd-product-price">₹{p.price}</div>
                  <button type="button" className="pd-copy-btn" onClick={() => copyLink(p.referral_link, p.id)}>
                    {copyId === p.id ? 'Copied!' : 'Copy link'}
                  </button>
                </div>
              </div>
            ))}
          </div>
        </section>
        <section className="pd-section">
          <h2>Recent commissions</h2>
          <table className="pd-table">
            <thead><tr><th>Order</th><th>Amount</th><th>Commission</th><th>Status</th></tr></thead>
            <tbody>
              {(commissions || []).map((c) => (
                <tr key={c.id}>
                  <td>#{c.order_id}</td>
                  <td>₹{Number(c.order_amount).toFixed(2)}</td>
                  <td>₹{Number(c.commission_amount).toFixed(2)}</td>
                  <td><span className={`pd-badge pd-badge-${c.status}`}>{c.status}</span></td>
                </tr>
              ))}
            </tbody>
          </table>
          {(!commissions || commissions.length === 0) && <p>No commissions yet.</p>}
        </section>
      </div>
    </div>
  );
};

export default PromoterDashboard;
