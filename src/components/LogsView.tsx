import React, { useState, useMemo, useEffect, useCallback } from 'react';
import { DataViews } from '@wordpress/dataviews';
import { Spinner } from '@wordpress/components';
import { History, Activity, AlertCircle, CheckCircle, Info } from 'lucide-react';

const LogsView: React.FC = () => {
    const [data, setData] = useState<any[]>([]);
    const [isLoading, setIsLoading] = useState(true);
    const [view, setView] = useState({
        type: 'table',
        perPage: 20,
        page: 1,
        sort: { field: 'created_at', direction: 'desc' },
        filters: [],
        search: '',
    });

    const fetchLogs = useCallback(async () => {
        setIsLoading(true);
        try {
            // @ts-ignore
            const { root, nonce } = window.sgoplusSwkData;
            const response = await fetch(`${root}/logs?per_page=${view.perPage}&page=${view.page}`, {
                headers: { 'X-WP-Nonce': nonce }
            });
            const result = await response.json();
            setData(result);
        } catch (error) {
            console.error('Failed to fetch logs:', error);
        } finally {
            setIsLoading(false);
        }
    }, [view.perPage, view.page]);

    useEffect(() => {
        fetchLogs();
    }, [fetchLogs]);

    const fields = useMemo(() => [
        { id: 'created_at', label: 'Time', type: 'datetime' },
        { 
            id: 'license_key', 
            label: 'License', 
            type: 'text',
            render: ({ item }: any) => <code className="font-mono text-xs text-primary-600 bg-primary-50 px-2 py-1 rounded">{item.license_key}</code>
        },
        { 
            id: 'event_type', 
            label: 'Type', 
            type: 'text',
            render: ({ item }: any) => {
                const colors: any = {
                    success: 'text-accent-600 bg-accent-50',
                    error: 'text-red-600 bg-red-50',
                    admin: 'text-blue-600 bg-blue-50',
                    info: 'text-gray-600 bg-gray-50'
                };
                const icons: any = {
                    success: <CheckCircle size={14} />,
                    error: <AlertCircle size={14} />,
                    admin: <Activity size={14} />,
                    info: <Info size={14} />
                };
                return (
                    <span className={`flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider ${colors[item.event_type] || colors.info}`}>
                        {icons[item.event_type]}
                        {item.event_type}
                    </span>
                );
            }
        },
        { id: 'message', label: 'Event Details', type: 'text' },
        { id: 'domain', label: 'Domain', type: 'text', render: ({ item }: any) => item.domain || '-' },
        { id: 'ip_address', label: 'IP Address', type: 'text', render: ({ item }: any) => <code className="text-gray-400 text-xs">{item.ip_address}</code> },
    ], []);

    return (
        <div className="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden animate-fade-in">
            <div className="p-10 border-b border-gray-50 flex justify-between items-center bg-gray-50/30">
                <div>
                    <h2 className="text-2xl font-bold text-gray-900 flex items-center gap-3">
                        <History className="text-primary-500" />
                        Activation & Activity Logs
                    </h2>
                    <p className="text-gray-500">Real-time audit trail of all license events.</p>
                </div>
            </div>

            <div className="p-4 min-h-[500px]">
                <DataViews
                    data={data}
                    fields={fields}
                    view={view}
                    onChangeView={setView}
                    // @ts-ignore
                    layouts={{ table: { combineFields: { primary: ['license_key', 'event_type'] } } }}
                />

                {isLoading && (
                    <div className="flex justify-center p-12">
                        <Spinner />
                    </div>
                )}

                {!isLoading && data.length === 0 && (
                    <div className="p-20 text-center text-gray-400 font-medium">
                        No activity recorded yet.
                    </div>
                )}
            </div>
        </div>
    );
};

export default LogsView;
