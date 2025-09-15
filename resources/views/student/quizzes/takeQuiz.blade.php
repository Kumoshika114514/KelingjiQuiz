<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $questionSet->topic }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Modern Real-Time Timer -->
            <div class="flex items-center justify-center mb-6">
                <div class="bg-gradient-to-r from-blue-500 to-green-400 text-white rounded-full px-6 py-3 shadow-lg flex items-center gap-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                    </svg>
                    <span id="timer" class="text-2xl font-mono font-bold tracking-widest"></span>
                </div>
            </div>
            <input type="hidden" id="time_left" name="time_left" value="">

            <!-- Quiz Description Section -->
            <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
                <h3 class="font-semibold text-lg">Description:</h3>
                <p>{{ $questionSet->description ?? 'No description.' }}</p>
                @if(isset($questionSet->time_limit))
                    <p>Time Limit: {{ $questionSet->time_limit }} minutes</p>
                @endif
            </div>

            <!-- Questions Section -->
            <form method="POST" action="{{ route('student.quizzes.submit', $questionSet->id) }}">
                @csrf
                <h3 class="font-semibold text-lg mb-4">Questions:</h3>
                @forelse($questionSet->questions as $index => $question)
                    <div class="mb-6 p-4 bg-gray-50 rounded">
                        <div class="mb-2">
                            <strong>{{ $index + 1 }}.</strong>
                            {{ $question->text ?? $question->text ?? $question->content ?? $question->question ?? '[No question text found]' }}
                        </div>
                        @if(!empty($question->answer_a) && !empty($question->answer_b))
                            <!-- Multiple Choice -->
                            <div class="ml-4">
                                <div>
                                    <label>
                                        <input type="radio" name="answers[{{ $question->id }}]" value="A">
                                        {{ $question->answer_a }}
                                    </label>
                                </div>
                                <div>
                                    <label>
                                        <input type="radio" name="answers[{{ $question->id }}]" value="B">
                                        {{ $question->answer_b }}
                                    </label>
                                </div>
                                @if(!empty($question->answer_c))
                                    <div>
                                        <label>
                                            <input type="radio" name="answers[{{ $question->id }}]" value="C">
                                            {{ $question->answer_c }}
                                        </label>
                                    </div>
                                @endif
                                @if(!empty($question->answer_d))
                                    <div>
                                        <label>
                                            <input type="radio" name="answers[{{ $question->id }}]" value="D">
                                            {{ $question->answer_d }}
                                        </label>
                                    </div>
                                @endif
                                @error("answers.$question->id")
                                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @elseif(isset($question->correct_bool))
                            <!-- True/False -->
                            <div class="ml-4">
                                <div>
                                    <label>
                                        <input type="radio" name="answers[{{ $question->id }}]" value="1">
                                        True
                                    </label>
                                </div>
                                <div>
                                    <label>
                                        <input type="radio" name="answers[{{ $question->id }}]" value="0">
                                        False
                                    </label>
                                </div>
                                @error("answers.$question->id")
                                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @else
                            <!-- Short Answer -->
                            <input type="text" name="answers[{{ $question->id }}]" class="border rounded px-2 py-1 w-full"
                                placeholder="Your answer">
                            @error("answers.$question->id")
                                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>
                @empty
                    <p>No questions found for this Question Set.</p>
                @endforelse
                <button type="submit" class="btn btn-primary">Submit Quiz</button>
            </form>
        </div>
    </div>

    <!-- Real-Time Timer & Live Monitoring Script -->
    <script>
        // Timer setup
        let timeLimit = {{ $questionSet->time_limit ? $questionSet->time_limit * 60 : 300 }};
        let timerDisplay = document.getElementById('timer');
        let timeLeftInput = document.getElementById('time_left');

        function updateTimer() {
            let minutes = Math.floor(timeLimit / 60);
            let seconds = timeLimit % 60;
            timerDisplay.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            timeLeftInput.value = timeLimit;
            if (timeLimit <= 0) {
                alert('Time is up! Your quiz will be submitted automatically.');
                document.querySelector('form').submit();
            } else {
                timeLimit--;
                setTimeout(updateTimer, 1000);
            }
        }
        updateTimer();

        // Live monitoring: send progress every 10 seconds
        setInterval(function() {
            let formData = new FormData(document.querySelector('form'));
            fetch("{{ route('student.quizzes.liveUpdate', $questionSet->id) }}", {
                method: "POST",
                headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                body: formData
            });
        }, 10000);
    </script>
</x-app-layout>