<div class="karwan-ai-widget" id="karwanAiWidget">
    <details class="karwan-ai-details">
        <summary>
            <span>🤖</span>
            {{ __('website.ai_name') }}
        </summary>

        <div class="karwan-ai-panel">
            <div class="karwan-ai-head">
                <div>
                    <strong>{{ __('website.ai_assistant') }}</strong>
                    <span>{{ __('website.ai_services') }}</span>
                </div>

                <button type="button" id="karwanAiClear">
                    {{ __('website.ai_clear') }}
                </button>
            </div>

            <div class="karwan-ai-messages" id="karwanAiMessages">
                <div class="karwan-ai-message assistant">
                    {{ __('website.ai_welcome') }}
                </div>
            </div>

            <form class="karwan-ai-form" id="karwanAiForm">
                <textarea
                    id="karwanAiInput"
                    rows="2"
                    maxlength="2000"
                    placeholder="{{ __('website.ai_input_placeholder') }}"
                    required
                ></textarea>

                <button type="submit">
                    {{ __('website.ai_send') }}
                </button>
            </form>

            <div class="karwan-ai-footer">
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->whatsapp ?? '+923012592342') }}"
                   target="_blank"
                   rel="noopener">
                    {{ __('website.ai_whatsapp_support') }}
                </a>
            </div>
        </div>
    </details>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('karwanAiForm');
    const input = document.getElementById('karwanAiInput');
    const messages = document.getElementById('karwanAiMessages');
    const clearButton = document.getElementById('karwanAiClear');

    if (!form || !input || !messages) {
        return;
    }

    const chatUrl = @json(route('karwan-ai.chat'));
    const historyUrl = @json(route('karwan-ai.history'));
    const clearUrl = @json(route('karwan-ai.clear'));
    const csrfToken = @json(csrf_token());

    const welcomeMessage = @json(__('website.ai_welcome'));
    const sendLabel = @json(__('website.ai_send'));
    const sendingLabel = @json(__('website.ai_sending'));
    const sendErrorMessage = @json(__('website.ai_send_error'));
    const connectionErrorMessage = @json(__('website.ai_connection_error'));

    function addMessage(sender, message) {
        const element = document.createElement('div');

        element.className = 'karwan-ai-message ' + sender;
        element.textContent = message;

        messages.appendChild(element);
        messages.scrollTop = messages.scrollHeight;
    }

    async function loadHistory() {
        try {
            const response = await fetch(historyUrl, {
                headers: {
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();

            if (!Array.isArray(data.messages) || data.messages.length === 0) {
                return;
            }

            messages.innerHTML = '';

            data.messages.forEach(function (item) {
                addMessage(
                    item.sender === 'assistant' ? 'assistant' : 'user',
                    item.message
                );
            });
        } catch (error) {
            //
        }
    }

    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        const message = input.value.trim();

        if (!message) {
            return;
        }

        addMessage('user', message);
        input.value = '';

        const submitButton = form.querySelector('button[type="submit"]');

        submitButton.disabled = true;
        submitButton.textContent = sendingLabel;

        try {
            const response = await fetch(chatUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    message: message,
                }),
            });

            const data = await response.json();

            if (!response.ok) {
                addMessage('assistant', sendErrorMessage);
                return;
            }

            addMessage('assistant', data.reply);
        } catch (error) {
            addMessage('assistant', connectionErrorMessage);
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = sendLabel;
        }
    });

    clearButton?.addEventListener('click', async function () {
        try {
            await fetch(clearUrl, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            });

            messages.innerHTML = '';
            addMessage('assistant', welcomeMessage);
        } catch (error) {
            //
        }
    });

    loadHistory();
});
</script>