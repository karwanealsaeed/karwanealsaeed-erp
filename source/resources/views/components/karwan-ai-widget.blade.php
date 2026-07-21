@php
    $karwanAiMessageLimit =
        (int) config(
            'karwan_ai.message_max_length',
            2000,
        );

    $karwanAiIsUrdu =
        app()->isLocale(
            'ur',
        );
@endphp

@if (config('karwan_ai.enabled'))
    <div
        class="karwan-ai-widget"
        id="karwanAiWidget"
        data-karwan-ai-widget
        dir="{{ $karwanAiIsUrdu ? 'rtl' : 'ltr' }}"
    >
        <details
            class="karwan-ai-details"
            id="karwanAiDetails"
        >
            <summary
                id="karwanAiToggle"
                aria-controls="karwanAiPanel"
                aria-expanded="false"
            >
                <span
                    class="karwan-ai-summary-icon"
                    aria-hidden="true"
                >🤖</span>

                <span>{{ __('website.ai_name') }}</span>

                <span
                    class="karwan-ai-summary-status"
                    aria-hidden="true"
                ></span>
            </summary>

            <section
                class="karwan-ai-panel"
                id="karwanAiPanel"
                role="region"
                aria-labelledby="karwanAiTitle"
            >
                <header class="karwan-ai-head">
                    <div>
                        <strong id="karwanAiTitle">
                            {{ __('website.ai_assistant') }}
                        </strong>

                        <span>{{ __('website.ai_services') }}</span>
                    </div>

                    <button
                        type="button"
                        id="karwanAiClear"
                        aria-label="{{ __('website.ai_clear') }}"
                    >
                        {{ __('website.ai_clear') }}
                    </button>
                </header>

                <div
                    class="karwan-ai-status"
                    id="karwanAiStatus"
                    role="status"
                    aria-live="polite"
                    aria-atomic="true"
                    data-tone="neutral"
                ></div>

                <div
                    class="karwan-ai-messages"
                    id="karwanAiMessages"
                    role="log"
                    aria-live="polite"
                    aria-relevant="additions text"
                    aria-atomic="false"
                    aria-busy="false"
                    aria-label="{{ __('website.ai_assistant') }}"
                    tabindex="0"
                >
                    <div
                        class="karwan-ai-message assistant"
                        role="article"
                        aria-label="{{ __('website.ai_assistant') }}"
                        data-initial-message="true"
                        data-sender="assistant"
                    >
                        {{ __('website.ai_welcome') }}
                    </div>
                </div>

                <form
                    class="karwan-ai-form"
                    id="karwanAiForm"
                    novalidate
                >
                    <label
                        class="karwan-ai-sr-only"
                        for="karwanAiInput"
                    >
                        {{ __('website.ai_input_placeholder') }}
                    </label>

                    <textarea
                        id="karwanAiInput"
                        rows="2"
                        maxlength="{{ $karwanAiMessageLimit }}"
                        placeholder="{{ __('website.ai_input_placeholder') }}"
                        aria-describedby="karwanAiHelp karwanAiCharacterCount"
                        autocomplete="off"
                        enterkeyhint="send"
                        spellcheck="true"
                        required
                    ></textarea>

                    <div class="karwan-ai-form-meta">
                        <span id="karwanAiHelp">
                            {{ $karwanAiIsUrdu
                                ? 'Enter سے پیغام بھیجیں، نئی لائن کے لیے Shift + Enter دبائیں۔'
                                : 'Enter se message bhejein; new line ke liye Shift + Enter.' }}
                        </span>

                        <span
                            id="karwanAiCharacterCount"
                            aria-live="off"
                        >
                            0 / {{ $karwanAiMessageLimit }}
                        </span>
                    </div>

                    <button
                        type="submit"
                        id="karwanAiSubmit"
                        disabled
                    >
                        {{ __('website.ai_send') }}
                    </button>
                </form>

                <footer class="karwan-ai-footer">
                    <span>
                        {{ $karwanAiIsUrdu
                            ? 'کاروان اے آئی صرف کمپنی کی سفری معلومات فراہم کرتا ہے۔'
                            : 'Karwan AI sirf company travel information provide karta hai.' }}
                    </span>

                    <a
                        href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->whatsapp ?? '+923012592342') }}"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        {{ __('website.ai_whatsapp_support') }}
                    </a>
                </footer>
            </section>
        </details>
    </div>

    <script>
        (function () {
            'use strict';

            function initializeKarwanAiWidget() {
                const widget =
                    document.querySelector(
                        '[data-karwan-ai-widget]',
                    );

                if (
                    ! widget
                    || widget.dataset.karwanAiReady === 'true'
                ) {
                    return;
                }

                const details =
                    widget.querySelector(
                        '#karwanAiDetails',
                    );

                const toggle =
                    widget.querySelector(
                        '#karwanAiToggle',
                    );

                const form =
                    widget.querySelector(
                        '#karwanAiForm',
                    );

                const input =
                    widget.querySelector(
                        '#karwanAiInput',
                    );

                const messages =
                    widget.querySelector(
                        '#karwanAiMessages',
                    );

                const status =
                    widget.querySelector(
                        '#karwanAiStatus',
                    );

                const clearButton =
                    widget.querySelector(
                        '#karwanAiClear',
                    );

                const submitButton =
                    widget.querySelector(
                        '#karwanAiSubmit',
                    );

                const characterCount =
                    widget.querySelector(
                        '#karwanAiCharacterCount',
                    );

                if (
                    ! details
                    || ! toggle
                    || ! form
                    || ! input
                    || ! messages
                    || ! status
                    || ! clearButton
                    || ! submitButton
                    || ! characterCount
                ) {
                    return;
                }

                widget.dataset.karwanAiReady =
                    'true';

                const chatUrl =
                    @json(route('karwan-ai.chat'));

                const historyUrl =
                    @json(route('karwan-ai.history'));

                const clearUrl =
                    @json(route('karwan-ai.clear'));

                const csrfToken =
                    @json(csrf_token());

                const messageLimit =
                    @json($karwanAiMessageLimit);

                const labels = {
                    welcome:
                        @json(__('website.ai_welcome')),

                    assistant:
                        @json(__('website.ai_assistant')),

                    send:
                        @json(__('website.ai_send')),

                    sending:
                        @json(__('website.ai_sending')),

                    sendError:
                        @json(__('website.ai_send_error')),

                    connectionError:
                        @json(__('website.ai_connection_error')),

                    loadingHistory:
                        @json(
                            $karwanAiIsUrdu
                                ? 'پچھلی گفتگو لوڈ ہو رہی ہے۔'
                                : 'Pichli guftagu load ho rahi hai.'
                        ),

                    ready:
                        @json(
                            $karwanAiIsUrdu
                                ? 'کاروان اے آئی تیار ہے۔'
                                : 'Karwan AI tayyar hai.'
                        ),

                    clearing:
                        @json(
                            $karwanAiIsUrdu
                                ? 'گفتگو صاف کی جا رہی ہے۔'
                                : 'Guftagu clear ho rahi hai.'
                        ),

                    cleared:
                        @json(
                            $karwanAiIsUrdu
                                ? 'گفتگو صاف ہو گئی۔'
                                : 'Guftagu clear ho gayi.'
                        ),

                    clearError:
                        @json(
                            $karwanAiIsUrdu
                                ? 'گفتگو صاف نہیں ہو سکی۔ دوبارہ کوشش کریں۔'
                                : 'Guftagu clear nahi hui. Dobara try karein.'
                        ),

                    rateLimited:
                        @json(
                            $karwanAiIsUrdu
                                ? 'بہت زیادہ درخواستیں بھیجی گئی ہیں۔ ایک منٹ بعد دوبارہ کوشش کریں۔'
                                : 'Bohat zyada requests hain. Aik minute baad dobara try karein.'
                        ),

                    unavailable:
                        @json(
                            $karwanAiIsUrdu
                                ? 'کاروان اے آئی عارضی طور پر دستیاب نہیں۔ واٹس ایپ سپورٹ استعمال کریں۔'
                                : 'Karwan AI filhal available nahi. WhatsApp support use karein.'
                        ),

                    offline:
                        @json(
                            $karwanAiIsUrdu
                                ? 'آپ آف لائن ہیں۔ انٹرنیٹ کنکشن چیک کریں۔'
                                : 'Aap offline hain. Internet connection check karein.'
                        ),

                    online:
                        @json(
                            $karwanAiIsUrdu
                                ? 'انٹرنیٹ کنکشن بحال ہو گیا۔'
                                : 'Internet connection restore ho gaya.'
                        ),

                    required:
                        @json(
                            $karwanAiIsUrdu
                                ? 'براہ کرم اپنا سوال لکھیں۔'
                                : 'Apna sawal likhein.'
                        ),

                    timeout:
                        @json(
                            $karwanAiIsUrdu
                                ? 'جواب میں وقت لگ رہا ہے۔ دوبارہ کوشش کریں یا واٹس ایپ سپورٹ استعمال کریں۔'
                                : 'Response timeout hua. Dobara try karein ya WhatsApp support use karein.'
                        ),
                };

                let historyLoaded =
                    false;

                let isSending =
                    false;

                let isClearing =
                    false;

                function setStatus(
                    message,
                    tone = 'neutral',
                ) {
                    status.textContent =
                        message || '';

                    status.dataset.tone =
                        tone;
                }

                function setMessagesBusy(
                    busy,
                ) {
                    messages.setAttribute(
                        'aria-busy',
                        busy
                            ? 'true'
                            : 'false',
                    );
                }

                function updateCharacterCount() {
                    characterCount.textContent =
                        input.value.length
                        + ' / '
                        + messageLimit;
                }

                function updateControls() {
                    const hasMessage =
                        input.value.trim().length > 0;

                    const offline =
                        navigator.onLine === false;

                    input.disabled =
                        isSending
                        || isClearing;

                    submitButton.disabled =
                        isSending
                        || isClearing
                        || offline
                        || ! hasMessage;

                    clearButton.disabled =
                        isSending
                        || isClearing;

                    submitButton.textContent =
                        isSending
                            ? labels.sending
                            : labels.send;
                }

                function addMessage(
                    sender,
                    message,
                    options = {},
                ) {
                    if (
                        typeof message !== 'string'
                        || message.trim() === ''
                    ) {
                        return;
                    }

                    const element =
                        document.createElement(
                            'div',
                        );

                    const safeSender =
                        sender === 'user'
                            ? 'user'
                            : 'assistant';

                    element.className =
                        'karwan-ai-message '
                        + safeSender;

                    element.setAttribute(
                        'role',
                        'article',
                    );

                    element.setAttribute(
                        'data-sender',
                        safeSender,
                    );

                    element.setAttribute(
                        'aria-label',
                        safeSender === 'user'
                            ? 'You'
                            : labels.assistant,
                    );

                    if (
                        options.initial === true
                    ) {
                        element.setAttribute(
                            'data-initial-message',
                            'true',
                        );
                    }

                    element.textContent =
                        message;

                    messages.appendChild(
                        element,
                    );

                    messages.scrollTop =
                        messages.scrollHeight;
                }

                function resetMessages() {
                    messages.replaceChildren();

                    addMessage(
                        'assistant',
                        labels.welcome,
                        {
                            initial:
                                true,
                        },
                    );
                }

                async function requestJson(
                    url,
                    options = {},
                ) {
                    const controller =
                        new AbortController();

                    const timeoutId =
                        window.setTimeout(
                            function () {
                                controller.abort();
                            },
                            15000,
                        );

                    const headers =
                        Object.assign(
                            {
                                'Accept':
                                    'application/json',

                                'X-Requested-With':
                                    'XMLHttpRequest',
                            },
                            options.headers || {},
                        );

                    try {
                        const response =
                            await fetch(
                                url,
                                Object.assign(
                                    {},
                                    options,
                                    {
                                        headers:
                                            headers,

                                        credentials:
                                            'same-origin',

                                        cache:
                                            'no-store',

                                        signal:
                                            controller.signal,
                                    },
                                ),
                            );

                        let data =
                            {};

                        const contentType =
                            response.headers.get(
                                'content-type',
                            ) || '';

                        if (
                            contentType.includes(
                                'application/json',
                            )
                        ) {
                            data =
                                await response.json();
                        }

                        return {
                            response:
                                response,

                            data:
                                data,
                        };
                    } finally {
                        window.clearTimeout(
                            timeoutId,
                        );
                    }
                }

                function responseErrorMessage(
                    response,
                    data,
                ) {
                    if (
                        response.status === 422
                    ) {
                        return data
                            && data.errors
                            && Array.isArray(
                                data.errors.message,
                            )
                            && data.errors.message[0]
                                ? data.errors.message[0]
                                : labels.sendError;
                    }

                    if (
                        response.status === 429
                    ) {
                        return labels.rateLimited;
                    }

                    if (
                        response.status === 503
                    ) {
                        return labels.unavailable;
                    }

                    if (
                        data
                        && typeof data.message === 'string'
                        && data.message.trim() !== ''
                    ) {
                        return data.message;
                    }

                    return labels.sendError;
                }

                async function loadHistory() {
                    if (
                        historyLoaded
                        || isSending
                        || isClearing
                    ) {
                        return;
                    }

                    setMessagesBusy(
                        true,
                    );

                    setStatus(
                        labels.loadingHistory,
                        'progress',
                    );

                    try {
                        const result =
                            await requestJson(
                                historyUrl,
                            );

                        if (
                            ! result.response.ok
                        ) {
                            setStatus(
                                responseErrorMessage(
                                    result.response,
                                    result.data,
                                ),
                                'error',
                            );

                            return;
                        }

                        historyLoaded =
                            true;

                        const history =
                            result.data.messages;

                        if (
                            Array.isArray(
                                history,
                            )
                            && history.length > 0
                        ) {
                            messages.replaceChildren();

                            history.forEach(
                                function (item) {
                                    addMessage(
                                        item.sender === 'assistant'
                                            ? 'assistant'
                                            : 'user',

                                        String(
                                            item.message || '',
                                        ),
                                    );
                                },
                            );
                        }

                        setStatus(
                            labels.ready,
                            'success',
                        );
                    } catch (error) {
                        setStatus(
                            error
                            && error.name === 'AbortError'
                                ? labels.timeout
                                : labels.connectionError,
                            'error',
                        );
                    } finally {
                        setMessagesBusy(
                            false,
                        );
                    }
                }

                form.addEventListener(
                    'submit',
                    async function (event) {
                        event.preventDefault();

                        if (
                            isSending
                            || isClearing
                        ) {
                            return;
                        }

                        const message =
                            input.value.trim();

                        if (! message) {
                            setStatus(
                                labels.required,
                                'error',
                            );

                            input.focus();

                            return;
                        }

                        if (
                            message.length > messageLimit
                        ) {
                            setStatus(
                                labels.sendError,
                                'error',
                            );

                            return;
                        }

                        addMessage(
                            'user',
                            message,
                        );

                        input.value =
                            '';

                        updateCharacterCount();

                        isSending =
                            true;

                        updateControls();

                        setMessagesBusy(
                            true,
                        );

                        setStatus(
                            labels.sending,
                            'progress',
                        );

                        try {
                            const result =
                                await requestJson(
                                    chatUrl,
                                    {
                                        method:
                                            'POST',

                                        headers: {
                                            'Content-Type':
                                                'application/json',

                                            'X-CSRF-TOKEN':
                                                csrfToken,
                                        },

                                        body:
                                            JSON.stringify(
                                                {
                                                    message:
                                                        message,
                                                },
                                            ),
                                    },
                                );

                            if (
                                ! result.response.ok
                            ) {
                                const errorMessage =
                                    responseErrorMessage(
                                        result.response,
                                        result.data,
                                    );

                                addMessage(
                                    'assistant',
                                    errorMessage,
                                );

                                setStatus(
                                    errorMessage,
                                    'error',
                                );

                                return;
                            }

                            if (
                                ! result.data
                                || typeof result.data.reply !== 'string'
                                || result.data.reply.trim() === ''
                            ) {
                                addMessage(
                                    'assistant',
                                    labels.sendError,
                                );

                                setStatus(
                                    labels.sendError,
                                    'error',
                                );

                                return;
                            }

                            addMessage(
                                'assistant',
                                result.data.reply,
                            );

                            historyLoaded =
                                true;

                            setStatus(
                                labels.ready,
                                'success',
                            );
                        } catch (error) {
                            const errorMessage =
                                error
                                && error.name === 'AbortError'
                                    ? labels.timeout
                                    : labels.connectionError;

                            addMessage(
                                'assistant',
                                errorMessage,
                            );

                            setStatus(
                                errorMessage,
                                'error',
                            );
                        } finally {
                            isSending =
                                false;

                            setMessagesBusy(
                                false,
                            );

                            updateControls();

                            input.focus();
                        }
                    },
                );

                clearButton.addEventListener(
                    'click',
                    async function () {
                        if (
                            isSending
                            || isClearing
                        ) {
                            return;
                        }

                        isClearing =
                            true;

                        updateControls();

                        setMessagesBusy(
                            true,
                        );

                        setStatus(
                            labels.clearing,
                            'progress',
                        );

                        try {
                            const result =
                                await requestJson(
                                    clearUrl,
                                    {
                                        method:
                                            'DELETE',

                                        headers: {
                                            'X-CSRF-TOKEN':
                                                csrfToken,
                                        },
                                    },
                                );

                            if (
                                ! result.response.ok
                            ) {
                                setStatus(
                                    responseErrorMessage(
                                        result.response,
                                        result.data,
                                    ) || labels.clearError,
                                    'error',
                                );

                                return;
                            }

                            resetMessages();

                            historyLoaded =
                                true;

                            setStatus(
                                labels.cleared,
                                'success',
                            );
                        } catch (error) {
                            setStatus(
                                error
                                && error.name === 'AbortError'
                                    ? labels.timeout
                                    : labels.clearError,
                                'error',
                            );
                        } finally {
                            isClearing =
                                false;

                            setMessagesBusy(
                                false,
                            );

                            updateControls();

                            input.focus();
                        }
                    },
                );

                input.addEventListener(
                    'input',
                    function () {
                        updateCharacterCount();

                        updateControls();
                    },
                );

                input.addEventListener(
                    'keydown',
                    function (event) {
                        if (
                            event.key === 'Enter'
                            && ! event.shiftKey
                            && ! event.isComposing
                        ) {
                            event.preventDefault();

                            if (
                                ! submitButton.disabled
                            ) {
                                form.requestSubmit();
                            }
                        }
                    },
                );

                details.addEventListener(
                    'toggle',
                    function () {
                        toggle.setAttribute(
                            'aria-expanded',
                            details.open
                                ? 'true'
                                : 'false',
                        );

                        if (
                            details.open
                        ) {
                            loadHistory();

                            window.setTimeout(
                                function () {
                                    input.focus();
                                },
                                0,
                            );
                        }
                    },
                );

                window.addEventListener(
                    'offline',
                    function () {
                        setStatus(
                            labels.offline,
                            'error',
                        );

                        updateControls();
                    },
                );

                window.addEventListener(
                    'online',
                    function () {
                        setStatus(
                            labels.online,
                            'success',
                        );

                        updateControls();
                    },
                );

                updateCharacterCount();
                updateControls();

                if (
                    navigator.onLine === false
                ) {
                    setStatus(
                        labels.offline,
                        'error',
                    );
                } else {
                    setStatus(
                        labels.ready,
                        'success',
                    );
                }

                if (
                    details.open
                ) {
                    loadHistory();
                }
            }

            if (
                document.readyState === 'loading'
            ) {
                document.addEventListener(
                    'DOMContentLoaded',
                    initializeKarwanAiWidget,
                    {
                        once:
                            true,
                    },
                );
            } else {
                initializeKarwanAiWidget();
            }
        }());
    </script>
@endif
