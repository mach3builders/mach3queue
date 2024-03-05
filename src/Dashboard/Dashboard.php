<?php

namespace Mach3queue\Dashboard;

use DOMDocument;
use Mach3queue\Job\Job;
use Mach3queue\Supervisor\SupervisorRepository;

class Dashboard
{
    public static function parse(array $data = []): string
    {
        $data = $data ?: $_GET;

        if (!empty($data['data'])) {
            header('Content-Type: application/json; charset=utf-8');
            return match ($data['data']) {
                'dashboard' => self::dashboard(),
                'pending' => self::pending(),
                'completed' => self::completed(),
                'failed' => self::failed(),
            };
        }

        $path = __DIR__ . '/../../dashboard/dist/';
        $html = file_get_contents($path . 'index.html');

        $dom = new DOMDocument();
        $dom->loadHTML($html);

        $script_tags = $dom->getElementsByTagName('script');
        $link_tags = $dom->getElementsByTagName('link');

        foreach ($link_tags as $link_tag) {
            if ($link_tag->getAttribute('rel') == 'stylesheet' && $link_tag->hasAttribute('href')) {
                $href = $link_tag->getAttribute('href');
                $link_content = file_get_contents($path . $href);
                $link_tag->removeAttribute('href');
                $link_tag->textContent = $link_content;
                $style = $dom->createElement('style', $link_content);
                $link_tag->parentNode->replaceChild($style, $link_tag);
            }
        }

        foreach ($script_tags as $script_tag) {
            $src = $script_tag->getAttribute('src');

            // If the script has a src attribute (external source):
            $script_content = file_get_contents($path . $src);  // Get content from external file

            $script_tag->removeAttribute('src');  // Remove the src attribute
            $script_tag->textContent = $script_content;  // Replace the content of the script tag with the content of the external file
        }

        return $dom->saveHTML();
    }

    public static function dashboard(): string
    {
        $supervisors = collect(SupervisorRepository::all());
        $master_index = $supervisors->search(fn($s) => $s->master == null);
        $active = $master_index !== false;

        if ($master_index !== false) {
            $supervisors->forget($master_index);
        }

        $jobs->map(function ($job) {
            $payload = unserialize($job->payload);
            $job->name = get_class($payload);
            $tags = [];

            $params = get_object_vars($payload);

            foreach($params as $key => $value) {
                if (is_object($value)) {
                    $value = $value->id ?? null;
                }

                $tags[] = [
                    'name' => $key,
                    'value' => $value,
                ];
            }

            $job->tags = $tags;

            return $job;
        });

        return json_encode([
            'active' => $active,
            'queues' => $active ? Job::queuesWorkload()->get() : [],
            'supervisors' => $active ? $supervisors : [],
            'completedJobs' => Job::completed()->count(),
            'failedJobs' => Job::failed()->count(),
            'pendingJobs' => Job::pending()->count(),
        ]);
    }

    public static function pending(): string
    {
        $jobs = Job::pending()->get();

        $jobs->map(function ($job) {
            $payload = unserialize($job->payload);
            $job->name = get_class($payload);
            $tags = [];

            $params = get_object_vars($payload);

            foreach($params as $key => $value) {
                if (is_object($value)) {
                    $value = $value->id ?? null;
                }

                $tags[] = [
                    'name' => $key,
                    'value' => $value,
                ];
            }

            $job->tags = $tags;

            return $job;
        });

        return json_encode($jobs);
    }

    public static function completed(): string
    {
        $jobs = Job::completed()->get();

        $jobs->map(function ($job) {
            $payload = unserialize($job->payload);
            $job->name = get_class($payload);
            $tags = [];

            $params = get_object_vars($payload);

            foreach($params as $key => $value) {
                if (is_object($value)) {
                    $value = $value->id ?? null;
                }

                $tags[] = [
                    'name' => $key,
                    'value' => $value,
                ];
            }

            $job->tags = $tags;

            return $job;
        });

        return json_encode($jobs);
    }

    public static function failed(): string
    {
        $jobs = Job::failed()->get();

        $jobs->map(function ($job) {
            $payload = unserialize($job->payload);
            $job->name = get_class($payload);
            $tags = [];

            $params = get_object_vars($payload);

            foreach($params as $key => $value) {
                if (is_object($value)) {
                    $value = $value->id ?? null;
                }

                $tags[] = [
                    'name' => $key,
                    'value' => $value,
                ];
            }

            $job->tags = $tags;

            return $job;
        });

        return json_encode($jobs);
    }
}