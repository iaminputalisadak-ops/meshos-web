export const subscriptionPlans = [
  {
    id: 1,
    name: 'Basic',
    price: 299,
    duration: 30, // days
    popular: false,
    features: [
      'Access to all products',
      '5% discount on all purchases',
      'Free delivery on orders above ₹499',
      'Priority customer support',
      'Early access to sales',
    ],
    color: '#4CAF50',
  },
  {
    id: 2,
    name: 'Premium',
    price: 599,
    duration: 30,
    popular: true,
    features: [
      'Everything in Basic',
      '10% discount on all purchases',
      'Free delivery on all orders',
      '24/7 priority support',
      'Exclusive premium products',
      'Early access to new arrivals',
      'Monthly gift vouchers worth ₹200',
    ],
    color: '#f43397',
  },
  {
    id: 3,
    name: 'Gold',
    price: 999,
    duration: 30,
    popular: false,
    features: [
      'Everything in Premium',
      '15% discount on all purchases',
      'Free express delivery',
      'Dedicated account manager',
      'VIP access to events',
      'Unlimited returns',
      'Monthly gift vouchers worth ₹500',
      'Special birthday offers',
    ],
    color: '#FFD700',
  },
];

export const subscriptionBenefits = {
  basic: {
    discount: 5,
    freeDelivery: 499,
    support: 'Priority',
  },
  premium: {
    discount: 10,
    freeDelivery: 0,
    support: '24/7 Priority',
  },
  gold: {
    discount: 15,
    freeDelivery: 0,
    support: 'Dedicated Manager',
  },
};



