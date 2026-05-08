import { useState, useEffect, useRef, useCallback } from 'react'
import axios from '../lib/axios';

const SUGGESTIONS = [
  { label: '🖥 Build me a PC',           text: 'Can you help me build a gaming PC? What budget should I set?' },
  { label: '🎮 Best GPU for gaming',     text: 'What GPU do you recommend for 1440p gaming in 2025?'          },
  { label: '🔧 Check compatibility',     text: 'How do I check if my PC parts are compatible with each other?' },
  { label: '💰 Budget build under $800', text: 'Can you suggest a complete PC build under $800?'              },
  { label: '🛠 PC troubleshooting',      text: 'My PC is running slow and overheating. What should I do?'     },
  { label: '📦 How to order',            text: 'How do I place an order and what payment methods do you accept?' },
]

const BOT_INTRO = {
  id: 0,
  sender: 'bot',
  text: "👋 Hi! I'm **TRX**, your AI assistant at TRONMATIX COMPUTER.\n\nI can help you with **PC builds**, **part recommendations**, **compatibility checks**, **tech support**, and pretty much anything else you need to know.\n\nWhat can I help you with today?",
  ts: new Date(),
}

function formatTime(date) {
  return new Date(date).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

// Simple markdown renderer: bold, bullet points, numbered lists
function renderMarkdown(text) {
  if (!text) return null

  const lines = text.split('\n')
  const elements = []
  let key = 0

  for (let i = 0; i < lines.length; i++) {
    const line = lines[i]

    if (line.trim() === '') {
      elements.push(<div key={key++} style={{ height: 6 }} />)
      continue
    }

    // Numbered list item
    const numMatch = line.match(/^(\d+)\.\s+(.+)/)
    if (numMatch) {
      elements.push(
        <div key={key++} style={{ display: 'flex', gap: 6, marginBottom: 3 }}>
          <span style={{ fontWeight: 700, minWidth: 16, color: 'inherit', opacity: 0.7 }}>{numMatch[1]}.</span>
          <span>{inlineMarkdown(numMatch[2])}</span>
        </div>
      )
      continue
    }

    // Bullet point
    const bulletMatch = line.match(/^[•\-\*]\s+(.+)/)
    if (bulletMatch) {
      elements.push(
        <div key={key++} style={{ display: 'flex', gap: 6, marginBottom: 3 }}>
          <span style={{ minWidth: 10, opacity: 0.6 }}>•</span>
          <span>{inlineMarkdown(bulletMatch[1])}</span>
        </div>
      )
      continue
    }

    // Normal line
    elements.push(<div key={key++} style={{ marginBottom: 2 }}>{inlineMarkdown(line)}</div>)
  }

  return elements
}

// Render inline **bold** and `code`
function inlineMarkdown(text) {
  const parts = text.split(/(\*\*[^*]+\*\*|`[^`]+`)/)
  return parts.map((part, i) => {
    if (part.startsWith('**') && part.endsWith('**')) {
      return <strong key={i}>{part.slice(2, -2)}</strong>
    }
    if (part.startsWith('`') && part.endsWith('`')) {
      return <code key={i} style={{
        background: 'rgba(0,0,0,0.1)', borderRadius: 3,
        padding: '0 4px', fontSize: '0.9em', fontFamily: 'monospace'
      }}>{part.slice(1, -1)}</code>
    }
    return part
  })
}

function Bubble({ msg, isNew }) {
  const isUser = msg.sender === 'user'
  return (
    <div
      className={`flex ${isUser ? 'justify-end' : 'justify-start'} mb-3`}
      style={{ animation: isNew ? 'slideUp 0.25s ease-out' : 'none' }}
    >
      {!isUser && (
        <div
          className="w-8 h-8 rounded-full flex items-center justify-center text-white font-black mr-2 flex-shrink-0 mt-1"
          style={{
            fontSize: 13,
            background: 'linear-gradient(135deg, #f97316, #ea580c)',
            boxShadow: '0 2px 8px rgba(249,115,22,0.4)',
          }}
        >
          ⚡
        </div>
      )}
      <div style={{ maxWidth: '80%' }}>
        <div
          style={{
            padding: '10px 14px',
            borderRadius: isUser ? '18px 18px 4px 18px' : '18px 18px 18px 4px',
            fontSize: 14,
            lineHeight: 1.55,
            wordBreak: 'break-word',
            background: isUser
              ? 'linear-gradient(135deg, #f97316, #ea580c)'
              : '#f3f4f6',
            color: isUser ? '#fff' : '#1f2937',
            boxShadow: isUser
              ? '0 2px 12px rgba(249,115,22,0.3)'
              : '0 1px 4px rgba(0,0,0,0.08)',
          }}
        >
          {renderMarkdown(msg.text)}
        </div>
        <div
          style={{
            marginTop: 4,
            fontSize: 11,
            color: '#9ca3af',
            textAlign: isUser ? 'right' : 'left',
          }}
        >
          {formatTime(msg.ts)}
        </div>
      </div>
    </div>
  )
}

function TypingIndicator() {
  return (
    <div className="flex items-center gap-2 mb-3">
      <div
        className="w-8 h-8 rounded-full flex items-center justify-center text-white font-black mr-2 flex-shrink-0"
        style={{
          fontSize: 13,
          background: 'linear-gradient(135deg, #f97316, #ea580c)',
          boxShadow: '0 2px 8px rgba(249,115,22,0.4)',
        }}
      >
        ⚡
      </div>
      <div
        style={{
          background: '#f3f4f6',
          borderRadius: '18px 18px 18px 4px',
          padding: '12px 16px',
          display: 'flex',
          gap: 5,
          alignItems: 'center',
        }}
      >
        {[0, 150, 300].map(delay => (
          <div
            key={delay}
            style={{
              width: 7, height: 7, borderRadius: '50%',
              background: '#9ca3af',
              animation: `typingBounce 1s ease-in-out ${delay}ms infinite`,
            }}
          />
        ))}
      </div>
      <style>{`
        @keyframes typingBounce { 0%,60%,100%{transform:translateY(0)} 30%{transform:translateY(-5px)} }
        @keyframes slideUp { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }
        @keyframes fadeIn { from{opacity:0} to{opacity:1} }
      `}</style>
    </div>
  )
}

export default function SupportChat() {
  const [open,      setOpen]      = useState(false)
  const [messages,  setMessages]  = useState(() => {
    try {
      const saved = sessionStorage.getItem('tronmatix_chat')
      if (saved) {
        const parsed = JSON.parse(saved)
        return Array.isArray(parsed) && parsed.length > 0 ? parsed : [BOT_INTRO]
      }
    } catch {}
    return [BOT_INTRO]
  })
  const [input,     setInput]     = useState('')
  const [typing,    setTyping]    = useState(false)
  const [sessionId, setSessionId] = useState(() => {
    try { return sessionStorage.getItem('tronmatix_chat_session') || null } catch { return null }
  })
  const [newMsgId,  setNewMsgId]  = useState(null)
  const bottomRef = useRef(null)
  const inputRef  = useRef(null)
  const textareaRef = useRef(null)

  useEffect(() => {
    try { sessionStorage.setItem('tronmatix_chat', JSON.stringify(messages)) } catch {}
  }, [messages])

  useEffect(() => {
    bottomRef.current?.scrollIntoView({ behavior: 'smooth' })
  }, [messages, typing])

  useEffect(() => {
    if (open) setTimeout(() => inputRef.current?.focus(), 150)
  }, [open])

  // Auto-resize textarea
  useEffect(() => {
    if (textareaRef.current) {
      textareaRef.current.style.height = 'auto'
      textareaRef.current.style.height = Math.min(textareaRef.current.scrollHeight, 100) + 'px'
    }
  }, [input])

  const sendMessage = useCallback(async (text) => {
    const trimmed = (text ?? input).trim()
    if (!trimmed || typing) return

    const userMsg = { id: Date.now(), sender: 'user', text: trimmed, ts: new Date() }
    const updatedMessages = [...messages, userMsg]

    setMessages(updatedMessages)
    setNewMsgId(userMsg.id)
    setInput('')
    setTyping(true)

    try {
      const res = await axios.post('/api/chat/message', {
        message:    trimmed,
        session_id: sessionId,
        history:    updatedMessages.slice(-12).map(m => ({
          role:    m.sender === 'user' ? 'user' : 'assistant',
          content: m.text,
        })),
      })

      const data  = res.data
      const reply = data?.reply || data?.message || "I'll get back to you on that!"

      if (data?.session_id) {
        setSessionId(data.session_id)
        try { sessionStorage.setItem('tronmatix_chat_session', data.session_id) } catch {}
      }

      const botMsg = { id: Date.now() + 1, sender: 'bot', text: reply, ts: new Date() }
      setMessages(prev => [...prev, botMsg])
      setNewMsgId(botMsg.id)

    } catch (error) {
      console.error('Chat error:', error)
      const status = error?.response?.status

      let errText = "⚠️ Something went wrong. Please try again in a moment."
      if (status === 429) {
        errText = error?.response?.data?.reply || "⏳ You're sending messages too fast. Please wait a moment."
      } else if (status === 403) {
        errText = "🔒 Session error. Please refresh the page and try again."
      }

      const errMsg = { id: Date.now() + 1, sender: 'bot', text: errText, ts: new Date() }
      setMessages(prev => [...prev, errMsg])
      setNewMsgId(errMsg.id)
    } finally {
      setTyping(false)
    }
  }, [input, messages, sessionId, typing])

  function handleKeyDown(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault()
      sendMessage()
    }
  }

  function clearChat() {
    setMessages([BOT_INTRO])
    setSessionId(null)
    try {
      sessionStorage.removeItem('tronmatix_chat')
      sessionStorage.removeItem('tronmatix_chat_session')
    } catch {}
  }

  const unreadCount = !open && messages.filter(m => m.sender === 'bot' && m.id !== 0).length
  const showSuggestions = messages.length <= 1 && !typing

  return (
    <>
      {/* ── Chat window ─────────────────────────────────────────────────────── */}
      <div
        style={{
          position: 'fixed',
          bottom: 90,
          right: 16,
          zIndex: 200,
          width: 370,
          maxHeight: '75vh',
          background: '#fff',
          borderRadius: 20,
          boxShadow: '0 20px 60px rgba(0,0,0,0.18), 0 4px 16px rgba(0,0,0,0.1)',
          border: '1px solid rgba(0,0,0,0.08)',
          overflow: 'hidden',
          display: 'flex',
          flexDirection: 'column',
          transition: 'all 0.3s cubic-bezier(0.34,1.56,0.64,1)',
          opacity:    open ? 1 : 0,
          transform:  open ? 'scale(1) translateY(0)' : 'scale(0.92) translateY(16px)',
          pointerEvents: open ? 'auto' : 'none',
        }}
      >
        {/* Header */}
        <div style={{
          background: 'linear-gradient(135deg, #111827 0%, #1f2937 100%)',
          padding: '14px 16px',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between',
          flexShrink: 0,
        }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
            <div style={{
              width: 40, height: 40, borderRadius: '50%',
              background: 'linear-gradient(135deg, #f97316, #ea580c)',
              display: 'flex', alignItems: 'center', justifyContent: 'center',
              fontSize: 18, boxShadow: '0 3px 12px rgba(249,115,22,0.5)',
            }}>⚡</div>
            <div>
              <div style={{ color: '#fff', fontWeight: 800, fontSize: 15, fontFamily: 'Rajdhani, sans-serif', letterSpacing: 0.3 }}>
                TRXBot — AI Assistant
              </div>
              <div style={{ display: 'flex', alignItems: 'center', gap: 5, marginTop: 1 }}>
                <div style={{
                  width: 7, height: 7, borderRadius: '50%', background: '#4ade80',
                  animation: 'pulse 2s infinite',
                }} />
                <span style={{ color: '#4ade80', fontSize: 11 }}>Online · Powered by Gemin AI</span>
              </div>
            </div>
          </div>
          <div style={{ display: 'flex', gap: 6 }}>
            <button
              onClick={clearChat}
              title="Clear chat"
              style={{
                background: 'rgba(255,255,255,0.08)', border: 'none',
                color: '#9ca3af', borderRadius: 8, width: 30, height: 30,
                cursor: 'pointer', fontSize: 14, display: 'flex',
                alignItems: 'center', justifyContent: 'center',
              }}
            >🗑</button>
            <button
              onClick={() => setOpen(false)}
              style={{
                background: 'rgba(255,255,255,0.08)', border: 'none',
                color: '#9ca3af', borderRadius: 8, width: 30, height: 30,
                cursor: 'pointer', fontSize: 16, fontWeight: 700,
                display: 'flex', alignItems: 'center', justifyContent: 'center',
              }}
            >✕</button>
          </div>
        </div>

        {/* Messages */}
        <div style={{
          flex: 1, overflowY: 'auto', padding: '16px 14px 8px',
          minHeight: 0,
          scrollbarWidth: 'thin',
          scrollbarColor: '#e5e7eb transparent',
        }}>
          {messages.map(msg => (
            <Bubble key={msg.id} msg={msg} isNew={msg.id === newMsgId} />
          ))}
          {typing && <TypingIndicator />}
          <div ref={bottomRef} />
        </div>

        {/* Quick suggestions */}
        {showSuggestions && (
          <div style={{
            padding: '0 14px 10px',
            display: 'flex', flexWrap: 'wrap', gap: 6,
            animation: 'fadeIn 0.3s ease',
          }}>
            {SUGGESTIONS.map(s => (
              <button
                key={s.label}
                onClick={() => sendMessage(s.text)}
                style={{
                  fontSize: 12, fontWeight: 600,
                  background: '#fff7ed',
                  border: '1px solid #fed7aa',
                  color: '#ea580c',
                  borderRadius: 20,
                  padding: '5px 11px',
                  cursor: 'pointer',
                  transition: 'all 0.15s',
                  whiteSpace: 'nowrap',
                }}
                onMouseEnter={e => { e.target.style.background = '#ffedd5'; e.target.style.borderColor = '#f97316' }}
                onMouseLeave={e => { e.target.style.background = '#fff7ed'; e.target.style.borderColor = '#fed7aa' }}
              >
                {s.label}
              </button>
            ))}
          </div>
        )}

        {/* Input area */}
        <div style={{
          padding: '10px 12px 12px',
          borderTop: '1px solid #f3f4f6',
          display: 'flex',
          gap: 8,
          alignItems: 'flex-end',
          background: '#fafafa',
        }}>
          <textarea
            ref={el => { inputRef.current = el; textareaRef.current = el }}
            value={input}
            onChange={e => setInput(e.target.value)}
            onKeyDown={handleKeyDown}
            placeholder="Ask me anything…"
            rows={1}
            disabled={typing}
            style={{
              flex: 1,
              border: '1.5px solid #e5e7eb',
              borderRadius: 14,
              padding: '9px 13px',
              fontSize: 14,
              resize: 'none',
              outline: 'none',
              fontFamily: 'inherit',
              lineHeight: 1.5,
              maxHeight: 100,
              background: '#fff',
              color: '#1f2937',
              transition: 'border-color 0.2s',
              overflow: 'hidden',
            }}
            onFocus={e => { e.target.style.borderColor = '#f97316' }}
            onBlur={e => { e.target.style.borderColor = '#e5e7eb' }}
          />
          <button
            onClick={() => sendMessage()}
            disabled={!input.trim() || typing}
            style={{
              width: 40, height: 40,
              background: input.trim() && !typing
                ? 'linear-gradient(135deg, #f97316, #ea580c)'
                : '#e5e7eb',
              border: 'none',
              borderRadius: 12,
              cursor: input.trim() && !typing ? 'pointer' : 'not-allowed',
              display: 'flex', alignItems: 'center', justifyContent: 'center',
              flexShrink: 0,
              transition: 'all 0.2s',
              boxShadow: input.trim() && !typing ? '0 3px 10px rgba(249,115,22,0.4)' : 'none',
            }}
          >
            <svg width="18" height="18" fill="none" stroke={input.trim() && !typing ? '#fff' : '#9ca3af'}
              strokeWidth={2.2} viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
            </svg>
          </button>
        </div>

        {/* Footer */}
        <div style={{
          textAlign: 'center', fontSize: 10, color: '#d1d5db',
          paddingBottom: 8, background: '#fafafa',
          letterSpacing: 0.3,
        }}>
          Powered by Google Gemini AI · TRONMATIX COMPUTER
        </div>
      </div>

      {/* ── Floating toggle button ───────────────────────────────────────────── */}
      <button
        onClick={() => setOpen(o => !o)}
        style={{
          position: 'fixed', bottom: 20, right: 16, zIndex: 200,
          width: 56, height: 56,
          background: open ? '#374151' : 'linear-gradient(135deg, #f97316, #ea580c)',
          border: 'none', borderRadius: '50%',
          cursor: 'pointer',
          display: 'flex', alignItems: 'center', justifyContent: 'center',
          boxShadow: '0 4px 20px rgba(249,115,22,0.5)',
          transition: 'all 0.3s cubic-bezier(0.34,1.56,0.64,1)',
          transform: open ? 'scale(0.95)' : 'scale(1)',
        }}
        onMouseEnter={e => { if (!open) e.currentTarget.style.transform = 'scale(1.12)' }}
        onMouseLeave={e => { e.currentTarget.style.transform = open ? 'scale(0.95)' : 'scale(1)' }}
      >
        {open ? (
          <svg width="22" height="22" fill="none" stroke="#fff" strokeWidth={2.5} viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        ) : (
          <>
            <svg width="24" height="24" fill="none" stroke="#fff" strokeWidth={2} viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round"
                d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
            </svg>
            {unreadCount > 0 && (
              <div style={{
                position: 'absolute', top: -2, right: -2,
                width: 18, height: 18,
                background: '#ef4444', borderRadius: '50%',
                fontSize: 10, fontWeight: 700, color: '#fff',
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                border: '2px solid #fff',
                animation: 'fadeIn 0.3s',
              }}>
                {unreadCount > 9 ? '9+' : unreadCount}
              </div>
            )}
          </>
        )}
      </button>

      <style>{`
        @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.5} }
        @keyframes fadeIn { from{opacity:0} to{opacity:1} }
        @keyframes slideUp { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }
      `}</style>
    </>
  )
}