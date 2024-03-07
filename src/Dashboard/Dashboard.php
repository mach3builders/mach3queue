<?php

namespace Mach3queue\Dashboard;

use DOMException;
use Mach3queue\Job\Job;
use Mach3queue\Supervisor\SupervisorRepository;

class Dashboard
{
    /**
     * @throws DOMException
     */
    public static function parse(array $data = []): string
    {
        $data = $data ?: $_GET;

        if (!empty($data['data'])) {
            header('Content-Type: application/json; charset=utf-8');
            
            return match ($data['data']) {
                'dashboard' => (new DashboardData)->get(),
                'pending' => JobsFormatter::format(Job::pending()->get()),
                'completed' => JobsFormatter::format(Job::completed()->get()),
                'failed' => JobsFormatter::format(Job::failed()->get()),
            };
        }

        return (new DashboardHtml)->parse();
    }
}
