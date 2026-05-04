// src/components/profile/LocationsTab.jsx
import { useState, useEffect } from 'react'
import { useLang } from '../../context/LanguageContext'
import axiosClient from '../../lib/axios'
import { useTheme } from '../../context/ThemeContext'
import MapPickerModal from './MapPickerModal'

// ─── Cambodian provinces ───────────────────────────────────────────────────────
const KH_CITIES = [
  'Phnom Penh','Siem Reap','Battambang','Kampong Cham','Kampong Chhnang',
  'Kampong Speu','Kampong Thom','Kampot','Kandal','Kep','Koh Kong',
  'Kratié','Mondulkiri','Oddar Meanchey','Pailin','Preah Sihanouk',
  'Preah Vihear','Prey Veng','Pursat','Ratanakiri','Stung Treng',
  'Svay Rieng','Takéo','Tboung Khmum',
]

const getLabelStyle = (isKhmer) => ({
  display: 'block', fontFamily: isKhmer ? 'KantumruyPro, Khmer OS, sans-serif' : 'Rajdhani, sans-serif',
  fontSize: 11, fontWeight: 700, letterSpacing: isKhmer ? 0 : 2,
  color: '#6B7280', marginBottom: 6, textTransform: isKhmer ? 'none' : 'uppercase',
})

function btnStyle(bg, color, border, isKhmer = false) {
  return {
    background: bg, color, border: `1px solid ${border}`,
    borderRadius: 8, padding: '6px 14px',
    fontSize: 13, fontWeight: 600, cursor: 'pointer',
    fontFamily: isKhmer ? 'KantumruyPro, Khmer OS, sans-serif' : 'Rajdhani, sans-serif', letterSpacing: isKhmer ? 0 : 0.5,
    transition: 'opacity 0.15s',
  }
}

// ── Location card ──────────────────────────────────────────────────────────────
function LocationCard({ loc, onEdit, onDelete, onSetDefault }) {
  const { dark } = useTheme()
  const { t, isKhmer } = useLang()
  const labelStyle = getLabelStyle(isKhmer)
  const [confirmDel, setConfirmDel] = useState(false)

  const cardBg     = dark ? '#111827' : '#fff'
  const cardBorder = loc.is_default ? '#F97316' : (dark ? '#374151' : '#E5E7EB')
  const nameColor  = dark ? '#f9fafb' : '#111'
  const addrColor  = dark ? '#d1d5db' : '#4B5563'
  const metaColor  = dark ? '#9ca3af' : '#6B7280'
  const divider    = dark ? '#1f2937' : '#F3F4F6'

  return (
    <div className="loc-card" style={{
      border: `${loc.is_default ? '1.5px' : '1px'} solid ${cardBorder}`,
      borderRadius: 14, padding: '16px 20px', background: cardBg,
      position: 'relative', transition: 'box-shadow 0.2s',
      boxShadow: loc.is_default ? '0 0 0 3px rgba(249,115,22,0.1)' : (dark ? '0 1px 4px rgba(0,0,0,0.3)' : '0 1px 4px rgba(0,0,0,0.06)'),
    }}>
      {loc.is_default && (
        <span style={{
          position: 'absolute', top: -1, right: 16,
          background: '#F97316', color: '#fff',
          fontSize: 11, fontWeight: 700, letterSpacing: 1,
          padding: '2px 10px', borderRadius: '0 0 8px 8px',
          fontFamily: 'Rajdhani, sans-serif',
        }}>{isKhmer ? t('locations.default') : 'DEFAULT'}</span>
      )}
      <div style={{ display: 'flex', gap: 12, alignItems: 'flex-start' }}>
        <div style={{
          width: 38, height: 38, borderRadius: 10,
          background: loc.is_default ? 'rgba(249,115,22,0.1)' : (dark ? '#1f2937' : '#F9FAFB'),
          display: 'flex', alignItems: 'center', justifyContent: 'center',
          fontSize: 18, flexShrink: 0,
          border: loc.is_default ? '1px solid rgba(249,115,22,0.3)' : `1px solid ${dark ? '#374151' : '#E5E7EB'}`,
        }}>📍</div>
        <div style={{ flex: 1, minWidth: 0 }}>
          <div style={{ fontWeight: 700, fontSize: 16, color: nameColor, fontFamily: 'Rajdhani, sans-serif' }}>
            {loc.name}
          </div>
          <div style={{ fontSize: 14, color: metaColor, marginTop: 2 }}>📞 {loc.phone}</div>
          <div style={{ fontSize: 14, color: addrColor, marginTop: 4, lineHeight: 1.5 }}>
            {loc.address}, {loc.city}
          </div>
          {loc.lat && loc.lng && (
            <span style={{
              display: 'inline-flex', alignItems: 'center', gap: 4,
              fontSize: 11, fontWeight: 700, color: '#3b82f6',
              background: 'rgba(59,130,246,0.08)', border: '1px solid rgba(59,130,246,0.2)',
              borderRadius: 6, padding: '2px 8px', marginTop: 4,
            }}>📌 Map pin saved</span>
          )}
          {loc.note && (
            <div style={{ fontSize: 13, color: dark ? '#6b7280' : '#9CA3AF', marginTop: 4, fontStyle: 'italic' }}>
              Note: {loc.note}
            </div>
          )}
        </div>
      </div>
      <div style={{ display: 'flex', gap: 8, marginTop: 14, paddingTop: 12, borderTop: `1px solid ${divider}` }}>
        {!loc.is_default && (
          <button onClick={() => onSetDefault(loc.id)} style={btnStyle('#F9FAFB', '#374151', '#E5E7EB', isKhmer)}>
            Set Default
          </button>
        )}
        <button onClick={() => onEdit(loc)} style={btnStyle('#FFF7ED', '#C2410C', '#FED7AA', isKhmer)}>{isKhmer ? `✏️ ${t('locations.edit')}` : '✏️ Edit'}</button>
        {confirmDel ? (
          <>
            <button onClick={() => onDelete(loc.id)} style={btnStyle('#FEF2F2', '#DC2626', '#FCA5A5', isKhmer)}>
              {isKhmer ? t('locations.confirmDelete') : 'Confirm Delete'}
            </button>
            <button onClick={() => setConfirmDel(false)} style={btnStyle('#F9FAFB', '#6B7280', '#E5E7EB', isKhmer)}>
              {isKhmer ? t('common.cancel') : 'Cancel'}
            </button>
          </>
        ) : (
          <button onClick={() => setConfirmDel(true)} style={btnStyle('#F9FAFB', '#6B7280', '#E5E7EB', isKhmer)}>
            {isKhmer ? `🗑️ ${t('locations.delete')}` : '🗑️ Delete'}
          </button>
        )}
      </div>
    </div>
  )
}

// ── Location modal ─────────────────────────────────────────────────────────────
function LocationModal({ loc, onClose, onSave }) {
  const { dark } = useTheme()
  const { t, isKhmer } = useLang()
  const modalFont = isKhmer ? 'Kh_Jrung_Thom, Khmer OS, sans-serif' : 'Rajdhani, sans-serif'
  const bodyFont  = isKhmer ? 'KantumruyPro, Khmer OS, sans-serif'  : 'Rajdhani, sans-serif'
  const labelStyle = getLabelStyle(isKhmer)
  const isEdit = !!loc?.id
  const [form, setForm] = useState({
    name: loc?.name || '', phone: loc?.phone || '',
    address: loc?.address || '', city: loc?.city || 'Phnom Penh',
    note: loc?.note || '', is_default: loc?.is_default ?? false,
  })
  const [mapPin, setMapPin] = useState(loc?.lat ? { lat: parseFloat(loc.lat), lng: parseFloat(loc.lng), address: loc.map_address || '' } : null)
  const [showMapPicker, setShowMapPicker] = useState(false)
  const [saving, setSaving] = useState(false)
  const [saveErr, setSaveErr] = useState(null)
  const set = (k, v) => setForm(f => ({ ...f, [k]: v }))

  const validate = () => {
    const e = {}
    if (!form.name.trim()) e.name = isKhmer ? 'ឈ្មោះត្រូវបានទាមទារ' : 'Name is required'
    if (!form.phone.trim()) e.phone = isKhmer ? 'លេខទូរស័ព្ទត្រូវបានទាមទារ' : 'Phone is required'
    if (!form.address.trim()) e.address = isKhmer ? 'អាសយដ្ឋានត្រូវបានទាមទារ' : 'Address is required'
    return Object.keys(e).length === 0
  }

  const submit = async () => {
    if (!validate()) return
    setSaving(true)
    setSaveErr(null)
    try {
      await onSave({
        ...form,
        lat: mapPin?.lat ?? null,
        lng: mapPin?.lng ?? null,
        map_address: mapPin?.address ?? null,
      }, loc?.id)
    } catch (err) {
      setSaveErr(err?.response?.data?.message || 'Failed to save. Please try again.')
    } finally {
      setSaving(false)
    }
  }

  const modalBg = dark ? '#1f2937' : '#fff'
  const titleColor = dark ? '#f9fafb' : '#111'
  const labelColor = dark ? '#9ca3af' : '#6B7280'

  const iStyle = (err) => ({
    width: '100%', border: `1px solid ${err ? '#FCA5A5' : (dark ? '#374151' : '#D1D5DB')}`,
    borderRadius: 10, padding: '10px 14px', fontSize: 15,
    fontFamily: bodyFont, outline: 'none',
    background: dark ? '#111827' : '#fff',
    color: dark ? '#f9fafb' : '#111',
    transition: 'border-color 0.2s', boxSizing: 'border-box',
  })

  return (
    <>
      {showMapPicker && (
        <MapPickerModal
          initialLat={mapPin?.lat}
          initialLng={mapPin?.lng}
          onClose={() => setShowMapPicker(false)}
          onConfirm={(pin) => {
            setMapPin(pin)
            setShowMapPicker(false)
          }}
        />
      )}
      <div style={{
        position: 'fixed', inset: 0, zIndex: 1000,
        background: 'rgba(0,0,0,0.45)', backdropFilter: 'blur(4px)',
        display: 'flex', alignItems: 'center', justifyContent: 'center', padding: 16,
      }} onClick={e => e.target === e.currentTarget && onClose()}>
        <div style={{
          background: modalBg, borderRadius: 20, padding: 28,
          width: '100%', maxWidth: 520,
          boxShadow: '0 24px 64px rgba(0,0,0,0.2)',
          animation: 'modalIn 0.25s ease',
        }}>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 24 }}>
            <h2 style={{ fontFamily: modalFont, fontSize: 22, fontWeight: 800, letterSpacing: isKhmer ? 0 : 1, color: titleColor }}>
              {isEdit ? (isKhmer ? `✏️ ${t('locations.edit')}` : '✏️ Edit Location') : (isKhmer ? `📍 ${t('locations.addNew')}` : '📍 Add New Location')}
            </h2>
            <button onClick={onClose} style={{ background: 'none', border: 'none', fontSize: 22, cursor: 'pointer', color: '#9CA3AF' }}>✕</button>
          </div>

          <div style={{ display: 'grid', gap: 16 }}>
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
              <div>
                <label style={{ ...labelStyle, color: labelColor }}>
                  {isKhmer ? t('locations.recipientName') : 'RECIPIENT NAME'} *
                </label>
                <input value={form.name} onChange={e => set('name', e.target.value)}
                  placeholder={isKhmer ? 'ឧ. ឈ្មោះអ្នកទទួល' : 'e.g. YOURNAME'} style={iStyle(false)} />
              </div>
              <div>
                <label style={{ ...labelStyle, color: labelColor }}>
                  {isKhmer ? t('locations.phoneNumber') : 'PHONE NUMBER'} *
                </label>
                <input value={form.phone} onChange={e => set('phone', e.target.value)}
                  placeholder="0xx xxx xxx" style={iStyle(false)} />
              </div>
            </div>

            <div>
              <label style={{ ...labelStyle, color: labelColor }}>
                {isKhmer ? t('locations.streetAddress') : 'STREET ADDRESS'} *
              </label>
              <input value={form.address} onChange={e => set('address', e.target.value)}
                placeholder={isKhmer ? 'លេខផ្ទះ, ផ្លូវ, ភូមិ, ឃុំ' : 'House No., Street, Village, Commune'} style={iStyle(false)} />
            </div>

            <div>
              <label style={{ ...labelStyle, color: labelColor }}>
                {isKhmer ? t('locations.cityProvince') : 'CITY / PROVINCE'}
              </label>
              <select value={form.city} onChange={e => set('city', e.target.value)}
                style={{ ...iStyle(false), cursor: 'pointer' }}>
                {KH_CITIES.map(c => <option key={c} value={c}>{c}</option>)}
              </select>
            </div>

            <div>
              <label style={{ ...labelStyle, color: labelColor }}>
                {isKhmer ? t('locations.note') : 'NOTE (OPTIONAL)'}
              </label>
              <input value={form.note} onChange={e => set('note', e.target.value)}
                placeholder={isKhmer ? 'ចំណុចសំគាល់, ពណ៌ទ្វារ ។ល។' : 'Landmark, gate color, etc.'} style={iStyle(false)} />
            </div>

            {/* Map Pin with Search/Paste */}
            <div>
              <label style={{ ...labelStyle, color: labelColor }}>
                {isKhmer ? t('locations.mapPin') : 'MAP PIN (OPTIONAL)'}
              </label>
              <button
                type="button"
                onClick={() => setShowMapPicker(true)}
                style={{
                  width: '100%', padding: '10px 14px', borderRadius: 10, cursor: 'pointer',
                  border: mapPin ? '1.5px solid #3b82f6' : '1px dashed #D1D5DB',
                  background: mapPin ? 'rgba(59,130,246,0.05)' : (dark ? '#1f2937' : '#F9FAFB'),
                  display: 'flex', alignItems: 'center', gap: 10,
                  fontFamily: bodyFont, fontSize: 14, fontWeight: 600,
                  color: mapPin ? '#3b82f6' : (dark ? '#9ca3af' : '#6B7280'), textAlign: 'left',
                }}>
                <span style={{ fontSize: 18 }}>{mapPin ? '📌' : '🗺️'}</span>
                <div style={{ flex: 1, overflow: 'hidden' }}>
                  {mapPin
                    ? (
                      <>
                        <div style={{ fontWeight: 700 }}>{isKhmer ? t('locationSearch.pinSaved') : 'Pin saved ✓'}</div>
                        <div style={{ fontSize: 12, color: '#9CA3AF', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>
                          {mapPin.address || `${mapPin.lat.toFixed(5)}, ${mapPin.lng.toFixed(5)}`}
                        </div>
                      </>
                    )
                    : (isKhmer ? '🔎 ចុចដើម្បីកំណត់ទីតាំង (ឬស្វែងរក/បិទភ្ជាប់តំណ)' : '🔎 Pin location (or search/paste link)')
                  }
                </div>
                {mapPin && (
                  <span
                    onClick={e => { e.stopPropagation(); setMapPin(null) }}
                    style={{ fontSize: 16, color: '#9CA3AF', cursor: 'pointer', padding: '0 4px' }}
                  >✕</span>
                )}
              </button>
            </div>

            {saveErr && (
              <div style={{ color: '#DC2626', fontSize: 13, fontWeight: 600, padding: '8px 12px', background: '#FEF2F2', borderRadius: 8, border: '1px solid #FCA5A5' }}>
                ⚠ {saveErr}
              </div>
            )}

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
              <span style={{ fontFamily: bodyFont, fontSize: isKhmer ? 14 : 15, fontWeight: 600, color: dark ? '#d1d5db' : '#374151' }}>
                {isKhmer ? t('locations.setDefault') : 'Set as default delivery address'}
              </span>
            </label>
          </div>

          <div style={{ display: 'flex', gap: 10, marginTop: 24 }}>
            <button onClick={onClose} style={{
              flex: 1, padding: '12px', borderRadius: 10,
              border: `1px solid ${dark ? '#374151' : '#E5E7EB'}`,
              background: dark ? '#111827' : '#F9FAFB',
              fontFamily: bodyFont, fontSize: 15, fontWeight: 700,
              cursor: 'pointer', color: dark ? '#9ca3af' : '#6B7280',
            }}>{isKhmer ? t('common.cancel') : 'CANCEL'}</button>
            <button onClick={submit} disabled={saving} style={{
              flex: 2, padding: '12px', borderRadius: 10, border: 'none',
              background: saving ? '#FED7AA' : '#F97316', color: '#fff',
              fontFamily: bodyFont, fontSize: 15, fontWeight: 700,
              cursor: saving ? 'not-allowed' : 'pointer', letterSpacing: isKhmer ? 0 : 1,
              transition: 'background 0.2s',
            }}>
              {saving
                ? (isKhmer ? t('profile.saving') : 'SAVING...')
                : isEdit
                  ? (isKhmer ? t('locations.save') : 'SAVE CHANGES')
                  : (isKhmer ? t('locations.addNew') : 'ADD LOCATION')}
            </button>
          </div>
        </div>
      </div>
    </>
  )
}

// ── Main LocationsTab export ───────────────────────────────────────────────────
export default function LocationsTab({ notify }) {
  const { t, isKhmer } = useLang()
  const tabFont  = isKhmer ? 'Kh_Jrung_Thom, Khmer OS, sans-serif'  : 'Rajdhani, sans-serif'
  const bodyFont = isKhmer ? 'KantumruyPro, Khmer OS, sans-serif'    : 'Rajdhani, sans-serif'
  const [locations,  setLocations]  = useState([])
  const [locLoading, setLocLoading] = useState(false)
  const [locModal,   setLocModal]   = useState(null)

  // Fix: use useEffect instead of calling fetch during render body (caused infinite loops)
  useEffect(() => { fetchLocations() }, []) // eslint-disable-line

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

  // Fix: rethrow errors so LocationModal's try/catch receives them and doesn't get stuck
  const saveLocation = async (form, id) => {
    if (id) {
      await axiosClient.put(`/api/user/locations/${id}`, form)
      notify('Location updated!')
    } else {
      await axiosClient.post('/api/user/locations', form)
      notify('Location added!')
    }
    setLocModal(null)
    fetchLocations()
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
            <h2 style={{ fontSize: 20, fontWeight: 800, letterSpacing: isKhmer ? 0 : 1, margin: 0, fontFamily: tabFont }}>{isKhmer ? t('locations.title') : 'Delivery Locations'}</h2>
            <div style={{ fontSize: 14, color: '#9CA3AF', marginTop: 4, fontFamily: bodyFont ?? 'Rajdhani, sans-serif' }}>
              {isKhmer
                ? `${locations.length} ${t('locations.savedAddresses')}`
                : `${locations.length} saved address${locations.length !== 1 ? 'es' : ''}`}
            </div>
          </div>
          <button onClick={() => setLocModal({})} style={{
            background: '#F97316', color: '#fff', border: 'none',
            borderRadius: 10, padding: '9px 20px', cursor: 'pointer',
            fontFamily: tabFont, fontSize: 14, fontWeight: 700,
            letterSpacing: isKhmer ? 0 : 1, boxShadow: '0 4px 12px rgba(249,115,22,0.3)',
          }}>{isKhmer ? `+ ${t('locations.addNew')}` : '+ ADD LOCATION'}</button>
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
            <div style={{ fontSize: 18, fontWeight: 700, color: '#374151', marginBottom: 8, fontFamily: tabFont }}>{isKhmer ? t('locations.noLocations') : 'No saved locations'}</div>
            <div style={{ fontSize: 14, marginBottom: 20 }}>{isKhmer ? t('locations.addFirst') : 'Add a delivery address to speed up checkout'}</div>
            <button onClick={() => setLocModal({})} style={{
              background: '#F97316', color: '#fff', border: 'none',
              borderRadius: 10, padding: '10px 28px', cursor: 'pointer',
              fontFamily: tabFont, fontSize: 15, fontWeight: 700,
            }}>{isKhmer ? `+ ${t('locations.addFirst')}` : '+ ADD FIRST LOCATION'}</button>
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
            <span>
              {isKhmer
                ? t('locations.defaultTip')
                : <>The <strong>Default</strong> location will be pre-filled at checkout automatically.</>}
            </span>
          </div>
        )}
      </div>
    </>
  )
}
