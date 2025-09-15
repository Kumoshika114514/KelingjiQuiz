<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-6">
    <div class="bg-white dark:bg-gray-900 shadow sm:rounded-lg p-6">

        <div class="flex justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Comments</h3>

            <!-- Sort by -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center gap-2 text-sm border rounded px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white"
                        title="Sort comments">
                    Sort by
                </button>
                <div x-show="open" @click.away="open = false" class="absolute right-0 bg-white border rounded shadow mt-1 w-40">
                    <a href="{{ route('teacher.questionset', ['quizClassId' => $questionSet->class_id, 'questionSetId' => $questionSet->id, 'sort' => 'likes']) }}"
                        class="block px-4 py-2 hover:bg-gray-100 {{ $sort === 'likes' ? 'bg-gray-300' : '' }}">Most Liked</a>
                    <a href="{{ route('teacher.questionset', ['quizClassId' => $questionSet->class_id, 'questionSetId' => $questionSet->id, 'sort' => 'latest']) }}"
                        class="block px-4 py-2 hover:bg-gray-100 {{ $sort === 'latest' ? 'bg-gray-300' : '' }}">Latest</a>
                    <a href="{{ route('teacher.questionset', ['quizClassId' => $questionSet->class_id, 'questionSetId' => $questionSet->id, 'sort' => 'oldest']) }}"
                        class="block px-4 py-2 hover:bg-gray-100 {{ $sort === 'oldest' ? 'bg-gray-300' : '' }}">Oldest</a>
                </div>
            </div>
        </div>

        <!-- User add a comment -->
        <div x-data="{ open: false, content: '' }" class="mb-6">
            <div x-show="!open" @click="open = true; $nextTick(() => $refs.commentInput.focus())"
                class="border rounded px-3 py-2 text-gray-500 cursor-text">
                Add a comment...
            </div>
        
            <form method="POST" action="{{ route('comments.store', ['questionSet' => $questionSetId]) }}" x-show="open">
                @csrf
                <input type="hidden" name="question_set_id" value="{{ $questionSetId }}">
        
                <textarea x-ref="commentInput" x-model="content"
                    @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px';" name="comment_content"
                    rows="1" class="w-full border rounded p-2 resize-none overflow-hidden" placeholder="Write your comment..."
                    required></textarea>
        
                <div class="flex justify-end gap-2 mt-1">
                    <button type="button" @click="open=false;content=''" class="px-4 py-1 rounded-full hover:bg-gray-200">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-1 text-white rounded-full bg-indigo-600 hover:bg-indigo-700">
                        Comment
                    </button>
                </div>
            </form>
        </div>

        <!-- User Comments -->
        <ul class="space-y-4">
            @foreach($comments as $comment)
            <li class="border-b p-3">

                <div class="flex justify-between" x-data="{ editing: false, open: false }">

                    <div class="w-full">
                        <span class="font-semibold">{{ $comment->user->name }}</span>
                        <span class="text-gray-500 text-xs ml-2">{{ $comment->created_at->diffForHumans() }}</span>

                        <template x-if="!editing">
                            <p>{{ $comment->comment_content }}</p>
                        </template>

                        <template x-if="editing">
                            <form method="POST" action="{{ route('comments.update', $comment) }}" class="mt-1 flex flex-col gap-2">
                                @csrf 
                                @method('PUT')
                                <textarea x-model="content" x-init="content = {{ json_encode($comment->comment_content) }}"
                                    @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px';" name="comment_content"
                                    rows="1" class="w-full border rounded p-2 resize-none overflow-hidden" placeholder="Edit your comment..."
                                    required></textarea>
                                <div class="flex gap-2 justify-end mt-1">
                                    <button type="button" @click="editing = false" class="px-4 py-1 rounded-full hover:bg-gray-200">
                                        Cancel
                                    </button>
                                    <button type="submit" class="px-4 py-1 text-white rounded-full bg-indigo-600 hover:bg-indigo-700">
                                        Save
                                    </button>
                                </div>
                            </form>
                        </template>

                        <div class="flex items-center gap-4 mt-2 text-xs">
                            @php 
                            $userLiked = $comment->likes->contains('user_id', Auth::id()); 
                            @endphp
                            <form method="POST" action="{{ route('comments.like', $comment->id) }}">
                                @csrf
                                <button type="submit" class="px-2 py-1 rounded-full hover:bg-gray-200
                                                    {{ $userLiked ? 'text-white bg-blue-600' : '' }}">
                                    👍 {{ $comment->likes_count }}
                                </button>
                            </form>
                            <button @click="$dispatch('toggle-reply', { id: {{ $comment->id }} })"  
                                    class="px-2 py-1 rounded-full hover:bg-gray-200 font-semibold">
                                Reply
                            </button>
                        </div>
                    </div>

                    @if(Auth::id() === $comment->user_id || Auth::user()->isTeacher())
                    <div class="relative">

                        <button @click="open = !open" class="px-4 py-2 rounded-full hover:bg-gray-200">⋮</button>

                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-1 w-20 bg-white border rounded shadow">

                            @if(Auth::id() === $comment->user_id)
                            <button @click="editing = true; open = false"
                                    class="block px-4 py-2 w-full text-left hover:bg-gray-100">
                                Edit
                            </button>
                            @endif

                            <form method="POST" action="{{ route('comments.destroy', $comment->id) }}">
                                @csrf 
                                @method('DELETE')
                                <button class="block px-4 py-2 w-full text-left hover:bg-gray-100">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Reply form -->
                <div x-data="{ open: false, content: '' }" @toggle-reply.window="if ($event.detail.id === {{ $comment->id }}) open = !open" 
                        class="ml-6 mt-6">
                    <div x-show="open">
                        <form method="POST" action="{{ route('comments.reply', $comment->id) }}">
                            @csrf
                            <input type="hidden" name="question_set_id" value="{{ $questionSetId }}">
                            <textarea x-model="content" @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px';"
                                    name="comment_content" rows="1" class="w-full border rounded p-2 resize-none overflow-hidden"
                                    placeholder="Write your reply..." required></textarea>
                            <div class="flex justify-end gap-2 mt-1">
                                <button type="button" @click="open=false; content=''" class="px-4 py-1 rounded-full hover:bg-gray-200">
                                    Cancel
                                </button>
                                <button type="submit" class="px-4 py-1 text-white rounded-full bg-indigo-600 hover:bg-indigo-700">
                                    Reply
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Replies -->
                @if($comment->replies->count() > 0)
                <div x-data="{ open: false }" class="mt-2">
                    <button @click="open = !open" class="text-sm text-blue-600 flex items-center gap-1">
                        <span x-show="!open">▼</span>
                        <span x-show="open">▲</span>
                        {{ $comment->replies->count() }} replies
                    </button>
                    <ul x-show="open" class="ml-6 mt-2 space-y-2">
                        @foreach($comment->replies as $reply)
                        <li>
                            <div class="flex justify-between" x-data="{ editing: false, open: false }">
                                <div class="w-full">
                                    <span class="font-semibold">{{ $reply->user->name }}</span>
                                    <span class="text-gray-500 text-xs ml-2">{{ $reply->created_at->diffForHumans() }}</span>

                                    <template x-if="!editing">
                                        <p>{{ $reply->comment_content }}</p>
                                    </template>

                                    <template x-if="editing">
                                        <form method="POST" action="{{ route('comments.update', $reply) }}" class="mt-1 flex flex-col gap-2">
                                            @csrf 
                                            @method('PUT')
                                            <textarea x-model="content" x-init="content = {{ json_encode($reply->comment_content) }}"
                                                @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px';" name="comment_content"
                                                rows="1" class="w-full border rounded p-2 resize-none overflow-hidden" placeholder="Edit your comment..."
                                                required></textarea>
                                            <div class="flex gap-2 justify-end mt-1">
                                                <button type="button" @click="editing = false" class="px-4 py-1 rounded-full hover:bg-gray-200">
                                                    Cancel
                                                </button>
                                                <button type="submit" class="px-4 py-1 text-white rounded-full bg-indigo-600 hover:bg-indigo-700">
                                                    Save
                                                </button>
                                            </div>
                                        </form>
                                    </template>
                                </div>

                                @if(Auth::id() === $reply->user_id || Auth::user()->isTeacher())
                                <div class="relative">
                                    <button @click="open = !open" class="px-4 py-2 rounded-full hover:bg-gray-200">⋮</button>

                                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-1 w-20 bg-white border rounded shadow">
                                        @if(Auth::id() === $reply->user_id)
                                        <button @click="editing = true; open = false"
                                                class="block px-4 py-2 w-full text-left hover:bg-gray-100">
                                            Edit
                                        </button>
                                        @endif

                                        <form method="POST" action="{{ route('comments.destroy', $reply->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="block px-4 py-2 w-full text-left hover:bg-gray-100">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </li>
            @endforeach
        </ul>

        <div class="mt-4">
            {{ $comments->links() }}
        </div>

    </div>
</div>
