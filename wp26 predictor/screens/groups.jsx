// groups.jsx — Group standings
const { T, Crest, Glass, Chip, H, NeonBg, Ic, I, TabBar, Screen } = window;

function StandingRow({ pos, team, pld, w, d, l, gf, ga, pts, status }) {
  const statusColor = status === 'q' ? T.green : status === 'p' ? T.cyan : status === 'e' ? T.red : null;
  return (
    <div style={{
      display: 'grid', gridTemplateColumns: '24px 110px repeat(5, 1fr) 36px',
      gap: 4, alignItems: 'center',
      padding: '10px 8px',
      borderRadius: 10,
      background: statusColor ? `${statusColor}0F` : 'transparent',
      borderLeft: statusColor ? `2px solid ${statusColor}` : '2px solid transparent',
    }}>
      <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 13, color: statusColor || T.textMid, fontVariantNumeric: 'tabular-nums' }}>{pos}</div>
      <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
        <Crest team={team} size={22} ring={false} />
        <span style={{ fontFamily: T.display, fontWeight: 700, fontSize: 12, color: T.text }}>{team}</span>
      </div>
      {[pld, w, d, l, `${gf}:${ga}`].map((v, i) => (
        <div key={i} style={{ textAlign: 'center', fontFamily: T.mono, fontSize: 12, color: T.textMid, fontVariantNumeric: 'tabular-nums' }}>{v}</div>
      ))}
      <div style={{ textAlign: 'right', fontFamily: T.display, fontSize: 14, fontWeight: 800, color: T.text, fontVariantNumeric: 'tabular-nums' }}>{pts}</div>
    </div>
  );
}

function GroupTable({ letter, teams }) {
  return (
    <Glass br={18} p={14} style={{ marginBottom: 14 }}>
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 12 }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
          <div style={{
            width: 30, height: 30, borderRadius: 10,
            background: `linear-gradient(135deg, ${T.cyan}, ${T.violet})`,
            display: 'flex', alignItems: 'center', justifyContent: 'center',
            fontFamily: T.display, fontWeight: 800, fontSize: 14, color: '#06121C',
            boxShadow: `0 0 16px ${T.cyan}55`,
          }}>{letter}</div>
          <div>
            <div style={{ fontFamily: T.display, fontWeight: 700, fontSize: 14, color: T.text }}>Group {letter}</div>
            <div style={{ fontFamily: T.body, fontSize: 10, color: T.textDim, letterSpacing: '0.1em', textTransform: 'uppercase' }}>Matchday 2 of 3</div>
          </div>
        </div>
        <Chip color={T.cyan}>Live</Chip>
      </div>

      {/* Column header */}
      <div style={{
        display: 'grid', gridTemplateColumns: '24px 110px repeat(5, 1fr) 36px', gap: 4,
        padding: '4px 8px 8px', borderBottom: `1px solid ${T.border}`, marginBottom: 4,
      }}>
        <div></div><div></div>
        {['Pl', 'W', 'D', 'L', 'GF:GA'].map((h, i) => (
          <div key={i} style={{ textAlign: 'center', fontFamily: T.body, fontSize: 9, fontWeight: 700, color: T.textDim, letterSpacing: '0.1em', textTransform: 'uppercase' }}>{h}</div>
        ))}
        <div style={{ textAlign: 'right', fontFamily: T.body, fontSize: 9, fontWeight: 700, color: T.textDim, letterSpacing: '0.1em', textTransform: 'uppercase' }}>Pts</div>
      </div>

      {teams.map((t, i) => <StandingRow key={i} pos={i + 1} {...t} status={i < 2 ? 'q' : i === 3 ? 'e' : null} />)}

      <div style={{ display: 'flex', gap: 14, marginTop: 10, paddingTop: 10, borderTop: `1px solid ${T.border}` }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 6, fontFamily: T.body, fontSize: 10, color: T.textDim }}>
          <div style={{ width: 8, height: 8, borderRadius: 2, background: T.green }} />Qualified
        </div>
        <div style={{ display: 'flex', alignItems: 'center', gap: 6, fontFamily: T.body, fontSize: 10, color: T.textDim }}>
          <div style={{ width: 8, height: 8, borderRadius: 2, background: T.red }} />Eliminated
        </div>
      </div>
    </Glass>
  );
}

function GroupsScreen() {
  return (
    <NeonBg>
      <Screen>
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '6px 0 16px' }}>
          <div>
            <div style={{ fontFamily: T.body, fontSize: 10, fontWeight: 600, color: T.cyan, letterSpacing: '0.16em', textTransform: 'uppercase' }}>WP26 · Group Stage</div>
            <div style={{ fontFamily: T.display, fontSize: 22, fontWeight: 800, color: T.text, letterSpacing: '-0.03em', marginTop: 2 }}>Standings</div>
          </div>
          <Glass p={9} br={12}><Ic d={I.cal} size={18} color={T.text} /></Glass>
        </div>

        {/* Group selector */}
        <div style={{ display: 'flex', gap: 6, overflowX: 'auto', marginBottom: 14, paddingBottom: 4 }}>
          {['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'].map((g, i) => (
            <div key={g} style={{
              flexShrink: 0, padding: '8px 14px',
              borderRadius: 10,
              background: i === 0 ? `linear-gradient(135deg, ${T.cyan}33, ${T.violet}22)` : T.card,
              border: `1px solid ${i === 0 ? T.cyan + '66' : T.border}`,
              fontFamily: T.display, fontSize: 12, fontWeight: 700,
              color: i === 0 ? T.text : T.textMid,
              boxShadow: i === 0 ? `0 0 16px ${T.cyan}33` : 'none',
            }}>Group {g}</div>
          ))}
        </div>

        <GroupTable letter="A" teams={[
          { team: 'MEX', pld: 2, w: 2, d: 0, l: 0, gf: 5, ga: 1, pts: 6 },
          { team: 'USA', pld: 2, w: 1, d: 1, l: 0, gf: 3, ga: 2, pts: 4 },
          { team: 'CAN', pld: 2, w: 0, d: 1, l: 1, gf: 1, ga: 3, pts: 1 },
          { team: 'JPN', pld: 2, w: 0, d: 0, l: 2, gf: 1, ga: 4, pts: 0 },
        ]} />

        <GroupTable letter="B" teams={[
          { team: 'BRA', pld: 2, w: 2, d: 0, l: 0, gf: 6, ga: 0, pts: 6 },
          { team: 'POR', pld: 2, w: 1, d: 0, l: 1, gf: 3, ga: 2, pts: 3 },
          { team: 'KOR', pld: 2, w: 1, d: 0, l: 1, gf: 2, ga: 3, pts: 3 },
          { team: 'AUS', pld: 2, w: 0, d: 0, l: 2, gf: 0, ga: 6, pts: 0 },
        ]} />
      </Screen>
      <TabBar active="matches" />
    </NeonBg>
  );
}

window.GroupsScreen = GroupsScreen;
