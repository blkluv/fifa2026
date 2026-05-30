// fa/notifs.jsx — مرکز اطلاع‌رسانی
const { T, toFa, Glass, Chip, NeonBg, Ic, I, Screen, AppBar, useApi, apiPost, useMe } = window;

function NotifItem({ icon, color, title, body, time, unread, action }) {
  return (
    <div style={{
      display: 'flex', gap: 12, padding: '14px 14px', borderRadius: 14,
      background: unread ? `${color}0F` : 'transparent',
      borderInlineStart: unread ? `2px solid ${color}` : '2px solid transparent',
      marginBottom: 4,
    }}>
      <div style={{
        width: 38, height: 38, borderRadius: 12, flexShrink: 0,
        background: `linear-gradient(135deg, ${color}33, ${color}11)`,
        border: `1px solid ${color}55`,
        display: 'flex', alignItems: 'center', justifyContent: 'center',
        boxShadow: unread ? `0 0 16px ${color}55` : 'none',
      }}><Ic d={icon} size={18} color={color} sw={2} /></div>
      <div style={{ flex: 1, minWidth: 0 }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', gap: 8 }}>
          <span style={{ fontFamily: T.display, fontWeight: 800, fontSize: 13, color: T.text }}>{title}</span>
          <span style={{ fontFamily: T.body, fontSize: 10, color: T.textDim, flexShrink: 0 }}>{time}</span>
        </div>
        <div style={{ fontFamily: T.body, fontSize: 12, color: T.textMid, marginTop: 2, lineHeight: 1.5 }}>{body}</div>
        {action && (
          <button style={{
            marginTop: 8, padding: '6px 10px', borderRadius: 8,
            background: `${color}22`, border: `1px solid ${color}66`,
            fontFamily: T.display, fontSize: 11, fontWeight: 800, color,
            cursor: 'pointer',
          }}>{action} ←</button>
        )}
      </div>
      {unread && <div style={{ width: 8, height: 8, borderRadius: 999, background: color, boxShadow: `0 0 8px ${color}`, alignSelf: 'center' }} />}
    </div>
  );
}

function FaNotifs({ go }) {
  const { me } = useMe();
  const isLoggedIn = !!me;
  const [refresh, setRefresh] = React.useState(0);
  const { data, loading, error } = useApi(isLoggedIn ? '/notifications' : null, [isLoggedIn, refresh]);
  const rows = Array.isArray(data) ? data : [];

  const relTime = (createdAt) => {
    const dt = new Date(String(createdAt).replace(' ', 'T'));
    const diff = Math.max(0, Date.now() - dt.getTime());
    const mins = Math.floor(diff / 60000);
    if (mins < 60) return `${toFa(mins)} د`;
    const hrs = Math.floor(mins / 60);
    if (hrs < 24) return `${toFa(hrs)} س`;
    const days = Math.floor(hrs / 24);
    return `${toFa(days)} روز`;
  };

  const mapType = (type) => {
    const t = String(type || '');
    if (t.includes('badge')) return { icon: I.crown, color: T.amber };
    if (t.includes('leader')) return { icon: I.trophy, color: T.amber };
    if (t.includes('lock')) return { icon: I.lock, color: T.textMid };
    if (t.includes('match')) return { icon: I.cal, color: T.violet };
    if (t.includes('predict')) return { icon: I.check, color: T.cyan };
    return { icon: I.bell, color: T.cyan };
  };

  const markAll = async () => {
    if (!isLoggedIn) return;
    try {
      await apiPost('/notifications/read', {});
      setRefresh((x) => x + 1);
    } catch (e) {}
  };

  return (
    <NeonBg>
      <Screen>
        <AppBar
          title="اطلاع‌رسانی"
          leadingBack onBack={() => go && go('home')}
          right={<button onClick={markAll} style={{ background: 'transparent', border: 'none', color: T.cyan, fontFamily: T.body, fontSize: 12, fontWeight: 800, cursor: 'pointer' }}>همه را خواندم</button>}
        />

        {!isLoggedIn && (
          <Glass br={14} p={14} style={{ marginBottom: 12, borderColor: T.amber + '66', background: `linear-gradient(90deg, ${T.amber}1F, ${T.amber}06)` }}>
            <div style={{ fontFamily: T.display, fontWeight: 800, fontSize: 13, color: T.text }}>برای مشاهده اعلان‌ها وارد شوید</div>
          </Glass>
        )}

        {error && (
          <Glass br={14} p={14} style={{ marginBottom: 12, borderColor: T.red + '66', background: `linear-gradient(90deg, ${T.red}1F, ${T.red}06)` }}>
            <div style={{ fontFamily: T.body, fontSize: 12, color: T.text }}>{error?.message || 'خطا'}</div>
          </Glass>
        )}

        {loading && (
          <Glass br={14} p={14} style={{ marginBottom: 12 }}>
            <div style={{ fontFamily: T.body, fontSize: 12, color: T.textMid }}>در حال دریافت اعلان‌ها…</div>
          </Glass>
        )}

        {!loading && isLoggedIn && rows.length === 0 && (
          <Glass br={14} p={14} style={{ marginBottom: 12 }}>
            <div style={{ fontFamily: T.body, fontSize: 12, color: T.textMid }}>اعلان خوانده‌نشده‌ای وجود ندارد.</div>
          </Glass>
        )}

        {!loading && isLoggedIn && rows.map((n) => {
          const m = mapType(n.type);
          return (
            <NotifItem
              key={n.id}
              icon={m.icon}
              color={m.color}
              unread={Number(n.is_read) === 0}
              title={String(n.title || 'اعلان')}
              body={String(n.body || '')}
              time={relTime(n.created_at)}
            />
          );
        })}
      </Screen>
    </NeonBg>
  );
}

window.FaNotifs = FaNotifs;
