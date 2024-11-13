<?php

namespace App\Services;

use App\Repositories\BackgroundJobRepositoryInterface;
use App\Models\BackgroundJob;

class BackgroundJobService
{
    /**
     * The BackgroundJobRepository instance.
     *
     * @var BackgroundJobRepositoryInterface
     */
    protected BackgroundJobRepositoryInterface $backgroundJobRepository;

    /**
     * Constructor to inject the BackgroundJobRepository.
     *
     * @param BackgroundJobRepositoryInterface $backgroundJobRepository
     */
    public function __construct(BackgroundJobRepositoryInterface $backgroundJobRepository)
    {
        // Inject the BackgroundJobRepository into the service
        $this->backgroundJobRepository = $backgroundJobRepository;
    }

    /**
     * Get all background jobs.
     *
     * @return \Illuminate\Database\Eloquent\Collection|BackgroundJob[]
     */
    public function getAllJobs(): \Illuminate\Database\Eloquent\Collection
    {
        // Delegate the call to the repository to fetch all background jobs
        return $this->backgroundJobRepository->getAllJobs();
    }

    /**
     * Cancel a specific background job by updating its status to 'failed'.
     *
     * @param int $id
     * @return bool
     */
    public function cancelJob(int $id): bool
    {
        // Delegate the call to the repository to update the job status
        return $this->backgroundJobRepository->updateJobStatus($id, 'failed');
    }
}
