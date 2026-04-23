import React, { useState } from 'react';
import { ShieldCheck, User, Mail, Package, AlertCircle } from 'lucide-react';

interface AddLicenseViewProps {
  onSuccess: () => void;
}

const AddLicenseView: React.FC<AddLicenseViewProps> = ({ onSuccess }) => {
  const [formData, setFormData] = useState({
    product_id: '',
    customer_email: '',
    customer_name: '',
    activation_limit: 1
  });
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [error, setError] = useState('');

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);
    setError('');

    try {
      // @ts-ignore
      const { root, nonce } = window.sgoplusSwkData;
      const response = await fetch(`${root}/licenses`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': nonce
        },
        body: JSON.stringify(formData)
      });

      const data = await response.json();
      if (response.ok) {
        onSuccess();
      } else {
        setError(data.message || 'Failed to create license');
      }
    } catch (err) {
      setError('A connection error occurred.');
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="max-w-2xl mx-auto animate-fade-in">
      <div className="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div className="premium-gradient p-8 text-white">
          <h2 className="text-2xl font-bold flex items-center gap-2">
            <ShieldCheck />
            Generate New License
          </h2>
          <p className="opacity-90 mt-1">Create a secure, unique license key for your software.</p>
        </div>

        <form onSubmit={handleSubmit} className="p-8 space-y-6">
          {error && (
            <div className="bg-red-50 text-red-600 p-4 rounded-xl flex items-center gap-3 text-sm font-medium border border-red-100">
              <AlertCircle size={18} />
              {error}
            </div>
          )}

          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div className="space-y-2">
              <label className="text-sm font-bold text-gray-700 flex items-center gap-2">
                <Package size={16} className="text-primary-500" />
                Product ID
              </label>
              <input
                required
                type="text"
                placeholder="e.g. PLUGIN-PRO"
                className="w-full p-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all outline-none"
                value={formData.product_id}
                onChange={e => setFormData({ ...formData, product_id: e.target.value })}
              />
            </div>

            <div className="space-y-2">
              <label className="text-sm font-bold text-gray-700 flex items-center gap-2">
                <ShieldCheck size={16} className="text-primary-500" />
                Activation Limit
              </label>
              <input
                required
                type="number"
                min="1"
                className="w-full p-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all outline-none"
                value={formData.activation_limit}
                onChange={e => setFormData({ ...formData, activation_limit: parseInt(e.target.value) })}
              />
            </div>
          </div>

          <div className="space-y-2">
            <label className="text-sm font-bold text-gray-700 flex items-center gap-2">
              <User size={16} className="text-primary-500" />
              Customer Name
            </label>
            <input
              type="text"
              placeholder="John Doe"
              className="w-full p-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all outline-none"
              value={formData.customer_name}
              onChange={e => setFormData({ ...formData, customer_name: e.target.value })}
            />
          </div>

          <div className="space-y-2">
            <label className="text-sm font-bold text-gray-700 flex items-center gap-2">
              <Mail size={16} className="text-primary-500" />
              Customer Email
            </label>
            <input
              required
              type="email"
              placeholder="john@example.com"
              className="w-full p-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all outline-none"
              value={formData.customer_email}
              onChange={e => setFormData({ ...formData, customer_email: e.target.value })}
            />
          </div>

          <div className="pt-4">
            <button
              disabled={isSubmitting}
              type="submit"
              className="w-full premium-gradient text-white py-4 rounded-xl font-bold text-lg shadow-lg hover:shadow-primary-500/30 transition-all disabled:opacity-50"
            >
              {isSubmitting ? 'Generating...' : 'Generate & Activate Key'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default AddLicenseView;
