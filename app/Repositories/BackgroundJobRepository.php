<?php

namespace App\Repositories;

use App\Models\BackgroundJob;

class BackgroundJobRepository implements BackgroundJobRepositoryInterface
{
    /**
     * Get all background jobs ordered by creation date.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllJobs(): \Illuminate\Database\Eloquent\Collection
    {
        return BackgroundJob::orderBy('created_at', 'desc')->get();
    }

    /**
     * Find a background job by its ID.
     *
     * @param int $id
     * @return \App\Models\BackgroundJob
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findJobById(int $id): BackgroundJob
    {
        return BackgroundJob::findOrFail($id);
    }

    /**
     * Update the status of a specific background job.
     *
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function updateJobStatus(int $id, string $status): bool
    {
        $job = $this->findJobById($id);
        return $job->update(['status' => $status]);
    }
}
