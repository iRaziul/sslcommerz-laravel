<?php

declare(strict_types=1);

namespace Raziul\Sslcommerz\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

use function Laravel\Prompts\confirm;

#[AsCommand(
    name: 'sslcommerz-laravel:install',
    description: 'Install the Sslcommerz Laravel package and publish the configuration',
    aliases: ['sslcommerz:install'],
)]
final class InstallCommand extends Command
{
    public function handle(): int
    {
        $this->publishConfig();
        $this->askToStarRepo('iraziul/sslcommerz-laravel');

        return self::SUCCESS;
    }

    private function publishConfig(): void
    {
        $this->call('vendor:publish', [
            '--tag' => 'sslcommerz-config',
        ]);
    }

    private function askToStarRepo(string $repoVendorPath): void
    {
        if (confirm('Would you like to star this repo on GitHub?', true)) {
            $repoUrl = "https://github.com/{$repoVendorPath}";

            match (mb_strtolower(PHP_OS_FAMILY)) {
                'darwin' => exec("open {$repoUrl}"),
                'linux' => exec("xdg-open {$repoUrl}"),
                'windows' => exec("start {$repoUrl}"),
                default => null,
            };
        }

        $this->components->info('Thank you ❤️');
    }
}
