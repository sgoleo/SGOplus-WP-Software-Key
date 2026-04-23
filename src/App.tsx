import React, { useState, useEffect } from 'react';
import { LayoutDashboard, Key, History, Settings, ShieldCheck, FileText, PlusCircle } from 'lucide-react';
import LicenseDataView from './components/LicenseDataView';
import LogsView from './components/LogsView';
import SettingsView from './components/SettingsView';

const App: React.FC = () => {
  const [currentView, setCurrentView] = useState('licenses');

  useEffect(() => {
    const params = new URLSearchParams(window.location.search);
    const page = params.get('page') || 'sgoplus-swk-dashboard';
    
    if (page.includes('logs')) setCurrentView('logs');
    else if (page.includes('settings')) setCurrentView('settings');
    else if (page.includes('add')) setCurrentView('add-new');
    else if (page.includes('guide')) setCurrentView('guide');
    else setCurrentView('licenses');
  }, []);

  const renderContent = () => {
    switch (currentView) {
      case 'logs':
        return <LogsView />;
      case 'settings':
        return <SettingsView />;
      case 'add-new':
        return (
          <div className="bg-white p-12 rounded-2xl shadow-sm border border-gray-100 text-center animate-fade-in">
            <PlusCircle size={48} className="mx-auto text-blue-500 mb-4" />
            <h2 className="text-2xl font-bold">Generate New License</h2>
            <p className="text-gray-500 mb-6">Start by selecting a product and customer details.</p>
            <button className="premium-gradient text-white px-6 py-2 rounded-lg">Open Generator</button>
          </div>
        );
      case 'guide':
        return (
          <div className="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 animate-fade-in">
            <h2 className="text-2xl font-bold mb-4 flex items-center gap-2">
              <FileText className="text-orange-500" />
              Integration Guide
            </h2>
            <div className="prose max-w-none text-gray-600">
              <p>To integrate <strong>Software Key+</strong> with your application, use our secure REST API endpoints.</p>
              <pre className="bg-gray-900 text-gray-100 p-4 rounded-lg mt-4">
                {`POST /wp-json/sgoplus-swk/v1/activate\n{\n  "license_key": "YOUR_KEY",\n  "domain": "example.com"\n}`}
              </pre>
            </div>
          </div>
        );
      default:
        return (
          <>
            <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
              {[
                { label: 'Active Licenses', value: '1,284', icon: <Key className="text-blue-500" />, trend: '+12%' },
                { label: 'Total Activations', value: '4,592', icon: <History className="text-purple-500" />, trend: '+5%' },
                { label: 'Revenue', value: '$42,500', icon: <ShieldCheck className="text-green-500" />, trend: '+18%' },
                { label: 'Security Health', value: '99.9%', icon: <LayoutDashboard className="text-orange-500" />, trend: 'Stable' },
              ].map((stat, i) => (
                <div key={i} className="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                  <div className="flex justify-between items-start mb-4">
                    <div className="p-2 bg-gray-50 rounded-xl">{stat.icon}</div>
                    <span className="text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded-full">{stat.trend}</span>
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
    <div className="min-h-screen bg-gray-50/50 p-8">
      <header className="mb-8 flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold tracking-tight text-gray-900 flex items-center gap-2">
            SGOplus Software Key<span className="text-blue-600">+</span>
          </h1>
          <p className="text-gray-500">Manage your software licenses with modern precision.</p>
        </div>
        <div className="flex gap-3">
          <button className="premium-gradient text-white px-4 py-2 rounded-lg shadow-lg hover:opacity-90 transition-all font-medium flex items-center gap-2">
            <ShieldCheck size={18} />
            System Secure
          </button>
        </div>
      </header>

      {renderContent()}
    </div>
  );
};

export default App;
