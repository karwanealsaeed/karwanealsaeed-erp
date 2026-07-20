<div
    style="
        margin-bottom: 1rem;
        border: 1px solid #f59e0b;
        border-radius: 0.9rem;
        background: #fffbeb;
        padding: 1rem 1.15rem;
    "
>
    <div
        style="
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        "
    >
        <div>
            <div
                style="
                    color: #92400e;
                    font-weight: 800;
                    font-size: 0.95rem;
                "
            >
                ⚠ Scheduler {{ $status['state'] }}
            </div>

            <div
                style="
                    margin-top: 0.3rem;
                    color: #78350f;
                    font-size: 0.88rem;
                    line-height: 1.5;
                "
            >
                {{ $status['message'] }}
                Scheduled backups, reminders aur alerts affected ho sakte hain.
            </div>
        </div>

        <a
            href="{{ url('/admin/system-health-check') }}"
            style="
                display: inline-flex;
                align-items: center;
                border-radius: 0.65rem;
                background: #92400e;
                padding: 0.55rem 0.85rem;
                color: white;
                font-size: 0.82rem;
                font-weight: 700;
                text-decoration: none;
            "
        >
            Open System Health
        </a>
    </div>
</div>