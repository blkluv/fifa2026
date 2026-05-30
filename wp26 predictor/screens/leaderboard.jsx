// leaderboard.jsx — Live leaderboard with podium
const { T, Crest, Glass, Chip, H, NeonBg, Ic, I, TabBar, Screen } = window;

function Avatar({ name, idx, size = 36, glow }) {
  return (
    <div style={{
      width: size, height: size, borderRadius: '50%',
      background: `linear-gradient(135deg, hsl(${idx*53} 70% 55%), hsl(${idx*53+50} 70% 35%))`,
      display: 'flex', alignItems: 'center', justifyContent: 'center',
      fontFamily: T.display, fontWeight: 800, color: '#fff',
      fontSize: size * 0.42, letterSpacing: '-0.02em',
      boxShadow: glow ? `0 0 16px ${glow}` : `0 2px 6px rgba(0,0,0,0.35)`,
      border: `2px solid rgba(255,255,255,0.12)`,
      flexShrink: 0,
    }}>{name[0]}</div>
  );
}

function PodiumStep({ rank, name, pts, country, color, height, idx }) {
  return (
    <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', flex: 1, minWidth: 0 }}>
      <div style={{ position: 'relative', marginBottom: 8 }}>
        <Avatar name={name} idx={idx} size={rank === 1 ? 64 : 52} glow={color} />
        {rank === 1 && (
          <div style={{
            position: 'absolute', top: -14, left: '50%', transform: 'translateX(-50%)',
            width: 32, height: 32, borderRadius: '50%',
            background: `radial-gradient(circle at 30% 30%, ${T.amber}, ${T.amber}88)`,
            display: 'flex', alignItems: 'center', justifyContent: 'center',
            boxShadow: `0 0 20px ${T.amber}aa`,
          }}>
            <Ic d={I.crown} size={16} color="#06121C" sw={2.4} />
          </div>
        )}
        <div style={{
          position: 'absolute', bottom: -6, right: -6,
          background: T.bg2, borderRadius: '50%', padding: 2,
          border: `2px solid ${T.bg}`,
        }}><Crest team={country} size={20} ring={false} /></div>
      </div>
      <div style={{ fontFamily: T.display, fontWeight: 700, fontSize: 12, color: T.text, textAlign: 'center', maxWidth: '100%', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{name}</div>
      <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 16, color: color, marginTop: 2, fontVariantNumeric: 'tabular-nums' }}>{pts.toLocaleString()}</div>
      <div style={{
        marginTop: 8, width: '100%', height,
        background: `linear-gradient(180deg, ${color}66, ${color}11)`,
        border: `1px solid ${color}88`,
        borderRadius: '12px 12px 0 0',
        display: 'flex', alignItems: 'flex-start', justifyContent: 'center', paddingTop: 8,
        fontFamily: T.display, fontWeight: 800, fontSize: 22, color: color, letterSpacing: '-0.04em',
        boxShadow: `inset 0 0 20px ${color}33, 0 0 24px ${color}33`,
      }}>{rank}</div>
    </div>
  );
}

function LeaderRow({ rank, name, pts, exact, gd, mv, country, you, idx }) {
  return (
    <div style={{
      display: 'flex', alignItems: 'center', gap: 10,
      padding: '12px 12px',
      borderRadius: 14,
      background: you ? `linear-gradient(90deg, ${T.cyan}1F, ${T.violet}14)` : T.card,
      border: `1px solid ${you ? T.cyan + '66' : T.border}`,
      boxShadow: you ? `0 0 24px ${T.cyan}33` : 'none',
    }}>
      <div style={{ width: 28, textAlign: 'center', fontFamily: T.display, fontWeight: 800, fontSize: 14, color: T.textMid, fontVariantNumeric: 'tabular-nums' }}>{rank}</div>
      <div style={{ position: 'relative' }}>
        <Avatar name={name} idx={idx} size={32} />
        <div style={{ position: 'absolute', bottom: -3, right: -3, background: T.bg, borderRadius: '50%', padding: 1 }}><Crest team={country} size={14} ring={false} /></div>
      </div>
      <div style={{ flex: 1, minWidth: 0 }}>
        <div style={{ fontFamily: T.display, fontWeight: you ? 800 : 700, fontSize: 13, color: T.text, whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>{name}{you && ' · You'}</div>
        <div style={{ display: 'flex', gap: 8, marginTop: 3 }}>
          <span style={{ fontFamily: T.body, fontSize: 10, color: T.cyan, fontWeight: 600 }}>{exact}× exact</span>
          <span style={{ fontFamily: T.body, fontSize: 10, color: T.violet, fontWeight: 600 }}>{gd}× GD</span>
        </div>
      </div>
      <div style={{ width: 30, textAlign: 'right' }}>
        {mv > 0 && <span style={{ fontFamily: T.body, fontSize: 10, fontWeight: 700, color: T.green }}>▲{mv}</span>}
        {mv < 0 && <span style={{ fontFamily: T.body, fontSize: 10, fontWeight: 700, color: T.red }}>▼{Math.abs(mv)}</span>}
        {mv === 0 && <span style={{ fontFamily: T.body, fontSize: 11, fontWeight: 700, color: T.textFaint }}>—</span>}
      </div>
      <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 15, color: T.text, fontVariantNumeric: 'tabular-nums', minWidth: 52, textAlign: 'right' }}>{pts.toLocaleString()}</div>
    </div>
  );
}

function LeaderboardScreen() {
  const top3 = [
    { rank: 1, name: 'Kenji T.', pts: 1842, country: 'JPN', color: T.amber, height: 72, idx: 0 },
    { rank: 2, name: 'Amara O.', pts: 1798, country: 'SEN', color: T.cyan, height: 56, idx: 1 },
    { rank: 3, name: 'Lukas P.', pts: 1781, country: 'GER', color: T.violet, height: 44, idx: 2 },
  ];
  const rest = [
    { rank: 4, name: 'Sofía R.', pts: 1402, exact: 11, gd: 9, mv: 2, country: 'ARG', idx: 4 },
    { rank: 5, name: 'Mateus B.', pts: 1389, exact: 10, gd: 11, mv: -1, country: 'BRA', idx: 5 },
    { rank: 6, name: 'Pierre D.', pts: 1342, exact: 9, gd: 12, mv: 0, country: 'FRA', idx: 6 },
    { rank: 7, name: 'Aoife M.', pts: 1318, exact: 9, gd: 10, mv: 5, country: 'ENG', idx: 7 },
    { rank: 247, name: 'Diego M.', pts: 1284, exact: 9, gd: 7, mv: 14, country: 'MEX', you: true, idx: 8 },
    { rank: 248, name: 'Karim B.', pts: 1283, exact: 8, gd: 8, mv: -2, country: 'MAR', idx: 9 },
    { rank: 249, name: 'Yuki S.', pts: 1281, exact: 7, gd: 11, mv: 3, country: 'JPN', idx: 10 },
  ];

  return (
    <NeonBg hue="cyan">
      <Screen>
        {/* Header */}
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '6px 0 16px' }}>
          <div>
            <div style={{ fontFamily: T.body, fontSize: 10, fontWeight: 600, color: T.cyan, letterSpacing: '0.16em', textTransform: 'uppercase' }}>● Live · refreshing</div>
            <div style={{ fontFamily: T.display, fontSize: 22, fontWeight: 800, color: T.text, letterSpacing: '-0.03em', marginTop: 2 }}>Global Leaderboard</div>
          </div>
          <Glass p={9} br={12}><Ic d={I.set} size={18} color={T.text} /></Glass>
        </div>

        {/* Filter tabs */}
        <div style={{ display: 'flex', gap: 6, padding: 4, background: T.card, borderRadius: 14, border: `1px solid ${T.border}`, marginBottom: 16 }}>
          {['Global', 'Friends', 'Country', 'Round'].map((t, i) => (
            <div key={i} style={{
              flex: 1, textAlign: 'center', padding: '8px 4px',
              borderRadius: 10,
              background: i === 0 ? `linear-gradient(135deg, ${T.cyan}, ${T.violet})` : 'transparent',
              color: i === 0 ? '#06121C' : T.textMid,
              fontFamily: T.display, fontSize: 11, fontWeight: 700,
              boxShadow: i === 0 ? `0 0 16px ${T.cyan}55` : 'none',
            }}>{t}</div>
          ))}
        </div>

        {/* Podium */}
        <Glass p={18} br={22} style={{ marginBottom: 16, position: 'relative', overflow: 'hidden' }}>
          <div style={{ position: 'absolute', inset: 0, background: `radial-gradient(circle at 50% 0%, ${T.amber}33, transparent 55%)`, pointerEvents: 'none' }} />
          <div style={{ display: 'flex', alignItems: 'flex-end', gap: 12, position: 'relative' }}>
            <PodiumStep {...top3[1]} />
            <PodiumStep {...top3[0]} />
            <PodiumStep {...top3[2]} />
          </div>
        </Glass>

        {/* List */}
        <div style={{ display: 'flex', flexDirection: 'column', gap: 8, marginBottom: 12 }}>
          {rest.slice(0, 4).map((r, i) => <LeaderRow key={i} {...r} />)}
        </div>

        {/* Divider — your rank context */}
        <div style={{ display: 'flex', alignItems: 'center', gap: 8, margin: '14px 0 10px' }}>
          <div style={{ flex: 1, height: 1, background: T.border }} />
          <div style={{ fontFamily: T.body, fontSize: 10, fontWeight: 700, color: T.textDim, letterSpacing: '0.14em', textTransform: 'uppercase' }}>Your bracket</div>
          <div style={{ flex: 1, height: 1, background: T.border }} />
        </div>

        <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
          {rest.slice(4).map((r, i) => <LeaderRow key={i} {...r} />)}
        </div>
      </Screen>
      <TabBar active="rank" />
    </NeonBg>
  );
}

window.LeaderboardScreen = LeaderboardScreen;
