import { Component } from "react";

/**
 * Catches render errors in the routed pages and shows a readable message
 * instead of a blank white screen. Clicking "Reload" restarts the route.
 */
export default class ErrorBoundary extends Component {
  constructor(props) {
    super(props);
    this.state = { hasError: false, message: "" };
  }

  static getDerivedStateFromError(error) {
    return { hasError: true, message: error?.message || String(error) };
  }

  componentDidCatch(error, info) {
    console.error("[ErrorBoundary]", error, info);
  }

  render() {
    if (this.state.hasError) {
      return (
        <div
          style={{
            minHeight: "60vh",
            display: "flex",
            flexDirection: "column",
            alignItems: "center",
            justifyContent: "center",
            gap: 12,
            padding: 24,
            textAlign: "center",
            fontFamily: "Rajdhani, sans-serif",
          }}
        >
          <div style={{ fontSize: 48 }}>⚠️</div>
          <div style={{ fontSize: 18, fontWeight: 700, color: "#ef4444" }}>
            Something went wrong
          </div>
          <div style={{ fontSize: 13, color: "#6b7280", maxWidth: 420, wordBreak: "break-word" }}>
            {this.state.message}
          </div>
          <button
            onClick={() => {
              this.setState({ hasError: false, message: "" });
              window.location.reload();
            }}
            style={{
              padding: "10px 24px",
              borderRadius: 8,
              background: "#F97316",
              color: "#fff",
              border: "none",
              fontSize: 15,
              fontWeight: 700,
              cursor: "pointer",
              fontFamily: "Rajdhani, sans-serif",
            }}
          >
            Reload
          </button>
        </div>
      );
    }

    return this.props.children;
  }
}
