import React from 'react';

/**
 * ProductCard component modularized for Figma / Figma Make imports
 */
export function ProductCard({ id, name, description, price, isAvailable, onEdit, onDelete }) {
  return (
    <div className="card border-0 shadow-sm">
      <div className="card-body d-flex justify-content-between align-items-center">
        <div>
          <div className="d-flex align-items-center gap-2 mb-1">
            <h6 className="fw-bold mb-0">{name}</h6>
            {isAvailable ? (
              <span className="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                <i className="fas fa-check-circle me-1"></i>In Stock
              </span>
            ) : (
              <span className="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">
                <i className="fas fa-times-circle me-1"></i>Out of Stock
              </span>
            )}
          </div>
          <p className="card-text text-muted small mb-2">{description || 'No description provided.'}</p>
          <span className="fw-bold fs-5 text-dark">${typeof price === 'number' ? price.toFixed(2) : price}</span>
        </div>

        <div className="d-flex gap-1">
          <button className="btn btn-outline-primary btn-sm p-2" title="Edit Product" onClick={onEdit}>
            <i className="fas fa-edit"></i>
          </button>
          <button className="btn btn-outline-danger btn-sm p-2" title="Delete Product" onClick={onDelete}>
            <i className="fas fa-trash"></i>
          </button>
        </div>
      </div>
    </div>
  );
}

export default ProductCard;
