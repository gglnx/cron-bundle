<?php

declare(strict_types=1);

namespace Shapecode\Bundle\CronBundle\Event;

use Shapecode\Bundle\CronBundle\Model\CronJobMetadata;
use Symfony\Contracts\EventDispatcher\Event;

final class LoadJobsEvent extends Event
{
    public const NAME = 'shapecode_cron.load_jobs';

    /** @var list<CronJobMetadata> */
    private array $jobs = [];

    public function addJob(CronJobMetadata $cronJobMetadata): void
    {
        $this->jobs[] = $cronJobMetadata;
    }

    /**
     * @return list<CronJobMetadata>
     */
    public function getJobs(): array
    {
        return $this->jobs;
    }
}
