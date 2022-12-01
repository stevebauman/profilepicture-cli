<?php

namespace App\Commands;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;

class DownloadCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'download';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Download all your 4k images from ProfilePicture.AI';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $credentials = json_decode(
            file_get_contents($this->ask('Where is your credentials.json file?', './credentials.json')), true
        );

        if (empty($credentials['user_id'] ?? null)) {
            return $this->error('No [user_id] key specified in the credentials.json file.');
        }

        if (empty($credentials['access_token'] ?? null)) {
            return $this->error('No [access_token] key specified in the credentials.json file.');
        }

        $http = $this->makeHttpClient($credentials['user_id'], $credentials['access_token']);

        $imagesJson = json_decode(
            file_get_contents($this->ask('Where is your images.json file?', './images.json')), true
        );

        $outputDir = $this->ask('Where should I output your 4k images? (full directory path)', './downloads');

        if (! is_dir($outputDir)) {
            mkdir($outputDir);
        }

        $existing = collect(scandir($outputDir))->filter(
            fn ($file) => Str::of($file)->endsWith('.png')
        )->map(
            fn ($file) => Str::remove('.png', $file)
        );

        $images = collect($imagesJson['images'])
            ->pluck('urls')
            ->flatten()
            ->reject(fn ($url) => $existing->contains(md5($url)));

        if (empty($images)) {
            return $this->error('No images were found inside of your images.json file.');
        }

        $this->line('Downloading images...');

        $this->newLine();

        $this->withProgressBar($images, function ($imageUrl) use ($outputDir, $http) {
            $id = $http->post(
                'https://s1.profilepicture.ai/tune/download/large', ['image' => $imageUrl]
            )->json('id');

            $attempts = 0;

            do {
                if ($attempts > 0) {
                    sleep(1);
                }

                $download = $http
                    ->withHeaders(['uid' => null])
                    ->get("https://s1.profilepicture.ai/tune/download/poll/{$id}");

                $attempts++;
            } while (
                in_array($download->json('status'), ['starting', 'processing'])
            );

            if ($download->json('status') == 'failed') {
                $this->newLine();
                $this->warn("Failed downloading image with ID [$id]. Response: '{$download->body()}'. Skipping...");
                $this->newLine();

                return;
            }

            $filename = implode('.', [md5($imageUrl), 'png']);

            file_put_contents(
                Str::of($outputDir)->finish('/')->append($filename)->toString(),
                file_get_contents($download->json('downloadUrl'))
            );
        });

        $this->newLine();

        $this->line('Finished downloading images.');

        return static::SUCCESS;
    }

    /**
     * @param  mixed  $uid
     * @param  mixed  $token
     * @return \Illuminate\Http\Client\PendingRequest
     */
    protected function makeHttpClient($uid, $token)
    {
        return Http::acceptJson()->withHeaders([
            'uid' => $uid,
            'idToken' => $token,
        ])->throw();
    }
}
