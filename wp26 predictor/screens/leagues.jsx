// leagues.jsx — Mini league page
const { T, Crest, Glass, Chip, H, NeonBg, Ic, I, TabBar, Screen } = window;

function LeaguesScreen() {
  return (
    <NeonBg>
      <Screen>
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '6px 0 16px' }}>
          <div>
            <div style={{ fontFamily: T.body, fontSize: 10, fontWeight: 600, color: T.cyan, letterSpacing: '0.16em', textTransform: 'uppercase' }}>Private league</div>
            <div style={{ fontFamily: T.display, fontSize: 22, fontWeight: 800, color: T.text, letterSpacing: '-0.03em', marginTop: 2 }}>El Grupo</div>
          </div>
          <Glass p={9} br={12}><Ic d={I.set} size={18} color={T.text} /></Glass>
        </div>

        {/* Banner */}
        <Glass br={22} p={0} style={{ marginBottom: 16, overflow: 'hidden', position: 'relative' }}>
          <div style={{
            height: 110, position: 'relative',
            background: `linear-gradient(135deg, ${T.violet}, ${T.cyan})`,
          }}>
            {/* texture */}
            <div style={{
              position: 'absolute', inset: 0,
              backgroundImage: 'repeating-linear-gradient(45deg, rgba(255,255,255,0.06) 0 2px, transparent 2px 24px)',
            }} />
            <div style={{ position: 'absolute', right: -20, bottom: -20, width: 140, height: 140, borderRadius: '50%', background: `radial-gradient(circle, ${T.amber}66, transparent 60%)` }} />
            <div style={{ position: 'absolute', left: 18, bottom: 14, color: '#fff' }}>
              <div style={{ fontFamily: T.body, fontSize: 10, fontWeight: 700, letterSpacing: '0.16em', textTransform: 'uppercase', opacity: 0.85 }}>Season XI · invite-only</div>
              <div style={{ fontFamily: T.display, fontSize: 24, fontWeight: 800, letterSpacing: '-0.03em' }}>El Grupo</div>
            </div>
          </div>
          <div style={{ display: 'flex', padding: 14, gap: 12 }}>
            <div style={{ flex: 1 }}>
              <div style={{ fontFamily: T.body, fontSize: 10, fontWeight: 600, color: T.textDim, letterSpacing: '0.12em', textTransform: 'uppercase' }}>Members</div>
              <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 18, color: T.text, marginTop: 2 }}>14</div>
            </div>
            <div style={{ flex: 1 }}>
              <div style={{ fontFamily: T.body, fontSize: 10, fontWeight: 600, color: T.textDim, letterSpacing: '0.12em', textTransform: 'uppercase' }}>Prize pool</div>
              <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 18, color: T.text, marginTop: 2 }}>700 pts</div>
            </div>
            <div style={{ flex: 1 }}>
              <div style={{ fontFamily: T.body, fontSize: 10, fontWeight: 600, color: T.textDim, letterSpacing: '0.12em', textTransform: 'uppercase' }}>Your rank</div>
              <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 18, color: T.cyan, marginTop: 2 }}>#2</div>
            </div>
          </div>
        </Glass>

        {/* Invite code */}
        <Glass br={16} p={14} style={{ marginBottom: 18, display: 'flex', alignItems: 'center', gap: 12 }}>
          <div style={{
            width: 44, height: 44, borderRadius: 12,
            background: `linear-gradient(135deg, ${T.cyan}33, ${T.violet}33)`,
            border: `1px solid ${T.cyan}66`,
            display: 'flex', alignItems: 'center', justifyContent: 'center',
          }}><Ic d={I.share} size={20} color={T.cyan} sw={2} /></div>
          <div style={{ flex: 1 }}>
            <div style={{ fontFamily: T.body, fontSize: 10, fontWeight: 600, color: T.textDim, letterSpacing: '0.12em', textTransform: 'uppercase' }}>Invite code</div>
            <div style={{ fontFamily: T.mono, fontWeight: 800, fontSize: 18, color: T.text, letterSpacing: '0.2em', marginTop: 2 }}>WP26-GRUP</div>
          </div>
          <button style={{
            background: `linear-gradient(135deg, ${T.cyan}, ${T.violet})`,
            border: 'none', borderRadius: 12, padding: '10px 14px',
            fontFamily: T.display, fontSize: 12, fontWeight: 700, color: '#06121C', cursor: 'pointer',
            boxShadow: `0 0 18px ${T.cyan}55`,
          }}>Share →</button>
        </Glass>

        {/* Standings */}
        <H kicker="Round 2 settled" action="Full table">Standings</H>
        <Glass br={18} p={4} style={{ marginBottom: 22 }}>
          {[
            { rank: 1, name: 'Sofía R.', pts: 1402, mv: 0, country: 'ARG', idx: 0, trophy: true },
            { rank: 2, name: 'Diego M.', pts: 1284, mv: 1, country: 'MEX', idx: 1, you: true },
            { rank: 3, name: 'Marco D.', pts: 1276, mv: -1, country: 'ITA', idx: 2 },
            { rank: 4, name: 'Lina K.', pts: 1198, mv: 2, country: 'GER', idx: 3 },
            { rank: 5, name: 'Joel A.', pts: 1184, mv: 0, country: 'SEN', idx: 4 },
            { rank: 6, name: 'Yuki S.', pts: 1142, mv: -2, country: 'JPN', idx: 5 },
            { rank: 7, name: 'Olivia W.', pts: 1098, mv: 3, country: 'ENG', idx: 6 },
            { rank: 8, name: 'Paulo F.', pts: 1043, mv: 0, country: 'POR', idx: 7 },
          ].map((r, i) => (
            <div key={i} style={{
              display: 'flex', alignItems: 'center', gap: 10,
              padding: '10px 12px',
              borderTop: i ? `1px solid ${T.border}` : 'none',
              background: r.you ? `linear-gradient(90deg, ${T.cyan}1A, ${T.violet}10)` : 'transparent',
            }}>
              <div style={{
                width: 24, textAlign: 'center',
                fontFamily: T.display, fontWeight: 800, fontSize: 13,
                color: r.trophy ? T.amber : r.you ? T.cyan : T.textMid,
              }}>{r.rank}</div>
              <div style={{ position: 'relative' }}>
                <div style={{
                  width: 32, height: 32, borderRadius: '50%',
                  background: `linear-gradient(135deg, hsl(${r.idx*60} 65% 55%), hsl(${r.idx*60+30} 65% 35%))`,
                  display: 'flex', alignItems: 'center', justifyContent: 'center',
                  fontFamily: T.display, fontWeight: 700, color: '#fff', fontSize: 13,
                }}>{r.name[0]}</div>
                <div style={{ position: 'absolute', bottom: -3, right: -3, background: T.bg, borderRadius: '50%', padding: 1 }}>
                  <Crest team={r.country} size={14} ring={false} />
                </div>
              </div>
              <div style={{ flex: 1, fontFamily: T.display, fontWeight: r.you ? 800 : 600, fontSize: 13, color: T.text }}>
                {r.name}{r.you && <span style={{ color: T.cyan, fontWeight: 700 }}> · You</span>}
              </div>
              {r.mv !== 0 ? (
                <span style={{ fontFamily: T.body, fontSize: 10, fontWeight: 700, color: r.mv > 0 ? T.green : T.red, minWidth: 22, textAlign: 'right' }}>
                  {r.mv > 0 ? '▲' : '▼'}{Math.abs(r.mv)}
                </span>
              ) : <span style={{ minWidth: 22, textAlign: 'right', color: T.textFaint, fontSize: 10 }}>—</span>}
              <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 14, color: T.text, fontVariantNumeric: 'tabular-nums', minWidth: 48, textAlign: 'right' }}>{r.pts.toLocaleString()}</div>
            </div>
          ))}
        </Glass>

        {/* Discover */}
        <H action="Explore">Discover leagues</H>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
          {[
            { name: 'Office Pool 2026', mem: 38, c1: T.cyan, c2: T.violet, hot: true },
            { name: 'Latin Pundits', mem: 142, c1: T.amber, c2: T.red, hot: false },
            { name: 'Engineering Bracket', mem: 56, c1: T.green, c2: T.cyan, hot: true },
          ].map((l, i) => (
            <Glass key={i} br={14} p={12}>
              <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                <div style={{
                  width: 44, height: 44, borderRadius: 12,
                  background: `linear-gradient(135deg, ${l.c1}, ${l.c2})`,
                  boxShadow: `0 0 16px ${l.c1}44`,
                  display: 'flex', alignItems: 'center', justifyContent: 'center',
                  fontFamily: T.display, fontWeight: 800, color: '#06121C', fontSize: 18,
                }}>{l.name[0]}</div>
                <div style={{ flex: 1 }}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                    <span style={{ fontFamily: T.display, fontWeight: 700, fontSize: 13, color: T.text }}>{l.name}</span>
                    {l.hot && <Chip color={T.amber}>🔥 hot</Chip>}
                  </div>
                  <div style={{ fontFamily: T.body, fontSize: 11, color: T.textDim, marginTop: 2 }}>{l.mem} members · Public</div>
                </div>
                <button style={{
                  background: T.card, border: `1px solid ${T.borderHi}`,
                  borderRadius: 10, padding: '8px 14px',
                  fontFamily: T.display, fontSize: 11, fontWeight: 700, color: T.text, cursor: 'pointer',
                }}>Join</button>
              </div>
            </Glass>
          ))}
        </div>
      </Screen>
      <TabBar active="rank" />
    </NeonBg>
  );
}

window.LeaguesScreen = LeaguesScreen;
