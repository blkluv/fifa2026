// profile.jsx — User profile + badge collection
const { T, Crest, Glass, Chip, H, NeonBg, Ic, I, TabBar, Screen, TEAMS } = window;

function Badge({ name, desc, icon, color, earned, prog, rare }) {
  return (
    <Glass p={14} br={16} glow={earned ? color : undefined} style={{ textAlign: 'center', position: 'relative', overflow: 'hidden' }}>
      {rare && earned && (
        <div style={{ position: 'absolute', top: 8, right: 8, fontFamily: T.body, fontSize: 8, fontWeight: 800, color: T.amber, letterSpacing: '0.14em', textTransform: 'uppercase' }}>★ Rare</div>
      )}
      <div style={{
        width: 56, height: 56, borderRadius: '50%', margin: '4px auto 10px',
        background: earned
          ? `conic-gradient(from 0deg, ${color}, ${T.violet}, ${color})`
          : T.bg2,
        border: `1px solid ${earned ? color : T.border}`,
        display: 'flex', alignItems: 'center', justifyContent: 'center',
        boxShadow: earned ? `0 0 24px ${color}88, inset 0 0 12px rgba(255,255,255,0.2)` : 'none',
        position: 'relative',
        opacity: earned ? 1 : 0.55,
      }}>
        <div style={{
          width: 46, height: 46, borderRadius: '50%',
          background: earned ? `radial-gradient(circle at 30% 30%, ${color}, ${color}55)` : T.bg3,
          display: 'flex', alignItems: 'center', justifyContent: 'center',
        }}>
          <Ic d={icon} size={22} color={earned ? '#06121C' : T.textMid} sw={2.2} />
        </div>
      </div>
      <div style={{ fontFamily: T.display, fontWeight: 700, fontSize: 12, color: T.text, letterSpacing: '-0.01em' }}>{name}</div>
      <div style={{ fontFamily: T.body, fontSize: 10, color: T.textDim, marginTop: 2, lineHeight: 1.3, minHeight: 26 }}>{desc}</div>
      {!earned && (
        <div style={{ marginTop: 6 }}>
          <div style={{ height: 4, borderRadius: 2, background: T.border, overflow: 'hidden' }}>
            <div style={{ height: '100%', width: `${prog}%`, background: color }} />
          </div>
          <div style={{ fontFamily: T.body, fontSize: 9, color: T.textDim, marginTop: 4, fontWeight: 600 }}>{prog}%</div>
        </div>
      )}
    </Glass>
  );
}

function ProfileScreen() {
  return (
    <NeonBg hue="violet">
      <Screen>
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '6px 0 16px' }}>
          <div style={{ fontFamily: T.display, fontSize: 18, fontWeight: 700, color: T.text }}>Profile</div>
          <div style={{ display: 'flex', gap: 8 }}>
            <Glass p={9} br={12}><Ic d={I.share} size={18} color={T.text} /></Glass>
            <Glass p={9} br={12}><Ic d={I.set} size={18} color={T.text} /></Glass>
          </div>
        </div>

        {/* Profile hero */}
        <Glass br={22} p={18} style={{ marginBottom: 18, textAlign: 'center', position: 'relative', overflow: 'hidden' }}>
          <div style={{ position: 'absolute', inset: 0, background: `radial-gradient(circle at 50% -30%, ${T.violet}55, transparent 60%)`, pointerEvents: 'none' }} />
          <div style={{ position: 'relative' }}>
            {/* Avatar */}
            <div style={{ position: 'relative', display: 'inline-block', marginBottom: 12 }}>
              <div style={{
                width: 88, height: 88, borderRadius: '50%',
                background: `conic-gradient(from 0deg, ${T.cyan}, ${T.violet}, ${T.amber}, ${T.cyan})`,
                padding: 3,
                boxShadow: `0 0 32px ${T.cyan}55`,
              }}>
                <div style={{
                  width: '100%', height: '100%', borderRadius: '50%',
                  background: `linear-gradient(135deg, hsl(15 70% 55%), hsl(285 70% 35%))`,
                  display: 'flex', alignItems: 'center', justifyContent: 'center',
                  fontFamily: T.display, fontWeight: 800, fontSize: 36, color: '#fff',
                }}>D</div>
              </div>
              <div style={{ position: 'absolute', bottom: -4, right: -4, background: T.bg, borderRadius: '50%', padding: 2, border: `2px solid ${T.bg}` }}>
                <Crest team="MEX" size={26} />
              </div>
            </div>
            <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 22, color: T.text, letterSpacing: '-0.02em' }}>Diego Mendez</div>
            <div style={{ fontFamily: T.body, fontSize: 12, color: T.textMid, marginTop: 2 }}>@diegopredicts · joined Round 0</div>

            <div style={{ display: 'flex', gap: 6, justifyContent: 'center', marginTop: 10 }}>
              <Chip color={T.amber}>Tier · Gold II</Chip>
              <Chip color={T.cyan}>247 worldwide</Chip>
            </div>

            <div style={{ display: 'flex', marginTop: 18, paddingTop: 16, borderTop: `1px solid ${T.border}` }}>
              {[
                { l: 'Points', v: '1,284' },
                { l: 'Exact', v: '9' },
                { l: 'Streak', v: '4 🔥' },
                { l: 'Joker', v: '1' },
              ].map((s, i) => (
                <div key={i} style={{ flex: 1, textAlign: 'center', borderRight: i < 3 ? `1px solid ${T.border}` : 'none' }}>
                  <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 18, color: T.text, letterSpacing: '-0.02em' }}>{s.v}</div>
                  <div style={{ fontFamily: T.body, fontSize: 9, color: T.textDim, letterSpacing: '0.12em', textTransform: 'uppercase', marginTop: 2 }}>{s.l}</div>
                </div>
              ))}
            </div>
          </div>
        </Glass>

        {/* Accuracy breakdown */}
        <H kicker="24 predictions made">Accuracy breakdown</H>
        <Glass br={18} p={16} style={{ marginBottom: 22 }}>
          {[
            { label: 'Exact score', count: 9, max: 24, color: T.cyan, pts: '+72' },
            { label: 'Goal diff correct', count: 7, max: 24, color: T.violet, pts: '+28' },
            { label: 'Winner correct', count: 4, max: 24, color: T.green, pts: '+8' },
            { label: 'Wrong outcome', count: 4, max: 24, color: T.red, pts: '0' },
          ].map((r, i) => (
            <div key={i} style={{ marginBottom: i < 3 ? 12 : 0 }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 6 }}>
                <span style={{ fontFamily: T.body, fontSize: 12, fontWeight: 600, color: T.text }}>{r.label}</span>
                <span style={{ display: 'flex', gap: 8, alignItems: 'baseline' }}>
                  <span style={{ fontFamily: T.display, fontWeight: 800, fontSize: 13, color: T.text }}>{r.count}<span style={{ color: T.textDim, fontWeight: 500 }}>/{r.max}</span></span>
                  <span style={{ fontFamily: T.body, fontSize: 11, fontWeight: 700, color: r.color }}>{r.pts}</span>
                </span>
              </div>
              <div style={{ height: 6, borderRadius: 3, background: T.bg3, overflow: 'hidden', position: 'relative' }}>
                <div style={{
                  height: '100%', width: `${(r.count / r.max) * 100}%`,
                  background: `linear-gradient(90deg, ${r.color}, ${r.color}88)`,
                  boxShadow: `0 0 12px ${r.color}66`,
                  borderRadius: 3,
                }} />
              </div>
            </div>
          ))}
        </Glass>

        {/* Badges grid */}
        <H kicker="2 of 14 unlocked" action="See all">Achievements</H>
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 10, marginBottom: 22 }}>
          <Badge name="World Cup Oracle" desc="Predict 5 group winners exactly" icon={I.crown} color={T.amber} earned rare />
          <Badge name="Hot Streak" desc="4 predictions in a row correct" icon={I.fire} color={T.red} earned />
          <Badge name="Exact Score Master" desc="10 exact scores in tournament" icon={I.target} color={T.cyan} earned={false} prog={90} />
          <Badge name="GD Wizard" desc="15 goal-difference hits" icon={I.bolt} color={T.violet} earned={false} prog={47} />
          <Badge name="Underdog" desc="Predict 3 upsets correctly" icon={I.star} color={T.amber} earned={false} prog={33} />
          <Badge name="King of Knockouts" desc="Win 8/8 R16 predictions" icon={I.trophy} color={T.green} earned={false} prog={0} />
        </div>

        {/* Recent activity */}
        <H>Recent activity</H>
        <Glass br={16} p={4} style={{ marginBottom: 16 }}>
          {[
            { home: 'MEX', away: 'CAN', score: '2-0', pred: '2-0', pts: 8, hit: 'exact', t: '2h ago' },
            { home: 'BRA', away: 'AUS', score: '4-0', pred: '3-0', pts: 4, hit: 'gd', t: '6h ago' },
            { home: 'POR', away: 'KOR', score: '1-2', pred: '2-1', pts: 0, hit: 'miss', t: '1d ago' },
          ].map((m, i) => {
            const hitColor = m.hit === 'exact' ? T.cyan : m.hit === 'gd' ? T.violet : m.hit === 'win' ? T.green : T.red;
            return (
              <div key={i} style={{
                display: 'flex', alignItems: 'center', gap: 10,
                padding: '12px 12px',
                borderTop: i ? `1px solid ${T.border}` : 'none',
              }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: -8 }}>
                  <Crest team={m.home} size={26} />
                  <Crest team={m.away} size={26} ring />
                </div>
                <div style={{ flex: 1 }}>
                  <div style={{ fontFamily: T.display, fontWeight: 700, fontSize: 12, color: T.text }}>{m.home} {m.score} {m.away}</div>
                  <div style={{ fontFamily: T.body, fontSize: 10, color: T.textDim, marginTop: 2 }}>Your pick {m.pred} · {m.t}</div>
                </div>
                <div style={{ textAlign: 'right' }}>
                  <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 14, color: hitColor }}>{m.pts > 0 ? '+' : ''}{m.pts}</div>
                  <div style={{ fontFamily: T.body, fontSize: 9, color: hitColor, letterSpacing: '0.1em', textTransform: 'uppercase', fontWeight: 700, marginTop: 1 }}>
                    {m.hit === 'exact' ? '● exact' : m.hit === 'gd' ? '◆ GD' : m.hit === 'win' ? '✓ win' : '✕ miss'}
                  </div>
                </div>
              </div>
            );
          })}
        </Glass>
      </Screen>
      <TabBar active="me" />
    </NeonBg>
  );
}

window.ProfileScreen = ProfileScreen;
