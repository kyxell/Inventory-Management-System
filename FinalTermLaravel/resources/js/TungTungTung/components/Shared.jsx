export function PageHeader({ eyebrow = 'INVENTORY MANAGEMENT', title, description, icon = 'fa-boxes-stacked', action }) {
  return <header className="dashboard-header mb-4"><div><span className="dashboard-label">{eyebrow}</span><h1>{title}</h1><p>{description}</p></div><div className="header-actions">{action}<div className="dashboard-header-icon"><i className={`fa-solid ${icon}`} /></div></div></header>
}

export function Notice({ error, success }) {
  if (!error && !success) return null
  return <div className={`alert ${error ? 'alert-danger' : 'alert-success'}`} role="alert">{error || success}</div>
}

export function FilterSelect({ label, name, value, change, children }) {
  return <div className="col-md-auto"><label htmlFor={`filter-${name}`} className="form-label fw-semibold">{label}</label><select id={`filter-${name}`} className="form-select" name={name} value={value} onChange={change}>{children}</select></div>
}

export function FilterInput({ label, name, value, change }) {
  return <div className="col-md-auto"><label htmlFor={`filter-${name}`} className="form-label fw-semibold">{label}</label><input id={`filter-${name}`} className="form-control" type="date" name={name} value={value} onChange={change} /></div>
}

