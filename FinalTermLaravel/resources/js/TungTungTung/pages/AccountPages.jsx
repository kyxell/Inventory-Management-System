import { useEffect, useState } from 'react'
import { NavLink, useLocation, useNavigate, useParams } from 'react-router-dom'
import { getAccountData, postForm } from '../api'
import { Notice, PageHeader } from '../components/Shared'
import { formatDate } from '../utils/date'

export function AccountsPage() {
  const [users, setUsers] = useState([])
  const [error, setError] = useState('')
  const [notice, setNotice] = useState('')
  const location = useLocation()
  function load() {
    getAccountData({ type: 'users' })
      .then((data) => setUsers(data.users || []))
      .catch((reason) => setError(reason.message))
  }
  useEffect(load, [])
  async function remove(user) {
    if (!window.confirm(`Delete account ${user.username}?`)) return
    try {
      const result = await postForm('account_delete_process.php', { id: user.id })
      if (!result.success) throw new Error(result.error || 'Could not delete this account.')
      setNotice(result.message || 'Account deleted.')
      load()
    } catch (reason) { setError(reason.message) }
  }
  return <>
    <PageHeader title="Account management" description="Manage application user accounts." icon="fa-users" />
    <Notice error={error} success={notice || location.state?.notice} />
    <div className="card shadow-sm"><div className="card-body p-0"><div className="table-responsive"><table className="table table-hover align-middle mb-0">
      <thead><tr><th>Username</th><th>Email</th><th>Role</th><th>Last login</th><th>Actions</th></tr></thead>
      <tbody>{users.map((user) => <tr key={user.id}>
        <td className="fw-semibold">{user.username}</td><td>{user.email}</td><td>{user.role_name || '-'}</td><td>{user.last_login ? formatDate(user.last_login) : 'Never'}</td>
        <td><div className="table-actions"><NavLink className="btn btn-sm btn-outline-secondary" title={`Edit ${user.username}`} to={`/accounts/${user.id}/edit`}><i className="fa-solid fa-pen" /></NavLink><button className="btn btn-sm btn-outline-danger" type="button" title={`Delete ${user.username}`} onClick={() => remove(user)}><i className="fa-solid fa-trash" /></button></div></td>
      </tr>)}{!users.length && <tr><td colSpan="5" className="text-center text-muted py-5">No accounts found.</td></tr>}</tbody>
    </table></div></div></div>
  </>
}

export function AccountEdit() {
  const { id } = useParams()
  const navigate = useNavigate()
  const [roles, setRoles] = useState([])
  const [values, setValues] = useState({ username: '', email: '', role_id: '', last_login: '' })
  const [error, setError] = useState('')
  const [busy, setBusy] = useState(false)
  useEffect(() => {
    Promise.all([getAccountData({ type: 'user_for_edit', id }), getAccountData({ type: 'roles' })])
      .then(([user, roleData]) => {
        setValues({ ...user, last_login: user.last_login ? user.last_login.replace(' ', 'T').slice(0, 16) : '' })
        setRoles(roleData.roles || [])
      })
      .catch((reason) => setError(reason.message))
  }, [id])
  function update(event) { setValues((current) => ({ ...current, [event.target.name]: event.target.value })) }
  async function submit(event) {
    event.preventDefault()
    setBusy(true)
    setError('')
    try {
      const result = await postForm('account_edit_process.php', { ...values, id, last_login: values.last_login.replace('T', ' ') })
      if (!result.success) throw new Error(result.error || 'Could not update this account.')
      navigate('/accounts', { state: { notice: result.message } })
    } catch (reason) { setError(reason.message) } finally { setBusy(false) }
  }
  return <>
    <PageHeader title="Edit account" description="Update account details and access role." icon="fa-user-pen" />
    <Notice error={error} />
    <div className="card form-card"><div className="card-body"><form onSubmit={submit}>
      <div className="row mb-3"><label className="col-sm-4 col-form-label" htmlFor="account-username">Username</label><div className="col-sm-8"><input id="account-username" className="form-control" name="username" value={values.username} onChange={update} required /></div></div>
      <div className="row mb-3"><label className="col-sm-4 col-form-label" htmlFor="account-email">Email</label><div className="col-sm-8"><input id="account-email" className="form-control" type="email" name="email" value={values.email} onChange={update} required /></div></div>
      <div className="row mb-3"><label className="col-sm-4 col-form-label" htmlFor="account-role">Role</label><div className="col-sm-8"><select id="account-role" className="form-select" name="role_id" value={values.role_id} onChange={update} required><option value="">Select a role</option>{roles.map((role) => <option key={role.id} value={role.id}>{role.name}</option>)}</select></div></div>
      <div className="row mb-3"><label className="col-sm-4 col-form-label" htmlFor="account-last-login">Last login</label><div className="col-sm-8"><input id="account-last-login" className="form-control" type="datetime-local" name="last_login" value={values.last_login} onChange={update} /></div></div>
      <div className="form-actions"><button className="btn btn-primary" type="submit" disabled={busy}>{busy ? 'Saving...' : 'Save changes'}</button><button className="btn btn-outline-secondary" type="button" onClick={() => navigate('/accounts')}>Cancel</button></div>
    </form></div></div>
  </>
}
