// fa/tokens.jsx — Persian / RTL tokens, atoms, shared components

const T = {
  bg: '#0B1020', bg2: '#131A2E', bg3: '#0F1528',
  card: 'rgba(255,255,255,0.06)', cardSolid: '#171F38',
  cardHi: 'rgba(255,255,255,0.10)',
  border: 'rgba(255,255,255,0.08)', borderHi: 'rgba(255,255,255,0.16)',
  cyan: '#00E5FF', violet: '#7C4DFF',
  green: '#00C853', red: '#FF5252', amber: '#FFC107',
  text: '#FFFFFF',
  textMid: 'rgba(255,255,255,0.72)',
  textDim: 'rgba(255,255,255,0.50)',
  textFaint: 'rgba(255,255,255,0.32)',
  display: "'Sora', 'Vazirmatn', system-ui, sans-serif",
  body:    "'Vazirmatn', system-ui, sans-serif",
  mono:    "'JetBrains Mono', monospace",
};

// Persian digit conversion
const FA = '۰۱۲۳۴۵۶۷۸۹';
const toFa = (s) => String(s).replace(/[0-9]/g, d => FA[+d]);

let wc26Boot = null;
let wc26MeCache = undefined;
const wc26MeListeners = new Set();
const wc26BootEvent = 'wc26_boot_updated';
let wc26AuthToken = null;

const emitMe = () => {
  try { wc26MeListeners.forEach((fn) => fn(wc26MeCache)); } catch (e) {}
};

const emitBoot = () => {
  try { window.dispatchEvent(new Event(wc26BootEvent)); } catch (e) {}
};

const wc26BasePath = (() => {
  const p = window.location.pathname || '';
  const i = p.indexOf('/wp-content/');
  return i >= 0 ? p.slice(0, i) : '';
})();

let wc26ApiBase = wc26BasePath + '/wp-json/wc26/v1';
let wc26Nonce = null;

const normalizeApiBase = (v) => String(v || '').replace(/\/+$/, '');

const setBoot = (boot) => {
  if (!boot || typeof boot !== 'object') return;
  wc26Boot = boot;
  if (boot.apiBase) wc26ApiBase = normalizeApiBase(boot.apiBase);
  if (boot.nonce) wc26Nonce = String(boot.nonce);
  if (boot.authToken) wc26AuthToken = String(boot.authToken);
  if (Object.prototype.hasOwnProperty.call(boot, 'me')) {
    wc26MeCache = boot.me;
    emitMe();
  }
  emitBoot();
};

try {
  if (window.wc26AppBoot) setBoot(window.wc26AppBoot);
  else if (window.top && window.top.wc26AppBoot) setBoot(window.top.wc26AppBoot);
} catch (e) {}

try {
  window.addEventListener('message', (ev) => {
    const d = ev && ev.data;
    if (d && d.type === 'wc26_boot' && d.boot) setBoot(d.boot);
  });
} catch (e) {}

const getInitial = (name) => {
  const s = String(name || '').trim();
  return s ? s.charAt(0) : '?';
};

const teamColorsFromSeed = (seed) => {
  const s = String(seed || 'TBD');
  let h = 0;
  for (let i = 0; i < s.length; i++) h = (h * 31 + s.charCodeAt(i)) >>> 0;
  const hue = h % 360;
  const c1 = `hsl(${hue} 85% 55%)`;
  const c2 = `hsl(${(hue + 120) % 360} 85% 45%)`;
  return { c1, c2 };
};

const apiFetch = async (path, { method = 'GET', body = null } = {}) => {
  const base = normalizeApiBase(wc26ApiBase);
  const url = /^https?:\/\//.test(path) ? path : (base + (path.startsWith('/') ? path : ('/' + path)));
  const headers = { 'Accept': 'application/json' };
  if (wc26AuthToken) headers['Authorization'] = 'Bearer ' + wc26AuthToken;
  if (method !== 'GET' && method !== 'HEAD') {
    headers['Content-Type'] = 'application/json; charset=utf-8';
    if (wc26Nonce) headers['X-WP-Nonce'] = wc26Nonce;
  }

  const res = await fetch(url, {
    method,
    credentials: 'include',
    headers,
    body: body ? JSON.stringify((method !== 'GET' && method !== 'HEAD' && wc26Nonce) ? { ...body, nonce: wc26Nonce } : body) : null,
  });

  const txt = await res.text();
  let data = null;
  try { data = txt ? JSON.parse(txt) : null; } catch (e) { data = null; }

  if (!res.ok) {
    const msg = (data && data.message) ? data.message : 'خطا در ارتباط با سرور';
    const err = new Error(msg);
    err.status = res.status;
    err.data = data;
    throw err;
  }

  return data;
};

const apiGet = (path) => apiFetch(path, { method: 'GET' });
const apiPost = (path, body) => apiFetch(path, { method: 'POST', body });

function useApi(path, deps = []) {
  const [data, setData] = React.useState(null);
  const [loading, setLoading] = React.useState(!!path);
  const [error, setError] = React.useState(null);
  const [bootTick, setBootTick] = React.useState(0);

  React.useEffect(() => {
    const onBoot = () => setBootTick((t) => t + 1);
    window.addEventListener(wc26BootEvent, onBoot);
    return () => window.removeEventListener(wc26BootEvent, onBoot);
  }, []);

  React.useEffect(() => {
    let alive = true;
    if (!path) {
      setData(null);
      setLoading(false);
      setError(null);
      return;
    }

    setLoading(true);
    setError(null);
    apiGet(path)
      .then((d) => {
        if (!alive) return;
        setData(d);
        setLoading(false);
      })
      .catch((e) => {
        if (!alive) return;
        setError(e);
        setLoading(false);
      });

    return () => { alive = false; };
  }, [path, bootTick, ...deps]);

  return { data, loading, error };
}

let wc26MePromise = null;

const fetchMe = async () => {
  if (wc26MeCache !== undefined) return wc26MeCache;
  if (wc26MePromise) return wc26MePromise;

  wc26MePromise = apiGet('/me')
    .catch(() => null)
    .then((d) => {
      wc26MeCache = d;
      emitMe();
      return d;
    })
    .finally(() => { wc26MePromise = null; });

  return wc26MePromise;
};

function useMe() {
  const [me, setMe] = React.useState(wc26MeCache !== undefined ? wc26MeCache : null);
  const [loading, setLoading] = React.useState(wc26MeCache === undefined);

  React.useEffect(() => {
    let alive = true;
    const onMe = (v) => {
      if (!alive) return;
      if (v === undefined) {
        setLoading(true);
        return;
      }
      setMe(v);
      setLoading(false);
    };

    wc26MeListeners.add(onMe);

    const onBoot = () => {
      if (!alive) return;
      if (wc26MeCache === undefined) return;
      onMe(wc26MeCache);
    };
    window.addEventListener(wc26BootEvent, onBoot);

    if (wc26MeCache === undefined) {
      fetchMe().then((d) => {
        if (!alive) return;
        setMe(d);
        setLoading(false);
      });
    }
    return () => {
      alive = false;
      wc26MeListeners.delete(onMe);
      window.removeEventListener(wc26BootEvent, onBoot);
    };
  }, []);

  return { me, loading, isLoggedIn: !!me };
}

// Country crests — circular 2-tone gradient discs. Persian display names below.
const TEAMS = {
  // AFC
  IRN: { code: 'IRN', name: 'ایران',              c1: '#1B7F3A', c2: '#E63946' },
  JPN: { code: 'JPN', name: 'ژاپن',               c1: '#F5F5F5', c2: '#E63946' },
  KOR: { code: 'KOR', name: 'کره جنوبی',          c1: '#1F4FD9', c2: '#E63946' },
  AUS: { code: 'AUS', name: 'استرالیا',           c1: '#0E2D6E', c2: '#FFD93D' },
  KSA: { code: 'KSA', name: 'عربستان سعودی',      c1: '#1B7F3A', c2: '#F5F5F5' },
  QAT: { code: 'QAT', name: 'قطر',                c1: '#8B1A3A', c2: '#F5F5F5' },
  IRQ: { code: 'IRQ', name: 'عراق',               c1: '#1B7F3A', c2: '#E63946' },
  JOR: { code: 'JOR', name: 'اردن',               c1: '#1A1A1A', c2: '#E63946' },
  UZB: { code: 'UZB', name: 'ازبکستان',           c1: '#1F4FD9', c2: '#F5F5F5' },
  PLE: { code: 'PLE', name: 'فلسطین',             c1: '#1A1A1A', c2: '#1B7F3A' },
  // UEFA
  FRA: { code: 'FRA', name: 'فرانسه',             c1: '#0E2D6E', c2: '#E63946' },
  ENG: { code: 'ENG', name: 'انگلیس',             c1: '#F5F5F5', c2: '#C8102E' },
  ESP: { code: 'ESP', name: 'اسپانیا',            c1: '#E63946', c2: '#FFC93D' },
  GER: { code: 'GER', name: 'آلمان',              c1: '#1A1A1A', c2: '#FFD93D' },
  POR: { code: 'POR', name: 'پرتغال',             c1: '#1B7F3A', c2: '#E63946' },
  NED: { code: 'NED', name: 'هلند',               c1: '#FF7A1A', c2: '#FFFFFF' },
  ITA: { code: 'ITA', name: 'ایتالیا',            c1: '#1B7F3A', c2: '#7C4DFF' },
  BEL: { code: 'BEL', name: 'بلژیک',              c1: '#1A1A1A', c2: '#FFD93D' },
  CRO: { code: 'CRO', name: 'کرواسی',             c1: '#1F4FD9', c2: '#E63946' },
  SRB: { code: 'SRB', name: 'صربستان',            c1: '#E63946', c2: '#1F4FD9' },
  SUI: { code: 'SUI', name: 'سوئیس',              c1: '#E63946', c2: '#F5F5F5' },
  DEN: { code: 'DEN', name: 'دانمارک',            c1: '#E63946', c2: '#F5F5F5' },
  AUT: { code: 'AUT', name: 'اتریش',              c1: '#E63946', c2: '#F5F5F5' },
  TUR: { code: 'TUR', name: 'ترکیه',              c1: '#E63946', c2: '#F5F5F5' },
  UKR: { code: 'UKR', name: 'اوکراین',            c1: '#1F4FD9', c2: '#FFD93D' },
  HUN: { code: 'HUN', name: 'مجارستان',           c1: '#E63946', c2: '#F5F5F5' },
  CZE: { code: 'CZE', name: 'جمهوری چک',         c1: '#1F4FD9', c2: '#E63946' },
  ROU: { code: 'ROU', name: 'رومانی',             c1: '#1F4FD9', c2: '#FFD93D' },
  SCO: { code: 'SCO', name: 'اسکاتلند',           c1: '#1F4FD9', c2: '#F5F5F5' },
  POL: { code: 'POL', name: 'لهستان',             c1: '#E63946', c2: '#F5F5F5' },
  ALB: { code: 'ALB', name: 'آلبانی',             c1: '#E63946', c2: '#1A1A1A' },
  SVK: { code: 'SVK', name: 'اسلواکی',            c1: '#1F4FD9', c2: '#F5F5F5' },
  SVN: { code: 'SVN', name: 'اسلوونی',            c1: '#1F4FD9', c2: '#F5F5F5' },
  GEO: { code: 'GEO', name: 'گرجستان',            c1: '#E63946', c2: '#F5F5F5' },
  GRE: { code: 'GRE', name: 'یونان',              c1: '#1F4FD9', c2: '#F5F5F5' },
  WAL: { code: 'WAL', name: 'ولز',                c1: '#E63946', c2: '#1B7F3A' },
  NOR: { code: 'NOR', name: 'نروژ',               c1: '#1F4FD9', c2: '#E63946' },
  SWE: { code: 'SWE', name: 'سوئد',               c1: '#1F4FD9', c2: '#FFD93D' },
  // CONMEBOL
  ARG: { code: 'ARG', name: 'آرژانتین',           c1: '#75AADB', c2: '#F5C842' },
  BRA: { code: 'BRA', name: 'برزیل',              c1: '#FFD93D', c2: '#1B7F3A' },
  URU: { code: 'URU', name: 'اروگوئه',            c1: '#1F4FD9', c2: '#F5F5F5' },
  COL: { code: 'COL', name: 'کلمبیا',             c1: '#FFD93D', c2: '#1B7F3A' },
  ECU: { code: 'ECU', name: 'اکوادور',            c1: '#FFD93D', c2: '#1F4FD9' },
  VEN: { code: 'VEN', name: 'ونزوئلا',            c1: '#E63946', c2: '#FFD93D' },
  PAR: { code: 'PAR', name: 'پاراگوئه',           c1: '#E63946', c2: '#F5F5F5' },
  CHI: { code: 'CHI', name: 'شیلی',               c1: '#E63946', c2: '#F5F5F5' },
  PER: { code: 'PER', name: 'پرو',                c1: '#E63946', c2: '#F5F5F5' },
  BOL: { code: 'BOL', name: 'بولیوی',             c1: '#1B7F3A', c2: '#FFD93D' },
  // CONCACAF
  USA: { code: 'USA', name: 'آمریکا',             c1: '#1F4FD9', c2: '#E63946' },
  MEX: { code: 'MEX', name: 'مکزیک',              c1: '#1B7F3A', c2: '#E63946' },
  CAN: { code: 'CAN', name: 'کانادا',             c1: '#E63946', c2: '#F5F5F5' },
  CRC: { code: 'CRC', name: 'کاستاریکا',          c1: '#1F4FD9', c2: '#E63946' },
  HON: { code: 'HON', name: 'هندوراس',            c1: '#1F4FD9', c2: '#F5F5F5' },
  GUA: { code: 'GUA', name: 'گواتمالا',           c1: '#1F4FD9', c2: '#F5F5F5' },
  JAM: { code: 'JAM', name: 'جامائیکا',           c1: '#1B7F3A', c2: '#FFD93D' },
  TTO: { code: 'TTO', name: 'ترینیداد و توباگو',  c1: '#E63946', c2: '#1A1A1A' },
  PAN: { code: 'PAN', name: 'پاناما',             c1: '#E63946', c2: '#1F4FD9' },
  SLV: { code: 'SLV', name: 'السالوادور',         c1: '#1F4FD9', c2: '#F5F5F5' },
  HAI: { code: 'HAI', name: 'هائیتی',             c1: '#1F4FD9', c2: '#E63946' },
  // CAF
  SEN: { code: 'SEN', name: 'سنگال',              c1: '#1B7F3A', c2: '#FFD93D' },
  MAR: { code: 'MAR', name: 'مراکش',              c1: '#1B7F3A', c2: '#C8102E' },
  NGA: { code: 'NGA', name: 'نیجریه',             c1: '#1B7F3A', c2: '#F5F5F5' },
  CMR: { code: 'CMR', name: 'کامرون',             c1: '#1B7F3A', c2: '#E63946' },
  GHA: { code: 'GHA', name: 'غنا',                c1: '#1A1A1A', c2: '#FFD93D' },
  TUN: { code: 'TUN', name: 'تونس',               c1: '#E63946', c2: '#F5F5F5' },
  ALG: { code: 'ALG', name: 'الجزایر',            c1: '#1B7F3A', c2: '#F5F5F5' },
  CIV: { code: 'CIV', name: 'ساحل عاج',           c1: '#FF7A1A', c2: '#1B7F3A' },
  COD: { code: 'COD', name: 'کنگو',               c1: '#1F4FD9', c2: '#E63946' },
  RSA: { code: 'RSA', name: 'آفریقای جنوبی',      c1: '#1B7F3A', c2: '#FFD93D' },
  EGY: { code: 'EGY', name: 'مصر',                c1: '#E63946', c2: '#F5F5F5' },
  MLI: { code: 'MLI', name: 'مالی',               c1: '#1B7F3A', c2: '#FFD93D' },
  BEN: { code: 'BEN', name: 'بنین',               c1: '#1B7F3A', c2: '#FFD93D' },
  KEN: { code: 'KEN', name: 'کنیا',               c1: '#1A1A1A', c2: '#E63946' },
  GUI: { code: 'GUI', name: 'گینه',               c1: '#E63946', c2: '#FFD93D' },
  LBA: { code: 'LBA', name: 'لیبی',               c1: '#1B7F3A', c2: '#E63946' },
  // OFC
  NZL: { code: 'NZL', name: 'نیوزیلند',           c1: '#1A1A1A', c2: '#F5F5F5' },
  FIJ: { code: 'FIJ', name: 'فیجی',               c1: '#1F4FD9', c2: '#F5F5F5' },
};

// English name → Persian name (for teams imported with English names from openfootball)
const TEAM_NAMES_FA = {
  'Argentina': 'آرژانتین', 'Australia': 'استرالیا', 'Belgium': 'بلژیک',
  'Bolivia': 'بولیوی', 'Brazil': 'برزیل', 'Cameroon': 'کامرون',
  'Canada': 'کانادا', 'Chile': 'شیلی', 'Colombia': 'کلمبیا',
  'Costa Rica': 'کاستاریکا', 'Croatia': 'کرواسی', 'Denmark': 'دانمارک',
  'Ecuador': 'اکوادور', 'Egypt': 'مصر', 'El Salvador': 'السالوادور',
  'England': 'انگلیس', 'France': 'فرانسه', 'Germany': 'آلمان',
  'Ghana': 'غنا', 'Greece': 'یونان', 'Honduras': 'هندوراس',
  'Hungary': 'مجارستان', 'Iran': 'ایران', 'Iraq': 'عراق',
  'Italy': 'ایتالیا', 'Jamaica': 'جامائیکا', 'Japan': 'ژاپن',
  'Jordan': 'اردن', 'Kenya': 'کنیا', 'Mali': 'مالی',
  'Mexico': 'مکزیک', 'Morocco': 'مراکش', 'Netherlands': 'هلند',
  'New Zealand': 'نیوزیلند', 'Nigeria': 'نیجریه', 'Panama': 'پاناما',
  'Paraguay': 'پاراگوئه', 'Peru': 'پرو', 'Poland': 'لهستان',
  'Portugal': 'پرتغال', 'Qatar': 'قطر', 'Romania': 'رومانی',
  'Saudi Arabia': 'عربستان سعودی', 'Scotland': 'اسکاتلند', 'Senegal': 'سنگال',
  'Serbia': 'صربستان', 'Slovakia': 'اسلواکی', 'Slovenia': 'اسلوونی',
  'South Africa': 'آفریقای جنوبی', 'South Korea': 'کره جنوبی',
  'Korea Republic': 'کره جنوبی', 'Spain': 'اسپانیا', 'Switzerland': 'سوئیس',
  'Trinidad and Tobago': 'ترینیداد و توباگو', 'Trinidad & Tobago': 'ترینیداد و توباگو',
  'Tunisia': 'تونس', 'Turkey': 'ترکیه', 'Ukraine': 'اوکراین',
  'United States': 'آمریکا', 'USA': 'آمریکا', 'Uruguay': 'اروگوئه',
  'Uzbekistan': 'ازبکستان', 'Venezuela': 'ونزوئلا',
  'Ivory Coast': 'ساحل عاج', "Côte d'Ivoire": 'ساحل عاج', "Cote d'Ivoire": 'ساحل عاج',
  'DR Congo': 'کنگو', 'Congo DR': 'کنگو', 'Algeria': 'الجزایر',
  'Benin': 'بنین', 'Libya': 'لیبی', 'Tanzania': 'تانزانیا',
  'Guinea': 'گینه', 'Albania': 'آلبانی', 'Austria': 'اتریش',
  'Czech Republic': 'جمهوری چک', 'Czechia': 'جمهوری چک',
  'Guatemala': 'گواتمالا', 'Haiti': 'هائیتی', 'Palestine': 'فلسطین',
  'Georgia': 'گرجستان', 'Fiji': 'فیجی', 'Cuba': 'کوبا',
  'Sweden': 'سوئد', 'Norway': 'نروژ', 'Wales': 'ولز',
  'Zambia': 'زامبیا', 'Israel': 'اسرائیل',
};

const normTeamKey = (s) => String(s || '')
  .trim()
  .replace(/\s+/g, ' ')
  .replace(/[’`]/g, "'")
  .replace(/\u00A0/g, ' ');

const TEAM_NAMES_FA_NORM = (() => {
  const out = {};
  Object.keys(TEAM_NAMES_FA).forEach((k) => {
    out[normTeamKey(k).toLowerCase()] = TEAM_NAMES_FA[k];
  });
  return out;
})();

const faTeamName = (code, engName) => {
  const c = normTeamKey(code).toUpperCase();
  if (c && TEAMS[c]?.name) return TEAMS[c].name;
  const n = normTeamKey(engName);
  if (n && TEAM_NAMES_FA[n]) return TEAM_NAMES_FA[n];
  const nl = n.toLowerCase();
  if (nl && TEAM_NAMES_FA_NORM[nl]) return TEAM_NAMES_FA_NORM[nl];
  return String(engName || code || '—').trim() || '—';
};

function Crest({ team, size = 32, glow = false, ring = true }) {
  const t = typeof team === 'string' ? (TEAMS[team] || { code: team, name: team }) : team;
  if (!t) return null;
  const code = t.code || (typeof team === 'string' ? team : 'TBD');
  const colors = (t.c1 && t.c2) ? { c1: t.c1, c2: t.c2 } : teamColorsFromSeed(code);
  const flagUrl = t.flag_url || t.flagUrl || t.flag || '';
  const f = size * 0.34;
  return (
    <div style={{
      width: size, height: size, borderRadius: '50%',
      position: 'relative', flexShrink: 0,
      background: `conic-gradient(from 220deg, ${colors.c1} 0deg 180deg, ${colors.c2} 180deg 360deg)`,
      boxShadow: ring ? `inset 0 0 0 ${Math.max(1, size*0.04)}px rgba(255,255,255,0.18), 0 2px 8px rgba(0,0,0,0.4)${glow ? `, 0 0 24px ${colors.c1}55` : ''}` : 'none',
      display: 'flex', alignItems: 'center', justifyContent: 'center',
    }}>
      {flagUrl ? (
        <img
          src={flagUrl}
          alt={t.name || code}
          style={{
            width: '100%',
            height: '100%',
            borderRadius: '50%',
            objectFit: 'cover',
            display: 'block',
          }}
        />
      ) : (
        <span style={{
          fontWeight: 800, fontSize: f,
          color: '#fff', letterSpacing: '-0.02em', direction: 'ltr',
          textShadow: '0 1px 2px rgba(0,0,0,0.55)',
        }}>{code}</span>
      )}
    </div>
  );
}

function Glass({ children, style = {}, hi = false, glow, p = 16, br = 18, onClick }) {
  return (
    <div onClick={onClick} style={{
      background: hi ? T.cardHi : T.card,
      backdropFilter: 'blur(20px) saturate(140%)',
      WebkitBackdropFilter: 'blur(20px) saturate(140%)',
      border: `1px solid ${hi ? T.borderHi : T.border}`,
      borderRadius: br, padding: p,
      boxShadow: glow
        ? `0 8px 28px rgba(0,0,0,0.4), 0 0 40px ${glow}33, inset 0 1px 0 rgba(255,255,255,0.08)`
        : '0 8px 28px rgba(0,0,0,0.35), inset 0 1px 0 rgba(255,255,255,0.05)',
      ...style,
    }}>{children}</div>
  );
}

function Chip({ children, color = T.cyan, solid = false, style = {} }) {
  return (
    <span style={{
      display: 'inline-flex', alignItems: 'center', gap: 4,
      fontFamily: T.body, fontWeight: 600, fontSize: 10,
      letterSpacing: '0.04em',
      padding: '4px 8px', borderRadius: 999,
      color: solid ? '#06121C' : color,
      background: solid ? color : `${color}1F`,
      border: solid ? 'none' : `1px solid ${color}55`,
      ...style,
    }}>{children}</span>
  );
}

function H({ children, kicker, action, style = {} }) {
  return (
    <div style={{ display: 'flex', alignItems: 'flex-end', justifyContent: 'space-between', marginBottom: 12, gap: 8, ...style }}>
      <div style={{ minWidth: 0 }}>
        {kicker && <div style={{ fontFamily: T.body, fontSize: 10, fontWeight: 700, color: T.cyan, letterSpacing: '0.02em', marginBottom: 4 }}>{kicker}</div>}
        <div style={{ fontFamily: T.display, fontSize: 18, fontWeight: 800, color: T.text, letterSpacing: '-0.02em' }}>{children}</div>
      </div>
      {action && <div style={{ fontFamily: T.body, fontSize: 12, fontWeight: 600, color: T.textMid, flexShrink: 0 }}>{action}</div>}
    </div>
  );
}

function NeonBg({ children, hue = 'mix' }) {
  const bgImg = hue === 'cyan'
    ? `radial-gradient(circle at 20% -10%, ${T.cyan}33, transparent 50%), radial-gradient(circle at 90% 110%, ${T.violet}22, transparent 55%)`
    : hue === 'violet'
    ? `radial-gradient(circle at 80% -10%, ${T.violet}3F, transparent 55%), radial-gradient(circle at 10% 110%, ${T.cyan}22, transparent 55%)`
    : `radial-gradient(circle at 10% -10%, ${T.cyan}2E, transparent 50%), radial-gradient(circle at 95% 5%, ${T.violet}33, transparent 50%), radial-gradient(circle at 50% 110%, ${T.violet}22, transparent 55%)`;
  return (
    <div style={{
      width: '100%', height: '100%', position: 'relative',
      background: T.bg, overflow: 'hidden',
      fontFamily: T.body, color: T.text,
    }}>
      <div style={{ position: 'absolute', inset: 0, pointerEvents: 'none', backgroundImage: bgImg }} />
      <div style={{ position: 'relative', height: '100%' }}>{children}</div>
    </div>
  );
}

// Stroke-style icons. RTL flip is handled per use with style transform.
const I = {
  home: 'M3 11l9-8 9 8v9a2 2 0 0 1-2 2h-4v-7H10v7H6a2 2 0 0 1-2-2z',
  pitch: 'M4 6h16v12H4z M12 6v12 M4 9v6 M20 9v6',
  trophy: 'M8 4h8v3a4 4 0 0 1-8 0z M5 5h3 M16 5h3 M10 13h4v3h-4z M9 19h6',
  crown: 'M3 8l4 5 5-8 5 8 4-5v10H3z',
  users: 'M9 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6z m6 0a3 3 0 1 0 0-6 3 3 0 0 0 0 6z M2 20c0-3 3-5 7-5s7 2 7 5 M14 15c4 0 7 2 7 5',
  user: 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8z m-8 9c0-4 4-7 8-7s8 3 8 7',
  bell: 'M6 8a6 6 0 0 1 12 0v5l2 3H4l2-3z M10 16a2 2 0 0 0 4 0',
  bolt: 'M13 2L4 14h7l-1 8 9-12h-7z',
  fire: 'M12 22c5 0 8-3 8-7 0-3-2-5-3-7-1 2-3 3-3 1 0-4-4-4-4-9-3 3-6 6-6 12 0 6 3 10 8 10z',
  target: 'M12 4a8 8 0 1 0 0 16 8 8 0 0 0 0-16z M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8z',
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
};
const Ic = ({ d, size = 18, color = 'currentColor', sw = 1.7, style }) => (
  <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke={color} strokeWidth={sw} strokeLinecap="round" strokeLinejoin="round" style={style}>
    <path d={d} />
  </svg>
);

function TabBar({ active, onChange }) {
  const tabs = [
    { id: 'home',    label: 'خانه',       d: I.home },
    { id: 'matches', label: 'مسابقه‌ها', d: I.pitch },
    { id: 'predict', label: 'پیش‌بینی',   d: I.target, primary: true },
    { id: 'rank',    label: 'رتبه‌ها',    d: I.trophy },
    { id: 'me',      label: 'پروفایل',    d: I.user },
  ];
  return (
    <div style={{
      position: 'absolute', bottom: 0, left: 0, right: 0,
      paddingBottom: 'max(12px, env(safe-area-inset-bottom))',
      paddingTop: 24,
      background: 'linear-gradient(to top, rgba(11,16,32,0.95) 30%, rgba(11,16,32,0))',
      zIndex: 10,
    }}>
      <div style={{
        margin: '0 16px',
        background: 'rgba(19,26,46,0.78)',
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
              <button key={t.id} onClick={() => onChange(t.id)} aria-label={t.label} style={{
                width: 54, height: 54, marginTop: -18, borderRadius: 999,
                background: `radial-gradient(circle at 30% 20%, ${T.cyan}, ${T.violet})`,
                border: 'none', cursor: 'pointer',
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                boxShadow: `0 8px 20px ${T.cyan}66, 0 0 32px ${T.violet}55, inset 0 -2px 0 rgba(0,0,0,0.2)`,
              }}>
                <Ic d={t.d} size={24} color="#06121C" sw={2.4} />
              </button>
            );
          }
          return (
            <button key={t.id} onClick={() => onChange(t.id)} style={{
              background: 'transparent', border: 'none', cursor: 'pointer',
              padding: '8px 10px', display: 'flex', flexDirection: 'column',
              alignItems: 'center', gap: 3, color: isActive ? T.cyan : T.textDim,
              fontFamily: T.body, fontWeight: 700, fontSize: 10,
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

// Top app bar inside a screen — pads top for safe area, holds title + right actions
function AppBar({ title, kicker, leadingBack, onBack, right }) {
  return (
    <div style={{
      display: 'flex', alignItems: 'center', gap: 10,
      padding: '14px 0 16px',
    }}>
      {leadingBack && (
        <button onClick={onBack} aria-label="بازگشت" style={{
          background: T.card, border: `1px solid ${T.border}`, borderRadius: 12, padding: 8, color: T.text, cursor: 'pointer',
          display: 'flex', alignItems: 'center', justifyContent: 'center',
        }}>
          {/* In RTL, "back" arrow points right (visual previous) */}
          <Ic d={I.chev} size={18} sw={2.2} color={T.text} />
        </button>
      )}
      <div style={{ flex: 1, minWidth: 0 }}>
        {kicker && <div style={{ fontFamily: T.body, fontSize: 10, fontWeight: 700, color: T.cyan, letterSpacing: '0.02em' }}>{kicker}</div>}
        <div style={{ fontFamily: T.display, fontSize: 22, fontWeight: 800, color: T.text, letterSpacing: '-0.03em', marginTop: 2 }}>{title}</div>
      </div>
      {right}
    </div>
  );
}

function Screen({ children, pad = 16, top = 12, bottom = 110 }) {
  return (
    <div style={{
      height: '100%', overflowY: 'auto',
      padding: `${top}px ${pad}px ${bottom}px`,
      paddingTop: `calc(${top}px + env(safe-area-inset-top, 0px))`,
      boxSizing: 'border-box',
    }}>{children}</div>
  );
}

function Stat({ label, value, sub, color = T.cyan }) {
  return (
    <div style={{ flex: 1, textAlign: 'center' }}>
      <div style={{ fontFamily: T.body, fontSize: 10, fontWeight: 700, color: T.textDim }}>{label}</div>
      <div style={{ fontFamily: T.display, fontSize: 22, fontWeight: 800, color: T.text, letterSpacing: '-0.02em', lineHeight: 1, marginTop: 6, direction: 'ltr' }}>{value}</div>
      {sub && <div style={{ fontFamily: T.body, fontSize: 11, fontWeight: 600, color, marginTop: 4 }}>{sub}</div>}
    </div>
  );
}

// Small avatar bubble — uses Latin initial for stable rendering.
function Avatar({ seed, idx = 0, size = 32, glow }) {
  const letter = (seed || '?').trim().charAt(0) || '?';
  return (
    <div style={{
      width: size, height: size, borderRadius: '50%',
      background: `linear-gradient(135deg, hsl(${idx*53} 70% 55%), hsl(${idx*53+50} 70% 35%))`,
      display: 'flex', alignItems: 'center', justifyContent: 'center',
      fontWeight: 800, color: '#fff',
      fontSize: size * 0.42, direction: 'ltr',
      boxShadow: glow ? `0 0 16px ${glow}` : `0 2px 6px rgba(0,0,0,0.35)`,
      border: `2px solid rgba(255,255,255,0.12)`,
      flexShrink: 0,
    }}>{letter}</div>
  );
}

Object.assign(window, {
  T, TEAMS, TEAM_NAMES_FA, faTeamName, toFa, FA,
  Crest, Glass, Chip, H, NeonBg, Ic, I, TabBar, AppBar, Screen, Stat, Avatar,
  wc26ApiBase, wc26Nonce, apiGet, apiPost, useApi, fetchMe, useMe, getInitial,
});
