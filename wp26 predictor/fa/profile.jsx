const { T, toFa, faTeamName, Crest, Glass, Chip, H, NeonBg, Ic, I, Screen, AppBar, useMe, getInitial, useApi } = window;

function BadgeCard({ name, desc, icon, color, earned, prog }) {
  return (
    <Glass p={14} br={16} glow={earned ? color : undefined} style={{ textAlign: 'center', position: 'relative', overflow: 'hidden' }}>
      <div style={{
        width: 52, height: 52, borderRadius: '50%', margin: '4px auto 10px',
        background: earned ? `conic-gradient(from 0deg, ${color}, ${T.violet}, ${color})` : T.bg2,
        border: `1px solid ${earned ? color : T.border}`,
        display: 'flex', alignItems: 'center', justifyContent: 'center',
        boxShadow: earned ? `0 0 24px ${color}88` : 'none',
        opacity: earned ? 1 : 0.55,
      }}>
        <div style={{
          width: 42, height: 42, borderRadius: '50%',
          background: earned ? `radial-gradient(circle at 30% 30%, ${color}, ${color}55)` : T.bg3,
          display: 'flex', alignItems: 'center', justifyContent: 'center',
        }}><Ic d={icon} size={20} color={earned ? '#06121C' : T.textMid} sw={2.2} /></div>
      </div>
      <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 11, color: T.text, lineHeight: 1.3 }}>{name}</div>
      <div style={{ fontFamily: T.body, fontSize: 10, color: T.textDim, marginTop: 3, lineHeight: 1.4, minHeight: 28 }}>{desc}</div>
      {!earned && (
        <div style={{ marginTop: 6 }}>
          <div style={{ height: 4, borderRadius: 2, background: 'rgba(255,255,255,0.18)', overflow: 'hidden' }}>
            <div style={{ height: '100%', width: `${prog}%`, background: color }} />
          </div>
          <div style={{ fontFamily: T.body, fontSize: 9, color: T.textDim, marginTop: 3, fontWeight: 700, direction: 'ltr' }}>{toFa(prog)}٪</div>
        </div>
      )}
    </Glass>
  );
}

const badgeUi = (slug) => {
  const s = String(slug || '');
  if (s.includes('exact') || s.includes('score'))   return { icon: I.target, color: T.cyan };
  if (s.includes('goal') || s.includes('diff'))     return { icon: I.trend,  color: T.violet };
  if (s.includes('champion') || s.includes('predict')) return { icon: I.crown, color: T.amber };
  if (s.includes('sultan') || s.includes('king') || s.includes('wizard')) return { icon: I.trophy, color: T.amber };
  return { icon: I.star, color: T.textMid };
};

const hitLabel = (type) => {
  const m = { exact: '● دقیق', goal_diff: '◆ اختلاف', winner: '✓ برنده', draw: '✓ مساوی', one_team: '▸ یک گل', miss: '✕ غلط', '': '— منتظر' };
  return m[String(type || '')] || String(type || '—');
};
const hitColor = (type) => {
  const m = { exact: T.cyan, goal_diff: T.violet, winner: T.green, draw: T.green, one_team: T.amber, miss: T.red };
  return m[String(type || '')] || T.textDim;
};

const formatStage = (stage, groupId) => {
  if (groupId) return 'گروهی';
  const m = { round_32: '۳۲ تیم', round_16: '۱/۸', quarter: '۱/۴', semi: 'نیمه‌نهایی', third_place: 'رده‌بندی', final: 'فینال' };
  return m[String(stage || '')] || String(stage || '—');
};

const relTime = (created_at) => {
  const dt = new Date(String(created_at || '').replace(' ', 'T'));
  const diff = Math.max(0, Date.now() - dt.getTime());
  const mins = Math.floor(diff / 60000);
  if (mins < 60) return `${toFa(mins)} دقیقه پیش`;
  const hrs = Math.floor(mins / 60);
  if (hrs < 24) return `${toFa(hrs)} ساعت پیش`;
  return `${toFa(Math.floor(hrs / 24))} روز پیش`;
};

function FaProfile() {
  const { me, loading: meLoading } = useMe();
  const displayName = meLoading ? '…' : (me?.display_name || 'مهمان');
  const initial = getInitial(displayName);
  const handle = me?.user_login ? '@' + me.user_login : '';

  const { data: summary, loading: sumLoading } = useApi(me?.id ? '/me/summary' : null, [me?.id]);
  const { data: badgesData, loading: badgesLoading } = useApi(me?.id ? '/me/badges' : null, [me?.id]);
  const { data: predsData, loading: predsLoading } = useApi(me?.id ? '/my-predictions' : null, [me?.id]);

  const totalPoints  = summary?.total_points   ? Number(summary.total_points)   : 0;
  const exactHits    = summary?.exact_hits      ? Number(summary.exact_hits)      : 0;
  const goalDiffHits = summary?.goal_diff_hits  ? Number(summary.goal_diff_hits)  : 0;
  const winnerHits   = summary?.winner_hits     ? Number(summary.winner_hits)     : 0;
  const streak       = summary?.streak          !== undefined ? Number(summary.streak) : 0;
  const rank         = summary?.rank_position   ? Number(summary.rank_position)   : null;
  const totalUsers   = summary?.total_users     ? Number(summary.total_users)     : 0;
  const totalScored  = summary?.total_scored    ? Number(summary.total_scored)    : 0;
  const missCount    = Math.max(0, totalScored - exactHits - goalDiffHits - winnerHits);

  const badges = Array.isArray(badgesData) ? badgesData : [];
  const earnedCount = badges.filter((b) => !!b.earned).length;

  const preds = Array.isArray(predsData) ? predsData : [];
  const jokerUsed = preds.some((p) => Number(p.is_joker) === 1);
  const jokerRemaining = jokerUsed ? 0 : 1;
  const recentScored = preds.filter((p) => p.match_status === 'finished').slice(0, 5);

  const isLoading = meLoading || sumLoading;

  return (
    <NeonBg hue="violet">
      <Screen>
        <AppBar
          title="پروفایل"
          right={
            <div style={{ display: 'flex', gap: 8 }}>
              <Glass p={9} br={12}><Ic d={I.share} size={18} color={T.text} /></Glass>
              <Glass p={9} br={12}><Ic d={I.set} size={18} color={T.text} /></Glass>
            </div>
          }
        />

        {/* Hero */}
        <Glass br={22} p={18} style={{ marginBottom: 18, textAlign: 'center', position: 'relative', overflow: 'hidden' }}>
          <div style={{ position: 'absolute', inset: 0, background: `radial-gradient(circle at 50% -30%, ${T.violet}55, transparent 60%)`, pointerEvents: 'none' }} />
          <div style={{ position: 'relative' }}>
            <div style={{ position: 'relative', display: 'inline-block', marginBottom: 12 }}>
              <div style={{
                width: 88, height: 88, borderRadius: '50%',
                background: `conic-gradient(from 0deg, ${T.cyan}, ${T.violet}, ${T.amber}, ${T.cyan})`,
                padding: 3, boxShadow: `0 0 32px ${T.cyan}55`,
              }}>
                <div style={{
                  width: '100%', height: '100%', borderRadius: '50%',
                  background: `linear-gradient(135deg, hsl(15 70% 55%), hsl(285 70% 35%))`,
                  display: 'flex', alignItems: 'center', justifyContent: 'center',
                  fontWeight: 800, fontSize: 36, color: '#fff',
                }}>
                  {me?.avatar ? (
                    <img
                      src={me.avatar}
                      alt={displayName}
                      style={{
                        width: '100%',
                        height: '100%',
                        borderRadius: '50%',
                        objectFit: 'cover',
                        display: 'block',
                      }}
                    />
                  ) : (
                    initial
                  )}
                </div>
              </div>
            </div>
            <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 22, color: T.text, letterSpacing: '-0.02em' }}>{displayName}</div>
            {/* {handle && <div style={{ fontFamily: T.body, fontSize: 12, color: T.textMid, marginTop: 2, direction: 'ltr' }}>{handle}</div>} */}

            <div style={{ display: 'flex', gap: 6, justifyContent: 'center', marginTop: 10, flexWrap: 'wrap' }}>
              {rank && <Chip color={T.amber}>رتبه {toFa(rank)} {totalUsers ? `از ${toFa(totalUsers)}` : ''}</Chip>}
              {!rank && !isLoading && <Chip color={T.textDim}>هنوز رتبه‌ای ندارید</Chip>}
            </div>

            <div style={{ display: 'flex', marginTop: 18, paddingTop: 16, borderTop: `1px solid ${T.border}` }}>
              {[
                { l: 'امتیاز',  v: isLoading ? '…' : toFa(totalPoints.toLocaleString()) },
                { l: 'دقیق',   v: isLoading ? '…' : toFa(exactHits)  },
                { l: 'استریک', v: isLoading ? '…' : `${toFa(streak)}` },
                { l: 'جوکر',   v: isLoading ? '…' : toFa(jokerRemaining) },
              ].map((s, i) => (
                <div key={i} style={{ flex: 1, textAlign: 'center', borderInlineEnd: i < 3 ? `1px solid ${T.border}` : 'none' }}>
                  <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 18, color: T.text, letterSpacing: '-0.02em', direction: 'ltr' }}>{s.v}</div>
                  <div style={{ fontFamily: T.body, fontSize: 9, color: T.textDim, fontWeight: 700, marginTop: 2 }}>{s.l}</div>
                </div>
              ))}
            </div>
          </div>
        </Glass>

        {/* Total Scored */}
        {totalScored > 0 && (
          <>
            <H kicker={`${toFa(totalScored)} پیش‌بینی نهایی شده`}>تحلیل دقت</H>
            <Glass br={18} p={16} style={{ marginBottom: 22 }}>
              {[
                { label: 'نتیجه دقیق',      count: exactHits,    color: T.cyan   },
                { label: 'اختلاف گل درست',  count: goalDiffHits, color: T.violet },
                { label: 'برنده/مساوی',     count: winnerHits,   color: T.green  },
                { label: 'پیش‌بینی غلط',    count: missCount,    color: T.red    },
              ].map((r, i) => (
                <div key={i} style={{ marginBottom: i < 3 ? 12 : 0 }}>
                  <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 6 }}>
                    <span style={{ fontFamily: T.body, fontSize: 12, fontWeight: 700, color: T.text }}>{r.label}</span>
                    <span style={{ fontFamily: T.display, fontWeight: 800, fontSize: 13, color: T.text, direction: 'ltr' }}>
                      {toFa(r.count)}<span style={{ color: T.textDim, fontWeight: 500 }}>/{toFa(totalScored)}</span>
                    </span>
                  </div>
                  <div style={{ height: 6, borderRadius: 3, background: T.bg3, overflow: 'hidden' }}>
                    <div style={{
                      height: '100%',
                      width: totalScored > 0 ? `${Math.round((r.count / totalScored) * 100)}%` : '0%',
                      background: `linear-gradient(90deg, ${r.color}, ${r.color}88)`,
                      boxShadow: `0 0 12px ${r.color}66`, borderRadius: 3,
                    }} />
                  </div>
                </div>
              ))}
            </Glass>
          </>
        )}

        {/* Bades */}
        {!badgesLoading && badges.length > 0 && (
          <>
            <H kicker={`${toFa(earnedCount)} از ${toFa(badges.length)} باز شده`} action="همه">دستاوردها</H>
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 10, marginBottom: 22 }}>
              {badges.map((b) => {
                const ui = badgeUi(b.slug);
                return (
                  <BadgeCard
                    key={b.id || b.slug}
                    name={b.name}
                    desc={b.description}
                    icon={ui.icon}
                    color={ui.color}
                    earned={!!b.earned}
                    prog={Number(b.progress_pct || 0)}
                  />
                );
              })}
            </div>
          </>
        )}

        {badgesLoading && me?.id && (
          <Glass br={14} p={14} style={{ marginBottom: 16 }}>
            <div style={{ fontFamily: T.body, fontSize: 12, color: T.textMid }}>در حال دریافت دستاوردها…</div>
          </Glass>
        )}

        {/* Last Activity */}
        {recentScored.length > 0 && (
          <>
            <H>فعالیت اخیر</H>
            <Glass br={16} p={4} style={{ marginBottom: 16 }}>
              {recentScored.map((p, i) => {
                const type = String(p.prediction_type || '');
                const hc = hitColor(type);
                const hl = hitLabel(type);
                const pts = Number(p.earned_points || 0);
                return (
                  <div key={p.id || i} style={{
                    display: 'flex', alignItems: 'center', gap: 10,
                    padding: '12px 12px',
                    borderTop: i ? `1px solid ${T.border}` : 'none',
                  }}>
                    <div style={{ display: 'flex', alignItems: 'center' }}>
                      <Crest team={{ code: p.home_team_code, name: p.home_team_name, flag_url: p.home_flag }} size={26} />
                      <div style={{ marginInlineStart: -6 }}>
                        <Crest team={{ code: p.away_team_code, name: p.away_team_name, flag_url: p.away_flag }} size={26} />
                      </div>
                    </div>
                    <div style={{ flex: 1, minWidth: 0 }}>
                      <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 12, color: T.text, direction: 'ltr', textAlign: 'right' }}>
                        {faTeamName(p.home_team_code, p.home_team_name)} {toFa(p.real_home_score ?? '?')}-{toFa(p.real_away_score ?? '?')} {faTeamName(p.away_team_code, p.away_team_name)}
                      </div>
                      <div style={{ fontFamily: T.body, fontSize: 10, color: T.textDim, marginTop: 2 }}>
                        پیش‌بینی شما <span style={{ direction: 'ltr', display: 'inline-block' }}>{toFa(p.pred_home_score)}-{toFa(p.pred_away_score)}</span>
                        {' · '}{relTime(p.updated_at || p.created_at)}
                        {Number(p.is_joker) === 1 && <span style={{ color: T.amber, marginRight: 4 }}>· جوکر</span>}
                      </div>
                    </div>
                    <div style={{ textAlign: 'center', flexShrink: 0 }}>
                      <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 14, color: hc, direction: 'ltr' }}>{pts > 0 ? '+' : ''}{toFa(pts)}</div>
                      <div style={{ fontFamily: T.body, fontSize: 9, color: hc, fontWeight: 800, marginTop: 1 }}>{hl}</div>
                    </div>
                  </div>
                );
              })}
            </Glass>
          </>
        )}

        {predsLoading && me?.id && (
          <Glass br={14} p={14} style={{ marginBottom: 12 }}>
            <div style={{ fontFamily: T.body, fontSize: 12, color: T.textMid }}>در حال دریافت پیش‌بینی‌ها…</div>
          </Glass>
        )}

        {!me && !meLoading && (
          <Glass br={14} p={14} style={{ marginBottom: 12, borderColor: T.amber + '66', background: `linear-gradient(90deg, ${T.amber}1F, ${T.amber}06)` }}>
            <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 13, color: T.text }}>برای مشاهده پروفایل وارد شوید</div>
          </Glass>
        )}
      </Screen>
    </NeonBg>
  );
}

window.FaProfile = FaProfile;
