# Karwan AI Final UAT

## Scope

This checklist covers the public website and customer portal Karwan AI widget. External AI providers remain disabled. The approved runtime provider is `local`.

## Automated acceptance

- Emergency switch removes widget markup when disabled.
- Chat, history and clear endpoints complete the session-scoped flow.
- Provider, source, latency and request ID metadata are returned.
- Raw messages are rendered through `textContent`.
- Requests use same-origin credentials, CSRF protection and a 15-second timeout.
- HTTP 422, 429 and 503 states receive user-friendly handling.
- Offline and online browser events update the widget state.
- Responsive CSS includes safe-area, reduced-motion and forced-colors handling.

## Manual browser checklist

### Chrome / Edge desktop

- Open the public home page.
- Open Karwan AI and confirm focus moves to the message field.
- Send a Roman Urdu question using Enter.
- Use Shift + Enter and confirm it creates a new line.
- Confirm the send button cannot submit an empty message.
- Refresh the page, reopen the widget and confirm history is restored.
- Clear the conversation and confirm the welcome message returns.

### Mobile viewport

- Test at 360 × 800 and 390 × 844.
- Confirm the panel stays inside the viewport.
- Confirm the keyboard does not hide the send button permanently.
- Confirm buttons meet touch-target expectations.
- Confirm the widget does not cover the primary navigation controls.

### Keyboard-only

- Reach the widget toggle with Tab.
- Open with Enter or Space.
- Navigate to Clear, message field, Send and WhatsApp support.
- Confirm visible focus indicators.
- Confirm Escape/browser navigation does not trap keyboard focus.

### Screen reader

- Confirm the widget toggle announces its expanded state.
- Confirm the panel has a labelled region.
- Confirm new messages are announced through the chat log.
- Confirm status changes are announced without repeating the full conversation.
- Confirm the textarea has a meaningful label and help text.

### Offline / timeout

- Disable the network and confirm the offline status appears.
- Confirm Send becomes unavailable while offline.
- Restore the network and confirm controls recover.
- Simulate a slow request and confirm the timeout message appears.

### Rate limit

- Trigger the chat throttle in a test environment.
- Confirm HTTP 429 displays the wait-and-retry message.
- Confirm the interface returns to an interactive state.

### Emergency switch

- Set `KARWAN_AI_ENABLED=false` in a test environment.
- Clear the config cache.
- Confirm the widget markup is absent from public and portal pages.
- Confirm direct AI endpoints return the configured disabled response.

## Privacy and security

- No raw prompt or reply is stored in `ai_usage_logs`.
- No message HTML is inserted with `innerHTML`.
- No chat content is stored in localStorage or sessionStorage.
- WhatsApp opens with `noopener noreferrer`.
- External OpenAI/Gemini requests remain disabled.

## Manual sign-off

- Tester:
- Browser/version:
- Desktop result:
- Mobile result:
- Keyboard result:
- Screen-reader result:
- Network/error result:
- Final decision:
- Date:
