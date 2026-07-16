<div class="karwan-ai-widget" id="karwanAiWidget">
    <details class="karwan-ai-details">
        <summary>
            <span>🤖</span>
            Karwan AI
        </summary>

        <div class="karwan-ai-panel">
            <div class="karwan-ai-head">
                <div>
                    <strong>Karwan AI Assistant</strong>
                    <span>Umrah, Visas, Tickets, Hotels & Tours</span>
                </div>

                <button type="button" id="karwanAiClear">Clear</button>
            </div>

            <div class="karwan-ai-messages" id="karwanAiMessages">
                <div class="karwan-ai-message assistant">
                    Assalam o Alaikum! Main Karwan E Al Saeed ki travel services ke bare me madad kar sakta hoon.
                </div>
            </div>

            <form class="karwan-ai-form" id="karwanAiForm">
                <textarea
                    id="karwanAiInput"
                    rows="2"
                    maxlength="2000"
                    placeholder="Apna sawal likhein..."
                    required
                ></textarea>

                <button type="submit">Send</button>
            </form>

            <div class="karwan-ai-footer">
                <a href="https://wa.me/923012592342" target="_blank">
                    WhatsApp Support
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
        submitButton.textContent = 'Sending...';

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
                addMessage('assistant', 'Message send nahi hua. Dobara try karein.');
                return;
            }

            addMessage('assistant', data.reply);
        } catch (error) {
            addMessage('assistant', 'Connection error. WhatsApp support use karein.');
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = 'Send';
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
            addMessage(
                'assistant',
                'Assalam o Alaikum! Main Karwan E Al Saeed ki travel services ke bare me madad kar sakta hoon.'
            );
        } catch (error) {
            //
        }
    });

    loadHistory();
});
</script>