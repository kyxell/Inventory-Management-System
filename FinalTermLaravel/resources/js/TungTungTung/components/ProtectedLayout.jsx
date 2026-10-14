import { useEffect, useState } from 'react'
import { NavLink, Outlet, useLocation, useNavigate } from 'react-router-dom'
import { ApiError, getData, request } from '../api'

export function ProtectedLayout() {
  const [status, setStatus] = useState('checking')
  const navigate = useNavigate()
  const location = useLocation()
  const [isSidebarOpen, setSidebarOpen] = useState(true)

  useEffect(() => {
    let active = true
    getData({ type: 'categories' }).then(() => active && setStatus('ready')).catch((error) => {
      if (!active) return
      if (error instanceof ApiError && error.status === 401) navigate('/login', { replace: true, state: { from: location.pathname } })
      else setStatus('error')
    })
    return () => { active = false }
  }, [location.pathname, navigate])

  if (status === 'checking') return <div className="loading-screen">Checking your session---</div>
  if (status === 'error') return <div className="loading-screen">Could not connect to the inventory service. Check the Laravel database and reload.</div>
  return <div className={`app-layout ${isSidebarOpen ? '' : 'sidebar-collapsed'}`}>
    <aside className="sidebar" aria-label="Main navigation">
      <div className="sidebar__controls"><div className="brand-mark">SF</div><div className="brand-copy"><strong>Streetfood</strong><span>Stock management</span></div><button className="sidebar__toggle" type="button" title="Collapse sidebar" aria-label="Collapse sidebar" onClick={() => setSidebarOpen(false)}><i className="fa-solid fa-bars" /></button></div>
      <hr />
      <nav><span className="nav-caption">WORKSPACE</span>
        <NavLink to="/" end className={({ isActive }) => `sidebar-link ${isActive ? 'active' : ''}`}><i className="fa-solid fa-chart-line" />Dashboard</NavLink>
        <NavLink to="/inventory" className={({ isActive }) => `sidebar-link ${isActive ? 'active' : ''}`}><i className="fa-solid fa-boxes-stacked" />Inventory</NavLink>
        <NavLink to="/logs" className={({ isActive }) => `sidebar-link ${isActive ? 'active' : ''}`}><i className="fa-solid fa-clock-rotate-left" />Transaction logs</NavLink>
        <NavLink to="/accounts" className={({ isActive }) => `sidebar-link ${isActive ? 'active' : ''}`}><i className="fa-solid fa-users" />Account management</NavLink>
      </nav>
      <button className="sidebar-link sidebar-logout" type="button" onClick={async () => { try { await request('logout_process.php', { method: 'POST' }) } finally { navigate('/login', { replace: true }) } }}><i className="fa-solid fa-arrow-right-from-bracket" />Log out</button>
    </aside>
    {!isSidebarOpen && <button className="sidebar__show" type="button" aria-label="Open sidebar" title="Open sidebar" onClick={() => setSidebarOpen(true)}><i className="fa-solid fa-bars" /></button>}
    <main className="content app-content"><Outlet /></main>
  </div>
}
