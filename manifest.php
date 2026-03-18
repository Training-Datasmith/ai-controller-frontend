<?php

declare(strict_types=1);

return [
    'name' => 'ai-controller-frontend',
    'depends' => [
        'aimeos-core',
    ],
    'config' => [
        'config',
    ],
    'include' => [
        'src',
    ],
    'i18n' => [
        'controller/frontend' => 'i18n',
    ],
];
