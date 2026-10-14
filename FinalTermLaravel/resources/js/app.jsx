import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom'
import { ProtectedLayout } from './TungTungTung/components/ProtectedLayout'
import { AccountEdit, AccountsPage } from './TungTungTung/pages/AccountPages'
import { AuthPage, RegisterPage } from './TungTungTung/pages/AuthPages'
import { DashboardPage } from './TungTungTung/pages/DashboardPage'
import { InventoryPage } from './TungTungTung/pages/InventoryPage'
import { LogsPage } from './TungTungTung/pages/LogsPage'
import { DeleteProduct, ProductForm, StockForm } from './TungTungTung/pages/ProductPages'
import './TungTungTung/ReactApp.css'

function App() {
    return <BrowserRouter basename="/stock"><Routes>
        <Route path="/login" element={<AuthPage />} />
        <Route path="/register" element={<RegisterPage />} />
        <Route element={<ProtectedLayout />}>
            <Route path="/" element={<DashboardPage />} />
            <Route path="/inventory" element={<InventoryPage />} />
            <Route path="/logs" element={<LogsPage />} />
            <Route path="/products/new" element={<ProductForm />} />
            <Route path="/products/:id/edit" element={<ProductForm />} />
            <Route path="/products/:id/delete" element={<DeleteProduct />} />
            <Route path="/products/:id/import" element={<StockForm direction="import" />} />
            <Route path="/products/:id/export" element={<StockForm direction="export" />} />
            <Route path="/accounts" element={<AccountsPage />} />
            <Route path="/accounts/:id/edit" element={<AccountEdit />} />
            <Route path="*" element={<Navigate to="/" replace />} />
        </Route>
    </Routes></BrowserRouter>
}

createRoot(document.getElementById('root')).render(
    <StrictMode>
        <App />
    </StrictMode>,
)
