<?php
global $db, $date;
$loginID = $_SESSION['id'];
?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h3 class="m-0">
                    <svg class="nav-icon mr-2" height="1.5em" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20"><path d="M10 0a10 10 0 1 0 10 10A10.011 10.011 0 0 0 10 0Zm0 5a3 3 0 1 1 0 6 3 3 0 0 1 0-6Zm0 13a8.949 8.949 0 0 1-4.951-1.488A3.987 3.987 0 0 1 9 13h2a3.987 3.987 0 0 1 3.951 3.512A8.949 8.949 0 0 1 10 18Z" /></svg>
                    L4U Password
                </h3>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item active"><a href="main.php">L4U Password</a></li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<div class="content">
    <div class="container-fluid">

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-end">
                        
                    </div>

                    <div class="card-body">
                        <div class="card">
                            <div class="card-body table-responsive p-4" style="height: 620px;">
                                <table id="datatable" class="table table-borderless table-striped table-hover" style="width:100%">
                                    <thead class="thead-dark">
                                    <tr>
                                        <th style="width:25%">Level : Name</th>
                                        <th style="width:15%">Link</th>
                                        <th style="width:20%">User</th>
                                        <th style="width:15%">Password</th>
                                        <th style="width:20%">Note</th>
                                    </tr>
                                    </thead>
                                    <tfoot class="thead-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Link</th>
                                        <th>User</th>
                                        <th>Password</th>
                                        <th>Note</th>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                            
                            <!-- /.card-body -->
                        </div>
                    </div>

                </div>
            </div><!-- /.col-md-12 -->
        </div><!-- /.row -->

        <div id="alert" style="
        display: float; 
        right: 20px; 
        bottom: 30px; 
        position: fixed; 
        background-color: #007bff; 
        color: white; 
        padding: 15px; 
        border-radius: 5px;
        z-index: 1;
        box-shadow: 0 4px 4px 0 rgb(191 191 191 / 20%);
        ">
            Text Copied
        </div>

        <!-- Modal -->
        <div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
            
        </div>

    </div><!-- /.container-fluid -->
</div>
<!-- /.content -->

<script>

    function showCopy() {
        $("#alert").fadeIn(500);
        setTimeout(function() {
            $("#alert").fadeOut();
        }, 1000);
    }

</script>