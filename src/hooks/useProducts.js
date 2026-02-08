import { useState, useEffect } from 'react';
import { productsAPI } from '../services/api';

/**
 * Custom hook for fetching products
 */
export const useProducts = (category = null, search = null) => {
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchProducts = async () => {
      try {
        setLoading(true);
        setError(null);
        
        let data;
        if (category && category !== 'Popular') {
          data = await productsAPI.getByCategory(category);
        } else {
          data = await productsAPI.getAll(search);
        }
        
        // Transform data to match frontend format
        const transformedData = data.map(product => ({
          id: product.id,
          name: product.name,
          category: product.category_name || product.category,
          price: parseFloat(product.price),
          originalPrice: parseFloat(product.original_price || product.price),
          discount: product.discount || 0,
          image: product.image || (product.images && product.images.length > 0 ? product.images[0] : null),
          images: product.images || (product.image ? [product.image] : []),
          rating: parseFloat(product.rating || 0),
          reviews: parseInt(product.reviews || 0),
          description: product.description || '',
          inStock: product.in_stock !== undefined ? Boolean(product.in_stock) : true,
        }));
        
        setProducts(transformedData);
      } catch (err) {
        console.error('Error fetching products:', err);
        setError(err.message);
        // Fallback to empty array on error
        setProducts([]);
      } finally {
        setLoading(false);
      }
    };

    fetchProducts();
  }, [category, search]);

  return { products, loading, error };
};

/**
 * Custom hook for fetching a single product
 */
export const useProduct = (productId) => {
  const [product, setProduct] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    if (!productId) {
      setLoading(false);
      return;
    }

    const fetchProduct = async () => {
      try {
        setLoading(true);
        setError(null);
        
        const data = await productsAPI.getById(productId);
        
        // Transform data to match frontend format
        const transformedProduct = {
          id: data.id,
          name: data.name,
          category: data.category_name || data.category,
          price: parseFloat(data.price),
          originalPrice: parseFloat(data.original_price || data.price),
          discount: data.discount || 0,
          image: data.image || (data.images && data.images.length > 0 ? data.images[0] : null),
          images: data.images || (data.image ? [data.image] : []),
          rating: parseFloat(data.rating || 0),
          reviews: parseInt(data.reviews || 0),
          description: data.description || '',
          inStock: data.in_stock !== undefined ? Boolean(data.in_stock) : true,
        };
        
        setProduct(transformedProduct);
      } catch (err) {
        console.error('Error fetching product:', err);
        setError(err.message);
        setProduct(null);
      } finally {
        setLoading(false);
      }
    };

    fetchProduct();
  }, [productId]);

  return { product, loading, error };
};

