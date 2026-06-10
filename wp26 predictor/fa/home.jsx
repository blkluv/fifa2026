// fa/home.jsx — صفحه اصلی (داشبورد)
const { T, toFa, faTeamName, Crest, Glass, Chip, H, NeonBg, Ic, I, Screen, Stat, Avatar, useMe, getInitial, useApi } = window;

function CountdownCell({ v, l }) {
  return (
    <div style={{ textAlign: 'center', minWidth: 40 }}>
      <div style={{ fontFamily: T.display, fontSize: 24, fontWeight: 800, color: T.text, letterSpacing: '-0.04em', lineHeight: 1, direction: 'ltr' }}>{toFa(String(v).padStart(2, '0'))}</div>
      <div style={{ fontFamily: T.body, fontSize: 9, fontWeight: 700, color: T.textDim, marginTop: 4 }}>{l}</div>
    </div>
  );
}

function Countdown({ d, h, m, s }) {
  const dot = <div style={{ color: T.cyan, fontSize: 22, fontWeight: 800, marginTop: -6 }}>:</div>;
  return (
    <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 4, direction: 'ltr' }}>
      <CountdownCell v={d} l="روز" />{dot}<CountdownCell v={h} l="ساعت" />{dot}<CountdownCell v={m} l="دقیقه" />{dot}<CountdownCell v={s} l="ثانیه" />
    </div>
  );
}

function useCountdownTo(kickoffAt) {
  const calc = React.useCallback(() => {
    if (!kickoffAt) return { d: 0, h: 0, m: 0, s: 0, done: true };
    const dt = new Date(String(kickoffAt).replace(' ', 'T'));
    const diff = Math.max(0, dt.getTime() - Date.now());
    const s = Math.floor(diff / 1000);
    const d = Math.floor(s / 86400);
    const h = Math.floor((s % 86400) / 3600);
    const m = Math.floor((s % 3600) / 60);
    const sec = s % 60;
    return { d, h, m, s: sec, done: diff <= 0 };
  }, [kickoffAt]);

  const [v, setV] = React.useState(calc);

  React.useEffect(() => {
    setV(calc());
    const t = setInterval(() => setV(calc()), 1000);
    return () => clearInterval(t);
  }, [calc]);

  return v;
}

function FaHome({ go }) {
  const { me, loading } = useMe();
  const displayName = loading ? '…' : (me?.display_name || 'مهمان');
  const initial = getInitial(displayName);

  const { data: matches } = useApi('/matches', []);
  const upcoming = Array.isArray(matches)
    ? matches.filter((m) => m && m.status === 'scheduled').slice().sort((a, b) => String(a.kickoff_at).localeCompare(String(b.kickoff_at)))
    : [];
  const nextMatch = upcoming[0] || null;
  const nextCountdown = useCountdownTo(nextMatch?.kickoff_at);

  const { data: summary, loading: summaryLoading, error: summaryError } = useApi(me?.id ? '/me/summary' : null, [me?.id]);
  const summaryReady = !summaryLoading && !!summary;
  const myRank   = summaryReady && summary.rank_position != null ? Number(summary.rank_position) : null;
  const myPoints = summaryReady ? Number(summary.total_points  ?? 0) : 0;
  const myExact  = summaryReady ? Number(summary.exact_hits    ?? 0) : 0;
  const myAccuracy  = summaryReady && summary.accuracy_pct != null ? Number(summary.accuracy_pct) : null;
  const myStreak    = summaryReady && summary.streak       != null ? Number(summary.streak)       : null;
  const points7d    = summaryReady && summary.points_last_7_days != null ? Number(summary.points_last_7_days) : null;

  const { data: badgesData, loading: badgesLoading, error: badgesError } = useApi(me?.id ? '/me/badges' : null, [me?.id]);
  const badges = Array.isArray(badgesData) ? badgesData : [];
  const earnedCount = badges.filter((b) => !!b.earned).length;
  const totalBadges = badges.length;

  const badgeUi = (slug) => {
    const s = String(slug || '');
    if (s.includes('exact')) return { icon: I.target, color: T.cyan };
    if (s.includes('goal')) return { icon: I.trend, color: T.violet };
    if (s.includes('wizard')) return { icon: I.crown, color: T.amber };
    if (s.includes('champion')) return { icon: I.trophy, color: T.amber };
    return { icon: I.crown, color: T.textMid };
  };

  return (
    <NeonBg>
      <Screen>
        {/* Header */}
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '6px 0 16px' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
            <div style={{
              width: 40, height: 40, borderRadius: 12,
              background: `linear-gradient(135deg, ${T.cyan}, ${T.violet})`,
              display: 'flex', alignItems: 'center', justifyContent: 'center',
              boxShadow: `0 0 24px ${T.cyan}55`,
              overflow: 'hidden',
            }}>
              {me?.avatar ? (
                <img
                  src={me.avatar}
                  alt={displayName}
                  style={{ width: '100%', height: '100%', objectFit: 'cover', display: 'block' }}
                />
              ) : (
                <div style={{ fontWeight: 800, color: '#06121C', fontSize: 14, letterSpacing: '-0.04em', direction: 'ltr' }}>{initial}</div>
              )}
            </div>
            <div>
              <div style={{ fontFamily: T.body, fontSize: 11, color: T.textDim, fontWeight: 500 }}>پیش‌بین جام جهانی ۲۰۲۶</div>
              <div style={{ fontFamily: T.display, fontSize: 16, fontWeight: 800, color: T.text, letterSpacing: '-0.02em' }}>{displayName}</div>
            </div>
          </div>
          <div style={{ display: 'flex', gap: 8 }}>
            <Glass p={9} br={12} style={{ position: 'relative' }} onClick={() => go && go('notifs')}>
              <Ic d={I.bell} size={20} color={T.text} />
              <div style={{ position: 'absolute', top: 6, left: 6, width: 8, height: 8, borderRadius: 999, background: T.cyan, boxShadow: `0 0 8px ${T.cyan}` }} />
            </Glass>
            <Glass p={9} br={12}><Ic d={I.search} size={20} color={T.text} /></Glass>
          </div>
        </div>

        {/* Points Card */}
        <Glass hi glow={T.cyan} br={22} p={18} style={{ marginBottom: 14, position: 'relative', overflow: 'hidden' }}>
          <div style={{ position: 'absolute', left: -40, top: -40, width: 180, height: 180,
            background: `radial-gradient(circle, ${T.violet}55, transparent 65%)` }} />
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 16, position: 'relative' }}>
            <Chip color={T.cyan}>● زنده · مرحله گروهی</Chip>
            {points7d !== null && (
              <div style={{ display: 'flex', alignItems: 'center', gap: 4, color: points7d >= 0 ? T.green : T.red, fontFamily: T.body, fontWeight: 800, fontSize: 12, direction: 'ltr' }}>
                <Ic d={I.trend} size={14} sw={2} /> {toFa((points7d >= 0 ? '+' : '') + String(points7d))}
              </div>
            )}
          </div>
          <div style={{ display: 'flex', alignItems: 'flex-end', gap: 16, position: 'relative' }}>
            <div>
              <div style={{ fontFamily: T.body, fontSize: 10, fontWeight: 700, color: T.textDim }}>رتبه شما</div>
              <div style={{ display: 'flex', alignItems: 'baseline', gap: 6, marginTop: 4, direction: 'ltr' }}>
                {myRank && <span style={{ fontFamily: T.display, fontSize: 14, color: T.textMid, fontWeight: 700 }}>#</span>}
                <span style={{ fontFamily: T.display, fontSize: 44, fontWeight: 800, color: T.text, letterSpacing: '-0.04em', lineHeight: 1 }}>
                  {!me?.id ? '—' : summaryLoading ? '…' : myRank ? toFa(String(myRank)) : '—'}
                </span>
              </div>
              <div style={{ fontFamily: T.body, fontSize: 11, color: summaryError ? T.red : T.textDim, marginTop: 4 }}>
                {!me?.id
                  ? 'برای مشاهده رتبه وارد شوید'
                  : summaryError
                    ? (summaryError.message || 'خطا در دریافت اطلاعات')
                    : summaryLoading
                      ? 'در حال دریافت…'
                      : summary?.total_users
                        ? `از ${toFa(String(summary.total_users))} نفر`
                        : 'در حال محاسبه…'}
              </div>
            </div>
            <div style={{ flex: 1, textAlign: 'left' }}>
              <div style={{ fontFamily: T.body, fontSize: 10, fontWeight: 700, color: T.textDim }}>امتیاز</div>
              <div style={{ fontFamily: T.display, fontSize: 44, fontWeight: 800, lineHeight: 1, marginTop: 4, letterSpacing: '-0.04em', direction: 'ltr', color: T.cyan }}>
                {summaryLoading ? '…' : toFa(String(myPoints.toLocaleString()))}
              </div>
              <div style={{ fontFamily: T.body, fontSize: 11, color: points7d !== null && points7d > 0 ? T.green : T.textDim, marginTop: 4, fontWeight: 700 }}>
                {points7d !== null ? `${toFa((points7d >= 0 ? '+' : '') + String(points7d))} امتیاز در ۷ روز اخیر` : (nextMatch ? 'مسابقات طبق دیتابیس' : 'در انتظار دیتای مسابقات')}
              </div>
            </div>
          </div>
          <div style={{ display: 'flex', gap: 10, marginTop: 16, paddingTop: 14, borderTop: `1px solid ${T.border}` }}>
            <Stat label="دقت" value={myAccuracy !== null ? toFa(String(myAccuracy) + '%') : '—'} sub={summary?.total_scored ? `از ${toFa(String(summary.total_scored))}` : ''} color={T.green} />
            <div style={{ width: 1, background: T.border }} />
            <Stat label="دقیق" value={toFa(String(myExact))} sub={summary?.total_scored ? `از ${toFa(String(summary.total_scored))}` : ''} color={T.cyan} />
            <div style={{ width: 1, background: T.border }} />
            <Stat label="استریک" value={myStreak !== null ? toFa(String(myStreak)) : '—'} sub={myStreak !== null ? 'پشت‌سرهم' : ''} color={T.amber} />
          </div>
        </Glass>

        {/* مسابقه بعدی + شمارش معکوس */}
        <Glass br={20} p={16} style={{ marginBottom: 18 }}>
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 14 }}>
            <Chip color={T.amber}>قفل می‌شود تا</Chip>
            <div style={{ fontFamily: T.body, fontSize: 11, color: T.textMid }}>{nextMatch ? `${nextMatch.venue || '—'} · ${nextMatch.group_id ? 'مرحله گروهی' : (nextMatch.stage || '—')}` : '—'}</div>
          </div>
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
            <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 6, width: 64 }}>
              <Crest team={nextMatch ? { code: nextMatch.home_team_code, name: nextMatch.home_team_name, flag_url: nextMatch.home_flag } : { code: '—' }} size={48} glow />
              <div style={{ fontFamily: T.display, fontSize: 12, fontWeight: 800, color: T.text }}>{nextMatch ? faTeamName(nextMatch.home_team_code, nextMatch.home_team_name) : '—'}</div>
            </div>
            <div style={{ flex: 1, padding: '0 8px' }}>
              <Countdown d={nextCountdown.d} h={nextCountdown.h} m={nextCountdown.m} s={nextCountdown.s} />
            </div>
            <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 6, width: 64 }}>
              <Crest team={nextMatch ? { code: nextMatch.away_team_code, name: nextMatch.away_team_name, flag_url: nextMatch.away_flag } : { code: '—' }} size={48} glow />
              <div style={{ fontFamily: T.display, fontSize: 12, fontWeight: 800, color: T.text }}>{nextMatch ? faTeamName(nextMatch.away_team_code, nextMatch.away_team_name) : '—'}</div>
            </div>
          </div>
          <button onClick={() => nextMatch && go && go('predict', { matchId: nextMatch.id })} style={{
            marginTop: 14, width: '100%',
            background: `linear-gradient(135deg, ${T.cyan}, ${T.violet})`,
            border: 'none', borderRadius: 14, padding: '12px',
            fontFamily: T.display, fontWeight: 800, fontSize: 14, color: '#06121C',
            cursor: 'pointer',
            boxShadow: `0 8px 20px ${T.cyan}44, inset 0 -2px 0 rgba(0,0,0,0.15)`,
          }}>ثبت پیش‌بینی ←</button>
        </Glass>

        {/* فیکسچرهای پیش‌رو */}
        <H kicker={nextMatch ? `طبق دیتابیس · ${toFa(String(Math.min(6, upcoming.length)))} بازی` : '—'} action="مشاهده همه">بازی‌های پیش‌رو</H>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 10, marginBottom: 22 }}>
          {upcoming.slice(0, 3).map((m, i) => (
            <Glass key={m.id || i} br={14} p={12} onClick={() => !m.locked && go && go('predict', { matchId: m.id })} style={{ cursor: m.locked ? 'default' : 'pointer' }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                <div style={{ display: 'flex', alignItems: 'center', width: 64 }}>
                  <Crest team={{ code: m.home_team_code, name: m.home_team_name, flag_url: m.home_flag }} size={28} />
                  <div style={{ marginInlineStart: -6 }}><Crest team={{ code: m.away_team_code, name: m.away_team_name, flag_url: m.away_flag }} size={28} /></div>
                </div>
                <div style={{ flex: 1 }}>
                  <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 13, color: T.text }}>
                    {faTeamName(m.home_team_code, m.home_team_name)} <span style={{ color: T.textDim, fontWeight: 500 }}>مقابل</span> {faTeamName(m.away_team_code, m.away_team_name)}
                  </div>
                  <div style={{ fontFamily: T.body, fontSize: 11, color: T.textDim, marginTop: 2, direction: 'ltr', textAlign: 'right' }}>{String(m.kickoff_at || '').replace(' ', ' · ')} · {m.stage || 'group'}</div>
                </div>
                {m.locked ? (
                  <Chip color={T.amber}><Ic d={I.lock} size={10} sw={2.4} /> قفل</Chip>
                ) : (
                  <Chip color={T.cyan} solid>پیش‌بینی</Chip>
                )}
              </div>
            </Glass>
          ))}
        </div>

        {/* Achievements */}
        <H kicker={me?.id ? (totalBadges ? `${toFa(String(earnedCount))} از ${toFa(String(totalBadges))} باز شده` : '—') : 'برای مشاهده وارد شوید'} action="همه">دستاوردها</H>

        {badgesError && (
          <Glass br={14} p={14} style={{ marginBottom: 12, borderColor: T.red + '66', background: `linear-gradient(90deg, ${T.red}1F, ${T.red}06)` }}>
            <div style={{ fontFamily: T.body, fontSize: 12, color: T.text }}>{badgesError?.message || 'خطا در دریافت دستاوردها'}</div>
          </Glass>
        )}

        {badgesLoading && me?.id && (
          <Glass br={14} p={14} style={{ marginBottom: 12 }}>
            <div style={{ fontFamily: T.body, fontSize: 12, color: T.textMid }}>در حال دریافت دستاوردها…</div>
          </Glass>
        )}

        {!badgesLoading && me?.id && badges.length > 0 && (
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 10, marginBottom: 18 }}>
            {badges.slice(0, 3).map((b) => {
              const ui = badgeUi(b.slug);
              const color = ui.color;
              const earned = !!b.earned;
              const prog = Number(b.progress_pct || 0);
              return (
                <Glass key={b.id || b.slug} p={12} br={14} style={{ textAlign: 'center' }} glow={earned ? color : undefined}>
                  <div style={{
                    width: 44, height: 44, borderRadius: '50%', margin: '0 auto 8px',
                    background: earned ? `radial-gradient(circle at 30% 30%, ${color}, ${color}66)` : T.bg2,
                    border: `1px solid ${earned ? color : T.border}`,
                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                    boxShadow: earned ? `0 0 18px ${color}88` : 'none',
                    opacity: earned ? 1 : 0.6,
                  }}>
                    <Ic d={ui.icon} size={20} color={earned ? '#06121C' : T.textMid} sw={2.2} />
                  </div>
                  <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 11, color: T.text, whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>{b.name}</div>
                  {b.description && (
                    <div style={{ fontFamily: T.body, fontSize: 9, color: T.textFaint, marginTop: 2, lineHeight: 1.4, minHeight: 24, overflow: 'hidden', display: '-webkit-box', WebkitLineClamp: 2, WebkitBoxOrient: 'vertical' }}>{b.description}</div>
                  )}
                  {!earned && (
                    <>
                      <div style={{ marginTop: 6, height: 3, borderRadius: 2, background: 'rgba(255,255,255,0.18)', overflow: 'hidden' }}>
                        <div style={{ width: `${prog}%`, height: '100%', background: color }} />
                      </div>
                      <div style={{ fontFamily: T.body, fontSize: 9, color: prog > 0 ? color : T.textFaint, marginTop: 3, fontWeight: 700, direction: 'ltr' }}>{toFa(prog)}٪</div>
                    </>
                  )}
                  {earned && (
                    <div style={{ fontFamily: T.body, fontSize: 9, color, marginTop: 4, fontWeight: 800 }}>✓ کسب شد</div>
                  )}
                </Glass>
              );
            })}
          </div>
        )}
      </Screen>
    </NeonBg>
  );
}

window.FaHome = FaHome;
