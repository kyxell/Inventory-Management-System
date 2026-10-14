import { useEffect, useState } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import { getData, postForm } from '../api'
import { Notice, PageHeader } from '../components/Shared'

export function ProductForm() {
  const { id } = useParams(); 
  const editing = Boolean(id); 
  const navigate = useNavigate()
  const [categories, setCategories] = useState([])
  const [values, setValues] = useState({ name: '', description: '', category_id: '', unit_price: '', cost_price: '', quantity: '' })
  const [error, setError] = useState(''); 
  const [busy, setBusy] = useState(false)

  useEffect(() => {
    getData({ type: 'categories' })
    .then((data) => setCategories(data.categories || []))
    .catch((reason) => setError(reason.message))
    if (editing) getData({ type: 'product_for_edit', id })
      .then((data) => setValues({ ...data, quantity: '' }))
    .catch((reason) => setError(reason.message))
  }, [editing, id])
  function update(event) { 
    setValues((current) => ({ ...current, [event.target.name]: event.target.value })) 
  }

  async function submit(event) {
    event.preventDefault(); setBusy(true); setError('')
    try {
      const payload = { ...values }
      if (editing) payload.id = id
      const result = await postForm(editing ? 'edit_process.php' : 'add_process.php', payload)
      if (!result.success) throw new Error(result.error || 'Could not save the product.')
      navigate('/inventory', { state: { notice: result.message } })
    } catch (reason) { setError(reason.message) } finally { setBusy(false) }
  }

  return <>
  <PageHeader title={editing ? 'Edit product' : 'Add product'} description={editing ? 'Update product details.' : 'Create a product and set its initial stock.'} icon={editing ? 'fa-pen' : 'fa-plus'} />
  <Notice error={error} />
  <div className="card form-card">
    <div className="card-body">
      <form onSubmit={submit}>
      <div className="row mb-3">
        <label className="col-sm-4 col-form-label" htmlFor="product-name">Product name</label>
        <div className="col-sm-8">
          <input id="product-name" className="form-control" name="name" value={values.name || ''} onChange={update} required />
        </div>
      </div>
      <div className="row mb-3">
        <label className="col-sm-4 col-form-label" htmlFor="product-description">Description</label>
        <div className="col-sm-8">
          <textarea id="product-description" className="form-control" name="description" rows="3" value={values.description || ''} onChange={update} required />
        </div>
      </div>
      <div className="row mb-3">
        <label className="col-sm-4 col-form-label" htmlFor="product-category">Category</label>
        <div className="col-sm-8">
          <select id="product-category" className="form-select" name="category_id" value={values.category_id || ''} onChange={update} required>
            <option value="">Select a category</option>{categories.map((category) => <option key={category.id} value={category.id}>{category.name}</option>)}
          </select>
        </div>
      </div>
      <PriceInput label="Unit price" name="unit_price" value={values.unit_price || ''} update={update} />
      <PriceInput label="Cost price" name="cost_price" value={values.cost_price || ''} update={update} />
      {!editing && 
        <div className="row mb-3">
          <label className="col-sm-4 col-form-label" htmlFor="product-quantity">Quantity</label>
          <div className="col-sm-8">
            <input id="product-quantity" className="form-control" type="number" name="quantity" min="1" value={values.quantity} onChange={update} required />
          </div>
        </div>}
      <div className="form-actions">
        <button className="btn btn-primary" type="submit" disabled={busy}>{busy ? 'Saving' : editing ? 'Save changes' : 'Add product'}</button>
        <button className="btn btn-outline-secondary" type="button" onClick={() => navigate('/inventory')}>Cancel</button>
      </div>
      </form>
    </div>
  </div>
  </>
}

function PriceInput({ label, name, value, update }) {
  return (
    <div className="row mb-3">
      <label className="col-sm-4 col-form-label" htmlFor={name}>{label}</label>
      <div className="col-sm-8">
        <div className="input-group">
          <span className="input-group-text">₱</span>
          <input id={name} className="form-control" type="number" name={name} min="0" step="0.01" value={value} onChange={update} required />
        </div>
      </div>
    </div>
  )
}

export function DeleteProduct() {
  const { id } = useParams(); 
  const navigate = useNavigate()
  const [product, setProduct] = useState(null); 
  const [error, setError] = useState(''); 
  const [busy, setBusy] = useState(false)
  useEffect(() => { 
    getData({ type: 'product_for_delete', id })
    .then(setProduct)
    .catch((reason) => setError(reason.message)) }, [id])
  async function submit(event) { 
    event.preventDefault(); setBusy(true); 
    try { const result = await postForm('delete_process.php', { id }); if (!result.success) throw new Error(result.error || 'Could not delete this product.'); navigate('/inventory') } 
    catch (reason) { setError(reason.message) } 
    finally { setBusy(false) } }
  return <>
  <PageHeader title="Delete product" description="This action cannot be undone." icon="fa-trash" />
  <Notice error={error} />
  <div className="card form-card">
    <div className="card-body">
      <div className="alert alert-warning">Delete <strong>{product?.name || 'this product'}</strong> and its inventory record?</div>
      <form onSubmit={submit}>
        <div className="form-actions">
          <button className="btn btn-danger" type="submit" disabled={busy || !product}>{busy ? 'Deleting...' : 'Delete product'}</button>
          <button className="btn btn-outline-secondary" type="button" onClick={() => navigate('/inventory')}>Cancel</button>
        </div>
      </form>
    </div>
  </div>
  </>
}

export function StockForm({ direction }) {
  const { id } = useParams(); 
  const navigate = useNavigate(); 
  const importing = direction === 'import'
  const [product, setProduct] = useState(null); 
  const [quantity, setQuantity] = useState(''); 
  const [error, setError] = useState(''); 
  const [busy, setBusy] = useState(false)

  useEffect(() => { 
    getData({ type: importing ? 'product_for_import' : 'product_for_deliver', id })
    .then(setProduct)
    .catch((reason) => setError(reason.message)) }, [id, importing])

  async function submit(event) { 
    event.preventDefault(); setBusy(true); setError(''); 
    try { const result = await postForm(importing ? 'import_process.php' : 'deliver_process.php', { id, quantity }); if (!result.success) throw new Error(result.error || 'Stock update failed.'); navigate('/inventory', { state: { notice: result.message } }) } 
    catch (reason) { setError(reason.message) } 
    finally { setBusy(false) } }
  return <>
  <PageHeader title={importing ? 'Import stock' : 'Export stock'} description={importing ? 'Add units to the current stock.' : 'Remove units from the current stock.'} icon={importing ? 'fa-arrow-down' : 'fa-arrow-up'} />
  <Notice error={error} />
  <div className="card form-card">
    <div className="card-body">
      <form onSubmit={submit}>
        <div className="row mb-3">
          <label className="col-sm-4 col-form-label">Product</label>
          <div className="col-sm-8"><input className="form-control" value={product?.name || 'Loading...'} readOnly /></div>
        </div>
        <div className="row mb-3">
          <div className="offset-sm-4 col-sm-8">
            <p className="text-muted mb-0">Current stock: <strong>{product?.quantity ?? ''}</strong></p>
          </div>
        </div>
        <div className="row mb-3">
          <label className="col-sm-4 col-form-label" htmlFor="stock-quantity">Quantity</label>
          <div className="col-sm-8">
            <input id="stock-quantity" className="form-control" type="number" min="1" name="quantity" value={quantity} onChange={(event) => setQuantity(event.target.value)} required />
          </div>
        </div>
        <div className="form-actions">
          <button className="btn btn-primary" type="submit" disabled={busy || !product}>{busy ? 'Submitting...' : importing ? 'Import stock' : 'Export stock'}</button>
          <button className="btn btn-outline-secondary" type="button" onClick={() => navigate('/inventory')}>Cancel</button>
        </div>
      </form>
    </div>
  </div>
</>
}
