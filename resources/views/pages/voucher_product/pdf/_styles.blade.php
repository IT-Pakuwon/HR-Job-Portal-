<style>
    /* NOTE: do not reset margin/padding on the universal `*` selector — dompdf
       applies that reset to the @page margin box too and silently zeroes out
       the page margins below. Reset only the specific elements that need it. */
    * { box-sizing: border-box; }
    @page { margin: 20mm 18mm 24mm 18mm; }
    body { margin: 0; padding: 0; font-family: "DejaVu Sans", Arial, sans-serif; font-size: 10px; color: #111827; }
    h1, p { margin: 0; padding: 0; }
    table { margin: 0; padding: 0; }
    td, th { margin: 0; }

    /* ---------------------------------------------------------- */
    /* Header                                                      */
    /* ---------------------------------------------------------- */
    table.doc-header { width: 100%; table-layout: fixed; border-collapse: collapse; }
    table.doc-header td { vertical-align: top; padding: 0; }
    table.doc-header td.col-company { width: 62%; }
    table.doc-header td.col-docbox { width: 38%; text-align: right; }

    .company-name { font-size: 14px; font-weight: 700; letter-spacing: .4px; text-transform: uppercase; color: #111827; }
    .company-sub { font-size: 9px; color: #4b5563; margin-top: 2px; }

    .doc-ref { font-size: 14px; font-weight: 700; letter-spacing: .4px; color: #111827; }
    .doc-date { font-size: 9px; color: #4b5563; margin-top: 2px; }

    hr.header-rule { border: none; border-top: 1px solid #111827; margin: 14px 0 0; background: transparent; }

    /* ---------------------------------------------------------- */
    /* Title band                                                  */
    /* ---------------------------------------------------------- */
    .title-band { text-align: center; border-bottom: 1px solid #d1d5db; padding: 10px 0 9px; margin-bottom: 18px; }
    .title-band h1 { display: inline; font-size: 12px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #111827; }

    /* ---------------------------------------------------------- */
    /* Form details grid                                           */
    /* ---------------------------------------------------------- */
    table.info-box { width: 100%; table-layout: fixed; border-collapse: collapse; margin-bottom: 18px; }
    table.info-box td { padding: 6px 2px; font-size: 9px; vertical-align: top; border-bottom: 1px solid #e5e7eb; }
    table.info-box td.label { width: 30mm; color: #6b7280; text-transform: uppercase; letter-spacing: .3px; font-size: 8px; font-weight: 700; }
    table.info-box td.value { font-weight: 700; color: #111827; padding-left: 10px; }

    /* ---------------------------------------------------------- */
    /* Detail table — hairline rules, no grid                       */
    /* ---------------------------------------------------------- */
    table.detail-table { width: 100%; table-layout: fixed; border-collapse: collapse; margin-bottom: 18px; }
    table.detail-table thead th { border-bottom: 1.5px solid #111827; color: #111827; font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: .3px; padding: 7px 6px; text-align: center; }
    table.detail-table tbody td { border-bottom: 1px solid #e5e7eb; padding: 6px 6px; font-size: 9px; word-wrap: break-word; }
    table.detail-table td.num { text-align: right; }
    table.detail-table td.center { text-align: center; }
    table.detail-table tfoot td { border-top: 1.5px solid #111827; font-weight: 700; padding: 7px 6px; }
    table.detail-table tfoot td.label-cell { text-align: right; padding-right: 10px; text-transform: uppercase; letter-spacing: .3px; font-size: 8.5px; }

    /* ---------------------------------------------------------- */
    /* Approval / sign-off block                                   */
    /* ---------------------------------------------------------- */
    .section-label { font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: #374151; margin-bottom: 6px; }

    table.sign-table { width: 100%; table-layout: fixed; border-collapse: collapse; }
    table.sign-table thead th { border-bottom: 1.5px solid #111827; padding: 6px 6px 8px; font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: .3px; color: #374151; text-align: center; }
    table.sign-table tbody td { border-right: 1px solid #e5e7eb; padding: 0 10mm; text-align: center; vertical-align: top; }
    table.sign-table tbody td:last-child { border-right: none; }

    /* Blank reserved area above the rule — where a signature/stamp would go */
    .sign-space { height: 14mm; }
    .sign-rule { border-top: 1px dotted #9ca3af; margin: 0 8mm 8px; }
    .sign-name { font-weight: 700; font-size: 9.5px; color: #111827; margin-bottom: 3px; }
    .sign-meta { font-size: 8px; color: #6b7280; padding-bottom: 10px; }
    .sign-meta-pending { font-style: italic; }

    /* ---------------------------------------------------------- */
    /* Footer — repeats on every page                               */
    /* ---------------------------------------------------------- */
    .pdf-footer { position: fixed; bottom: -18mm; left: 0; right: 0; }
    .pdf-footer .footer-rule { border-top: 1px solid #d1d5db; margin-bottom: 4px; }
    table.footer-table { width: 100%; border-collapse: collapse; }
    table.footer-table td { font-size: 7.5px; color: #6b7280; padding: 0; }
    table.footer-table td.footer-right { text-align: right; }
    .footer-page:after { content: "Page " counter(page); }
</style>
