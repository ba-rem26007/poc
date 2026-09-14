import React, { useState, useEffect } from 'react';
import { Package, ShieldCheck, RefreshCw, ExternalLink, Plus, Trash2, CheckCircle2, XCircle } from 'lucide-react';

const API_BASE = import.meta.env.VITE_API_URL || 'https://localhost/api';

export function App() {
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [newProduct, setNewProduct] = useState({ name: '', description: '', price: '', isAvailable: true });
  const [creating, setCreating] = useState(false);

  const fetchProducts = async () => {
    setLoading(true);
    setError(null);
    try {
      const response = await fetch(`${API_BASE}/products`, {
        headers: {
          'Accept': 'application/ld+json',
        },
      });
      if (!response.ok) {
        throw new Error(`Error ${response.status}: ${response.statusText}`);
      }
      const data = await response.json();
      setProducts(data['member'] || data['hydra:member'] || data);
    } catch (err) {
      setError(err.message || 'Failed to connect to Symfony API');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchProducts();
  }, []);

  const handleCreateProduct = async (e) => {
    e.preventDefault();
    if (!newProduct.name || !newProduct.price) return;
    setCreating(true);
    try {
      const response = await fetch(`${API_BASE}/products`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/ld+json',
          'Accept': 'application/ld+json',
        },
        body: JSON.stringify({
          name: newProduct.name,
          description: newProduct.description,
          price: parseFloat(newProduct.price),
          isAvailable: newProduct.isAvailable,
        }),
      });

      if (!response.ok) {
        throw new Error(`Failed to create product (${response.status})`);
      }

      setNewProduct({ name: '', description: '', price: '', isAvailable: true });
      await fetchProducts();
    } catch (err) {
      alert(`Error creating product: ${err.message}`);
    } finally {
      setCreating(false);
    }
  };

  const handleDeleteProduct = async (id) => {
    if (!confirm('Are you sure you want to delete this product?')) return;
    try {
      const response = await fetch(`${API_BASE}/products/${id}`, {
        method: 'DELETE',
      });
      if (!response.ok) {
        throw new Error(`Failed to delete product (${response.status})`);
      }
      await fetchProducts();
    } catch (err) {
      alert(`Error deleting product: ${err.message}`);
    }
  };

  return (
    <div style={{ fontFamily: 'system-ui, -apple-system, sans-serif', backgroundColor: '#f8fafc', minHeight: '100vh', color: '#0f172a' }}>
      {/* Header */}
      <header style={{ backgroundColor: '#1e293b', color: '#fff', padding: '1.25rem 2rem', boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)' }}>
        <div style={{ maxWidth: '1100px', margin: '0 auto', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem' }}>
            <Package style={{ width: '32px', height: '32px', color: '#38bdf8' }} />
            <div>
              <h1 style={{ margin: 0, fontSize: '1.5rem', fontWeight: 700, letterSpacing: '-0.025em' }}>
                3-Tier TLS Platform
              </h1>
              <span style={{ fontSize: '0.85rem', color: '#94a3b8' }}>React Frontend &bull; Symfony API Platform &bull; EasyAdmin</span>
            </div>
          </div>
          <div style={{ display: 'flex', alignItems: 'center', gap: '1rem' }}>
            <span style={{ display: 'inline-flex', alignItems: 'center', gap: '0.35rem', backgroundColor: '#064e3b', color: '#34d399', padding: '0.35rem 0.75rem', borderRadius: '9999px', fontSize: '0.8rem', fontWeight: 600 }}>
              <ShieldCheck style={{ width: '16px', height: '16px' }} /> TLS Encrypted
            </span>
            <a
              href="https://localhost/admin"
              target="_blank"
              rel="noreferrer"
              style={{ display: 'inline-flex', alignItems: 'center', gap: '0.35rem', backgroundColor: '#3b82f6', color: '#fff', padding: '0.45rem 0.9rem', borderRadius: '0.375rem', fontSize: '0.875rem', fontWeight: 600, textDecoration: 'none' }}
            >
              EasyAdmin <ExternalLink style={{ width: '14px', height: '14px' }} />
            </a>
            <a
              href="https://localhost/api"
              target="_blank"
              rel="noreferrer"
              style={{ display: 'inline-flex', alignItems: 'center', gap: '0.35rem', backgroundColor: '#334155', color: '#e2e8f0', padding: '0.45rem 0.9rem', borderRadius: '0.375rem', fontSize: '0.875rem', fontWeight: 600, textDecoration: 'none' }}
            >
              Swagger Docs <ExternalLink style={{ width: '14px', height: '14px' }} />
            </a>
          </div>
        </div>
      </header>

      {/* Main Content */}
      <main style={{ maxWidth: '1100px', margin: '2rem auto', padding: '0 1rem' }}>
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 2fr', gap: '2rem' }}>

          {/* Create Product Form */}
          <div style={{ backgroundColor: '#fff', borderRadius: '0.75rem', padding: '1.5rem', boxShadow: '0 1px 3px rgba(0,0,0,0.1)', height: 'fit-content' }}>
            <h2 style={{ marginTop: 0, fontSize: '1.25rem', fontWeight: 600, color: '#1e293b', borderBottom: '1px solid #e2e8f0', paddingBottom: '0.75rem' }}>
              Add New Product
            </h2>
            <form onSubmit={handleCreateProduct} style={{ display: 'flex', flexDirection: 'column', gap: '1rem', marginTop: '1rem' }}>
              <div>
                <label style={{ display: 'block', fontSize: '0.875rem', fontWeight: 600, marginBottom: '0.25rem' }}>Product Name</label>
                <input
                  type="text"
                  required
                  value={newProduct.name}
                  onChange={(e) => setNewProduct({ ...newProduct, name: e.target.value })}
                  placeholder="e.g. Wireless Mouse"
                  style={{ width: '100%', padding: '0.5rem', borderRadius: '0.375rem', border: '1px solid #cbd5e1', fontSize: '0.9rem', boxSizing: 'border-box' }}
                />
              </div>
              <div>
                <label style={{ display: 'block', fontSize: '0.875rem', fontWeight: 600, marginBottom: '0.25rem' }}>Description</label>
                <textarea
                  value={newProduct.description}
                  onChange={(e) => setNewProduct({ ...newProduct, description: e.target.value })}
                  placeholder="Product details..."
                  rows={3}
                  style={{ width: '100%', padding: '0.5rem', borderRadius: '0.375rem', border: '1px solid #cbd5e1', fontSize: '0.9rem', boxSizing: 'border-box' }}
                />
              </div>
              <div>
                <label style={{ display: 'block', fontSize: '0.875rem', fontWeight: 600, marginBottom: '0.25rem' }}>Price ($)</label>
                <input
                  type="number"
                  step="0.01"
                  required
                  value={newProduct.price}
                  onChange={(e) => setNewProduct({ ...newProduct, price: e.target.value })}
                  placeholder="29.99"
                  style={{ width: '100%', padding: '0.5rem', borderRadius: '0.375rem', border: '1px solid #cbd5e1', fontSize: '0.9rem', boxSizing: 'border-box' }}
                />
              </div>
              <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                <input
                  type="checkbox"
                  id="isAvailable"
                  checked={newProduct.isAvailable}
                  onChange={(e) => setNewProduct({ ...newProduct, isAvailable: e.target.checked })}
                />
                <label htmlFor="isAvailable" style={{ fontSize: '0.875rem', fontWeight: 500 }}>Available in Stock</label>
              </div>
              <button
                type="submit"
                disabled={creating}
                style={{
                  backgroundColor: '#2563eb',
                  color: '#fff',
                  border: 'none',
                  padding: '0.65rem 1rem',
                  borderRadius: '0.375rem',
                  fontWeight: 600,
                  cursor: 'pointer',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  gap: '0.5rem',
                  marginTop: '0.5rem'
                }}
              >
                <Plus style={{ width: '18px', height: '18px' }} /> {creating ? 'Saving...' : 'Add Product'}
              </button>
            </form>
          </div>

          {/* Product Catalog List */}
          <div>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '1rem' }}>
              <h2 style={{ margin: 0, fontSize: '1.25rem', fontWeight: 600, color: '#1e293b' }}>
                Catalog Products ({products.length})
              </h2>
              <button
                onClick={fetchProducts}
                style={{ display: 'inline-flex', alignItems: 'center', gap: '0.35rem', backgroundColor: '#e2e8f0', color: '#334155', border: 'none', padding: '0.4rem 0.8rem', borderRadius: '0.375rem', cursor: 'pointer', fontWeight: 600, fontSize: '0.85rem' }}
              >
                <RefreshCw style={{ width: '14px', height: '14px' }} /> Refresh
              </button>
            </div>

            {loading && <div style={{ padding: '2rem', textAlign: 'center', color: '#64748b' }}>Loading products from Symfony API...</div>}

            {error && (
              <div style={{ backgroundColor: '#fef2f2', border: '1px solid #fecaca', color: '#991b1b', padding: '1rem', borderRadius: '0.5rem', marginBottom: '1rem' }}>
                <strong>API Connection Error:</strong> {error}
              </div>
            )}

            {!loading && !error && products.length === 0 && (
              <div style={{ backgroundColor: '#fff', padding: '2rem', textAlign: 'center', borderRadius: '0.75rem', color: '#64748b' }}>
                No products found. Add one using the form on the left.
              </div>
            )}

            <div style={{ display: 'grid', gap: '1rem' }}>
              {products.map((product) => {
                const available = product.available ?? product.isAvailable;
                return (
                  <div
                    key={product.id || product['@id']}
                    style={{
                      backgroundColor: '#fff',
                      borderRadius: '0.75rem',
                      padding: '1.25rem',
                      boxShadow: '0 1px 3px rgba(0,0,0,0.08)',
                      display: 'flex',
                      justifyContent: 'space-between',
                      alignItems: 'center',
                      border: '1px solid #e2e8f0'
                    }}
                  >
                    <div>
                      <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', marginBottom: '0.25rem' }}>
                        <h3 style={{ margin: 0, fontSize: '1.1rem', fontWeight: 600, color: '#0f172a' }}>{product.name}</h3>
                        {available ? (
                          <span style={{ display: 'inline-flex', alignItems: 'center', gap: '0.2rem', color: '#16a34a', fontSize: '0.75rem', fontWeight: 600, backgroundColor: '#dcfce7', padding: '0.15rem 0.5rem', borderRadius: '9999px' }}>
                            <CheckCircle2 style={{ width: '12px', height: '12px' }} /> In Stock
                          </span>
                        ) : (
                          <span style={{ display: 'inline-flex', alignItems: 'center', gap: '0.2rem', color: '#dc2626', fontSize: '0.75rem', fontWeight: 600, backgroundColor: '#fee2e2', padding: '0.15rem 0.5rem', borderRadius: '9999px' }}>
                            <XCircle style={{ width: '12px', height: '12px' }} /> Out of Stock
                          </span>
                        )}
                      </div>
                      <p style={{ margin: '0 0 0.5rem 0', fontSize: '0.875rem', color: '#64748b' }}>
                        {product.description || 'No description provided.'}
                      </p>
                      <div style={{ fontSize: '1.15rem', fontWeight: 700, color: '#0f172a' }}>
                        ${typeof product.price === 'number' ? product.price.toFixed(2) : product.price}
                      </div>
                    </div>

                    <button
                      onClick={() => handleDeleteProduct(product.id || product['@id'].split('/').pop())}
                      style={{ backgroundColor: 'transparent', border: 'none', color: '#ef4444', cursor: 'pointer', padding: '0.5rem', borderRadius: '0.375rem' }}
                      title="Delete Product"
                    >
                      <Trash2 style={{ width: '18px', height: '18px' }} />
                    </button>
                  </div>
                );
              })}
            </div>
          </div>
        </div>
      </main>
    </div>
  );
}

export default App;
