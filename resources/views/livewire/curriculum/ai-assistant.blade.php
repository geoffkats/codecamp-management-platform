<div>
    {{-- Floating Chat Toggle Button --}}
    <button 
        wire:click="toggleChat"
        class="fixed bottom-6 right-6 z-50 p-4 bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white rounded-full shadow-2xl transition-all duration-300 transform hover:scale-110 group"
        title="AI Curriculum Assistant">
        <div class="flex items-center gap-2">
            @if($showChat ?? false)
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            @else
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
                <span class="font-semibold">AI Assistant</span>
            @endif
        </div>
        
        {{-- Notification dot --}}
        <span class="absolute top-0 right-0 w-3 h-3 bg-green-400 rounded-full animate-pulse"></span>
    </button>

    {{-- Floating Chat Window --}}
    <div 
        x-data="{ show: @entangle('showChat') }"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-x-full"
        x-transition:enter-end="opacity-100 transform translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform translate-x-0"
        x-transition:leave-end="opacity-0 transform translate-x-full"
        class="fixed bottom-6 right-6 z-40 w-96 h-[600px] bg-white dark:bg-gray-800 rounded-2xl shadow-2xl flex flex-col overflow-hidden border border-gray-200 dark:border-gray-700"
        style="display: none;">
        
        {{-- Chat Header --}}
        <div class="bg-gradient-to-r from-purple-600 to-blue-600 px-6 py-4 text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-lg">AI Assistant</h3>
                    <p class="text-xs text-white/80">Powered by Gemini</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button 
                    wire:click="clearChat"
                    class="p-2 hover:bg-white/10 rounded-lg transition-colors"
                    title="Clear chat">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
                <button 
                    wire:click="closeChat"
                    class="p-2 hover:bg-white/10 rounded-lg transition-colors"
                    title="Close">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-wrap gap-2">
                <button 
                    wire:click="quickAction('outline')"
                    class="px-3 py-1 text-xs bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded-full hover:bg-purple-200 dark:hover:bg-purple-900/50 transition-colors">
                    📋 Outline
                </button>
                <button 
                    wire:click="quickAction('lesson')"
                    class="px-3 py-1 text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-full hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors">
                    📖 Lesson
                </button>
                <button 
                    wire:click="quickAction('quiz')"
                    class="px-3 py-1 text-xs bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-full hover:bg-green-200 dark:hover:bg-green-900/50 transition-colors">
                    ❓ Quiz
                </button>
                <button 
                    wire:click="quickAction('improve')"
                    class="px-3 py-1 text-xs bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 rounded-full hover:bg-orange-200 dark:hover:bg-orange-900/50 transition-colors">
                    ✨ Improve
                </button>
            </div>
        </div>

        {{-- Messages Area --}}
        <div 
            class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50 dark:bg-gray-900"
            x-data="{ 
                scrollToBottom() {
                    this.$el.scrollTop = this.$el.scrollHeight;
                }
            }"
            x-init="scrollToBottom()"
            @message-added.window="$nextTick(() => scrollToBottom())"
            wire:key="messages-container">
            
            @forelse($messages ?? [] as $index => $message)
                <div 
                    wire:key="message-{{ $index }}"
                    class="flex {{ $message['type'] === 'user' ? 'justify-end' : 'justify-start' }}">
                    
                    @if($message['type'] === 'user')
                        {{-- User Message --}}
                        <div class="max-w-[80%] bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-2xl rounded-tr-sm px-4 py-3 shadow-md">
                            <p class="text-sm whitespace-pre-wrap">{{ $message['content'] }}</p>
                            <p class="text-xs text-white/70 mt-1">{{ $message['timestamp']->format('H:i') }}</p>
                        </div>
                    @elseif($message['type'] === 'ai')
                        {{-- AI Message --}}
                        <div class="max-w-[85%] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl rounded-tl-sm px-4 py-3 shadow-md">
                            <div class="flex items-start gap-2 mb-2">
                                <div class="w-6 h-6 bg-gradient-to-r from-purple-600 to-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                    </svg>
                                </div>
                                <span class="text-xs font-semibold text-purple-600 dark:text-purple-400">AI Assistant</span>
                            </div>
                            <div class="text-sm text-gray-800 dark:text-gray-200" 
                                 x-data="{ 
                                     formattedContent: '' 
                                 }"
                                 x-init="
                                     const content = `{{ addslashes($message['content']) }}`;
                                     // Simple markdown-like formatting
                                     let formatted = content
                                         .replace(/\*\*(.+?)\*\*/g, '<strong class=\'font-bold\'>$1</strong>')
                                         .replace(/\*(.+?)\*/g, '<em class=\'italic\'>$1</em>')
                                         .replace(/^###\s(.+)$/gm, '<h3 class=\'font-bold text-base mt-3 mb-2\'>$1</h3>')
                                         .replace(/^##\s(.+)$/gm, '<h2 class=\'font-bold text-lg mt-4 mb-2\'>$1</h2>')
                                         .replace(/^#\s(.+)$/gm, '<h1 class=\'font-bold text-xl mt-4 mb-2\'>$1</h1>')
                                         .replace(/^\*\s(.+)$/gm, '<li class=\'ml-4\'>$1</li>')
                                         .replace(/^-\s(.+)$/gm, '<li class=\'ml-4\'>$1</li>')
                                         .replace(/^(\d+)\.\s(.+)$/gm, '<li class=\'ml-4\'>$2</li>')
                                         .replace(/`(.+?)`/g, '<code class=\'bg-gray-100 dark:bg-gray-700 px-1 py-0.5 rounded text-xs font-mono\'>$1</code>');
                                     
                                     // Wrap consecutive <li> in <ul>
                                     formatted = formatted.replace(/(<li.*?<\/li>\n?)+/g, '<ul class=\'list-disc space-y-1 my-2\'>$&</ul>');
                                     
                                     formattedContent = formatted;
                                     $el.innerHTML = formattedContent;
                                 ">
                            </div>
                            <div class="flex items-center justify-between mt-2">
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $message['timestamp']->format('H:i') }}</p>
                                <button 
                                    onclick="
                                        let text = `{{ addslashes($message['content']) }}`;
                                        // Remove markdown formatting for clean copy
                                        text = text
                                            .replace(/\*\*(.+?)\*\*/g, '$1')
                                            .replace(/\*(.+?)\*/g, '$1')
                                            .replace(/^###\s/gm, '')
                                            .replace(/^##\s/gm, '')
                                            .replace(/^#\s/gm, '')
                                            .replace(/`(.+?)`/g, '$1');
                                        navigator.clipboard.writeText(text).then(() => {
                                            this.innerHTML = '<svg class=\'w-3 h-3\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M5 13l4 4L19 7\'></path></svg> Copied!';
                                            setTimeout(() => {
                                                this.innerHTML = '<svg class=\'w-3 h-3\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z\'></path></svg> Copy';
                                            }, 2000);
                                        });
                                    "
                                    class="text-xs text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                    Copy
                                </button>
                            </div>
                        </div>
                    @else
                        {{-- System Message --}}
                        <div class="w-full bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg px-4 py-2 text-center">
                            <p class="text-xs text-blue-800 dark:text-blue-200 whitespace-pre-wrap">{{ $message['content'] }}</p>
                        </div>
                    @endif
                </div>
            @empty
                <div class="flex items-center justify-center h-full text-gray-400 dark:text-gray-500">
                    <p class="text-sm">No messages yet</p>
                </div>
            @endforelse

            {{-- Loading Indicator --}}
            <div wire:loading wire:target="sendMessage" class="flex justify-start">
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl rounded-tl-sm px-4 py-3 shadow-md">
                    <div class="flex items-center gap-2">
                        <div class="flex gap-1">
                            <div class="w-2 h-2 bg-purple-600 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                            <div class="w-2 h-2 bg-purple-600 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                            <div class="w-2 h-2 bg-purple-600 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                        </div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">AI is thinking...</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Input Area --}}
        <div class="p-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
            <form wire:submit.prevent="sendMessage" class="flex gap-2">
                <input 
                    type="text"
                    wire:model="userMessage"
                    wire:keydown.enter.prevent="sendMessage"
                    placeholder="Ask me anything about your curriculum..."
                    class="flex-1 px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 text-sm"
                    wire:loading.attr="disabled"
                    wire:target="sendMessage">
                
                <button 
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="sendMessage"
                    x-bind:disabled="!$wire.userMessage"
                    class="px-4 py-3 bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 disabled:from-gray-400 disabled:to-gray-400 text-white rounded-xl transition-all duration-200 disabled:cursor-not-allowed flex items-center justify-center">
                    <span wire:loading.remove wire:target="sendMessage">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                    </span>
                    <span wire:loading wire:target="sendMessage">
                        <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                </button>
            </form>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 text-center">
                Press Enter to send • AI reads your course structure
            </p>
        </div>
    </div>
</div>
