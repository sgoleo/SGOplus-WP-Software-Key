import React, { useState, useMemo, useEffect, useCallback } from 'react';
import { DataViews, filterData, sortData } from '@wordpress/dataviews';
import { useSelect } from '@wordpress/data';
import { Spinner, Button, Notice } from '@wordpress/components';

/**
 * License DataView Component
 */
const LicenseDataView: React.FC = () => {
    // 1. State for Data and View
    const [data, setData] = useState<any[]>([]);
    const [isLoading, setIsLoading] = useState(true);
    const [view, setView] = useState(() => {
        // Persistence: Try to load from localStorage
        const savedView = localStorage.getItem('sgoplus_swk_view_settings');
        return savedView ? JSON.parse(savedView) : {
            type: 'table',
            perPage: 20,
            page: 1,
            sort: { field: 'id', direction: 'desc' },
            filters: [],
            search: '',
            layout: {
                primaryField: 'license_key',
                groupByField: 'product_id',
            },
        };
    });

    // 2. Fetch Data from API
    const fetchData = useCallback(async () => {
        setIsLoading(true);
        try {
            // @ts-ignore
            const { root, nonce } = window.sgoplusSwkData;
            
            const queryParams = new URLSearchParams({
                per_page: view.perPage.toString(),
                page: view.page.toString(),
                orderby: view.sort.field,
                order: view.sort.direction,
                search: view.search || '',
            });

            const response = await fetch(`${root}/licenses?${queryParams}`, {
                headers: { 'X-WP-Nonce': nonce }
            });
            const result = await response.json();
            
            if (view.page > 1) {
                setData(prev => [...prev, ...result]); // Infinite Scroll behavior
            } else {
                setData(result);
            }
        } catch (error) {
            console.error('Failed to fetch licenses:', error);
        } finally {
            setIsLoading(false);
        }
    }, [view]);

    useEffect(() => {
        fetchData();
        // Persistence: Save to localStorage on change
        localStorage.setItem('sgoplus_swk_view_settings', JSON.stringify(view));
    }, [view, fetchData]);

    // 3. Define Fields
    const fields = useMemo(() => [
        { id: 'id', label: 'ID', type: 'integer' },
        { id: 'license_key', label: 'License Key', type: 'text', render: ({ item }: any) => <code className="bg-gray-100 px-2 py-1 rounded text-blue-600 font-mono text-xs">{item.license_key}</code> },
        { id: 'product_id', label: 'Product', type: 'text' },
        { id: 'customer_email', label: 'Customer', type: 'text' },
        { 
            id: 'status', 
            label: 'Status', 
            type: 'text',
            render: ({ item }: any) => (
                <span className={`px-2 py-1 rounded-full text-xs font-medium ${item.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`}>
                    {item.status.toUpperCase()}
                </span>
            )
        },
        { id: 'activation_count', label: 'Activations', type: 'integer', render: ({ item }: any) => `${item.activation_count} / ${item.activation_limit}` },
        { id: 'created_at', label: 'Created', type: 'datetime' },
    ], []);

    // 4. Define Layouts
    const layouts = useMemo(() => ({
        table: {
            combineFields: {
                primary: ['license_key', 'product_id'],
            },
        },
        grid: {
            columnCount: 3,
        }
    }), []);

    // 5. Define Actions
    const actions = useMemo(() => [
        {
            id: 'toggle-status',
            label: 'Toggle Status',
            isPrimary: true,
            callback: (items: any[]) => {
                console.log('Toggling status for:', items);
                // Implementation for batch update would go here
            }
        },
        {
            id: 'delete',
            label: 'Delete',
            isDestructive: true,
            callback: (items: any[]) => {
                if (confirm(`Are you sure you want to delete ${items.length} items?`)) {
                    console.log('Deleting:', items);
                }
            }
        }
    ], []);

    // 6. Infinite Scroll Handler
    const onScroll = useCallback((e: any) => {
        const { scrollTop, clientHeight, scrollHeight } = e.currentTarget;
        if (scrollHeight - scrollTop === clientHeight && !isLoading) {
            setView((prev: any) => ({ ...prev, page: prev.page + 1 }));
        }
    }, [isLoading]);

    return (
        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" style={{ height: '700px', overflowY: 'auto' }} onScroll={onScroll}>
            <div className="p-6 border-b border-gray-50 flex justify-between items-center bg-gray-50/30">
                <h2 className="text-lg font-bold text-gray-900">License Key Overview</h2>
                <div className="flex gap-2">
                    <Button variant="secondary" onClick={() => setView((prev: any) => ({ ...prev, page: 1 }))}>Refresh</Button>
                </div>
            </div>

            <DataViews
                data={data}
                fields={fields}
                view={view}
                onChangeView={setView}
                actions={actions}
                // @ts-ignore
                layouts={layouts}
            />

            {isLoading && (
                <div className="p-8 text-center">
                    <Spinner />
                </div>
            )}
            
            {!isLoading && data.length === 0 && (
                <div className="p-20 text-center text-gray-500">
                    No licenses found. Start by adding one or running the migration.
                </div>
            )}
        </div>
    );
};

export default LicenseDataView;
