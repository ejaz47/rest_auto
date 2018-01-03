<?php require_once 'config.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Rest Generator</title>
    <!-- Bootstrap core CSS -->
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/styles/default.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/styles/monokai-sublime.min.css">
    <!-- Custom styles for this template -->

    <script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/highlight.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/highlightjs-line-numbers.js/2.1.0/highlightjs-line-numbers.min.js"></script>
    <style>
    body {
        padding-top: 54px;
    }

    @media (min-width: 992px) {
        body {
            padding-top: 56px;
        }
    }

    pre{
        box-shadow: 3px 3px 18px rgba(0, 0, 0, 0.35), 6px 5px 26px rgba(0, 0, 0, 0.38);
    }

    code{
        overflow: scroll;
        max-height: 500px;
    }

    .scroll4::-webkit-scrollbar {
      width: 10px;
      height: 10px;
    }
     
    .scroll4::-webkit-scrollbar-thumb {
      background: #666;
      /*border-radius: 20px;*/
    }

    .scroll4::-webkit-scrollbar-track {
      background: transparent;
      border-radius: 20px;
    }

    .scroll4::-webkit-scrollbar-corner{
        height: 0px;
        width: 0px;
        background-color: transparent;
    }

    #php_data{
        min-height: 500px;
        width: 100%;
        background-color: #000;
    }

    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">Rest Generator</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <!-- <div class="collapse navbar-collapse" id="navbarResponsive">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item active">
                        <a class="nav-link" href="#">Home
                <span class="sr-only">(current)</span>
              </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Contact</a>
                    </li>
                </ul>
            </div> -->
        </div>
    </nav>

    <!-- Page Content -->
    <div class="container">
        <div class="row">
            <div class="col-md-6" style="padding-top: 20px; ">
                
                <div class="form-group">
                    <select class="form-control" id="ddl_database">
                        <option disabled selected>Select Database</option>
                        <?php
                            // Usage without mysql_list_dbs()
                            $link = mysql_connect($config['database']['server'], $config['database']['username'], $config['database']['password']);
                            if (!$link) {
                                die('Could not connect: ' . mysql_error());
                            }
                            $res = mysql_query("SHOW DATABASES");

                            while ($row = mysql_fetch_assoc($res)) {
                                echo '<option>'. $row['Database'] .'</option>';
                            }
                        ?>
                    </select>
                </div>
            </div>
        </div>
        
        <div id="tables_"></div>
        
        <div id="php_data"></div>

    </div>

    <!-- Bootstrap core JavaScript -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function(){
            console.log('running');
            hljs.initHighlightingOnLoad();
            $('#ddl_database').on('change', function(e){
                $('#tables_').text('Please Wait ...');
                console.log($(this).val());
                var db = $(this).val();
                //do ajax here
                $.get('get_tables.php?database=' + db, function(data){
                    // console.log(data);
                    $('#tables_').html(data);
                });

            });

            $('body').on('change', '#ddl_table, #txt_name', function(e){
                $('#php_data').html('<h5 style="text-align: center;padding:30px; color:#fff">Please Wait..</h1>');
                var table = $('#ddl_table').val();
                var db = $('#ddl_database').val();
                var name = $('#txt_name').val();
                //do ajax here
                $.get('get_php.php?database=' + db + '&table=' + table + '&name=' + name, function(data){
                    // console.log(data);
                    $('#php_data').html(data);
                    $('code').each(function(i, block) {
                        hljs.highlightBlock(block);
                        hljs.lineNumbersBlock(block);
                    });
                });

            });

        });
    </script>
</body>

</html>