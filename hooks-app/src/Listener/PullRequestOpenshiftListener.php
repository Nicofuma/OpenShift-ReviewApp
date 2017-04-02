<?php

/**
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace Phpbb\DevHooks\Listener;

use chobie\Jira\Api as JiraClient;
use Phpbb\DevHooks\Helper\GithubHelper;
use Phpbb\DevHooks\Helper\OpenShiftHelper;

class PullRequestOpenshiftListener implements Listener
{
    private $openshiftHelper;
    private $githubHelper;

    public function __construct(OpenShiftHelper $openshiftHelper, GithubHelper $githubHelper)
    {
        $this->openshiftHelper = $openshiftHelper;
        $this->githubHelper = $githubHelper;
    }

    public function handle(array $data)
    {
        $commitData = [
            'owner' => $data['repository']['owner']['login'],
            'repository' => $data['repository']['name'],
            'sha' => $data['pull_request']['head']['sha'],
        ];

        $statusData = [
            'context' => 'Review application',
        ];

        switch ($data['action']) {
            case 'reopened':
            case 'opened':
                $this->githubHelper->setCommitStatus($commitData, $statusData, 'pending', 'Deploying environment');

                try {
                    $response = $this->openshiftHelper->createEnvironment(
                        $this->getEnvironmentName($data['repository']['full_name'], $data['pull_request']['number']),
                        [
                            'pr'    => $data['pull_request']['number'],
                            'usage' => 'review-app',
                        ]
                    );

                    $this->githubHelper->setCommitStatus($commitData, $statusData, 'success', 'Environment deployed');
                } catch (\Throwable $e) {
                    $this->githubHelper->setCommitStatus($commitData, $statusData, 'failure', 'Cannot deploy environment');

                    throw $e;
                }

                break;
            case 'closed':
                $this->openshiftHelper->deleteEnvironment($this->getEnvironmentName($data['repository']['full_name'], $data['pull_request']['number']));
                break;
        }
    }

    private function getEnvironmentName(string $repository, int $prNumber)
    {
        return sprintf('Review App - %s - PR %d', $repository, $prNumber);
    }
}
