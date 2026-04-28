// src/components/orders/OrderHeader.jsx
import { Link } from "react-router-dom";
import { useTheme } from "../../context/ThemeContext";
import { useLang } from "../../context/LanguageContext";

export default function OrderHeader({ username }) {
  const { dark } = useTheme();
  const { t, isKhmer}     = useLang();
  const khfont    = isKhmer ? 'Kh_Jrung_Thom, Khmer OS, sans-serif' : ' Rajdhani, sans-serif'
  const bodyFont  = isKhmer ? 'KantumruyPro, Khmer OS, sans-serif' : 'Rajdhani, sans-serif'
  return (
    <div className="flex items-center justify-between mb-6">
      <div>
        <h1
          className="font-black"
          style={{ fontFamily: khfont, fontSize: 30, color: dark ? "#f9fafb" : "#111827" }}
        >
          {isKhmer ? 'ការបញ្ជាទិញរបស់អ្នក' : 'MY ORDERS'}
        </h1>
        <p style={{ fontFamily: bodyFont, fontSize: 18, color: dark ? "#9ca3af" : "#374151" }}>
          {isKhmer ? 'នេះជាបញ្ជីការទិញរបស់អ្នក' : 'This is your order list'}, {" "}
          <span className="text-primary font-bold">{username}</span>
        </p>
      </div>
      <Link
        to="/"
        className="text-primary font-bold hover:underline flex items-center gap-1"
        style={{ fontFamily: bodyFont, fontSize: 16 }}
      >
        ← {isKhmer ? 'ទៅទំព័រដើម' : 'Back Home'}
      </Link>
    </div>
  );
}
