import { useState, useEffect } from 'react';
import { createPortal } from 'react-dom';
import { useLang } from '../context/LanguageContext';

export default function Notification({ message, onClose, duration = 3000 }) {
  const [visible, setVisible] = useState(false);
  const { isKhmer } = useLang();

  useEffect(() => {
    setVisible(true);
    const timer = setTimeout(() => {
      setVisible(false);
      setTimeout(onClose, 300); // Wait for exit animation
    }, duration);
    return () => clearTimeout(timer);
  }, [duration, onClose]);

  return createPortal(
    <div
      className={`fixed top-20 right-5 z-[1000] px-6 py-3 rounded-lg shadow-2xl flex items-center gap-3 transition-all duration-300 transform ${
        visible ? 'translate-x-0 opacity-100' : 'translate-x-full opacity-0'
      }`}
      style={{
        background: 'rgba(249, 115, 22, 0.95)',
        backdropFilter: 'blur(8px)',
        color: '#fff',
        fontWeight: 700,
        border: '1px solid rgba(255,255,255,0.2)',
      }}
    >      <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
      </svg>
      {message}
    </div>,
    document.body
  );
}
