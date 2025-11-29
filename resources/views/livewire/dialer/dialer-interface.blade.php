<div>
    <div class="{{ $embedded ? '' : 'p-6' }}">
        <div class="{{ $embedded ? '' : 'max-w-7xl mx-auto' }}">
            @if (!$embedded)
                <h1 class="text-2xl font-bold mb-6">Dialer</h1>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    @if ($activeCallSession)
                        <div
                            class="bg-white dark:bg-zinc-900 rounded-lg shadow border border-zinc-200 dark:border-zinc-700 p-6">
                            <h2 class="text-lg font-semibold mb-4">Active Call</h2>
                            <div class="space-y-2 mb-4">
                                <p><strong>Contact:</strong> {{ $activeCallSession->contact->full_name }}</p>
                                <p><strong>Phone:</strong> {{ $activeCallSession->contact->phone }}</p>
                                <p><strong>Status:</strong> <span
                                        id="call-status">{{ ucfirst(str_replace('_', ' ', $activeCallSession->status)) }}</span>
                                </p>
                            </div>
                            <div class="flex flex-wrap gap-4">
                                <button id="mute-btn"
                                    class="bg-zinc-200 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-200 px-4 py-2 rounded hover:bg-zinc-300 dark:hover:bg-zinc-600 cursor-pointer">
                                    Mute
                                </button>
                                <button id="hangup-btn"
                                    class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 cursor-pointer">
                                    Hang Up
                                </button>
                            </div>
                        </div>

                        <div
                            class="bg-white dark:bg-zinc-900 rounded-lg shadow border border-zinc-200 dark:border-zinc-700 p-6">
                            <h2 class="text-lg font-semibold mb-4">Transcript</h2>
                            <div id="transcript-panel" wire:poll.10s="reloadTranscripts"
                                class="h-96 overflow-y-auto space-y-3 p-2">
                                @forelse ($transcripts as $index => $transcript)
                                    @php
                                        $isVa = $transcript['speaker'] === 'va';
                                        $isSystem = $transcript['speaker'] === 'system';
                                    @endphp
                                    @if ($isSystem)
                                        {{-- System messages centered --}}
                                        <div wire:key="transcript-system-{{ $index }}-{{ $transcript['timestamp'] }}"
                                            class="flex justify-center">
                                            <div
                                                class="px-3 py-1.5 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 text-sm">
                                                {{ $transcript['text'] }}
                                            </div>
                                        </div>
                                    @else
                                        {{-- Chat-like messages: VA on right, Prospect on left --}}
                                        <div wire:key="transcript-{{ $transcript['speaker'] }}-{{ $index }}-{{ $transcript['timestamp'] }}"
                                            class="flex {{ $isVa ? 'justify-end' : 'justify-start' }}">
                                            <div
                                                class="max-w-[75%] flex gap-2 {{ $isVa ? 'flex-row-reverse' : 'flex-row' }}">
                                                {{-- Avatar --}}
                                                <div
                                                    class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold {{ $isVa ? 'bg-blue-500 text-white' : 'bg-zinc-500 text-white' }}">
                                                    {{ $isVa ? 'VA' : 'P' }}
                                                </div>
                                                {{-- Message bubble --}}
                                                <div class="flex flex-col {{ $isVa ? 'items-end' : 'items-start' }}">
                                                    <div
                                                        class="px-4 py-2 rounded-lg {{ $isVa ? 'bg-blue-500 text-white rounded-br-sm' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 rounded-bl-sm' }}">
                                                        <p class="text-sm whitespace-pre-wrap break-words">
                                                            {{ $transcript['text'] }}</p>
                                                    </div>
                                                    @if (isset($transcript['timestamp']))
                                                        <span
                                                            class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 px-1">
                                                            {{ gmdate('H:i:s', (int) $transcript['timestamp']) }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @empty
                                    <div
                                        class="flex items-center justify-center h-full text-zinc-500 dark:text-zinc-400">
                                        <p>No transcript yet. Waiting for conversation...</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @else
                        <div
                            class="bg-white dark:bg-zinc-900 rounded-lg shadow border border-zinc-200 dark:border-zinc-700 p-6 text-center">
                            <p class="text-zinc-500 dark:text-zinc-400 mb-4">No active call</p>
                            <button wire:click="callNext"
                                class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 cursor-pointer">
                                Call Next
                            </button>
                        </div>
                    @endif
                </div>

                <div class="space-y-6">
                    @if ($activeCallSession && $currentSuggestion)
                        <div
                            class="bg-white dark:bg-zinc-900 rounded-lg shadow border border-zinc-200 dark:border-zinc-700 p-6">
                            <h2 class="text-lg font-semibold mb-4">AI Suggestions</h2>
                            @if ($conversationState)
                                <div class="mb-4">
                                    <span
                                        class="px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full text-sm">
                                        {{ ucfirst(str_replace('_', ' ', $conversationState)) }}
                                    </span>
                                </div>
                            @endif
                            <div class="mb-4">
                                <p class="text-zinc-700 dark:text-zinc-300">{{ $currentSuggestion }}</p>
                            </div>
                            @if (count($flags) > 0)
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($flags as $flag)
                                        <span
                                            class="px-2 py-1 bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300 rounded text-xs">
                                            {{ ucfirst(str_replace('_', ' ', $flag)) }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if ($activeCallSession)
        <div x-data x-init="(async function() {
            const callSessionId = {{ $activeCallSession->id }};
            const expectedContactId = {{ $activeCallSession->contact_id }};
            const initialContactId = {{ $initialContactId ?? 'null' }};

            // Verify the call session matches the expected contact
            if (initialContactId !== null && expectedContactId !== initialContactId) {
                console.error('Mismatch detected: Call session contact ID does not match initial contact ID', {
                    callSessionContactId: expectedContactId,
                    initialContactId: initialContactId,
                    callSessionId: callSessionId
                });
                // Don't initialize call if there's a mismatch - let Livewire handle it
                return;
            }

            // Prevent multiple initializations for the same call session
            if (window.activeCallInitialized && window.activeCallInitialized === callSessionId) {
                console.log('Call already initialized for session:', callSessionId);
                return;
            }
            window.activeCallInitialized = callSessionId;

            console.log('Initializing call for session:', callSessionId);

            // Request microphone permission FIRST
            try {
                console.log('Requesting microphone permission...');
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                stream.getTracks().forEach(track => track.stop());
                console.log('Microphone permission granted');
            } catch (error) {
                console.error('Microphone permission denied:', error);
                alert('Microphone access is required to make calls. Please allow microphone access and try again.');
                return;
            }

            // Wait for Livewire to be ready
            await new Promise((resolve) => {
                if (window.Livewire) {
                    resolve();
                } else {
                    document.addEventListener('livewire:init', resolve);
                }
            });

            // Wait for Twilio SDK to be loaded
            await new Promise((resolve) => {
                if (window.Twilio && window.Twilio.Device) {
                    resolve();
                } else {
                    const checkTwilio = setInterval(() => {
                        if (window.Twilio && window.Twilio.Device) {
                            clearInterval(checkTwilio);
                            resolve();
                        }
                    }, 100);
                }
            });

            const { Device } = window.Twilio;

            // Build TwiML URL with call session ID as query parameter
            // This works with the Twilio Application Voice URL which points to /api/twilio/twiml
            const baseUrl = window.location.origin;
            const twimlUrl = `${baseUrl}/api/twilio/twiml?callSession=${callSessionId}`;
            console.log('TwiML URL:', twimlUrl);
            console.log('Call Session ID:', callSessionId);
            console.log('Expected Contact ID:', {{ $activeCallSession->contact_id }});

            const componentId = '{{ $this->getId() }}';
            let device = null;
            let call = null;
            let isMuted = false;

            // Get Livewire component instance
            const getLivewireComponent = () => {
                if (window.Livewire) {
                    return window.Livewire.find(componentId);
                }
                return null;
            };

            // Get Twilio access token
            try {
                const csrfMeta = document.querySelector('meta[name=csrf-token]');
                const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
                const tokenResponse = await fetch('/api/twilio/token', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });

                if (!tokenResponse.ok) {
                    const errorData = await tokenResponse.json().catch(() => ({ error: 'Unknown error' }));
                    const errorMessage = errorData.error || `HTTP ${tokenResponse.status}: ${tokenResponse.statusText}`;
                    console.error('Token API error:', errorMessage, errorData);
                    throw new Error(`Failed to get access token: ${errorMessage}`);
                }

                const { token } = await tokenResponse.json();

                // Initialize Twilio Device
                device = new Device(token, {
                    logLevel: 1,
                    codecPreferences: ['opus', 'pcmu']
                });

                device.on('registered', () => {
                    console.log('Twilio Device registered');
                    updateCallStatus('Connecting...');
                    refreshComponent();
                });

                device.on('error', (error) => {
                    console.error('Twilio Device error:', error);
                    updateCallStatus('Error: ' + error.message);
                    refreshComponent();
                });

                // Make the call
                const params = {
                    To: twimlUrl
                };

                call = await device.connect({ params });

                call.on('accept', () => {
                    console.log('Call accepted', call);
                    updateCallStatus('In Progress');
                    refreshComponent();

                    // Get CallSid from call parameters
                    const component = getLivewireComponent();
                    if (call && call.parameters && call.parameters.CallSid) {
                        component?.call('updateCallSid', call.parameters.CallSid).then(() => {
                            refreshComponent();
                        });
                    } else if (call && call.sid) {
                        component?.call('updateCallSid', call.sid).then(() => {
                            refreshComponent();
                        });
                    }
                });

                call.on('disconnect', () => {
                    console.log('Call disconnected');
                    updateCallStatus('Disconnected');
                    const component = getLivewireComponent();
                    if (component) {
                        component.call('endCall').then(() => {
                            refreshComponent();
                        });
                    }
                    cleanup();
                });

                call.on('cancel', () => {
                    console.log('Call canceled');
                    updateCallStatus('Canceled');
                    const component = getLivewireComponent();
                    if (component) {
                        component.call('endCall').then(() => {
                            refreshComponent();
                        });
                    }
                    cleanup();
                });

                call.on('reject', () => {
                    console.log('Call rejected');
                    updateCallStatus('Rejected');
                    const component = getLivewireComponent();
                    if (component) {
                        component.call('endCall').then(() => {
                            refreshComponent();
                        });
                    }
                    cleanup();
                });

                // Mute button
                const muteBtn = document.getElementById('mute-btn');
                if (muteBtn) {
                    muteBtn.addEventListener('click', () => {
                        if (call) {
                            if (isMuted) {
                                call.mute(false);
                                isMuted = false;
                                muteBtn.textContent = 'Mute';
                            } else {
                                call.mute(true);
                                isMuted = true;
                                muteBtn.textContent = 'Unmute';
                            }
                        }
                    });
                }

                // Hang up button
                const hangupBtn = document.getElementById('hangup-btn');
                if (hangupBtn) {
                    hangupBtn.addEventListener('click', () => {
                        if (call) {
                            call.disconnect();
                        }
                    });
                }

                function updateCallStatus(status) {
                    const statusElement = document.getElementById('call-status');
                    if (statusElement) {
                        statusElement.textContent = status;
                    }
                }

                function refreshComponent() {
                    const component = getLivewireComponent();
                    if (component) {
                        component.call('$refresh');
                    }
                }

                function scrollTranscriptToBottom() {
                    const transcriptPanel = document.getElementById('transcript-panel');
                    if (transcriptPanel) {
                        transcriptPanel.scrollTo({
                            top: transcriptPanel.scrollHeight,
                            behavior: 'smooth'
                        });
                    }
                }

                // Auto-scroll on initial load
                setTimeout(() => {
                    scrollTranscriptToBottom();
                }, 500);

                function cleanup() {
                    if (device) {
                        device.destroy();
                        device = null;
                    }
                    call = null;
                }

                // Cleanup on page unload
                window.addEventListener('beforeunload', cleanup);

                // Echo listeners for transcripts and AI suggestions (optional - works without websockets)
                if (window.Echo) {
                    try {
                        const channel = window.Echo.private(`call-session.${callSessionId}`);

                        channel.listen('.transcript.updated', (e) => {
                            console.log('Transcript updated event received:', e);
                            const component = getLivewireComponent();

                            // Validate event data
                            if (!e || typeof e !== 'object') {
                                console.warn('Invalid event data received:', e);
                                return;
                            }

                            if (component && e.speaker && e.text !== undefined && e.text !== null && e.text !== '') {
                                // Fast path: directly add transcript from event payload (no DB query)
                                console.log('Adding transcript directly from event:', {
                                    speaker: e.speaker,
                                    text: e.text.substring(0, 50) + '...',
                                    timestamp: e.timestamp
                                });

                                component.call('addTranscript', e.speaker, e.text, e.timestamp || 0)
                                    .then(() => {
                                        console.log('Transcript added successfully');
                                        refreshComponent();
                                        // Auto-scroll to bottom after new transcript
                                        setTimeout(() => {
                                            scrollTranscriptToBottom();
                                        }, 100);
                                    })
                                    .catch((error) => {
                                        console.error('Error adding transcript, falling back to reload:', error);
                                        // Fallback to reload if direct add fails
                                        if (component) {
                                            component.call('reloadTranscripts')
                                                .then(() => {
                                                    refreshComponent();
                                                    setTimeout(() => {
                                                        scrollTranscriptToBottom();
                                                    }, 100);
                                                })
                                                .catch((reloadError) => {
                                                    console.error('Error reloading transcripts:', reloadError);
                                                    refreshComponent();
                                                });
                                        } else {
                                            refreshComponent();
                                        }
                                    });
                            } else {
                                // Fallback: reload all transcripts if event data is incomplete
                                console.warn('Event data incomplete, reloading all transcripts', {
                                    hasComponent: !!component,
                                    hasSpeaker: !!e.speaker,
                                    hasText: e.text !== undefined,
                                    textValue: e.text
                                });

                                if (component) {
                                    component.call('reloadTranscripts')
                                        .then(() => {
                                            refreshComponent();
                                            setTimeout(() => {
                                                scrollTranscriptToBottom();
                                            }, 100);
                                        })
                                        .catch((error) => {
                                            console.error('Error reloading transcripts:', error);
                                            refreshComponent();
                                        });
                                } else {
                                    console.warn('No Livewire component found, refreshing page');
                                    refreshComponent();
                                }
                            }
                        });

                        // Log when channel is subscribed
                        channel.subscribed(() => {
                            console.log('Echo channel subscribed successfully for call session:', callSessionId);
                            // Reload transcripts once when channel is ready to ensure we have latest
                            const component = getLivewireComponent();
                            if (component) {
                                component.call('reloadTranscripts').catch(err => {
                                    console.error('Error reloading transcripts on channel subscribe:', err);
                                });
                            }
                        });

                        // Log subscription errors
                        channel.error((error) => {
                            console.error('Echo channel subscription error:', error);
                        });

                        channel.listen('.ai.suggestion.updated', (e) => {
                            console.log('AI suggestion updated event received:', e);
                            const component = getLivewireComponent();
                            if (component) {
                                component.call('reloadAiState').then(() => {
                                    refreshComponent();
                                });
                            } else {
                                refreshComponent();
                            }
                        });
                    } catch (error) {
                        console.warn('Echo channel setup failed, using polling instead:', error);
                        // Fallback to polling if websockets fail
                        const pollInterval = setInterval(() => {
                            const component = getLivewireComponent();
                            if (component) {
                                component.call('reloadTranscripts').then(() => {
                                    refreshComponent();
                                    setTimeout(() => {
                                        scrollTranscriptToBottom();
                                    }, 100);
                                }).catch(() => {
                                    refreshComponent();
                                    setTimeout(() => {
                                        scrollTranscriptToBottom();
                                    }, 100);
                                });
                            } else {
                                refreshComponent();
                                setTimeout(() => {
                                    scrollTranscriptToBottom();
                                }, 100);
                            }
                        }, 3000);

                        // Stop polling when call ends
                        if (call) {
                            call.on('disconnect', () => clearInterval(pollInterval));
                            call.on('cancel', () => clearInterval(pollInterval));
                            call.on('reject', () => clearInterval(pollInterval));
                        }
                    }
                } else {
                    // Fallback to polling if Echo is not available
                    console.log('WebSockets not available, using polling for updates');
                    const pollInterval = setInterval(() => {
                        const component = getLivewireComponent();
                        if (component) {
                            component.call('reloadTranscripts').then(() => {
                                refreshComponent();
                                setTimeout(() => {
                                    scrollTranscriptToBottom();
                                }, 100);
                            }).catch(() => {
                                refreshComponent();
                                setTimeout(() => {
                                    scrollTranscriptToBottom();
                                }, 100);
                            });
                        } else {
                            refreshComponent();
                            setTimeout(() => {
                                scrollTranscriptToBottom();
                            }, 100);
                        }
                    }, 3000);

                    // Stop polling when call ends
                    if (call) {
                        call.on('disconnect', () => clearInterval(pollInterval));
                        call.on('cancel', () => clearInterval(pollInterval));
                        call.on('reject', () => clearInterval(pollInterval));
                    }
                }
            } catch (error) {
                console.error('Error initializing call:', error);
                alert('Failed to initialize call: ' + error.message);
            }
        })();"></div>
    @endif
</div>
