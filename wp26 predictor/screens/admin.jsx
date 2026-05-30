// admin.jsx — Admin dashboard (desktop)
const { T, Crest, Glass, Chip, NeonBg, Ic, I, TEAMS } = window;

function AdminSidebar({ active = 'matches' }) {
  const items = [
    { id: 'dash', label: 'Overview', d: I.home },
    { id: 'matches', label: 'Matches', d: I.pitch },
    { id: 'teams', label: 'Teams', d: I.users },
    { id: 'rules', label: 'Scoring rules', d: I.set },
    { id: 'rank', label: 'Leaderboards', d: I.trophy },
    { id: 'notifs', label: 'Notifications', d: I.bell },
    { id: 'imports', label: 'Imports', d: I.bolt },
  ];
  return (
    <div style={{
      width: 240, padding: 18, flexShrink: 0,
      background: 'rgba(11,16,32,0.5)',
      borderRight: `1px solid ${T.border}`,
      display: 'flex', flexDirection: 'column', height: '100%',
    }}>
      <div style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '4px 6px 22px' }}>
        <div style={{
          width: 36, height: 36, borderRadius: 10,
          background: `linear-gradient(135deg, ${T.cyan}, ${T.violet})`,
          display: 'flex', alignItems: 'center', justifyContent: 'center',
          fontFamily: T.display, fontWeight: 800, color: '#06121C', fontSize: 14,
          boxShadow: `0 0 18px ${T.cyan}55`,
        }}>26</div>
        <div>
          <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 14, color: T.text, letterSpacing: '-0.02em' }}>WP26 Predictor</div>
          <div style={{ fontFamily: T.body, fontSize: 10, color: T.textDim }}>Admin · v2.4.0</div>
        </div>
      </div>

      <div style={{ flex: 1 }}>
        {items.map((it) => {
          const isActive = it.id === active;
          return (
            <div key={it.id} style={{
              display: 'flex', alignItems: 'center', gap: 10,
              padding: '10px 12px', marginBottom: 2, borderRadius: 10, cursor: 'pointer',
              background: isActive ? `linear-gradient(90deg, ${T.cyan}22, ${T.violet}14)` : 'transparent',
              border: `1px solid ${isActive ? T.cyan + '44' : 'transparent'}`,
              color: isActive ? T.text : T.textMid,
              fontFamily: T.body, fontSize: 13, fontWeight: isActive ? 700 : 500,
              boxShadow: isActive ? `0 0 16px ${T.cyan}22` : 'none',
            }}>
              <Ic d={it.d} size={16} color={isActive ? T.cyan : T.textMid} />
              <span>{it.label}</span>
              {isActive && <div style={{ marginLeft: 'auto', width: 4, height: 4, borderRadius: 999, background: T.cyan, boxShadow: `0 0 8px ${T.cyan}` }} />}
            </div>
          );
        })}
      </div>

      <div style={{ padding: 12, borderRadius: 14, background: T.card, border: `1px solid ${T.border}`, marginTop: 14 }}>
        <div style={{ fontFamily: T.body, fontSize: 10, fontWeight: 700, color: T.textDim, letterSpacing: '0.14em', textTransform: 'uppercase' }}>Tournament status</div>
        <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 16, color: T.text, marginTop: 4 }}>● Live</div>
        <div style={{ fontFamily: T.body, fontSize: 11, color: T.textMid, marginTop: 2 }}>Group stage · MD 2 of 3</div>
        <div style={{ marginTop: 10, height: 4, borderRadius: 2, background: T.bg3, overflow: 'hidden' }}>
          <div style={{ width: '32%', height: '100%', background: `linear-gradient(90deg, ${T.cyan}, ${T.violet})` }} />
        </div>
      </div>
    </div>
  );
}

function StatCard({ label, value, delta, color, sparkline }) {
  return (
    <Glass br={16} p={16} style={{ flex: 1 }}>
      <div style={{ fontFamily: T.body, fontSize: 10, fontWeight: 700, color: T.textDim, letterSpacing: '0.14em', textTransform: 'uppercase' }}>{label}</div>
      <div style={{ display: 'flex', alignItems: 'baseline', justifyContent: 'space-between', marginTop: 6 }}>
        <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 28, color: T.text, letterSpacing: '-0.03em' }}>{value}</div>
        <div style={{ fontFamily: T.body, fontSize: 12, fontWeight: 700, color: delta.startsWith('-') ? T.red : T.green }}>{delta}</div>
      </div>
      {sparkline}
    </Glass>
  );
}

function Spark({ color, points = [3, 5, 4, 7, 6, 9, 8, 11, 10, 13, 12, 15] }) {
  const w = 100, h = 30;
  const max = Math.max(...points);
  const step = w / (points.length - 1);
  const d = points.map((p, i) => `${i === 0 ? 'M' : 'L'} ${i * step} ${h - (p / max) * h}`).join(' ');
  return (
    <svg viewBox={`0 0 ${w} ${h}`} preserveAspectRatio="none" style={{ width: '100%', height: 32, marginTop: 10 }}>
      <defs>
        <linearGradient id={`g-${color.slice(1)}`} x1="0" x2="0" y1="0" y2="1">
          <stop offset="0%" stopColor={color} stopOpacity="0.5" />
          <stop offset="100%" stopColor={color} stopOpacity="0" />
        </linearGradient>
      </defs>
      <path d={`${d} L ${w} ${h} L 0 ${h} Z`} fill={`url(#g-${color.slice(1)})`} />
      <path d={d} fill="none" stroke={color} strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
    </svg>
  );
}

function AdminScreen() {
  const matches = [
    { home: 'ARG', away: 'NED', stage: 'R16', date: 'Sun · 17:00', venue: 'Mercedes-Benz · ATL', status: 'open', preds: 12482 },
    { home: 'FRA', away: 'POR', stage: 'R16', date: 'Sat · 17:30', venue: 'SoFi · LA', status: 'open', preds: 9120 },
    { home: 'BRA', away: 'CRO', stage: 'R16', date: 'Tomorrow · 21:00', venue: 'AT&T · Dallas', status: 'open', preds: 14056 },
    { home: 'MEX', away: 'CAN', stage: 'Group A · MD2', date: 'Today · 14:00', venue: 'BMO · LA', status: 'final', score: '2-0', preds: 24890 },
    { home: 'USA', away: 'JPN', stage: 'Group A · MD2', date: 'Today · 17:30', venue: 'BMO · LA', status: 'live', score: '1-0', preds: 18204 },
    { home: 'GER', away: 'ESP', stage: 'Group F · MD1', date: 'Yesterday', venue: 'Lincoln · Phl', status: 'final', score: '2-2', preds: 21340 },
  ];

  const statusColor = (s) => s === 'live' ? T.red : s === 'final' ? T.textMid : T.cyan;
  const statusLabel = (s) => s === 'live' ? '● Live' : s === 'final' ? 'Final' : 'Predictions open';

  return (
    <div style={{ width: '100%', height: '100%', display: 'flex', background: T.bg, color: T.text, fontFamily: T.body, overflow: 'hidden' }}>
      {/* Glow background */}
      <div style={{ position: 'absolute', inset: 0, pointerEvents: 'none',
        backgroundImage: `radial-gradient(circle at 10% 10%, ${T.cyan}22, transparent 40%), radial-gradient(circle at 95% 5%, ${T.violet}22, transparent 40%)` }} />

      <AdminSidebar />

      <div style={{ flex: 1, overflow: 'auto', position: 'relative' }}>
        {/* Top bar */}
        <div style={{
          display: 'flex', alignItems: 'center', gap: 16,
          padding: '16px 28px',
          borderBottom: `1px solid ${T.border}`,
          background: 'rgba(11,16,32,0.5)',
          backdropFilter: 'blur(20px)',
          position: 'sticky', top: 0, zIndex: 5,
        }}>
          <div>
            <div style={{ fontFamily: T.body, fontSize: 11, fontWeight: 600, color: T.cyan, letterSpacing: '0.14em', textTransform: 'uppercase' }}>Tournament management</div>
            <div style={{ fontFamily: T.display, fontSize: 22, fontWeight: 800, color: T.text, letterSpacing: '-0.02em', marginTop: 2 }}>Matches</div>
          </div>
          <div style={{ flex: 1 }} />
          <div style={{
            display: 'flex', alignItems: 'center', gap: 8,
            background: T.card, border: `1px solid ${T.border}`, borderRadius: 10,
            padding: '8px 12px', width: 280, color: T.textDim,
          }}>
            <Ic d={I.search} size={14} color={T.textDim} />
            <span style={{ fontFamily: T.body, fontSize: 12 }}>Search match, team, stadium…</span>
            <span style={{ marginLeft: 'auto', fontFamily: T.mono, fontSize: 10, padding: '2px 6px', borderRadius: 4, background: T.bg3, border: `1px solid ${T.border}` }}>⌘K</span>
          </div>
          <button style={{
            background: T.card, border: `1px solid ${T.border}`, color: T.text,
            borderRadius: 10, padding: '8px 14px', fontFamily: T.body, fontSize: 12, fontWeight: 600, cursor: 'pointer',
            display: 'flex', alignItems: 'center', gap: 6,
          }}><Ic d={I.share} size={14} /> Import CSV</button>
          <button style={{
            background: `linear-gradient(135deg, ${T.cyan}, ${T.violet})`, border: 'none', color: '#06121C',
            borderRadius: 10, padding: '8px 14px', fontFamily: T.display, fontSize: 12, fontWeight: 800, cursor: 'pointer',
            display: 'flex', alignItems: 'center', gap: 6,
            boxShadow: `0 0 18px ${T.cyan}55`,
          }}><Ic d={I.plus} size={14} color="#06121C" sw={2.6} /> New match</button>
        </div>

        <div style={{ padding: 28, position: 'relative' }}>
          {/* Stat row */}
          <div style={{ display: 'flex', gap: 14, marginBottom: 20 }}>
            <StatCard label="Active predictors" value="84,201" delta="+12.4%" color={T.cyan} sparkline={<Spark color={T.cyan} />} />
            <StatCard label="Predictions / hr" value="2,340" delta="+8.1%" color={T.violet} sparkline={<Spark color={T.violet} points={[2,4,3,6,5,8,7,9,8,11,10,12]} />} />
            <StatCard label="Avg accuracy" value="44.2%" delta="+0.6%" color={T.green} sparkline={<Spark color={T.green} points={[5,4,5,6,5,7,6,8,7,8,9,10]} />} />
            <StatCard label="Server load" value="38%" delta="-3.2%" color={T.amber} sparkline={<Spark color={T.amber} points={[6,8,7,9,7,6,5,7,6,5,4,5]} />} />
          </div>

          {/* Filters */}
          <Glass br={14} p={12} style={{ marginBottom: 14, display: 'flex', alignItems: 'center', gap: 10 }}>
            <div style={{ display: 'flex', gap: 6 }}>
              {['All', 'Open', 'Live', 'Final', 'Hidden'].map((f, i) => (
                <div key={i} style={{
                  padding: '6px 12px', borderRadius: 8,
                  background: i === 0 ? `${T.cyan}22` : 'transparent',
                  border: `1px solid ${i === 0 ? T.cyan + '55' : T.border}`,
                  fontFamily: T.body, fontSize: 11, fontWeight: 600, color: i === 0 ? T.cyan : T.textMid,
                }}>{f}</div>
              ))}
            </div>
            <div style={{ flex: 1 }} />
            <div style={{ fontFamily: T.body, fontSize: 11, color: T.textDim }}>Showing <span style={{ color: T.text, fontWeight: 700 }}>6</span> of 64 matches · Round 2</div>
            <div style={{ width: 1, height: 16, background: T.border }} />
            <div style={{ fontFamily: T.body, fontSize: 11, color: T.textDim }}>Group <span style={{ color: T.text, fontWeight: 700 }}>All ▾</span></div>
          </Glass>

          {/* Match table */}
          <Glass br={16} p={0} style={{ overflow: 'hidden' }}>
            {/* Header row */}
            <div style={{
              display: 'grid', gridTemplateColumns: '2.4fr 1.2fr 1.4fr 1.1fr 1.3fr 70px',
              padding: '12px 18px',
              borderBottom: `1px solid ${T.border}`,
              background: T.bg3,
              fontFamily: T.body, fontSize: 10, fontWeight: 700, color: T.textDim,
              letterSpacing: '0.14em', textTransform: 'uppercase',
            }}>
              <div>Match</div>
              <div>Stage</div>
              <div>Kickoff</div>
              <div>Status</div>
              <div>Predictions</div>
              <div></div>
            </div>
            {matches.map((m, i) => (
              <div key={i} style={{
                display: 'grid', gridTemplateColumns: '2.4fr 1.2fr 1.4fr 1.1fr 1.3fr 70px',
                padding: '14px 18px', alignItems: 'center',
                borderBottom: i < matches.length - 1 ? `1px solid ${T.border}` : 'none',
              }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                  <div style={{ display: 'flex', alignItems: 'center' }}>
                    <Crest team={m.home} size={28} />
                    <div style={{ marginLeft: -8 }}><Crest team={m.away} size={28} ring /></div>
                  </div>
                  <div>
                    <div style={{ fontFamily: T.display, fontWeight: 700, fontSize: 13, color: T.text }}>
                      {m.home} {m.score ? <span style={{ color: T.textMid, fontVariantNumeric: 'tabular-nums', margin: '0 6px' }}>{m.score}</span> : <span style={{ color: T.textDim }}>vs</span>} {m.away}
                    </div>
                    <div style={{ fontFamily: T.body, fontSize: 10, color: T.textDim, marginTop: 1 }}>{m.venue}</div>
                  </div>
                </div>
                <div style={{ fontFamily: T.body, fontSize: 12, color: T.textMid }}>{m.stage}</div>
                <div style={{ fontFamily: T.body, fontSize: 12, color: T.text, fontVariantNumeric: 'tabular-nums' }}>{m.date}</div>
                <div>
                  <span style={{
                    display: 'inline-flex', alignItems: 'center',
                    padding: '4px 8px', borderRadius: 999,
                    background: `${statusColor(m.status)}1F`,
                    border: `1px solid ${statusColor(m.status)}55`,
                    color: statusColor(m.status),
                    fontFamily: T.body, fontSize: 10, fontWeight: 700,
                    letterSpacing: '0.06em', textTransform: 'uppercase',
                  }}>{statusLabel(m.status)}</span>
                </div>
                <div style={{ fontFamily: T.display, fontWeight: 700, fontSize: 13, color: T.text, fontVariantNumeric: 'tabular-nums' }}>{m.preds.toLocaleString()}</div>
                <div style={{ display: 'flex', gap: 4, justifyContent: 'flex-end' }}>
                  <button style={{ background: 'transparent', border: 'none', color: T.textMid, cursor: 'pointer', padding: 4 }}>···</button>
                </div>
              </div>
            ))}
          </Glass>

          {/* Bottom: scoring rules + activity */}
          <div style={{ display: 'flex', gap: 14, marginTop: 18 }}>
            <Glass br={16} p={18} style={{ flex: 1.2 }}>
              <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 14 }}>
                <div>
                  <div style={{ fontFamily: T.body, fontSize: 10, fontWeight: 700, color: T.cyan, letterSpacing: '0.14em', textTransform: 'uppercase' }}>Live config</div>
                  <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 16, color: T.text, marginTop: 2 }}>Scoring rules</div>
                </div>
                <button style={{ background: 'transparent', border: `1px solid ${T.border}`, color: T.text, borderRadius: 8, padding: '6px 12px', fontFamily: T.body, fontSize: 11, fontWeight: 600, cursor: 'pointer' }}>Edit</button>
              </div>
              {[
                { l: 'Exact score', v: 8, c: T.cyan },
                { l: 'Goal difference', v: 4, c: T.violet },
                { l: 'Winner only', v: 2, c: T.green },
                { l: 'Knockout bonus', v: '×1.5', c: T.amber },
                { l: 'Joker multiplier', v: '×2', c: T.amber },
              ].map((r, i) => (
                <div key={i} style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '10px 0', borderTop: i ? `1px solid ${T.border}` : 'none' }}>
                  <div style={{ width: 6, height: 6, borderRadius: 999, background: r.c, boxShadow: `0 0 8px ${r.c}` }} />
                  <span style={{ flex: 1, fontFamily: T.body, fontSize: 12, color: T.text }}>{r.l}</span>
                  <span style={{ fontFamily: T.display, fontWeight: 800, fontSize: 14, color: r.c }}>{r.v}</span>
                </div>
              ))}
            </Glass>
            <Glass br={16} p={18} style={{ flex: 1 }}>
              <div style={{ fontFamily: T.body, fontSize: 10, fontWeight: 700, color: T.cyan, letterSpacing: '0.14em', textTransform: 'uppercase' }}>Recent activity</div>
              <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 16, color: T.text, marginTop: 2, marginBottom: 14 }}>Admin log</div>
              {[
                { user: 'You', action: 'Updated final score', target: 'MEX 2-0 CAN', t: '2m ago', c: T.green },
                { user: 'You', action: 'Imported fixture CSV', target: '64 matches', t: '1h ago', c: T.cyan },
                { user: 'Sara P.', action: 'Edited scoring rule', target: 'Joker × 2', t: '6h ago', c: T.violet },
                { user: 'Sara P.', action: 'Published notification', target: 'R16 reminder', t: 'Yesterday', c: T.amber },
              ].map((a, i) => (
                <div key={i} style={{ display: 'flex', gap: 10, padding: '10px 0', borderTop: i ? `1px solid ${T.border}` : 'none' }}>
                  <div style={{ width: 8, height: 8, borderRadius: 999, marginTop: 6, background: a.c, boxShadow: `0 0 8px ${a.c}`, flexShrink: 0 }} />
                  <div style={{ flex: 1, minWidth: 0 }}>
                    <div style={{ fontFamily: T.body, fontSize: 12, color: T.text }}>
                      <span style={{ fontWeight: 700 }}>{a.user}</span>{' '}
                      <span style={{ color: T.textMid }}>{a.action}</span>{' '}
                      <span style={{ fontWeight: 600 }}>{a.target}</span>
                    </div>
                    <div style={{ fontFamily: T.body, fontSize: 10, color: T.textDim, marginTop: 2 }}>{a.t}</div>
                  </div>
                </div>
              ))}
            </Glass>
          </div>
        </div>
      </div>
    </div>
  );
}

window.AdminScreen = AdminScreen;
