import { useEffect, useState } from 'react'
import { NavLink, useLocation, useSearchParams } from 'react-router-dom'
import { getData } from '../api'
import { Notice, PageHeader } from '../components/Shared'

const currency = new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' })

export function InventoryPage() {
  const [searchParams, setSearchParams] = useSearchParams()
  const location = useLocation()
  const categoryId = searchParams.get('category_id') || '0'
  const [categories, setCategories] = useState([])
  const [products, setProducts] = useState([])
  const [lowStock, setLowStock] = useState([])
  const [error, setError] = useState('')
  useEffect(() => {
    getData({ type: 'categories' }).then((data) => setCategories(data.categories || [])).catch((reason) => setError(reason.message))
    const params = { type: 'products' }
    if (categoryId !== '0') params.category_id = categoryId
    getData(params).then((data) => { setProducts(data.products || []); setLowStock(data.lowStockProducts || []) }).catch((reason) => setError(reason.message))
  }, [categoryId])

  return <>
    <PageHeader title="Inventory" description="Manage products and stock levels." action={<NavLink to="/products/new" className="btn btn-light"><i className="fa-solid fa-plus me-2" />Add product</NavLink>} />
    <Notice error={error} success={location.state?.notice} />
    {lowStock.length > 0 && <div className="alert alert-warning"><strong>Low stock alert</strong><ul className="mb-0 mt-2">{lowStock.map((product) => <li key={product.id}>{product.name} has {product.quantity} item(s) left.</li>)}</ul></div>}
    <div className="toolbar-row"><label htmlFor="inventory-category">Category</label><select id="inventory-category" className="form-select category-filter" value={categoryId} onChange={(event) => setSearchParams(event.target.value === '0' ? {} : { category_id: event.target.value })}><option value="0">All categories</option>{categories.map((category) => <option key={category.id} value={category.id}>{category.name}</option>)}</select><span className="result-count">{products.length} products</span></div>
    <div className="card shadow-sm"><div className="card-body p-0"><div className="table-responsive"><table className="table table-hover align-middle mb-0"><thead className="table-dark"><tr><th>#</th><th>Product</th><th>Category</th><th>Unit price</th><th>Cost price</th><th>Stock</th><th>Actions</th></tr></thead><tbody>
      {products.length ? products.map((product, index) => <tr key={product.id}><td>{index + 1}</td><td className="fw-semibold">{product.name}</td><td>{product.category || 'Uncategorized'}</td><td>{currency.format(Number(product.unit_price))}</td><td>{currency.format(Number(product.cost_price))}</td><td><span className={`stock-value ${Number(product.quantity) < 10 ? 'stock-low' : ''}`}>{product.quantity}</span></td><td><div className="table-actions">{product.isAdmin && <><NavLink title="Edit product" className="btn btn-sm btn-outline-secondary" to={`/products/${product.id}/edit`}><i className="fa-solid fa-pen" /></NavLink><NavLink title="Delete product" className="btn btn-sm btn-outline-danger" to={`/products/${product.id}/delete`}><i className="fa-solid fa-trash" /></NavLink></>}<NavLink title="Import stock" className="btn btn-sm btn-outline-success" to={`/products/${product.id}/import`}><i className="fa-solid fa-arrow-down" /></NavLink><NavLink title="Export stock" className="btn btn-sm btn-outline-primary" to={`/products/${product.id}/export`}><i className="fa-solid fa-arrow-up" /></NavLink></div></td></tr>) : <tr><td colSpan="7" className="text-center text-muted py-5">No products found.</td></tr>}
    </tbody></table></div></div></div>
  </>
}
