// states.jsx — Lock, empty, loading, error variants + design system token card
const { T, Crest, Glass, Chip, H, NeonBg, Ic, I, TabBar, Screen } = window;

function StatesScreen() {
  return (
    <NeonBg>
      <Screen>
        <div style={{ padding: '6px 0 16px' }}>
          <div style={{ fontFamily: T.body, fontSize: 10, fontWeight: 600, color: T.cyan, letterSpacing: '0.16em', textTransform: 'uppercase' }}>System library</div>
          <div style={{ fontFamily: T.display, fontSize: 22, fontWeight: 800, color: T.text, letterSpacing: '-0.03em', marginTop: 2 }}>States</div>
        </div>

        {/* Locked state — large */}
        <div style={{ fontFamily: T.body, fontSize: 10, fontWeight: 700, color: T.textDim, letterSpacing: '0.14em', textTransform: 'uppercase', marginBottom: 8 }}>Prediction locked</div>
        <Glass br={20} p={20} style={{ marginBottom: 18, background: `linear-gradient(135deg, ${T.amber}14, transparent)`, textAlign: 'center', position: 'relative', overflow: 'hidden' }}>
          <div style={{ position: 'absolute', inset: 0, background: `radial-gradient(circle at 50% 0%, ${T.amber}33, transparent 60%)`, pointerEvents: 'none' }} />
          <div style={{ position: 'relative' }}>
            <div style={{
              width: 64, height: 64, borderRadius: '50%', margin: '6px auto 12px',
              background: `radial-gradient(circle at 30% 30%, ${T.amber}, ${T.amber}55)`,
              border: `1px solid ${T.amber}`,
              display: 'flex', alignItems: 'center', justifyContent: 'center',
              boxShadow: `0 0 32px ${T.amber}88`,
            }}><Ic d={I.lock} size={28} color="#06121C" sw={2.2} /></div>
            <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 16, color: T.text }}>Predictions locked</div>
            <div style={{ fontFamily: T.body, fontSize: 12, color: T.textMid, marginTop: 4, maxWidth: 240, marginLeft: 'auto', marginRight: 'auto' }}>Kickoff started 4 min ago. Your final pick: <span style={{ color: T.text, fontWeight: 700 }}>ARG 2–1 NED</span></div>
            <div style={{ display: 'flex', gap: 14, alignItems: 'center', justifyContent: 'center', marginTop: 14 }}>
              <Crest team="ARG" size={36} />
              <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 24, color: T.text, fontVariantNumeric: 'tabular-nums' }}>2–1</div>
              <Crest team="NED" size={36} />
            </div>
          </div>
        </Glass>

        {/* Empty */}
        <div style={{ fontFamily: T.body, fontSize: 10, fontWeight: 700, color: T.textDim, letterSpacing: '0.14em', textTransform: 'uppercase', marginBottom: 8 }}>Empty</div>
        <Glass br={20} p={24} style={{ marginBottom: 18, textAlign: 'center' }}>
          <div style={{
            width: 56, height: 56, borderRadius: 16, margin: '0 auto 12px',
            background: T.bg3, border: `1px dashed ${T.borderHi}`,
            display: 'flex', alignItems: 'center', justifyContent: 'center',
          }}><Ic d={I.target} size={24} color={T.textMid} sw={1.6} /></div>
          <div style={{ fontFamily: T.display, fontWeight: 700, fontSize: 15, color: T.text }}>No predictions yet</div>
          <div style={{ fontFamily: T.body, fontSize: 12, color: T.textMid, marginTop: 4, lineHeight: 1.4 }}>Round 1 opens in 2 days. We'll buzz you when it's time to lock in.</div>
          <button style={{
            marginTop: 14, padding: '10px 16px', borderRadius: 12, border: 'none', cursor: 'pointer',
            background: `linear-gradient(135deg, ${T.cyan}, ${T.violet})`,
            fontFamily: T.display, fontSize: 12, fontWeight: 800, color: '#06121C',
            boxShadow: `0 0 18px ${T.cyan}44`,
          }}>Browse fixtures →</button>
        </Glass>

        {/* Loading skeleton */}
        <div style={{ fontFamily: T.body, fontSize: 10, fontWeight: 700, color: T.textDim, letterSpacing: '0.14em', textTransform: 'uppercase', marginBottom: 8 }}>Loading skeleton</div>
        <Glass br={16} p={14} style={{ marginBottom: 10 }}>
          {[0, 1, 2].map((i) => (
            <div key={i} style={{ display: 'flex', alignItems: 'center', gap: 12, padding: '8px 0', borderTop: i ? `1px solid ${T.border}` : 'none' }}>
              <div style={{ width: 32, height: 32, borderRadius: '50%', background: T.bg3, position: 'relative', overflow: 'hidden' }}>
                <div style={{ position: 'absolute', inset: 0, background: `linear-gradient(90deg, transparent, ${T.borderHi}, transparent)`, animation: 'shimmer 1.6s infinite' }} />
              </div>
              <div style={{ flex: 1 }}>
                <div style={{ height: 10, width: '60%', borderRadius: 4, background: T.bg3, marginBottom: 6, position: 'relative', overflow: 'hidden' }}>
                  <div style={{ position: 'absolute', inset: 0, background: `linear-gradient(90deg, transparent, ${T.borderHi}, transparent)`, animation: 'shimmer 1.6s infinite' }} />
                </div>
                <div style={{ height: 8, width: '38%', borderRadius: 4, background: T.bg3, position: 'relative', overflow: 'hidden' }}>
                  <div style={{ position: 'absolute', inset: 0, background: `linear-gradient(90deg, transparent, ${T.borderHi}, transparent)`, animation: 'shimmer 1.6s infinite' }} />
                </div>
              </div>
              <div style={{ width: 40, height: 14, borderRadius: 4, background: T.bg3, position: 'relative', overflow: 'hidden' }}>
                <div style={{ position: 'absolute', inset: 0, background: `linear-gradient(90deg, transparent, ${T.borderHi}, transparent)`, animation: 'shimmer 1.6s infinite' }} />
              </div>
            </div>
          ))}
        </Glass>

        {/* Error */}
        <div style={{ fontFamily: T.body, fontSize: 10, fontWeight: 700, color: T.textDim, letterSpacing: '0.14em', textTransform: 'uppercase', marginBottom: 8, marginTop: 14 }}>Error</div>
        <Glass br={16} p={16} style={{ borderColor: T.red + '66', background: `linear-gradient(135deg, ${T.red}14, transparent)`, marginBottom: 18 }}>
          <div style={{ display: 'flex', gap: 12 }}>
            <div style={{
              width: 36, height: 36, borderRadius: 12, flexShrink: 0,
              background: `${T.red}22`, border: `1px solid ${T.red}55`,
              display: 'flex', alignItems: 'center', justifyContent: 'center',
            }}><Ic d={I.x} size={18} color={T.red} sw={2.4} /></div>
            <div style={{ flex: 1 }}>
              <div style={{ fontFamily: T.display, fontWeight: 700, fontSize: 14, color: T.text }}>We couldn't reach the scoring service</div>
              <div style={{ fontFamily: T.body, fontSize: 11, color: T.textMid, marginTop: 2, lineHeight: 1.5 }}>Your prediction is saved locally. We'll sync it as soon as the network's back.</div>
              <div style={{ display: 'flex', gap: 8, marginTop: 10 }}>
                <button style={{ background: T.red, color: '#06121C', border: 'none', borderRadius: 10, padding: '7px 12px', fontFamily: T.display, fontSize: 11, fontWeight: 800, cursor: 'pointer' }}>Retry now</button>
                <button style={{ background: 'transparent', border: `1px solid ${T.border}`, color: T.text, borderRadius: 10, padding: '7px 12px', fontFamily: T.body, fontSize: 11, fontWeight: 600, cursor: 'pointer' }}>Dismiss</button>
              </div>
            </div>
          </div>
        </Glass>

        {/* Outcome reveal — 4 result colors */}
        <div style={{ fontFamily: T.body, fontSize: 10, fontWeight: 700, color: T.textDim, letterSpacing: '0.14em', textTransform: 'uppercase', marginBottom: 8 }}>Prediction outcomes</div>
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 10, marginBottom: 18 }}>
          {[
            { l: 'Exact score', pts: '+8', c: T.cyan, icon: I.target },
            { l: 'Goal diff', pts: '+4', c: T.violet, icon: I.bolt },
            { l: 'Winner', pts: '+2', c: T.green, icon: I.check },
            { l: 'Wrong', pts: '0', c: T.red, icon: I.x },
          ].map((o, i) => (
            <Glass key={i} br={14} p={12} glow={o.c} style={{ background: `linear-gradient(135deg, ${o.c}1A, transparent)`, borderColor: `${o.c}55` }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                <div style={{
                  width: 32, height: 32, borderRadius: 10,
                  background: o.c, display: 'flex', alignItems: 'center', justifyContent: 'center',
                  boxShadow: `0 0 16px ${o.c}88`,
                }}><Ic d={o.icon} size={16} color="#06121C" sw={2.4} /></div>
                <div style={{ flex: 1 }}>
                  <div style={{ fontFamily: T.body, fontSize: 10, color: T.textDim, letterSpacing: '0.1em', textTransform: 'uppercase', fontWeight: 700 }}>{o.l}</div>
                  <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 18, color: o.c, letterSpacing: '-0.02em' }}>{o.pts}</div>
                </div>
              </div>
            </Glass>
          ))}
        </div>
      </Screen>
      <style>{`@keyframes shimmer { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }`}</style>
    </NeonBg>
  );
}

// Design system tokens
function TokensScreen() {
  const swatches = [
    { l: 'BG / 0B1020', c: T.bg },
    { l: 'BG2 / 131A2E', c: T.bg2 },
    { l: 'Cyan / 00E5FF', c: T.cyan },
    { l: 'Violet / 7C4DFF', c: T.violet },
    { l: 'Success / 00C853', c: T.green },
    { l: 'Danger / FF5252', c: T.red },
    { l: 'Warning / FFC107', c: T.amber },
    { l: 'Text / #FFF', c: T.text },
  ];
  return (
    <NeonBg>
      <Screen pad={18} bottom={24}>
        <div style={{ padding: '6px 0 14px' }}>
          <div style={{ fontFamily: T.body, fontSize: 10, fontWeight: 600, color: T.cyan, letterSpacing: '0.16em', textTransform: 'uppercase' }}>Design system</div>
          <div style={{ fontFamily: T.display, fontSize: 22, fontWeight: 800, color: T.text, letterSpacing: '-0.03em', marginTop: 2 }}>Tokens & atoms</div>
        </div>

        {/* Colors */}
        <H>Colors</H>
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 8, marginBottom: 18 }}>
          {swatches.map((s, i) => (
            <div key={i} style={{
              display: 'flex', alignItems: 'center', gap: 10,
              padding: 10, borderRadius: 12,
              background: T.card, border: `1px solid ${T.border}`,
            }}>
              <div style={{
                width: 28, height: 28, borderRadius: 8,
                background: s.c, boxShadow: `0 0 16px ${s.c}55, inset 0 0 0 1px rgba(255,255,255,0.1)`,
                flexShrink: 0,
              }} />
              <div style={{ fontFamily: T.mono, fontSize: 10, color: T.textMid }}>{s.l}</div>
            </div>
          ))}
        </div>

        {/* Type scale */}
        <H>Type</H>
        <Glass br={14} p={14} style={{ marginBottom: 18 }}>
          <div style={{ fontFamily: T.display, fontSize: 32, fontWeight: 800, color: T.text, letterSpacing: '-0.04em', lineHeight: 1 }}>Display · 32/800</div>
          <div style={{ fontFamily: T.body, fontSize: 10, color: T.textDim, marginTop: 2 }}>Sora · used for scores, ranks, big numbers</div>
          <div style={{ height: 1, background: T.border, margin: '12px 0' }} />
          <div style={{ fontFamily: T.display, fontSize: 18, fontWeight: 700, color: T.text, letterSpacing: '-0.02em' }}>Heading · 18/700</div>
          <div style={{ fontFamily: T.body, fontSize: 14, fontWeight: 500, color: T.text, marginTop: 6 }}>Body · 14/500 — Inter, the workhorse for paragraphs and labels.</div>
          <div style={{ fontFamily: T.body, fontSize: 10, fontWeight: 700, color: T.textDim, letterSpacing: '0.14em', textTransform: 'uppercase', marginTop: 10 }}>Kicker · 10/700 tracked</div>
          <div style={{ fontFamily: T.mono, fontSize: 11, color: T.cyan, marginTop: 6 }}>WP26-GRUP · 11/mono</div>
        </Glass>

        {/* Buttons */}
        <H>Buttons</H>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 10, marginBottom: 18 }}>
          <button style={{
            padding: 12, borderRadius: 14, border: 'none', cursor: 'pointer',
            background: `linear-gradient(135deg, ${T.cyan}, ${T.violet})`,
            fontFamily: T.display, fontSize: 13, fontWeight: 800, color: '#06121C',
            boxShadow: `0 8px 20px ${T.cyan}55, inset 0 -2px 0 rgba(0,0,0,0.15)`,
          }}>Primary · gradient</button>
          <button style={{
            padding: 12, borderRadius: 14, cursor: 'pointer',
            background: T.card, border: `1px solid ${T.borderHi}`,
            fontFamily: T.display, fontSize: 13, fontWeight: 700, color: T.text,
            backdropFilter: 'blur(20px)',
          }}>Secondary · glass</button>
          <button style={{
            padding: 12, borderRadius: 14, cursor: 'pointer',
            background: 'transparent', border: `1px solid ${T.cyan}66`,
            fontFamily: T.display, fontSize: 13, fontWeight: 700, color: T.cyan,
          }}>Tertiary · outline</button>
          <button style={{
            padding: 12, borderRadius: 14, cursor: 'pointer',
            background: T.red, border: 'none',
            fontFamily: T.display, fontSize: 13, fontWeight: 800, color: '#06121C',
            boxShadow: `0 0 18px ${T.red}55`,
          }}>Destructive</button>
        </div>

        {/* Chips */}
        <H>Chips</H>
        <div style={{ display: 'flex', flexWrap: 'wrap', gap: 6, marginBottom: 18 }}>
          <Chip color={T.cyan}>● Live</Chip>
          <Chip color={T.amber}><Ic d={I.lock} size={10} sw={2.4} /> Locked</Chip>
          <Chip color={T.violet}>Group A</Chip>
          <Chip color={T.green}>Joker × 2</Chip>
          <Chip color={T.cyan} solid>Predict</Chip>
          <Chip color={T.red}>● Final</Chip>
        </div>

        {/* Crests */}
        <H>Team crests</H>
        <div style={{ display: 'flex', flexWrap: 'wrap', gap: 8, marginBottom: 4 }}>
          {['ARG', 'BRA', 'FRA', 'ENG', 'ESP', 'GER', 'POR', 'NED', 'ITA', 'BEL', 'MEX', 'USA'].map((c) => (
            <Crest key={c} team={c} size={36} />
          ))}
        </div>
      </Screen>
    </NeonBg>
  );
}

window.StatesScreen = StatesScreen;
window.TokensScreen = TokensScreen;
