{{-- CHANGED (UX/bulk PDF): styles extracted from report-cards/pdf.blade.php so the single-student
     and bulk (whole class) PDF templates share one stylesheet. Static CSS only — never put
     Blade echoes in here (formatters corrupt them; brand colour is applied via inline attributes). --}}
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 11px;
        color: #333;
        padding: 20px;
    }

    .header {
        margin-bottom: 20px;
        /* brand-coloured border applied inline on the element */
        padding-bottom: 15px;
    }

    .header-table {
        width: 100%;
        border-collapse: collapse;
    }

    .header-left,
    .header-right {
        width: 18%;
        text-align: center;
        vertical-align: top;
    }

    .header-middle {
        width: 64%;
        text-align: center;
        vertical-align: top;
    }

    .badge,
    .student-photo {
        width: 85px;
        height: 85px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        object-fit: cover;
    }

    .header h1 {
        font-size: 18px;
        /* brand colour applied inline on the element */
    }

    .header p {
        font-size: 10px;
        color: #666;
        margin-top: 3px;
    }

    .header .title {
        font-size: 14px;
        font-weight: bold;
        margin-top: 10px;
        text-transform: uppercase;
        letter-spacing: 1px;
        /* brand colour applied inline on the element */
    }

    .info-grid {
        display: table;
        width: 100%;
        margin-bottom: 15px;
    }

    .info-row {
        display: table-row;
    }

    .info-cell {
        display: table-cell;
        width: 50%;
        padding: 3px 0;
    }

    .info-cell .label {
        color: #888;
        font-size: 9px;
        text-transform: uppercase;
    }

    .info-cell .value {
        font-weight: bold;
        font-size: 11px;
    }

    table.grades {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    table.grades th {
        background: #1e40af;
        color: #fff;
        padding: 8px 6px;
        font-size: 10px;
        text-transform: uppercase;
        text-align: left;
    }

    table.grades td {
        padding: 6px;
        border-bottom: 1px solid #e5e7eb;
        font-size: 11px;
    }

    table.grades tr:nth-child(even) {
        background: #f9fafb;
    }

    .summary {
        margin-top: 15px;
        padding: 10px;
        background: #f9fbff;
        border-radius: 4px;
        border: 1px solid #e5e7eb;
    }

    .summary p {
        margin-bottom: 4px;
    }

    .footer {
        margin-top: 40px;
        display: table;
        width: 100%;
    }

    .sig-block {
        display: table-cell;
        width: 33%;
        text-align: center;
        padding-top: 30px;
    }

    .sig-line {
        border-top: 1px solid #333;
        width: 80%;
        margin: 0 auto;
    }

    .sig-label {
        font-size: 9px;
        color: #666;
        margin-top: 4px;
    }

    .page-break {
        page-break-after: always;
    }
</style>