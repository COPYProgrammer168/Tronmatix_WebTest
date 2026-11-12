import { useEffect } from "react";
import logo from "../assets/logo.png";

export default function AdminRedirect() {
  useEffect(() => {
    const apiBase = (import.meta.env.VITE_API_URL || "").replace(/\/$/, "");
    const adminBase = apiBase || window.location.origin;
    window.location.href = `${adminBase}/dashboard`;
  }, []);

  // Brief spinner so the redirect doesn't feel like a blank flash
  return (
    <div style={{ display: "flex", flexDirection: "column", alignItems: "center", justifyContent: "center", minHeight: "80vh" }}>
      <div style={{ position: "relative", width: 88, height: 88 }}>
        <div style={{
          position: "absolute", inset: 0,
          border: "4px solid rgba(249,115,22,0.2)",
          borderTopColor: "#F97316",
          borderRadius: "50%",
          animation: "adminSpin 0.8s linear infinite",
        }} />
        <img
          src={logo}
          alt="Tronmatix"
          style={{
            position: "absolute", inset: 0,
            margin: "auto",
            width: 48, height: 48,
            objectFit: "contain",
          }}
        />
      </div>
      <style>{"@keyframes adminSpin{to{transform:rotate(360deg)}}"}</style>
    </div>
  );
}
