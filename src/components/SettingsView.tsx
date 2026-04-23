import React, { useState, useEffect } from 'react';
import { Settings, Shield, Bell, Save, Loader2, Key } from 'lucide-react';

const SettingsView: React.FC = () => {
  const [settings, setSettings] = useState({
    enable_logs: 'yes',
    auto_expire_days: 365,
    api_secret: ''
  });
  const [isLoading, setIsLoading] = useState(true);
  const [isSaving, setIsSaving] = useState(false);

  useEffect(() => {
    fetchSettings();
  }, []);

  const fetchSettings = async () => {
    try {
      // @ts-ignore
      const { root, nonce } = window.sgoplusSwkData;
      const response = await fetch(`${root}/settings`, { headers: { 'X-WP-Nonce': nonce } });
      const data = await response.json();
      setSettings(data);
    } catch (e) {
      console.error('Failed to fetch settings');
    } finally {
      setIsLoading(false);
    }
  };

  const handleSave = async () => {
    setIsSaving(true);
    try {
      // @ts-ignore
      const { root, nonce } = window.sgoplusSwkData;
      await fetch(`${root}/settings`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
        body: JSON.stringify(settings)
      });
      alert('Settings saved successfully!');
    } catch (e) {
      alert('Failed to save settings');
    } finally {
      setIsSaving(false);
    }
  };

  if (isLoading) return <div className="flex justify-center p-20"><Loader2 className="animate-spin text-primary-500" size={48} /></div>;

  return (
    <div className="max-w-4xl mx-auto space-y-8 animate-fade-in">
      <div className="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
        <div className="p-10 border-b border-gray-50 flex justify-between items-center bg-gray-50/30">
          <div>
            <h2 className="text-2xl font-bold text-gray-900 flex items-center gap-3">
              <Settings className="text-primary-500" />
              Global Settings
            </h2>
            <p className="text-gray-500">Configure your software licensing engine behavior.</p>
          </div>
          <button
            onClick={handleSave}
            disabled={isSaving}
            className="premium-gradient text-white px-8 py-3 rounded-2xl shadow-lg hover:shadow-primary-500/20 transition-all font-bold flex items-center gap-2"
          >
            {isSaving ? <Loader2 className="animate-spin" size={20} /> : <Save size={20} />}
            Save Changes
          </button>
        </div>

        <div className="p-10 space-y-10">
          {/* Section 1: General */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div className="col-span-1">
              <h3 className="text-lg font-bold flex items-center gap-2 text-gray-800">
                <Bell size={20} className="text-orange-500" />
                Audit & Logs
              </h3>
              <p className="text-sm text-gray-500 mt-2">Control how activity is tracked across the system.</p>
            </div>
            <div className="col-span-2 space-y-6">
              <div className="bg-gray-50 p-6 rounded-2xl border border-gray-100">
                <label className="flex items-center justify-between cursor-pointer">
                  <div>
                    <p className="font-bold text-gray-900">Enable Activity Logging</p>
                    <p className="text-sm text-gray-500">Store all activation attempts and validation errors.</p>
                  </div>
                  <select 
                    value={settings.enable_logs}
                    onChange={e => setSettings({ ...settings, enable_logs: e.target.value })}
                    className="bg-white p-2 rounded-lg border border-gray-200 outline-none focus:ring-2 focus:ring-primary-500/20"
                  >
                    <option value="yes">Enabled (Recommended)</option>
                    <option value="no">Disabled</option>
                  </select>
                </label>
              </div>
            </div>
          </div>

          <hr className="border-gray-50" />

          {/* Section 2: Security */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div className="col-span-1">
              <h3 className="text-lg font-bold flex items-center gap-2 text-gray-800">
                <Shield size={20} className="text-accent-500" />
                Security & API
              </h3>
              <p className="text-sm text-gray-500 mt-2">Manage API authentication and data lifecycle.</p>
            </div>
            <div className="col-span-2 space-y-6">
              <div className="space-y-2">
                <label className="text-sm font-bold text-gray-700">API Secret Key</label>
                <div className="relative">
                  <input 
                    type="password"
                    readOnly
                    value={settings.api_secret}
                    className="w-full p-4 pr-12 rounded-2xl bg-gray-50 border border-gray-200 font-mono text-sm"
                  />
                  <Key size={18} className="absolute right-4 top-4 text-gray-400" />
                </div>
                <p className="text-xs text-gray-400">Used for server-to-server communication. Do not share.</p>
              </div>

              <div className="space-y-2">
                <label className="text-sm font-bold text-gray-700">Auto-Expire Unused Keys (Days)</label>
                <input 
                  type="number"
                  value={settings.auto_expire_days}
                  onChange={e => setSettings({ ...settings, auto_expire_days: parseInt(e.target.value) })}
                  className="w-full p-4 rounded-2xl bg-gray-50 border border-gray-200 outline-none focus:ring-2 focus:ring-primary-500/20"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default SettingsView;
