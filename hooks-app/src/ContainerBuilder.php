<?php

/**
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace Phpbb\DevHooks;

use GuzzleHttp\Client;
use Phpbb\DevHooks\Helper\OpenShiftClient;
use Phpbb\DevHooks\Helper\OpenShiftHelper;
use Pimple\Container;

class ContainerBuilder
{
    /** @return \ArrayAccess */
    public function build()
    {
        $values = [
            // Services
            'kernel' => function ($c) {
                return new Kernel(
                    $c['github_webhooks_secret'],
                    $c
                );
            },
            'client.github' => function ($c) {
                return new \Github\Client;
            },
            'helper.github' => function ($c) {
                return new Helper\GithubHelper(
                    $c['client.github'],
                    $c['github_api_token']
                );
            },
            'client.openshift' => function ($c) {
                return new OpenShiftClient(
                    new Client([
                        'base_uri' => $c['openshift_master_url'],
                    ]), $c['openshift_token_file'], $c['openshift_ca_file']
                );
            },
            'helper.openshift' => function ($c) {
                return new OpenShiftHelper(
                    $c['client.openshift']
                );
            },
            'listener.pull_request.openshift' => function ($c) {
                return new Listener\PullRequestOpenshiftListener(
                    $c['helper.openshift'],
                    $c['helper.github']
                );
            },
        ];

        $secretsFile = __DIR__.'/../config/parameters.php';
        if (file_exists($secretsFile)) {
            $secrets = require $secretsFile;
            if (is_array($secrets)) {
                $values = array_merge($values, $secrets);
            }
        }

        return new Container($values);
    }
}
