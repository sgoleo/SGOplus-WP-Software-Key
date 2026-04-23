import React from 'react';
import { LayoutDashboard, Key, History, Settings, ShieldCheck } from 'lucide-react';
import LicenseDataView from './components/LicenseDataView';

const App: React.FC = () => {
  return (
    <div className="min-h-screen bg-gray-50/50 p-8 animate-fade-in">
      <header className="mb-8 flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold tracking-tight text-gray-900">SGOplus Software Key</h1>
          <p className="text-gray-500">Manage your software licenses with modern precision.</p>
        </div>
        <div className="flex gap-3">
          <button className="premium-gradient text-white px-4 py-2 rounded-lg shadow-lg hover:opacity-90 transition-all font-medium flex items-center gap-2">
            <ShieldCheck size={18} />
            System Secure
          </button>
        </div>
      </header>

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
    </div>
  );
};

export default App;
