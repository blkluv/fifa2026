// fa/app.jsx — روتر تب‌ها + لایوت ریسپانسیو
const { T, TabBar, FaHome, FaPredict, FaRank, FaMatches, FaProfile, FaNotifs } = window;

const SCREENS = {
  home:    { tab: 'home',    Comp: FaHome },
  predict: { tab: 'predict', Comp: FaPredict },
  rank:    { tab: 'rank',    Comp: FaRank },
  matches: { tab: 'matches', Comp: FaMatches },
  me:      { tab: 'me',      Comp: FaProfile },
  notifs:  { tab: 'home',    Comp: FaNotifs },
};

function App() {
  const [route, setRoute] = React.useState('home');
  const [params, setParams] = React.useState({ matchId: null });
  const { Comp, tab } = SCREENS[route] || SCREENS.home;

  const go = (nextRoute, nextParams) => {
    setRoute(nextRoute);
    if (nextParams && typeof nextParams === 'object') {
      setParams((p) => ({ ...p, ...nextParams }));
    }
  };

  return (
    <div className="wp26-shell">
      <div className="wp26-app">
        <Comp go={go} matchId={params.matchId} />
        <TabBar active={tab} onChange={(id) => go(id)} />
      </div>
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('root')).render(<App />);
