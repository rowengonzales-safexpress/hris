<?php

namespace App\Http\Controllers\Hris;

use App\Http\Controllers\Controller;
use App\Models\Hris\JobOpening;
use App\Models\Hris\JobApplication;
use App\Models\Hris\Interview;
use App\Models\Hris\Employee;
use App\Models\Hris\Department;
use App\Models\Hris\Position;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class RecruitmentController extends Controller
{
    /**
     * Display a listing of job openings.
     */
    public function jobOpenings(Request $request): Response
    {
        $query = JobOpening::with(['department', 'position', 'hiringManager', 'recruiter'])
            ->when($request->search, function ($query, $search) {
                return $query->where('title', 'like', "%{$search}%")
                            ->orWhere('job_code', 'like', "%{$search}%");
            })
            ->when($request->department_id, function ($query, $departmentId) {
                return $query->where('department_id', $departmentId);
            })
            ->when($request->position_id, function ($query, $positionId) {
                return $query->where('position_id', $positionId);
            })
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->employment_type, function ($query, $employmentType) {
                return $query->where('employment_type', $employmentType);
            })
            ->when($request->location, function ($query, $location) {
                return $query->where('location', 'like', "%{$location}%");
            });

        $jobOpenings = $query->latest('created_at')->paginate($request->input('per_page', 15));
        
        $departments = Department::active()->get(['id', 'name']);
        $positions = Position::active()->get(['id', 'title']);
        $employees = Employee::active()->with('department')
            ->get(['id', 'first_name', 'last_name', 'employee_id', 'department_id']);

        return Inertia::render('Hris/Recruitment/JobOpenings/Index', [
            'jobOpenings' => $jobOpenings,
            'departments' => $departments,
            'positions' => $positions,
            'employees' => $employees,
            'filters' => $request->only(['search', 'department_id', 'position_id', 'status', 'employment_type', 'location']),
        ]);
    }

    /**
     * Show the form for creating a new job opening.
     */
    public function createJobOpening(): Response
    {
        $departments = Department::active()->get(['id', 'name']);
        $positions = Position::active()->get(['id', 'title']);
        $employees = Employee::active()->with('department')
            ->get(['id', 'first_name', 'last_name', 'employee_id', 'department_id']);

        return Inertia::render('Hris/Recruitment/JobOpenings/Create', [
            'departments' => $departments,
            'positions' => $positions,
            'employees' => $employees,
        ]);
    }

    /**
     * Store a newly created job opening.
     */
    public function storeJobOpening(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:200',
            'job_code' => 'required|string|max:20|unique:hris_job_openings,job_code',
            'department_id' => 'required|exists:hris_departments,id',
            'position_id' => 'required|exists:hris_positions,id',
            'description' => 'required|string',
            'requirements' => 'required|array',
            'responsibilities' => 'required|array',
            'employment_type' => 'required|in:Full-time,Part-time,Contract,Temporary,Internship',
            'location' => 'required|string|max:100',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0|gte:salary_min',
            'positions_available' => 'required|integer|min:1',
            'priority' => 'required|in:Low,Medium,High,Urgent',
            'application_deadline' => 'required|date|after:today',
            'hiring_manager_id' => 'required|exists:hris_employees,id',
            'recruiter_id' => 'nullable|exists:hris_employees,id',
            'is_remote' => 'boolean',
            'benefits' => 'nullable|array',
            'qualifications' => 'nullable|array',
        ]);

        $jobOpening = JobOpening::create([
            'title' => $request->title,
            'job_code' => $request->job_code,
            'department_id' => $request->department_id,
            'position_id' => $request->position_id,
            'description' => $request->description,
            'requirements' => $request->requirements,
            'responsibilities' => $request->responsibilities,
            'employment_type' => $request->employment_type,
            'location' => $request->location,
            'salary_min' => $request->salary_min,
            'salary_max' => $request->salary_max,
            'positions_available' => $request->positions_available,
            'priority' => $request->priority,
            'application_deadline' => $request->application_deadline,
            'hiring_manager_id' => $request->hiring_manager_id,
            'recruiter_id' => $request->recruiter_id,
            'is_remote' => $request->boolean('is_remote'),
            'benefits' => $request->benefits ?? [],
            'qualifications' => $request->qualifications ?? [],
            'status' => 'Draft',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('hris.recruitment.job-openings')
            ->with('success', 'Job opening created successfully!');
    }

    /**
     * Display the specified job opening.
     */
    public function showJobOpening(JobOpening $jobOpening): Response
    {
        $jobOpening->load(['department', 'position', 'hiringManager', 'recruiter', 'applications.interviews']);

        return Inertia::render('Hris/Recruitment/JobOpenings/Show', [
            'jobOpening' => $jobOpening,
        ]);
    }

    /**
     * Show the form for editing the specified job opening.
     */
    public function editJobOpening(JobOpening $jobOpening): Response
    {
        $jobOpening->load(['department', 'position', 'hiringManager', 'recruiter']);
        
        $departments = Department::active()->get(['id', 'name']);
        $positions = Position::active()->get(['id', 'title']);
        $employees = Employee::active()->with('department')
            ->get(['id', 'first_name', 'last_name', 'employee_id', 'department_id']);

        return Inertia::render('Hris/Recruitment/JobOpenings/Edit', [
            'jobOpening' => $jobOpening,
            'departments' => $departments,
            'positions' => $positions,
            'employees' => $employees,
        ]);
    }

    /**
     * Update the specified job opening.
     */
    public function updateJobOpening(Request $request, JobOpening $jobOpening)
    {
        $request->validate([
            'title' => 'required|string|max:200',
            'job_code' => 'required|string|max:20|unique:hris_job_openings,job_code,' . $jobOpening->id,
            'department_id' => 'required|exists:hris_departments,id',
            'position_id' => 'required|exists:hris_positions,id',
            'description' => 'required|string',
            'requirements' => 'required|array',
            'responsibilities' => 'required|array',
            'employment_type' => 'required|in:Full-time,Part-time,Contract,Temporary,Internship',
            'location' => 'required|string|max:100',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0|gte:salary_min',
            'positions_available' => 'required|integer|min:1',
            'priority' => 'required|in:Low,Medium,High,Urgent',
            'application_deadline' => 'required|date',
            'hiring_manager_id' => 'required|exists:hris_employees,id',
            'recruiter_id' => 'nullable|exists:hris_employees,id',
            'is_remote' => 'boolean',
            'benefits' => 'nullable|array',
            'qualifications' => 'nullable|array',
        ]);

        $jobOpening->update([
            'title' => $request->title,
            'job_code' => $request->job_code,
            'department_id' => $request->department_id,
            'position_id' => $request->position_id,
            'description' => $request->description,
            'requirements' => $request->requirements,
            'responsibilities' => $request->responsibilities,
            'employment_type' => $request->employment_type,
            'location' => $request->location,
            'salary_min' => $request->salary_min,
            'salary_max' => $request->salary_max,
            'positions_available' => $request->positions_available,
            'priority' => $request->priority,
            'application_deadline' => $request->application_deadline,
            'hiring_manager_id' => $request->hiring_manager_id,
            'recruiter_id' => $request->recruiter_id,
            'is_remote' => $request->boolean('is_remote'),
            'benefits' => $request->benefits ?? [],
            'qualifications' => $request->qualifications ?? [],
        ]);

        return redirect()->route('hris.recruitment.job-openings')
            ->with('success', 'Job opening updated successfully!');
    }

    /**
     * Remove the specified job opening.
     */
    public function destroyJobOpening(JobOpening $jobOpening)
    {
        if ($jobOpening->applications()->count() > 0) {
            return back()->withErrors(['message' => 'Cannot delete job opening with existing applications.']);
        }

        $jobOpening->delete();

        return redirect()->route('hris.recruitment.job-openings')
            ->with('success', 'Job opening deleted successfully!');
    }

    /**
     * Publish a job opening.
     */
    public function publishJobOpening(JobOpening $jobOpening)
    {
        if ($jobOpening->status !== 'Draft') {
            return response()->json(['message' => 'Only draft job openings can be published'], 422);
        }

        $jobOpening->update([
            'status' => 'Published',
            'published_date' => now(),
        ]);

        return response()->json(['message' => 'Job opening published successfully']);
    }

    /**
     * Close a job opening.
     */
    public function closeJobOpening(JobOpening $jobOpening)
    {
        if ($jobOpening->status === 'Closed') {
            return response()->json(['message' => 'Job opening is already closed'], 422);
        }

        $jobOpening->update([
            'status' => 'Closed',
            'closed_date' => now(),
        ]);

        return response()->json(['message' => 'Job opening closed successfully']);
    }

    /**
     * Display a listing of job applications.
     */
    public function applications(Request $request): Response
    {
        $query = JobApplication::with(['jobOpening.department', 'interviews'])
            ->when($request->search, function ($query, $search) {
                return $query->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
            })
            ->when($request->job_opening_id, function ($query, $jobOpeningId) {
                return $query->where('job_opening_id', $jobOpeningId);
            })
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->stage, function ($query, $stage) {
                return $query->where('stage', $stage);
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                return $query->where('applied_date', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                return $query->where('applied_date', '<=', $dateTo);
            });

        $applications = $query->latest('applied_date')->paginate($request->input('per_page', 15));
        
        $jobOpenings = JobOpening::published()->with('department')
            ->get(['id', 'title', 'job_code', 'department_id']);

        return Inertia::render('Hris/Recruitment/Applications/Index', [
            'applications' => $applications,
            'jobOpenings' => $jobOpenings,
            'filters' => $request->only(['search', 'job_opening_id', 'status', 'stage', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Show the form for creating a new job application.
     */
    public function createApplication(): Response
    {
        $jobOpenings = JobOpening::published()->with('department')
            ->get(['id', 'title', 'job_code', 'department_id']);

        return Inertia::render('Hris/Recruitment/Applications/Create', [
            'jobOpenings' => $jobOpenings,
        ]);
    }

    /**
     * Store a newly created job application.
     */
    public function storeApplication(Request $request)
    {
        $request->validate([
            'job_opening_id' => 'required|exists:hris_job_openings,id',
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|email|max:100',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:200',
            'city' => 'nullable|string|max:50',
            'state' => 'nullable|string|max:50',
            'postal_code' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:50',
            'resume_path' => 'nullable|string|max:255',
            'cover_letter' => 'nullable|string',
            'expected_salary' => 'nullable|numeric|min:0',
            'availability_date' => 'nullable|date',
            'source' => 'nullable|string|max:50',
            'skills' => 'nullable|array',
            'experience_years' => 'nullable|integer|min:0',
            'education' => 'nullable|array',
            'references' => 'nullable|array',
        ]);

        // Check for duplicate application
        $exists = JobApplication::where('job_opening_id', $request->job_opening_id)
            ->where('email', $request->email)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'email' => 'Application already exists for this job opening with this email address.'
            ]);
        }

        $application = JobApplication::create([
            'job_opening_id' => $request->job_opening_id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'country' => $request->country,
            'resume_path' => $request->resume_path,
            'cover_letter' => $request->cover_letter,
            'expected_salary' => $request->expected_salary,
            'availability_date' => $request->availability_date,
            'source' => $request->source,
            'skills' => $request->skills ?? [],
            'experience_years' => $request->experience_years,
            'education' => $request->education ?? [],
            'references' => $request->references ?? [],
            'status' => 'New',
            'stage' => 'Application Review',
            'applied_date' => now(),
        ]);

        return redirect()->route('hris.recruitment.applications')
            ->with('success', 'Job application created successfully!');
    }

    /**
     * Display the specified job application.
     */
    public function showApplication(JobApplication $application): Response
    {
        $application->load(['jobOpening.department', 'interviews.interviewer']);

        return Inertia::render('Hris/Recruitment/Applications/Show', [
            'application' => $application,
        ]);
    }

    /**
     * Show the form for editing the specified job application.
     */
    public function editApplication(JobApplication $application): Response
    {
        $application->load(['jobOpening.department']);
        
        $jobOpenings = JobOpening::published()->with('department')
            ->get(['id', 'title', 'job_code', 'department_id']);

        return Inertia::render('Hris/Recruitment/Applications/Edit', [
            'application' => $application,
            'jobOpenings' => $jobOpenings,
        ]);
    }

    /**
     * Update the specified job application.
     */
    public function updateApplication(Request $request, JobApplication $application)
    {
        $request->validate([
            'job_opening_id' => 'required|exists:hris_job_openings,id',
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|email|max:100',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:200',
            'city' => 'nullable|string|max:50',
            'state' => 'nullable|string|max:50',
            'postal_code' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:50',
            'resume_path' => 'nullable|string|max:255',
            'cover_letter' => 'nullable|string',
            'expected_salary' => 'nullable|numeric|min:0',
            'availability_date' => 'nullable|date',
            'source' => 'nullable|string|max:50',
            'skills' => 'nullable|array',
            'experience_years' => 'nullable|integer|min:0',
            'education' => 'nullable|array',
            'references' => 'nullable|array',
            'overall_score' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $application->update([
            'job_opening_id' => $request->job_opening_id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'country' => $request->country,
            'resume_path' => $request->resume_path,
            'cover_letter' => $request->cover_letter,
            'expected_salary' => $request->expected_salary,
            'availability_date' => $request->availability_date,
            'source' => $request->source,
            'skills' => $request->skills ?? [],
            'experience_years' => $request->experience_years,
            'education' => $request->education ?? [],
            'references' => $request->references ?? [],
            'overall_score' => $request->overall_score,
            'notes' => $request->notes,
        ]);

        return redirect()->route('hris.recruitment.applications')
            ->with('success', 'Job application updated successfully!');
    }

    /**
     * Remove the specified job application.
     */
    public function destroyApplication(JobApplication $application)
    {
        $application->delete();

        return redirect()->route('hris.recruitment.applications')
            ->with('success', 'Job application deleted successfully!');
    }

    /**
     * Move application to next stage.
     */
    public function moveToNextStage(Request $request, JobApplication $application)
    {
        $application->moveToNextStage($request->input('notes'));

        return response()->json(['message' => 'Application moved to next stage successfully']);
    }

    /**
     * Reject an application.
     */
    public function rejectApplication(Request $request, JobApplication $application)
    {
        $application->reject($request->input('rejection_reason', 'No reason provided'));

        return response()->json(['message' => 'Application rejected successfully']);
    }

    /**
     * Hire an applicant.
     */
    public function hireApplicant(Request $request, JobApplication $application)
    {
        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'salary' => 'required|numeric|min:0',
            'position_id' => 'required|exists:hris_positions,id',
            'department_id' => 'required|exists:hris_departments,id',
        ]);

        $application->hire(
            $request->start_date,
            $request->salary,
            $request->position_id,
            $request->department_id,
            $request->input('hiring_notes')
        );

        return response()->json(['message' => 'Applicant hired successfully']);
    }

    /**
     * Display a listing of interviews.
     */
    public function interviews(Request $request): Response
    {
        $query = Interview::with(['jobApplication.jobOpening', 'interviewer'])
            ->when($request->search, function ($query, $search) {
                return $query->whereHas('jobApplication', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->job_application_id, function ($query, $applicationId) {
                return $query->where('job_application_id', $applicationId);
            })
            ->when($request->interviewer_id, function ($query, $interviewerId) {
                return $query->where('primary_interviewer_id', $interviewerId);
            })
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->interview_type, function ($query, $type) {
                return $query->where('interview_type', $type);
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                return $query->where('scheduled_date', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                return $query->where('scheduled_date', '<=', $dateTo);
            });

        $interviews = $query->latest('scheduled_date')->paginate($request->input('per_page', 15));
        
        $applications = JobApplication::with('jobOpening')
            ->whereIn('status', ['Under Review', 'Shortlisted', 'Interview'])
            ->get(['id', 'first_name', 'last_name', 'job_opening_id']);
        
        $interviewers = Employee::active()->with('department')
            ->get(['id', 'first_name', 'last_name', 'employee_id', 'department_id']);

        return Inertia::render('Hris/Recruitment/Interviews/Index', [
            'interviews' => $interviews,
            'applications' => $applications,
            'interviewers' => $interviewers,
            'filters' => $request->only(['search', 'job_application_id', 'interviewer_id', 'status', 'interview_type', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Show the form for creating a new interview.
     */
    public function createInterview(): Response
    {
        $applications = JobApplication::with('jobOpening')
            ->whereIn('status', ['Under Review', 'Shortlisted', 'Interview'])
            ->get(['id', 'first_name', 'last_name', 'job_opening_id']);
        
        $interviewers = Employee::active()->with('department')
            ->get(['id', 'first_name', 'last_name', 'employee_id', 'department_id']);

        return Inertia::render('Hris/Recruitment/Interviews/Create', [
            'applications' => $applications,
            'interviewers' => $interviewers,
        ]);
    }

    /**
     * Store a newly created interview.
     */
    public function storeInterview(Request $request)
    {
        $request->validate([
            'job_application_id' => 'required|exists:hris_job_applications,id',
            'interview_type' => 'required|in:Phone,Video,In-Person,Panel,Technical,HR',
            'scheduled_date' => 'required|date|after:now',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'location' => 'nullable|string|max:200',
            'meeting_link' => 'nullable|url|max:255',
            'primary_interviewer_id' => 'required|exists:hris_employees,id',
            'panel_interviewers' => 'nullable|array',
            'panel_interviewers.*' => 'exists:hris_employees,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $interview = Interview::create([
            'job_application_id' => $request->job_application_id,
            'interview_type' => $request->interview_type,
            'scheduled_date' => $request->scheduled_date,
            'duration_minutes' => $request->duration_minutes,
            'location' => $request->location,
            'meeting_link' => $request->meeting_link,
            'primary_interviewer_id' => $request->primary_interviewer_id,
            'panel_interviewers' => $request->panel_interviewers ?? [],
            'status' => 'Scheduled',
            'notes' => $request->notes,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('hris.recruitment.interviews')
            ->with('success', 'Interview scheduled successfully!');
    }

    /**
     * Display the specified interview.
     */
    public function showInterview(Interview $interview): Response
    {
        $interview->load(['jobApplication.jobOpening', 'interviewer']);

        return Inertia::render('Hris/Recruitment/Interviews/Show', [
            'interview' => $interview,
        ]);
    }

    /**
     * Show the form for editing the specified interview.
     */
    public function editInterview(Interview $interview): Response
    {
        if ($interview->status === 'Completed') {
            return back()->withErrors(['message' => 'Cannot edit completed interviews.']);
        }

        $interview->load(['jobApplication.jobOpening', 'interviewer']);
        
        $applications = JobApplication::with('jobOpening')
            ->whereIn('status', ['Under Review', 'Shortlisted', 'Interview'])
            ->get(['id', 'first_name', 'last_name', 'job_opening_id']);
        
        $interviewers = Employee::active()->with('department')
            ->get(['id', 'first_name', 'last_name', 'employee_id', 'department_id']);

        return Inertia::render('Hris/Recruitment/Interviews/Edit', [
            'interview' => $interview,
            'applications' => $applications,
            'interviewers' => $interviewers,
        ]);
    }

    /**
     * Update the specified interview.
     */
    public function updateInterview(Request $request, Interview $interview)
    {
        if ($interview->status === 'Completed') {
            return back()->withErrors(['message' => 'Cannot update completed interviews.']);
        }

        $request->validate([
            'job_application_id' => 'required|exists:hris_job_applications,id',
            'interview_type' => 'required|in:Phone,Video,In-Person,Panel,Technical,HR',
            'scheduled_date' => 'required|date',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'location' => 'nullable|string|max:200',
            'meeting_link' => 'nullable|url|max:255',
            'primary_interviewer_id' => 'required|exists:hris_employees,id',
            'panel_interviewers' => 'nullable|array',
            'panel_interviewers.*' => 'exists:hris_employees,id',
            'notes' => 'nullable|string|max:1000',
            'feedback' => 'nullable|string|max:1000',
            'rating' => 'nullable|numeric|min:1|max:5',
            'recommendation' => 'nullable|in:Strongly Recommend,Recommend,Neutral,Not Recommend,Strongly Not Recommend',
        ]);

        $interview->update([
            'job_application_id' => $request->job_application_id,
            'interview_type' => $request->interview_type,
            'scheduled_date' => $request->scheduled_date,
            'duration_minutes' => $request->duration_minutes,
            'location' => $request->location,
            'meeting_link' => $request->meeting_link,
            'primary_interviewer_id' => $request->primary_interviewer_id,
            'panel_interviewers' => $request->panel_interviewers ?? [],
            'notes' => $request->notes,
            'feedback' => $request->feedback,
            'rating' => $request->rating,
            'recommendation' => $request->recommendation,
        ]);

        return redirect()->route('hris.recruitment.interviews')
            ->with('success', 'Interview updated successfully!');
    }

    /**
     * Remove the specified interview.
     */
    public function destroyInterview(Interview $interview)
    {
        if ($interview->status === 'Completed') {
            return back()->withErrors(['message' => 'Cannot delete completed interviews.']);
        }

        $interview->delete();

        return redirect()->route('hris.recruitment.interviews')
            ->with('success', 'Interview deleted successfully!');
    }

    /**
     * Complete an interview.
     */
    public function completeInterview(Request $request, Interview $interview)
    {
        if ($interview->status !== 'Scheduled') {
            return response()->json(['message' => 'Only scheduled interviews can be completed'], 422);
        }

        $request->validate([
            'feedback' => 'required|string|max:1000',
            'rating' => 'required|numeric|min:1|max:5',
            'recommendation' => 'required|in:Strongly Recommend,Recommend,Neutral,Not Recommend,Strongly Not Recommend',
            'follow_up_required' => 'boolean',
            'follow_up_notes' => 'nullable|string|max:500',
        ]);

        $interview->complete(
            $request->feedback,
            $request->rating,
            $request->recommendation,
            $request->boolean('follow_up_required'),
            $request->follow_up_notes
        );

        return response()->json(['message' => 'Interview completed successfully']);
    }

    /**
     * Cancel an interview.
     */
    public function cancelInterview(Request $request, Interview $interview)
    {
        if ($interview->status === 'Completed') {
            return response()->json(['message' => 'Cannot cancel completed interviews'], 422);
        }

        $interview->cancel($request->input('cancellation_reason', 'No reason provided'));

        return response()->json(['message' => 'Interview cancelled successfully']);
    }

    /**
     * Reschedule an interview.
     */
    public function rescheduleInterview(Request $request, Interview $interview)
    {
        if ($interview->status === 'Completed') {
            return response()->json(['message' => 'Cannot reschedule completed interviews'], 422);
        }

        $request->validate([
            'new_scheduled_date' => 'required|date|after:now',
            'reschedule_reason' => 'nullable|string|max:500',
        ]);

        $interview->reschedule($request->new_scheduled_date, $request->reschedule_reason);

        return response()->json(['message' => 'Interview rescheduled successfully']);
    }

    /**
     * Get recruitment statistics.
     */
    public function getStats(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month');

        $jobOpeningStats = [
            'total_openings' => JobOpening::whereYear('created_at', $year)->count(),
            'published_openings' => JobOpening::published()->whereYear('created_at', $year)->count(),
            'closed_openings' => JobOpening::where('status', 'Closed')->whereYear('created_at', $year)->count(),
            'urgent_openings' => JobOpening::where('priority', 'Urgent')->whereYear('created_at', $year)->count(),
            'by_department' => JobOpening::with('department')
                ->whereYear('created_at', $year)
                ->get()
                ->groupBy('department.name')
                ->map->count(),
        ];

        $applicationQuery = JobApplication::query();
        if ($month) {
            $applicationQuery->whereYear('applied_date', $year)->whereMonth('applied_date', $month);
        } else {
            $applicationQuery->whereYear('applied_date', $year);
        }

        $applicationStats = [
            'total_applications' => (clone $applicationQuery)->count(),
            'new_applications' => (clone $applicationQuery)->where('status', 'New')->count(),
            'under_review' => (clone $applicationQuery)->where('status', 'Under Review')->count(),
            'shortlisted' => (clone $applicationQuery)->where('status', 'Shortlisted')->count(),
            'hired' => (clone $applicationQuery)->where('status', 'Hired')->count(),
            'rejected' => (clone $applicationQuery)->where('status', 'Rejected')->count(),
            'by_source' => (clone $applicationQuery)->selectRaw('source, COUNT(*) as count')
                ->groupBy('source')
                ->get(),
        ];

        $interviewQuery = Interview::query();
        if ($month) {
            $interviewQuery->whereYear('scheduled_date', $year)->whereMonth('scheduled_date', $month);
        } else {
            $interviewQuery->whereYear('scheduled_date', $year);
        }

        $interviewStats = [
            'total_interviews' => (clone $interviewQuery)->count(),
            'scheduled_interviews' => (clone $interviewQuery)->where('status', 'Scheduled')->count(),
            'completed_interviews' => (clone $interviewQuery)->where('status', 'Completed')->count(),
            'cancelled_interviews' => (clone $interviewQuery)->where('status', 'Cancelled')->count(),
            'average_rating' => (clone $interviewQuery)->whereNotNull('rating')->avg('rating'),
            'by_type' => (clone $interviewQuery)->selectRaw('interview_type, COUNT(*) as count, AVG(rating) as avg_rating')
                ->groupBy('interview_type')
                ->get(),
        ];

        return response()->json([
            'job_openings' => $jobOpeningStats,
            'applications' => $applicationStats,
            'interviews' => $interviewStats,
        ]);
    }
}