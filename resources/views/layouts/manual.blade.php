<style>
    /* ================================
   MANUAL BASE STYLE
================================ */

    .manual-note {
        border-radius: 12px;
        padding: 16px 18px;
        font-size: 14px;
        line-height: 1.6;
        border: 1px solid #e5e7eb;
        background: #ffffff;
        transition: all .25s ease;
    }

    /* subtle hover like modern docs */
    .manual-note:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 14px rgba(0, 0, 0, 0.06);
    }

    /* DARK MODE BASE */
    .dark .manual-note {
        background: #0f172a;
        border-color: #1f2937;
    }


    /* ================================
   INFO STYLE (BLUE)
================================ */

    .manual-info {
        background-color: #eff6ff;
        border-left: 4px solid #3b82f6;
        border-color: #bfdbfe;
        color: #1e40af;
    }

    .dark .manual-info {
        background-color: rgba(30, 58, 138, 0.18);
        border-left-color: #60a5fa;
        border-color: #1e3a8a;
        color: #93c5fd;
    }


    /* ================================
   WARNING STYLE (ORANGE)
================================ */

    .manual-warning {
        background-color: #fff7ed;
        border-left: 4px solid #f97316;
        border-color: #fed7aa;
        color: #9a3412;
    }

    .dark .manual-warning {
        background-color: rgba(154, 52, 18, 0.18);
        border-left-color: #fb923c;
        border-color: #7c2d12;
        color: #fdba74;
    }


    /* ================================
   IMPORTANT STYLE (RED)
================================ */

    .manual-important {
        background-color: #fef2f2;
        border-left: 4px solid #ef4444;
        border-color: #fecaca;
        color: #991b1b;
    }

    .dark .manual-important {
        background-color: rgba(127, 29, 29, 0.25);
        border-left-color: #f87171;
        border-color: #7f1d1d;
        color: #fca5a5;
    }


    /* ================================
   SUCCESS STYLE (GREEN)
================================ */

    .manual-success {
        background-color: #ecfdf5;
        border-left: 4px solid #22c55e;
        border-color: #bbf7d0;
        color: #065f46;
    }

    .dark .manual-success {
        background-color: rgba(6, 95, 70, 0.25);
        border-left-color: #4ade80;
        border-color: #065f46;
        color: #86efac;
    }


    /* ================================
   OPTIONAL: TITLE STYLE INSIDE BOX
================================ */

    .manual-note strong {
        font-weight: 600;
    }

    /* ===============================
   Manual Figure (Global Standard)
================================= */

    .manual-figure {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
    }

    /* Image Styling */
    .manual-figure img {
        max-width: 100%;
        height: auto;
        border-radius: 0.75rem;
        /* rounded-xl */
        border: 1px solid #e5e7eb;
        /* gray-200 */
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.06);
    }

    /* Caption Styling */
    .manual-figure figcaption {
        margin-top: 0.75rem;
        font-size: 0.75rem;
        /* text-xs */
        color: #6b7280;
        /* gray-500 */
    }

    /* ===============================
   Dark Mode Support
================================= */

    .dark .manual-figure img {
        border: 1px solid #1f2937;
        /* gray-800 */
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
    }

    .dark .manual-figure figcaption {
        color: #9ca3af;
        /* gray-400 */
    }

    .faq-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 1rem;
        padding: 1.2rem;
        transition: all 0.2s ease;
    }

    .faq-card:hover {
        border-color: #d1d5db;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.04);
    }

    .dark .faq-card {
        background: #111827;
        border: 1px solid #1f2937;
    }

    .dark .faq-card:hover {
        border-color: #374151;
    }

    .faq-question {
        width: 100%;
        text-align: left;
        font-weight: 600;
        font-size: 0.95rem;
        color: #111827;
    }

    .dark .faq-question {
        color: #f9fafb;
    }

    .faq-answer {
        margin-top: 1rem;
        font-size: 0.875rem;
        color: #4b5563;
    }

    .dark .faq-answer {
        color: #9ca3af;
    }
</style>
