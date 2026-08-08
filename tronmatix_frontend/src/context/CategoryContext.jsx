// src/context/CategoryContext.jsx
import { createContext, useContext, useState, useEffect, useCallback, useMemo } from "react";

const CategoryContext = createContext(null);

// Must point at the API base the same way axios.js does. On Render the
// frontend and backend live on different origins, so a relative URL would
// hit the frontend and fail. Resolve against VITE_API_URL like lib/axios.js.
const BASE_URL = (import.meta.env.VITE_API_URL || '').replace(/\/$/, '')
const API_URL = `${BASE_URL}/api/categories/tree`;

export function CategoryProvider({ children }) {
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    let cancelled = false;

    async function load() {
      setLoading(true);
      setError(null);
      try {
        const res = await fetch(API_URL, { headers: { Accept: "application/json" } });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const json = await res.json();
        const data = json?.success && Array.isArray(json.data) ? json.data : [];
        if (!cancelled) {
          setCategories(data);
          setLoading(false);
        }
      } catch (e) {
        if (!cancelled) {
          setError(e.message || "Failed to load categories");
          setLoading(false);
        }
      }
    }

    load();
    return () => { cancelled = true };
  }, []);

  const refresh = useCallback(() => {
    setLoading(true);
    setError(null);
    fetch(API_URL, { headers: { Accept: "application/json" } })
      .then(r => r.ok ? r.json() : Promise.reject(r))
      .then(json => {
        const data = json?.success && Array.isArray(json.data) ? json.data : [];
        setCategories(data);
        setLoading(false);
      })
      .catch(e => { setError(e.message || "Failed to load categories"); setLoading(false); });
  }, []);

  const value = useMemo(() => ({
    categories,
    loading,
    error,
    refresh,
  }), [categories, loading, error, refresh]);

  return (
    <CategoryContext.Provider value={value}>
      {children}
    </CategoryContext.Provider>
  );
}

export function useCategories() {
  const ctx = useContext(CategoryContext);
  if (!ctx) throw new Error("useCategories must be used within a CategoryProvider");
  return ctx;
}
