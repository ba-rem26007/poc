import React, { useState, useEffect } from 'react';
import { sendErrorToBackend } from './logger';

const API_BASE = import.meta.env.VITE_API_URL || 'https://localhost/api';

export function App() {
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  // Auth state
  const [token, setToken] = useState(() => localStorage.getItem('jwt_token') || '');
  const [loginEmail, setLoginEmail] = useState('admin@example.com');
  const [loginPassword, setLoginPassword] = useState('admin123');
  const [loginError, setLoginError] = useState('');
  const [authenticating, setAuthenticating] = useState(false);
  const [showLoginModal, setShowLoginModal] = useState(false);

  // Profile Modal State
  const [showProfileModal, setShowProfileModal] = useState(false);
  const [profile, setProfile] = useState({ fullName: '', email: '', isTwoFactorEnabled: false });
  const [currentPassword, setCurrentPassword] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [updatingProfile, setUpdatingProfile] = useState(false);

  // Password Reset Modal State
  const [showResetModal, setShowResetModal] = useState(false);
  const [resetEmail, setResetEmail] = useState('');
  const [resetTokenInput, setResetTokenInput] = useState('');
  const [resetNewPassword, setResetNewPassword] = useState('');
  const [generatedToken, setGeneratedToken] = useState('');
  const [resetStep, setResetStep] = useState(1);

  // RAG AI State
  const [showRagModal, setShowRagModal] = useState(false);
  const [ragQuestion, setRagQuestion] = useState('');
  const [ragAnswer, setRagAnswer] = useState('');
  const [askingRag, setAskingRag] = useState(false);
  const [ragSource, setRagSource] = useState('');

  // Notifications State
  const [notifications, setNotifications] = useState([]);
  const [showNotificationsDropdown, setShowNotificationsDropdown] = useState(false);

  // Form state
  const [newProduct, setNewProduct] = useState({ name: '', description: '', price: '', isAvailable: true });
  const [creating, setCreating] = useState(false);

  // Edit Product Modal State
  const [editingProduct, setEditingProduct] = useState(null);
  const [updating, setUpdating] = useState(false);

  // Toast State
  const [toast, setToast] = useState({ message: '', type: '' });

  const showToast = (message, type = 'danger') => {
    setToast({ message, type });
    setTimeout(() => setToast({ message: '', type: '' }), 4000);
  };

  useEffect(() => {
    const handleGlobalError = (event) => {
      sendErrorToBackend(event.error || event.message);
      showToast(event.message || 'An unexpected error occurred', 'danger');
    };
    window.addEventListener('error', handleGlobalError);
    return () => window.removeEventListener('error', handleGlobalError);
  }, []);

  const fetchProducts = async () => {
    setLoading(true);
    setError(null);
    try {
      const headers = { 'Accept': 'application/ld+json' };
      if (token) headers['Authorization'] = `Bearer ${token}`;

      const response = await fetch(`${API_BASE}/products`, { headers });
      if (!response.ok) {
        const msg = `Error ${response.status}: ${response.statusText}`;
        sendErrorToBackend(msg, 'fetchProducts failed');
        throw new Error(msg);
      }
      const data = await response.json();
      setProducts(data['member'] || data['hydra:member'] || data);
    } catch (err) {
      setError(err.message || 'Failed to connect to Symfony API');
    } finally {
      setLoading(false);
    }
  };

  const fetchUserProfile = async () => {
    if (!token) return;
    try {
      const response = await fetch(`${API_BASE}/me`, {
        headers: { 'Authorization': `Bearer ${token}` }
      });
      if (response.ok) {
        const data = await response.json();
        setProfile(data);
      }
    } catch (err) {
      console.error(err);
    }
  };

  const fetchNotifications = async () => {
    if (!token) return;
    try {
      const response = await fetch(`${API_BASE}/notifications`, {
        headers: { 'Accept': 'application/ld+json', 'Authorization': `Bearer ${token}` }
      });
      if (response.ok) {
        const data = await response.json();
        setNotifications(data['member'] || data['hydra:member'] || data || []);
      }
    } catch (err) {
      console.error(err);
    }
  };

  useEffect(() => {
    fetchProducts();
    if (token) {
      fetchUserProfile();
      fetchNotifications();
    } else {
      setProfile({ fullName: '', email: '', isTwoFactorEnabled: false });
      setNotifications([]);
    }
  }, [token]);

  const handleLogin = async (e) => {
    e.preventDefault();
    setLoginError('');
    setAuthenticating(true);
    try {
      const response = await fetch(`${API_BASE}/login_check`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username: loginEmail, password: loginPassword }),
      });

      if (!response.ok) {
        const msg = 'Invalid credentials';
        sendErrorToBackend(msg, `Login attempt failed for ${loginEmail}`);
        throw new Error(msg);
      }

      const data = await response.json();
      if (data.token) {
        setToken(data.token);
        localStorage.setItem('jwt_token', data.token);
        setShowLoginModal(false);
        showToast('Successfully authenticated via JWT!', 'success');
      } else {
        throw new Error('No token received');
      }
    } catch (err) {
      setLoginError(err.message || 'Authentication failed');
    } finally {
      setAuthenticating(false);
    }
  };

  const handleLogout = () => {
    setToken('');
    localStorage.removeItem('jwt_token');
    showToast('Logged out from JWT session', 'info');
  };

  const handleAskRag = async (e) => {
    e.preventDefault();
    if (!ragQuestion) return;
    if (!token) {
      setShowLoginModal(true);
      showToast('Please log in with JWT to use AI Assistant', 'warning');
      return;
    }

    setAskingRag(true);
    setRagAnswer('');
    try {
      const response = await fetch(`${API_BASE}/rag/ask`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({ question: ragQuestion })
      });

      if (!response.ok) {
        throw new Error('Failed to query RAG Proxy');
      }

      const data = await response.json();
      setRagAnswer(data.answer);
      setRagSource(data.source);
    } catch (err) {
      showToast(err.message, 'danger');
    } finally {
      setAskingRag(false);
    }
  };

  const handleUpdateProfile = async (e) => {
    e.preventDefault();
    setUpdatingProfile(true);
    try {
      const response = await fetch(`${API_BASE}/me`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({
          fullName: profile.fullName,
          isTwoFactorEnabled: profile.isTwoFactorEnabled,
          currentPassword: currentPassword || undefined,
          newPassword: newPassword || undefined
        })
      });

      if (!response.ok) {
        const errData = await response.json();
        throw new Error(errData.error || 'Failed to update profile');
      }

      const data = await response.json();
      setProfile(data);
      setCurrentPassword('');
      setNewPassword('');
      setShowProfileModal(false);
      showToast('Profile updated successfully!', 'success');
    } catch (err) {
      showToast(err.message, 'danger');
    } finally {
      setUpdatingProfile(false);
    }
  };

  const handleRequestPasswordReset = async (e) => {
    e.preventDefault();
    try {
      const response = await fetch(`${API_BASE}/password_reset/request`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: resetEmail })
      });
      const data = await response.json();
      if (data.resetToken) {
        setGeneratedToken(data.resetToken);
        setResetTokenInput(data.resetToken);
      }
      showToast('Password reset token generated!', 'info');
      setResetStep(2);
    } catch (err) {
      showToast('Failed to request password reset', 'danger');
    }
  };

  const handleResetPassword = async (e) => {
    e.preventDefault();
    try {
      const response = await fetch(`${API_BASE}/password_reset/reset`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ token: resetTokenInput, newPassword: resetNewPassword })
      });

      if (!response.ok) {
        const errData = await response.json();
        throw new Error(errData.error || 'Password reset failed');
      }

      showToast('Password reset successfully! You can now log in.', 'success');
      setShowResetModal(false);
      setResetStep(1);
      setShowLoginModal(true);
    } catch (err) {
      showToast(err.message, 'danger');
    }
  };

  const handleCreateProduct = async (e) => {
    e.preventDefault();
    if (!newProduct.name || !newProduct.price) return;

    if (!token) {
      setShowLoginModal(true);
      showToast('Please log in with JWT to create products', 'warning');
      return;
    }

    setCreating(true);
    try {
      const headers = {
        'Content-Type': 'application/ld+json',
        'Accept': 'application/ld+json',
        'Authorization': `Bearer ${token}`
      };

      const response = await fetch(`${API_BASE}/products`, {
        method: 'POST',
        headers,
        body: JSON.stringify({
          name: newProduct.name,
          description: newProduct.description,
          price: parseFloat(newProduct.price),
          isAvailable: newProduct.isAvailable,
        }),
      });

      if (!response.ok) {
        const msg = response.status === 401 || response.status === 403
          ? 'Authentication required or insufficient permissions'
          : `Failed to create product (${response.status})`;
        sendErrorToBackend(msg, JSON.stringify(newProduct));
        throw new Error(msg);
      }

      setNewProduct({ name: '', description: '', price: '', isAvailable: true });
      showToast('Product created successfully!', 'success');
      await fetchProducts();
    } catch (err) {
      showToast(err.message, 'danger');
    } finally {
      setCreating(false);
    }
  };

  const handleUpdateProduct = async (e) => {
    e.preventDefault();
    if (!editingProduct) return;

    if (!token) {
      setShowLoginModal(true);
      showToast('Please log in with JWT to edit products', 'warning');
      return;
    }

    setUpdating(true);
    try {
      const headers = {
        'Content-Type': 'application/ld+json',
        'Accept': 'application/ld+json',
        'Authorization': `Bearer ${token}`
      };

      const productId = editingProduct.id || editingProduct['@id'].split('/').pop();
      const response = await fetch(`${API_BASE}/products/${productId}`, {
        method: 'PUT',
        headers,
        body: JSON.stringify({
          name: editingProduct.name,
          description: editingProduct.description,
          price: parseFloat(editingProduct.price),
          isAvailable: editingProduct.isAvailable,
        }),
      });

      if (!response.ok) {
        const msg = response.status === 401 || response.status === 403
          ? 'Authentication required or insufficient permissions'
          : `Failed to update product (${response.status})`;
        sendErrorToBackend(msg, JSON.stringify(editingProduct));
        throw new Error(msg);
      }

      setEditingProduct(null);
      showToast('Product updated successfully!', 'success');
      await fetchProducts();
    } catch (err) {
      showToast(err.message, 'danger');
    } finally {
      setUpdating(false);
    }
  };

  const handleDeleteProduct = async (id) => {
    if (!token) {
      setShowLoginModal(true);
      showToast('Please log in with JWT to delete products', 'warning');
      return;
    }

    if (!confirm('Are you sure you want to delete this product?')) return;
    try {
      const headers = { 'Authorization': `Bearer ${token}` };

      const response = await fetch(`${API_BASE}/products/${id}`, {
        method: 'DELETE',
        headers,
      });

      if (!response.ok) {
        const msg = response.status === 403
          ? 'Only Admin users can delete products'
          : `Failed to delete product (${response.status})`;
        sendErrorToBackend(msg, `Product ID: ${id}`);
        throw new Error(msg);
      }

      showToast('Product deleted', 'info');
      await fetchProducts();
    } catch (err) {
      showToast(err.message, 'danger');
    }
  };

  return (
    <div className="bg-light min-vh-100">

      {/* Toast Alert */}
      {toast.message && (
        <div className="position-fixed bottom-0 end-0 p-3" style={{ zIndex: 1080 }}>
          <div className={`alert alert-${toast.type} alert-dismissible fade show shadow-lg`} role="alert">
            <i className="fas fa-info-circle me-2"></i>
            {toast.message}
            <button type="button" className="btn-close" onClick={() => setToast({ message: '', type: '' })}></button>
          </div>
        </div>
      )}

      {/* Navbar */}
      <nav className="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm py-3">
        <div className="container">
          <a className="navbar-brand d-flex align-items-center gap-2 fw-bold" href="#">
            <i className="fas fa-cubes text-info fs-4"></i>
            <span>3-Tier TLS Platform (PWA)</span>
          </a>

          <div className="d-flex align-items-center gap-2">
            <span className="badge bg-success d-inline-flex align-items-center gap-1 px-2 py-2">
              <i className="fas fa-lock"></i> TLS Encrypted
            </span>

            <button className="btn btn-warning btn-sm d-flex align-items-center gap-1 fw-bold text-dark" onClick={() => setShowRagModal(true)}>
              <i className="fas fa-robot"></i> AI RAG Mistral
            </button>

            {token ? (
              <div className="d-flex align-items-center gap-2">
                {/* Notifications Dropdown */}
                <div className="position-relative">
                  <button className="btn btn-outline-light btn-sm position-relative" onClick={() => setShowNotificationsDropdown(!showNotificationsDropdown)}>
                    <i className="fas fa-bell"></i>
                    {notifications.length > 0 && (
                      <span className="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        {notifications.length}
                      </span>
                    )}
                  </button>
                  {showNotificationsDropdown && (
                    <div className="position-absolute end-0 mt-2 bg-white text-dark rounded shadow p-2" style={{ width: '280px', zIndex: 1050 }}>
                      <h6 className="fw-bold border-bottom pb-2 mb-2"><i className="fas fa-bell me-1"></i>Notifications</h6>
                      {notifications.length === 0 ? (
                        <p className="text-muted small mb-0">No new notifications</p>
                      ) : (
                        notifications.map((n, i) => (
                          <div key={i} className="p-1 border-bottom small">
                            <strong>{n.title}</strong>
                            <p className="mb-0 text-muted">{n.message}</p>
                          </div>
                        ))
                      )}
                    </div>
                  )}
                </div>

                {/* Profile Button */}
                <button className="btn btn-outline-info btn-sm d-flex align-items-center gap-1" onClick={() => setShowProfileModal(true)}>
                  <i className="fas fa-user-circle"></i> Profile
                </button>

                <button className="btn btn-outline-danger btn-sm d-flex align-items-center gap-1" onClick={handleLogout}>
                  <i className="fas fa-sign-out-alt"></i> Logout
                </button>
              </div>
            ) : (
              <button className="btn btn-success btn-sm d-flex align-items-center gap-1 fw-semibold" onClick={() => setShowLoginModal(true)}>
                <i className="fas fa-sign-in-alt"></i> JWT Login
              </button>
            )}

            <a href="https://localhost/doc" target="_blank" rel="noreferrer" className="btn btn-info btn-sm d-flex align-items-center gap-1 text-white fw-semibold">
              <i className="fas fa-sitemap"></i> Arch Doc
            </a>
            <a href="https://localhost/admin" target="_blank" rel="noreferrer" className="btn btn-primary btn-sm d-flex align-items-center gap-1">
              <i className="fas fa-user-shield"></i> EasyAdmin
            </a>
            <a href="https://localhost/api" target="_blank" rel="noreferrer" className="btn btn-secondary btn-sm d-flex align-items-center gap-1">
              <i className="fas fa-book"></i> Swagger
            </a>
          </div>
        </div>
      </nav>

      {/* Main Container */}
      <div className="container my-4">

        {/* RAG AI Assistant Modal */}
        {showRagModal && (
          <div className="modal d-block bg-dark bg-opacity-50" tabIndex="-1">
            <div className="modal-dialog modal-dialog-centered modal-lg">
              <div className="modal-content shadow">
                <div className="modal-header bg-warning text-dark">
                  <h5 className="modal-title fw-bold"><i className="fas fa-robot me-2"></i>Assistant IA Mistral (Proxy RAG)</h5>
                  <button type="button" className="btn-close" onClick={() => setShowRagModal(false)}></button>
                </div>
                <form onSubmit={handleAskRag}>
                  <div className="modal-body">
                    <p className="text-muted small mb-3">
                      Posez une question sur le catalogue. La requête est transmise au proxy Symfony qui enrichit le prompt avec le contexte PostgreSQL avant d'interroger Mistral AI.
                    </p>

                    <div className="mb-3">
                      <label className="form-label fw-semibold">Votre question</label>
                      <input type="text" className="form-control" placeholder="ex: Quels produits sont disponibles en stock sous 50$ ?" value={ragQuestion} onChange={(e) => setRagQuestion(e.target.value)} required />
                    </div>

                    {askingRag && (
                      <div className="text-center py-3 text-muted">
                        <i className="fas fa-spinner fa-spin me-2"></i>Génération RAG en cours...
                      </div>
                    )}

                    {ragAnswer && (
                      <div className="alert alert-secondary mt-3">
                        <div className="d-flex justify-content-between align-items-center mb-1">
                          <strong className="text-dark"><i className="fas fa-brain me-1 text-warning"></i>Réponse de l'Assistant :</strong>
                          <span className="badge bg-dark">{ragSource}</span>
                        </div>
                        <p className="mb-0 style-pre-wrap">{ragAnswer}</p>
                      </div>
                    )}
                  </div>
                  <div className="modal-footer">
                    <button type="button" className="btn btn-secondary" onClick={() => setShowRagModal(false)}>Fermer</button>
                    <button type="submit" className="btn btn-warning fw-bold text-dark" disabled={askingRag}>
                      {askingRag ? 'Analyse...' : 'Interroger IA'}
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        )}

        {/* JWT Login Modal */}
        {showLoginModal && (
          <div className="modal d-block bg-dark bg-opacity-50" tabIndex="-1">
            <div className="modal-dialog modal-dialog-centered">
              <div className="modal-content shadow">
                <div className="modal-header">
                  <h5 className="modal-title fw-bold"><i className="fas fa-shield-alt text-success me-2"></i>JWT API Authentication</h5>
                  <button type="button" className="btn-close" onClick={() => setShowLoginModal(false)}></button>
                </div>
                <form onSubmit={handleLogin}>
                  <div className="modal-body">
                    <p className="text-muted small">Sign in via <code>/api/login_check</code> as <code>admin@example.com</code> or <code>user@example.com</code>.</p>

                    {loginError && (
                      <div className="alert alert-danger py-2 small"><i className="fas fa-exclamation-circle me-1"></i>{loginError}</div>
                    )}

                    <div className="mb-3">
                      <label className="form-label fw-semibold">Email address</label>
                      <input type="email" className="form-control" required value={loginEmail} onChange={(e) => setLoginEmail(e.target.value)} />
                    </div>
                    <div className="mb-3">
                      <label className="form-label fw-semibold">Password</label>
                      <input type="password" className="form-control" required value={loginPassword} onChange={(e) => setLoginPassword(e.target.value)} />
                    </div>
                    <div className="text-end">
                      <button type="button" className="btn btn-link btn-sm text-decoration-none p-0" onClick={() => { setShowLoginModal(false); setShowResetModal(true); }}>
                        Forgot Password?
                      </button>
                    </div>
                  </div>
                  <div className="modal-footer">
                    <button type="button" className="btn btn-secondary" onClick={() => setShowLoginModal(false)}>Cancel</button>
                    <button type="submit" className="btn btn-success" disabled={authenticating}>
                      {authenticating ? 'Signing in...' : 'Sign In'}
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        )}

        {/* Profile Modal */}
        {showProfileModal && (
          <div className="modal d-block bg-dark bg-opacity-50" tabIndex="-1">
            <div className="modal-dialog modal-dialog-centered">
              <div className="modal-content shadow">
                <div className="modal-header">
                  <h5 className="modal-title fw-bold"><i className="fas fa-user-circle text-info me-2"></i>Mon Profil</h5>
                  <button type="button" className="btn-close" onClick={() => setShowProfileModal(false)}></button>
                </div>
                <form onSubmit={handleUpdateProfile}>
                  <div className="modal-body">
                    <div className="mb-3">
                      <label className="form-label fw-semibold">Email</label>
                      <input type="email" className="form-control" disabled value={profile.email || ''} />
                    </div>
                    <div className="mb-3">
                      <label className="form-label fw-semibold">Nom Complet</label>
                      <input type="text" className="form-control" value={profile.fullName || ''} onChange={(e) => setProfile({ ...profile, fullName: e.target.value })} placeholder="ex: Jules Dupont" />
                    </div>

                    <div className="form-check form-switch mb-3">
                      <input className="form-check-input" type="checkbox" id="2faSwitch" checked={profile.isTwoFactorEnabled} onChange={(e) => setProfile({ ...profile, isTwoFactorEnabled: e.target.checked })} />
                      <label className="form-check-label fw-semibold" htmlFor="2faSwitch">Activer Sécurité 2FA (Double Authentification)</label>
                    </div>

                    <hr />
                    <h6 className="fw-bold text-secondary mb-3"><i className="fas fa-key me-1"></i>Changer le mot de passe</h6>
                    <div className="mb-3">
                      <label className="form-label small fw-semibold">Mot de passe actuel</label>
                      <input type="password" className="form-control" value={currentPassword} onChange={(e) => setCurrentPassword(e.target.value)} />
                    </div>
                    <div className="mb-3">
                      <label className="form-label small fw-semibold">Nouveau mot de passe</label>
                      <input type="password" className="form-control" value={newPassword} onChange={(e) => setNewPassword(e.target.value)} />
                    </div>
                  </div>
                  <div className="modal-footer">
                    <button type="button" className="btn btn-secondary" onClick={() => setShowProfileModal(false)}>Fermer</button>
                    <button type="submit" className="btn btn-primary" disabled={updatingProfile}>
                      {updatingProfile ? 'Enregistrement...' : 'Mettre à jour'}
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        )}

        {/* Password Reset Modal */}
        {showResetModal && (
          <div className="modal d-block bg-dark bg-opacity-50" tabIndex="-1">
            <div className="modal-dialog modal-dialog-centered">
              <div className="modal-content shadow">
                <div className="modal-header">
                  <h5 className="modal-title fw-bold"><i className="fas fa-unlock-alt me-2 text-warning"></i>Mot de Passe Oublié</h5>
                  <button type="button" className="btn-close" onClick={() => setShowResetModal(false)}></button>
                </div>
                {resetStep === 1 ? (
                  <form onSubmit={handleRequestPasswordReset}>
                    <div className="modal-body">
                      <p className="text-muted small">Entrez votre email pour générer un jeton de réinitialisation.</p>
                      <div className="mb-3">
                        <label className="form-label fw-semibold">Adresse Email</label>
                        <input type="email" className="form-control" required value={resetEmail} onChange={(e) => setResetEmail(e.target.value)} placeholder="admin@example.com" />
                      </div>
                    </div>
                    <div className="modal-footer">
                      <button type="button" className="btn btn-secondary" onClick={() => setShowResetModal(false)}>Annuler</button>
                      <button type="submit" className="btn btn-warning">Générer le jeton</button>
                    </div>
                  </form>
                ) : (
                  <form onSubmit={handleResetPassword}>
                    <div className="modal-body">
                      <p className="text-muted small">Un jeton a été généré : <code>{generatedToken}</code></p>
                      <div className="mb-3">
                        <label className="form-label fw-semibold">Jeton de réinitialisation</label>
                        <input type="text" className="form-control" required value={resetTokenInput} onChange={(e) => setResetTokenInput(e.target.value)} />
                      </div>
                      <div className="mb-3">
                        <label className="form-label fw-semibold">Nouveau Mot de Passe</label>
                        <input type="password" className="form-control" required value={resetNewPassword} onChange={(e) => setResetNewPassword(e.target.value)} />
                      </div>
                    </div>
                    <div className="modal-footer">
                      <button type="button" className="btn btn-secondary" onClick={() => setResetStep(1)}>Retour</button>
                      <button type="submit" className="btn btn-success">Réinitialiser le mot de passe</button>
                    </div>
                  </form>
                )}
              </div>
            </div>
          </div>
        )}

        {/* Edit Product Modal */}
        {editingProduct && (
          <div className="modal d-block bg-dark bg-opacity-50" tabIndex="-1">
            <div className="modal-dialog modal-dialog-centered">
              <div className="modal-content shadow">
                <div className="modal-header">
                  <h5 className="modal-title fw-bold"><i className="fas fa-edit me-2 text-primary"></i>Edit Product</h5>
                  <button type="button" className="btn-close" onClick={() => setEditingProduct(null)}></button>
                </div>
                <form onSubmit={handleUpdateProduct}>
                  <div className="modal-body">
                    <div className="mb-3">
                      <label className="form-label fw-semibold">Product Name</label>
                      <input type="text" className="form-control" required value={editingProduct.name} onChange={(e) => setEditingProduct({ ...editingProduct, name: e.target.value })} />
                    </div>
                    <div className="mb-3">
                      <label className="form-label fw-semibold">Description</label>
                      <textarea className="form-control" rows="3" value={editingProduct.description || ''} onChange={(e) => setEditingProduct({ ...editingProduct, description: e.target.value })}></textarea>
                    </div>
                    <div className="mb-3">
                      <label className="form-label fw-semibold">Price ($)</label>
                      <input type="number" step="0.01" className="form-control" required value={editingProduct.price} onChange={(e) => setEditingProduct({ ...editingProduct, price: e.target.value })} />
                    </div>
                    <div className="form-check mb-3">
                      <input className="form-check-input" type="checkbox" id="editCheck" checked={editingProduct.isAvailable ?? editingProduct.available} onChange={(e) => setEditingProduct({ ...editingProduct, isAvailable: e.target.checked, available: e.target.checked })} />
                      <label className="form-check-label fw-semibold" htmlFor="editCheck">In Stock</label>
                    </div>
                  </div>
                  <div className="modal-footer">
                    <button type="button" className="btn btn-secondary" onClick={() => setEditingProduct(null)}>Cancel</button>
                    <button type="submit" className="btn btn-primary" disabled={updating}>
                      {updating ? 'Saving...' : 'Save Changes'}
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        )}

        {/* 2-Column Layout */}
        <div className="row g-4">

          {/* Left Column: Create Product Form */}
          <div className="col-lg-4">
            <div className="card shadow-sm border-0">
              <div className="card-header bg-white border-bottom py-3">
                <h5 className="card-title mb-0 fw-bold"><i className="fas fa-plus-circle text-primary me-2"></i>Add New Product</h5>
              </div>
              <div className="card-body">
                <form onSubmit={handleCreateProduct}>
                  <div className="mb-3">
                    <label className="form-label fw-semibold small">Product Name</label>
                    <input type="text" className="form-control" required value={newProduct.name} onChange={(e) => setNewProduct({ ...newProduct, name: e.target.value })} placeholder="e.g. Wireless Mouse" />
                  </div>
                  <div className="mb-3">
                    <label className="form-label fw-semibold small">Description</label>
                    <textarea className="form-control" rows="3" value={newProduct.description} onChange={(e) => setNewProduct({ ...newProduct, description: e.target.value })} placeholder="Product details..."></textarea>
                  </div>
                  <div className="mb-3">
                    <label className="form-label fw-semibold small">Price ($)</label>
                    <input type="number" step="0.01" className="form-control" required value={newProduct.price} onChange={(e) => setNewProduct({ ...newProduct, price: e.target.value })} placeholder="29.99" />
                  </div>
                  <div className="form-check mb-3">
                    <input className="form-check-input" type="checkbox" id="addCheck" checked={newProduct.isAvailable} onChange={(e) => setNewProduct({ ...newProduct, isAvailable: e.target.checked })} />
                    <label className="form-check-label small fw-semibold" htmlFor="addCheck">Available in Stock</label>
                  </div>
                  <button type="submit" className="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2 fw-semibold" disabled={creating}>
                    <i className="fas fa-plus"></i> {creating ? 'Saving...' : 'Add Product'}
                  </button>
                </form>
              </div>
            </div>
          </div>

          {/* Right Column: Catalog List */}
          <div className="col-lg-8">
            <div className="d-flex justify-content-between align-items-center mb-3">
              <h5 className="fw-bold mb-0"><i className="fas fa-boxes text-secondary me-2"></i>Catalog Products ({products.length})</h5>
              <button className="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1" onClick={fetchProducts}>
                <i className="fas fa-sync-alt"></i> Refresh
              </button>
            </div>

            {loading && <div className="text-center py-5 text-muted"><i className="fas fa-spinner fa-spin me-2"></i>Loading products from Symfony API...</div>}

            {error && (
              <div className="alert alert-danger d-flex align-items-center gap-2 shadow-sm" role="alert">
                <i className="fas fa-exclamation-triangle fs-4"></i>
                <div><strong>API Error:</strong> {error}</div>
              </div>
            )}

            {!loading && !error && products.length === 0 && (
              <div className="card border-0 shadow-sm text-center py-5 text-muted">
                No products found. Add one using the form on the left.
              </div>
            )}

            <div className="d-flex flex-column gap-3">
              {products.map((product) => {
                const available = product.available ?? product.isAvailable;
                const productId = product.id || product['@id'].split('/').pop();
                return (
                  <div key={product.id || product['@id']} className="card border-0 shadow-sm">
                    <div className="card-body d-flex justify-content-between align-items-center">
                      <div>
                        <div className="d-flex align-items-center gap-2 mb-1">
                          <h6 className="fw-bold mb-0">{product.name}</h6>
                          {available ? (
                            <span className="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                              <i className="fas fa-check-circle me-1"></i>In Stock
                            </span>
                          ) : (
                            <span className="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">
                              <i className="fas fa-times-circle me-1"></i>Out of Stock
                            </span>
                          )}
                        </div>
                        <p className="card-text text-muted small mb-2">{product.description || 'No description provided.'}</p>
                        <span className="fw-bold fs-5 text-dark">${typeof product.price === 'number' ? product.price.toFixed(2) : product.price}</span>
                      </div>

                      <div className="d-flex gap-1">
                        <button className="btn btn-outline-primary btn-sm p-2" title="Edit Product" onClick={() => setEditingProduct(product)}>
                          <i className="fas fa-edit"></i>
                        </button>
                        <button className="btn btn-outline-danger btn-sm p-2" title="Delete Product" onClick={() => handleDeleteProduct(productId)}>
                          <i className="fas fa-trash"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                );
              })}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

export default App;
