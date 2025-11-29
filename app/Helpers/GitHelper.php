<?php

namespace App\Helpers;

class GitHelper
{
    public static function getDeploymentId(): string
    {
        $gitPath = base_path('.git');

        if (! is_dir($gitPath)) {
            return 'unknown';
        }

        $projectPath = base_path();
        $command = sprintf(
            'cd %s && git rev-parse --short=7 HEAD 2>%s',
            escapeshellarg($projectPath),
            PHP_OS_FAMILY === 'Windows' ? 'nul' : '/dev/null'
        );

        $output = shell_exec($command);

        if (! $output) {
            return 'unknown';
        }

        return trim($output);
    }
}
