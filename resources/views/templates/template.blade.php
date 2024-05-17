<!DOCTYPE html>
<html>
<head>
    <!-- <link rel="stylesheet" href="{{ asset('asset/css/template.css') }}"> -->
    <link rel="stylesheet" href="{{ asset('asset/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/css/template.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/css/select2.min.css') }}">

    <script src="{{ asset('asset/js/jquery.min.js') }}"></script>
    <script src="{{ asset('asset/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('asset/js/select2.min.js') }}"></script>
    <script src="{{ asset('asset/js/template.js') }}"></script>
    <title>{form_name}</title>
    <style>
        header {
            background-color: {header_background_color};
            color: {header_color};
            padding: 10px;
            font-size: {header_front_size}px;
            text-align: center;
        }

        form{
            font-size: {form_front_size}px;
        }

        footer {
            background-color: {footer_background_color};
            color: {footer_color};
            padding: 10px;
            font-size: {footer_front_size}px;
            text-align: center;
        }
        button {
            background-color: {form_button_background_color} !important ;
            color: {form_button_color} !important ;
        }
    </style>
</head>
<body>
    <header>
        <h1>{header_title}</h1>
    </header>

    <div class="container">        
        {form_data}
    </div>
    <div class="container">        
        <div class="alert alert-success" id="successMessage" style="display: none;">
            <strong>{message_after_success}</strong> 
        </div>
        <div id="error_msg">    
        </div>
    </div>

    <footer>
        <h1>{footer_title}</h1>
    </footer>
</body>
</html>