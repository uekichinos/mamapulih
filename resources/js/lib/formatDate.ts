/**
 * Formats an ISO date/datetime string as an unambiguous, readable date
 * (e.g. "10 November 2026") instead of the raw "2026-11-10T00:00:00.000000Z"
 * that Carbon's default JSON serialization produces.
 */
export function formatDate(value: string | null | undefined): string {
    if (!value) {
        return '—';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
}

/**
 * Same as {@link formatDate}, but with the time included — for timestamps
 * where "when exactly" matters, like audit log entries.
 */
export function formatDateTime(value: string | null | undefined): string {
    if (!value) {
        return '—';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toLocaleString('en-GB', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}
