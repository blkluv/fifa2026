// dashboard.jsx — Home dashboard
const { T, Crest, Glass, Chip, H, NeonBg, Ic, I, TabBar, Screen, Stat } = window;

function CountdownBlock({ days, hours, mins, secs }) {
  const cell = (v, l) => (
    <div style={{ textAlign: 'center', minWidth: 44 }}>
      <div style={{ fontFamily: T.display, fontSize: 26, fontWeight: 800, color: T.text, letterSpacing: '-0.04em', lineHeight: 1, fontVariantNumeric: 'tabular-nums' }}>{String(v).padStart(2, '0')}</div>
      <div style={{ fontFamily: T.body, fontSize: 9, fontWeight: 600, color: T.textDim, letterSpacing: '0.16em', textTransform: 'uppercase', marginTop: 4 }}>{l}</div>
    </div>
  );
  const dot = () => <div style={{ color: T.cyan, fontSize: 22, fontWeight: 800, marginTop: -8 }}>:</div>;
  return (
    <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 4 }}>
      {cell(days, 'Days')}{dot()}{cell(hours, 'Hrs')}{dot()}{cell(mins, 'Min')}{dot()}{cell(secs, 'Sec')}
    </div>
  );
}

function HomeScreen() {
  return (
    <NeonBg>
      <Screen>
        {/* Top bar */}
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '6px 0 18px' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
            <div style={{
              width: 38, height: 38, borderRadius: 12,
              background: `linear-gradient(135deg, ${T.cyan}, ${T.violet})`,
              display: 'flex', alignItems: 'center', justifyContent: 'center',
              fontFamily: T.display, fontWeight: 800, color: '#06121C', fontSize: 16, letterSpacing: '-0.04em',
              boxShadow: `0 0 24px ${T.cyan}55`,
            }}>26</div>
            <div>
              <div style={{ fontFamily: T.body, fontSize: 11, color: T.textDim, fontWeight: 500 }}>Good evening</div>
              <div style={{ fontFamily: T.display, fontSize: 16, fontWeight: 700, color: T.text, letterSpacing: '-0.02em' }}>Diego M.</div>
            </div>
          </div>
          <div style={{ display: 'flex', gap: 8 }}>
            <Glass p={9} br={12} style={{ position: 'relative' }}>
              <Ic d={I.bell} size={20} color={T.text} />
              <div style={{ position: 'absolute', top: 6, right: 6, width: 8, height: 8, borderRadius: 999, background: T.cyan, boxShadow: `0 0 8px ${T.cyan}` }} />
            </Glass>
            <Glass p={9} br={12}><Ic d={I.search} size={20} color={T.text} /></Glass>
          </div>
        </div>

        {/* Hero rank card */}
        <Glass hi glow={T.cyan} br={22} p={18} style={{ marginBottom: 14, position: 'relative', overflow: 'hidden' }}>
          <div style={{
            position: 'absolute', right: -40, top: -40, width: 180, height: 180,
            background: `radial-gradient(circle, ${T.violet}55, transparent 65%)`,
          }} />
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 16, position: 'relative' }}>
            <Chip color={T.cyan}>● Live · Group Stage</Chip>
            <div style={{ display: 'flex', alignItems: 'center', gap: 4, color: T.green, fontFamily: T.body, fontWeight: 700, fontSize: 12 }}>
              <Ic d={I.trend} size={14} sw={2} /> +14
            </div>
          </div>
          <div style={{ display: 'flex', alignItems: 'flex-end', gap: 16, position: 'relative' }}>
            <div>
              <div style={{ fontFamily: T.body, fontSize: 10, fontWeight: 600, color: T.textDim, letterSpacing: '0.14em', textTransform: 'uppercase' }}>Your Rank</div>
              <div style={{ display: 'flex', alignItems: 'baseline', gap: 6, marginTop: 4 }}>
                <span style={{ fontFamily: T.display, fontSize: 14, color: T.textMid, fontWeight: 600 }}>#</span>
                <span style={{ fontFamily: T.display, fontSize: 44, fontWeight: 800, color: T.text, letterSpacing: '-0.04em', lineHeight: 1 }}>247</span>
              </div>
              <div style={{ fontFamily: T.body, fontSize: 11, color: T.textDim, marginTop: 4 }}>of 84,201 predictors</div>
            </div>
            <div style={{ flex: 1, textAlign: 'right' }}>
              <div style={{ fontFamily: T.body, fontSize: 10, fontWeight: 600, color: T.textDim, letterSpacing: '0.14em', textTransform: 'uppercase' }}>Points</div>
              <div style={{ fontFamily: T.display, fontSize: 44, fontWeight: 800, lineHeight: 1, marginTop: 4, letterSpacing: '-0.04em',
                background: `linear-gradient(180deg, ${T.cyan}, ${T.violet})`, WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent' }}>1,284</div>
              <div style={{ fontFamily: T.body, fontSize: 11, color: T.green, marginTop: 4, fontWeight: 600 }}>+86 this round</div>
            </div>
          </div>
          <div style={{ display: 'flex', gap: 10, marginTop: 16, paddingTop: 14, borderTop: `1px solid ${T.border}` }}>
            <Stat label="Accuracy" value="61%" sub="↑ 4%" color={T.green} />
            <div style={{ width: 1, background: T.border }} />
            <Stat label="Exact" value="9" sub="of 24" color={T.cyan} />
            <div style={{ width: 1, background: T.border }} />
            <Stat label="Streak" value="4" sub="🔥 hot" color={T.amber} />
          </div>
        </Glass>

        {/* Next match countdown */}
        <Glass br={20} p={16} style={{ marginBottom: 18, position: 'relative', overflow: 'hidden' }}>
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 14 }}>
            <Chip color={T.amber}>Lock in</Chip>
            <div style={{ fontFamily: T.body, fontSize: 11, color: T.textMid }}>BMO Stadium · Group A</div>
          </div>
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
            <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 6, width: 64 }}>
              <Crest team="MEX" size={48} glow />
              <div style={{ fontFamily: T.display, fontSize: 12, fontWeight: 700, color: T.text }}>MEX</div>
            </div>
            <div style={{ flex: 1, padding: '0 8px' }}>
              <CountdownBlock days={2} hours={14} mins={36} secs={12} />
            </div>
            <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 6, width: 64 }}>
              <Crest team="USA" size={48} glow />
              <div style={{ fontFamily: T.display, fontSize: 12, fontWeight: 700, color: T.text }}>USA</div>
            </div>
          </div>
          <button style={{
            marginTop: 14, width: '100%',
            background: `linear-gradient(135deg, ${T.cyan}, ${T.violet})`,
            border: 'none', borderRadius: 14, padding: '12px',
            fontFamily: T.display, fontWeight: 700, fontSize: 14, color: '#06121C',
            letterSpacing: '-0.01em', cursor: 'pointer',
            boxShadow: `0 8px 20px ${T.cyan}44, inset 0 -2px 0 rgba(0,0,0,0.15)`,
          }}>Make prediction →</button>
        </Glass>

        {/* Upcoming */}
        <H kicker="Round 2 · 6 matches" action="See all">Upcoming fixtures</H>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 10, marginBottom: 22 }}>
          {[
            { home: 'BRA', away: 'CRO', time: 'Tomorrow · 21:00', stage: 'R16', locked: false },
            { home: 'FRA', away: 'POR', time: 'Sat · 17:30', stage: 'R16', locked: false },
            { home: 'ARG', away: 'NED', time: 'Sat · 21:00', stage: 'R16', locked: true },
          ].map((m, i) => (
            <Glass key={i} br={14} p={12}>
              <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: -6, width: 64 }}>
                  <Crest team={m.home} size={28} />
                  <Crest team={m.away} size={28} ring />
                </div>
                <div style={{ flex: 1 }}>
                  <div style={{ fontFamily: T.display, fontWeight: 700, fontSize: 13, color: T.text }}>{m.home} vs {m.away}</div>
                  <div style={{ fontFamily: T.body, fontSize: 11, color: T.textDim, marginTop: 2 }}>{m.time} · {m.stage}</div>
                </div>
                {m.locked ? (
                  <Chip color={T.amber}><Ic d={I.lock} size={10} sw={2.4} /> Locked</Chip>
                ) : (
                  <Chip color={T.cyan} solid>Predict</Chip>
                )}
              </div>
            </Glass>
          ))}
        </div>

        {/* Mini league widget */}
        <H kicker="Mini league" action="Manage">El Grupo</H>
        <Glass br={18} p={14} style={{ marginBottom: 22 }}>
          {[
            { rank: 1, name: 'Sofía R.', pts: 1402, you: false, mv: 0 },
            { rank: 2, name: 'You', pts: 1284, you: true, mv: 1 },
            { rank: 3, name: 'Marco D.', pts: 1276, you: false, mv: -1 },
            { rank: 4, name: 'Lina K.', pts: 1198, you: false, mv: 0 },
          ].map((r, i) => (
            <div key={i} style={{
              display: 'flex', alignItems: 'center', gap: 10,
              padding: '10px 8px',
              borderRadius: 12,
              background: r.you ? `linear-gradient(90deg, ${T.cyan}1A, ${T.violet}14)` : 'transparent',
              border: r.you ? `1px solid ${T.cyan}55` : '1px solid transparent',
              marginBottom: i < 3 ? 2 : 0,
            }}>
              <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 13, color: r.rank === 1 ? T.amber : T.textMid, width: 22 }}>{r.rank}</div>
              <div style={{
                width: 30, height: 30, borderRadius: '50%',
                background: `linear-gradient(135deg, hsl(${i*70} 60% 50%), hsl(${i*70+40} 60% 35%))`,
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                fontFamily: T.display, fontWeight: 700, color: '#fff', fontSize: 12,
              }}>{r.name[0]}</div>
              <div style={{ flex: 1, fontFamily: T.body, fontWeight: r.you ? 700 : 600, fontSize: 13, color: T.text }}>{r.name}</div>
              {r.mv !== 0 && (
                <span style={{ fontFamily: T.body, fontSize: 10, fontWeight: 700, color: r.mv > 0 ? T.green : T.red }}>{r.mv > 0 ? '▲' : '▼'} {Math.abs(r.mv)}</span>
              )}
              <div style={{ fontFamily: T.display, fontWeight: 700, fontSize: 14, color: T.text, fontVariantNumeric: 'tabular-nums' }}>{r.pts.toLocaleString()}</div>
            </div>
          ))}
        </Glass>

        {/* Badge progress */}
        <H kicker="2 of 14 earned" action="View all">Achievements</H>
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 10, marginBottom: 18 }}>
          {[
            { name: 'Oracle', icon: I.crown, color: T.amber, earned: true, prog: 100 },
            { name: 'Exact 5×', icon: I.target, color: T.cyan, earned: false, prog: 60 },
            { name: 'Hot Streak', icon: I.fire, color: T.red, earned: true, prog: 100 },
          ].map((b, i) => (
            <Glass key={i} p={12} br={14} style={{ textAlign: 'center' }} glow={b.earned ? b.color : undefined}>
              <div style={{
                width: 44, height: 44, borderRadius: '50%', margin: '0 auto 8px',
                background: b.earned ? `radial-gradient(circle at 30% 30%, ${b.color}, ${b.color}66)` : T.bg2,
                border: `1px solid ${b.earned ? b.color : T.border}`,
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                boxShadow: b.earned ? `0 0 18px ${b.color}88` : 'none',
                opacity: b.earned ? 1 : 0.6,
              }}>
                <Ic d={b.icon} size={20} color={b.earned ? '#06121C' : T.textMid} sw={2.2} />
              </div>
              <div style={{ fontFamily: T.display, fontWeight: 700, fontSize: 11, color: T.text }}>{b.name}</div>
              <div style={{ marginTop: 6, height: 3, borderRadius: 2, background: T.border, overflow: 'hidden' }}>
                <div style={{ width: `${b.prog}%`, height: '100%', background: b.color }} />
              </div>
            </Glass>
          ))}
        </div>
      </Screen>
      <TabBar active="home" />
    </NeonBg>
  );
}

window.HomeScreen = HomeScreen;
