<x-app-layout>
    <div class="p-6 bg-white dark:bg-gray-800 shadow rounded-xl">
        <h1 class="text-2xl font-bold mb-4 text-gray-900 dark:text-white">Job Application Detail</h1>
        
        <div class="mb-6">
            <p><strong>DocID:</strong> {{ $jobapply->docid }}</p>
            <p><strong>Applicant ID:</strong> {{ $jobapply->applicant_id }}</p>
            <p><strong>Apply Date:</strong> {{ $jobapply->apply_date }}</p>
            <p><strong>Step:</strong> 
                @if ($jobapply->apply_step == 'JOAPP')
                    <span class="text-blue-600 font-semibold">Job Apply</span>
                @elseif ($jobapply->apply_step == 'WIHC')
                    <span class="text-blue-600 font-semibold">Waiting Interview HC</span>
                @elseif ($jobapply->apply_step == 'IHC')
                    <span class="text-blue-600 font-semibold">Interview HC</span>
                @elseif ($jobapply->apply_step == 'WIU')
                    <span class="text-blue-600 font-semibold">Waiting Interview User</span>
                @elseif ($jobapply->apply_step == 'IU')
                    <span class="text-blue-600 font-semibold">Interview User</span>
                @elseif ($jobapply->apply_step == 'WPT')
                    <span class="text-blue-600 font-semibold">Waiting Psycho Test</span>
                @elseif ($jobapply->apply_step == 'PT')
                    <span class="text-blue-600 font-semibold">Psycho Test</span>
                @elseif ($jobapply->apply_step == 'OFF')
                    <span class="text-blue-600 font-semibold">Offering</span>
                @elseif ($jobapply->apply_step == 'JOIN')
                    <span class="text-blue-600 font-semibold">Join</span>
                @else
                    <span class="text-red-600 font-semibold">-</span>
                @endif
            </p>
            <p><strong>Status:</strong> 
                @if ($jobapply->status == 'P')
                    <span class="text-blue-600 font-semibold">On Progress</span>
                @elseif ($jobapply->status == 'R')
                    <span class="text-blue-600 font-semibold">Rejected</span>
                @elseif ($jobapply->status == 'C')
                    <span class="text-blue-600 font-semibold">Completed</span>               
                @else
                    <span class="text-red-600 font-semibold">-</span>
                @endif
            </p>
        </div>

        @if($jobposting)
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-2">Job Posting</h2>
            <p><strong>Title:</strong> {{ $jobposting->job_title ?? '-' }}</p>
            <p><strong>Type:</strong> {{ $jobposting->job_type ?? '-' }}</p>
            <p><strong>Location:</strong> Jakarta</p>
            <p><strong>Description:</strong></p>
            <div class="prose dark:prose-invert max-w-full">Please join our team to Pakuwon Group.</div>
        </div>
        @endif

        @if($jobres->count())
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-2">Responsibilities</h2>
            <ul class="list-disc list-inside text-gray-700 dark:text-gray-300">
                @foreach($jobres as $res)
                    <li>{{ $res->job_responsibilities_descr }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if($jobqua->count())
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-2">Qualifications</h2>
            <ul class="list-disc list-inside text-gray-700 dark:text-gray-300">
                @foreach($jobqua as $qua)
                    <li>{{ $qua->job_qualification_descr }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <a href="{{ route('myjobapply') }}"
           class="inline-block mt-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded">
            ← Back to Applications
        </a>
    </div>
</x-app-layout>
