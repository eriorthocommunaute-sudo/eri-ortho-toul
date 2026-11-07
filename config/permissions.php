<?php

return [
    'roles' => [
        'administrator' => [
            'members.view', 'members.create', 'members.update', 'members.delete',
            'members.financial-history.view',
            'references.manage', 'users.manage',
            'finance.view', 'finance.record', 'finance.reports',
            'logistics.view', 'logistics.manage',
            'events.view', 'events.manage',
            'purchases.view', 'purchases.manage',
            'secretary.view', 'secretary.manage',
        ],
        'secretary' => [
            'members.view', 'members.create', 'members.update',
            'members.financial-history.view',
            'secretary.view', 'secretary.manage',
            'events.view',
        ],
        'finance' => [
            'finance.view', 'finance.record', 'finance.reports',
            'members.view',
            'members.financial-history.view',
        ],
        'logistics' => [
            'logistics.view', 'logistics.manage',
            'finance.view', 'finance.record',
        ],
        'event-coordinator' => [
            'events.view', 'events.manage',
        ],
        'reviewer' => [
            'members.view',
            'finance.view',
            'logistics.view',
            'events.view',
        ],
    ],
];