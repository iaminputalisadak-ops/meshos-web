import { useState, useEffect } from 'react';
import { categoriesAPI } from '../services/api';

/**
 * Custom hook for fetching categories
 */
export const useCategories = () => {
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchCategories = async () => {
      try {
        setLoading(true);
        setError(null);
        
        const data = await categoriesAPI.getAll();
        
        // Transform data to match frontend format
        // Add default categories if API doesn't return them
        const defaultCategories = [
          { id: 1, name: 'Popular', slug: 'popular' },
        ];
        
        const transformedData = data.map(cat => ({
          id: cat.id,
          name: cat.name,
          slug: cat.slug || cat.name.toLowerCase().replace(/\s+/g, '-'),
          icon: getCategoryIcon(cat.name),
          image: getCategoryImage(cat.name),
        }));
        
        // Merge with default categories
        const allCategories = [...defaultCategories, ...transformedData];
        setCategories(allCategories);
      } catch (err) {
        console.error('Error fetching categories:', err);
        setError(err.message);
        // Fallback to default categories on error
        setCategories([
          { id: 1, name: 'Popular', slug: 'popular', icon: '⭐', image: 'https://images.unsplash.com/photo-1513151233558-d860c5398176?w=200&q=80' },
        ]);
      } finally {
        setLoading(false);
      }
    };

    fetchCategories();
  }, []);

  return { categories, loading, error };
};

/**
 * Get category icon based on name
 */
function getCategoryIcon(name) {
  const iconMap = {
    'Popular': '⭐',
    'Kurti, Saree & Lehenga': '👗',
    'Women Western': '👚',
    'Lingerie': '👙',
    'Men': '👔',
    'Kids & Toys': '👶',
    'Home & Kitchen': '🏠',
    'Beauty & Health': '💄',
    'Jewellery & Accessories': '💍',
    'Bags & Footwear': '👜',
    'Electronics': '📱',
  };
  return iconMap[name] || '📦';
}

/**
 * Get category image based on name
 */
function getCategoryImage(name) {
  const imageMap = {
    'Popular': 'https://images.unsplash.com/photo-1513151233558-d860c5398176?w=200&q=80',
    'Kurti, Saree & Lehenga': 'https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?w=200&q=80',
    'Women Western': 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=200&q=80',
    'Lingerie': 'https://images.unsplash.com/photo-1583496661160-fb588827e1a3?w=200&q=80',
    'Men': 'https://images.unsplash.com/photo-1617137968427-85924c800a22?w=200&q=80',
    'Kids & Toys': 'https://images.unsplash.com/photo-1555252333-9f8e92e65df9?w=200&q=80',
    'Home & Kitchen': 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=200&q=80',
    'Beauty & Health': 'https://images.unsplash.com/photo-1596462502278-27bfdc403348?w=200&q=80',
    'Jewellery & Accessories': 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=200&q=80',
    'Bags & Footwear': 'https://images.unsplash.com/photo-1544966503-7cc5ac882d5f?w=200&q=80',
    'Electronics': 'https://images.unsplash.com/photo-1468495244123-6c6c332eeece?w=200&q=80',
  };
  return imageMap[name] || 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=200&q=80';
}

