<?php return [
    'github_api_token' => getenv('GITHUB_API_TOKEN'),
    'github_webhooks_secret' => getenv('GITHUB_WEBHOOKS_SECRET'),
    'openshift_master_url' => getenv('OPENSHIFT_MASTER'),
    'openshift_token_file' => getenv('OPENSHIFT_TOKEN_FILE'),
    'openshift_ca_file' => getenv('OPENSHIFT_CA_FILE'),
];
