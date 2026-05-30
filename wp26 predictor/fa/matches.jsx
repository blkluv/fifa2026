const { T, toFa, faTeamName, Crest, Glass, Chip, NeonBg, Ic, I, Screen, AppBar, useApi } = window;

function StandingRow({ pos, team, pld, w, d, l, gd, pts, status }) {
  const sc = status === 'q' ? T.green : status === 'e' ? T.red : null;
  return (
    <div style={{
      display: 'grid', gridTemplateColumns: '20px 1.5fr repeat(5, 1fr) 32px',
      gap: 4, alignItems: 'center',
      padding: '10px 8px', borderRadius: 10,
      background: sc ? `${sc}0F` : 'transparent',
      borderInlineStart: sc ? `2px solid ${sc}` : '2px solid transparent',
    }}>
      <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 13, color: sc || T.textMid, direction: 'ltr', textAlign: 'center' }}>{toFa(pos)}</div>
      <div style={{ display: 'flex', alignItems: 'center', gap: 8, minWidth: 0 }}>
        <Crest team={team} size={22} ring={false} />
        <span style={{ fontFamily: T.display, fontWeight: 800, fontSize: 12, color: T.text, whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>{faTeamName(team?.code, team?.name)}</span>
      </div>
      {[pld, w, d, l, gd].map((v, i) => (
        <div key={i} style={{ textAlign: 'center', fontFamily: T.mono, fontSize: 12, color: T.textMid, direction: 'ltr' }}>{toFa(v)}</div>
      ))}
      <div style={{ textAlign: 'center', fontFamily: T.display, fontSize: 14, fontWeight: 800, color: T.text, direction: 'ltr' }}>{toFa(pts)}</div>
    </div>
  );
}

function GroupTable({ letter, teams }) {
  const n = Array.isArray(teams) ? teams.length : 0;
  const expectedPld = Math.max(0, n - 1);
  const maxPld = teams.reduce((m, t) => Math.max(m, Number(t?.pld || 0)), 0);
  const complete = expectedPld > 0 && maxPld >= expectedPld && teams.every((t) => Number(t?.pld || 0) >= expectedPld);
  const qualifyCount = n >= 2 ? 2 : n;
  const elimCount = n >= 4 ? 2 : 0;
  const elimStart = n - elimCount;
  const statusFor = (idx) => {
    if (!complete) return null;
    if (idx < qualifyCount) return 'q';
    if (elimCount > 0 && idx >= elimStart) return 'e';
    return null;
  };

  return (
    <Glass br={18} p={14} style={{ marginBottom: 14 }}>
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 12 }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
          <div style={{
            width: 30, height: 30, borderRadius: 10,
            background: `linear-gradient(135deg, ${T.cyan}, ${T.violet})`,
            display: 'flex', alignItems: 'center', justifyContent: 'center',
            fontWeight: 800, fontSize: 14, color: '#06121C',
            boxShadow: `0 0 16px ${T.cyan}55`,
          }}>{letter}</div>
          <div>
            <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 14, color: T.text }}>گروه {letter}</div>
            <div style={{ fontFamily: T.body, fontSize: 10, color: T.textDim }}>مرحله گروهی</div>
          </div>
        </div>
      </div>

      <div style={{
        display: 'grid', gridTemplateColumns: '20px 1.5fr repeat(5, 1fr) 32px', gap: 4,
        padding: '4px 8px 8px', borderBottom: `1px solid ${T.border}`, marginBottom: 4,
      }}>
        <div></div><div></div>
        {['بازی', 'برد', 'مساوی', 'باخت', 'تفاضل'].map((h, i) => (
          <div key={i} style={{ textAlign: 'center', fontFamily: T.body, fontSize: 10, fontWeight: 800, color: T.textDim }}>{h}</div>
        ))}
        <div style={{ textAlign: 'center', fontFamily: T.body, fontSize: 10, fontWeight: 800, color: T.textDim }}>امتیاز</div>
      </div>

      {teams.map((t, i) => <StandingRow key={i} pos={i + 1} {...t} status={statusFor(i)} />)}

      <div style={{ display: 'flex', gap: 14, marginTop: 10, paddingTop: 10, borderTop: `1px solid ${T.border}`, flexWrap: 'wrap' }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 6, fontFamily: T.body, fontSize: 10, color: T.textDim }}>
          <div style={{ width: 8, height: 8, borderRadius: 2, background: T.green }} />صعود کرده
        </div>
        <div style={{ display: 'flex', alignItems: 'center', gap: 6, fontFamily: T.body, fontSize: 10, color: T.textDim }}>
          <div style={{ width: 8, height: 8, borderRadius: 2, background: T.red }} />حذف شده
        </div>
      </div>
    </Glass>
  );
}

const MONTHS_FA = ['ژانویه','فوریه','مارس','آوریل','مه','ژوئن','ژوئیه','اوت','سپتامبر','اکتبر','نوامبر','دسامبر'];

function fmtKickoff(kickoffAt) {
  if (!kickoffAt) return { time: '—', date: '' };
  // Parse directly from stored string (YYYY-MM-DD HH:MM:SS in WP/Tehran timezone)
  // Avoids browser timezone conversion issues.
  const m = String(kickoffAt).match(/(\d{4})-(\d{2})-(\d{2})[T ](\d{2}):(\d{2})/);
  if (!m) return { time: String(kickoffAt), date: '' };
  const month = MONTHS_FA[parseInt(m[2], 10) - 1] || m[2];
  return {
    time: toFa(`${m[4]}:${m[5]}`),
    date: `${toFa(parseInt(m[3], 10))} ${month}`,
  };
}

function MatchCard({ match, go }) {
  const home = { code: match.home_team_code, name: match.home_team_name, flag_url: match.home_flag };
  const away = { code: match.away_team_code, name: match.away_team_name, flag_url: match.away_flag };
  const isFinished = match.status === 'finished';
  const isLive = match.status === 'live';
  const isScheduled = match.status === 'scheduled';
  const isLocked = !!match.locked;
  const { time, date } = fmtKickoff(match.kickoff_at);

  return (
    <Glass br={14} p={12} style={{ marginBottom: 10 }}>
      {/* Teams row */}
      <div style={{ display: 'flex', alignItems: 'center', gap: 6, direction: 'ltr' }}>
        {/* Home */}
        <div style={{ flex: 1, display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 6 }}>
          <Crest team={home} size={36} ring={false} />
          <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 11, color: T.text, textAlign: 'center' }}>
            {faTeamName(home.code, home.name)}
          </div>
        </div>

        {/* Center */}
        <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 4, minWidth: 64 }}>
          {isFinished ? (
            <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 22, color: T.text, letterSpacing: '-0.04em', direction: 'ltr' }}>
              {toFa(match.home_score)} – {toFa(match.away_score)}
            </div>
          ) : isLive ? (
            <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 14, color: T.green }}>زنده</div>
          ) : (
            <>
              <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 15, color: T.text, direction: 'ltr' }}>{time}</div>
              {date && <div style={{ fontFamily: T.body, fontSize: 10, color: T.textDim }}>{date}</div>}
            </>
          )}
          {isFinished && <Chip color={T.textDim} style={{ fontSize: 9 }}>پایان</Chip>}
          {isLive && <Chip color={T.green} style={{ fontSize: 9 }}>زنده</Chip>}
        </div>

        {/* Away */}
        <div style={{ flex: 1, display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 6 }}>
          <Crest team={away} size={36} ring={false} />
          <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 11, color: T.text, textAlign: 'center' }}>
            {faTeamName(away.code, away.name)}
          </div>
        </div>
      </div>

      {/* Action row */}
      {!isFinished && (
        <div style={{ marginTop: 10, paddingTop: 10, borderTop: `1px solid ${T.border}`, display: 'flex', justifyContent: 'center' }}>
          {isLocked || isLive ? (
            <Chip color={T.red}>
              <Ic d={I.lock} size={11} color={T.red} sw={2} />
              <span style={{ marginRight: 4 }}>قفل شده</span>
            </Chip>
          ) : (
            <button
              onClick={() => go && go('predict', { matchId: Number(match.id) })}
              style={{
                padding: '8px 20px', borderRadius: 10, border: 'none', cursor: 'pointer',
                background: `linear-gradient(135deg, ${T.cyan}DD, ${T.violet}CC)`,
                fontFamily: T.display, fontSize: 12, fontWeight: 800, color: '#06121C',
                boxShadow: `0 4px 16px ${T.cyan}44`,
                display: 'flex', alignItems: 'center', gap: 6,
              }}
            >
              ثبت پیش‌بینی ←
            </button>
          )}
        </div>
      )}
    </Glass>
  );
}

function FaMatches({ go }) {
  const { data: groups, loading: groupsLoading, error: groupsError } = useApi('/groups', []);
  const groupRows = Array.isArray(groups) ? groups : [];
  const groupTabs = groupRows.map((g) => {
    const name = String(g.name || '');
    const m = name.match(/([A-L])\s*$/i) || name.match(/Group\s+([A-L])/i);
    const letter = (m && m[1]) ? String(m[1]).toUpperCase() : name;
    return { id: Number(g.id), name, letter };
  });

  const [activeGroupId, setActiveGroupId] = React.useState(groupTabs[0]?.id || null);

  React.useEffect(() => {
    if (activeGroupId) return;
    if (groupTabs[0]?.id) setActiveGroupId(groupTabs[0].id);
  }, [groupTabs.length]);

  const active = groupTabs.find((g) => Number(g.id) === Number(activeGroupId)) || groupTabs[0] || null;
  const { data: standings, loading: standingsLoading, error: standingsError } = useApi(active?.id ? `/standings?group_id=${active.id}` : null, [active?.id]);
  const teams = Array.isArray(standings)
    ? standings.map((r) => ({
        team: { code: r.code, name: r.team_name, flag_url: r.flag_url },
        pld: Number(r.played || 0),
        w: Number(r.won || 0),
        d: Number(r.draw || 0),
        l: Number(r.lost || 0),
        gd: Number(r.goal_difference || 0),
        pts: Number(r.points || 0),
      }))
    : [];

  const { data: allMatches, loading: matchesLoading } = useApi('/matches', []);
  const groupMatches = React.useMemo(() => {
    if (!Array.isArray(allMatches) || !active?.id) return [];
    return allMatches
      .filter((m) => Number(m.group_id) === Number(active.id))
      .sort((a, b) => String(a.kickoff_at).localeCompare(String(b.kickoff_at)));
  }, [allMatches, active?.id]);

  return (
    <NeonBg>
      <Screen>
        <AppBar
          kicker="جام جهانی ۲۰۲۶ · مرحله گروهی"
          title="جدول‌ها"
          right={<Glass p={9} br={12}><Ic d={I.cal} size={18} color={T.text} /></Glass>}
        />

        {groupsLoading && (
          <Glass br={14} p={14} style={{ marginBottom: 12 }}>
            <div style={{ fontFamily: T.body, fontSize: 12, color: T.textMid }}>در حال دریافت گروه‌ها…</div>
          </Glass>
        )}

        {groupsError && (
          <Glass br={14} p={14} style={{ marginBottom: 12, borderColor: T.red + '66', background: `linear-gradient(90deg, ${T.red}1F, ${T.red}06)` }}>
            <div style={{ fontFamily: T.body, fontSize: 12, color: T.text }}>{groupsError?.message || 'خطا در دریافت گروه‌ها'}</div>
          </Glass>
        )}

        {!groupsLoading && !groupsError && groupTabs.length === 0 && (
          <Glass br={14} p={14} style={{ marginBottom: 12, borderColor: T.amber + '66', background: `linear-gradient(90deg, ${T.amber}1F, ${T.amber}06)` }}>
            <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 13, color: T.text }}>گروهی پیدا نشد</div>
            <div style={{ fontFamily: T.body, fontSize: 12, color: T.textMid, marginTop: 4 }}>
              ابتدا از داشبورد پلاگین، ایمپورت دیتای جام جهانی ۲۰۲۶ را انجام دهید.
            </div>
          </Glass>
        )}

        {groupTabs.length > 0 && (
          <div style={{ display: 'flex', gap: 6, overflowX: 'auto', marginBottom: 14, paddingBottom: 4 }}>
            {groupTabs.map((g) => (
              <div key={g.id || g.letter} onClick={() => setActiveGroupId(g.id)} style={{
                flexShrink: 0, padding: '8px 14px', borderRadius: 10,
                background: g.id === activeGroupId ? `linear-gradient(135deg, ${T.cyan}33, ${T.violet}22)` : T.card,
                border: `1px solid ${g.id === activeGroupId ? T.cyan + '66' : T.border}`,
                fontFamily: T.display, fontSize: 12, fontWeight: 800,
                color: g.id === activeGroupId ? T.text : T.textMid,
                boxShadow: g.id === activeGroupId ? `0 0 16px ${T.cyan}33` : 'none',
                cursor: 'pointer',
              }}>گروه {g.letter}</div>
            ))}
          </div>
        )}

        {standingsLoading && (
          <Glass br={14} p={14} style={{ marginBottom: 12 }}>
            <div style={{ fontFamily: T.body, fontSize: 12, color: T.textMid }}>در حال دریافت جدول گروه…</div>
          </Glass>
        )}

        {standingsError && (
          <Glass br={14} p={14} style={{ marginBottom: 12, borderColor: T.red + '66', background: `linear-gradient(90deg, ${T.red}1F, ${T.red}06)` }}>
            <div style={{ fontFamily: T.body, fontSize: 12, color: T.text }}>{standingsError?.message || 'خطا در دریافت جدول'}</div>
          </Glass>
        )}

        {!standingsLoading && active && teams.length > 0 && <GroupTable letter={active.letter} teams={teams} />}

        {/* Matches for the active group */}
        {active && (
          <>
            <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 10 }}>
              <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 14, color: T.text }}>مسابقه‌های گروه {active.letter}</div>
              {matchesLoading && <div style={{ fontFamily: T.body, fontSize: 11, color: T.textDim }}>…</div>}
            </div>

            {!matchesLoading && groupMatches.length === 0 && (
              <Glass br={14} p={14} style={{ marginBottom: 12 }}>
                <div style={{ fontFamily: T.body, fontSize: 12, color: T.textDim }}>مسابقه‌ای برای این گروه ثبت نشده.</div>
              </Glass>
            )}

            {groupMatches.map((m) => (
              <MatchCard key={m.id} match={m} go={go} />
            ))}
          </>
        )}
      </Screen>
    </NeonBg>
  );
}

window.FaMatches = FaMatches;
