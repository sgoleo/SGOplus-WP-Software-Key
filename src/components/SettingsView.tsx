import React from 'react';
import { Save, Shield, Bell, Zap, Database } from 'lucide-react';

const SettingsView: React.FC = () => {
  return (
    <div className="animate-fade-in space-y-8">
      <div>
        <h2 className="text-2xl font-bold text-gray-900">General Settings</h2>
        <p className="text-gray-500 mt-1">Configure your license management engine parameters.</p>
      </div>

      <div className="grid grid-cols-1 gap-6">
        {/* Security Section */}
        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
          <div className="flex items-center gap-3 mb-6">
            <div className="p-2 bg-blue-50 rounded-lg"><Shield className="text-blue-600" /></div>
            <h3 className="text-lg font-semibold">Security & Verification</h3>
          </div>
          
          <div className="space-y-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="font-medium text-gray-900">Enforce Domain Binding</p>
                <p className="text-sm text-gray-500">Only allow activations from registered domains.</p>
              </div>
              <div className="h-6 w-11 bg-blue-600 rounded-full relative"><div className="absolute right-1 top-1 w-4 h-4 bg-white rounded-full"></div></div>
            </div>
            
            <div className="flex items-center justify-between">
              <div>
                <p className="font-medium text-gray-900">Enable Heartbeat</p>
                <p className="text-sm text-gray-500">Clients must check-in every 24 hours.</p>
              </div>
              <div className="h-6 w-11 bg-gray-200 rounded-full relative"><div className="absolute left-1 top-1 w-4 h-4 bg-white rounded-full"></div></div>
            </div>
          </div>
        </div>

        {/* API Section */}
        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
          <div className="flex items-center gap-3 mb-6">
            <div className="p-2 bg-purple-50 rounded-lg"><Zap className="text-purple-600" /></div>
            <h3 className="text-lg font-semibold">REST API Configuration</h3>
          </div>
          
          <div className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">API Root Slug</label>
              <input type="text" defaultValue="sgoplus-swk/v1" className="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Secret Salt</label>
              <input type="password" value="************************" readOnly className="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg" />
            </div>
          </div>
        </div>
      </div>

      <div className="flex justify-end">
        <button className="premium-gradient text-white px-8 py-3 rounded-xl shadow-lg hover:shadow-xl transition-all font-bold flex items-center gap-2">
          <Save size={20} />
          Save Settings
        </button>
      </div>
    </div>
  );
};

export default SettingsView;
