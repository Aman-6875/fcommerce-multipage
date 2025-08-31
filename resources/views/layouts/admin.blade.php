
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>@yield('title') | {{ app_name() }}</title>
    
    {{-- Favicons and App Icons --}}
    @include('components.favicon')

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('css/bootstrap1.min.css') }}" />
    <!-- themefy CSS -->
    <link rel="stylesheet" href="{{ asset('vendors/themefy_icon/themify-icons.css') }}" />
    <!-- select2 CSS -->
    <link rel="stylesheet" href="{{ asset('vendors/niceselect/css/nice-select.css') }}" />
    <!-- owl carousel CSS -->
    <link rel="stylesheet" href="{{ asset('vendors/owl_carousel/css/owl.carousel.css') }}" />
    <!-- gijgo css -->
    <link rel="stylesheet" href="{{ asset('vendors/gijgo/gijgo.min.css') }}" />
    <!-- font awesome CSS -->
    <link rel="stylesheet" href="{{ asset('vendors/font_awesome/css/all.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendors/tagsinput/tagsinput.css') }}" />

    <!-- date picker -->
     <link rel="stylesheet" href="{{ asset('vendors/datepicker/date-picker.css') }}" />

     <link rel="stylesheet" href="{{ asset('vendors/vectormap-home/vectormap-2.0.2.css') }}" />

     <!-- scrollabe  -->
     <link rel="stylesheet" href="{{ asset('vendors/scroll/scrollable.css') }}" />
    <!-- datatable CSS -->
    <link rel="stylesheet" href="{{ asset('vendors/datatable/css/jquery.dataTables.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendors/datatable/css/responsive.dataTables.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendors/datatable/css/buttons.dataTables.min.css') }}" />
    <!-- text editor css -->
    <link rel="stylesheet" href="{{ asset('vendors/text_editor/summernote-bs4.css') }}" />
    <!-- morris css -->
    <link rel="stylesheet" href="{{ asset('vendors/morris/morris.css') }}">
    <!-- metarial icon css -->
    <link rel="stylesheet" href="{{ asset('vendors/material_icon/material-icons.css') }}" />

    <!-- menu css  -->
    <link rel="stylesheet" href="{{ asset('css/metisMenu.css') }}">
    <!-- style CSS -->
    <link rel="stylesheet" href="{{ asset('css/style1.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/colors/default.css') }}" id="colorSkinCSS">

    <!-- Modal Fix CSS for Admin Panel -->
    <style>
        .modal-backdrop {
            z-index: 1040 !important;
            background-color: rgba(0, 0, 0, 0.5) !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }
        .modal {
            z-index: 1050 !important;
            pointer-events: none !important;
        }
        .modal-dialog {
            z-index: 1060 !important;
            position: relative;
            pointer-events: auto !important;
        }
        .modal-content {
            background-color: #fff !important;
            border-radius: 8px !important;
            pointer-events: auto !important;
        }
        .modal.fade.show {
            z-index: 1050 !important;
            display: block !important;
        }
        .modal-backdrop.fade.show {
            z-index: 1040 !important;
            opacity: 0.5 !important;
        }
    </style>

    @stack('styles')
</head>
<body class="crm_body_bg">

    @include('layouts.partials.admin.sidebar')

    <section class="main_content dashboard_part large_header_bg">
        @include('layouts.partials.admin.header')

        <div class="main_content_iner overly_inner ">
            <div class="container-fluid p-0 ">
                @yield('content')
            </div>
        </div>

        @include('layouts.partials.admin.footer')
    </section>

    <!-- footer  -->
    <script src="{{ asset('js/jquery1-3.4.1.min.js') }}"></script>
    <!-- popper js -->
    <script src="{{ asset('js/popper1.min.js') }}"></script>
    <!-- bootstarp js -->
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <!-- sidebar menu  -->
    <script src="{{ asset('js/metisMenu.js') }}"></script>
    <!-- waypoints js -->
    <script src="{{ asset('vendors/count_up/jquery.waypoints.min.js') }}"></script>
    <!-- waypoints js -->
    <script src="{{ asset('vendors/chartlist/Chart.min.js') }}"></script>
    <!-- counterup js -->
    <script src="{{ asset('vendors/count_up/jquery.counterup.min.js') }}"></script>

    <!-- nice select -->
    <script src="{{ asset('vendors/niceselect/js/jquery.nice-select.min.js') }}"></script>
    <!-- owl carousel -->
    <script src="{{ asset('vendors/owl_carousel/js/owl.carousel.min.js') }}"></script>

    <!-- responsive table -->
    <script src="{{ asset('vendors/datatable/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendors/datatable/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('vendors/datatable/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('vendors/datatable/js/buttons.flash.min.js') }}"></script>
    <script src="{{ asset('vendors/datatable/js/jszip.min.js') }}"></script>
    <script src="{{ asset('vendors/datatable/js/pdfmake.min.js') }}"></script>
    <script src="{{ asset('vendors/datatable/js/vfs_fonts.js') }}"></script>
    <script src="{{ asset('vendors/datatable/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('vendors/datatable/js/buttons.print.min.js') }}"></script>

    <!-- datepicker  -->
    <script src="{{ asset('vendors/datepicker/datepicker.js') }}"></script>
    <script src="{{ asset('vendors/datepicker/datepicker.en.js') }}"></script>
    <script src="{{ asset('vendors/datepicker/datepicker.custom.js') }}"></script>

    <script src="{{ asset('js/chart.min.js') }}"></script>
    <script src="{{ asset('vendors/chartjs/roundedBar.min.js') }}"></script>

    <!-- progressbar js -->
    <script src="{{ asset('vendors/progressbar/jquery.barfiller.js') }}"></script>
    <!-- tag input -->
    <script src="{{ asset('vendors/tagsinput/tagsinput.js') }}"></script>
    <!-- text editor js -->
    <script src="{{ asset('vendors/text_editor/summernote-bs4.js') }}"></script>
    <script src="{{ asset('vendors/am_chart/amcharts.js') }}"></script>

    <!-- scrollabe  -->
    <script src="{{ asset('vendors/scroll/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('vendors/scroll/scrollable-custom.js') }}"></script>

    <!-- vector map  -->
    <script src="{{ asset('vendors/vectormap-home/vectormap-2.0.2.min.js') }}"></script>
    <script src="{{ asset('vendors/vectormap-home/vectormap-world-mill-en.js') }}"></script>

    <!-- apex chrat  -->
    <script src="{{ asset('vendors/apex_chart/apex-chart2.js') }}"></script>
    <script src="{{ asset('vendors/apex_chart/apex_dashboard.js') }}"></script>

    <script src="{{ asset('vendors/echart/echarts.min.js') }}"></script>


    <script src="{{ asset('vendors/chart_am/core.js') }}"></script>
    <script src="{{ asset('vendors/chart_am/charts.js') }}"></script>
    <script src="{{ asset('vendors/chart_am/animated.js') }}"></script>
    <script src="{{ asset('vendors/chart_am/kelly.js') }}"></script>
    <script src="{{ asset('vendors/chart_am/chart-custom.js') }}"></script>
    <!-- custom js -->
    <script src="{{ asset('js/dashboard_init.js') }}"></script>
    <script src="{{ asset('js/custom.js') }}"></script>

    @stack('scripts')
</body>
</html>
