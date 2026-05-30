// fa/rank.jsx — رتبه‌بندی زنده با سکو
const { T, toFa, Crest, Glass, Chip, NeonBg, Ic, I, Screen, AppBar, Avatar, useMe, getInitial, useApi } = window;

function UserAvatar({ avatar, initial, idx, size, glow }) {
  if (avatar) {
    return (
      <div style={{
        width: size, height: size, borderRadius: '50%',
        overflow: 'hidden',
        boxShadow: glow ? `0 0 16px ${glow}` : `0 2px 6px rgba(0,0,0,0.35)`,
        border: `2px solid rgba(255,255,255,0.12)`,
        flexShrink: 0,
      }}>
        <img src={avatar} alt="" style={{ width: '100%', height: '100%', objectFit: 'cover', display: 'block' }} />
      </div>
    );
  }
  return <Avatar seed={initial} idx={idx} size={size} glow={glow} />;
}

function PodiumStep({ rank, name, initial, pts, avatar, color, height, idx }) {
  return (
    <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', flex: 1, minWidth: 0 }}>
      <div style={{ position: 'relative', marginBottom: 8 }}>
        <UserAvatar avatar={avatar} initial={initial} idx={idx} size={rank === 1 ? 64 : 52} glow={color} />
        {rank === 1 && (
          <div style={{
            position: 'absolute', top: -14, left: '50%', transform: 'translateX(-50%)',
            width: 32, height: 32, borderRadius: '50%',
            background: `radial-gradient(circle at 30% 30%, ${T.amber}, ${T.amber}88)`,
            display: 'flex', alignItems: 'center', justifyContent: 'center',
            boxShadow: `0 0 20px ${T.amber}aa`,
          }}><Ic d={I.crown} size={16} color="#06121C" sw={2.4} /></div>
        )}
      </div>
      <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 12, color: T.text, textAlign: 'center', maxWidth: '100%', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{name}</div>
      <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 16, color: color, marginTop: 2, direction: 'ltr' }}>{toFa(pts.toLocaleString())}</div>
      <div style={{
        marginTop: 8, width: '100%', height,
        background: `linear-gradient(180deg, ${color}66, ${color}11)`,
        border: `1px solid ${color}88`,
        borderRadius: '12px 12px 0 0',
        display: 'flex', alignItems: 'flex-start', justifyContent: 'center', paddingTop: 8,
        fontFamily: T.display, fontWeight: 800, fontSize: 22, color, letterSpacing: '-0.04em', direction: 'ltr',
        boxShadow: `inset 0 0 20px ${color}33, 0 0 24px ${color}33`,
      }}>{toFa(rank)}</div>
    </div>
  );
}

function LeaderRow({ rank, name, initial, pts, exact, gd, mv, avatar, you, idx }) {
  return (
    <div style={{
      display: 'flex', alignItems: 'center', gap: 10,
      padding: '12px 12px', borderRadius: 14,
      background: you ? `linear-gradient(90deg, ${T.cyan}1F, ${T.violet}14)` : T.card,
      border: `1px solid ${you ? T.cyan + '66' : T.border}`,
      boxShadow: you ? `0 0 24px ${T.cyan}33` : 'none',
    }}>
      <div style={{ width: 32, textAlign: 'center', fontFamily: T.display, fontWeight: 800, fontSize: 14, color: T.textMid, direction: 'ltr' }}>{toFa(rank)}</div>
      <UserAvatar avatar={avatar} initial={initial} idx={idx} size={32} />
      <div style={{ flex: 1, minWidth: 0 }}>
        <div style={{ fontFamily: T.display, fontWeight: you ? 800 : 700, fontSize: 13, color: T.text, whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>{name}{you && ' · شما'}</div>
        <div style={{ display: 'flex', gap: 8, marginTop: 3 }}>
          <span style={{ fontFamily: T.body, fontSize: 10, color: T.cyan, fontWeight: 700, direction: 'ltr' }}>{toFa(exact)}× دقیق</span>
          <span style={{ fontFamily: T.body, fontSize: 10, color: T.violet, fontWeight: 700, direction: 'ltr' }}>{toFa(gd)}× اختلاف</span>
        </div>
      </div>
      <div style={{ width: 32, textAlign: 'center', direction: 'ltr' }}>
        {mv > 0 && <span style={{ fontFamily: T.body, fontSize: 10, fontWeight: 800, color: T.green }}>▲{toFa(mv)}</span>}
        {mv < 0 && <span style={{ fontFamily: T.body, fontSize: 10, fontWeight: 800, color: T.red }}>▼{toFa(Math.abs(mv))}</span>}
        {mv === 0 && <span style={{ fontFamily: T.body, fontSize: 11, fontWeight: 800, color: T.textFaint }}>—</span>}
      </div>
      <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 15, color: T.text, direction: 'ltr', minWidth: 52, textAlign: 'left' }}>{toFa(pts.toLocaleString())}</div>
    </div>
  );
}

function FaRank() {
  const { me, loading } = useMe();
  const { data: lb, loading: lbLoading } = useApi('/leaderboard?limit=500', []);
  const rows = Array.isArray(lb) ? lb : [];
  const [showAll, setShowAll] = React.useState(false);

  const normalized = rows.map((r, i) => {
    const name = String(r.display_name || '—');
    const pts = Number(r.total_points || 0);
    return {
      userId: Number(r.user_id || 0),
      rank: Number(r.rank_position || (i + 1)),
      name,
      initial: getInitial(name),
      pts,
      exact: Number(r.exact_hits || 0),
      gd: Number(r.goal_diff_hits || 0),
      mv: 0,
      avatar: String(r.avatar || ''),
      you: me?.id ? (Number(r.user_id) === Number(me.id)) : false,
      idx: i,
    };
  });

  const top3Raw = normalized.slice(0, 3);
  const top3 = [
    { ...(top3Raw[0] || { rank: 1, name: '—', initial: '—', pts: 0, avatar: '', idx: 0 }), color: T.amber,  height: 72 },
    { ...(top3Raw[1] || { rank: 2, name: '—', initial: '—', pts: 0, avatar: '', idx: 1 }), color: T.cyan,   height: 56 },
    { ...(top3Raw[2] || { rank: 3, name: '—', initial: '—', pts: 0, avatar: '', idx: 2 }), color: T.violet, height: 44 },
  ];
  const rest = normalized.slice(3);

  const PRIMARY_COUNT = 20;
  const primary = rest.slice(0, PRIMARY_COUNT);

  const youIdx = me?.id ? normalized.findIndex((r) => r.you) : -1;
  const isYouInsidePrimary = youIdx >= 3 && youIdx < 3 + PRIMARY_COUNT;
  const youSlice = (youIdx >= 3 && !isYouInsidePrimary)
    ? normalized.slice(Math.max(0, youIdx - 2), Math.min(normalized.length, youIdx + 3))
    : [];

  const shownIds = new Set();
  for (let i = 0; i < primary.length; i++) shownIds.add(primary[i].userId);
  for (let i = 0; i < youSlice.length; i++) shownIds.add(youSlice[i].userId);

  const remainingAll = rest.filter((r) => !shownIds.has(r.userId));
  const remaining = showAll ? remainingAll : [];

  return (
    <NeonBg hue="cyan">
      <Screen>
        <AppBar
          kicker="رتبه‌بندی کاربران سایت"
          title="جدول امتیازات"
          right={<Glass p={9} br={12}><Ic d={I.set} size={18} color={T.text} /></Glass>}
        />

        {!lbLoading && normalized.length === 0 && (
          <Glass br={16} p={14} style={{ marginBottom: 12 }}>
            <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 13, color: T.text }}>هنوز رتبه‌بندی موجود نیست</div>
            <div style={{ fontFamily: T.body, fontSize: 12, color: T.textMid, marginTop: 6 }}>
              بعد از ثبت اولین پیش‌بینی‌ها و محاسبه امتیازها، جدول رتبه‌بندی نمایش داده می‌شود.
            </div>
          </Glass>
        )}

        {/* سکو */}
        <Glass p={18} br={22} style={{ marginBottom: 16, position: 'relative', overflow: 'hidden' }}>
          <div style={{ position: 'absolute', inset: 0, background: `radial-gradient(circle at 50% 0%, ${T.amber}33, transparent 55%)`, pointerEvents: 'none' }} />
          <div style={{ display: 'flex', alignItems: 'flex-end', gap: 12, position: 'relative' }}>
            <PodiumStep {...top3[1]} />
            <PodiumStep {...top3[0]} />
            <PodiumStep {...top3[2]} />
          </div>
        </Glass>

        <div style={{ display: 'flex', flexDirection: 'column', gap: 8, marginBottom: 12 }}>
          {(lbLoading ? [] : primary).map((r, i) => <LeaderRow key={r.userId || i} {...r} />)}
        </div>

        {!lbLoading && youSlice.length > 0 && (
          <>
            <div style={{ display: 'flex', alignItems: 'center', gap: 8, margin: '14px 0 10px' }}>
              <div style={{ flex: 1, height: 1, background: T.border }} />
              <div style={{ fontFamily: T.body, fontSize: 10, fontWeight: 800, color: T.textDim }}>محدوده شما</div>
              <div style={{ flex: 1, height: 1, background: T.border }} />
            </div>

            <div style={{ display: 'flex', flexDirection: 'column', gap: 8, marginBottom: 12 }}>
              {youSlice.map((r, i) => <LeaderRow key={r.userId || ('you-' + i)} {...r} />)}
            </div>
          </>
        )}

        {!lbLoading && remainingAll.length > 0 && !showAll && (
          <div style={{ display: 'flex', justifyContent: 'center', marginTop: 4 }}>
            <button
              onClick={() => setShowAll(true)}
              style={{
                padding: '10px 14px', borderRadius: 12, border: `1px solid ${T.border}`,
                background: T.card, cursor: 'pointer',
                fontFamily: T.display, fontSize: 12, fontWeight: 800, color: T.text,
              }}
            >
              نمایش بقیه کاربران ({toFa(remainingAll.length)})
            </button>
          </div>
        )}

        <div style={{ display: 'flex', flexDirection: 'column', gap: 8, marginTop: 12 }}>
          {(lbLoading ? [] : remaining).map((r, i) => <LeaderRow key={r.userId || ('r-' + i)} {...r} />)}
        </div>
      </Screen>
    </NeonBg>
  );
}

window.FaRank = FaRank;
