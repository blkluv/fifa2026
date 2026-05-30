// notifications.jsx — Notification center + empty/loading/toast variants
const { T, Crest, Glass, Chip, H, NeonBg, Ic, I, TabBar, Screen } = window;

function NotifItem({ icon, color, title, body, time, unread, action }) {
  return (
    <div style={{
      display: 'flex', gap: 12, padding: '14px 14px',
      borderRadius: 14,
      background: unread ? `${color}0F` : 'transparent',
      borderLeft: unread ? `2px solid ${color}` : '2px solid transparent',
      marginBottom: 4,
    }}>
      <div style={{
        width: 38, height: 38, borderRadius: 12, flexShrink: 0,
        background: `linear-gradient(135deg, ${color}33, ${color}11)`,
        border: `1px solid ${color}55`,
        display: 'flex', alignItems: 'center', justifyContent: 'center',
        boxShadow: unread ? `0 0 16px ${color}55` : 'none',
      }}>
        <Ic d={icon} size={18} color={color} sw={2} />
      </div>
      <div style={{ flex: 1, minWidth: 0 }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', gap: 8 }}>
          <span style={{ fontFamily: T.display, fontWeight: 700, fontSize: 13, color: T.text }}>{title}</span>
          <span style={{ fontFamily: T.body, fontSize: 10, color: T.textDim, flexShrink: 0 }}>{time}</span>
        </div>
        <div style={{ fontFamily: T.body, fontSize: 12, color: T.textMid, marginTop: 2, lineHeight: 1.4 }}>{body}</div>
        {action && (
          <button style={{
            marginTop: 8, padding: '6px 10px', borderRadius: 8,
            background: `${color}22`, border: `1px solid ${color}66`,
            fontFamily: T.display, fontSize: 11, fontWeight: 700, color: color,
            cursor: 'pointer',
          }}>{action} →</button>
        )}
      </div>
      {unread && <div style={{ width: 8, height: 8, borderRadius: 999, background: color, boxShadow: `0 0 8px ${color}`, alignSelf: 'center' }} />}
    </div>
  );
}

function NotificationsScreen() {
  return (
    <NeonBg>
      <Screen>
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '6px 0 16px' }}>
          <div style={{ fontFamily: T.display, fontSize: 22, fontWeight: 800, color: T.text, letterSpacing: '-0.03em' }}>Notifications</div>
          <button style={{ background: 'transparent', border: 'none', color: T.cyan, fontFamily: T.body, fontSize: 12, fontWeight: 700, cursor: 'pointer' }}>Mark all read</button>
        </div>

        <div style={{ display: 'flex', gap: 6, padding: 4, background: T.card, borderRadius: 12, border: `1px solid ${T.border}`, marginBottom: 14 }}>
          {['All', 'Matches', 'Points', 'Social'].map((t, i) => (
            <div key={i} style={{
              flex: 1, textAlign: 'center', padding: '7px 4px',
              borderRadius: 8,
              background: i === 0 ? T.bg3 : 'transparent',
              color: i === 0 ? T.text : T.textMid,
              fontFamily: T.display, fontSize: 11, fontWeight: 700,
            }}>{t}{i === 0 && <span style={{ marginLeft: 4, color: T.cyan }}>· 4</span>}</div>
          ))}
        </div>

        {/* Today */}
        <div style={{ fontFamily: T.body, fontSize: 10, fontWeight: 700, color: T.textDim, letterSpacing: '0.14em', textTransform: 'uppercase', margin: '8px 0' }}>Today</div>

        <NotifItem
          icon={I.trophy} color={T.amber} unread
          title="🏆 Badge unlocked · Hot Streak"
          body="4 correct predictions in a row. You're cooking."
          action="View badge"
          time="2h"
        />
        <NotifItem
          icon={I.trend} color={T.green} unread
          title="Rank up · #247 worldwide"
          body="You jumped 14 places after the MEX-CAN result. Keep going."
          time="2h"
        />
        <NotifItem
          icon={I.check} color={T.cyan} unread
          title="Points earned · +8"
          body="Exact score on MEX 2-0 CAN. Joker not used — saved for later."
          time="2h"
        />

        {/* Earlier */}
        <div style={{ fontFamily: T.body, fontSize: 10, fontWeight: 700, color: T.textDim, letterSpacing: '0.14em', textTransform: 'uppercase', margin: '16px 0 8px' }}>Earlier this week</div>

        <NotifItem
          icon={I.bell} color={T.violet} unread
          title="Match starting soon"
          body="ARG vs NED kicks off in 30 minutes. Lock in your prediction before kickoff."
          action="Predict now"
          time="Yesterday"
        />
        <NotifItem
          icon={I.lock} color={T.textMid} 
          title="Predictions locked · Round 2"
          body="6 of 6 picks in. Average confidence: 74%. Results live tomorrow 17:00."
          time="2d"
        />
        <NotifItem
          icon={I.users} color={T.cyan}
          title="Marco joined El Grupo"
          body="Your league grew to 14. Marco predicts MEX to win Group A."
          time="3d"
        />

        {/* Toast preview */}
        <div style={{ fontFamily: T.body, fontSize: 10, fontWeight: 700, color: T.textDim, letterSpacing: '0.14em', textTransform: 'uppercase', margin: '20px 0 8px' }}>Toast preview</div>

        <Glass br={14} p={0} style={{ marginBottom: 8, padding: '12px 14px', borderColor: T.green + '66', background: `linear-gradient(90deg, ${T.green}1F, ${T.green}06)`, boxShadow: `0 0 24px ${T.green}33` }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
            <div style={{ width: 28, height: 28, borderRadius: '50%', background: T.green, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
              <Ic d={I.check} size={16} color="#06121C" sw={3} />
            </div>
            <div style={{ flex: 1 }}>
              <div style={{ fontFamily: T.display, fontWeight: 700, fontSize: 13, color: T.text }}>Prediction saved</div>
              <div style={{ fontFamily: T.body, fontSize: 11, color: T.textMid }}>ARG 2-1 NED · You can edit until kickoff.</div>
            </div>
          </div>
        </Glass>

        <Glass br={14} p={0} style={{ marginBottom: 8, padding: '12px 14px', borderColor: T.red + '66', background: `linear-gradient(90deg, ${T.red}1F, ${T.red}06)` }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
            <div style={{ width: 28, height: 28, borderRadius: '50%', background: T.red, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
              <Ic d={I.x} size={14} color="#06121C" sw={3} />
            </div>
            <div style={{ flex: 1 }}>
              <div style={{ fontFamily: T.display, fontWeight: 700, fontSize: 13, color: T.text }}>Couldn't save prediction</div>
              <div style={{ fontFamily: T.body, fontSize: 11, color: T.textMid }}>Kickoff already started · predictions are now locked.</div>
            </div>
          </div>
        </Glass>

        <Glass br={14} p={0} style={{ padding: '12px 14px', borderColor: T.amber + '66', background: `linear-gradient(90deg, ${T.amber}1F, ${T.amber}06)` }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
            <div style={{ width: 28, height: 28, borderRadius: '50%', background: T.amber, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
              <Ic d={I.bolt} size={16} color="#06121C" sw={2.4} />
            </div>
            <div style={{ flex: 1 }}>
              <div style={{ fontFamily: T.display, fontWeight: 700, fontSize: 13, color: T.text }}>Joker armed for ARG-NED</div>
              <div style={{ fontFamily: T.body, fontSize: 11, color: T.textMid }}>Your next prediction will be 2× points if it scores.</div>
            </div>
          </div>
        </Glass>
      </Screen>
      <TabBar active="home" />
    </NeonBg>
  );
}

window.NotificationsScreen = NotificationsScreen;
