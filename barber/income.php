<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css/table.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="icon" type="image/png" href="css/images/favicon-32x32.png">
    <title>Income</title>

</head>
<body>
    <div class="body d-flex py-3 mt-5">
      <div class="container-xxl">
        <h1 class="dashboard mb-5 ms-0">Income</h1>
        
        <div class="row g-3 mb-4">
                <div class="col-6 custom-width">
                    <div class="alert-warning alert mb-0">
                        <div class="d-flex align-items-center">
                            <div class=""><i class="fa-solid fa-dollar-sign fa-lg"></i></div>
                            <div class="flex-fill ms-3 text-truncate">
                                <div class="h5 mb-0 mt-0">Today</div>
                                  
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-6 custom-width">
                    <div class="alert-warning alert mb-0">
                        <div class="d-flex align-items-center">
                            <div class=""><i class="fa-solid fa-dollar-sign fa-lg"></i></div>
                            <div class="flex-fill ms-3 text-truncate">
                                <div class="h5 mb-0 mt-0">This Week</div>
                            
                            </div>
                        </div>
                    </div>
                </div>
        </div>
              
        <div class="row g-3 mb-4">
                    <div class="col-6 custom-width">
                        <div class="alert-warning alert mb-0">
                            <div class="d-flex align-items-center">
                                <div class=""><i class="fa-solid fa-dollar-sign fa-lg"></i></div>
                                <div class="flex-fill ms-3 text-truncate">
                                    <div class="h5 mb-0 mt-0">This Month</div>

                                </div>
                            </div>
                        </div>
                    </div>
             
                    <div class="col-6 custom-width">
                        <div class="alert-warning alert mb-0">
                            <div class="d-flex align-items-center">
                                <div class=""><i class="fa-solid fa-dollar-sign fa-lg"></i></div>
                                <div class="flex-fill ms-3 text-truncate">
                                    <div class="h5 mb-0 mt-0">This Year</div>
                              
                                </div>
                            </div>
                        </div>
                    </div>
        </div>
          <div class="row g-3 mb-5 ms-0">
            <div class="col-md-12">
                <div class="card border-danger">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center bg-transparent border-bottom-0">
                        <h4 class="ms-2 mt-2 fw-bold" style="color: #000000;">Today</h4> <!-- lagyan container -->
                        <td><button> View All </button></td>
                    </div>

                    <div class="card-body">
                        <table id="myDataTable" class="table table-hover align-middle mb-0" style="width: 100%;">  
                          <thead>
                              <tr>
                                  <td>Time</td>
                                  <td>Total</td>
                              </tr>
                          </thead>
                          <tbody>

                          </tbody>
                        </table>
                    </div>
                </div>
            </div>
          </div>


<nav id="sidebarMenu" class="collapse d-lg-block sidebar collapse">
  <div class="position-sticky">
    <div class="list-group list-group-flush mx-3 mt-5">
    <a href="b_dashboard.php"><img src="css/images/jof_logo_black.png" alt="logo" width="45" height="45"></a>
    <img src="css/images/barber.jpg" alt="Avatar" width="140r" height="140" style="border: 5px solid #000000; border-radius: 50%;">

    <h5 style="text-align: center;"> Barber </h5>
      <a href="b_dashboard.php" class="list-group-item list-group-item-action py-2 ripple">
        <i class="fa-solid fa-border-all fa-fw me-3"></i><span>Dashboard</span>
      </a>
      <a href="schedule.php" class="list-group-item list-group-item-action py-2 ripple">
        <i class="fa-solid fa-users fa-fw me-3"></i><span>Schedule</span>
      </a>
      <a href="b_history.php" class="list-group-item list-group-item-action py-2 ripple"
        ><i class="fa-solid fa-pills fa-fw me-3"></i><span>History</span></a
      >
      <a href="income.php" class="list-group-item list-group-item-action py-2 ripple"
        ><i class="fa-solid fa-receipt fa-fw me-3"></i><span>Income</span></a
      >
      <a href="../logout-staff.php" class="list-group-item list-group-item-action py-2 ripple"
        ><i class="fa-solid fa-right-from-bracket fa-fw me-3"></i><span>Log Out</span></a
      >
    </div>
  </div>
</nav>


</body>
</html>