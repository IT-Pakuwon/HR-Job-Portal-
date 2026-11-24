{{-- Work tabs of the Approval Dashboard: the approval panel itself (Waiting Approval / Approval History, plus the My Activity tab that
     index.blade.php adds). Placed by Dashboard Setup (block "tabs"); dashboard-top includes it with the variables the panel reads
     ($doctypes, $akses_cc, $isGA, $myPage, $embedded), passed from dashboard-approval as block-data. --}}
@include('components.panel.approval')
