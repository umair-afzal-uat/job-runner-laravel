<?php

namespace App\Http\Controllers;

use App\Services\BackgroundJobService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BackgroundJobController extends Controller
{
    // Declare the background job service instance
    protected BackgroundJobService $backgroundJobService;

    /**
     * Constructor to inject the BackgroundJobService.
     *
     * @param BackgroundJobService $backgroundJobService
     */
    public function __construct(BackgroundJobService $backgroundJobService)
    {
        // Inject the BackgroundJobService into the controller
        $this->backgroundJobService = $backgroundJobService;
    }

    /**
     * Display the list of background jobs.
     *
     * @return View
     */
    public function index(): View
    {
        // Fetch all jobs using the service method
        $jobs = $this->backgroundJobService->getAllJobs();

        // Return the view with the jobs data
        return view('admin.pages.background-jobs.index', compact('jobs'));
    }

    /**
     * Cancel a specific background job.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function cancel(int $id): RedirectResponse
    {
        // Use the service to cancel the job by updating its status to 'failed'
        $this->backgroundJobService->cancelJob($id);

        // Redirect back with a success message
        return redirect()->back()->with('status', 'Job cancelled successfully.');
    }
}
