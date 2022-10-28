<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="TemplateMo">
    <link href="https://fonts.googleapis.com/css?family=Poppins:100,200,300,400,500,600,700,800,900" rel="stylesheet">

    <title>SkilHive</title>

    <!-- Bootstrap core CSS -->


    <link rel="stylesheet" href="./plugins/landing/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">


    <!-- Additional CSS Files -->

    <link rel="stylesheet" href="./plugins/landing/assets/css/fontawesome.css">
    <link rel="stylesheet" href="./plugins/landing/assets/css/templatemo-edu-meeting.css">
    <link rel="stylesheet" href="./plugins/landing/assets/css/owl.css">
    <link rel="stylesheet" href="./plugins/landing/assets/css/lightbox.css">

    <!--

TemplateMo 569 Edu Meeting

https://templatemo.com/tm-569-edu-meeting

-->
</head>

<body>

    <!-- Sub Header -->
    <div class="sub-header">
        <div class="container">
            <div class="row">

                <div class="col-lg-4 col-sm-4">
                    <div class="right-icons">
                        <ul>
                            <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                            <li><a href="#"><i class="fa fa-twitter"></i></a></li>
                            <li><a href="#"><i class="fa fa-behance"></i></a></li>
                            <li><a href="#"><i class="fa fa-linkedin"></i></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ***** Header Area Start ***** -->
    <header class="header-area header-sticky">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <nav class="main-nav">
                        <!-- ***** Logo Start ***** -->
                        <a href="index.html" class="logo">
                            SkilHive
                        </a>
                        <!-- ***** Logo End ***** -->
                        <!-- ***** Menu Start ***** -->
                        <ul class="nav">
                            <li class="scroll-to-section"><a href="#" class="active">Home</a></li>
                            <li><a href={{ route('login') }}>Login</a></li>
                            <li class="scroll-to-section">
                                <a href="{{ route('register') }}">Register</a>
                            </li>

                            <li class="scroll-to-section"><a href="#">About Us</a></li>
                            <li class="scroll-to-section"><a href="#">Contact Us</a></li>
                        </ul>
                        <a class='menu-trigger'>
                            <span>Menu</span>
                        </a>
                        <!-- ***** Menu End ***** -->
                    </nav>
                </div>
            </div>
        </div>
    </header>
    <!-- ***** Header Area End ***** -->

    <!-- ***** Main Banner Area Start ***** -->
    <section class="section main-banner" id="top" data-section="section1">
        <video autoplay muted loop id="bg-video">


            <source src="./plugins/landing/assets/images/mama.mkv" type="video/mp4" />

        </video>

        <div class="video-overlay header-text">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="caption">
                            
                            <h2>Welcome to SkilHive</h2>
                            <p>
                            Forget the old rules. You can have the best people.
                            Right now. Right here.

                            </p>
                            <div class="main-button-red">
                                <div class="scroll-to-section"><a href="{{ route('register') }}">Join Us Now!</a></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ***** Main Banner Area End ***** -->

    <section class="services">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="owl-service-item owl-carousel">

                        <div class="item">
                            <div class="icon">

                             <img src="{{asset('landing/assets/images/service-icon-01.png')}}" alt="">


                            </div>
                            <div class="down-content">
                                <h4>Reminders</h4>
                                <p>
                                    We will remind you of your next appointment and also send you a notification when you
                                    are due for your next appointment or when you are due for your next checkup.

                                    </p>
                            </div>
                        </div>

                        <div class="item">
                            <div class="icon">
                                <img src="{{asset('landing/assets/images/service-icon-01.png')}}" alt="">
                            </div>
                            <div class="down-content">
                                <h4>Meal Plans and Foods</h4>
                                <p>
                                    We will provide you with a meal plan that will help you to have a healthy diet during
                                    your pregnancy and also provide you with a list of foods that you should avoid during
                                    your pregnancy.
                                    </p>
                            </div>
                        </div>

                        <div class="item">
                            <div class="icon">
                             <img src="{{asset('landing/assets/images/service-icon-01.png')}}" alt="">
                            </div>
                            <div class="down-content">
                                <h4>Check Ups</h4>
                                <p>
                                    We will provide you with a list of checkups that you should do during your pregnancy
                                    and also provide you with a list of checkups that you should do after your pregnancy.

                                    </p>
                            </div>
                        </div>

                        <div class="item">
                            <div class="icon">
                                <img src="{{asset('landing/assets/images/service-icon-01.png')}}" alt="">
                            </div>
                            <div class="down-content">
                                <h4>Conselling Services</h4>
                                <p>
                                    We will provide you with a list of counsellors that you can contact in case you need
                                    help or you need to talk to someone.

                                    </p>
                            </div>
                        </div>

                        <div class="item">
                            <div class="icon">
                             <img src="{{asset('landing/assets/images/service-icon-01.png')}}" alt="">
                            </div>
                            <div class="down-content">
                                <h4>Emergency Contacts</h4>
                                <p>
                                    We will provide you with a list of emergency contacts that you can contact in case of
                                    an emergency.
                                    </p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>



    <!-- Scripts -->
    <!-- Bootstrap core JavaScript -->

    <script src="./plugins/landing/vendor/jquery/jquery.min.js" defer></script>
    <script src="./plugins/landing/vendor/bootstrap/js/bootstrap.bundle.min.js" defer></script>
    <script src="./plugins/landing/vendor/bootstrap/js/bootstrap.bundle.min.js" defer></script>


    <script src="./plugins/landing/assets/js/isotope.js" defer></script>
    <script src="./plugins/landing/assets/js/owl-carousel.js" defer></script>
    <script src="./plugins/landing/assets/js/lightbox.js" defer></script>
    <script src="./plugins/landing/assets/js/tabs.js" defer></script>
    <script src="./plugins/landing/assets/js/video.js" defer></script>
    <script src="./plugins/landing/assets/js/slick-slider.js" defer></script>
    <script src="./plugins/landing/assets/js/custom.js" defer></script>

    <script>
        //according to loftblog tut
        $('.nav li:first').addClass('active');

        var showSection = function showSection(section, isAnimate) {
            var
                direction = section.replace(/#/, ''),
                reqSection = $('.section').filter('[data-section="' + direction + '"]'),
                reqSectionPos = reqSection.offset().top - 0;

            if (isAnimate) {
                $('body, html').animate({
                        scrollTop: reqSectionPos
                    },
                    800);
            } else {
                $('body, html').scrollTop(reqSectionPos);
            }

        };

        var checkSection = function checkSection() {
            $('.section').each(function() {
                var
                    $this = $(this),
                    topEdge = $this.offset().top - 80,
                    bottomEdge = topEdge + $this.height(),
                    wScroll = $(window).scrollTop();
                if (topEdge < wScroll && bottomEdge > wScroll) {
                    var
                        currentId = $this.data('section'),
                        reqLink = $('a').filter('[href*=\\#' + currentId + ']');
                    reqLink.closest('li').addClass('active').
                    siblings().removeClass('active');
                }
            });
        };

        $('.main-menu, .responsive-menu, .scroll-to-section').on('click', 'a', function(e) {
            e.preventDefault();
            showSection($(this).attr('href'), true);
        });

        $(window).scroll(function() {
            checkSection();
        });
    </script>
</body>
<script defer src="https://widget.tochat.be/bundle.js?key=858a201d-3630-4b2d-b974-e9de0e660929"></script>
</body>

</html>
