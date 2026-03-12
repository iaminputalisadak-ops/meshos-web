import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useCart } from '../../context/CartContext';
import API_BASE_URL from '../../config/api';
import './Checkout.css';

const Checkout = () => {
  const { cartItems, getCartTotal, clearCart } = useCart();
  const navigate = useNavigate();
  const [referral, setReferral] = useState(null);
  const [address, setAddress] = useState('');
  const [placing, setPlacing] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    fetch(`${API_BASE_URL}/referral/get.php`, { credentials: 'include' })
      .then((r) => r.json())
      .then((d) => d.success && d.data && setReferral(d.data))
      .catch(() => {});
  }, []);

  const subtotal = getCartTotal();
  const delivery = subtotal >= 499 ? 0 : 40;
  const finalTotal = subtotal + delivery;
  const items = cartItems.map((item) => ({
    product_id: item.id,
    quantity: item.quantity,
    price: item.price,
    discount: 0,
    subtotal: item.price * item.quantity,
  }));

  const handlePlaceOrder = async () => {
    if (!address.trim()) {
      setError('Please enter shipping address');
      return;
    }
    setPlacing(true);
    setError('');
    try {
      const res = await fetch(`${API_BASE_URL}/orders.php`, {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          items: items,
          total_amount: subtotal,
          discount_amount: 0,
          final_amount: finalTotal,
          shipping_address: address.trim(),
          payment_method: 'cod',
          promoter_id: referral ? referral.promoter_id : null,
          referral_code: referral ? referral.code : null,
        }),
      });
      const data = await res.json().catch(() => ({}));
      if (data.success) {
        clearCart();
        navigate('/order-success?id=' + (data.data?.order_id || ''));
      } else {
        setError(data.message || 'Failed to place order');
      }
    } catch (e) {
      setError('Network error. Please try again.');
    } finally {
      setPlacing(false);
    }
  };

  if (cartItems.length === 0 && !placing) {
    navigate('/cart');
    return null;
  }

  return (
    <div className="checkout-page">
      <div className="container">
        <h1>Checkout</h1>
        {error && <div className="checkout-error">{error}</div>}
        <div className="checkout-form">
          <label>Shipping Address *</label>
          <textarea
            value={address}
            onChange={(e) => setAddress(e.target.value)}
            placeholder="Full address, city, state, pincode"
            rows={3}
          />
        </div>
        <div className="order-summary">
          <h3>Order Summary</h3>
          <p>Subtotal: ₹{subtotal.toFixed(2)}</p>
          <p>Delivery: {delivery === 0 ? 'FREE' : `₹${delivery}`}</p>
          <p><strong>Total: ₹{finalTotal.toFixed(2)}</strong></p>
          {referral && <p className="ref-note">Referred by promoter (ref: {referral.code})</p>}
        </div>
        <button className="place-order-btn" onClick={handlePlaceOrder} disabled={placing}>
          {placing ? 'Placing Order...' : 'Place Order'}
        </button>
      </div>
    </div>
  );
};

export default Checkout;
