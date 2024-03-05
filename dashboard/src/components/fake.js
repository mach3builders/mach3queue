const data = {
    pending: [
        {
            name: 'Queue\\GeneratePageMetaTitle',
            queue: 'ai',
            queued: '2024-03-02 12:57:45',
            tags: [
                {
                    name: 'App\\Page',
                    value: 12
                }
            ]
        },
    ],
    dashboard: {
        completedJobs: 100,
        failedJobs: 5,
        pendingJobs: 20,
        active: true,
        queues: [
            {
                name: 'default',
                count: 15
            },
            {
                name: 'high',
                count: 3
            },
            {
                name: 'low',
                count: 2
            }
        ],
        supervisors: [
            {
                options: { name: 'default' },
                queues: 'default',
                processes: 5,
            },
            {
                options: { name: 'high' },
                queues: 'high',
                processes: 1,
            },
            {
                options: { name: 'low' },
                queues: 'low',
                processes: 1,
            },
        ],
    },
    completed: [
        {
            name: 'Queue\\GeneratePageMetaTitle',
            queue: 'ai',
            queued: '2024-03-02 12:57:45',
            completed_at: '2024-03-02 12:57:45',
            runtime: 0.09,
            tags: [
                {
                    name: 'App\\Page',
                    value: 12
                }
            ]
        },
    ],
    failed: [
        {
            name: 'Queue\\GeneratePageMetaTitle',
            queue: 'ai',
            queued: '2024-03-02 12:57:45',
            buried_dt: '2024-03-02 12:57:45',
            runtime: 0.09,
            tags: [
                {
                    name: 'App\\Page',
                    value: 12
                }
            ]
        },
    ],
};

export function fake(type) {
    return data[type] ?? null
}