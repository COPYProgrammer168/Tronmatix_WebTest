// src/components/orders/FulfillmentBadge.jsx
// Drop-in badge component — import and use in OrderCard.jsx
//
// Usage inside OrderCard:
//   import FulfillmentBadge from './FulfillmentBadge'
//   <FulfillmentBadge type={order.fulfillment_type} />

export default function FulfillmentBadge({ type }) {
  const isPickup = type === 'pickup'
  return (
    <span style={{
      display: 'inline-flex', alignItems: 'center', gap: 4,
      padding: '3px 10px', borderRadius: 999,
      fontSize: 11, fontWeight: 700, letterSpacing: 0.5,
      background: isPickup ? 'rgba(34,197,94,0.12)' : 'rgba(167,139,250,0.12)',
      border: `1px solid ${isPickup ? 'rgba(34,197,94,0.3)' : 'rgba(167,139,250,0.3)'}`,
      color: isPickup ? '#22c55e' : '#a78bfa',
    }}>
      {isPickup ? '🏪 PICKUP' : '🚚 DELIVERY'}
    </span>
  )
}