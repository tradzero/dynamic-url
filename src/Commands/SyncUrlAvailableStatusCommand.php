<?php

namespace Tradzero\DynamicUrl\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Console\Command;
use Tradzero\DynamicUrl\Models\DynamicUrl;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Promise\Utils;
use Illuminate\Support\Arr;

class SyncUrlAvailableStatusCommand extends Command
{
    protected $signature = 'sync:url_available_status';

    protected $description = 'sync url available status, it should be run every minute';

    protected $endpoints;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $endpoints = DynamicUrl::where('enable', true)->get();
        $this->endpoints = $endpoints;

        $client = new Client([
            'timeout' => 5
        ]);

        $promises = $endpoints->mapWithKeys(function ($endpoint) use ($client) {
            $id = $endpoint->id;
            $request = $client->getAsync($endpoint->url);
            return [$id => $request];
        });
        $promises = $promises->toArray();

        $responses = Utils::settle($promises)->wait();

        foreach ($responses as $key => $response) {
            if ($reason = Arr::get($response, 'reason')) {
                if (! $reason instanceof ServerException && ! $reason instanceof ClientException) {
                    $this->updateEndpoint($key, false);
                    continue;
                }
            }
            $this->updateEndpoint($key, true);
        }

        $endpoints->toQuery()->update([
            'check_at' => now(),
        ]);
        return 0;
    }

    protected function updateEndpoint($id, $result)
    {
        $endpoints = $this->endpoints;
        $endpoint = $endpoints->where('id', $id)->first();
        if ($endpoint) {
            $raw = (bool) $endpoint->available;
            if ($result != $raw) {
                $endpoint->update([
                    'available' => $result
                ]);
            }
        }
    }
}
