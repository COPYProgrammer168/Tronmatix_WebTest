import { useState, useEffect, useRef, useCallback } from 'react'
const API_URL = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000'

const SUGGESTIONS = [
  { label: '🖥 Help me build a PC',       text: 'I need help building a PC. What should I consider?' },
  { label: '🎮 GPU recommendations',      text: 'What GPU do you recommend for gaming in 2025?'      },
  { label: '🔧 Check compatibility',      text: 'How do I check if PC parts are compatible?'          },
  { label: '💰 Budget PC under $1000',    text: 'Can you suggest a good PC build under $1000?'       },
]

const BOT_INTRO = {
  id: 0,
  sender: 'bot',
  text: "👋 Hi! I'm your **PC Hardware Assistant**. I can help you pick parts, check compatibility, and suggest builds.\n\nWhat can I help you with today?",
  ts: new Date(),
}

function formatTime(date) {
  return new Date(date).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

function Bubble({ msg }) {
  const isUser = msg.sender === 'user'
  return (
    <div className={`flex ${isUser ? 'justify-end' : 'justify-start'} mb-3`}>
      {!isUser && (
        <div className="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-white font-black mr-2 flex-shrink-0 mt-1"
          style={{ fontSize: 13 }}>🤖</div>
      )}
      <div className={`max-w-[78%]`}>
        <div className={`px-4 py-2.5 rounded-2xl ${isUser
          ? 'bg-primary text-white rounded-tr-sm'
          : 'bg-gray-100 text-gray-800 rounded-tl-sm'}`}
          style={{ fontSize: 14, lineHeight: 1.5, whiteSpace: 'pre-wrap', wordBreak: 'break-word' }}>
          {msg.text}
        </div>
        <div className={`mt-1 text-gray-400 ${isUser ? 'text-right' : ''}`} style={{ fontSize: 11 }}>
          {formatTime(msg.ts)}
        </div>
      </div>
    </div>
  )
}

function TypingIndicator() {
  return (
    <div className="flex items-center gap-2 mb-3">
      <div className="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-white font-black mr-2 flex-shrink-0"
        style={{ fontSize: 13 }}>🤖</div>
      <div className="bg-gray-100 rounded-2xl rounded-tl-sm px-4 py-3 flex gap-1.5">
        {[0, 150, 300].map(delay => (
          <div key={delay} className="w-2 h-2 rounded-full bg-gray-400"
            style={{ animation: `bounce 1s ease-in-out ${delay}ms infinite` }} />
        ))}
      </div>
      <style>{`@keyframes bounce { 0%,60%,100%{transform:translateY(0)} 30%{transform:translateY(-6px)} }`}</style>
    </div>
  )
}

export default function SupportChat() {
  const [open,      setOpen]      = useState(false)
  const [messages,  setMessages]  = useState([BOT_INTRO])
  const [input,     setInput]     = useState('')
  const [typing,    setTyping]    = useState(false)
  const [sessionId, setSessionId] = useState(null)
  const bottomRef = useRef(null)
  const inputRef  = useRef(null)

  // Auto-scroll to latest message
  useEffect(() => {
    bottomRef.current?.scrollIntoView({ behavior: 'smooth' })
  }, [messages, typing])

  // Focus input when chat opens
  useEffect(() => {
    if (open) setTimeout(() => inputRef.current?.focus(), 100)
  }, [open])

  const sendMessage = useCallback(async (text) => {
    const trimmed = text?.trim() || input.trim()
    if (!trimmed) return

    const userMsg = { id: Date.now(), sender: 'user', text: trimmed, ts: new Date() }
    setMessages(prev => [...prev, userMsg])
    setInput('')
    setTyping(true)

    try {
      // POST to backend AI chat endpoint
      const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token')
      const res = await fetch(`${API_URL}/api/chat/message`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          ...(token ? { 'Authorization': `Bearer ${token}` } : {}),
        },
        credentials: 'include',
        body: JSON.stringify({
          message:    trimmed,
          session_id: sessionId,
          history:    messages.slice(-8).map(m => ({
            role:    m.sender === 'user' ? 'user' : 'assistant',
            content: m.text,
          })),
        }),
      })

      if (!res.ok) throw new Error(`HTTP ${res.status}`)
      const data = await res.json()
      const reply = data?.reply || data?.message || "I'll get back to you on that!"
      if (data?.session_id) setSessionId(data.session_id)

      setMessages(prev => [...prev, {
        id:     Date.now() + 1,
        sender: 'bot',
        text:   reply,
        ts:     new Date(),
      }])
    } catch {
      setMessages(prev => [...prev, {
        id:     Date.now() + 1,
        sender: 'bot',
        text:   "Sorry, I couldn't reach the server. Make sure the backend is running at " + API_URL + ". You can also reach us on Facebook or call 077 711 126!",
        ts:     new Date(),
      }])
    } finally {
      setTyping(false)
    }
  }, [input, messages, sessionId])

  function handleKeyDown(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault()
      sendMessage()
    }
  }

  const unread = !open && messages.length > 1  // has replies beyond intro

  return (
    <>
      {/* Chat window */}
      <div className={`fixed bottom-24 right-4 z-[200] w-[360px] bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden
        transition-all duration-300 ${open ? 'opacity-100 scale-100' : 'opacity-0 scale-95 pointer-events-none'}`}
        style={{ maxHeight: '70vh', display: 'flex', flexDirection: 'column' }}>

        {/* Header */}
        <div className="bg-gradient-to-r from-gray-900 to-gray-800 px-4 py-3 flex items-center justify-between">
          <div className="flex items-center gap-3">
            <div className="w-9 h-9 rounded-full bg-primary flex items-center justify-center text-white font-black"
              style={{ fontSize: 16 }}>🤖</div>
            <div>
              <div className="text-white font-black" style={{ fontFamily: 'Rajdhani, sans-serif', fontSize: 15 }}>
                PC Hardware Assistant
              </div>
              <div className="flex items-center gap-1.5">
                <div className="w-2 h-2 rounded-full bg-green-400 animate-pulse" />
                <span className="text-green-400" style={{ fontSize: 11 }}>Online · Usually replies instantly</span>
              </div>
            </div>
          </div>
          <button onClick={() => setOpen(false)}
            className="text-gray-400 hover:text-white font-bold text-xl leading-none">✕</button>
        </div>

        {/* Messages */}
        <div className="flex-1 overflow-y-auto px-4 py-4" style={{ minHeight: 0 }}>
          {messages.map(msg => <Bubble key={msg.id} msg={msg} />)}
          {typing && <TypingIndicator />}
          <div ref={bottomRef} />
        </div>

        {/* Quick suggestions (only before user has typed) */}
        {messages.length <= 1 && (
          <div className="px-4 pb-2 flex flex-wrap gap-2">
            {SUGGESTIONS.map(s => (
              <button key={s.label}
                onClick={() => sendMessage(s.text)}
                className="text-xs bg-orange-50 border border-orange-200 text-primary font-bold px-3 py-1.5 rounded-full hover:bg-orange-100 transition-colors">
                {s.label}
              </button>
            ))}
          </div>
        )}

        {/* Input */}
        <div className="px-4 py-3 border-t border-gray-100 flex gap-2">
          <textarea
            ref={inputRef}
            value={input}
            onChange={e => setInput(e.target.value)}
            onKeyDown={handleKeyDown}
            placeholder="Ask about PC parts, builds, or compatibility…"
            rows={1}
            className="flex-1 border border-gray-300 rounded-xl px-3 py-2 resize-none focus:outline-none focus:border-primary"
            style={{ fontSize: 14, maxHeight: 80 }}
          />
          <button
            onClick={() => sendMessage()}
            disabled={!input.trim() || typing}
            className="w-10 h-10 bg-primary text-white rounded-xl flex items-center justify-center hover:bg-orange-600 transition-colors disabled:opacity-40 flex-shrink-0 self-end"
          >
            <svg className="w-5 h-5" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
            </svg>
          </button>
        </div>
      </div>

      {/* Floating toggle button */}
      <button
        onClick={() => setOpen(o => !o)}
        className="fixed bottom-6 right-4 z-[200] w-14 h-14 bg-primary text-white rounded-full shadow-2xl flex items-center justify-center hover:bg-orange-600 transition-all hover:scale-110"
      >
        {open ? (
          <svg className="w-6 h-6" fill="none" stroke="currentColor" strokeWidth={2.5} viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        ) : (
          <>
            <svg className="w-6 h-6" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
            </svg>
            {unread && (
              <div className="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full flex items-center justify-center"
                style={{ fontSize: 10, fontWeight: 700, color: 'white' }}>
                {messages.length - 1}
              </div>
            )}
          </>
        )}
      </button>
    </>
  )
}
