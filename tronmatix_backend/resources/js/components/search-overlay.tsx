import { useState, useEffect, useRef, useCallback } from 'react';

export interface BrandHit {
    type: 'brand';
    id: number;
    name: string;
    slug: string;
    image: string | null;
    url: string;
}

export interface ProductHit {
    type: 'product';
    id: number;
    name: string;
    price: number | string;
    image: string | null;
    url: string;
    brand: string;
    category: string;
}

export interface SearchResponse {
    success: boolean;
    query: string;
    brands: BrandHit[];
    products: ProductHit[];
    related: ProductHit[];
    hasMore: boolean;
}

const API_BASE = '/api/products/suggestions';

function escapeHtml(str: string): string {
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

export function useSearchOverlay() {
    const [open, setOpen] = useState(false);
    const [query, setQuery] = useState('');
    const [data, setData] = useState<SearchResponse | null>(null);
    const [loading, setLoading] = useState(false);
    const debounceRef = useRef<ReturnType<typeof setTimeout> | null>(null);

    const openOverlay = useCallback(() => {
        setOpen(true);
        setQuery('');
        setData(null);
        document.body.style.overflow = 'hidden';
    }, []);

    const closeOverlay = useCallback(() => {
        setOpen(false);
        setQuery('');
        setData(null);
        document.body.style.overflow = '';
    }, []);

    const onQueryChange = useCallback((val: string) => {
        setQuery(val);
        setLoading(true);
        clearTimeout(debounceRef.current!);

        if (val.trim().length < 2) {
            setData(null);
            setLoading(false);
            // fetch popular/recommended on empty
            if (val.trim().length === 0) {
                debounceRef.current = setTimeout(() => {
                    fetch(API_BASE)
                        .then((r) => r.json())
                        .then((d: SearchResponse) => {
                            setData(d);
                            setLoading(false);
                        })
                        .catch(() => setLoading(false));
                }, 100);
            }
            return;
        }

        debounceRef.current = setTimeout(() => {
            fetch(`${API_BASE}?q=${encodeURIComponent(val.trim())}`)
                .then((r) => r.json())
                .then((d: SearchResponse) => {
                    setData(d);
                    setLoading(false);
                })
                .catch(() => setLoading(false));
        }, 220);
    }, []);

    useEffect(() => {
        const handleKey = (e: KeyboardEvent) => {
            if (e.key === 'Escape' && open) closeOverlay();
        };
        document.addEventListener('keydown', handleKey);
        return () => document.removeEventListener('keydown', handleKey);
    }, [open, closeOverlay]);

    useEffect(() => {
        return () => { if (debounceRef.current) clearTimeout(debounceRef.current); };
    }, []);

    return { open, closeOverlay, openOverlay, query, onQueryChange, data, loading };
}

export function SearchOverlay({
    open,
    closeOverlay,
    query,
    onQueryChange,
    data,
    loading,
}: {
    open: boolean;
    closeOverlay: () => void;
    query: string;
    onQueryChange: (val: string) => void;
    data: SearchResponse | null;
    loading: boolean;
}) {
    if (!open) return null;

    const brands = data?.brands ?? [];
    const products = data?.products ?? [];
    const related = data?.related ?? [];
    const showBrands = brands.length > 0;
    const showProducts = products.length > 0;
    const showRelated = related.length > 0;
    const showEmpty = !loading && query.trim().length >= 0 && query.trim().length < 2 && !data;
    const showNoResults = !loading && query.trim().length >= 2 && !showProducts && !showBrands;

    return (
        <div
            className="fixed inset-0 z-[200] flex justify-end"
            onClick={(e) => e.target === e.currentTarget && closeOverlay()}
        >
            {/* Backdrop */}
            <div className="absolute inset-0 bg-black/55 backdrop-blur-sm animate-in fade-in duration-200" />

            {/* Panel — slides from right */}
            <div
                className="relative h-full w-full max-w-[520px] bg-background border-l border-border shadow-2xl flex flex-col overflow-hidden animate-in slide-in-from-right duration-300"
                style={{ animationTimingFunction: 'cubic-bezier(0.22, 1, 0.36, 1)' }}
            >
                {/* Header */}
                <div className="flex items-center justify-between px-5 py-4 border-b border-border shrink-0">
                    <span className="font-bold text-lg tracking-[3px] text-foreground">SEARCH</span>
                    <button
                        onClick={closeOverlay}
                        className="text-3xl leading-none text-muted-foreground hover:text-orange-500 transition-colors"
                        aria-label="Close search"
                    >
                        &times;
                    </button>
                </div>

                {/* Input */}
                <div className="flex items-center gap-3 px-5 py-3.5 border-b border-border shrink-0">
                    <span className="text-lg opacity-50">🔍</span>
                    <input
                        type="text"
                        value={query}
                        onChange={(e) => onQueryChange(e.target.value)}
                        placeholder="Search products, brands…"
                        autoFocus
                        className="flex-1 bg-transparent border-none outline-none text-foreground text-lg font-semibold placeholder:text-muted-foreground placeholder:font-normal min-w-0"
                    />
                    {query && (
                        <button
                            onClick={() => onQueryChange('')}
                            className="text-xl text-muted-foreground hover:text-destructive transition-colors shrink-0"
                            aria-label="Clear search"
                        >
                            &times;
                        </button>
                    )}
                </div>

                {/* Body */}
                <div className="flex-1 overflow-y-auto px-5 py-4 space-y-6">
                    {/* Brands horizontal scroll */}
                    {showBrands && (
                        <section>
                            <div className="text-xs font-bold tracking-[2px] text-muted-foreground mb-2.5">
                                BRANDS
                            </div>
                            <div className="flex gap-3 overflow-x-auto pb-2 snap-x snap-mandatory scroll-smooth">
                                {brands.map((b) => (
                                    <a
                                        key={b.id}
                                        href={b.url}
                                        onClick={closeOverlay}
                                        className="flex flex-col items-center gap-1.5 min-w-[76px] max-w-[88px] snap-start hover:-translate-y-0.5 transition-transform"
                                    >
                                        <div className="w-[60px] h-[60px] rounded-xl border border-border bg-muted/50 flex items-center justify-center p-1.5 overflow-hidden">
                                            {b.image ? (
                                                <img
                                                    src={b.image}
                                                    alt={escapeHtml(b.name)}
                                                    className="w-full h-full object-contain"
                                                    onError={(e) =>
                                                        ((e.target as HTMLImageElement).style.display = 'none')
                                                    }
                                                />
                                            ) : (
                                                <span className="text-2xl">🏷️</span>
                                            )}
                                        </div>
                                        <span className="text-xs font-semibold text-foreground text-center truncate w-full">
                                            {escapeHtml(b.name)}
                                        </span>
                                    </a>
                                ))}
                            </div>
                        </section>
                    )}

                    {/* Products grid */}
                    {showProducts && (
                        <section>
                            <div className="text-xs font-bold tracking-[2px] text-muted-foreground mb-2.5">
                                PRODUCTS
                            </div>
                            <div className="grid grid-cols-2 gap-2.5">
                                {products.map((p) => (
                                    <a
                                        key={p.id}
                                        href={p.url}
                                        onClick={closeOverlay}
                                        className="flex gap-2.5 p-2.5 rounded-xl border border-border bg-muted/30 hover:border-orange-500/50 hover:bg-orange-500/5 transition-colors"
                                    >
                                        <img
                                            src={p.image || ''}
                                            alt={escapeHtml(p.name)}
                                            className="w-[52px] h-[52px] rounded-lg object-cover shrink-0 bg-muted"
                                            onError={(e) =>
                                                ((e.target as HTMLImageElement).style.display = 'none')
                                            }
                                        />
                                        <div className="flex flex-col justify-center min-w-0 gap-0.5">
                                            <div
                                                className="text-sm font-bold text-foreground truncate"
                                                title={escapeHtml(p.name)}
                                            >
                                                {escapeHtml(p.name)}
                                            </div>
                                            <div className="text-xs text-muted-foreground truncate">
                                                {escapeHtml(p.brand || p.category || '')}
                                            </div>
                                            <div className="text-sm font-extrabold text-orange-500">
                                                ${Number(p.price).toFixed(2)}
                                            </div>
                                        </div>
                                    </a>
                                ))}
                            </div>
                        </section>
                    )}

                    {/* Related products horizontal scroll */}
                    {showRelated && (
                        <section>
                            <div className="text-xs font-bold tracking-[2px] text-muted-foreground mb-2.5">
                                YOU MAY ALSO LIKE
                            </div>
                            <div className="flex gap-3 overflow-x-auto pb-2 snap-x snap-mandatory scroll-smooth">
                                {related.map((p) => (
                                    <a
                                        key={p.id}
                                        href={p.url}
                                        onClick={closeOverlay}
                                        className="flex flex-col gap-1.5 min-w-[120px] max-w-[140px] snap-start hover:-translate-y-0.5 transition-transform"
                                    >
                                        <img
                                            src={p.image || ''}
                                            alt={escapeHtml(p.name)}
                                            className="w-[120px] h-[120px] rounded-xl border border-border bg-muted object-cover"
                                            onError={(e) =>
                                                ((e.target as HTMLImageElement).style.display = 'none')
                                            }
                                        />
                                        <div
                                            className="text-xs font-semibold text-foreground truncate"
                                            title={escapeHtml(p.name)}
                                        >
                                            {escapeHtml(p.name)}
                                        </div>
                                        <div className="text-sm font-extrabold text-orange-500">
                                            ${Number(p.price).toFixed(2)}
                                        </div>
                                    </a>
                                ))}
                            </div>
                        </section>
                    )}

                    {/* Empty state */}
                    {showEmpty && (
                        <div className="flex flex-col items-center justify-center py-16 text-muted-foreground">
                            <div className="text-5xl mb-3 opacity-60">🔍</div>
                            <div className="text-lg">Type at least 2 characters to search.</div>
                        </div>
                    )}

                    {/* No results */}
                    {showNoResults && (
                        <div className="flex flex-col items-center justify-center py-16 text-muted-foreground">
                            <div className="text-5xl mb-3 opacity-60">😕</div>
                            <div className="text-lg">No products found. Try another keyword.</div>
                        </div>
                    )}

                    {/* Loading spinner */}
                    {loading && (
                        <div className="flex justify-center py-8">
                            <div className="w-8 h-8 border-2 border-orange-500/30 border-t-orange-500 rounded-full animate-spin" />
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
