import React from 'react';
import { Link, useSearchParams } from 'react-router-dom';

const OrderSuccess = () => {
  const [searchParams] = useSearchParams();
  const id = searchParams.get('id');
  return (
    <div className="container" style={{ padding: '60px 20px', textAlign: 'center' }}>
      <h1 style={{ color: '#4caf50' }}>Order Placed Successfully</h1>
      {id && <p>Order ID: #{id}</p>}
      <Link to="/" style={{ display: 'inline-block', marginTop: 20, color: '#667eea' }}>Continue Shopping</Link>
    </div>
  );
};

export default OrderSuccess;
