import { useEffect, useState } from 'react'
import axios from 'axios'

export default function TestConnection() {
  const [status, setStatus] = useState('Testing...')

  useEffect(() => {
    axios.get('/api/products')
      .then(() => setStatus('✅ Connected to backend!'))
      .catch((err) => setStatus(`❌ Failed: ${err.message}`))
  }, [])

  return (
    <div style={{ padding: 20, fontSize: 18, fontWeight: 'bold' }}>
      Backend Status: {status}
    </div>
  )
}