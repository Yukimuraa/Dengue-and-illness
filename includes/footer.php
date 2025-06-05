</div>
    <!-- End of Main Content Container -->

    <!-- Footer -->
    <footer class="bg-light py-4 mt-auto border-top">
        <div class="container-fluid">
            <div class="text-center">
                <span>Copyright &copy; Talisay City Health Office <?php echo date('Y'); ?></span>
            </div>
        </div>
    </footer>

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="<?php echo $site_url; ?>/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Core JavaScript-->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Chart.js for analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.5.1/dist/chart.min.js"></script>

    <!-- Custom scripts -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set Chart.js defaults
        if (typeof Chart !== 'undefined') {
            Chart.defaults.font.family = "'Nunito', 'Segoe UI', Roboto, Arial, sans-serif";
            Chart.defaults.font.size = 12;
            Chart.defaults.color = '#858796';
            
            // Monthly Dengue Chart
            const monthlyDengueCtx = document.getElementById('monthlyDengueChart');
            if (monthlyDengueCtx) {
                const monthlyData = JSON.parse(monthlyDengueCtx.getAttribute('data-values'));
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                
                new Chart(monthlyDengueCtx, {
                    type: 'line',
                    data: {
                        labels: months,
                        datasets: [{
                            label: 'Dengue Cases',
                            data: monthlyData,
                            backgroundColor: 'rgba(231, 74, 59, 0.1)',
                            borderColor: '#e74a3b',
                            borderWidth: 2,
                            pointBackgroundColor: '#e74a3b',
                            pointBorderColor: '#fff',
                            pointHoverRadius: 5,
                            pointHoverBackgroundColor: '#e74a3b',
                            pointHoverBorderColor: '#fff',
                            pointHitRadius: 10,
                            pointBorderWidth: 2,
                            tension: 0.3,
                            fill: true
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: '#fff',
                                titleColor: '#5a5c69',
                                bodyColor: '#858796',
                                borderColor: '#dddfeb',
                                borderWidth: 1,
                                xPadding: 15,
                                yPadding: 15,
                                displayColors: false,
                                caretPadding: 10,
                                callbacks: {
                                    label: function(context) {
                                        return context.parsed.y + ' cases';
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false,
                                    drawBorder: false
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: "rgba(0, 0, 0, 0.05)",
                                },
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }
            
            // Illness Type Chart
            const illnessTypeCtx = document.getElementById('illnessTypeChart');
            if (illnessTypeCtx) {
                const labels = JSON.parse(illnessTypeCtx.getAttribute('data-labels'));
                const values = JSON.parse(illnessTypeCtx.getAttribute('data-values'));
                
                new Chart(illnessTypeCtx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: values,
                            backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#5a5c69'],
                            hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617', '#3a3b45'],
                            hoverBorderColor: "rgba(234, 236, 244, 1)",
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    padding: 20
                                }
                            },
                            tooltip: {
                                backgroundColor: '#fff',
                                titleColor: '#5a5c69',
                                bodyColor: '#858796',
                                borderColor: '#dddfeb',
                                borderWidth: 1,
                                xPadding: 15,
                                yPadding: 15,
                                displayColors: false,
                                caretPadding: 10,
                                callbacks: {
                                    label: function(context) {
                                        return context.label + ': ' + context.parsed + ' cases';
                                    }
                                }
                            }
                        }
                    }
                });
            }
            
            // Barangay Distribution Chart
            const barangayDistributionCtx = document.getElementById('barangayDistributionChart');
            if (barangayDistributionCtx) {
                const labels = JSON.parse(barangayDistributionCtx.getAttribute('data-labels'));
                const values = JSON.parse(barangayDistributionCtx.getAttribute('data-values'));
                
                new Chart(barangayDistributionCtx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Dengue Cases',
                            data: values,
                            backgroundColor: '#4e73df',
                            hoverBackgroundColor: '#2e59d9',
                            borderWidth: 0,
                            borderRadius: 4
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: '#fff',
                                titleColor: '#5a5c69',
                                bodyColor: '#858796',
                                borderColor: '#dddfeb',
                                borderWidth: 1,
                                xPadding: 15,
                                yPadding: 15,
                                displayColors: false,
                                caretPadding: 10,
                                callbacks: {
                                    label: function(context) {
                                        return context.parsed.y + ' cases';
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false,
                                    drawBorder: false
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: "rgba(0, 0, 0, 0.05)",
                                },
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }
        }
        
        // Scroll to top button appear
        $(document).on('scroll', function() {
            var scrollDistance = $(this).scrollTop();
            if (scrollDistance > 100) {
                $('.scroll-to-top').fadeIn();
            } else {
                $('.scroll-to-top').fadeOut();
            }
        });
        
        // Smooth scrolling using jQuery easing
        $(document).on('click', 'a.scroll-to-top', function(e) {
            var $anchor = $(this);
            $('html, body').stop().animate({
                scrollTop: 0
            }, 1000, 'easeInOutExpo');
            e.preventDefault();
        });
        
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
    });
    </script>
</body>
</html>