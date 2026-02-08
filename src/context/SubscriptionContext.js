import React, { createContext, useContext, useState, useEffect } from 'react';

const SubscriptionContext = createContext();

export const useSubscription = () => {
  const context = useContext(SubscriptionContext);
  if (!context) {
    throw new Error('useSubscription must be used within a SubscriptionProvider');
  }
  return context;
};

export const SubscriptionProvider = ({ children }) => {
  const [subscription, setSubscription] = useState(() => {
    const savedSubscription = localStorage.getItem('userSubscription');
    return savedSubscription ? JSON.parse(savedSubscription) : null;
  });

  useEffect(() => {
    if (subscription) {
      localStorage.setItem('userSubscription', JSON.stringify(subscription));
    } else {
      localStorage.removeItem('userSubscription');
    }
  }, [subscription]);

  const subscribe = (plan) => {
    const subscriptionData = {
      planId: plan.id,
      planName: plan.name,
      price: plan.price,
      duration: plan.duration,
      features: plan.features,
      startDate: new Date().toISOString(),
      endDate: new Date(Date.now() + plan.duration * 24 * 60 * 60 * 1000).toISOString(),
      status: 'active',
    };
    setSubscription(subscriptionData);
    return subscriptionData;
  };

  const cancelSubscription = () => {
    if (subscription) {
      setSubscription({ ...subscription, status: 'cancelled' });
    }
  };

  const renewSubscription = (plan) => {
    if (subscription) {
      const newSubscription = {
        ...subscription,
        startDate: new Date().toISOString(),
        endDate: new Date(Date.now() + plan.duration * 24 * 60 * 60 * 1000).toISOString(),
        status: 'active',
      };
      setSubscription(newSubscription);
      return newSubscription;
    }
  };

  const isSubscribed = () => {
    if (!subscription || subscription.status !== 'active') return false;
    return new Date(subscription.endDate) > new Date();
  };

  const getDaysRemaining = () => {
    if (!subscription || subscription.status !== 'active') return 0;
    const endDate = new Date(subscription.endDate);
    const today = new Date();
    const diffTime = endDate - today;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays > 0 ? diffDays : 0;
  };

  const value = {
    subscription,
    subscribe,
    cancelSubscription,
    renewSubscription,
    isSubscribed,
    getDaysRemaining,
  };

  return (
    <SubscriptionContext.Provider value={value}>
      {children}
    </SubscriptionContext.Provider>
  );
};



