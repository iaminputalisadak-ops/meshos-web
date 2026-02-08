import React, { useState, useEffect } from 'react';
import { useParams } from 'react-router-dom';
import { products as localProducts } from '../../data/products';
import { useProducts } from '../../hooks/useProducts';
import ProductCard from '../../components/ProductCard/ProductCard';
import './Category.css';

const Category = () => {
  const { categoryName } = useParams();
  const decodedCategory = decodeURIComponent(categoryName);
  const categorySlug = decodedCategory === 'Popular' ? null : decodedCategory.toLowerCase().replace(/\s+/g, '-');
  
  // Fetch from API
  const { products: apiProducts, loading, error } = useProducts(categorySlug);
  
  // Use API data if available, otherwise fallback to local
  const allProducts = apiProducts.length > 0 ? apiProducts : localProducts;
  
  const [categoryProducts, setCategoryProducts] = useState([]);
  const [selectedGender, setSelectedGender] = useState('');
  const [sortBy, setSortBy] = useState('Relevance');
  const [showFilters, setShowFilters] = useState(false);

  useEffect(() => {
    let filtered = [];
    
    // Handle "Popular" category - show all products sorted by rating
    if (decodedCategory === 'Popular') {
      filtered = [...allProducts];
    } else {
      filtered = allProducts.filter(
        (product) => product.category === decodedCategory || 
                    product.category?.toLowerCase().replace(/\s+/g, '-') === categorySlug
      );
    }
    
    if (selectedGender) {
      filtered = filtered.filter(p => {
        if (selectedGender === 'Women' || selectedGender === 'Men') {
          return p.category.includes(selectedGender);
        }
        return true;
      });
    }

    // Sort products
    if (sortBy === 'Price: Low to High') {
      filtered.sort((a, b) => a.price - b.price);
    } else if (sortBy === 'Price: High to Low') {
      filtered.sort((a, b) => b.price - a.price);
    } else if (sortBy === 'Rating') {
      filtered.sort((a, b) => b.rating - a.rating);
    } else if (sortBy === 'Relevance' && decodeURIComponent(categoryName) === 'Popular') {
      // For Popular, default sort by rating
      filtered.sort((a, b) => b.rating - a.rating);
    }

    setCategoryProducts(filtered);
  }, [decodedCategory, categorySlug, allProducts, selectedGender, sortBy]);

  return (
    <div className="category-page">
      <div className="container">
        <div className="category-header">
          <h1 className="category-title">
            {decodeURIComponent(categoryName) === 'Popular' 
              ? 'Popular Products' 
              : decodeURIComponent(categoryName)}
          </h1>
          <p className="category-count">
            {categoryProducts.length} {categoryProducts.length === 1 ? 'Product' : 'Products'} found
          </p>
        </div>

        <div className="category-content">
          {/* Filters Sidebar */}
          <aside className="filters-sidebar">
            <div className="filters-header">
              <h3>FILTERS</h3>
              <span className="products-count">{categoryProducts.length}+ Products</span>
            </div>

            <div className="filter-section">
              <div className="filter-section-header">
                <span>Sort by</span>
              </div>
              <select 
                className="sort-select"
                value={sortBy}
                onChange={(e) => setSortBy(e.target.value)}
              >
                <option value="Relevance">Relevance</option>
                <option value="Price: Low to High">Price: Low to High</option>
                <option value="Price: High to Low">Price: High to Low</option>
                <option value="Rating">Rating</option>
              </select>
            </div>

            <div className="filter-section">
              <div className="filter-section-header">
                <span>Gender</span>
                <i className="fas fa-chevron-up"></i>
              </div>
              <div className="gender-filters">
                <button
                  className={`gender-btn ${selectedGender === 'Boys' ? 'active' : ''}`}
                  onClick={() => setSelectedGender(selectedGender === 'Boys' ? '' : 'Boys')}
                >
                  Boys
                </button>
                <button
                  className={`gender-btn ${selectedGender === 'Girls' ? 'active' : ''}`}
                  onClick={() => setSelectedGender(selectedGender === 'Girls' ? '' : 'Girls')}
                >
                  Girls
                </button>
                <button
                  className={`gender-btn ${selectedGender === 'Men' ? 'active' : ''}`}
                  onClick={() => setSelectedGender(selectedGender === 'Men' ? '' : 'Men')}
                >
                  Men
                </button>
                <button
                  className={`gender-btn ${selectedGender === 'Women' ? 'active' : ''}`}
                  onClick={() => setSelectedGender(selectedGender === 'Women' ? '' : 'Women')}
                >
                  Women
                </button>
              </div>
            </div>

            <div className="filter-section">
              <div className="filter-section-header">
                <span>Category</span>
                <i className="fas fa-chevron-up"></i>
              </div>
              <div className="category-filters">
                <input type="text" placeholder="Search" className="filter-search" />
                <div className="filter-checkboxes">
                  <label className="filter-checkbox">
                    <input type="checkbox" />
                    <span>Women T-shirts</span>
                  </label>
                  <label className="filter-checkbox">
                    <input type="checkbox" />
                    <span>Women Tops And Tunics</span>
                  </label>
                  <label className="filter-checkbox">
                    <input type="checkbox" />
                    <span>Analog Watches</span>
                  </label>
                  <label className="filter-checkbox">
                    <input type="checkbox" />
                    <span>Bangles & Bracelets</span>
                  </label>
                </div>
              </div>
            </div>

            <div className="filter-section">
              <div className="filter-section-header">
                <span>Color</span>
                <i className="fas fa-chevron-down"></i>
              </div>
            </div>

            <div className="filter-section">
              <div className="filter-section-header">
                <span>Fabric</span>
                <i className="fas fa-chevron-down"></i>
              </div>
            </div>

            <div className="filter-section">
              <div className="filter-section-header">
                <span>Price</span>
                <i className="fas fa-chevron-down"></i>
              </div>
            </div>

            <div className="filter-section">
              <div className="filter-section-header">
                <span>Rating</span>
                <i className="fas fa-chevron-down"></i>
              </div>
            </div>
          </aside>

          {/* Products Grid */}
          <div className="products-container">
            {loading ? (
              <div className="loading-products">
                <i className="fas fa-spinner fa-spin"></i>
                <p>Loading products...</p>
              </div>
            ) : categoryProducts.length > 0 ? (
              <div className="products-grid">
                {categoryProducts.map((product) => (
                  <ProductCard key={product.id} product={product} />
                ))}
              </div>
            ) : (
              <div className="no-products">
                <i className="fas fa-box-open"></i>
                <p>No products found in this category</p>
                {error && <p className="error-message">Error: {error}</p>}
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default Category;
