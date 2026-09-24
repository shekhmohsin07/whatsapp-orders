<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

<style>
    :root {
        --brand: #F15A24;
        --brand-dark: #d94d1a;
        --brand-soft: #fff1ec;
        --bs-primary: #F15A24;
        --bs-primary-rgb: 241, 90, 36;
        --bs-link-color: #F15A24;
        --bs-link-color-rgb: 241, 90, 36;
        --bs-link-hover-color: #d94d1a;
        --bs-link-hover-color-rgb: 217, 77, 26;
    }

    body { background: #f6f7f9; }

    .btn-primary {
        --bs-btn-bg: #F15A24; --bs-btn-border-color: #F15A24;
        --bs-btn-hover-bg: #d94d1a; --bs-btn-hover-border-color: #d94d1a;
        --bs-btn-active-bg: #c24416; --bs-btn-active-border-color: #c24416;
        --bs-btn-disabled-bg: #F15A24; --bs-btn-disabled-border-color: #F15A24;
    }
    .btn-outline-primary {
        --bs-btn-color: #F15A24; --bs-btn-border-color: #F15A24;
        --bs-btn-hover-bg: #F15A24; --bs-btn-hover-border-color: #F15A24;
        --bs-btn-active-bg: #F15A24; --bs-btn-active-border-color: #F15A24;
        --bs-btn-disabled-color: #F15A24; --bs-btn-disabled-border-color: #F15A24;
    }
    .text-primary { color: #F15A24 !important; }
    .form-control:focus, .form-select:focus, .form-check-input:focus {
        border-color: #F15A24;
        box-shadow: 0 0 0 .25rem rgba(241, 90, 36, .2);
    }
    .form-check-input:checked { background-color: #F15A24; border-color: #F15A24; }

    .card { border: 1px solid #eceef1; border-radius: .75rem; }
    .brand-mark { color: var(--brand); }
</style>