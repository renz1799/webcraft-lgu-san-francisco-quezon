<style>
    .core-print-sidebar {
        display: flex;
        flex-direction: column;
        gap: 1.125rem;
    }

    .core-print-sidebar__intro {
        padding-bottom: 0.25rem;
        border-bottom: 1px solid rgba(148, 163, 184, 0.22);
    }

    .core-print-sidebar__eyebrow {
        font-size: 0.74rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: rgb(180, 83, 9);
        margin-bottom: 0.45rem;
    }

    .core-print-sidebar__help {
        margin: 0;
        font-size: 0.96rem;
        line-height: 1.55;
        color: rgb(71, 85, 105);
    }

    .core-print-sidebar__form {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .core-print-sidebar__section {
        display: flex;
        flex-direction: column;
        gap: 0.95rem;
        padding: 0.1rem 0;
    }

    .core-print-sidebar__section--actions {
        padding-top: 1rem;
        border-top: 1px solid rgba(148, 163, 184, 0.22);
    }

    .core-print-sidebar__section-title {
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: rgb(100, 116, 139);
    }

    .core-print-sidebar__field {
        display: flex;
        flex-direction: column;
        gap: 0.45rem;
    }

    .core-print-sidebar__field-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.85rem;
    }

    .core-print-sidebar .form-label {
        margin-bottom: 0;
        font-size: 0.94rem;
        font-weight: 600;
        color: rgb(30, 41, 59);
    }

    .core-print-sidebar__label {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }

    .core-print-sidebar__tooltip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.1rem;
        height: 1.1rem;
        border-radius: 9999px;
        background: rgba(37, 99, 235, 0.12);
        color: rgb(37, 99, 235);
        font-size: 0.78rem;
        line-height: 1;
        cursor: help;
        user-select: none;
    }

    .core-print-sidebar__tooltip:focus-visible {
        outline: 2px solid rgba(37, 99, 235, 0.35);
        outline-offset: 2px;
    }

    .core-print-sidebar .form-control {
        min-height: 2.9rem;
        font-size: 0.96rem;
        border-radius: 0.6rem;
        padding: 0.75rem 0.9rem;
    }

    .core-print-sidebar .form-control::placeholder {
        color: rgb(148, 163, 184);
        font-size: 0.93rem;
    }

    .core-print-sidebar__note {
        margin: -0.15rem 0 0;
        font-size: 0.88rem;
        line-height: 1.5;
        color: rgb(100, 116, 139);
    }

    .core-print-sidebar__link-button {
        align-self: flex-start;
        padding: 0;
        border: 0;
        background: transparent;
        color: rgb(37, 99, 235);
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
    }

    .core-print-sidebar__link-button:hover {
        color: rgb(29, 78, 216);
        text-decoration: underline;
    }

    .core-print-sidebar__stats {
        display: flex;
        flex-direction: column;
        gap: 0.7rem;
    }

    .core-print-sidebar__stat {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 1rem;
        font-size: 0.94rem;
    }

    .core-print-sidebar__stat-label {
        color: rgb(71, 85, 105);
    }

    .core-print-sidebar__stat-value {
        color: rgb(15, 23, 42);
        font-weight: 700;
        text-align: right;
    }

    .core-print-sidebar__actions {
        display: flex;
        flex-direction: column;
        gap: 0.7rem;
    }

    .core-print-sidebar .ti-btn {
        min-height: 2.9rem;
        font-size: 0.96rem;
        font-weight: 600;
        border-radius: 0.6rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .core-print-sidebar__reset {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        min-height: 2.5rem;
        font-size: 0.92rem;
        font-weight: 500;
        color: rgb(71, 85, 105);
        text-decoration: none;
        border-radius: 0.55rem;
        transition: background-color 0.15s ease, color 0.15s ease;
    }

    .core-print-sidebar__reset:hover {
        background: rgba(148, 163, 184, 0.08);
        color: rgb(15, 23, 42);
        text-decoration: none;
    }

    @media (max-width: 640px) {
        .core-print-sidebar__field-grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }
</style>
