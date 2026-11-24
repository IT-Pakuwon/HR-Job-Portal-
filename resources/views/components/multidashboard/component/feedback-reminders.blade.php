{{-- Pending training-feedback on a dashboard: only the popup ("Your feedback is needed") is shown. The amber reminder strip
     that pending-training-feedback.js also draws is kept out of sight (display:none !important beats the script's class toggling),
     because the popup already covers it.
     The container itself must stay in the page: the script fills it and does nothing at all when it is missing, which would
     also switch the popup off. My APP (pages/my-page) has its own container and still shows the strip.
     Rendered by component/favourite.blade.php, or by dashboard-top when My Favourites is not on the dashboard. --}}
<div id="pendingFeedbackReminders" class="hidden" style="display: none !important;" aria-hidden="true"></div>

<script src="{{ asset('assets/js/multidashboard/pending-training-feedback.js') }}?v={{ filemtime(public_path('assets/js/multidashboard/pending-training-feedback.js')) }}"></script>
