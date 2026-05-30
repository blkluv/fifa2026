const { T, toFa, faTeamName, Crest, Glass, Chip, NeonBg, Ic, I, Screen, AppBar, useApi, apiPost, useMe } = window;

const RULE_DEFS = [
  { key: 'exact_score',      label: 'نتیجه دقیق',          c: T.cyan   },
  { key: 'goal_difference',  label: 'اختلاف گل درست',       c: T.violet },
  { key: 'correct_draw',     label: 'مساوی درست',           c: T.green  },
  { key: 'correct_winner',   label: 'برنده درست',           c: T.green  },
  { key: 'one_team_correct', label: 'گل یک تیم درست',       c: T.amber  },
];

function ScoringRulesBox() {
  const { data: rulesMap, loading } = useApi('/scoring-rules', []);
  return (
    <div style={{ marginTop: 18, padding: 14, borderRadius: 14, background: T.bg3, border: `1px solid ${T.border}` }}>
      <div style={{ fontFamily: T.body, fontSize: 10, fontWeight: 700, color: T.textDim, marginBottom: 10 }}>قوانین امتیازدهی</div>
      {loading && <div style={{ fontFamily: T.body, fontSize: 11, color: T.textDim }}>در حال دریافت…</div>}
      {!loading && RULE_DEFS.map((r, i) => {
        const pts = rulesMap && rulesMap[r.key] !== undefined ? Number(rulesMap[r.key]) : null;
        if (pts === null) return null;
        return (
          <div key={r.key} style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '6px 0', borderTop: i ? `1px solid ${T.border}` : 'none' }}>
            <div style={{ width: 6, height: 6, borderRadius: 999, background: r.c, boxShadow: `0 0 8px ${r.c}`, flexShrink: 0 }} />
            <div style={{ flex: 1, fontFamily: T.body, fontSize: 12, color: T.text }}>{r.label}</div>
            <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 14, color: r.c, direction: 'ltr' }}>{pts > 0 ? '+' : ''}{toFa(pts)}</div>
          </div>
        );
      })}
    </div>
  );
}

function ScoreDial({ value, onChange, color }) {
  return (
    <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 6 }}>
      <button onClick={() => onChange(value + 1)} aria-label="افزایش" style={{
        width: 36, height: 28, borderRadius: 10,
        background: T.card, border: `1px solid ${T.border}`,
        color: T.text, fontSize: 18, fontWeight: 800, cursor: 'pointer',
        display: 'flex', alignItems: 'center', justifyContent: 'center',
      }}>+</button>
      <div style={{
        width: 88, height: 88, borderRadius: 22,
        background: `radial-gradient(circle at 30% 20%, ${color}22, ${T.bg2})`,
        border: `2px solid ${color}66`,
        boxShadow: `0 0 32px ${color}55, inset 0 0 24px ${color}22`,
        display: 'flex', alignItems: 'center', justifyContent: 'center',
        fontFamily: T.display, fontSize: 52, fontWeight: 800, color: '#fff',
        letterSpacing: '-0.06em', direction: 'ltr',
        textShadow: `0 0 18px ${color}88`,
      }}>{toFa(value)}</div>
      <button onClick={() => onChange(Math.max(0, value - 1))} aria-label="کاهش" style={{
        width: 36, height: 28, borderRadius: 10,
        background: T.card, border: `1px solid ${T.border}`,
        color: T.text, fontSize: 18, fontWeight: 800, cursor: 'pointer',
        display: 'flex', alignItems: 'center', justifyContent: 'center',
      }}>−</button>
    </div>
  );
}

function FaPredict({ go, matchId }) {
  const [home, setHome] = React.useState(2);
  const [away, setAway] = React.useState(1);
  const [joker, setJoker] = React.useState(false);
  const [state, setState] = React.useState('edit');
  const [err, setErr] = React.useState('');
  const [saved, setSaved] = React.useState(null);
  const { me } = useMe();
  const isLoggedIn = !!me;

  const { data: matches } = useApi('/matches', []);
  const fallbackMatchId = React.useMemo(() => {
    if (!Array.isArray(matches)) return null;
    const upcoming = matches.filter((m) => m && m.status === 'scheduled').slice().sort((a, b) => String(a.kickoff_at).localeCompare(String(b.kickoff_at)));
    const unlocked = upcoming.find((m) => !m.locked);
    return unlocked ? unlocked.id : (upcoming[0]?.id || null);
  }, [matches]);

  const activeMatchId = matchId || fallbackMatchId;
  const { data: match, loading: matchLoading } = useApi(activeMatchId ? `/matches/${activeMatchId}` : null, [activeMatchId]);
  const locked = !!match?.locked;

  React.useEffect(() => {
    if (!activeMatchId || !Array.isArray(matches)) return;
    const row = matches.find((m) => Number(m.id) === Number(activeMatchId));
    if (!row) return;
    if (typeof row.pred_home_score === 'number' && typeof row.pred_away_score === 'number') {
      setHome(row.pred_home_score);
      setAway(row.pred_away_score);
    }
  }, [activeMatchId, matches]);

  const submit = async () => {
    setErr('');
    setSaved(null);
    if (!isLoggedIn) {
      setErr('برای ثبت پیش‌بینی باید وارد شوید.');
      return;
    }
    if (!activeMatchId) {
      setErr('مسابقه‌ای برای ثبت پیش‌بینی پیدا نشد.');
      return;
    }
    if (locked) {
      setErr('زمان ثبت پیش‌بینی به پایان رسیده است.');
      return;
    }

    setState('submitting');
    try {
      const res = await apiPost('/predict', { match_id: activeMatchId, home_score: home, away_score: away, is_joker: joker });
      setSaved(res || null);
      setState('done');
    } catch (e) {
      setState('edit');
      setErr(e?.message || 'خطا');
    }
  };

  return (
    <NeonBg hue="violet">
      <Screen>
        <AppBar
          kicker={match ? (match.stage || 'مرحله') : '—'}
          title="ثبت پیش‌بینی"
          leadingBack
          onBack={() => go && go('home')}
          right={<Chip color={T.amber}>{activeMatchId ? `بازی ${toFa(String(activeMatchId))}` : '—'}</Chip>}
        />

        <Glass br={24} p={18} glow={state === 'done' ? T.green : T.violet} style={{ marginBottom: 16, position: 'relative', overflow: 'hidden' }}>
          <div style={{ position: 'absolute', inset: 0, background: `radial-gradient(circle at 50% -10%, ${T.cyan}22, transparent 60%)`, pointerEvents: 'none' }} />

          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', position: 'relative' }}>
            <div>
              <div style={{ fontFamily: T.body, fontSize: 10, fontWeight: 700, color: T.cyan }}>{match?.group_id ? 'مرحله گروهی' : (match?.stage || '—')}</div>
              <div style={{ fontFamily: T.display, fontSize: 13, fontWeight: 700, color: T.textMid, marginTop: 2 }}>{match?.venue || '—'}</div>
            </div>
            <div style={{ textAlign: 'left' }}>
              <div style={{ fontFamily: T.body, fontSize: 9, fontWeight: 700, color: T.textDim }}>کیک‌آف</div>
              <div style={{ fontFamily: T.display, fontSize: 13, fontWeight: 800, color: T.text, direction: 'ltr' }}>{match?.kickoff_at ? String(match.kickoff_at) : '—'}</div>
            </div>
          </div>

          {/* تیم‌ها و امتیاز */}
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginTop: 20, marginBottom: 20, position: 'relative', direction: 'ltr' }}>
            <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 8, width: 70 }}>
              <Crest team={match ? { code: match.home_team_code, name: match.home_team_name, flag_url: match.home_flag } : { code: '—' }} size={56} glow />
              <div style={{ fontFamily: T.display, fontSize: 13, fontWeight: 800, color: T.text }}>{match ? faTeamName(match.home_team_code, match.home_team_name) : '—'}</div>
            </div>
            <ScoreDial value={home} onChange={setHome} color={T.cyan} />
            <div style={{ fontFamily: T.display, fontSize: 32, fontWeight: 800, color: T.textFaint, letterSpacing: '-0.05em' }}>–</div>
            <ScoreDial value={away} onChange={setAway} color={T.violet} />
            <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 8, width: 70 }}>
              <Crest team={match ? { code: match.away_team_code, name: match.away_team_name, flag_url: match.away_flag } : { code: '—' }} size={56} glow />
              <div style={{ fontFamily: T.display, fontSize: 13, fontWeight: 800, color: T.text }}>{match ? faTeamName(match.away_team_code, match.away_team_name) : '—'}</div>
            </div>
          </div>

          {/* انتخاب نتیجه */}
          <div style={{ display: 'flex', gap: 6, padding: 4, background: T.bg3, borderRadius: 12, border: `1px solid ${T.border}` }}>
            {[
              { label: 'برد میزبان', active: home > away, c: T.cyan },
              { label: 'تساوی', active: home === away, c: T.textMid },
              { label: 'برد میهمان', active: away > home, c: T.violet },
            ].map((o, i) => (
              <div key={i} style={{
                flex: 1, textAlign: 'center', padding: '8px 4px', borderRadius: 8,
                background: o.active ? `linear-gradient(135deg, ${o.c}33, ${o.c}11)` : 'transparent',
                border: o.active ? `1px solid ${o.c}66` : '1px solid transparent',
                fontFamily: T.body, fontSize: 11, fontWeight: 800,
                color: o.active ? '#fff' : T.textDim,
              }}>{o.label}</div>
            ))}
          </div>

          {/* جوکر */}
          <div onClick={() => setJoker(!joker)} style={{
            marginTop: 14, padding: '12px 14px', cursor: 'pointer',
            background: joker ? `linear-gradient(135deg, ${T.amber}22, ${T.amber}08)` : T.bg3,
            border: `1px solid ${joker ? T.amber + '88' : T.border}`,
            borderRadius: 14,
            display: 'flex', alignItems: 'center', gap: 12,
            boxShadow: joker ? `0 0 24px ${T.amber}44` : 'none',
          }}>
            <div style={{
              width: 36, height: 36, borderRadius: 10,
              background: joker ? T.amber : T.cardSolid,
              display: 'flex', alignItems: 'center', justifyContent: 'center',
              boxShadow: joker ? `0 0 18px ${T.amber}88` : 'none',
            }}><Ic d={I.bolt} size={20} color={joker ? '#06121C' : T.amber} sw={2.2} /></div>
            <div style={{ flex: 1 }}>
              <div style={{ fontFamily: T.display, fontSize: 13, fontWeight: 800, color: T.text }}>جوکر · امتیاز ۲ برابر</div>
              <div style={{ fontFamily: T.body, fontSize: 11, color: T.textDim, marginTop: 1 }}>{joker ? 'فعال شد — آخرین جوکر تورنمنت' : `${toFa('۱')} عدد باقی‌مانده · با احتیاط استفاده کنید`}</div>
            </div>
            <div style={{
              width: 40, height: 24, borderRadius: 999,
              background: joker ? T.amber : T.border,
              position: 'relative', transition: 'background .2s', flexShrink: 0,
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

        {err && (
          <Glass p={14} br={14} style={{ marginBottom: 12, borderColor: T.red + '66', background: `linear-gradient(90deg, ${T.red}1F, ${T.red}06)` }}>
            <div style={{ fontFamily: T.body, fontSize: 12, color: T.text }}>{err}</div>
          </Glass>
        )}

        {/* دکمه ثبت / وضعیت */}
        {state === 'edit' && (
          <button onClick={submit} disabled={!activeMatchId || locked || !isLoggedIn || matchLoading} style={{
            width: '100%', padding: 16, borderRadius: 18, border: 'none', cursor: 'pointer',
            background: `linear-gradient(135deg, ${T.cyan}, ${T.violet})`,
            fontFamily: T.display, fontSize: 15, fontWeight: 800, color: '#06121C',
            boxShadow: `0 12px 30px ${T.cyan}55, 0 0 40px ${T.violet}44, inset 0 -2px 0 rgba(0,0,0,0.15)`,
            opacity: (!activeMatchId || locked || !isLoggedIn || matchLoading) ? 0.6 : 1,
          }}>
            ثبت {toFa(home)}–{toFa(away)} {joker && '· ۲×'} ←
          </button>
        )}
        {state === 'submitting' && (
          <Glass p={16} br={18} style={{ textAlign: 'center' }}>
            <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 14, color: T.cyan }}>در حال ثبت…</div>
          </Glass>
        )}
        {state === 'done' && (
          <Glass br={18} p={16} glow={T.green} style={{ background: `linear-gradient(135deg, ${T.green}1F, ${T.green}0A)`, borderColor: T.green + '55' }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
              <div style={{ width: 42, height: 42, borderRadius: '50%', background: T.green,
                display: 'flex', alignItems: 'center', justifyContent: 'center', boxShadow: `0 0 24px ${T.green}88` }}>
                <Ic d={I.check} size={22} color="#06121C" sw={3} />
              </div>
              <div style={{ flex: 1 }}>
                <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 14, color: T.text }}>پیش‌بینی ثبت شد</div>
                <div style={{ fontFamily: T.body, fontSize: 11, color: T.textMid, direction: 'ltr', textAlign: 'right' }}>{toFa(home)}–{toFa(away)} · {joker ? 'جوکر فعال (۲×)' : 'استاندارد'}</div>
                {saved?.prediction_id && (
                  <div style={{ fontFamily: T.body, fontSize: 11, color: T.textDim, marginTop: 4, direction: 'ltr', textAlign: 'right' }}>
                    شناسه: #{toFa(String(saved.prediction_id))} · match #{toFa(String(saved.match_id || activeMatchId))}
                  </div>
                )}
              </div>
              <button onClick={() => setState('edit')} style={{ background: 'transparent', border: `1px solid ${T.border}`, color: T.text, borderRadius: 10, padding: '6px 12px', fontFamily: T.body, fontWeight: 700, fontSize: 11, cursor: 'pointer' }}>ویرایش</button>
            </div>
          </Glass>
        )}

        {/* قوانین امتیازدهی — از دیتابیس */}
        <ScoringRulesBox />
      </Screen>
    </NeonBg>
  );
}

window.FaPredict = FaPredict;
