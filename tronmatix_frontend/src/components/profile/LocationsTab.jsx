// src/components/profile/LocationsTab.jsx
import { useState } from 'react'
import axiosClient from '../../lib/axios'

// ─── Cambodian provinces ───────────────────────────────────────────────────────
const KH_CITIES = [
  'Phnom Penh','Siem Reap','Battambang','Kampong Cham','Kampong Chhnang',
  'Kampong Speu','Kampong Thom','Kampot','Kandal','Kep','Koh Kong',
  'Kratié','Mondulkiri','Oddar Meanchey','Pailin','Preah Sihanouk',
  'Preah Vihear','Prey Veng','Pursat','Ratanakiri','Stung Treng',
  'Svay Rieng','Takéo','Tboung Khmum',
]

const labelStyle = {
  display: 'block', fontFamily: 'Rajdhani, sans-serif',
  fontSize: 11, fontWeight: 700, letterSpacing: 2,
  color: '#6B7280', marginBottom: 6, textTransform: 'uppercase',
}

function btnStyle(bg, color, border) {
  return {
    background: bg, color, border: `1px solid ${border}`,
    borderRadius: 8, padding: '6px 14px',
    fontSize: 13, fontWeight: 600, cursor: 'pointer',
    fontFamily: 'Rajdhani, sans-serif', letterSpacing: 0.5,
    transition: 'opacity 0.15s',
  }
}

// ── Location card ──────────────────────────────────────────────────────────────
function LocationCard({ loc, onEdit, onDelete, onSetDefault }) {
  const [confirmDel, setConfirmDel] = useState(false)
  return (
    <div className="loc-card" style={{
      border: loc.is_default ? '1.5px solid #F97316' : '1px solid #E5E7EB',
      borderRadius: 14, padding: '16px 20px', background: '#fff',
      position: 'relative', transition: 'box-shadow 0.2s',
      boxShadow: loc.is_default ? '0 0 0 3px rgba(249,115,22,0.1)' : '0 1px 4px rgba(0,0,0,0.06)',
    }}>
      {loc.is_default && (
        <span style={{
          position: 'absolute', top: -1, right: 16,
          background: '#F97316', color: '#fff',
          fontSize: 11, fontWeight: 700, letterSpacing: 1,
          padding: '2px 10px', borderRadius: '0 0 8px 8px',
          fontFamily: 'Rajdhani, sans-serif',
        }}>DEFAULT</span>
      )}
      <div style={{ display: 'flex', gap: 12, alignItems: 'flex-start' }}>
        <div style={{
          width: 38, height: 38, borderRadius: 10,
          background: loc.is_default ? 'rgba(249,115,22,0.1)' : '#F9FAFB',
          display: 'flex', alignItems: 'center', justifyContent: 'center',
          fontSize: 18, flexShrink: 0,
          border: loc.is_default ? '1px solid rgba(249,115,22,0.3)' : '1px solid #E5E7EB',
        }}>📍</div>
        <div style={{ flex: 1, minWidth: 0 }}>
          <div style={{ fontWeight: 700, fontSize: 16, color: '#111', fontFamily: 'Rajdhani, sans-serif' }}>
            {loc.name}
          </div>
          <div style={{ fontSize: 14, color: '#6B7280', marginTop: 2 }}>📞 {loc.phone}</div>
          <div style={{ fontSize: 14, color: '#4B5563', marginTop: 4, lineHeight: 1.5 }}>
            {loc.address}, {loc.city}
          </div>
          {loc.note && (
            <div style={{ fontSize: 13, color: '#9CA3AF', marginTop: 4, fontStyle: 'italic' }}>
              Note: {loc.note}
            </div>
          )}
        </div>
      </div>
      <div style={{ display: 'flex', gap: 8, marginTop: 14, paddingTop: 12, borderTop: '1px solid #F3F4F6' }}>
        {!loc.is_default && (
          <button onClick={() => onSetDefault(loc.id)} style={btnStyle('#F9FAFB', '#374151', '#E5E7EB')}>
            Set Default
          </button>
        )}
        <button onClick={() => onEdit(loc)} style={btnStyle('#FFF7ED', '#C2410C', '#FED7AA')}>✏️ Edit</button>
        {confirmDel ? (
          <>
            <button onClick={() => onDelete(loc.id)} style={btnStyle('#FEF2F2', '#DC2626', '#FCA5A5')}>
              Confirm Delete
            </button>
            <button onClick={() => setConfirmDel(false)} style={btnStyle('#F9FAFB', '#6B7280', '#E5E7EB')}>
              Cancel
            </button>
          </>
        ) : (
          <button onClick={() => setConfirmDel(true)} style={btnStyle('#F9FAFB', '#6B7280', '#E5E7EB')}>
            🗑️ Delete
          </button>
        )}
      </div>
    </div>
  )
}

// ── Location modal ─────────────────────────────────────────────────────────────
function LocationModal({ loc, onClose, onSave }) {
  const isEdit = !!loc?.id
  const [form, setForm] = useState({
    name: loc?.name || '', phone: loc?.phone || '',
    address: loc?.address || '', city: loc?.city || 'Phnom Penh',
    note: loc?.note || '', is_default: loc?.is_default ?? false,
  })
  const [saving, setSaving] = useState(false)
  const [errors, setErrors] = useState({})
  const set = (k, v) => setForm(f => ({ ...f, [k]: v }))

  const validate = () => {
    const e = {}
    if (!form.name.trim())    e.name    = 'Name is required'
    if (!form.phone.trim())   e.phone   = 'Phone is required'
    if (!form.address.trim()) e.address = 'Address is required'
    setErrors(e)
    return Object.keys(e).length === 0
  }

  const submit = async () => {
    if (!validate()) return
    setSaving(true)
    await onSave(form, loc?.id)
    setSaving(false)
  }

  const iStyle = (err) => ({
    width: '100%', border: `1px solid ${err ? '#FCA5A5' : '#D1D5DB'}`,
    borderRadius: 10, padding: '10px 14px', fontSize: 15,
    fontFamily: 'Rajdhani, sans-serif', outline: 'none',
    background: '#fff', color: '#111', transition: 'border-color 0.2s',
    boxSizing: 'border-box',
  })

  return (
    <div style={{
      position: 'fixed', inset: 0, zIndex: 1000,
      background: 'rgba(0,0,0,0.45)', backdropFilter: 'blur(4px)',
      display: 'flex', alignItems: 'center', justifyContent: 'center', padding: 16,
    }} onClick={e => e.target === e.currentTarget && onClose()}>
      <div style={{
        background: '#fff', borderRadius: 20, padding: 28,
        width: '100%', maxWidth: 520,
        boxShadow: '0 24px 64px rgba(0,0,0,0.2)',
        animation: 'modalIn 0.25s ease',
      }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 24 }}>
          <h2 style={{ fontFamily: 'Rajdhani, sans-serif', fontSize: 22, fontWeight: 800, letterSpacing: 1 }}>
            {isEdit ? '✏️ Edit Location' : '📍 Add New Location'}
          </h2>
          <button onClick={onClose} style={{ background: 'none', border: 'none', fontSize: 22, cursor: 'pointer', color: '#9CA3AF' }}>✕</button>
        </div>

        <div style={{ display: 'grid', gap: 16 }}>
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
            <div>
              <label style={labelStyle}>RECIPIENT NAME *</label>
              <input value={form.name} onChange={e => set('name', e.target.value)}
                placeholder="e.g. Home, Office" style={iStyle(errors.name)} />
              {errors.name && <div style={{ color: '#DC2626', fontSize: 12, marginTop: 4 }}>{errors.name}</div>}
            </div>
            <div>
              <label style={labelStyle}>PHONE NUMBER *</label>
              <input value={form.phone} onChange={e => set('phone', e.target.value)}
                placeholder="0xx xxx xxx" style={iStyle(errors.phone)} />
              {errors.phone && <div style={{ color: '#DC2626', fontSize: 12, marginTop: 4 }}>{errors.phone}</div>}
            </div>
          </div>

          <div>
            <label style={labelStyle}>STREET ADDRESS *</label>
            <input value={form.address} onChange={e => set('address', e.target.value)}
              placeholder="House No., Street, Village, Commune" style={iStyle(errors.address)} />
            {errors.address && <div style={{ color: '#DC2626', fontSize: 12, marginTop: 4 }}>{errors.address}</div>}
          </div>

          <div>
            <label style={labelStyle}>CITY / PROVINCE</label>
            <select value={form.city} onChange={e => set('city', e.target.value)}
              style={{ ...iStyle(false), cursor: 'pointer' }}>
              {KH_CITIES.map(c => <option key={c} value={c}>{c}</option>)}
            </select>
          </div>

          <div>
            <label style={labelStyle}>NOTE (OPTIONAL)</label>
            <input value={form.note} onChange={e => set('note', e.target.value)}
              placeholder="Landmark, gate color, etc." style={iStyle(false)} />
          </div>

          <label style={{ display: 'flex', alignItems: 'center', gap: 10, cursor: 'pointer', userSelect: 'none' }}>
            <div style={{ position: 'relative', width: 44, height: 24 }}>
              <input type="checkbox" checked={form.is_default}
                onChange={e => set('is_default', e.target.checked)}
                style={{ opacity: 0, width: 0, height: 0, position: 'absolute' }} />
              <div onClick={() => set('is_default', !form.is_default)} style={{
                position: 'absolute', inset: 0,
                background: form.is_default ? '#F97316' : '#D1D5DB',
                borderRadius: 24, cursor: 'pointer', transition: 'background 0.3s',
              }}>
                <div style={{
                  position: 'absolute', top: 3, left: form.is_default ? 23 : 3,
                  width: 18, height: 18, borderRadius: '50%', background: '#fff',
                  transition: 'left 0.3s', boxShadow: '0 1px 4px rgba(0,0,0,0.2)',
                }} />
              </div>
            </div>
            <span style={{ fontFamily: 'Rajdhani, sans-serif', fontSize: 15, fontWeight: 600, color: '#374151' }}>
              Set as default delivery address
            </span>
          </label>
        </div>

        <div style={{ display: 'flex', gap: 10, marginTop: 24 }}>
          <button onClick={onClose} style={{
            flex: 1, padding: '12px', borderRadius: 10, border: '1px solid #E5E7EB',
            background: '#F9FAFB', fontFamily: 'Rajdhani, sans-serif',
            fontSize: 15, fontWeight: 700, cursor: 'pointer', color: '#6B7280',
          }}>CANCEL</button>
          <button onClick={submit} disabled={saving} style={{
            flex: 2, padding: '12px', borderRadius: 10, border: 'none',
            background: saving ? '#FED7AA' : '#F97316', color: '#fff',
            fontFamily: 'Rajdhani, sans-serif', fontSize: 15, fontWeight: 700,
            cursor: saving ? 'not-allowed' : 'pointer', letterSpacing: 1,
            transition: 'background 0.2s',
          }}>
            {saving ? 'SAVING...' : isEdit ? 'SAVE CHANGES' : 'ADD LOCATION'}
          </button>
        </div>
      </div>
    </div>
  )
}

// ── Main LocationsTab export ───────────────────────────────────────────────────
export default function LocationsTab({ notify }) {
  const [locations,  setLocations]  = useState([])
  const [locLoading, setLocLoading] = useState(false)
  const [locModal,   setLocModal]   = useState(null)
  const [loaded,     setLoaded]     = useState(false)

  // Fetch on first render
  if (!loaded) {
    setLoaded(true)
    fetchLocations()
  }

  async function fetchLocations() {
    setLocLoading(true)
    try {
      const res = await axiosClient.get('/api/user/locations')
      setLocations(Array.isArray(res.data?.data) ? res.data.data : res.data || [])
    } catch {
      notify('Failed to load locations', 'error')
    } finally {
      setLocLoading(false)
    }
  }

  const saveLocation = async (form, id) => {
    try {
      if (id) {
        await axiosClient.put(`/api/user/locations/${id}`, form)
        notify('Location updated!')
      } else {
        await axiosClient.post('/api/user/locations', form)
        notify('Location added!')
      }
      setLocModal(null)
      fetchLocations()
    } catch (err) {
      notify(err.response?.data?.message || 'Failed to save location', 'error')
    }
  }

  const deleteLocation = async (id) => {
    try {
      await axiosClient.delete(`/api/user/locations/${id}`)
      notify('Location deleted')
      fetchLocations()
    } catch {
      notify('Failed to delete', 'error')
    }
  }

  const setDefaultLocation = async (id) => {
    try {
      await axiosClient.put(`/api/user/locations/${id}`, { is_default: true })
      fetchLocations()
    } catch {
      notify('Failed to update default', 'error')
    }
  }

  return (
    <>
      {locModal !== null && (
        <LocationModal
          loc={locModal}
          onClose={() => setLocModal(null)}
          onSave={saveLocation}
        />
      )}

      <div style={{ padding: 32, animation: 'fadeUp 0.3s ease' }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 24 }}>
          <div>
            <h2 style={{ fontSize: 20, fontWeight: 800, letterSpacing: 1, margin: 0 }}>Delivery Locations</h2>
            <div style={{ fontSize: 14, color: '#9CA3AF', marginTop: 4 }}>
              {locations.length} saved address{locations.length !== 1 ? 'es' : ''}
            </div>
          </div>
          <button onClick={() => setLocModal({})} style={{
            background: '#F97316', color: '#fff', border: 'none',
            borderRadius: 10, padding: '9px 20px', cursor: 'pointer',
            fontFamily: 'Rajdhani, sans-serif', fontSize: 14, fontWeight: 700,
            letterSpacing: 1, boxShadow: '0 4px 12px rgba(249,115,22,0.3)',
          }}>+ ADD LOCATION</button>
        </div>

        {locLoading ? (
          <div style={{ textAlign: 'center', padding: '48px 0', color: '#9CA3AF' }}>
            <div style={{ fontSize: 36, marginBottom: 12 }}>📍</div>
            <div style={{ fontSize: 16 }}>Loading locations...</div>
          </div>
        ) : locations.length === 0 ? (
          <div style={{
            textAlign: 'center', padding: '56px 24px',
            border: '2px dashed #E5E7EB', borderRadius: 16, color: '#9CA3AF',
          }}>
            <div style={{ fontSize: 48, marginBottom: 12 }}>📦</div>
            <div style={{ fontSize: 18, fontWeight: 700, color: '#374151', marginBottom: 8 }}>No saved locations</div>
            <div style={{ fontSize: 14, marginBottom: 20 }}>Add a delivery address to speed up checkout</div>
            <button onClick={() => setLocModal({})} style={{
              background: '#F97316', color: '#fff', border: 'none',
              borderRadius: 10, padding: '10px 28px', cursor: 'pointer',
              fontFamily: 'Rajdhani, sans-serif', fontSize: 15, fontWeight: 700,
            }}>+ ADD FIRST LOCATION</button>
          </div>
        ) : (
          <div style={{ display: 'grid', gap: 14 }}>
            {[...locations]
              .sort((a, b) => (b.is_default ? 1 : 0) - (a.is_default ? 1 : 0))
              .map(loc => (
                <LocationCard
                  key={loc.id} loc={loc}
                  onEdit={setLocModal}
                  onDelete={deleteLocation}
                  onSetDefault={setDefaultLocation}
                />
              ))}
          </div>
        )}

        {locations.length > 0 && (
          <div style={{
            marginTop: 20, padding: '12px 16px', borderRadius: 10,
            background: '#FFF7ED', border: '1px solid #FED7AA',
            fontSize: 13, color: '#92400E', display: 'flex', gap: 8, alignItems: 'center',
          }}>
            <span>💡</span>
            <span>The <strong>Default</strong> location will be pre-filled at checkout automatically.</span>
          </div>
        )}
      </div>
    </>
  )
}
