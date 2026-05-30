// tokens.jsx — design tokens, mock data, shared atoms for WP26 Predictor

const T = {
  bg: '#0B1020',
  bg2: '#131A2E',
  bg3: '#0F1528',
  card: 'rgba(255,255,255,0.06)',
  cardSolid: '#171F38',
  cardHi: 'rgba(255,255,255,0.10)',
  border: 'rgba(255,255,255,0.08)',
  borderHi: 'rgba(255,255,255,0.16)',
  cyan: '#00E5FF',
  violet: '#7C4DFF',
  green: '#00C853',
  red: '#FF5252',
  amber: '#FFC107',
  text: '#FFFFFF',
  textMid: 'rgba(255,255,255,0.72)',
  textDim: 'rgba(255,255,255,0.50)',
  textFaint: 'rgba(255,255,255,0.32)',
};

// Country "crests" — original 2-tone gradient discs with monograms.
// Not real flags — these are simple visual stand-ins so we don't recreate any
// national/branded marks.
const TEAMS = {
  ARG: { code: 'ARG', name: 'Argentina',    c1: '#75AADB', c2: '#F5C842' },
  BRA: { code: 'BRA', name: 'Brazil',       c1: '#FFD93D', c2: '#1B7F3A' },
  FRA: { code: 'FRA', name: 'France',       c1: '#0E2D6E', c2: '#E63946' },
  ENG: { code: 'ENG', name: 'England',      c1: '#F5F5F5', c2: '#C8102E' },
  ESP: { code: 'ESP', name: 'Spain',        c1: '#E63946', c2: '#FFC93D' },
  GER: { code: 'GER', name: 'Germany',      c1: '#1A1A1A', c2: '#FFD93D' },
  POR: { code: 'POR', name: 'Portugal',     c1: '#1B7F3A', c2: '#E63946' },
  NED: { code: 'NED', name: 'Netherlands',  c1: '#FF7A1A', c2: '#FFFFFF' },
  ITA: { code: 'ITA', name: 'Italy',        c1: '#1B7F3A', c2: '#7C4DFF' },
  BEL: { code: 'BEL', name: 'Belgium',      c1: '#1A1A1A', c2: '#FFD93D' },
  CRO: { code: 'CRO', name: 'Croatia',      c1: '#1F4FD9', c2: '#E63946' },
  URU: { code: 'URU', name: 'Uruguay',      c1: '#1F4FD9', c2: '#F5F5F5' },
  USA: { code: 'USA', name: 'USA',          c1: '#1F4FD9', c2: '#E63946' },
  MEX: { code: 'MEX', name: 'Mexico',       c1: '#1B7F3A', c2: '#E63946' },
  CAN: { code: 'CAN', name: 'Canada',       c1: '#E63946', c2: '#F5F5F5' },
  MAR: { code: 'MAR', name: 'Morocco',      c1: '#1B7F3A', c2: '#C8102E' },
  JPN: { code: 'JPN', name: 'Japan',        c1: '#F5F5F5', c2: '#E63946' },
  KOR: { code: 'KOR', name: 'South Korea',  c1: '#1F4FD9', c2: '#E63946' },
  SEN: { code: 'SEN', name: 'Senegal',      c1: '#1B7F3A', c2: '#FFD93D' },
  AUS: { code: 'AUS', name: 'Australia',    c1: '#0E2D6E', c2: '#FFD93D' },
};

// Crest — circular 2-tone "team badge" with 3-letter monogram.
function Crest({ team, size = 32, glow = false, ring = true }) {
  const t = typeof team === 'string' ? TEAMS[team] : team;
  if (!t) return null;
  const f = size * 0.34;
  return (
    <div style={{
      width: size, height: size, borderRadius: '50%',
      position: 'relative', flexShrink: 0,
      background: `conic-gradient(from 220deg, ${t.c1} 0deg 180deg, ${t.c2} 180deg 360deg)`,
      boxShadow: ring ? `inset 0 0 0 ${Math.max(1, size*0.04)}px rgba(255,255,255,0.18), 0 2px 8px rgba(0,0,0,0.4)${glow ? `, 0 0 24px ${t.c1}55` : ''}` : 'none',
      display: 'flex', alignItems: 'center', justifyContent: 'center',
    }}>
      <span style={{
        fontFamily: T.display, fontWeight: 800, fontSize: f,
        color: '#fff', letterSpacing: '-0.02em',
        textShadow: '0 1px 2px rgba(0,0,0,0.55)',
      }}>{t.code}</span>
    </div>
  );
}

// Glass card — the workhorse container
function Glass({ children, style = {}, hi = false, glow, p = 16, br = 18, onClick }) {
  return (
    <div onClick={onClick} style={{
      background: hi ? T.cardHi : T.card,
      backdropFilter: 'blur(20px) saturate(140%)',
      WebkitBackdropFilter: 'blur(20px) saturate(140%)',
      border: `1px solid ${hi ? T.borderHi : T.border}`,
      borderRadius: br, padding: p,
      boxShadow: glow ? `0 8px 28px rgba(0,0,0,0.4), 0 0 40px ${glow}33, inset 0 1px 0 rgba(255,255,255,0.08)` : '0 8px 28px rgba(0,0,0,0.35), inset 0 1px 0 rgba(255,255,255,0.05)',
      ...style,
    }}>{children}</div>
  );
}

// Tiny pill / chip
function Chip({ children, color = T.cyan, solid = false, style = {} }) {
  return (
    <span style={{
      display: 'inline-flex', alignItems: 'center', gap: 4,
      fontFamily: T.body, fontWeight: 600, fontSize: 10,
      letterSpacing: '0.08em', textTransform: 'uppercase',
      padding: '4px 8px', borderRadius: 999,
      color: solid ? '#06121C' : color,
      background: solid ? color : `${color}1F`,
      border: solid ? 'none' : `1px solid ${color}55`,
      ...style,
    }}>{children}</span>
  );
}

// Section heading inside a screen
function H({ children, kicker, action, style = {} }) {
  return (
    <div style={{ display: 'flex', alignItems: 'flex-end', justifyContent: 'space-between', marginBottom: 12, ...style }}>
      <div>
        {kicker && <div style={{ fontFamily: T.body, fontSize: 10, fontWeight: 600, color: T.cyan, letterSpacing: '0.16em', textTransform: 'uppercase', marginBottom: 4 }}>{kicker}</div>}
        <div style={{ fontFamily: T.display, fontSize: 18, fontWeight: 700, color: T.text, letterSpacing: '-0.02em' }}>{children}</div>
      </div>
      {action && <div style={{ fontFamily: T.body, fontSize: 12, fontWeight: 500, color: T.textMid }}>{action}</div>}
    </div>
  );
}

// Background — radial neon glows beneath everything
function NeonBg({ children, hue = 'mix' }) {
  return (
    <div style={{
      width: '100%', height: '100%', position: 'relative',
      background: T.bg, overflow: 'hidden',
      fontFamily: T.body, color: T.text,
    }}>
      <div style={{
        position: 'absolute', inset: 0, pointerEvents: 'none',
        backgroundImage: hue === 'cyan'
          ? `radial-gradient(circle at 20% -10%, ${T.cyan}33, transparent 50%), radial-gradient(circle at 90% 110%, ${T.violet}22, transparent 55%)`
          : hue === 'violet'
          ? `radial-gradient(circle at 80% -10%, ${T.violet}3F, transparent 55%), radial-gradient(circle at 10% 110%, ${T.cyan}22, transparent 55%)`
          : `radial-gradient(circle at 10% -10%, ${T.cyan}2E, transparent 50%), radial-gradient(circle at 95% 5%, ${T.violet}33, transparent 50%), radial-gradient(circle at 50% 110%, ${T.violet}22, transparent 55%)`,
      }} />
      <div style={{ position: 'relative', height: '100%' }}>{children}</div>
    </div>
  );
}

// Icon set — tiny inline SVGs, stroke-style
const I = {
  home: 'M3 11l9-8 9 8v9a2 2 0 0 1-2 2h-4v-7H10v7H6a2 2 0 0 1-2-2z m0 0',
  pitch: 'M4 6h16v12H4z M12 6v12 M12 12a3 3 0 1 1 0-.001Z M4 9v6 M20 9v6',
  trophy: 'M8 4h8v3a4 4 0 0 1-8 0z M5 5h3 M16 5h3 M10 13h4v3h-4z M9 19h6',
  crown: 'M3 8l4 5 5-8 5 8 4-5v10H3z',
  users: 'M9 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6z m6 0a3 3 0 1 0 0-6 3 3 0 0 0 0 6z M2 20c0-3 3-5 7-5s7 2 7 5 M14 15c4 0 7 2 7 5',
  user: 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8z m-8 9c0-4 4-7 8-7s8 3 8 7',
  bell: 'M6 8a6 6 0 0 1 12 0v5l2 3H4l2-3z m4 8a2 2 0 0 0 4 0',
  bolt: 'M13 2L4 14h7l-1 8 9-12h-7z',
  fire: 'M12 22c5 0 8-3 8-7 0-3-2-5-3-7-1 2-3 3-3 1 0-4-4-4-4-9-3 3-6 6-6 12 0 6 3 10 8 10z',
  target: 'M12 4a8 8 0 1 0 0 16 8 8 0 0 0 0-16z M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8z M12 12.01',
  star: 'M12 3l2.6 5.7 6.4.6-4.8 4.3 1.4 6.4L12 16.7 6.4 20l1.4-6.4L3 9.3l6.4-.6z',
  arrow: 'M5 12h14 m-6-6l6 6-6 6',
  check: 'M5 12l5 5 9-11',
  x: 'M6 6l12 12 M18 6L6 18',
  lock: 'M6 11V8a6 6 0 0 1 12 0v3 M5 11h14v10H5z',
  plus: 'M12 5v14 M5 12h14',
  chev: 'M9 6l6 6-6 6',
  search: 'M11 3a8 8 0 1 0 0 16 8 8 0 0 0 0-16z m6 14l4 4',
  share: 'M5 12a3 3 0 1 0 0-6 3 3 0 0 0 0 6z m14 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6z m0-12a3 3 0 1 0 0-6 3 3 0 0 0 0 6z M7.5 10.5l9-5 M7.5 13.5l9 5',
  cal: 'M4 6h16v15H4z M4 10h16 M9 3v4 M15 3v4',
  set: 'M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8z M12 2v3 M12 19v3 M2 12h3 M19 12h3 M5 5l2 2 M17 17l2 2 M5 19l2-2 M17 7l2-2',
  trend: 'M3 17l6-6 4 4 8-9 m0 0v6 m0-6h-6',
  joker: 'M12 3l9 5v8l-9 5-9-5V8z',
};
const Ic = ({ d, size = 18, color = 'currentColor', sw = 1.7 }) => (
  <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke={color} strokeWidth={sw} strokeLinecap="round" strokeLinejoin="round">
    <path d={d} />
  </svg>
);

// Bottom tab bar
function TabBar({ active = 'home', onChange }) {
  const tabs = [
    { id: 'home', label: 'Home', d: I.home },
    { id: 'matches', label: 'Matches', d: I.pitch },
    { id: 'predict', label: 'Predict', d: I.target, primary: true },
    { id: 'rank', label: 'Ranks', d: I.trophy },
    { id: 'me', label: 'Profile', d: I.user },
  ];
  return (
    <div style={{
      position: 'absolute', bottom: 0, left: 0, right: 0, paddingBottom: 28,
      background: 'linear-gradient(to top, rgba(11,16,32,0.95) 30%, rgba(11,16,32,0))',
    }}>
      <div style={{
        margin: '0 16px',
        background: 'rgba(19,26,46,0.7)',
        backdropFilter: 'blur(24px) saturate(180%)',
        WebkitBackdropFilter: 'blur(24px) saturate(180%)',
        border: `1px solid ${T.borderHi}`,
        borderRadius: 28, padding: '8px 4px',
        display: 'flex', justifyContent: 'space-around', alignItems: 'center',
        boxShadow: '0 12px 40px rgba(0,0,0,0.5), inset 0 1px 0 rgba(255,255,255,0.08)',
      }}>
        {tabs.map((t) => {
          const isActive = active === t.id;
          if (t.primary) {
            return (
              <button key={t.id} onClick={() => onChange && onChange(t.id)} style={{
                width: 54, height: 54, marginTop: -18, borderRadius: 999,
                background: `radial-gradient(circle at 30% 20%, ${T.cyan}, ${T.violet})`,
                border: 'none', cursor: 'pointer',
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                boxShadow: `0 8px 20px ${T.cyan}66, 0 0 32px ${T.violet}55, inset 0 -2px 0 rgba(0,0,0,0.2)`,
                color: '#06121C',
              }}>
                <Ic d={t.d} size={24} color="#06121C" sw={2.4} />
              </button>
            );
          }
          return (
            <button key={t.id} onClick={() => onChange && onChange(t.id)} style={{
              background: 'transparent', border: 'none', cursor: 'pointer',
              padding: '8px 10px', display: 'flex', flexDirection: 'column',
              alignItems: 'center', gap: 3, color: isActive ? T.cyan : T.textDim,
              fontFamily: T.body, fontWeight: 600, fontSize: 10,
              letterSpacing: '0.02em',
            }}>
              <Ic d={t.d} size={20} />
              <span>{t.label}</span>
            </button>
          );
        })}
      </div>
    </div>
  );
}

// Phone screen scroll wrapper — pads top to clear the iOS status bar +
// dynamic island, and bottom for the floating tab bar.
function Screen({ children, pad = 16, top = 58, bottom = 110 }) {
  return (
    <div style={{
      height: '100%', overflowY: 'auto',
      padding: `${top}px ${pad}px ${bottom}px`,
      boxSizing: 'border-box',
    }}>{children}</div>
  );
}

// Compact stat tile
function Stat({ label, value, sub, color = T.cyan }) {
  return (
    <div style={{ flex: 1 }}>
      <div style={{ fontFamily: T.body, fontSize: 10, fontWeight: 600, color: T.textDim, letterSpacing: '0.12em', textTransform: 'uppercase' }}>{label}</div>
      <div style={{ fontFamily: T.display, fontSize: 26, fontWeight: 700, color: T.text, letterSpacing: '-0.03em', lineHeight: 1, marginTop: 6 }}>{value}</div>
      {sub && <div style={{ fontFamily: T.body, fontSize: 11, fontWeight: 500, color, marginTop: 4 }}>{sub}</div>}
    </div>
  );
}

Object.assign(window, { T, TEAMS, Crest, Glass, Chip, H, NeonBg, Ic, I, TabBar, Screen, Stat });
