<?php

namespace Tradzero\DynamicUrl\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Response;
use Illuminate\Console\Command;
use Tradzero\DynamicUrl\Models\DynamicUrl;

class SyncUrlAvailableStatusCommand extends Command
{
    protected $signature = 'sync:url_available_status';

    protected $description = 'sync url available status, it should be run every minute';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $endpoints = DynamicUrl::where('enable', true)->get();

        $client = new Client();

        $requests = function () use ($client, $endpoints) {
            foreach ($endpoints as $endpoint) {
                $url = $endpoint->url;
                yield function () use ($client, $url) {
                    return $client->getAsync($url);
                };
            }
        };

        $pool = new Pool($client, $requests(), [
            'concurrency' => 10,
            'fulfilled' => function (Response $response, $index) use ($endpoints) {
                $url = $response->getHeader('Referer');
                $endpoints->where('url', $url)->update([
                    'available' => true,
                    'check_at' => now(),
                ]);
            },
            'rejected' => function (RequestException $reason, $index) use ($endpoints) {
                $url = $reason->getRequest()->getHeader('Referer');
                $endpoints->where('url', $url)->update([
                    'available' => false,
                    'check_at' => now(),
                ]);
            },
        ]);
        
        $promise = $pool->promise();
        
        $promise->wait();

        return 0;
    }
}
