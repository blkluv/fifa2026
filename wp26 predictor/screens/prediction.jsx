// prediction.jsx — Match prediction card screen (INTERACTIVE)
const { T, Crest, Glass, Chip, NeonBg, Ic, I, TabBar, Screen } = window;

function ScoreDial({ value, onChange, color }) {
  return (
    <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 6 }}>
      <button onClick={() => onChange(value + 1)} style={{
        width: 36, height: 28, borderRadius: 10,
        background: T.card, border: `1px solid ${T.border}`,
        color: T.textMid, fontSize: 16, fontWeight: 700, cursor: 'pointer',
        display: 'flex', alignItems: 'center', justifyContent: 'center',
      }}>+</button>
      <div style={{
        width: 96, height: 96, borderRadius: 24,
        background: `radial-gradient(circle at 30% 20%, ${color}22, ${T.bg2})`,
        border: `2px solid ${color}66`,
        boxShadow: `0 0 32px ${color}55, inset 0 0 24px ${color}22`,
        display: 'flex', alignItems: 'center', justifyContent: 'center',
        fontFamily: T.display, fontSize: 56, fontWeight: 800, color: '#fff',
        letterSpacing: '-0.06em', fontVariantNumeric: 'tabular-nums',
        textShadow: `0 0 18px ${color}88`,
      }}>{value}</div>
      <button onClick={() => onChange(Math.max(0, value - 1))} style={{
        width: 36, height: 28, borderRadius: 10,
        background: T.card, border: `1px solid ${T.border}`,
        color: T.textMid, fontSize: 16, fontWeight: 700, cursor: 'pointer',
        display: 'flex', alignItems: 'center', justifyContent: 'center',
      }}>–</button>
    </div>
  );
}

function PredictScreen({ initialState }) {
  const [home, setHome] = React.useState(2);
  const [away, setAway] = React.useState(1);
  const [joker, setJoker] = React.useState(false);
  const [state, setState] = React.useState(initialState || 'edit'); // edit | submitting | done | locked

  const submit = () => {
    setState('submitting');
    setTimeout(() => setState('done'), 900);
  };

  return (
    <NeonBg hue="violet">
      <Screen>
        <div style={{ display: 'flex', alignItems: 'center', gap: 8, padding: '6px 0 16px' }}>
          <button style={{ background: T.card, border: `1px solid ${T.border}`, borderRadius: 12, padding: 8, color: T.text, cursor: 'pointer' }}>
            <Ic d={I.chev} size={18} sw={2.2} color={T.text} style={{ transform: 'rotate(180deg)' }} />
          </button>
          <div style={{ flex: 1, fontFamily: T.display, fontSize: 16, fontWeight: 700, color: T.text }}>Round of 16</div>
          <Chip color={T.amber}>Match 49</Chip>
        </div>

        {/* Hero card */}
        <Glass br={24} p={20} glow={state === 'done' ? T.green : T.violet} style={{ marginBottom: 16, position: 'relative', overflow: 'hidden' }}>
          {/* halo */}
          <div style={{ position: 'absolute', inset: 0, background: `radial-gradient(circle at 50% -10%, ${T.cyan}22, transparent 60%)`, pointerEvents: 'none' }} />

          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', position: 'relative' }}>
            <div>
              <div style={{ fontFamily: T.body, fontSize: 10, fontWeight: 600, color: T.cyan, letterSpacing: '0.14em', textTransform: 'uppercase' }}>Group A · Matchday 3</div>
              <div style={{ fontFamily: T.display, fontSize: 13, fontWeight: 600, color: T.textMid, marginTop: 2 }}>Mercedes-Benz Stadium · ATL</div>
            </div>
            <div style={{ textAlign: 'right' }}>
              <div style={{ fontFamily: T.body, fontSize: 9, fontWeight: 600, color: T.textDim, letterSpacing: '0.14em', textTransform: 'uppercase' }}>Kickoff</div>
              <div style={{ fontFamily: T.display, fontSize: 13, fontWeight: 700, color: T.text, fontVariantNumeric: 'tabular-nums' }}>Sun · 17:00</div>
            </div>
          </div>

          {/* Teams + score */}
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginTop: 22, marginBottom: 22, position: 'relative' }}>
            <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 8, width: 70 }}>
              <Crest team="ARG" size={56} glow />
              <div style={{ fontFamily: T.display, fontSize: 13, fontWeight: 700, color: T.text }}>ARG</div>
              <div style={{ fontFamily: T.body, fontSize: 9, fontWeight: 600, color: T.textDim, letterSpacing: '0.1em', textTransform: 'uppercase' }}>1.62 odds</div>
            </div>
            <ScoreDial value={home} onChange={setHome} color={TEAMS.ARG.c1} />
            <div style={{ fontFamily: T.display, fontSize: 32, fontWeight: 800, color: T.textFaint, letterSpacing: '-0.05em' }}>–</div>
            <ScoreDial value={away} onChange={setAway} color={TEAMS.NED.c1} />
            <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 8, width: 70 }}>
              <Crest team="NED" size={56} glow />
              <div style={{ fontFamily: T.display, fontSize: 13, fontWeight: 700, color: T.text }}>NED</div>
              <div style={{ fontFamily: T.body, fontSize: 9, fontWeight: 600, color: T.textDim, letterSpacing: '0.1em', textTransform: 'uppercase' }}>2.40 odds</div>
            </div>
          </div>

          {/* Outcome bar */}
          <div style={{ display: 'flex', gap: 6, padding: 4, background: T.bg3, borderRadius: 12, border: `1px solid ${T.border}` }}>
            {[
              { label: 'ARG win', active: home > away, c: TEAMS.ARG.c1 },
              { label: 'Draw', active: home === away, c: T.textMid },
              { label: 'NED win', active: away > home, c: TEAMS.NED.c1 },
            ].map((o, i) => (
              <div key={i} style={{
                flex: 1, textAlign: 'center', padding: '8px 4px',
                borderRadius: 8,
                background: o.active ? `linear-gradient(135deg, ${o.c}33, ${o.c}11)` : 'transparent',
                border: o.active ? `1px solid ${o.c}66` : '1px solid transparent',
                fontFamily: T.body, fontSize: 11, fontWeight: 700,
                color: o.active ? '#fff' : T.textDim,
                transition: 'all .2s',
              }}>{o.label}</div>
            ))}
          </div>

          {/* Joker toggle */}
          <div style={{
            marginTop: 14, padding: '12px 14px',
            background: joker ? `linear-gradient(135deg, ${T.amber}22, ${T.amber}08)` : T.bg3,
            border: `1px solid ${joker ? T.amber + '88' : T.border}`,
            borderRadius: 14,
            display: 'flex', alignItems: 'center', gap: 12,
            transition: 'all .2s',
            boxShadow: joker ? `0 0 24px ${T.amber}44` : 'none',
          }} onClick={() => setJoker(!joker)}>
            <div style={{
              width: 36, height: 36, borderRadius: 10,
              background: joker ? T.amber : T.cardSolid,
              display: 'flex', alignItems: 'center', justifyContent: 'center',
              boxShadow: joker ? `0 0 18px ${T.amber}88` : 'none',
            }}>
              <Ic d={I.bolt} size={20} color={joker ? '#06121C' : T.amber} sw={2.2} />
            </div>
            <div style={{ flex: 1 }}>
              <div style={{ fontFamily: T.display, fontSize: 13, fontWeight: 700, color: T.text }}>Joker · 2× points</div>
              <div style={{ fontFamily: T.body, fontSize: 11, color: T.textDim, marginTop: 1 }}>{joker ? 'Activated — last use of the tournament' : '1 remaining · use wisely'}</div>
            </div>
            <div style={{
              width: 40, height: 24, borderRadius: 999,
              background: joker ? T.amber : T.border,
              position: 'relative', transition: 'background .2s',
            }}>
              <div style={{
                position: 'absolute', top: 2, left: joker ? 18 : 2,
                width: 20, height: 20, borderRadius: '50%',
                background: '#fff', transition: 'left .2s',
                boxShadow: '0 2px 6px rgba(0,0,0,0.3)',
              }} />
            </div>
          </div>
        </Glass>

        {/* Submit / state */}
        {state === 'edit' && (
          <button onClick={submit} style={{
            width: '100%', padding: 16, borderRadius: 18, border: 'none', cursor: 'pointer',
            background: `linear-gradient(135deg, ${T.cyan}, ${T.violet})`,
            fontFamily: T.display, fontSize: 15, fontWeight: 800, color: '#06121C',
            letterSpacing: '-0.01em',
            boxShadow: `0 12px 30px ${T.cyan}55, 0 0 40px ${T.violet}44, inset 0 -2px 0 rgba(0,0,0,0.15)`,
          }}>
            Lock in {home}–{away} {joker && '· 2×'} →
          </button>
        )}
        {state === 'submitting' && (
          <Glass p={16} br={18} style={{ textAlign: 'center' }}>
            <div style={{ fontFamily: T.display, fontWeight: 700, fontSize: 14, color: T.cyan }}>Submitting prediction…</div>
          </Glass>
        )}
        {state === 'done' && (
          <Glass br={18} p={16} glow={T.green} style={{ background: `linear-gradient(135deg, ${T.green}1F, ${T.green}0A)`, borderColor: T.green + '55' }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
              <div style={{
                width: 42, height: 42, borderRadius: '50%', background: T.green,
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                boxShadow: `0 0 24px ${T.green}88`,
              }}><Ic d={I.check} size={22} color="#06121C" sw={3} /></div>
              <div style={{ flex: 1 }}>
                <div style={{ fontFamily: T.display, fontWeight: 700, fontSize: 14, color: T.text }}>Prediction locked</div>
                <div style={{ fontFamily: T.body, fontSize: 11, color: T.textMid }}>{home}–{away} · {joker ? 'Joker active (2×)' : 'Standard'}</div>
              </div>
              <button onClick={() => setState('edit')} style={{ background: 'transparent', border: `1px solid ${T.border}`, color: T.text, borderRadius: 10, padding: '6px 12px', fontFamily: T.body, fontWeight: 600, fontSize: 11, cursor: 'pointer' }}>Edit</button>
            </div>
          </Glass>
        )}
        {state === 'locked' && (
          <Glass br={18} p={16} style={{ background: T.bg3, borderColor: T.border }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
              <div style={{
                width: 42, height: 42, borderRadius: '50%', background: T.bg2,
                border: `1px solid ${T.border}`,
                display: 'flex', alignItems: 'center', justifyContent: 'center',
              }}><Ic d={I.lock} size={20} color={T.amber} sw={2.2} /></div>
              <div style={{ flex: 1 }}>
                <div style={{ fontFamily: T.display, fontWeight: 700, fontSize: 14, color: T.text }}>Predictions locked</div>
                <div style={{ fontFamily: T.body, fontSize: 11, color: T.textMid }}>Kickoff has started · Final: {home}–{away}</div>
              </div>
            </div>
          </Glass>
        )}

        {/* Scoring rules */}
        <div style={{ marginTop: 18, padding: 14, borderRadius: 14, background: T.bg3, border: `1px solid ${T.border}` }}>
          <div style={{ fontFamily: T.body, fontSize: 10, fontWeight: 600, color: T.textDim, letterSpacing: '0.14em', textTransform: 'uppercase', marginBottom: 10 }}>Scoring rules</div>
          {[
            { label: 'Exact score', pts: 8, c: T.cyan },
            { label: 'Goal diff correct', pts: 4, c: T.violet },
            { label: 'Winner correct', pts: 2, c: T.green },
            { label: 'Wrong outcome', pts: 0, c: T.red },
          ].map((r, i) => (
            <div key={i} style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '6px 0', borderTop: i ? `1px solid ${T.border}` : 'none' }}>
              <div style={{ width: 6, height: 6, borderRadius: 999, background: r.c, boxShadow: `0 0 8px ${r.c}` }} />
              <div style={{ flex: 1, fontFamily: T.body, fontSize: 12, color: T.text }}>{r.label}</div>
              <div style={{ fontFamily: T.display, fontWeight: 700, fontSize: 14, color: r.c }}>{r.pts > 0 ? '+' : ''}{r.pts}</div>
            </div>
          ))}
        </div>
      </Screen>
      <TabBar active="predict" />
    </NeonBg>
  );
}

window.PredictScreen = PredictScreen;
