import { useEffect, useState } from 'react'
import { getData } from '../api'
import { FilterInput, FilterSelect, Notice, PageHeader } from '../components/Shared'
import { formatDate } from '../utils/date'

export function LogsPage() {
  const [categories, setCategories] = useState([]); 
  const [products, setProducts] = useState([]); 
  const [logs, setLogs] = useState([]); 
  const [pagination, setPagination] = useState(null); 
  const [page, setPage] = useState(1); const [error, setError] = useState('')
  const [filters, setFilters] = 
    useState({ 
      category_id: '0', 
      product_id: '0', 
      direction: '', 
      date_from: '', 
      date_to: ''
    })

  useEffect(() => { 
    getData({type: 'categories' })
    .then((data) => setCategories(data.categories || []))
    .catch((reason) => setError(reason.message)) }, [])

  useEffect(() => {
    const params = filters.category_id === '0' ? { type: 'all_products' } : { type: 'products_by_category', category_id: filters.category_id }
    getData(params)
    .then((data) => setProducts(data.products || []))
    .catch((reason) => setError(reason.message))
  }, [filters.category_id])

  useEffect(() => {
    getData({ type: 'logs', ...filters, page: String(page), per_page: '25' })
    .then((data) => { setLogs(data.logs || []); setPagination(data.pagination) })
    .catch((reason) => setError(reason.message))
  }, [filters, page])

  function changeFilter(event) {
    setFilters((current) => ({ ...current, [event.target.name]: event.target.value, ...(event.target.name === 'category_id' ? { product_id: '0' } : {}) })); setPage(1) }
  const visibleProducts = products
  return <>
    <PageHeader title="Transaction logs" description="Review stock movements and filter activity." icon="fa-clock-rotate-left" /><Notice error={error} />
      <div className="card mb-3 shadow-sm">
        <div className="card-body">
          <div className="row g-3 align-items-end">
            <FilterSelect label="Category" name="category_id" value={filters.category_id} change={changeFilter}><option value="0">All categories</option>{categories.map((category) => <option key={category.id} value={category.id}>{category.name}</option>)}</FilterSelect>
            <FilterSelect label="Product" name="product_id" value={filters.product_id} change={changeFilter}><option value="0">All products</option>{visibleProducts.map((product) => <option key={product.id} value={product.id}>{product.name}</option>)}</FilterSelect>
            <FilterSelect label="Direction" name="direction" value={filters.direction} change={changeFilter}><option value="">All</option><option value="in">Stock in</option><option value="out">Stock out</option></FilterSelect>
            <FilterInput label="From" name="date_from" value={filters.date_from} change={changeFilter} /><FilterInput label="To" name="date_to" value={filters.date_to} change={changeFilter} />
            <div className="col-auto">
              <button className="btn btn-outline-secondary" type="button" onClick={() => { setFilters({ category_id: '0', product_id: '0', direction: '', date_from: '', date_to: '' }); setPage(1) }}>Reset</button>
            </div>
          </div>
        </div>
      </div>
    <div className="card shadow-sm">
      <div className="card-body">
        <div className="table-responsive">
          <table className="table table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>Date/time</th>
                <th>Product</th>
                <th>Category</th>
                <th>Direction</th>
                <th className="text-end">Change</th>
                <th className="text-end">Resulting quantity</th>
              </tr>
            </thead>
            <tbody>{logs.map((log) => <tr key={log.id}>
              <td>{formatDate(log.created_at)}</td>
              <td>{log.product_name}</td>
              <td>{log.category_name}</td>
              <td><span className={`badge ${log.direction === 'in' ? 'bg-success' : 'bg-danger'}`}>{log.direction === 'in' ? 'Stock in' : 'Stock out'}</span></td>
              <td className="text-end">{log.quantity_change > 0 ? '+' : ''}{log.quantity_change}</td>
              <td className="text-end">{log.new_quantity}</td></tr>)}
            </tbody>
          </table>
        </div>
      {!logs.length && <div className="text-center p-4"><p className="text-muted mb-0">No transaction logs found for the selected filters.</p></div>}
      {pagination?.total > 0 && <nav className="d-flex justify-content-between align-items-center mt-3"><span className="text-muted small">Showing {(page - 1) * pagination.per_page + 1} ○ {Math.min(page * pagination.per_page, pagination.total)} of {pagination.total}</span><div className="btn-group"><button className="btn btn-outline-secondary btn-sm" disabled={page <= 1} onClick={() => setPage((current) => current - 1)}>Previous</button><button className="btn btn-outline-secondary btn-sm" disabled={page >= pagination.total_pages} onClick={() => setPage((current) => current + 1)}>Next</button></div></nav>}
    </div></div>
  </>
}

