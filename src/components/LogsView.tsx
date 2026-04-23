import React from 'react';
import { Trash2, MapPin, Monitor, Clock, User, Globe } from 'lucide-react';

const LogsView: React.FC = () => {
  const logs = [
    { time: '2026-04-21 16:22:09', license: 'SGOPLUS-PRO-KEY-01', user: 'SGOplus Group', status: 'Success', ip: '106.105.178.122', country: 'Taiwan', agent: 'Chrome/Win' },
    { time: '2026-04-21 16:22:00', license: 'SGOPLUS-PRO-KEY-01', user: 'SGOplus Group', status: 'Success', ip: '106.105.178.122', country: 'Taiwan', agent: 'Chrome/Win' },
    { time: '2026-04-21 09:00:25', license: 'SGOPLUS-DEV-KEY-02', user: 'SGOplus Group', status: 'Success', ip: '219.68.127.110', country: 'Unknown', agent: 'Mozilla/5.0' },
    { time: '2026-04-21 01:07:18', license: 'SGOPLUS-WP-SHARE-PRO', user: 'SGOplus Group', status: 'Success', ip: '219.68.127.110', country: 'Unknown', agent: 'Mozilla/5.0' },
  ];

  return (
    <div className="animate-fade-in">
      <div className="flex justify-between items-end mb-6">
        <div>
          <h2 className="text-2xl font-bold text-gray-900 flex items-center gap-2">
            <Clock className="text-blue-500" />
            Activation Logs
          </h2>
          <p className="text-gray-500 mt-1">Track every activation and verification event with detailed metadata.</p>
        </div>
        <button className="flex items-center gap-2 px-4 py-2 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors border border-red-100">
          <Trash2 size={16} />
          Clear All Logs
        </button>
      </div>

      <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse">
            <thead>
              <tr className="bg-gray-50/50 border-b border-gray-100">
                <th className="px-6 py-4 text-xs font-semibold text-gray-500 uppercase">Time</th>
                <th className="px-6 py-4 text-xs font-semibold text-gray-500 uppercase">License / Product</th>
                <th className="px-6 py-4 text-xs font-semibold text-gray-500 uppercase">User</th>
                <th className="px-6 py-4 text-xs font-semibold text-gray-500 uppercase">IP Address</th>
                <th className="px-6 py-4 text-xs font-semibold text-gray-500 uppercase">Country</th>
                <th className="px-6 py-4 text-xs font-semibold text-gray-500 uppercase">Status</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-50">
              {logs.map((log, i) => (
                <tr key={i} className="hover:bg-gray-50/30 transition-colors">
                  <td className="px-6 py-4 text-sm text-gray-600">{log.time}</td>
                  <td className="px-6 py-4 text-sm font-medium text-gray-900">{log.license}</td>
                  <td className="px-6 py-4">
                    <div className="flex items-center gap-2 text-sm text-gray-600">
                      <User size={14} className="text-gray-400" />
                      {log.user}
                    </div>
                  </td>
                  <td className="px-6 py-4">
                    <code className="text-xs bg-gray-100 px-2 py-1 rounded text-gray-600">{log.ip}</code>
                  </td>
                  <td className="px-6 py-4">
                    <div className="flex items-center gap-2 text-sm text-gray-600">
                      <Globe size={14} className="text-gray-400" />
                      {log.country}
                    </div>
                  </td>
                  <td className="px-6 py-4">
                    <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                      {log.status}
                    </span>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};

export default LogsView;
