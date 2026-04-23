import React, { useState } from 'react';
import { BookOpen, Star, Zap, Code, Shield } from 'lucide-react';

const GuideView: React.FC = () => {
  const [lang, setLang] = useState<'zh' | 'jp' | 'en'>('zh');

  const content = {
    zh: {
      title: 'SGOplus 使用指南',
      subtitle: '專業的軟體授權管理解決方案',
      intro_title: '插件介紹',
      intro_text: 'SGOplus Software Key 是一款專為開發者設計的高性能授權管理插件。它提供了安全的 REST API、即時啟動日誌以及現代化的管理介面。',
      step1_title: '1. 生成授權碼',
      step1_text: '在「Licenses」頁面點擊「New License」，輸入產品 ID 與客戶資訊即可生成。',
      step2_title: '2. 整合 API',
      step2_text: '使用後端的 REST API 端點進行授權驗證。',
      step3_title: '3. 監控狀態',
      step3_text: '透過「Activation Logs」即時查看授權啟動情形。',
    },
    jp: {
      title: 'SGOplus ガイド',
      subtitle: 'プロフェッショナルなソフトウェアライセンス管理',
      intro_title: 'プラグイン紹介',
      intro_text: 'SGOplus Software Key は、開発者向けに設計された高性能なライセンス管理プラグインです。安全な REST API、リアルタイムログ、モダンな管理インターフェースを提供します。',
      step1_title: '1. キーの生成',
      step1_text: '「Licenses」ページで「New License」をクリックし、製品IDと顧客情報を入力して生成します。',
      step2_title: '2. API 統合',
      step2_text: 'バックエンドの REST API エンドポイントを使用してライセンス認証を行います。',
      step3_title: '3. ステータス監視',
      step3_text: '「Activation Logs」を通じて、ライセンスのアクティベーション状況をリアルタイムで確認できます。',
    },
    en: {
      title: 'SGOplus Usage Guide',
      subtitle: 'Professional Software License Solutions',
      intro_title: 'Introduction',
      intro_text: 'SGOplus Software Key is a high-performance license management plugin designed for developers. It features a secure REST API, real-time logs, and a modern dashboard.',
      step1_title: '1. Generate Keys',
      step1_text: 'Go to the "Licenses" page and click "New License". Enter Product ID and customer info to generate a key.',
      step2_title: '2. API Integration',
      step2_text: 'Integrate the REST API endpoints into your application for secure validation.',
      step3_title: '3. Monitoring',
      step3_text: 'Use the "Activation Logs" to track license activation events in real-time.',
    }
  };

  const current = content[lang];

  return (
    <div className="max-w-4xl mx-auto animate-fade-in">
      {/* Header */}
      <div className="premium-gradient p-12 rounded-[2rem] text-white text-center shadow-xl mb-10">
        <div className="bg-white/20 w-16 h-16 rounded-2xl backdrop-blur-md flex items-center justify-center mx-auto mb-6">
          <BookOpen size={32} />
        </div>
        <h1 className="text-4xl font-extrabold mb-2">{current.title}</h1>
        <p className="opacity-90 text-lg mb-8">{current.subtitle}</p>
        
        <div className="flex justify-center gap-2">
          {[
            { id: 'zh', label: '繁體中文' },
            { id: 'jp', label: '日本語' },
            { id: 'en', label: 'English' }
          ].map(l => (
            <button
              key={l.id}
              onClick={() => setLang(l.id as any)}
              className={`px-6 py-2 rounded-full font-bold transition-all backdrop-blur-md ${lang === l.id ? 'bg-white text-primary-600 shadow-lg' : 'bg-white/10 hover:bg-white/20'}`}
            >
              {l.label}
            </button>
          ))}
        </div>
      </div>

      <div className="space-y-8">
        {/* Intro */}
        <section className="bg-white p-10 rounded-3xl border border-gray-100 shadow-sm border-l-[6px] border-l-primary-500">
          <h2 className="text-2xl font-bold text-primary-600 flex items-center gap-3 mb-4">
            <Star />
            {current.intro_title}
          </h2>
          <p className="text-gray-600 leading-relaxed text-lg">
            {current.intro_text}
          </p>
        </section>

        {/* Steps Grid */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          {[
            { title: current.step1_title, text: current.step1_text, icon: <Zap className="text-orange-500" /> },
            { title: current.step2_title, text: current.step2_text, icon: <Code className="text-blue-500" /> },
            { title: current.step3_title, text: current.step3_text, icon: <Shield className="text-accent-500" /> }
          ].map((step, i) => (
            <div key={i} className="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm hover:translate-y-[-4px] transition-all">
              <div className="bg-gray-50 w-12 h-12 rounded-2xl flex items-center justify-center mb-6">
                {step.icon}
              </div>
              <h3 className="text-xl font-bold mb-3">{step.title}</h3>
              <p className="text-gray-500 leading-relaxed">{step.text}</p>
            </div>
          ))}
        </div>

        {/* API Reference */}
        <section className="bg-gray-900 rounded-[2rem] p-10 text-white shadow-2xl">
          <h2 className="text-2xl font-bold text-primary-400 flex items-center gap-3 mb-6">
            <Code />
            REST API Reference
          </h2>
          <div className="space-y-6">
            <div className="border-b border-white/10 pb-6">
              <code className="bg-primary-500/20 text-primary-300 px-4 py-2 rounded-lg font-mono mb-4 inline-block border border-primary-500/30">
                POST /sgoplus-swk/v1/activate
              </code>
              <p className="text-gray-400 text-sm">Activate a license on a specific domain.</p>
            </div>
            <pre className="bg-black/30 p-6 rounded-2xl font-mono text-sm overflow-x-auto text-primary-200 border border-white/5">
{`{
  "license_key": "SWK-XXXX-XXXX-XXXX",
  "domain": "yourdomain.com",
  "product_id": "optional-id"
}`}
            </pre>
          </div>
        </section>
      </div>

      <footer className="mt-16 text-center text-gray-400 pb-12 font-medium">
        © 2026 SGOplus Group • <a href="https://sgoplus.one" className="text-primary-500 hover:underline">Support Center</a>
      </footer>
    </div>
  );
};

export default GuideView;
