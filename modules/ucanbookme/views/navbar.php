<?php global$currentPage; ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-black px-3">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">L4UCanBookMe</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText"
                aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse px-3" id="navbarText">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <?php if($currentPage=="index.php"){ ?>
                        <a class="nav-link active" aria-current="page" href="index.php">Home</a>
                    <?php }else{ ?>
                        <a class="nav-link" href="index.php">Home</a>
                    <?php }//else ?>
                </li>
                <li class="nav-item">
                    <?php if($currentPage=="bookingStep.php"){ ?>
                        <a class="nav-link active" aria-current="page" href="bookingStep.php">Booking</a>
                    <?php }else{ ?>
                        <a class="nav-link" href="bookingStep.php">Booking</a>
                    <?php }//else ?>
                </li>
            </ul>
            <span class="navbar-text">
                logout
            </span>
        </div>
    </div>
</nav>