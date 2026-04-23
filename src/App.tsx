import React, { useState, useEffect } from 'react';
import { LayoutDashboard, Key, History, Settings, ShieldCheck, FileText, PlusCircle, Globe, Github, MessageSquare } from 'lucide-react';
import LicenseDataView from './components/LicenseDataView';
import LogsView from './components/LogsView';
import SettingsView from './components/SettingsView';
import AddLicenseView from './components/AddLicenseView';
import GuideView from './components/GuideView';

const App: React.FC = () => {
  const [currentView, setCurrentView] = useState('licenses');
  const [stats, setStats] = useState({ active_licenses: 0, total_activations: 0, revenue: '$0', security_health: '99.9%' });

  useEffect(() => {
    const params = new URLSearchParams(window.location.search);
    const page = params.get('page') || 'sgoplus-swk-dashboard';
    
    if (page.includes('logs')) setCurrentView('logs');
    else if (page.includes('settings')) setCurrentView('settings');
    else if (page.includes('add')) setCurrentView('add-new');
    else if (page.includes('guide')) setCurrentView('guide');
    else setCurrentView('licenses');

    fetchStats();
  }, []);

  const fetchStats = async () => {
    try {
      // @ts-ignore
      const { root, nonce } = window.sgoplusSwkData;
      const response = await fetch(`${root}/stats`, { headers: { 'X-WP-Nonce': nonce } });
      const data = await response.json();
      setStats(data);
    } catch (e) {
      console.error('Failed to fetch stats');
    }
  };

  const renderContent = () => {
    switch (currentView) {
      case 'logs': return <LogsView />;
      case 'settings': return <SettingsView />;
      case 'add-new': return <AddLicenseView onSuccess={() => setCurrentView('licenses')} />;
      case 'guide': return <GuideView />;
      default:
        return (
          <>
            <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8 animate-slide-up">
              {[
                { label: 'Active Licenses', value: stats.active_licenses, icon: <Key className="text-primary-500" />, trend: '+12%' },
                { label: 'Total Activations', value: stats.total_activations, icon: <History className="text-purple-500" />, trend: '+5%' },
                { label: 'Estimated Value', value: stats.revenue, icon: <ShieldCheck className="text-accent-500" />, trend: '+18%' },
                { label: 'System Health', value: stats.security_health, icon: <LayoutDashboard className="text-orange-500" />, trend: 'Stable' },
              ].map((stat, i) => (
                <div key={i} className="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-all">
                  <div className="flex justify-between items-start mb-4">
                    <div className="p-2 bg-gray-50 rounded-xl">{stat.icon}</div>
                    <span className="text-xs font-medium text-accent-600 bg-accent-50 px-2 py-1 rounded-full">{stat.trend}</span>
                  </div>
                  <h3 className="text-sm font-medium text-gray-500">{stat.label}</h3>
                  <p className="text-2xl font-bold text-gray-900">{stat.value}</p>
                </div>
              ))}
            </div>
            <LicenseDataView />
          </>
        );
    }
  };

  return (
    <div className="flex gap-8 p-8 max-w-[1600px] mx-auto">
      {/* Main Content */}
      <div className="flex-1 min-w-0">
        <header className="mb-8 flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold tracking-tight text-gray-900 flex items-center gap-2">
              SGOplus Software Key<span className="text-primary-600">+</span>
            </h1>
            <p className="text-gray-500">Manage your software licenses with modern precision.</p>
          </div>
          <div className="flex gap-3">
            <button 
              onClick={() => setCurrentView('add-new')}
              className="premium-gradient text-white px-6 py-2 rounded-xl shadow-lg hover:shadow-primary-500/20 transition-all font-medium flex items-center gap-2"
            >
              <PlusCircle size={18} />
              New License
            </button>
          </div>
        </header>

        {renderContent()}
      </div>

      {/* Sidebar - Developer Hub */}
      <aside className="w-[320px] shrink-0 space-y-6">
        <div className="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm text-center sticky top-8">
          <h3 className="text-xl font-bold text-gray-900 mb-6">Developer Hub</h3>
          
          <div className="mb-6 relative inline-block">
            <div className="w-24 h-24 rounded-full overflow-hidden border-4 border-gray-50 shadow-md mx-auto">
              <img 
                src="https://secure.gravatar.com/avatar/ad516503a11cd5ca435acc9bb6523536?s=200" 
                alt="SGOplus" 
              />
            </div>
            <div className="absolute -bottom-1 -right-1 bg-accent-500 text-white p-1 rounded-full border-2 border-white">
              <ShieldCheck size={14} />
            </div>
          </div>

          <p className="font-extrabold text-xl text-gray-900 mb-1">SGOplus Group</p>
          <p className="text-sm text-gray-500 mb-8 leading-relaxed">
            Premium WordPress Solutions<br />Crafted with Excellence
          </p>

          <div className="space-y-3 text-left">
            {[
              { label: 'Official Website', icon: <Globe size={18} />, url: 'https://sgoplus.one', color: 'text-primary-600', bg: 'bg-primary-50' },
              { label: 'GitHub Profile', icon: <Github size={18} />, url: 'https://github.com/sgoleo', color: 'text-gray-900', bg: 'bg-gray-50' },
              { label: 'Join Discord', icon: <MessageSquare size={18} />, url: 'https://discord.gg/WnkEKkZYFY', color: 'text-white', bg: 'bg-[#5865F2]' },
            ].map((link, i) => (
              <a 
                key={i}
                href={link.url}
                target="_blank"
                rel="noopener noreferrer"
                className={`flex items-center gap-3 p-3 rounded-xl font-semibold transition-all hover:scale-[1.02] ${link.bg} ${link.color}`}
              >
                {link.icon}
                {link.label}
              </a>
            ))}
          </div>

          <hr className="my-8 border-gray-50" />

          <div className="text-xs text-gray-400 font-medium">
            <p>SGOplus Software Key <strong>v1.0.9</strong></p>
            <p className="mt-1">© 2026 SGOplus</p>
          </div>
        </div>
      </aside>
    </div>
  );
};

export default App;

