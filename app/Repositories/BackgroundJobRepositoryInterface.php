<?php

namespace App\Repositories;

interface BackgroundJobRepositoryInterface
{
    /**
     * Get all background jobs.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllJobs(): \Illuminate\Database\Eloquent\Collection;

    /**
     * Find a background job by its ID.
     *
     * @param int $id
     * @return \App\Models\BackgroundJob|null
     */
    public function findJobById(int $id): ?\App\Models\BackgroundJob;

    /**
     * Update the status of a specific background job.
     *
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function updateJobStatus(int $id, string $status): bool;
}
