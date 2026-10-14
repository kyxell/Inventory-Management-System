import { useEffect, useState } from 'react'
import { getData } from '../api'
import { Notice, PageHeader } from '../components/Shared'

export function DashboardPage() {
  const [summary, setSummary] = useState(null)
  const [categories, setCategories] = useState([])
  const [charts, setCharts] = useState([])
  const [categoryId, setCategoryId] = useState('0')
  const [productId, setProductId] = useState('0')
  const [products, setProducts] = useState([])
  const [error, setError] = useState('')

  useEffect(() => {
    Promise.all([getData({ type: 'inventory_summary' }), getData({ type: 'categories' })])
      .then(([overview, categoryData]) => { setSummary(overview); setCategories(categoryData.categories || []) })
      .catch((reason) => setError(reason.message))
  }, [])
  useEffect(() => {
    const params = { type: 'inventory_charts' }
    if (categoryId !== '0') params.category_id = categoryId
    if (productId !== '0') params.product_id = productId
    getData(params).then((data) => setCharts(Object.values(data.chartsData || {}))).catch((reason) => setError(reason.message))
  }, [categoryId, productId])
  useEffect(() => {
    if (categoryId === '0') return
    getData({ type: 'products_by_category', category_id: categoryId }).then((data) => setProducts(data.products || [])).catch((reason) => setError(reason.message))
  }, [categoryId])

  return <>
    <PageHeader title="Dashboard" description="Overview of your current inventory and stock activity." icon="fa-chart-line" />
    <Notice error={error} />
    <div className="row g-3 mb-4"><SummaryCard icon="fa-box" label="Total products" value={summary?.totalProducts ?? '---'} /><SummaryCard icon="fa-cubes" label="Total inventory" value={summary?.totalInventory ?? '---'} /><SummaryCard icon="fa-triangle-exclamation" label="Low stock items" value={summary?.lowStock ?? '---'} /></div>
    <section className="section-heading"><div><h2>Stock fluctuation</h2><p>Quantity history by product</p></div></section>
    <div className="card mb-3 shadow-sm"><div className="card-body"><div className="row g-3 align-items-end">
      <div className="col-md-4"><label className="form-label" htmlFor="chart-category">Category</label><select id="chart-category" className="form-select" value={categoryId} onChange={(event) => { setCategoryId(event.target.value); setProductId('0') }}><option value="0">All categories</option>{categories.map((category) => <option key={category.id} value={category.id}>{category.name}</option>)}</select></div>
      <div className="col-md-4"><label className="form-label" htmlFor="chart-product">Product</label><select id="chart-product" className="form-select" value={productId} disabled={categoryId === '0' || !products.length} onChange={(event) => setProductId(event.target.value)}><option value="0">All products</option>{products.map((product) => <option key={product.id} value={product.id}>{product.name}</option>)}</select></div>
      <div className="col-md-4"><button className="btn btn-outline-secondary" type="button" onClick={() => { setCategoryId('0'); setProductId('0') }}>Reset filters</button></div>
    </div></div></div>
    {charts.length ? <div className="chart-grid">{charts.map((chart) => <StockChart key={chart.name} chart={chart} />)}</div> : <div className="card text-center p-5" id="emptyMessage"><p className="text-muted mb-0">No stock history logs available to plot graphs.</p></div>}
  </>
}

function SummaryCard({ icon, label, value }) {
  return <div className="col-md-4"><div className="summary-card"><div className="summary-icon"><i className={`fa-solid ${icon}`} /></div><div><p>{label}</p><h2>{value}</h2></div></div></div>
}

function StockChart({ chart }) {
  const values = chart.values || []
  const max = Math.max(...values, 1)
  const points = values.map((value, index) => `${values.length < 2 ? 50 : 12 + index * 76 / (values.length - 1)},${92 - value / max * 76}`).join(' ')
  return <section className="card chart-panel"><div className="card-body"><h3>{chart.name}</h3><svg viewBox="0 0 100 100" role="img" aria-label={`Stock quantity history for ${chart.name}`}><path className="chart-gridline" d="M8 16H96 M8 40H96 M8 64H96 M8 92H96" /><polyline points={points} /><text x="8" y="11">{max}</text><text x="8" y="99">0</text></svg><div className="chart-dates"><span>{chart.labels?.[0] || ''}</span><span>{chart.labels?.at(-1) || ''}</span></div></div></section>
}
